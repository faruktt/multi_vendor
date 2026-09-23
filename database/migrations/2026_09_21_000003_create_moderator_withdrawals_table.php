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
        Schema::create('moderator_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moderator_id')->constrained('moderators')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method', 50)->default('bkash'); // bkash, nagad, rocket, bank
            $table->text('payment_details');
            $table->string('status', 30)->default('pending'); // pending, approved, rejected
            $table->text('note')->nullable();
            $table->text('admin_note')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['moderator_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('moderator_withdrawals');
    }
};
