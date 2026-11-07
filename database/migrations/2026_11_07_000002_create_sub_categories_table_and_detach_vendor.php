<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sub_categories')) {
            Schema::create('sub_categories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('category_id');
                $table->string('name');
                $table->string('slug')->nullable();
                $table->string('image')->nullable();
                $table->string('status')->default('active');
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            });
        }

        // Drop foreign keys if present
        try {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropForeign('categories_parent_id_foreign');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropForeign('categories_vendor_id_foreign');
            });
        } catch (\Exception $e) {}

        if (Schema::hasColumn('categories', 'parent_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('parent_id');
            });
        }

        if (Schema::hasColumn('categories', 'vendor_id')) {
            try {
                DB::statement('ALTER TABLE categories MODIFY vendor_id BIGINT UNSIGNED NULL');
            } catch (\Exception $e) {}
        }
    }

    public function down(): void
    {
        // No-op rollback to protect data
    }
};

