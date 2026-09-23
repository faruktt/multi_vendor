<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'courier_name')) {
                $table->string('courier_name', 50)->nullable()->after('delivery_charge');
            }
            if (!Schema::hasColumn('sales', 'courier_tracking_code')) {
                $table->string('courier_tracking_code', 100)->nullable()->after('courier_name');
            }
            if (!Schema::hasColumn('sales', 'courier_consignment_id')) {
                $table->string('courier_consignment_id', 100)->nullable()->after('courier_tracking_code');
            }
            if (!Schema::hasColumn('sales', 'courier_status')) {
                $table->string('courier_status', 50)->nullable()->after('courier_consignment_id');
            }
            if (!Schema::hasColumn('sales', 'courier_response')) {
                $table->text('courier_response')->nullable()->after('courier_status');
            }
            if (!Schema::hasColumn('sales', 'courier_sent_at')) {
                $table->timestamp('courier_sent_at')->nullable()->after('courier_response');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'courier_name',
                'courier_tracking_code',
                'courier_consignment_id',
                'courier_status',
                'courier_response',
                'courier_sent_at',
            ]);
        });
    }
};
