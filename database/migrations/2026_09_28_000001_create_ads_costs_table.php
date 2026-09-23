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
        if (!Schema::hasTable('ads_costs')) {
            Schema::create('ads_costs', function (Blueprint $table) {
                $table->id();
                $table->string('platform', 50)->index(); // facebook, google, tiktok, instagram, youtube, snapchat, other
                $table->string('campaign_name')->index();
                $table->string('ad_account')->nullable();
                $table->date('cost_date')->index();
                $table->decimal('amount', 12, 2); // Spend in BDT
                $table->string('currency', 10)->default('BDT');
                $table->decimal('amount_usd', 10, 2)->nullable();
                $table->unsignedBigInteger('impressions')->default(0)->nullable();
                $table->unsignedBigInteger('clicks')->default(0)->nullable();
                $table->unsignedInteger('conversions')->default(0)->nullable();
                $table->string('target_url', 500)->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads_costs');
    }
};
