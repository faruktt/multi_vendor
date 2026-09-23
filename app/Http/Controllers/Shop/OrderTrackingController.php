<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\OrderStatus;
use App\Models\Sale;
use App\Models\Vendor;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    public function index(Request $request)
    {
        $branch = Vendor::onlineStore();
        $sale = null;
        $statusInfo = null;
        $searched = $request->filled('invoice');
        $notFound = false;

        if ($searched) {
            $sale = Sale::withoutGlobalScopes()
                ->where('vendor_id', $branch->id)
                ->where('invoice_no', trim($request->invoice))
                ->with(['saleItems.product', 'saleItems.variant', 'customer'])
                ->first();

            $notFound = !$sale;

            if ($sale) {
                $statusInfo = OrderStatus::where('key', $sale->order_status)->first();
            }
        }

        return view('shop.track.index', compact('branch', 'sale', 'searched', 'notFound', 'statusInfo'));
    }
}
