<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courier_settings', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique(); // steadfast, pathao, redx
            $table->string('name', 100);
            $table->boolean('is_active')->default(false);
            $table->text('api_key')->nullable();
            $table->text('secret_key')->nullable();
            $table->text('client_id')->nullable(); // for Pathao
            $table->string('store_id')->nullable(); // for Pathao
            $table->string('username')->nullable(); // for Pathao email
            $table->text('password')->nullable(); // for Pathao password
            $table->string('base_url')->nullable();
            $table->string('delivery_type', 50)->nullable()->default('48');
            $table->json('additional_settings')->nullable();
            $table->timestamps();
        });

        // Seed default records for the 3 couriers
        DB::table('courier_settings')->insert([
            [
                'code'        => 'steadfast',
                'name'        => 'Steadfast Courier',
                'is_active'   => false,
                'api_key'     => null,
                'secret_key'  => null,
                'client_id'   => null,
                'store_id'    => null,
                'username'    => null,
                'password'    => null,
                'base_url'    => 'https://portal.steadfast.com.bd/api/v1',
                'delivery_type' => null,
                'additional_settings' => null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'code'        => 'pathao',
                'name'        => 'Pathao Courier',
                'is_active'   => false,
                'api_key'     => null,
                'secret_key'  => null,
                'client_id'   => null,
                'store_id'    => null,
                'username'    => null,
                'password'    => null,
                'base_url'    => 'https://api-hermes.pathao.com',
                'delivery_type' => '48',
                'additional_settings' => null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'code'        => 'redx',
                'name'        => 'RedX Delivery',
                'is_active'   => false,
                'api_key'     => null,
                'secret_key'  => null,
                'client_id'   => null,
                'store_id'    => null,
                'username'    => null,
                'password'    => null,
                'base_url'    => 'https://openapi.redx.com.bd/v1.0.0',
                'delivery_type' => 'regular',
                'additional_settings' => null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_settings');
    }
};
