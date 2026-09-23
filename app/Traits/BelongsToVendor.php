<?php

namespace App\Traits;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class VendorScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->hasRole('super-admin')) return;
            $builder->where('vendor_id', $user->vendor_id);
        }
    }
}

trait BelongsToVendor
{
    protected static function booted(): void
    {
        static::addGlobalScope(new VendorScope());
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
