<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $totalProducts = Product::count();

        $salesToday   = Sale::whereDate('created_at', today())->sum('total');
        $todayOrders  = Sale::whereDate('created_at', today())->count();

        $totalCustomers = Customer::count();

        $totalInventory = Product::sum('stock_qty');

        $recentSales = Sale::with(['customer', 'createdBy'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($s) => [
                'id'             => $s->id,
                'invoice_no'     => $s->invoice_no,
                'customer'       => $s->customer?->name ?? 'Walk-in',
                'total'          => $s->total,
                'paid_amount'    => $s->paid_amount,
                'due_amount'     => $s->due_amount,
                'payment_method' => $s->payment_method,
                'status'         => $s->payment_status,
                'date'           => $s->created_at->format('d M Y'),
            ]);

        return response()->json([
            'total_products'   => $totalProducts,
            'sales_today'      => $salesToday,
            'today_orders'     => $todayOrders,
            'total_customers'  => $totalCustomers,
            'total_inventory'  => $totalInventory,
            'recent_sales'     => $recentSales,
        ]);
    }
}
