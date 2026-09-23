<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add commission_percentage to suppliers table
        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'commission_percentage')) {
                $table->decimal('commission_percentage', 5, 2)->default(5.00)->after('status');
            }
        });

        // 2. Add commission fields to sale_items table
        Schema::table('sale_items', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_items', 'admin_commission_rate')) {
                $table->decimal('admin_commission_rate', 5, 2)->default(0.00)->after('supplier_id');
            }
            if (!Schema::hasColumn('sale_items', 'admin_commission_amount')) {
                $table->decimal('admin_commission_amount', 10, 2)->default(0.00)->after('admin_commission_rate');
            }
            if (!Schema::hasColumn('sale_items', 'supplier_earning')) {
                $table->decimal('supplier_earning', 10, 2)->default(0.00)->after('admin_commission_amount');
            }
        });

        // 3. Backfill any existing sale_items that have supplier_id
        try {
            DB::statement("UPDATE sale_items si 
                JOIN suppliers s ON si.supplier_id = s.id 
                SET si.admin_commission_rate = COALESCE(s.commission_percentage, 5.00),
                    si.admin_commission_amount = ROUND(si.subtotal * (COALESCE(s.commission_percentage, 5.00) / 100), 2),
                    si.supplier_earning = si.subtotal - ROUND(si.subtotal * (COALESCE(s.commission_percentage, 5.00) / 100), 2)
                WHERE si.supplier_id IS NOT NULL");
        } catch (\Exception $e) {
            // Ignore if tables are empty
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('suppliers', 'commission_percentage')) {
                $table->dropColumn('commission_percentage');
            }
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('sale_items', 'admin_commission_rate')) $cols[] = 'admin_commission_rate';
            if (Schema::hasColumn('sale_items', 'admin_commission_amount')) $cols[] = 'admin_commission_amount';
            if (Schema::hasColumn('sale_items', 'supplier_earning')) $cols[] = 'supplier_earning';
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
