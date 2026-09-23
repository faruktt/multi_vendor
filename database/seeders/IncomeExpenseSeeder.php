<?php

namespace Database\Seeders;

use App\Models\IncomeExpense;
use App\Models\IncomeExpenseCategory;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class IncomeExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = Vendor::all();

        $incomeCategories = [
            ['name' => 'Sales Revenue',    'color' => '#22c55e'],
            ['name' => 'Service Fee',      'color' => '#14b8a6'],
            ['name' => 'Commission',       'color' => '#6366f1'],
            ['name' => 'Rental Income',    'color' => '#8b5cf6'],
            ['name' => 'Other Income',     'color' => '#64748b'],
        ];

        $expenseCategories = [
            ['name' => 'Rent',             'color' => '#ef4444'],
            ['name' => 'Salaries',         'color' => '#f97316'],
            ['name' => 'Utilities',        'color' => '#eab308'],
            ['name' => 'Transport',        'color' => '#0ea5e9'],
            ['name' => 'Marketing',        'color' => '#ec4899'],
            ['name' => 'Office Supplies',  'color' => '#a855f7'],
            ['name' => 'Other Expense',    'color' => '#94a3b8'],
        ];

        foreach ($vendors as $vendor) {
            // Create categories
            $incomeCats  = collect($incomeCategories)->map(fn($c) =>
                IncomeExpenseCategory::create(['vendor_id' => $vendor->id, 'type' => 'income', ...$c])
            );
            $expenseCats = collect($expenseCategories)->map(fn($c) =>
                IncomeExpenseCategory::create(['vendor_id' => $vendor->id, 'type' => 'expense', ...$c])
            );

            // Create 40 transactions over last 6 months
            for ($i = 0; $i < 40; $i++) {
                $daysAgo = rand(1, 180);
                $isIncome = rand(0, 2) > 0; // 2/3 chance income

                if ($isIncome) {
                    $cat = $incomeCats->random();
                    $amount = match ($cat->name) {
                        'Sales Revenue' => rand(20000, 150000),
                        'Service Fee'   => rand(5000, 30000),
                        'Commission'    => rand(2000, 15000),
                        'Rental Income' => rand(10000, 40000),
                        default         => rand(1000, 10000),
                    };
                } else {
                    $cat = $expenseCats->random();
                    $amount = match ($cat->name) {
                        'Rent'           => rand(15000, 50000),
                        'Salaries'       => rand(20000, 80000),
                        'Utilities'      => rand(2000, 8000),
                        'Transport'      => rand(1000, 5000),
                        'Marketing'      => rand(3000, 20000),
                        'Office Supplies'=> rand(500, 5000),
                        default          => rand(500, 10000),
                    };
                }

                IncomeExpense::create([
                    'vendor_id'   => $vendor->id,
                    'category_id' => $cat->id,
                    'type'        => $isIncome ? 'income' : 'expense',
                    'amount'      => $amount,
                    'date'        => now()->subDays($daysAgo)->format('Y-m-d'),
                    'note'        => fake()->optional(0.5)->sentence(4),
                    'reference'   => fake()->optional(0.3)->bothify('REF-####??'),
                ]);
            }
        }
    }
}
