<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationChannel extends Model
{
    protected $fillable = [
        'notification_id',
        'channel',
        'status',
        'provider_message_id',
        'error',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    /**
     * Relationship: Notification channel belongs to a notification
     */
    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }

    /**
     * Mark as sent
     */
    public function markAsSent(string $providerMessageId = null): void
    {
        $this->update([
            'status' => 'sent',
            'provider_message_id' => $providerMessageId,
            'sent_at' => now(),
            'error' => null,
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error' => $error,
        ]);
    }
}
