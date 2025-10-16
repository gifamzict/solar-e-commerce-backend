<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $subject }} - {{ $brandName }}</title>
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
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 3s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.3; }
        }
        .logo-icon {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            font-size: 40px;
            position: relative;
            z-index: 1;
        }
        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 1;
        }
        .header p {
            font-size: 16px;
            opacity: 0.95;
            position: relative;
            z-index: 1;
        }
        .content {
            padding: 40px 30px;
        }
        .status-card {
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 30px;
            text-align: center;
            border: 2px solid;
        }
        .status-card.balance {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-color: #f59e0b;
        }
        .status-card.ready {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border-color: #86efac;
        }
        .status-card.general {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-color: #60a5fa;
        }
        .status-card h2 {
            font-size: 24px;
            margin-bottom: 12px;
            font-weight: 700;
        }
        .status-card.balance h2 {
            color: #92400e;
        }
        .status-card.ready h2 {
            color: #166534;
        }
        .status-card.general h2 {
            color: #1e40af;
        }
        .status-card p {
            font-size: 15px;
            line-height: 1.6;
        }
        .status-card.balance p {
            color: #78350f;
        }
        .status-card.ready p {
            color: #15803d;
        }
        .status-card.general p {
            color: #1e40af;
        }
        .greeting {
            font-size: 16px;
            color: #1e293b;
            margin-bottom: 20px;
        }
        .main-text {
            font-size: 15px;
            color: #475569;
            line-height: 1.8;
            margin-bottom: 30px;
            white-space: pre-line;
        }
        .order-details {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            padding: 24px;
            margin: 24px 0;
        }
        .order-details h3 {
            font-size: 18px;
            color: #0f172a;
            margin-bottom: 16px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
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
            font-weight: 700;
            color: #1e293b;
            font-size: 14px;
            text-align: right;
        }
        .amount {
            color: #dc2626;
            font-size: 16px;
        }
        .cta-section {
            text-align: center;
            margin: 30px 0;
            padding: 30px 20px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 12px;
            border: 2px solid #e2e8f0;
        }
        .cta-button {
            display: inline-block;
            padding: 16px 40px;
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 16px;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
            transition: transform 0.2s, box-shadow 0.2s;
            letter-spacing: 0.3px;
        }
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(5, 150, 105, 0.4);
        }
        .deadline-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            color: #dc2626;
            margin-top: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .fulfillment-info {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-left: 4px solid #3b82f6;
            border-radius: 8px;
            padding: 20px;
            margin: 24px 0;
        }
        .fulfillment-info h3 {
            font-size: 16px;
            color: #1e40af;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
        }
        .fulfillment-info p {
            color: #1e40af;
            font-size: 14px;
            margin: 8px 0;
            line-height: 1.6;
        }
        .divider {
            height: 2px;
            background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
            margin: 30px 0;
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
            color: #059669;
            text-decoration: none;
            margin: 0 12px;
            font-size: 13px;
            font-weight: 500;
        }
        .copyright {
            margin-top: 16px;
            font-size: 12px;
            color: #94a3b8;
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
            .logo-icon {
                width: 70px;
                height: 70px;
                font-size: 35px;
            }
            .status-card h2 {
                font-size: 20px;
            }
            .cta-button {
                padding: 14px 32px;
                font-size: 15px;
            }
            .cta-section {
                padding: 24px 16px;
            }
            .detail-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
            }
            .detail-value {
                text-align: left;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="logo-icon">⚡</div>
            <h1>{{ $brandName }}</h1>
            <p>Your Pre-Order Update</p>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Status Card -->
            @php
                $statusClass = match($notificationData['mode']) {
                    'balance' => 'balance',
                    'ready' => 'ready',
                    default => 'general'
                };
                
                $statusTitle = match($notificationData['mode']) {
                    'balance' => '💳 Payment Required',
                    'ready' => '🎉 Your Order is Ready!',
                    default => '📋 Order Update'
                };
                
                $statusMessage = match($notificationData['mode']) {
                    'balance' => 'Please complete your payment to proceed with your pre-order.',
                    'ready' => 'Great news! Your pre-order is ready for ' . $notificationData['fulfillment_method'] . '.',
                    default => 'We have an important update about your pre-order.'
                };
            @endphp
            
            <div class="status-card {{ $statusClass }}">
                <h2>{{ $statusTitle }}</h2>
                <p>{{ $statusMessage }}</p>
            </div>

            <p class="greeting">Hello {{ $customerPreOrder->full_name }}! 👋</p>
            
            <div class="main-text">{{ $resolvedMessage }}</div>

            <!-- Order Details -->
            <div class="order-details">
                <h3>
                    <span>📦</span>
                    Pre-Order Details
                </h3>
                <div class="detail-row">
                    <span class="detail-label">Order Number:</span>
                    <span class="detail-value">{{ $customerPreOrder->pre_order_number }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Product:</span>
                    <span class="detail-value">{{ $customerPreOrder->product_name ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Quantity:</span>
                    <span class="detail-value">{{ $customerPreOrder->quantity }}</span>
                </div>
                @if($notificationData['mode'] === 'balance' && $customerPreOrder->remaining_amount > 0)
                <div class="detail-row">
                    <span class="detail-label">Remaining Balance:</span>
                    <span class="detail-value amount">{{ number_format($customerPreOrder->remaining_amount, 2) }} {{ $customerPreOrder->currency }}</span>
                </div>
                @endif
                @if(!empty($notificationData['ready_date']))
                <div class="detail-row">
                    <span class="detail-label">Ready Date:</span>
                    <span class="detail-value">{{ $notificationData['ready_date'] }}</span>
                </div>
                @endif
            </div>

            @if($notificationData['mode'] === 'balance')
                <!-- Payment CTA -->
                <div class="cta-section">
                    <a href="#" class="cta-button">
                        💳 Complete Payment Now
                    </a>
                    @if(!empty($notificationData['payment_deadline']))
                    <div class="deadline-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Payment due by {{ $notificationData['payment_deadline'] }}
                    </div>
                    @endif
                </div>
            @endif

            @if($notificationData['mode'] === 'ready')
                <!-- Fulfillment Info -->
                <div class="fulfillment-info">
                    <h3>
                        @if($notificationData['fulfillment_method'] === 'pickup')
                            <span>📍</span> Pickup Information
                        @else
                            <span>🚚</span> Delivery Information
                        @endif
                    </h3>
                    
                    @if($notificationData['fulfillment_method'] === 'pickup')
                        <p><strong>Pickup Location:</strong></p>
                        <p>{{ $notificationData['pickup_location'] ?? $customerPreOrder->pickup_location ?? 'To be confirmed' }}</p>
                    @else
                        <p><strong>Delivery Address:</strong></p>
                        <p>{{ $notificationData['shipping_address'] ?? $customerPreOrder->shipping_address ?? 'To be confirmed' }}</p>
                        @if(!empty($notificationData['city']) || !empty($customerPreOrder->city))
                        <p>{{ $notificationData['city'] ?? $customerPreOrder->city }}, {{ $notificationData['state'] ?? $customerPreOrder->state }}</p>
                        @endif
                    @endif
                </div>

                <!-- Ready CTA -->
                <div class="cta-section">
                    <a href="#" class="cta-button">
                        @if($notificationData['fulfillment_method'] === 'pickup')
                            📍 View Pickup Details
                        @else
                            🚚 Track Your Delivery
                        @endif
                    </a>
                </div>
            @endif

            @if(!empty($notificationData['reason']))
                <div class="fulfillment-info">
                    <h3>
                        <span>ℹ️</span> Additional Information
                    </h3>
                    <p>{{ $notificationData['reason'] }}</p>
                </div>
            @endif

            <div class="divider"></div>

            <!-- Support Message -->
            <div style="background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); border-radius: 8px; padding: 20px; text-align: center; margin-top: 24px;">
                <p style="color: #374151; font-size: 14px; margin: 0;">
                    <strong>Need help?</strong> Our support team is ready to assist you with your pre-order.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p class="footer-brand">⚡ {{ $brandName }}</p>
            <p>Thank you for choosing {{ $brandName }}! We're committed to delivering quality products.</p>
            
            <div class="footer-links">
                <a href="#">Help Center</a>
                <a href="#">Contact Support</a>
                <a href="#">My Account</a>
            </div>
            
            <p class="copyright">
                © {{ date('Y') }} {{ $brandName }}. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>