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
        $branch = Vendor::onlineStore() ?? Vendor::first();
        $input = trim((string)($request->input('phone') ?? $request->input('number') ?? $request->input('invoice') ?? ''));
        $searched = !empty($input);
        $sales = collect();
        $orderStatuses = OrderStatus::all()->keyBy('key');

        if ($searched) {
            $cleanDigits = preg_replace('/[^\d]/', '', $input);
            $cleanPhone = $cleanDigits;
            if (str_starts_with($cleanPhone, '880')) {
                $cleanPhone = substr($cleanPhone, 2);
            }
            if (strlen($cleanPhone) > 11) {
                $cleanPhone = substr($cleanPhone, -11);
            }

            $query = Sale::withoutGlobalScopes()
                ->where(function ($q) use ($input, $cleanPhone) {
                    if (!empty($cleanPhone) && strlen($cleanPhone) >= 6) {
                        $q->whereHas('customer', function ($cq) use ($cleanPhone) {
                            $cq->where('phone', 'like', "%{$cleanPhone}%");
                        })
                        ->orWhereHas('reseller', function ($rq) use ($cleanPhone) {
                            $rq->where('phone', 'like', "%{$cleanPhone}%");
                        })
                        ->orWhereHas('supplier', function ($sq) use ($cleanPhone) {
                            $sq->where('phone', 'like', "%{$cleanPhone}%");
                        });
                    }

                    // Also allow invoice number or courier tracking code search
                    $q->orWhere('invoice_no', $input)
                      ->orWhere('courier_tracking_code', $input)
                      ->orWhere('courier_consignment_id', $input);
                })
                ->with([
                    'saleItems.product',
                    'saleItems.variant',
                    'customer',
                    'vendor'
                ])
                ->latest();

            $sales = $query->get();
        }

        $notFound = $searched && $sales->isEmpty();

        return view('shop.track.index', compact('branch', 'sales', 'searched', 'notFound', 'input', 'orderStatuses'));
    }
}
