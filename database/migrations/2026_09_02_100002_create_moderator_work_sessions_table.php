<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moderator_work_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moderator_id')->constrained('moderators')->onDelete('cascade');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->string('status', 30)->default('in_progress'); // in_progress, completed
            $table->string('tasks_summary', 255)->nullable();
            $table->text('work_report')->nullable();
            $table->timestamp('report_submitted_at')->nullable();
            $table->timestamps();

            $table->index(['moderator_id', 'started_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moderator_work_sessions');
    }
};
