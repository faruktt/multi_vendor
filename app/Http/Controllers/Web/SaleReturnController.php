<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\StockMovement;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SaleReturnController extends Controller
{
    public function store(Request $request, Vendor $branch, Sale $sale)
    {
        $request->validate([
            'items'                 => 'required|array|min:1',
            'items.*.sale_item_id'  => 'required|exists:sale_items,id',
            'items.*.quantity'      => 'required|integer|min:1',
            'reason'                => 'nullable|string|max:500',
            'refund_method'         => ['required', 'string', Rule::in(PaymentMethod::where('is_active', true)->pluck('key'))],
        ]);

        $sale->load('saleItems.returnItems');

        $refundAmount = 0;
        $lines = [];

        foreach ($request->items as $row) {
            $saleItem = $sale->saleItems->firstWhere('id', (int) $row['sale_item_id']);

            if (!$saleItem) {
                return $this->fail($request, 'One of the selected items does not belong to this invoice.');
            }

            $alreadyReturned = $saleItem->returnItems->sum('quantity');
            $remaining = $saleItem->quantity - $alreadyReturned;

            if ($row['quantity'] > $remaining) {
                return $this->fail($request, "Cannot return {$row['quantity']} of \"{$saleItem->product?->name}\" — only {$remaining} left returnable.");
            }

            $subtotal = $saleItem->unit_price * $row['quantity'];
            $refundAmount += $subtotal;

            $lines[] = [
                'sale_item' => $saleItem,
                'quantity'  => (int) $row['quantity'],
                'subtotal'  => $subtotal,
            ];
        }

        $saleReturn = DB::transaction(function () use ($request, $branch, $sale, $lines, $refundAmount) {
            $saleReturn = SaleReturn::create([
                'vendor_id'     => $branch->id,
                'sale_id'       => $sale->id,
                'reason'        => $request->reason,
                'refund_amount' => $refundAmount,
                'refund_method' => $request->refund_method,
                'created_by'    => auth()->id(),
            ]);

            foreach ($lines as $line) {
                $saleItem = $line['sale_item'];

                $saleReturn->items()->create([
                    'sale_item_id' => $saleItem->id,
                    'product_id'   => $saleItem->product_id,
                    'quantity'     => $line['quantity'],
                    'unit_price'   => $saleItem->unit_price,
                    'subtotal'     => $line['subtotal'],
                ]);

                Product::withoutGlobalScopes()->where('id', $saleItem->product_id)
                    ->increment('stock_qty', $line['quantity']);

                if ($saleItem->product_variant_id) {
                    ProductVariant::where('id', $saleItem->product_variant_id)
                        ->increment('stock_qty', $line['quantity']);
                }

                StockMovement::create([
                    'vendor_id'      => $branch->id,
                    'product_id'     => $saleItem->product_id,
                    'type'           => 'in',
                    'quantity'       => $line['quantity'],
                    'reference_type' => 'sale_return',
                    'reference_id'   => $saleReturn->id,
                    'note'           => 'Return: ' . $sale->invoice_no,
                ]);
            }

            $total = max(0, $sale->total - $refundAmount);
            if ($sale->due_amount >= $refundAmount) {
                $due  = $sale->due_amount - $refundAmount;
                $paid = $sale->paid_amount;
            } else {
                $remainder = $refundAmount - $sale->due_amount;
                $due  = 0;
                $paid = max(0, $sale->paid_amount - $remainder);
            }
            $status = $due <= 0 ? 'paid' : ($paid > 0 ? 'partial' : 'pending');

            $sale->update([
                'total'          => $total,
                'due_amount'     => $due,
                'paid_amount'    => $paid,
                'payment_status' => $status,
            ]);

            return $saleReturn;
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success'        => true,
                'refund_amount'  => (float) $saleReturn->refund_amount,
                'total'          => (float) $sale->fresh()->total,
                'due_amount'     => (float) $sale->fresh()->due_amount,
                'paid_amount'    => (float) $sale->fresh()->paid_amount,
                'payment_status' => $sale->fresh()->payment_status,
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
