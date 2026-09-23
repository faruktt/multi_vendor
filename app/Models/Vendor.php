<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = [
        'name', 'owner_name', 'email', 'phone', 'address', 'status', 'system_name',
        'commission_percentage', 'logo', 'delivery_charge',
        'delivery_charge_inside_dhaka', 'delivery_charge_sub_dhaka', 'delivery_charge_outside_dhaka',
        'sub_dhaka_districts', 'sub_dhaka_thanas', 'is_online_store', 'is_warehouse'
    ];

    protected $casts = [
        'is_online_store'              => 'boolean',
        'is_warehouse'                 => 'boolean',
        'delivery_charge_inside_dhaka' => 'decimal:2',
        'delivery_charge_sub_dhaka'    => 'decimal:2',
        'delivery_charge_outside_dhaka'=> 'decimal:2',
        'sub_dhaka_districts'          => 'array',
        'sub_dhaka_thanas'             => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public static function onlineStore(): self
    {
        $online = static::where('is_online_store', true)->first();
        if ($online) {
            return $online;
        }

        $existing = static::where('name', 'like', '%online%')->first()
            ?? static::where('is_warehouse', false)->first();

        if ($existing) {
            $existing->update(['is_online_store' => true]);
            return $existing;
        }

        return static::create([
            'name'            => 'Online Store',
            'owner_name'      => 'Admin',
            'email'           => 'store@system.local',
            'phone'           => '01700000000',
            'address'         => 'Online Storefront',
            'system_name'     => 'Online Store',
            'status'          => 'active',
            'is_online_store' => true,
            'is_warehouse'    => false,
        ]);
    }

    public static function warehouse(): self
    {
        $warehouse = static::where('is_warehouse', true)->first();
        if ($warehouse) {
            return $warehouse;
        }

        $existing = static::where('name', 'like', '%warehouse%')->first();
        if ($existing) {
            $existing->update(['is_warehouse' => true]);
            return $existing;
        }

        return static::create([
            'name'            => 'Warehouse',
            'owner_name'      => 'Admin',
            'email'           => 'warehouse@system.local',
            'phone'           => '01700000000',
            'address'         => 'Main Warehouse',
            'system_name'     => 'Warehouse',
            'status'          => 'active',
            'is_warehouse'    => true,
            'is_online_store' => false,
        ]);
    }

    /** Lets routes use the literal segment "warehouse" instead of a numeric branch id. */
    public function resolveRouteBinding($value, $field = null)
    {
        if ($value === 'warehouse') {
            return static::warehouse();
        }

        return parent::resolveRouteBinding($value, $field);
    }
}

