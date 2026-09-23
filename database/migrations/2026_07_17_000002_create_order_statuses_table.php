<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->string('color');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_protected')->default(false);
            $table->timestamps();
        });

        $now = now();
        $defaults = [
            ['key' => 'pending',          'label' => 'Pending',          'color' => 'slate',   'is_protected' => true],
            ['key' => 'processing',       'label' => 'Processing',       'color' => 'blue',    'is_protected' => false],
            ['key' => 'sent_to_courier',  'label' => 'Sent to Courier',  'color' => 'indigo',  'is_protected' => false],
            ['key' => 'out_for_delivery', 'label' => 'Out for Delivery', 'color' => 'purple',  'is_protected' => false],
            ['key' => 'delivered',        'label' => 'Delivered',        'color' => 'teal',    'is_protected' => false],
            ['key' => 'completed',        'label' => 'Completed',        'color' => 'emerald', 'is_protected' => true],
            ['key' => 'cancelled',        'label' => 'Cancelled',        'color' => 'red',     'is_protected' => true],
        ];

        foreach ($defaults as $index => $status) {
            DB::table('order_statuses')->insert(array_merge($status, [
                'sort_order' => $index,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_statuses');
    }
};
