<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CourierSetting;
use App\Models\OrderStatus;
use App\Models\PaymentMethod;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierSaleController extends Controller
{
    public function index(Request $request)
    {
        $baseSupplierSalesQuery = fn() => Sale::withoutGlobalScopes()->where(function ($q) {
            $q->whereNotNull('supplier_id')
              ->orWhereHas('saleItems', fn($sq) => $sq->whereNotNull('supplier_id'));
        });

        $query = $baseSupplierSalesQuery()
            ->with([
                'customer',
                'supplier',
                'saleItems' => fn($q) => $q->with(['product', 'variant.color', 'variant.size', 'supplier']),
                'notes.user.roles'
            ])
            ->withCount('notes')
            ->latest();

        // Filter by supplier
        if ($request->filled('supplier_id')) {
            $supplierId = (int) $request->supplier_id;
            $query->where(function ($q) use ($supplierId) {
                $q->where('supplier_id', $supplierId)
                  ->orWhereHas('saleItems', fn($sq) => $sq->where('supplier_id', $supplierId));
            });
        }

        // Filter by search (invoice, customer name or phone)
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('invoice_no', 'like', "%{$s}%")
                  ->orWhereHas('customer', function ($cq) use ($s) {
                      $cq->where('name', 'like', "%{$s}%")
                         ->orWhere('phone', 'like', "%{$s}%");
                  });
            });
        }

        // Filter by payment status
        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        // Filter by order status
        if ($request->filled('order_status')) {
            $query->where('order_status', $request->order_status);
        }

        // Date range
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $sales = $query->paginate(20)->withQueryString();

        // Suppliers list with dynamic order counts for filter
        $suppliers = Supplier::orderBy('name')->get()->map(function ($supplier) {
            $supplier->orders_count = Sale::withoutGlobalScopes()
                ->where(function ($q) use ($supplier) {
                    $q->where('supplier_id', $supplier->id)
                      ->orWhereHas('saleItems', fn($sq) => $sq->where('supplier_id', $supplier->id));
                })->count();
            return $supplier;
        });

        // Financial KPIs based on current filter or overall supplier sales
        $itemQuery = SaleItem::whereNotNull('supplier_id');
        if ($request->filled('supplier_id')) {
            $itemQuery->where('supplier_id', (int) $request->supplier_id);
        }
        if ($request->filled('from')) {
            $itemQuery->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $itemQuery->whereDate('created_at', '<=', $request->to);
        }

        $kpis = [
            'total_orders'     => (clone $query)->count(),
            'total_gross'      => (float) (clone $itemQuery)->sum('subtotal'),
            'total_commission' => (float) (clone $itemQuery)->sum('admin_commission_amount'),
            'total_net'        => (float) (clone $itemQuery)->sum('supplier_earning'),
            'total_items'      => (int)   (clone $itemQuery)->sum('quantity'),
        ];

        $orderStatuses = OrderStatus::orderBy('sort_order')->get();
        $paymentMethods = PaymentMethod::options();
        $activeCouriers = CourierSetting::active()->get();

        return view('admin.supplier-sales.index', compact(
            'sales',
            'suppliers',
            'kpis',
            'orderStatuses',
            'paymentMethods',
            'activeCouriers'
        ));
    }

    public function show(Sale $sale)
    {
        $sale->load([
            'saleItems.product',
            'saleItems.variant.color',
            'saleItems.variant.size',
            'saleItems.supplier',
            'customer',
            'supplier',
            'createdBy',
            'notes.user.roles',
        ]);

        $orderStatuses = OrderStatus::orderBy('sort_order')->get();
        $paymentMethods = PaymentMethod::options();
        $activeCouriers = CourierSetting::active()->get();

        return view('admin.supplier-sales.show', compact(
            'sale',
            'orderStatuses',
            'paymentMethods',
            'activeCouriers'
        ));
    }

    public function updateStatus(Request $request, Sale $sale)
    {
        $request->validate([
            'order_status' => 'required|string|max:50',
        ]);

        $sale->update([
            'order_status' => $request->order_status,
        ]);

        return redirect()->back()->with('success', 'Order status updated successfully.');
    }

    public function invoice(Sale $sale)
    {
        $sale->load([
            'saleItems.product',
            'saleItems.variant.color',
            'saleItems.variant.size',
            'saleItems.supplier',
            'customer',
            'supplier',
            'createdBy',
            'vendor',
        ]);

        return view('admin.supplier-sales.invoice', compact('sale'));
    }
}
