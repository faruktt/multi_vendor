<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\ModeratorWithdrawal;
use Illuminate\Http\Request;

class ModeratorAccountController extends Controller
{
    /**
     * Moderator financial summary & account dashboard
     */
    public function index(Request $request)
    {
        $moderator = auth('moderator')->user();

        $totalSeconds   = $moderator->totalWorkSeconds();
        $totalMinutes   = $moderator->totalWorkMinutes();
        $ratePerMinute  = (float) ($moderator->rate_per_minute ?? 0);
        $totalEarned    = $moderator->totalEarnedAmount();
        $totalWithdrawn = $moderator->totalWithdrawnAmount();
        $pendingPayout  = $moderator->pendingWithdrawnAmount();
        $availableBalance = $moderator->availableBalance();

        // Paginated completed sessions
        $sessions = $moderator->workSessions()
            ->where('status', 'completed')
            ->latest('started_at')
            ->paginate(10, ['*'], 'shifts_page')
            ->withQueryString();

        // Paginated withdrawal requests
        $withdrawals = $moderator->withdrawals()
            ->latest()
            ->paginate(10, ['*'], 'withdrawals_page')
            ->withQueryString();

        return view('moderator.account.index', compact(
            'moderator',
            'totalSeconds',
            'totalMinutes',
            'ratePerMinute',
            'totalEarned',
            'totalWithdrawn',
            'pendingPayout',
            'availableBalance',
            'sessions',
            'withdrawals'
        ));
    }

    /**
     * Submit a salary withdrawal request
     */
    public function requestWithdrawal(Request $request)
    {
        $moderator = auth('moderator')->user();
        $availableBalance = $moderator->availableBalance();

        $validated = $request->validate([
            'amount'          => 'required|numeric|min:10|max:' . max(10, $availableBalance),
            'payment_method'  => 'required|string|in:bkash,nagad,rocket,bank',
            'payment_details' => 'required|string|max:500',
            'note'            => 'nullable|string|max:1000',
        ], [
            'amount.max' => 'উত্তোলনের পরিমাণ বর্তমান ব্যালেন্স (৳' . number_format($availableBalance, 2) . ') এর বেশি হতে পারবে না।',
            'amount.min' => 'কমপক্ষে ৳১০ উত্তোলন করতে পারবেন।',
            'payment_details.required' => 'পেমেন্ট গ্রহণের অ্যাকাউন্ট নম্বর বা ব্যাংক তথ্য দিন।',
        ]);

        if ($validated['amount'] > $availableBalance) {
            return back()->with('error', 'আপনার পর্যাপ্ত ব্যালেন্স নেই। বর্তমান ব্যালেন্স: ৳' . number_format($availableBalance, 2));
        }

        ModeratorWithdrawal::create([
            'moderator_id'    => $moderator->id,
            'amount'          => $validated['amount'],
            'payment_method'  => $validated['payment_method'],
            'payment_details' => $validated['payment_details'],
            'status'          => 'pending',
            'note'            => $validated['note'] ?? null,
        ]);

        return back()->with('success', 'বেতন উত্তোলনের রিকোয়েস্ট সফলভাবে জমা হয়েছে! এডমিনের অনুমোদনের পর টাকা পাঠানো হবে।');
    }
}
