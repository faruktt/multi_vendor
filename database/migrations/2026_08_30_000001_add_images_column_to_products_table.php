<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Add new JSON images column after the existing image column
            $table->json('images')->nullable()->after('image');
        });

        // Migrate existing single image values into the new images array
        DB::table('products')
            ->whereNotNull('image')
            ->where('image', '!=', '')
            ->get(['id', 'image'])
            ->each(function ($row) {
                // Extract just the filename from any stored path
                $filename = basename($row->image);
                DB::table('products')
                    ->where('id', $row->id)
                    ->update(['images' => json_encode([$filename])]);
            });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('images');
        });
    }
};
