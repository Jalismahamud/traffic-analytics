<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrafficLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'url',
        'method',
        'ip_address',
        'status_code',
        'response_time',
        'user_id',
        'user_agent',
        'referrer',
        'created_at',
    ];

    protected $casts = [
        'response_time' => 'float',
        'status_code'   => 'integer',
        'created_at'    => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeLastDays($query, int $days)
    {
        return $query->where('created_at', '>=', now()->subDays($days)->startOfDay());
    }

    public function scopeDateRange($query, string $from, string $to)
    {
        return $query->whereDate('created_at', '>=', $from)
                     ->whereDate('created_at', '<=', $to);
    }

    public function scopeErrors($query)
    {
        return $query->where('status_code', '>=', 400);
    }

    public function getStatusColorAttribute(): string
    {
        return match(true) {
            $this->status_code >= 500 => 'danger',
            $this->status_code >= 400 => 'warning',
            $this->status_code >= 300 => 'info',
            default                   => 'success',
        };
    }
}
