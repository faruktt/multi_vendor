<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Support\BangladeshLocations;
use Illuminate\Http\Request;

class ShippingChargeController extends Controller
{
    public function index()
    {
        $vendor = Vendor::onlineStore() ?? Vendor::where('is_warehouse', true)->first() ?? Vendor::first();
        abort_unless($vendor, 404, 'No store vendor found');

        $allDistricts = BangladeshLocations::districts();
        
        $subDhakaDistricts = $vendor->sub_dhaka_districts;
        if (is_string($subDhakaDistricts)) {
            $subDhakaDistricts = json_decode($subDhakaDistricts, true) ?: [];
        }
        if (empty($subDhakaDistricts) || !is_array($subDhakaDistricts)) {
            $subDhakaDistricts = BangladeshLocations::defaultSubDhakaDistricts();
        }

        $subDhakaThanas = $vendor->sub_dhaka_thanas;
        if (is_string($subDhakaThanas)) {
            $subDhakaThanas = json_decode($subDhakaThanas, true) ?: [];
        }
        if (empty($subDhakaThanas) || !is_array($subDhakaThanas)) {
            $subDhakaThanas = BangladeshLocations::defaultSubDhakaThanas();
        }

        $allDistrictsWithThanas = BangladeshLocations::all();

        return view('admin.shipping.index', compact(
            'vendor',
            'allDistricts',
            'subDhakaDistricts',
            'subDhakaThanas',
            'allDistrictsWithThanas'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'delivery_charge_inside_dhaka'  => 'required|numeric|min:0',
            'delivery_charge_sub_dhaka'     => 'required|numeric|min:0',
            'delivery_charge_outside_dhaka' => 'required|numeric|min:0',
            'sub_dhaka_thanas'              => 'nullable',
        ]);

        $subDhakaThanasInput = $request->input('sub_dhaka_thanas');
        if (is_string($subDhakaThanasInput)) {
            $subDhakaThanasInput = json_decode($subDhakaThanasInput, true) ?: [];
        }
        if (!is_array($subDhakaThanasInput)) {
            $subDhakaThanasInput = [];
        }

        // Clean up empty districts or invalid entries
        $cleaned = [];
        foreach ($subDhakaThanasInput as $dist => $thanas) {
            if (is_array($thanas)) {
                $filtered = array_values(array_unique(array_filter($thanas)));
                if (!empty($filtered)) {
                    $cleaned[$dist] = $filtered;
                }
            }
        }

        // Also derive sub_dhaka_districts for backwards compatibility
        $subDhakaDistricts = array_keys($cleaned);

        // Update online store and warehouse/vendors
        Vendor::query()->update([
            'delivery_charge_inside_dhaka'  => $request->delivery_charge_inside_dhaka,
            'delivery_charge_sub_dhaka'     => $request->delivery_charge_sub_dhaka,
            'delivery_charge_outside_dhaka' => $request->delivery_charge_outside_dhaka,
            'sub_dhaka_districts'           => json_encode($subDhakaDistricts),
            'sub_dhaka_thanas'              => json_encode($cleaned),
        ]);

        return back()->with('success', 'Shipping charges and Sub-Dhaka thanas updated successfully!');
    }
}
