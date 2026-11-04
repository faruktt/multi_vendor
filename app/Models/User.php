<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'phone', 'password', 'vendor_id', 'has_custom_permissions', 'image', 'nid_front', 'nid_back', 'guardian_nid_front', 'guardian_nid_back'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    public function hasPermissionTo($permission, ?string $guardName = null): bool
    {
        if ($this->hasRole('super-admin')) {
            return true;
        }

        if ($this->has_custom_permissions) {
            $permission = $this->filterPermission($permission, $guardName);
            return $this->hasDirectPermission($permission);
        }

        $permission = $this->filterPermission($permission, $guardName);
        return $this->hasDirectPermission($permission) || $this->hasPermissionViaRole($permission);
    }

    public function getAllPermissions(): \Illuminate\Support\Collection
    {
        if ($this->hasRole('super-admin')) {
            return \Spatie\Permission\Models\Permission::all();
        }

        if ($this->has_custom_permissions) {
            return $this->loadMissing('permissions')->permissions->sort()->values();
        }

        $permissions = $this->loadMissing('permissions')->permissions;
        if ($this->roles) {
            $permissions = $permissions->merge($this->getPermissionsViaRoles());
        }
        return $permissions->sort()->values();
    }

    protected $appends = [
        'image_url',
        'nid_front_url',
        'nid_back_url',
        'guardian_nid_front_url',
        'guardian_nid_back_url',
    ];

    public function getImageUrlAttribute(): ?string
    {
        return $this->resolveFileUrl($this->image);
    }

    public function getNidFrontUrlAttribute(): ?string
    {
        return $this->resolveFileUrl($this->nid_front);
    }

    public function getNidBackUrlAttribute(): ?string
    {
        return $this->resolveFileUrl($this->nid_back);
    }

    public function getGuardianNidFrontUrlAttribute(): ?string
    {
        return $this->resolveFileUrl($this->guardian_nid_front);
    }

    public function getGuardianNidBackUrlAttribute(): ?string
    {
        return $this->resolveFileUrl($this->guardian_nid_back);
    }

    protected function resolveFileUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('uploads/' . ltrim($path, '/'));
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'has_custom_permissions' => 'boolean',
        ];
    }
}
