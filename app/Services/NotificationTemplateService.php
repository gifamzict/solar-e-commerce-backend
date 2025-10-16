<?php

namespace App\Services;

use App\Models\CustomerPreOrder;
use NumberFormatter;

class NotificationTemplateService
{
    /**
     * Resolve message template with merge tags
     */
    public function resolveTemplate(
        string $template,
        CustomerPreOrder $customerPreOrder,
        array $notificationData
    ): array {
        $customerPreOrder->load('preOrder');

        // Prepare merge tags data
        $mergeTags = $this->prepareMergeTags($customerPreOrder, $notificationData);

        // Resolve template for email and SMS
        $resolvedEmail = $this->resolveMergeTags($template, $mergeTags);
        $resolvedSms = $this->resolveMergeTags($template, $mergeTags);

        return [
            'email' => $resolvedEmail,
            'sms' => $resolvedSms,
        ];
    }

    /**
     * Prepare merge tags data from customer pre-order and notification data
     */
    private function prepareMergeTags(CustomerPreOrder $customerPreOrder, array $notificationData): array
    {
        $formatter = new NumberFormatter('en_NG', NumberFormatter::CURRENCY);
        
        return [
            '{{customer_name}}' => $customerPreOrder->full_name,
            '{{product_name}}' => $customerPreOrder->preOrder->product_name ?? '-',
            '{{pre_order_number}}' => $customerPreOrder->pre_order_number,
            '{{quantity}}' => (string) $customerPreOrder->quantity,
            '{{remaining_amount}}' => $formatter->formatCurrency(
                $customerPreOrder->remaining_amount, 
                $customerPreOrder->currency
            ),
            '{{currency}}' => $customerPreOrder->currency,
            '{{payment_deadline}}' => $notificationData['payment_deadline'] ?? '-',
            '{{reason}}' => $notificationData['reason'] ?? '-',
            '{{fulfillment_method}}' => $notificationData['fulfillment_method'],
            '{{pickup_location}}' => $notificationData['pickup_location'] ?? $customerPreOrder->pickup_location ?? '-',
            '{{shipping_address}}' => $notificationData['shipping_address'] ?? $customerPreOrder->shipping_address ?? '-',
            '{{shipping_city}}' => $notificationData['city'] ?? $customerPreOrder->city ?? '-',
            '{{shipping_state}}' => $notificationData['state'] ?? $customerPreOrder->state ?? '-',
            '{{ready_date}}' => $notificationData['ready_date'] ?? '-',
        ];
    }

    /**
     * Replace merge tags in template
     */
    private function resolveMergeTags(string $template, array $mergeTags): string
    {
        $resolved = $template;

        foreach ($mergeTags as $tag => $value) {
            $resolved = str_replace($tag, $value, $resolved);
        }

        // Add mandatory payment instruction for balance mode
        if (str_contains($template, '{{payment_deadline}}') || str_contains($template, '{{reason}}')) {
            if (!str_contains($resolved, 'log in to your G-Tech Solar app')) {
                $resolved .= "\n\nPlease log in to your G-Tech Solar app/account to complete your payment.";
            }
        }

        return $resolved;
    }

    /**
     * Get available merge tags for documentation
     */
    public function getAvailableMergeTags(): array
    {
        return [
            '{{customer_name}}' => 'Customer first and last name',
            '{{product_name}}' => 'Pre-order product name',
            '{{pre_order_number}}' => 'Pre-order number',
            '{{quantity}}' => 'Order quantity',
            '{{remaining_amount}}' => 'Formatted remaining balance',
            '{{currency}}' => 'Currency code (e.g., NGN)',
            '{{payment_deadline}}' => 'Payment deadline (balance mode)',
            '{{reason}}' => 'Reason for balance request (balance mode)',
            '{{fulfillment_method}}' => 'pickup or delivery',
            '{{pickup_location}}' => 'Pickup location (pickup mode)',
            '{{shipping_address}}' => 'Shipping address (delivery mode)',
            '{{shipping_city}}' => 'Shipping city (delivery mode)',
            '{{shipping_state}}' => 'Shipping state (delivery mode)',
            '{{ready_date}}' => 'Ready date (ready mode)',
        ];
    }
}