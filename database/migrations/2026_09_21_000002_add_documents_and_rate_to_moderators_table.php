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
        Schema::table('moderators', function (Blueprint $table) {
            $table->decimal('rate_per_minute', 10, 2)->default(0.00)->after('status');
            $table->string('nid_front')->nullable()->after('rate_per_minute');
            $table->string('nid_back')->nullable()->after('nid_front');
            $table->string('guardian_nid_front')->nullable()->after('nid_back');
            $table->string('guardian_nid_back')->nullable()->after('guardian_nid_front');
        });

        Schema::table('moderator_work_sessions', function (Blueprint $table) {
            $table->decimal('rate_per_minute', 10, 2)->nullable()->after('duration_seconds');
            $table->decimal('earned_amount', 10, 2)->nullable()->after('rate_per_minute');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('moderators', function (Blueprint $table) {
            $table->dropColumn([
                'rate_per_minute',
                'nid_front',
                'nid_back',
                'guardian_nid_front',
                'guardian_nid_back',
            ]);
        });

        Schema::table('moderator_work_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'rate_per_minute',
                'earned_amount',
            ]);
        });
    }
};
