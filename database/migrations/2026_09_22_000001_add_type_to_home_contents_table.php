<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('home_contents', function (Blueprint $table) {
            $table->string('type', 30)->default('reseller')->after('title');
        });

        // Update all existing records to 'reseller'
        DB::table('home_contents')->whereNull('type')->orWhere('type', '')->update(['type' => 'reseller']);
    }

    public function down(): void
    {
        Schema::table('home_contents', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
