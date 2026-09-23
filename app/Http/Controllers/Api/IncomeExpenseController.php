<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IncomeExpense;
use App\Models\IncomeExpenseCategory;
use Illuminate\Http\Request;

class IncomeExpenseController extends Controller
{
    /* ── Categories ──────────────────────────── */

    public function categories()
    {
        return response()->json(
            IncomeExpenseCategory::withCount('transactions')
                ->withSum('transactions', 'amount')
                ->orderBy('type')
                ->orderBy('name')
                ->get()
        );
    }

    public function storeCategory(Request $request)
    {
        $vendorId = $request->user()->vendor_id ?? $request->integer('vendor_id');

        if (!$vendorId) {
            return response()->json(['message' => 'Please select a vendor.'], 422);
        }

        $v = $request->validate([
            'name'      => 'required|string|max:100',
            'type'      => 'required|in:income,expense',
            'color'     => 'required|string|max:7',
            'vendor_id' => 'nullable|integer',
        ]);

        $cat = IncomeExpenseCategory::create([
            'vendor_id' => $vendorId,
            'name'      => $v['name'],
            'type'      => $v['type'],
            'color'     => $v['color'],
        ]);
        return response()->json($cat->loadCount('transactions'), 201);
    }

    public function updateCategory(Request $request, IncomeExpenseCategory $category)
    {
        $v = $request->validate([
            'name'  => 'required|string|max:100',
            'color' => 'required|string|max:7',
        ]);
        $category->update($v);
        return response()->json($category->loadCount('transactions'));
    }

    public function destroyCategory(IncomeExpenseCategory $category)
    {
        $category->delete();
        return response()->json(['message' => 'Category deleted']);
    }

    /* ── Transactions ────────────────────────── */

    public function index(Request $request)
    {
        $q = IncomeExpense::with('category')->latest('date')->latest('id');

        if ($request->type)        $q->where('type', $request->type);
        if ($request->category_id) $q->where('category_id', $request->category_id);
        if ($request->month) {
            [$year, $month] = explode('-', $request->month);
            $q->whereYear('date', $year)->whereMonth('date', $month);
        }

        return response()->json($q->limit(200)->get());
    }

    public function store(Request $request)
    {
        $vendorId = $request->user()->vendor_id ?? $request->integer('vendor_id');

        if (!$vendorId) {
            return response()->json(['message' => 'Please select a vendor.'], 422);
        }

        $v = $request->validate([
            'type'        => 'required|in:income,expense',
            'category_id' => 'nullable|exists:income_expense_categories,id',
            'amount'      => 'required|numeric|min:0.01',
            'date'        => 'required|date',
            'note'        => 'nullable|string|max:500',
            'reference'   => 'nullable|string|max:255',
            'vendor_id'   => 'nullable|integer',
        ]);

        $tx = IncomeExpense::create([
            'vendor_id'   => $vendorId,
            'created_by'  => $request->user()->id,
            'type'        => $v['type'],
            'category_id' => $v['category_id'] ?? null,
            'amount'      => $v['amount'],
            'date'        => $v['date'],
            'note'        => $v['note'] ?? null,
            'reference'   => $v['reference'] ?? null,
        ]);

        return response()->json($tx->load('category'), 201);
    }

    public function update(Request $request, IncomeExpense $incomeExpense)
    {
        $v = $request->validate([
            'type'        => 'required|in:income,expense',
            'category_id' => 'nullable|exists:income_expense_categories,id',
            'amount'      => 'required|numeric|min:0.01',
            'date'        => 'required|date',
            'note'        => 'nullable|string|max:500',
            'reference'   => 'nullable|string|max:255',
        ]);

        $incomeExpense->update($v);
        return response()->json($incomeExpense->fresh()->load('category'));
    }

    public function destroy(IncomeExpense $incomeExpense)
    {
        $incomeExpense->delete();
        return response()->json(['message' => 'Deleted']);
    }

    /* ── Report ─────────────────────────────── */

    public function report()
    {
        $totalIncome  = IncomeExpense::where('type', 'income')->sum('amount');
        $totalExpense = IncomeExpense::where('type', 'expense')->sum('amount');

        $thisMonthIncome  = IncomeExpense::where('type', 'income')
            ->whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('amount');
        $thisMonthExpense = IncomeExpense::where('type', 'expense')
            ->whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('amount');

        // Last 6 months
        $monthly = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $monthly[] = [
                'month'   => $d->format('M Y'),
                'income'  => round(IncomeExpense::where('type', 'income')->whereMonth('date', $d->month)->whereYear('date', $d->year)->sum('amount'), 2),
                'expense' => round(IncomeExpense::where('type', 'expense')->whereMonth('date', $d->month)->whereYear('date', $d->year)->sum('amount'), 2),
            ];
        }

        // By category
        $incomeByCategory = IncomeExpenseCategory::where('type', 'income')
            ->withSum('transactions', 'amount')
            ->get()
            ->map(fn($c) => ['name' => $c->name, 'amount' => round($c->transactions_sum_amount ?? 0, 2), 'color' => $c->color])
            ->filter(fn($c) => $c['amount'] > 0)->values();

        $expenseByCategory = IncomeExpenseCategory::where('type', 'expense')
            ->withSum('transactions', 'amount')
            ->get()
            ->map(fn($c) => ['name' => $c->name, 'amount' => round($c->transactions_sum_amount ?? 0, 2), 'color' => $c->color])
            ->filter(fn($c) => $c['amount'] > 0)->values();

        return response()->json([
            'summary' => [
                'total_income'        => round($totalIncome, 2),
                'total_expense'       => round($totalExpense, 2),
                'net_balance'         => round($totalIncome - $totalExpense, 2),
                'this_month_income'   => round($thisMonthIncome, 2),
                'this_month_expense'  => round($thisMonthExpense, 2),
            ],
            'monthly'            => $monthly,
            'income_by_category' => $incomeByCategory,
            'expense_by_category'=> $expenseByCategory,
        ]);
    }
}
