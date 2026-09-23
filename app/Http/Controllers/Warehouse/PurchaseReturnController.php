<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\StockMovement;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurchaseReturnController extends Controller
{
    public function store(Request $request, Vendor $branch, Purchase $purchase)
    {
        $request->validate([
            'items'                     => 'required|array|min:1',
            'items.*.purchase_item_id'  => 'required|exists:purchase_items,id',
            'items.*.quantity'          => 'required|integer|min:1',
            'reason'                    => 'nullable|string|max:500',
            'refund_method'             => ['required', 'string', Rule::in(PaymentMethod::where('is_active', true)->pluck('key'))],
        ]);

        $purchase->load('items.returnItems', 'items.product');

        $refundAmount = 0;
        $lines = [];

        foreach ($request->items as $row) {
            $purchaseItem = $purchase->items->firstWhere('id', (int) $row['purchase_item_id']);

            if (!$purchaseItem) {
                return $this->fail($request, 'One of the selected items does not belong to this purchase.');
            }

            $alreadyReturned = $purchaseItem->returnItems->sum('quantity');
            $remaining = $purchaseItem->quantity - $alreadyReturned;

            if ($row['quantity'] > $remaining) {
                return $this->fail($request, "Cannot return {$row['quantity']} of \"{$purchaseItem->product_name}\" — only {$remaining} left returnable.");
            }

            // Can't send back more than is currently on the shelf — some of this purchase
            // may already have been transferred out to branches or sold since it arrived.
            $availableStock = $purchaseItem->product?->stock_qty ?? 0;
            if ($row['quantity'] > $availableStock) {
                return $this->fail($request, "Cannot return {$row['quantity']} of \"{$purchaseItem->product_name}\" — only {$availableStock} currently in Warehouse stock (the rest has already moved on).");
            }

            $subtotal = $purchaseItem->unit_cost * $row['quantity'];
            $refundAmount += $subtotal;

            $lines[] = [
                'purchase_item' => $purchaseItem,
                'quantity'      => (int) $row['quantity'],
                'subtotal'      => $subtotal,
            ];
        }

        $purchaseReturn = DB::transaction(function () use ($request, $branch, $purchase, $lines, $refundAmount) {
            $purchaseReturn = PurchaseReturn::create([
                'vendor_id'     => $branch->id,
                'purchase_id'   => $purchase->id,
                'reason'        => $request->reason,
                'refund_amount' => $refundAmount,
                'refund_method' => $request->refund_method,
                'created_by'    => auth()->id(),
            ]);

            foreach ($lines as $line) {
                $purchaseItem = $line['purchase_item'];

                $purchaseReturn->items()->create([
                    'purchase_item_id' => $purchaseItem->id,
                    'product_id'       => $purchaseItem->product_id,
                    'quantity'         => $line['quantity'],
                    'unit_cost'        => $purchaseItem->unit_cost,
                    'subtotal'         => $line['subtotal'],
                ]);

                Product::withoutGlobalScopes()->where('id', $purchaseItem->product_id)
                    ->decrement('stock_qty', $line['quantity']);

                StockMovement::create([
                    'vendor_id'      => $branch->id,
                    'product_id'     => $purchaseItem->product_id,
                    'type'           => 'out',
                    'quantity'       => $line['quantity'],
                    'reference_type' => 'purchase_return',
                    'reference_id'   => $purchaseReturn->id,
                    'note'           => 'Return: ' . $purchase->invoice_no,
                ]);
            }

            $total = max(0, $purchase->total - $refundAmount);
            if ($purchase->due_amount >= $refundAmount) {
                $due  = $purchase->due_amount - $refundAmount;
                $paid = $purchase->paid_amount;
            } else {
                $remainder = $refundAmount - $purchase->due_amount;
                $due  = 0;
                $paid = max(0, $purchase->paid_amount - $remainder);
            }
            $status = $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'pending');

            $purchase->update([
                'total'          => $total,
                'due_amount'     => $due,
                'paid_amount'    => $paid,
                'payment_status' => $status,
            ]);

            return $purchaseReturn;
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success'        => true,
                'refund_amount'  => (float) $purchaseReturn->refund_amount,
                'total'          => (float) $purchase->fresh()->total,
                'due_amount'     => (float) $purchase->fresh()->due_amount,
                'paid_amount'    => (float) $purchase->fresh()->paid_amount,
                'payment_status' => $purchase->fresh()->payment_status,
            ]);
        }

        return back()->with('success', 'Return recorded and stock updated.');
    }

    private function fail(Request $request, string $message)
    {
        if ($request->wantsJson()) {
            return response()->json(['success' => false, 'message' => $message], 422);
        }

        return back()->with('error', $message);
    }
}
