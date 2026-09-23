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
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'approval_status')) {
                $table->string('approval_status', 20)->default('approved')->after('status')->index();
            }
            if (!Schema::hasColumn('products', 'admin_commission_rate')) {
                $table->decimal('admin_commission_rate', 5, 2)->nullable()->default(null)->after('approval_status');
            }
            if (!Schema::hasColumn('products', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('admin_commission_rate');
            }
            if (!Schema::hasColumn('products', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('rejection_reason');
            }
        });

        // Backfill existing products with supplier_id: inherit supplier's commission_percentage
        try {
            DB::statement("UPDATE products p
                JOIN suppliers s ON p.supplier_id = s.id
                SET p.approval_status = 'approved',
                    p.admin_commission_rate = COALESCE(s.commission_percentage, 5.00),
                    p.approved_at = NOW()
                WHERE p.supplier_id IS NOT NULL AND p.admin_commission_rate IS NULL");
        } catch (\Exception $e) {
            // Ignore if error or empty
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('products', 'approval_status')) $cols[] = 'approval_status';
            if (Schema::hasColumn('products', 'admin_commission_rate')) $cols[] = 'admin_commission_rate';
            if (Schema::hasColumn('products', 'rejection_reason')) $cols[] = 'rejection_reason';
            if (Schema::hasColumn('products', 'approved_at')) $cols[] = 'approved_at';
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
