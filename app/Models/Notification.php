<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notification extends Model
{
    protected $fillable = [
        'customer_preorder_id',
        'mode',
        'channels',
        'subject',
        'message_template',
        'message_resolved_email',
        'message_resolved_sms',
        'payment_deadline',
        'reason',
        'ready_date',
        'fulfillment_method',
        'pickup_location',
        'shipping_address',
        'city',
        'state',
        'created_by_admin_id',
    ];

    protected $casts = [
        'channels' => 'array',
        'payment_deadline' => 'date',
        'ready_date' => 'date',
    ];

    /**
     * Relationship: Notification belongs to a customer pre-order
     */
    public function customerPreOrder(): BelongsTo
    {
        return $this->belongsTo(CustomerPreOrder::class);
    }

    /**
     * Relationship: Notification belongs to an admin
     */
    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    /**
     * Relationship: Notification has many notification channels
     */
    public function notificationChannels(): HasMany
    {
        return $this->hasMany(NotificationChannel::class);
    }

    /**
     * Get notification channels with their statuses
     */
    public function getChannelsWithStatusAttribute(): array
    {
        return $this->notificationChannels->map(function ($channel) {
            return [
                'channel' => $channel->channel,
                'status' => $channel->status,
                'provider_message_id' => $channel->provider_message_id,
                'error' => $channel->error,
                'sent_at' => $channel->sent_at,
            ];
        })->toArray();
    }
}
