<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Enhance suppliers table
        Schema::table('suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('suppliers', 'vendor_id')) {
                $table->foreignId('vendor_id')->nullable()->change();
            }
            if (!Schema::hasColumn('suppliers', 'company_name')) {
                $table->string('company_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('suppliers', 'password')) {
                $table->string('password')->nullable()->after('email');
            }
            if (!Schema::hasColumn('suppliers', 'status')) {
                $table->string('status')->default('pending')->after('address'); // pending, active, inactive
            }
            if (!Schema::hasColumn('suppliers', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('suppliers', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('approved_at');
            }
            if (!Schema::hasColumn('suppliers', 'logo')) {
                $table->string('logo')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('suppliers', 'bank_info')) {
                $table->text('bank_info')->nullable()->after('logo');
            }
            if (!Schema::hasColumn('suppliers', 'bkash_number')) {
                $table->string('bkash_number')->nullable()->after('bank_info');
            }
            if (!Schema::hasColumn('suppliers', 'remember_token')) {
                $table->rememberToken()->after('bkash_number');
            }
        });

        // 2. Add supplier_id to products
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'supplier_id')) {
                $table->foreignId('supplier_id')->nullable()->after('vendor_id')->constrained('suppliers')->nullOnDelete();
            }
        });

        // 3. Add supplier_id to sale_items
        Schema::table('sale_items', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_items', 'supplier_id')) {
                $table->foreignId('supplier_id')->nullable()->after('sale_id')->constrained('suppliers')->nullOnDelete();
            }
        });

        // 4. Add supplier_id to sales
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'supplier_id')) {
                $table->foreignId('supplier_id')->nullable()->after('reseller_id')->constrained('suppliers')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'supplier_id')) {
                $table->dropForeign(['supplier_id']);
                $table->dropColumn('supplier_id');
            }
        });

        Schema::table('sale_items', function (Blueprint $table) {
            if (Schema::hasColumn('sale_items', 'supplier_id')) {
                $table->dropForeign(['supplier_id']);
                $table->dropColumn('supplier_id');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'supplier_id')) {
                $table->dropForeign(['supplier_id']);
                $table->dropColumn('supplier_id');
            }
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $cols = ['company_name', 'password', 'status', 'approved_at', 'approved_by', 'logo', 'bank_info', 'bkash_number', 'remember_token'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('suppliers', $col)) {
                    if ($col === 'approved_by') {
                        $table->dropForeign(['approved_by']);
                    }
                    $table->dropColumn($col);
                }
            }
        });
    }
};
