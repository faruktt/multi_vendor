<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->decimal('delivery_charge_inside_dhaka', 10, 2)->default(60)->after('delivery_charge');
            $table->decimal('delivery_charge_outside_dhaka', 10, 2)->default(100)->after('delivery_charge_inside_dhaka');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['delivery_charge_inside_dhaka', 'delivery_charge_outside_dhaka']);
        });
    }
};
