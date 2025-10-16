<?php

namespace App\Services;

use App\Models\CustomerPreOrder;
use App\Models\Notification;
use App\Models\NotificationChannel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function __construct(
        private NotificationTemplateService $templateService,
        private EmailService $emailService,
        private SmsService $smsService
    ) {}

    /**
     * Send notification to customer pre-order
     */
    public function sendNotification(
        CustomerPreOrder $customerPreOrder,
        array $notificationData,
        int $adminId
    ): array {
        return DB::transaction(function () use ($customerPreOrder, $notificationData, $adminId) {
            // Resolve message templates
            $resolvedMessages = $this->templateService->resolveTemplate(
                $notificationData['message'],
                $customerPreOrder,
                $notificationData
            );

            // Create notification record
            $notification = Notification::create([
                'customer_preorder_id' => $customerPreOrder->id,
                'mode' => $notificationData['mode'],
                'channels' => $notificationData['channels'],
                'subject' => $notificationData['subject'],
                'message_template' => $notificationData['message'],
                'message_resolved_email' => $resolvedMessages['email'],
                'message_resolved_sms' => $resolvedMessages['sms'],
                'payment_deadline' => $notificationData['payment_deadline'] ?? null,
                'reason' => $notificationData['reason'] ?? null,
                'ready_date' => $notificationData['ready_date'] ?? null,
                'fulfillment_method' => $notificationData['fulfillment_method'],
                'pickup_location' => $notificationData['pickup_location'] ?? null,
                'shipping_address' => $notificationData['shipping_address'] ?? null,
                'city' => $notificationData['city'] ?? null,
                'state' => $notificationData['state'] ?? null,
                'created_by_admin_id' => $adminId,
            ]);

            // Send notifications via each channel
            $channelResults = [];
            foreach ($notificationData['channels'] as $channel) {
                $channelResult = $this->sendViaChannel(
                    $channel,
                    $customerPreOrder,
                    $notificationData['subject'],
                    $resolvedMessages[$channel === 'email' ? 'email' : 'sms'],
                    $notificationData // Pass notification data to sendViaChannel
                );

                // Create notification channel record
                $notificationChannel = NotificationChannel::create([
                    'notification_id' => $notification->id,
                    'channel' => $channel,
                    'status' => $channelResult['status'],
                    'provider_message_id' => $channelResult['provider_message_id'] ?? null,
                    'error' => $channelResult['error'] ?? null,
                    'sent_at' => $channelResult['status'] === 'sent' ? now() : null,
                ]);

                $channelResults[] = [
                    'channel' => $channel,
                    'status' => $channelResult['status'],
                    'provider_message_id' => $channelResult['provider_message_id'] ?? null,
                    'error' => $channelResult['error'] ?? null,
                ];
            }

            return [
                'notification_id' => $notification->id,
                'customer_preorder_id' => $customerPreOrder->id,
                'mode' => $notificationData['mode'],
                'channels' => $channelResults,
                'created_at' => $notification->created_at->toISOString(),
            ];
        });
    }

    /**
     * Send notification via specific channel
     */
    private function sendViaChannel(
        string $channel,
        CustomerPreOrder $customerPreOrder,
        string $subject,
        string $message,
        array $notificationData = []
    ): array {
        try {
            switch ($channel) {
                case 'email':
                    if (empty($customerPreOrder->customer_email)) {
                        throw new \Exception('Customer email not available');
                    }
                    
                    // Use the new HTML email method for customer pre-order notifications
                    return $this->emailService->sendCustomerPreOrderNotification(
                        $customerPreOrder,
                        $subject,
                        $message,
                        $notificationData
                    );

                case 'sms':
                    if (empty($customerPreOrder->customer_phone)) {
                        throw new \Exception('Customer phone not available');
                    }
                    $optimizedMessage = $this->smsService->optimizeMessage($message);
                    return $this->smsService->send(
                        $customerPreOrder->customer_phone,
                        $optimizedMessage
                    );

                default:
                    throw new \Exception("Unsupported channel: {$channel}");
            }
        } catch (\Exception $e) {
            Log::error("Channel delivery failed", [
                'channel' => $channel,
                'customer_preorder_id' => $customerPreOrder->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Resend notification via specific channels
     */
    public function resendNotification(Notification $notification, array $channels = null): array
    {
        $channelsToResend = $channels ?? $notification->channels;
        $customerPreOrder = $notification->customerPreOrder;
        
        // Reconstruct notification data from the notification record
        $notificationData = [
            'mode' => $notification->mode,
            'fulfillment_method' => $notification->fulfillment_method,
            'payment_deadline' => $notification->payment_deadline?->format('Y-m-d'),
            'reason' => $notification->reason,
            'ready_date' => $notification->ready_date?->format('Y-m-d'),
            'pickup_location' => $notification->pickup_location,
            'shipping_address' => $notification->shipping_address,
            'city' => $notification->city,
            'state' => $notification->state,
        ];
        
        $channelResults = [];
        foreach ($channelsToResend as $channel) {
            $message = $channel === 'email' 
                ? $notification->message_resolved_email 
                : $notification->message_resolved_sms;

            $channelResult = $this->sendViaChannel(
                $channel,
                $customerPreOrder,
                $notification->subject,
                $message,
                $notificationData
            );

            // Update existing notification channel or create new one
            $notificationChannel = $notification->notificationChannels()
                ->where('channel', $channel)
                ->first();

            if ($notificationChannel) {
                $notificationChannel->update([
                    'status' => $channelResult['status'],
                    'provider_message_id' => $channelResult['provider_message_id'] ?? null,
                    'error' => $channelResult['error'] ?? null,
                    'sent_at' => $channelResult['status'] === 'sent' ? now() : null,
                ]);
            } else {
                NotificationChannel::create([
                    'notification_id' => $notification->id,
                    'channel' => $channel,
                    'status' => $channelResult['status'],
                    'provider_message_id' => $channelResult['provider_message_id'] ?? null,
                    'error' => $channelResult['error'] ?? null,
                    'sent_at' => $channelResult['status'] === 'sent' ? now() : null,
                ]);
            }

            $channelResults[] = [
                'channel' => $channel,
                'status' => $channelResult['status'],
                'provider_message_id' => $channelResult['provider_message_id'] ?? null,
                'error' => $channelResult['error'] ?? null,
            ];
        }

        return $channelResults;
    }

    /**
     * Validate notification data
     */
    public function validateNotificationData(CustomerPreOrder $customerPreOrder, array $data): array
    {
        $errors = [];

        // Validate mode-specific requirements
        if ($data['mode'] === 'balance') {
            if ($customerPreOrder->remaining_amount <= 0) {
                $errors[] = 'No remaining balance to collect for this pre-order';
            }
            if (empty($data['payment_deadline'])) {
                $errors[] = 'Payment deadline is required for balance mode';
            }
            if (empty($data['reason'])) {
                $errors[] = 'Reason is required for balance mode';
            }
        }

        if ($data['mode'] === 'ready') {
            if ($customerPreOrder->payment_status !== 'fully_paid' && !($data['override_payment_check'] ?? false)) {
                $errors[] = 'Pre-order must be fully paid before marking as ready';
            }
            if (empty($data['ready_date'])) {
                $errors[] = 'Ready date is required for ready mode';
            }
        }

        // Validate fulfillment method requirements
        if ($data['fulfillment_method'] === 'pickup' && $data['mode'] === 'ready') {
            if (empty($data['pickup_location']) && empty($customerPreOrder->pickup_location)) {
                $errors[] = 'Pickup location is required for pickup fulfillment';
            }
        }

        if ($data['fulfillment_method'] === 'delivery' && $data['mode'] === 'ready') {
            if (empty($data['shipping_address']) && empty($customerPreOrder->shipping_address)) {
                $errors[] = 'Shipping address is required for delivery fulfillment';
            }
            if (empty($data['city']) && empty($customerPreOrder->city)) {
                $errors[] = 'City is required for delivery fulfillment';
            }
            if (empty($data['state']) && empty($customerPreOrder->state)) {
                $errors[] = 'State is required for delivery fulfillment';
            }
        }

        // Validate channel availability
        foreach ($data['channels'] as $channel) {
            if ($channel === 'email' && empty($customerPreOrder->customer_email)) {
                $errors[] = 'Customer email not available for email notifications';
            }
            if ($channel === 'sms' && empty($customerPreOrder->customer_phone)) {
                $errors[] = 'Customer phone not available for SMS notifications';
            }
        }

        return $errors;
    }
}