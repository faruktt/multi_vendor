<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\IncomeExpense;
use App\Models\IncomeExpenseCategory;
use App\Models\Vendor;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function index(Request $request, Vendor $branch)
    {
        $query = IncomeExpense::withoutGlobalScopes()
            ->with('category')
            ->where('vendor_id', $branch->id)
            ->latest('date')->latest('id');

        if ($request->filled('type'))        $query->where('type', $request->type);
        if ($request->filled('category_id')) $query->where('category_id', $request->category_id);
        if ($request->filled('month')) {
            [$year, $month] = explode('-', $request->month);
            $query->whereYear('date', $year)->whereMonth('date', $month);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('note', 'like', '%' . $request->search . '%')
                  ->orWhere('reference', 'like', '%' . $request->search . '%');
            });
        }

        $transactions = $query->paginate(20)->withQueryString();

        $categories = IncomeExpenseCategory::withoutGlobalScopes()
            ->where('vendor_id', $branch->id)
            ->withCount('transactions')
            ->withSum('transactions', 'amount')
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $baseQ = fn() => IncomeExpense::withoutGlobalScopes()->where('vendor_id', $branch->id);

        $totalIncome      = $baseQ()->where('type', 'income')->sum('amount');
        $totalExpense     = $baseQ()->where('type', 'expense')->sum('amount');
        $thisMonthIncome  = $baseQ()->where('type', 'income')->whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('amount');
        $thisMonthExpense = $baseQ()->where('type', 'expense')->whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('amount');
        $netBalance       = $totalIncome - $totalExpense;
        $thisMonthNet     = $thisMonthIncome - $thisMonthExpense;

        $lastMonthIncome  = $baseQ()->where('type', 'income')->whereMonth('date', now()->subMonth()->month)->whereYear('date', now()->subMonth()->year)->sum('amount');
        $lastMonthExpense = $baseQ()->where('type', 'expense')->whereMonth('date', now()->subMonth()->month)->whereYear('date', now()->subMonth()->year)->sum('amount');

        return view('finance.index', compact(
            'branch', 'transactions', 'categories',
            'totalIncome', 'totalExpense', 'netBalance',
            'thisMonthIncome', 'thisMonthExpense', 'thisMonthNet',
            'lastMonthIncome', 'lastMonthExpense'
        ));
    }

    public function storeCategory(Request $request, Vendor $branch)
    {
        $request->validate([
            'name'  => 'required|string|max:100',
            'type'  => 'required|in:income,expense',
            'color' => 'required|string|max:7',
        ]);
        IncomeExpenseCategory::create([
            'vendor_id' => $branch->id,
            ...$request->only('name', 'type', 'color'),
        ]);
        return back()->with('success', 'Category added.');
    }

    public function destroyCategory(Vendor $branch, IncomeExpenseCategory $category)
    {
        $category->delete();
        return back()->with('success', 'Category deleted.');
    }

    public function store(Request $request, Vendor $branch)
    {
        $request->validate([
            'type'        => 'required|in:income,expense',
            'category_id' => 'nullable|exists:income_expense_categories,id',
            'amount'      => 'required|numeric|min:0.01',
            'date'        => 'required|date',
            'note'        => 'nullable|string|max:500',
            'reference'   => 'nullable|string|max:255',
        ]);

        IncomeExpense::create([
            'vendor_id'  => $branch->id,
            'created_by' => auth()->id(),
            ...$request->only('type', 'category_id', 'amount', 'date', 'note', 'reference'),
        ]);

        return back()->with('success', 'Transaction added.');
    }

    public function destroy(Vendor $branch, IncomeExpense $incomeExpense)
    {
        $incomeExpense->delete();
        return back()->with('success', 'Transaction deleted.');
    }
}
