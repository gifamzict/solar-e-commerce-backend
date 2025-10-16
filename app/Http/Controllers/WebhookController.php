<?php

namespace App\Http\Controllers;

use App\Models\NotificationChannel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle email provider webhooks (delivery status updates)
     * POST /webhooks/email/provider
     */
    public function handleEmailWebhook(Request $request): JsonResponse
    {
        try {
            $data = $request->all();
            
            // Log webhook data for debugging
            Log::info('Email webhook received', $data);

            // Example handling for common email providers
            // Adjust based on your email provider's webhook format
            $messageId = $data['message_id'] ?? $data['MessageID'] ?? null;
            $status = $this->mapEmailStatus($data['status'] ?? $data['event'] ?? '');
            $error = $data['error'] ?? $data['reason'] ?? null;

            if ($messageId && $status) {
                $this->updateNotificationChannelStatus('email', $messageId, $status, $error);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Email webhook error', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Handle SMS provider webhooks (delivery status updates)
     * POST /webhooks/sms/provider
     */
    public function handleSmsWebhook(Request $request): JsonResponse
    {
        try {
            $data = $request->all();
            
            // Log webhook data for debugging
            Log::info('SMS webhook received', $data);

            // Handle Termii webhook format
            $messageId = $data['message_id'] ?? $data['id'] ?? null;
            $status = $this->mapSmsStatus($data['status'] ?? $data['dlr_status'] ?? '');
            $error = $data['error'] ?? $data['failure_reason'] ?? null;

            if ($messageId && $status) {
                $this->updateNotificationChannelStatus('sms', $messageId, $status, $error);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('SMS webhook error', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Update notification channel status based on provider callback
     */
    private function updateNotificationChannelStatus(
        string $channel,
        string $providerMessageId,
        string $status,
        ?string $error = null
    ): void {
        $notificationChannel = NotificationChannel::where('channel', $channel)
            ->where('provider_message_id', $providerMessageId)
            ->first();

        if ($notificationChannel) {
            $updateData = [
                'status' => $status,
                'updated_at' => now(),
            ];

            if ($status === 'sent') {
                $updateData['sent_at'] = now();
                $updateData['error'] = null;
            } elseif ($status === 'failed') {
                $updateData['error'] = $error;
            }

            $notificationChannel->update($updateData);

            Log::info('Notification channel status updated', [
                'channel' => $channel,
                'provider_message_id' => $providerMessageId,
                'status' => $status,
                'notification_id' => $notificationChannel->notification_id,
            ]);
        } else {
            Log::warning('Notification channel not found for webhook', [
                'channel' => $channel,
                'provider_message_id' => $providerMessageId,
            ]);
        }
    }

    /**
     * Map email provider status to our internal status
     */
    private function mapEmailStatus(string $providerStatus): string
    {
        $statusMap = [
            // Common email provider statuses
            'delivered' => 'sent',
            'bounced' => 'failed',
            'dropped' => 'failed',
            'spam' => 'failed',
            'rejected' => 'failed',
            'deferred' => 'queued',
            'processed' => 'sent',
            'opened' => 'sent', // Consider opened as successfully sent
            'clicked' => 'sent',
        ];

        return $statusMap[strtolower($providerStatus)] ?? 'queued';
    }

    /**
     * Map SMS provider status to our internal status
     */
    private function mapSmsStatus(string $providerStatus): string
    {
        $statusMap = [
            // Termii SMS statuses
            'delivered' => 'sent',
            'sent' => 'sent',
            'failed' => 'failed',
            'rejected' => 'failed',
            'expired' => 'failed',
            'unknown' => 'failed',
            'submitted' => 'queued',
            'accepted' => 'queued',
        ];

        return $statusMap[strtolower($providerStatus)] ?? 'queued';
    }
}
