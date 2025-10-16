<?php

namespace App\Services;

use App\Mail\CustomerPreOrderNotification;
use App\Models\CustomerPreOrder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * Send email notification
     */
    public function send(string $to, string $subject, string $message): array
    {
        try {
            $from = config('mail.from.address');
            $replyTo = config('mail.reply_to', $from);
            $brandName = config('app.brand_name', 'G-Tech Solar');

            // For simple emails without customer pre-order context
            Mail::raw($message, function ($mail) use ($to, $subject, $from, $replyTo, $brandName) {
                $mail->to($to)
                     ->subject($subject)
                     ->from($from, $brandName);
                
                if ($replyTo) {
                    $mail->replyTo($replyTo);
                }
            });

            Log::info("Email sent successfully", [
                'to' => $to,
                'subject' => $subject,
            ]);

            return [
                'success' => true,
                'status' => 'sent',
                'provider_message_id' => null, // Laravel Mail doesn't return message ID by default
            ];

        } catch (\Exception $e) {
            Log::error("Email sending failed", [
                'to' => $to,
                'subject' => $subject,
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
     * Send customer pre-order notification with HTML template
     */
    public function sendCustomerPreOrderNotification(
        CustomerPreOrder $customerPreOrder,
        string $subject,
        string $resolvedMessage,
        array $notificationData
    ): array {
        try {
            // Temporarily use simple HTML instead of complex Blade template to avoid recursion
            $simpleHtmlMessage = $this->createSimpleHtmlMessage(
                $customerPreOrder,
                $subject,
                $resolvedMessage,
                $notificationData
            );
            
            $from = config('mail.from.address');
            $brandName = config('app.brand_name', 'G-Tech Solar');
            
            Mail::html($simpleHtmlMessage, function ($mail) use ($customerPreOrder, $subject, $from, $brandName) {
                $mail->to($customerPreOrder->customer_email)
                     ->subject($subject)
                     ->from($from, $brandName);
            });

            Log::info("Customer pre-order notification sent successfully", [
                'to' => $customerPreOrder->customer_email,
                'customer_preorder_id' => $customerPreOrder->id,
                'subject' => $subject,
            ]);

            return [
                'success' => true,
                'status' => 'sent',
                'provider_message_id' => null,
            ];

        } catch (\Exception $e) {
            Log::error("Customer pre-order notification failed", [
                'to' => $customerPreOrder->customer_email,
                'customer_preorder_id' => $customerPreOrder->id,
                'subject' => $subject,
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
     * Create simple HTML message without Blade template
     */
    private function createSimpleHtmlMessage(
        CustomerPreOrder $customerPreOrder,
        string $subject,
        string $resolvedMessage,
        array $notificationData
    ): string {
        $brandName = config('app.brand_name', 'G-Tech Solar');
        $mode = $notificationData['mode'] ?? 'general';
        
        // Determine theme colors based on mode
        $themeColor = match($mode) {
            'balance' => '#f59e0b',
            'ready' => '#059669',
            default => '#3b82f6'
        };
        
        $statusTitle = match($mode) {
            'balance' => '💳 Payment Required',
            'ready' => '🎉 Your Order is Ready!',
            default => '📋 Order Update'
        };

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1'>
            <title>{$subject}</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px; background: #f5f5f5; }
                .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                .header { background: {$themeColor}; color: white; padding: 30px; text-align: center; }
                .header h1 { margin: 0; font-size: 24px; }
                .content { padding: 30px; }
                .status-card { background: linear-gradient(135deg, #f8f9fa, #e9ecef); padding: 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid {$themeColor}; }
                .order-details { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; color: #666; }
                .button { display: inline-block; background: {$themeColor}; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>⚡ {$brandName}</h1>
                    <p>Your Pre-Order Update</p>
                </div>
                <div class='content'>
                    <div class='status-card'>
                        <h2>{$statusTitle}</h2>
                        <p>We have an important update about your pre-order.</p>
                    </div>
                    
                    <p><strong>Hello {$customerPreOrder->full_name}!</strong></p>
                    
                    <div style='margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 5px;'>
                        " . nl2br(htmlspecialchars($resolvedMessage)) . "
                    </div>
                    
                    <div class='order-details'>
                        <h3>📦 Pre-Order Details</h3>
                        <p><strong>Order Number:</strong> {$customerPreOrder->pre_order_number}</p>
                        <p><strong>Product:</strong> " . ($customerPreOrder->product_name ?? 'N/A') . "</p>
                        <p><strong>Quantity:</strong> {$customerPreOrder->quantity}</p>
                        " . ($mode === 'balance' && $customerPreOrder->remaining_amount > 0 ? 
                            "<p><strong>Remaining Balance:</strong> <span style='color: #dc2626; font-weight: bold;'>" . number_format($customerPreOrder->remaining_amount, 2) . " {$customerPreOrder->currency}</span></p>" : '') . "
                    </div>
                    
                    " . ($mode === 'balance' ? 
                        "<div style='text-align: center; margin: 30px 0;'>
                            <a href='#' class='button'>💳 Complete Payment Now</a>
                        </div>" : '') . "
                    
                    <div style='background: #e8f4fd; padding: 15px; border-radius: 5px; text-align: center; margin-top: 20px;'>
                        <p><strong>Need help?</strong> Our support team is ready to assist you.</p>
                    </div>
                </div>
                <div class='footer'>
                    <p><strong>⚡ {$brandName}</strong></p>
                    <p>Thank you for choosing {$brandName}! We're committed to delivering quality products.</p>
                    <p>&copy; " . date('Y') . " {$brandName}. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Validate email configuration
     */
    public function isConfigured(): bool
    {
        return !empty(config('mail.from.address')) && 
               !empty(config('mail.mailers.smtp.host'));
    }
}