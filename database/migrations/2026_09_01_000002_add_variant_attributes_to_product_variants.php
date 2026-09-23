<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add color_id, size_id, and stored label columns to product_variants
        Schema::table('product_variants', function (Blueprint $table) {
            $table->foreignId('color_id')->nullable()->after('attributes')->constrained('colors')->onDelete('set null');
            $table->foreignId('size_id')->nullable()->after('color_id')->constrained('sizes')->onDelete('set null');
            // Store label snapshots so even if color/size is deleted later, historical data is safe
            $table->string('color_label')->nullable()->after('size_id');
            $table->string('size_label')->nullable()->after('color_label');
        });

        // Add short_description to products table
        Schema::table('products', function (Blueprint $table) {
            $table->text('short_description')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropForeign(['color_id']);
            $table->dropForeign(['size_id']);
            $table->dropColumn(['color_id', 'size_id', 'color_label', 'size_label']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('short_description');
        });
    }
};
