<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE categories MODIFY slug VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE products MODIFY slug VARCHAR(255) NOT NULL');

        Schema::table('categories', function (Blueprint $table) {
            $table->unique(['vendor_id', 'slug']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unique(['vendor_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['vendor_id', 'slug']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['vendor_id', 'slug']);
        });

        DB::statement('ALTER TABLE categories MODIFY slug VARCHAR(255) NULL');
        DB::statement('ALTER TABLE products MODIFY slug VARCHAR(255) NULL');
    }
};
