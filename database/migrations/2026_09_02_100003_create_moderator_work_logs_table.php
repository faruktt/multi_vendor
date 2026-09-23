<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ensure moderator_work_sessions columns are clean DATETIME without ON UPDATE CURRENT_TIMESTAMP
        DB::statement('ALTER TABLE moderator_work_sessions MODIFY COLUMN started_at DATETIME NOT NULL');
        DB::statement('ALTER TABLE moderator_work_sessions MODIFY COLUMN ended_at DATETIME NULL DEFAULT NULL');
        DB::statement('ALTER TABLE moderator_work_sessions MODIFY COLUMN report_submitted_at DATETIME NULL DEFAULT NULL');

        // 2. Create moderator_work_logs table for logging tasks during shift
        Schema::create('moderator_work_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_session_id')->constrained('moderator_work_sessions')->onDelete('cascade');
            $table->foreignId('moderator_id')->constrained('moderators')->onDelete('cascade');
            $table->dateTime('log_time');
            $table->text('activity');
            $table->timestamps();

            $table->index(['work_session_id', 'log_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moderator_work_logs');
    }
};
