<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'customer_id',
        'last_message_at',
        'customer_unread_count',
        'admin_unread_count',
        'status',
    ];

    protected $casts = [
        'last_message_at'       => 'datetime',
        'customer_unread_count' => 'integer',
        'admin_unread_count'    => 'integer',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }

    public function latestMessage()
    {
        return $this->hasOne(ChatMessage::class, 'conversation_id')->latestOfMany();
    }

    /**
     * Get snippet of the last message for conversation list preview.
     */
    public function getLastMessagePreviewAttribute(): string
    {
        $last = $this->latestMessage;
        if (!$last) {
            return 'No messages yet';
        }

        if (!empty($last->message)) {
            return $last->message;
        }

        if (!empty($last->image_path)) {
            return '📷 Photo';
        }

        return '';
    }

    /**
     * Human-friendly last activity time.
     */
    public function getFormattedTimeAttribute(): string
    {
        if (!$this->last_message_at) {
            return $this->created_at ? $this->created_at->diffForHumans() : '';
        }

        if ($this->last_message_at->isToday()) {
            return $this->last_message_at->format('h:i A');
        }

        if ($this->last_message_at->isYesterday()) {
            return 'Yesterday';
        }

        return $this->last_message_at->format('d/m/Y');
    }
}
