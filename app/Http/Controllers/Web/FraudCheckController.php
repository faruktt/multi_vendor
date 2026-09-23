<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FraudCheckController extends Controller
{
    public function index(Request $request, Vendor $branch)
    {
        $phone     = trim($request->input('phone', ''));
        $apiResult = null;
        $apiError  = null;
        $customers = collect();

        if ($phone !== '') {
            // Find matching customers in this branch only
            $customers = Customer::where('vendor_id', $branch->id)
                ->withCount('sales')
                ->withSum('sales', 'total')
                ->where('phone', $phone)
                ->get();

            // Call fraudchecker.link API
            try {
                $apiKey = env('FRAUD_CHECKER_API_KEY', '26dfafdb4ba3692d650917d71b420fbd');

                $response = Http::withoutVerifying()
                    ->withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                    ->asForm()
                    ->post('https://fraudchecker.link/api/v1/qc/', ['phone' => $phone]);

                if ($response->successful()) {
                    $apiResult = $response->json();
                } else {
                    $apiError = 'API returned status ' . $response->status();
                }
            } catch (\Exception $e) {
                $apiError = 'Connection failed: ' . $e->getMessage();
            }
        }

        return view('fraud-check.index', compact('branch', 'phone', 'apiResult', 'apiError', 'customers'));
    }
}
