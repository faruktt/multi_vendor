<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdsCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform',
        'campaign_name',
        'ad_account',
        'cost_date',
        'amount',
        'currency',
        'amount_usd',
        'impressions',
        'clicks',
        'conversions',
        'target_url',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'cost_date'   => 'date:Y-m-d',
        'amount'      => 'decimal:2',
        'amount_usd'  => 'decimal:2',
        'impressions' => 'integer',
        'clicks'      => 'integer',
        'conversions' => 'integer',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Human-friendly platform label.
     */
    public function getPlatformLabelAttribute(): string
    {
        return match (strtolower($this->platform)) {
            'facebook'  => 'Facebook Ads',
            'google'    => 'Google Ads',
            'tiktok'    => 'TikTok Ads',
            'instagram' => 'Instagram Ads',
            'youtube'   => 'YouTube Ads',
            'snapchat'  => 'Snapchat Ads',
            default     => ucfirst($this->platform),
        };
    }

    /**
     * FontAwesome icon class for the platform.
     */
    public function getPlatformIconAttribute(): string
    {
        return match (strtolower($this->platform)) {
            'facebook'  => 'fab fa-facebook text-blue-500',
            'google'    => 'fab fa-google text-red-500',
            'tiktok'    => 'fab fa-tiktok text-slate-800',
            'instagram' => 'fab fa-instagram text-pink-500',
            'youtube'   => 'fab fa-youtube text-red-600',
            'snapchat'  => 'fab fa-snapchat text-yellow-500',
            default     => 'fas fa-bullhorn text-indigo-500',
        };
    }

    /**
     * Badge CSS class for the platform.
     */
    public function getPlatformBadgeClassAttribute(): string
    {
        return match (strtolower($this->platform)) {
            'facebook'  => 'bg-blue-50 text-blue-700 border-blue-200',
            'google'    => 'bg-red-50 text-red-700 border-red-200',
            'tiktok'    => 'bg-slate-100 text-slate-800 border-slate-300',
            'instagram' => 'bg-pink-50 text-pink-700 border-pink-200',
            'youtube'   => 'bg-rose-50 text-rose-700 border-rose-200',
            'snapchat'  => 'bg-amber-50 text-amber-700 border-amber-200',
            default     => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        };
    }

    /**
     * Cost Per Click (CPC) in BDT.
     */
    public function getCpcAttribute(): float
    {
        return ($this->clicks && $this->clicks > 0)
            ? round((float) $this->amount / $this->clicks, 2)
            : 0.0;
    }

    /**
     * Cost Per Acquisition/Conversion (CPA) in BDT.
     */
    public function getCpaAttribute(): float
    {
        return ($this->conversions && $this->conversions > 0)
            ? round((float) $this->amount / $this->conversions, 2)
            : 0.0;
    }

    /**
     * Click-Through Rate (CTR) in %.
     */
    public function getCtrAttribute(): float
    {
        return ($this->impressions && $this->impressions > 0 && $this->clicks)
            ? round(($this->clicks / $this->impressions) * 100, 2)
            : 0.0;
    }
}
