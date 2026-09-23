<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The payments.method enum only allowed ['cash','card','check','bank_transfer','other'],
     * but every payment form in the app (collect payment, checkout, returns) actually offers
     * ['cash','bkash','nagad','card','bank','other'] — selecting bKash/Nagad/Bank crashed with
     * a truncation error since those values were never in the enum.
     */
    public function up(): void
    {
        // Widen first so old values remain valid while we remap them, then narrow to the final set.
        DB::statement("ALTER TABLE payments MODIFY method ENUM('cash','card','check','bank_transfer','bkash','nagad','bank','other') NOT NULL DEFAULT 'cash'");

        DB::table('payments')->where('method', 'bank_transfer')->update(['method' => 'bank']);
        DB::table('payments')->where('method', 'check')->update(['method' => 'other']);

        DB::statement("ALTER TABLE payments MODIFY method ENUM('cash','bkash','nagad','card','bank','other') NOT NULL DEFAULT 'cash'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE payments MODIFY method ENUM('cash','card','check','bank_transfer','other') NOT NULL DEFAULT 'cash'");
        DB::table('payments')->where('method', 'bank')->update(['method' => 'bank_transfer']);
    }
};
