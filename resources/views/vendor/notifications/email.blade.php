<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - {{ config('app.name', 'Gifamz Store') }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #fff5eb 0%, #fff9f0 50%, #fffbf5 100%);">
    <table role="presentation" style="width: 100%; border-collapse: collapse; background: linear-gradient(135deg, #fff5eb 0%, #fff9f0 50%, #fffbf5 100%); padding: 40px 20px;">
        <tr>
            <td align="center">
                <!-- Main Container -->
                <table role="presentation" style="width: 100%; max-width: 600px; border-collapse: collapse; background: #ffffff; border-radius: 24px; box-shadow: 0 20px 60px rgba(251, 146, 60, 0.15); overflow: hidden;">
                    
                    <!-- Header with Solar Theme -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #f97316 0%, #fb923c 50%, #fbbf24 100%); padding: 40px 30px; text-align: center; position: relative;">
                            <!-- Decorative circles -->
                            <div style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; background: rgba(255, 255, 255, 0.1); border-radius: 50%; filter: blur(40px);"></div>
                            <div style="position: absolute; bottom: -30px; left: -30px; width: 120px; height: 120px; background: rgba(255, 255, 255, 0.1); border-radius: 50%; filter: blur(40px);"></div>
                            
                            <!-- Sun Icon -->
                            <div style="margin-bottom: 20px; position: relative; z-index: 1;">
                                <div style="display: inline-block; width: 80px; height: 80px; background: rgba(255, 255, 255, 0.3); backdrop-filter: blur(10px); border-radius: 50%; padding: 15px; box-shadow: 0 8px 32px rgba(255, 255, 255, 0.3);">
                                    <svg width="50" height="50" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: block; margin: auto;">
                                        <circle cx="12" cy="12" r="5" fill="white"/>
                                        <path d="M12 1v3M12 20v3M22 12h-3M5 12H2M19.07 4.93l-2.12 2.12M7.05 16.95l-2.12 2.12M19.07 19.07l-2.12-2.12M7.05 7.05L4.93 4.93" stroke="white" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                </div>
                            </div>
                            
                            <h1 style="margin: 0; color: #ffffff; font-size: 32px; font-weight: 700; text-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); position: relative; z-index: 1;">
                                {{ config('app.name', 'Gifamz Store') }}
                            </h1>
                            <p style="margin: 8px 0 0 0; color: rgba(255, 255, 255, 0.95); font-size: 16px; position: relative; z-index: 1;">
                                Powering Your Future with Clean Energy
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 50px 40px;">
                            <h2 style="margin: 0 0 20px 0; color: #1f2937; font-size: 28px; font-weight: 700; text-align: center;">
                                Welcome to Our Solar Family! ☀️
                            </h2>
                            
                            <p style="margin: 0 0 25px 0; color: #4b5563; font-size: 16px; line-height: 1.6; text-align: center;">
                                Thank you for joining {{ config('app.name', 'Gifamz Store') }}! We're excited to have you on board as we work together towards a sustainable energy future.
                            </p>
                            
                            <p style="margin: 0 0 30px 0; color: #4b5563; font-size: 16px; line-height: 1.6; text-align: center;">
                                To get started, please verify your email address by clicking the button below:
                            </p>
                            
                            <!-- Verification Button -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; margin: 0 0 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $actionUrl }}" style="display: inline-block; background: linear-gradient(135deg, #f97316 0%, #fb923c 50%, #fbbf24 100%); color: #ffffff; text-decoration: none; padding: 18px 50px; border-radius: 16px; font-size: 18px; font-weight: 700; box-shadow: 0 10px 30px rgba(251, 146, 60, 0.4); transition: all 0.3s ease;">
                                            ⚡ Verify Email Address
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Benefits Section -->
                            <div style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border-radius: 16px; padding: 25px; margin: 30px 0; border-left: 4px solid #f97316;">
                                <h3 style="margin: 0 0 15px 0; color: #ea580c; font-size: 18px; font-weight: 700;">
                                    🎉 What's Next?
                                </h3>
                                <ul style="margin: 0; padding: 0 0 0 20px; color: #78350f;">
                                    <li style="margin-bottom: 10px; line-height: 1.6;">Browse our premium solar panels, inverters, and batteries</li>
                                    <li style="margin-bottom: 10px; line-height: 1.6;">Get expert advice on the best solar solutions for your needs</li>
                                    <li style="margin-bottom: 10px; line-height: 1.6;">Enjoy exclusive discounts on your first purchase</li>
                                    <li style="margin-bottom: 0; line-height: 1.6;">Track your orders and manage your account easily</li>
                                </ul>
                            </div>
                            
                            <!-- Info Box -->
                            <div style="background: #f3f4f6; border-radius: 12px; padding: 20px; margin: 25px 0;">
                                <p style="margin: 0; color: #6b7280; font-size: 14px; line-height: 1.6;">
                                    <strong style="color: #374151;">🔒 Security Note:</strong> If you did not create an account with {{ config('app.name', 'Gifamz Store') }}, please ignore this email. No further action is required, and your information remains secure.
                                </p>
                            </div>
                            
                            <p style="margin: 0 0 10px 0; color: #6b7280; font-size: 14px; line-height: 1.6; text-align: center;">
                                This verification link will expire in {{ config('auth.verification.expire', 60) }} minutes.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Alternative Link Section -->
                    <tr>
                        <td style="padding: 0 40px 40px 40px;">
                            <div style="background: #fef3c7; border: 1px dashed #fbbf24; border-radius: 12px; padding: 20px;">
                                <p style="margin: 0 0 10px 0; color: #92400e; font-size: 13px; font-weight: 600;">
                                    ⚠️ Having trouble with the button?
                                </p>
                                <p style="margin: 0 0 10px 0; color: #78350f; font-size: 13px; line-height: 1.5;">
                                    Copy and paste this URL into your browser:
                                </p>
                                <div style="background: #ffffff; border-radius: 8px; padding: 12px; word-break: break-all;">
                                    <a href="{{ $actionUrl }}" style="color: #f97316; text-decoration: none; font-size: 12px; font-family: monospace;">
                                        {{ $actionUrl }}
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #1f2937 0%, #111827 100%); padding: 40px; text-align: center;">
                            <!-- Social Icons -->
                            <div style="margin-bottom: 20px;">
                                <a href="#" style="display: inline-block; margin: 0 8px; text-decoration: none;">
                                    <div style="width: 40px; height: 40px; background: rgba(251, 146, 60, 0.2); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;">
                                        <span style="color: #fb923c; font-size: 18px;">f</span>
                                    </div>
                                </a>
                                <a href="#" style="display: inline-block; margin: 0 8px; text-decoration: none;">
                                    <div style="width: 40px; height: 40px; background: rgba(251, 146, 60, 0.2); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;">
                                        <span style="color: #fb923c; font-size: 18px;">𝕏</span>
                                    </div>
                                </a>
                                <a href="#" style="display: inline-block; margin: 0 8px; text-decoration: none;">
                                    <div style="width: 40px; height: 40px; background: rgba(251, 146, 60, 0.2); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;">
                                        <span style="color: #fb923c; font-size: 18px;">in</span>
                                    </div>
                                </a>
                            </div>
                            
                            <p style="margin: 0 0 15px 0; color: #d1d5db; font-size: 14px;">
                                <strong style="color: #ffffff;">{{ config('app.name', 'Gifamz Store') }}</strong><br>
                                Nigeria's #1 Solar Energy Solutions Provider
                            </p>
                            
                            <p style="margin: 0 0 15px 0; color: #9ca3af; font-size: 13px; line-height: 1.6;">
                                📧 support@gifamz.com | 📞 +234 800 G-Tech<br>
                                📍 Lagos, Nigeria
                            </p>
                            
                            <div style="border-top: 1px solid rgba(255, 255, 255, 0.1); padding-top: 20px; margin-top: 20px;">
                                <p style="margin: 0 0 10px 0; color: #6b7280; font-size: 12px;">
                                    © {{ date('Y') }} {{ config('app.name', 'Gifamz Store') }}. All rights reserved.
                                </p>
                                <p style="margin: 0; font-size: 11px;">
                                    <a href="#" style="color: #fb923c; text-decoration: none; margin: 0 8px;">Privacy Policy</a>
                                    <span style="color: #4b5563;">|</span>
                                    <a href="#" style="color: #fb923c; text-decoration: none; margin: 0 8px;">Terms of Service</a>
                                    <span style="color: #4b5563;">|</span>
                                    <a href="#" style="color: #fb923c; text-decoration: none; margin: 0 8px;">Unsubscribe</a>
                                </p>
                            </div>
                            
                            <div style="margin-top: 20px; padding: 15px; background: rgba(251, 146, 60, 0.1); border-radius: 8px; border: 1px solid rgba(251, 146, 60, 0.2);">
                                <p style="margin: 0; color: #fbbf24; font-size: 13px; font-weight: 600;">
                                    ☀️ Powered by 100% Renewable Solar Energy
                                </p>
                            </div>
                        </td>
                    </tr>
                    
                </table>
                
                <!-- Bottom Spacer -->
                <div style="height: 40px;"></div>
                
            </td>
        </tr>
    </table>
</body>
</html>
