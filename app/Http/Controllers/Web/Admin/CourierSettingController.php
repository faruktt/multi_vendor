<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourierSetting;
use App\Services\Courier\CourierManager;
use Illuminate\Http\Request;
use Exception;

class CourierSettingController extends Controller
{
    /**
     * Display courier settings management page
     */
    public function index()
    {
        $couriers = CourierSetting::orderBy('id')->get();
        return view('admin.courier.index', compact('couriers'));
    }

    /**
     * Update courier credentials and configuration
     */
    public function update(Request $request, CourierSetting $courier)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'api_key'       => 'nullable|string',
            'secret_key'    => 'nullable|string',
            'client_id'     => 'nullable|string',
            'store_id'      => 'nullable|string',
            'username'      => 'nullable|string',
            'password'      => 'nullable|string',
            'base_url'      => 'nullable|string',
            'delivery_type' => 'nullable|string|max:50',
        ]);

        $courier->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$courier->name} সেটিংস সফলভাবে আপডেট হয়েছে!",
                'courier' => $courier,
            ]);
        }

        return back()->with('success', "{$courier->name} সেটিংস সফলভাবে সেভ করা হয়েছে!");
    }

    /**
     * Toggle active / inactive status of a courier
     */
    public function toggle(Request $request, CourierSetting $courier)
    {
        $courier->is_active = !$courier->is_active;
        $courier->save();

        $statusText = $courier->is_active ? 'Active' : 'Inactive';

        if ($request->wantsJson()) {
            return response()->json([
                'success'   => true,
                'is_active' => $courier->is_active,
                'message'   => "{$courier->name} এখন {$statusText}!",
            ]);
        }

        return back()->with('success', "{$courier->name} এখন {$statusText}!");
    }

    /**
     * Test connection to the courier API
     */
    public function test(Request $request, CourierSetting $courier)
    {
        try {
            $service = CourierManager::resolve($courier);
            $result  = $service->testConnection();

            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'টেস্ট ব্যর্থ হয়েছে: ' . $e->getMessage(),
            ], 422);
        }
    }
}
