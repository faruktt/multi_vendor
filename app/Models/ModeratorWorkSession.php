<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ModeratorWorkSession extends Model
{
    use HasFactory;

    protected $table = 'moderator_work_sessions';

    protected $fillable = [
        'moderator_id',
        'started_at',
        'ended_at',
        'duration_seconds',
        'rate_per_minute',
        'earned_amount',
        'status',
        'tasks_summary',
        'work_report',
        'report_submitted_at',
    ];

    protected $casts = [
        'started_at'          => 'datetime',
        'ended_at'            => 'datetime',
        'report_submitted_at' => 'datetime',
        'duration_seconds'    => 'integer',
        'rate_per_minute'     => 'decimal:2',
        'earned_amount'       => 'decimal:2',
    ];

    /**
     * Get computed or stored earned amount
     */
    public function getEarnedAmountAttribute($value): float
    {
        if ($value !== null) {
            return (float) $value;
        }

        $rate = $this->rate_per_minute ?? $this->moderator?->rate_per_minute ?? 0;
        $seconds = $this->duration_seconds;
        if ($seconds === 0 && $this->isInProgress()) {
            $seconds = $this->elapsed_seconds;
        }

        return round(($seconds / 60) * (float)$rate, 2);
    }

    /**
     * Get duration in minutes
     */
    public function getDurationMinutesAttribute(): float
    {
        $seconds = $this->duration_seconds;
        if ($seconds === 0 && $this->isInProgress()) {
            $seconds = $this->elapsed_seconds;
        }

        return round($seconds / 60, 2);
    }

    public function moderator()
    {
        return $this->belongsTo(Moderator::class);
    }

    public function logs()
    {
        return $this->hasMany(ModeratorWorkLog::class, 'work_session_id')->latest('log_time');
    }

    /**
     * Check if session is still in progress
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Get real-time elapsed seconds (for in-progress or completed)
     */
    public function getElapsedSecondsAttribute(): int
    {
        if ($this->isInProgress()) {
            return max(0, abs(Carbon::now()->timestamp - $this->started_at->timestamp));
        }

        return $this->duration_seconds;
    }

    /**
     * Format duration nicely (e.g., "2h 35m 12s" or "45m 10s")
     */
    public function formattedDuration(): string
    {
        $seconds = $this->duration_seconds;
        if ($seconds === 0 && $this->isInProgress()) {
            $seconds = $this->elapsed_seconds;
        }

        $hours   = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs    = $seconds % 60;

        $parts = [];
        if ($hours > 0) {
            $parts[] = "{$hours} hr" . ($hours > 1 ? 's' : '');
        }
        if ($minutes > 0 || $hours > 0) {
            $parts[] = "{$minutes} min" . ($minutes > 1 ? 's' : '');
        }
        $parts[] = "{$secs} sec" . ($secs > 1 ? 's' : '');

        return implode(' ', $parts);
    }

    /**
     * Compact duration (e.g. "02:35:12")
     */
    public function compactDuration(): string
    {
        $seconds = $this->duration_seconds;
        if ($seconds === 0 && $this->isInProgress()) {
            $seconds = $this->elapsed_seconds;
        }

        $hours   = str_pad((string)floor($seconds / 3600), 2, '0', STR_PAD_LEFT);
        $minutes = str_pad((string)floor(($seconds % 3600) / 60), 2, '0', STR_PAD_LEFT);
        $secs    = str_pad((string)($seconds % 60), 2, '0', STR_PAD_LEFT);

        return "{$hours}:{$minutes}:{$secs}";
    }
}
