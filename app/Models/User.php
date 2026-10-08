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

#[Fillable(['name', 'email', 'phone', 'password', 'vendor_id', 'image', 'nid_front', 'nid_back', 'guardian_nid_front', 'guardian_nid_back'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

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
        ];
    }
}
