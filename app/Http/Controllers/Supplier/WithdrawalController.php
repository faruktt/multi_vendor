<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Models\SupplierWithdrawal;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    /**
     * Display supplier's financial statement and withdrawal history
     */
    public function index()
    {
        $supplier = auth('supplier')->user();

        $totalSales          = $supplier->totalSales();
        $totalCommission     = $supplier->totalAdminCommission();
        $totalEarned         = $supplier->totalNetEarnings();
        $totalWithdrawn      = $supplier->totalWithdrawnAmount();
        $pendingPayout       = $supplier->pendingWithdrawnAmount();
        $availableBalance    = $supplier->availableBalance();
        $withdrawableBalance = $supplier->withdrawableBalance();

        $withdrawals = $supplier->withdrawals()
            ->with('processedBy')
            ->latest()
            ->paginate(15);

        return view('supplier.withdrawals.index', compact(
            'supplier',
            'totalSales',
            'totalCommission',
            'totalEarned',
            'totalWithdrawn',
            'pendingPayout',
            'availableBalance',
            'withdrawableBalance',
            'withdrawals'
        ));
    }

    /**
     * Submit a new withdrawal request to admin
     */
    public function store(Request $request)
    {
        $supplier = auth('supplier')->user();
        $maxWithdrawable = max(0.00, (float) $supplier->withdrawableBalance());

        $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:10',
                'max:' . max(10, $maxWithdrawable),
            ],
            'payment_method'  => 'required|string|in:bkash,nagad,rocket,bank,cash',
            'payment_details' => 'required|string|max:500',
            'note'            => 'nullable|string|max:1000',
        ], [
            'amount.required'          => 'উত্তোলনের পরিমাণ লিখুন।',
            'amount.min'               => 'কমপক্ষে ৳১০ উত্তোলন করতে পারবেন।',
            'amount.max'               => 'উত্তোলনের পরিমাণ বর্তমান উত্তোলনযোগ্য ব্যালেন্স (৳' . number_format($maxWithdrawable, 2) . ') এর বেশি হতে পারবে না।',
            'payment_method.required'  => 'পেমেন্ট মেথড নির্বাচন করুন।',
            'payment_method.in'        => 'সঠিক পেমেন্ট মেথড নির্বাচন করুন।',
            'payment_details.required' => 'টাকা গ্রহণের অ্যাকাউন্ট নম্বর বা ব্যাংকের তথ্য প্রদান করুন।',
        ]);

        if ((float) $request->amount > $maxWithdrawable) {
            return back()->with('error', 'আপনার পর্যাপ্ত উত্তোলনযোগ্য ব্যালেন্স নেই। বর্তমান উত্তোলনযোগ্য ব্যালেন্স: ৳' . number_format($maxWithdrawable, 2));
        }

        SupplierWithdrawal::create([
            'supplier_id'     => $supplier->id,
            'amount'          => round((float) $request->amount, 2),
            'payment_method'  => $request->payment_method,
            'payment_details' => $request->payment_details,
            'status'          => 'pending',
            'note'            => $request->note,
        ]);

        return redirect()->route('supplier.withdrawals.index')
            ->with('success', 'টাকা উত্তোলনের রিকোয়েস্ট (৳' . number_format($request->amount, 2) . ') সফলভাবে সাবমিট করা হয়েছে। এডমিন অনুমোদন করলে আপনার ব্যালেন্স থেকে কর্তন হবে।');
    }
}
