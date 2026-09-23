<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('channel', 20)->default('pos')->after('order_status');
            $table->decimal('delivery_charge', 10, 2)->default(0)->after('channel');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->decimal('delivery_charge', 10, 2)->default(60)->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['channel', 'delivery_charge']);
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('delivery_charge');
        });
    }
};
