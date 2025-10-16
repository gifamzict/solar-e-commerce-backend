<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send SMS notification
     */
    public function send(string $to, string $message): array
    {
        try {
            $apiKey = config('services.sms.api_key');
            $senderId = config('services.sms.sender_id');
            $provider = config('services.sms.provider', 'termii');

            if (!$this->isConfigured()) {
                throw new \Exception('SMS service not properly configured');
            }

            // Clean phone number (remove + and spaces)
            $cleanNumber = preg_replace('/[\s\+\-\(\)]/', '', $to);
            
            // Ensure Nigeria number format
            if (str_starts_with($cleanNumber, '0')) {
                $cleanNumber = '234' . substr($cleanNumber, 1);
            } elseif (str_starts_with($cleanNumber, '234')) {
                // Already formatted
            } else {
                $cleanNumber = '234' . $cleanNumber;
            }

            $response = $this->sendViaTermii($cleanNumber, $message, $apiKey, $senderId);

            if ($response['success']) {
                Log::info("SMS sent successfully", [
                    'to' => $to,
                    'clean_number' => $cleanNumber,
                    'provider_message_id' => $response['message_id'] ?? null,
                ]);

                return [
                    'success' => true,
                    'status' => 'sent',
                    'provider_message_id' => $response['message_id'] ?? null,
                ];
            } else {
                throw new \Exception($response['error'] ?? 'SMS sending failed');
            }

        } catch (\Exception $e) {
            Log::error("SMS sending failed", [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send SMS via Termii API
     */
    private function sendViaTermii(string $to, string $message, string $apiKey, string $senderId): array
    {
        try {
            $response = Http::post('https://api.ng.termii.com/api/sms/send', [
                'to' => $to,
                'from' => $senderId,
                'sms' => $message,
                'type' => 'plain',
                'channel' => 'generic',
                'api_key' => $apiKey,
            ]);

            $data = $response->json();

            if ($response->successful() && isset($data['message_id'])) {
                return [
                    'success' => true,
                    'message_id' => $data['message_id'],
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $data['message'] ?? 'Unknown error from Termii',
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'HTTP request failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Validate SMS configuration
     */
    public function isConfigured(): bool
    {
        return !empty(config('services.sms.api_key')) && 
               !empty(config('services.sms.sender_id'));
    }

    /**
     * Optimize message for SMS length and GSM charset
     */
    public function optimizeMessage(string $message): string
    {
        // Convert to GSM 7-bit charset compatible characters
        $gsmReplacements = [
            '"' => '"',
            '"' => '"',
            '\'' => "'",
            '\'' => "'",
            '–' => '-',
            '—' => '-',
            '…' => '...',
        ];

        $optimized = str_replace(array_keys($gsmReplacements), array_values($gsmReplacements), $message);

        // Ensure maximum SMS length (concatenated SMS up to 480 chars)
        if (strlen($optimized) > 480) {
            $optimized = substr($optimized, 0, 477) . '...';
        }

        return $optimized;
    }
}