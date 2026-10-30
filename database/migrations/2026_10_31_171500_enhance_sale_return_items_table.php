<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_return_items', function (Blueprint $table) {
            // Drop foreign key on sale_item_id so it can be made nullable
            $table->dropForeign(['sale_item_id']);
        });

        Schema::table('sale_return_items', function (Blueprint $table) {
            $table->unsignedBigInteger('sale_item_id')->nullable()->change();
            $table->foreign('sale_item_id')->references('id')->on('sale_items')->nullOnDelete();

            $table->foreignId('supplier_id')->nullable()->after('product_id')->constrained('suppliers')->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->after('supplier_id')->constrained('product_variants')->nullOnDelete();
            $table->string('variant_name', 255)->nullable()->after('product_variant_id');
            $table->string('status', 30)->default('pending')->after('subtotal')->index();
            $table->timestamp('returned_at')->nullable()->after('status');
            $table->foreignId('returned_by')->nullable()->after('returned_at')->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable()->after('returned_by');
        });
    }

    public function down(): void
    {
        Schema::table('sale_return_items', function (Blueprint $table) {
            $table->dropForeign(['returned_by']);
            $table->dropForeign(['supplier_id']);
            $table->dropForeign(['product_variant_id']);
            $table->dropColumn(['supplier_id', 'product_variant_id', 'variant_name', 'status', 'returned_at', 'returned_by', 'notes']);
        });
    }
};

