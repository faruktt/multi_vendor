<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModeratorWorkLog extends Model
{
    use HasFactory;

    protected $table = 'moderator_work_logs';

    protected $fillable = [
        'work_session_id',
        'moderator_id',
        'log_time',
        'activity',
    ];

    protected $casts = [
        'log_time' => 'datetime',
    ];

    public function workSession()
    {
        return $this->belongsTo(ModeratorWorkSession::class, 'work_session_id');
    }

    public function moderator()
    {
        return $this->belongsTo(Moderator::class);
    }
}
