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
        Schema::table('reseller_withdrawals', function (Blueprint $table) {
            $table->string('payment_method')->default('cash')->after('amount');
            $table->text('payment_details')->nullable()->after('payment_method');
            $table->string('status')->default('pending')->after('payment_details');
            $table->text('admin_note')->nullable()->after('note');
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
            $table->timestamp('processed_at')->nullable()->after('processed_by');
        });

        // Mark existing records as approved so historical data remains accurate
        DB::table('reseller_withdrawals')->update([
            'status'       => 'approved',
            'processed_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reseller_withdrawals', function (Blueprint $table) {
            $table->dropForeign(['processed_by']);
            $table->dropColumn([
                'payment_method',
                'payment_details',
                'status',
                'admin_note',
                'processed_by',
                'processed_at',
            ]);
        });
    }
};
