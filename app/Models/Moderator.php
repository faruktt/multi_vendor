<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Moderator extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'moderators';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'image',
        'status',
        'rate_per_minute',
        'nid_front',
        'nid_back',
        'guardian_nid_front',
        'guardian_nid_back',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'status'          => 'string',
        'rate_per_minute' => 'decimal:2',
    ];

    protected $appends = [
        'image_url',
        'nid_front_url',
        'nid_back_url',
        'guardian_nid_front_url',
        'guardian_nid_back_url',
        'available_balance',
    ];

    /**
     * Get avatar / profile image URL
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->resolveFileUrl($this->image);
    }

    /**
     * Get moderator NID front image URL
     */
    public function getNidFrontUrlAttribute(): ?string
    {
        return $this->resolveFileUrl($this->nid_front);
    }

    /**
     * Get moderator NID back image URL
     */
    public function getNidBackUrlAttribute(): ?string
    {
        return $this->resolveFileUrl($this->nid_back);
    }

    /**
     * Get guardian NID front image URL
     */
    public function getGuardianNidFrontUrlAttribute(): ?string
    {
        return $this->resolveFileUrl($this->guardian_nid_front);
    }

    /**
     * Get guardian NID back image URL
     */
    public function getGuardianNidBackUrlAttribute(): ?string
    {
        return $this->resolveFileUrl($this->guardian_nid_back);
    }

    /**
     * Helper to resolve file URL from uploads disk or external
     */
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

    /**
     * Check if moderator is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Moderator's work sessions
     */
    public function workSessions()
    {
        return $this->hasMany(ModeratorWorkSession::class);
    }

    /**
     * Moderator's withdrawal requests
     */
    public function withdrawals()
    {
        return $this->hasMany(ModeratorWithdrawal::class)->latest();
    }

    /**
     * Currently active work session (in_progress)
     */
    public function activeSession(): ?ModeratorWorkSession
    {
        return $this->workSessions()->where('status', 'in_progress')->latest('started_at')->first();
    }

    /**
     * Check if moderator is currently on duty / working
     */
    public function isWorkingNow(): bool
    {
        return $this->activeSession() !== null;
    }

    /**
     * Total work seconds across all completed sessions
     */
    public function totalWorkSeconds(): int
    {
        return (int) $this->workSessions()->where('status', 'completed')->sum('duration_seconds');
    }

    /**
     * Total work minutes across all completed sessions
     */
    public function totalWorkMinutes(): float
    {
        return round($this->totalWorkSeconds() / 60, 2);
    }

    /**
     * Total money earned across all completed sessions based on rate per minute
     */
    public function totalEarnedAmount(): float
    {
        $sessions = $this->workSessions()->where('status', 'completed')->get();
        $total = 0.00;

        foreach ($sessions as $session) {
            $total += (float) $session->earned_amount;
        }

        return round($total, 2);
    }

    /**
     * Total withdrawn amount approved by admin
     */
    public function totalWithdrawnAmount(): float
    {
        return (float) $this->withdrawals()->where('status', 'approved')->sum('amount');
    }

    /**
     * Pending withdrawals awaiting admin approval
     */
    public function pendingWithdrawnAmount(): float
    {
        return (float) $this->withdrawals()->where('status', 'pending')->sum('amount');
    }

    /**
     * Net available balance for withdrawal
     */
    public function availableBalance(): float
    {
        $balance = $this->totalEarnedAmount() - $this->totalWithdrawnAmount() - $this->pendingWithdrawnAmount();
        return max(0.00, round($balance, 2));
    }

    public function getAvailableBalanceAttribute(): float
    {
        return $this->availableBalance();
    }

    /**
     * Formatted total work time (e.g., "12h 45m" or "0m")
     */
    public function formattedTotalWorkTime(): string
    {
        $seconds = $this->totalWorkSeconds();
        if ($seconds === 0) {
            return '0m';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }
}
