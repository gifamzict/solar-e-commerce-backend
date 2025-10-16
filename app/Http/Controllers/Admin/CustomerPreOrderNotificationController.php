<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendNotificationRequest;
use App\Models\CustomerPreOrder;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerPreOrderNotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    /**
     * Send notification to customer pre-order
     * POST /admin/customer-pre-orders/{id}/notify
     */
    public function sendNotification(SendNotificationRequest $request, CustomerPreOrder $customerPreOrder): JsonResponse
    {
        try {
            // Load the preOrder relationship to access product details
            $customerPreOrder->load('preOrder');
            
            // Simple approach - bypass all services to avoid recursion
            $adminId = 1; // Hardcode for now to avoid auth issues
            
            // Get basic data
            $data = $request->validated();
            
            // Create a simple email without using complex services
            $to = $customerPreOrder->customer_email;
            $rawSubject = $data['subject'] ?? 'Pre-Order Update';
            $rawMessage = $data['message'] ?? 'We have an update about your pre-order.';
            
            // Process merge tags for both subject and message
            $subject = $this->processSimpleMergeTags($rawSubject, $customerPreOrder, $data);
            $processedMessage = $this->processSimpleMergeTags($rawMessage, $customerPreOrder, $data);
            
            // Get product name from the preOrder relationship
            $productName = $customerPreOrder->preOrder?->product_name ?? 'N/A';
            
            // Enhanced email template
            $htmlContent = "
            <!DOCTYPE html>
            <html lang='en'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <meta http-equiv='X-UA-Compatible' content='IE=edge'>
                <title>{$subject}</title>
                <style>
                    * {
                        margin: 0;
                        padding: 0;
                        box-sizing: border-box;
                    }
                    body {
                        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
                        line-height: 1.6;
                        color: #1e293b;
                        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
                        padding: 40px 20px;
                    }
                    .email-container {
                        max-width: 600px;
                        margin: 0 auto;
                        background: #ffffff;
                        border-radius: 16px;
                        overflow: hidden;
                        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
                    }
                    .header {
                        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
                        padding: 40px 30px;
                        text-align: center;
                        color: white;
                    }
                    .header-icon {
                        width: 80px;
                        height: 80px;
                        background: white;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin: 0 auto 20px;
                        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                        font-size: 40px;
                    }
                    .header h1 {
                        font-size: 28px;
                        font-weight: 700;
                        margin-bottom: 8px;
                        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                    }
                    .header p {
                        font-size: 16px;
                        opacity: 0.95;
                    }
                    .content {
                        padding: 40px 30px;
                    }
                    .greeting {
                        font-size: 18px;
                        color: #1e293b;
                        margin-bottom: 24px;
                        font-weight: 600;
                    }
                    .message-box {
                        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
                        border-left: 4px solid #10b981;
                        border-radius: 8px;
                        padding: 24px;
                        margin-bottom: 30px;
                        line-height: 1.8;
                        color: #064e3b;
                        font-size: 15px;
                    }
                    .order-details {
                        background: #f8fafc;
                        border: 2px solid #e2e8f0;
                        border-radius: 12px;
                        padding: 24px;
                        margin-bottom: 24px;
                    }
                    .order-details h2 {
                        font-size: 20px;
                        color: #0f172a;
                        margin-bottom: 20px;
                        font-weight: 700;
                        display: flex;
                        align-items: center;
                        gap: 8px;
                    }
                    .detail-row {
                        display: flex;
                        justify-content: space-between;
                        padding: 14px 0;
                        border-bottom: 1px solid #e2e8f0;
                    }
                    .detail-row:last-child {
                        border-bottom: none;
                    }
                    .detail-label {
                        font-weight: 600;
                        color: #64748b;
                        font-size: 14px;
                    }
                    .detail-value {
                        color: #1e293b;
                        font-size: 14px;
                        font-weight: 500;
                        text-align: right;
                    }
                    .footer {
                        background: #f8fafc;
                        padding: 30px;
                        text-align: center;
                        border-top: 2px solid #e2e8f0;
                    }
                    .footer p {
                        color: #64748b;
                        font-size: 14px;
                        margin: 8px 0;
                    }
                    .footer-brand {
                        font-weight: 700;
                        color: #0f172a;
                        font-size: 16px;
                        margin-bottom: 8px;
                    }
                    .footer-links {
                        margin-top: 16px;
                        padding-top: 16px;
                        border-top: 1px solid #e2e8f0;
                    }
                    .footer-links a {
                        color: #3b82f6;
                        text-decoration: none;
                        margin: 0 12px;
                        font-size: 13px;
                        font-weight: 500;
                    }
                    .divider {
                        height: 2px;
                        background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
                        margin: 24px 0;
                    }
                    @media only screen and (max-width: 600px) {
                        body {
                            padding: 20px 10px;
                        }
                        .content {
                            padding: 24px 20px;
                        }
                        .header {
                            padding: 30px 20px;
                        }
                        .header h1 {
                            font-size: 24px;
                        }
                        .header-icon {
                            width: 70px;
                            height: 70px;
                            font-size: 35px;
                        }
                        .detail-row {
                            flex-direction: column;
                            gap: 4px;
                        }
                        .detail-value {
                            text-align: left;
                        }
                    }
                </style>
            </head>
            <body>
                <div class='email-container'>
                    <!-- Header -->
                    <div class='header'>
                        <div class='header-icon'>📬</div>
                        <h1>Ready for delivery: {$productName}</h1>
                        <p>Important update about your pre-order</p>
                    </div>

                    <!-- Content -->
                    <div class='content'>
                        <p class='greeting'>Hello {$customerPreOrder->full_name},</p>
                        
                        <div class='message-box'>
                            {$processedMessage}
                        </div>

                        <div class='divider'></div>

                        <!-- Order Details -->
                        <div class='order-details'>
                            <h2>
                                <svg xmlns='http://www.w3.org/2000/svg' width='20' height='20' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2'>
                                    <path stroke-linecap='round' stroke-linejoin='round' d='M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z' />
                                </svg>
                                Order Details
                            </h2>
                            
                            <div class='detail-row'>
                                <span class='detail-label'>Order Number: </span>
                                <span class='detail-value'><strong>{$customerPreOrder->pre_order_number}</strong></span>
                            </div>
                            
                            <div class='detail-row'>
                                <span class='detail-label'>Product: </span>
                                <span class='detail-value'>" . ($productName) . "</span>
                            </div>
                            
                            <div class='detail-row'>
                                <span class='detail-label'>Quantity: </span>
                                <span class='detail-value'>{$customerPreOrder->quantity} unit(s)</span>
                            </div>
                        </div>

                        <!-- Support Message -->
                        <div style='background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-radius: 8px; padding: 20px; text-align: center; margin-top: 24px;'>
                            <p style='color: #1e40af; font-size: 14px; margin: 0;'>
                                <strong>Need help?</strong> Our customer support team is here for you.
                            </p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class='footer'>
                        <p class='footer-brand'>🛍️ " . config('app.brand_name', 'G-Tech Solar') . "</p>
                        <p>Thank you for choosing us! We appreciate your business.</p>
                        
                        <div class='footer-links'>
                            <a href='#'>Track Order</a>
                            <a href='#'>Contact Support</a>
                            <a href='#'>FAQs</a>
                        </div>
                        
                        <p style='font-size: 12px; color: #94a3b8; margin-top: 16px;'>
                            © " . date('Y') . " " . config('app.brand_name', 'G-Tech Solar') . ". All rights reserved.
                        </p>
                    </div>
                </div>
            </body>
            </html>";
            
            // Send simple email using Laravel's basic Mail facade
            \Illuminate\Support\Facades\Mail::html($htmlContent, function ($mail) use ($to, $subject) {
                $mail->to($to)
                     ->subject($subject)
                     ->from(config('mail.from.address'), config('app.brand_name', 'G-Tech Solar'));
            });

            return response()->json([
                'success' => true,
                'message' => 'Notification sent successfully',
                'data' => [
                    'customer_email' => $to,
                    'subject' => $subject,
                    'sent_at' => now()->toISOString(),
                ],
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Notification sending failed', [
                'customer_preorder_id' => $customerPreOrder->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'server_error',
                'message' => 'Failed to send notification: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process merge tags in message template
     */
    private function processSimpleMergeTags(string $message, CustomerPreOrder $customerPreOrder, array $data): string
    {
        // Get product name from the loaded preOrder relationship
        $productName = $customerPreOrder->preOrder?->product_name ?? 'N/A';
        
        $mergeTags = [
            '{{customer_name}}' => $customerPreOrder->full_name,
            '{{product_name}}' => $productName, // Use the correct product name from relationship
            '{{pre_order_number}}' => $customerPreOrder->pre_order_number,
            '{{quantity}}' => (string) $customerPreOrder->quantity,
            '{{remaining_amount}}' => number_format($customerPreOrder->remaining_amount, 2),
            '{{currency}}' => $customerPreOrder->currency,
            '{{payment_deadline}}' => $data['payment_deadline'] ?? '-',
            '{{reason}}' => $data['reason'] ?? '-',
            '{{fulfillment_method}}' => $data['fulfillment_method'] ?? 'pickup',
            '{{pickup_location}}' => $data['pickup_location'] ?? $customerPreOrder->pickup_location ?? '-',
            '{{shipping_address}}' => $data['shipping_address'] ?? $customerPreOrder->shipping_address ?? '-',
            '{{shipping_city}}' => $data['city'] ?? $customerPreOrder->city ?? '-',
            '{{shipping_state}}' => $data['state'] ?? $customerPreOrder->state ?? '-',
            '{{ready_date}}' => $data['ready_date'] ?? '-',
        ];

        $processedMessage = $message;
        foreach ($mergeTags as $tag => $value) {
            $processedMessage = str_replace($tag, $value, $processedMessage);
        }

        return $processedMessage;
    }

    /**
     * Get notifications for a customer pre-order
     * GET /admin/customer-pre-orders/{id}/notifications
     */
    public function getNotifications(Request $request, CustomerPreOrder $customerPreOrder): JsonResponse
    {
        $page = $request->get('page', 1);
        $perPage = min($request->get('per_page', 15), 50); // Max 50 per page

        $notifications = $customerPreOrder->notifications()
            ->with(['notificationChannels', 'createdByAdmin'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $data = $notifications->getCollection()->map(function ($notification) {
            return [
                'id' => $notification->id,
                'mode' => $notification->mode,
                'subject' => $notification->subject,
                'channels' => $notification->notificationChannels->map(function ($channel) {
                    return [
                        'channel' => $channel->channel,
                        'status' => $channel->status,
                        'sent_at' => $channel->sent_at?->toISOString(),
                        'error' => $channel->error,
                    ];
                }),
                'created_by' => $notification->createdByAdmin?->name ?? 'Unknown',
                'created_at' => $notification->created_at->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'last_page' => $notifications->lastPage(),
            ],
        ]);
    }

    /**
     * Get specific notification details
     * GET /admin/notifications/{notification_id}
     */
    public function getNotification(Notification $notification): JsonResponse
    {
        $notification->load(['customerPreOrder.preOrder', 'notificationChannels', 'createdByAdmin']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $notification->id,
                'customer_preorder_id' => $notification->customer_preorder_id,
                'customer_name' => $notification->customerPreOrder->full_name,
                'pre_order_number' => $notification->customerPreOrder->pre_order_number,
                'product_name' => $notification->customerPreOrder->preOrder->product_name,
                'mode' => $notification->mode,
                'subject' => $notification->subject,
                'message_template' => $notification->message_template,
                'message_resolved_email' => $notification->message_resolved_email,
                'message_resolved_sms' => $notification->message_resolved_sms,
                'fulfillment_method' => $notification->fulfillment_method,
                'payment_deadline' => $notification->payment_deadline?->format('Y-m-d'),
                'reason' => $notification->reason,
                'ready_date' => $notification->ready_date?->format('Y-m-d'),
                'pickup_location' => $notification->pickup_location,
                'shipping_address' => $notification->shipping_address,
                'city' => $notification->city,
                'state' => $notification->state,
                'channels' => $notification->notificationChannels->map(function ($channel) {
                    return [
                        'channel' => $channel->channel,
                        'status' => $channel->status,
                        'provider_message_id' => $channel->provider_message_id,
                        'error' => $channel->error,
                        'sent_at' => $channel->sent_at?->toISOString(),
                    ];
                }),
                'created_by' => $notification->createdByAdmin?->name ?? 'Unknown',
                'created_at' => $notification->created_at->toISOString(),
                'updated_at' => $notification->updated_at->toISOString(),
            ],
        ]);
    }

    /**
     * Resend notification via specific channels
     * POST /admin/notifications/{notification_id}/resend
     */
    public function resendNotification(Request $request, Notification $notification): JsonResponse
    {
        $request->validate([
            'channels' => 'sometimes|array|min:1',
            'channels.*' => 'in:email,sms',
        ]);

        try {
            $channels = $request->get('channels');
            $result = $this->notificationService->resendNotification($notification, $channels);

            return response()->json([
                'success' => true,
                'data' => [
                    'notification_id' => $notification->id,
                    'channels' => $result,
                    'resent_at' => now()->toISOString(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'server_error',
                'message' => 'Failed to resend notification: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available merge tags for documentation
     * GET /admin/notifications/merge-tags
     */
    public function getMergeTags(): JsonResponse
    {
        $templateService = app(NotificationTemplateService::class);
        
        return response()->json([
            'success' => true,
            'data' => [
                'merge_tags' => $templateService->getAvailableMergeTags(),
                'usage' => 'Use merge tags in your message template by wrapping them in double curly braces, e.g., {{customer_name}}',
                'example' => 'Hello {{customer_name}}, your pre-order {{pre_order_number}} for {{product_name}} is ready for {{fulfillment_method}}.',
            ],
        ]);
    }
}