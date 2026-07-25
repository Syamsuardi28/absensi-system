<?php

namespace App\Models;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'user_id', 'type', 'channel', 'payload', 'sent_at', 'status',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'sent_at' => 'datetime',
            'channel' => NotificationChannel::class,
            'status' => NotificationStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => NotificationStatus::Sent,
            'sent_at' => now(),
        ]);
    }

    public function markAsFailed(): void
    {
        $this->update([
            'status' => NotificationStatus::Failed,
        ]);
    }
}
