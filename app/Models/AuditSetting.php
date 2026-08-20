<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditSetting extends Model
{
    protected $fillable = [
        'enabled', 'log_reads', 'log_creates', 'log_updates', 'log_deletes', 'log_logins',
        'capture_ip', 'capture_user_agent', 'retention_days',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'log_reads' => 'boolean',
        'log_creates' => 'boolean',
        'log_updates' => 'boolean',
        'log_deletes' => 'boolean',
        'log_logins' => 'boolean',
        'capture_ip' => 'boolean',
        'capture_user_agent' => 'boolean',
        'retention_days' => 'integer',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'enabled' => true,
            'log_creates' => true,
            'log_updates' => true,
            'log_deletes' => true,
            'log_logins' => true,
            'retention_days' => 365,
        ]);
    }
}
