<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocked_ips', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->unique();
            $table->string('reason', 255)->nullable();
            $table->string('location', 255)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('attempted_email', 150)->nullable();
            $table->unsignedInteger('failed_attempts')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('blocked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('blocked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('login_attempt_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->index();
            $table->string('email', 150)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('location', 255)->nullable();
            $table->boolean('is_success')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_attempt_logs');
        Schema::dropIfExists('blocked_ips');
    }
};

