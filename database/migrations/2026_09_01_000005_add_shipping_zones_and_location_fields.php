<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            if (!Schema::hasColumn('vendors', 'delivery_charge_sub_dhaka')) {
                $table->decimal('delivery_charge_sub_dhaka', 10, 2)->default(100.00)->after('delivery_charge_inside_dhaka');
            }
            if (!Schema::hasColumn('vendors', 'sub_dhaka_districts')) {
                $table->text('sub_dhaka_districts')->nullable()->after('delivery_charge_outside_dhaka');
            }
        });

        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'district')) {
                $table->string('district', 100)->nullable()->after('channel');
            }
            if (!Schema::hasColumn('sales', 'thana')) {
                $table->string('thana', 100)->nullable()->after('district');
            }
            if (!Schema::hasColumn('sales', 'delivery_zone')) {
                $table->string('delivery_zone', 50)->nullable()->after('thana'); // 'inside', 'sub_dhaka', 'outside'
            }
        });

        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'district')) {
                $table->string('district', 100)->nullable()->after('address');
            }
            if (!Schema::hasColumn('customers', 'thana')) {
                $table->string('thana', 100)->nullable()->after('district');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['delivery_charge_sub_dhaka', 'sub_dhaka_districts']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['district', 'thana', 'delivery_zone']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['district', 'thana']);
        });
    }
};
