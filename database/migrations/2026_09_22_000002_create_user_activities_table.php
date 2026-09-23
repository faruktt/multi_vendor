<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 100)->nullable()->index();
            $table->string('ip_address', 45)->nullable()->index();
            $table->string('method', 10)->default('GET');
            $table->string('path', 255)->index();
            $table->text('url');
            $table->string('page_name', 150)->nullable()->index();
            $table->string('user_type', 50)->default('guest')->index(); // guest, customer, reseller, supplier, admin
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('device', 50)->nullable()->index(); // Mobile, Desktop, Tablet
            $table->string('platform', 50)->nullable(); // Windows, Android, iOS, Mac, Linux
            $table->string('browser', 50)->nullable(); // Chrome, Safari, Firefox, Edge, etc.
            $table->text('referer')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['path', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};
