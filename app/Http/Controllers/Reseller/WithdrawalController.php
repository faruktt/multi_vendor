<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\ResellerWithdrawal;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function index()
    {
        $reseller = auth('reseller')->user();

        $withdrawals = $reseller->withdrawals()
            ->with(['processedBy'])
            ->latest()
            ->paginate(15);

        $totalProfit        = $reseller->total_profit;
        $totalWithdrawn     = $reseller->total_withdrawn;
        $availableBalance   = $reseller->available_balance;
        $pendingWithdrawals = $reseller->pending_withdrawals;
        $withdrawableBalance= $reseller->withdrawable_balance;

        return view('reseller.withdrawals.index', compact(
            'reseller',
            'withdrawals',
            'totalProfit',
            'totalWithdrawn',
            'availableBalance',
            'pendingWithdrawals',
            'withdrawableBalance'
        ));
    }

    public function store(Request $request)
    {
        $reseller = auth('reseller')->user();
        $maxWithdrawable = max(0, (float) $reseller->withdrawable_balance);

        if ($maxWithdrawable < 10) {
            if ($reseller->available_balance < 0) {
                return back()->with('error', 'আপনার বর্তমান ব্যালেন্স ঋণাত্মক (-৳' . number_format(abs($reseller->available_balance), 2) . ')। রিটার্ন অর্ডারের ডেলিভারি চার্জ সমন্বয় না হওয়া পর্যন্ত টাকা উত্তোলন করা যাবে না।');
            }
            return back()->with('error', 'টাকা উত্তোলন করার জন্য ন্যূনতম ১০ টাকা ব্যালেন্স প্রয়োজন (আপনার উত্তোলনযোগ্য ব্যালেন্স: ৳' . number_format($maxWithdrawable, 2) . ')।');
        }

        $request->validate([
            'amount'          => [
                'required',
                'numeric',
                'min:10',
                'max:' . $maxWithdrawable,
            ],
            'payment_method'  => 'required|string|in:bkash,nagad,rocket,bank,other',
            'payment_details' => 'required|string|max:255',
            'note'            => 'nullable|string|max:500',
        ], [
            'amount.min'               => 'Minimum withdrawal amount is ৳10.',
            'amount.max'               => 'Withdrawal amount cannot exceed your withdrawable balance of ৳' . number_format($maxWithdrawable, 2) . '.',
            'payment_method.in'        => 'Please select a valid payment method.',
            'payment_details.required' => 'Please provide your account / phone number or payment details.',
        ]);

        if ($request->amount > $maxWithdrawable) {
            return back()->with('error', 'Requested amount exceeds your withdrawable balance (৳' . number_format($maxWithdrawable, 2) . ').');
        }

        ResellerWithdrawal::create([
            'reseller_id'     => $reseller->id,
            'amount'          => round((float) $request->amount, 2),
            'payment_method'  => $request->payment_method,
            'payment_details' => $request->payment_details,
            'note'            => $request->note,
            'status'          => 'pending',
            'created_by'      => null,
        ]);

        return redirect()->route('reseller.withdrawals.index')
            ->with('success', 'Withdrawal request of ৳' . number_format($request->amount, 2) . ' submitted successfully. Admin will review and approve it.');
    }
}
