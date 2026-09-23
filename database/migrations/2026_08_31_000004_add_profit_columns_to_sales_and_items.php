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
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('reseller_profit', 10, 2)->default(0)->after('due_amount');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('reseller_buy_price', 10, 2)->nullable()->after('unit_price');
            $table->decimal('reseller_profit', 10, 2)->default(0)->after('subtotal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('reseller_profit');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn(['reseller_buy_price', 'reseller_profit']);
        });
    }
};
