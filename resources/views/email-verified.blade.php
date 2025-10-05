<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified - G-Tech</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #fff5eb 0%, #fff9f0 50%, #fffbf5 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
            position: relative;
            overflow-x: hidden;
            overflow-y: auto;
        }

        /* Animated background elements */
        .bg-decoration {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.3;
            animation: float 6s ease-in-out infinite;
        }

        .bg-decoration:nth-child(1) {
            width: 400px;
            height: 400px;
            background: #fbbf24;
            top: -100px;
            left: -100px;
            animation-delay: 0s;
        }

        .bg-decoration:nth-child(2) {
            width: 500px;
            height: 500px;
            background: #fb923c;
            bottom: -150px;
            right: -150px;
            animation-delay: 2s;
        }

        .bg-decoration:nth-child(3) {
            width: 350px;
            height: 350px;
            background: #f97316;
            top: 50%;
            left: -100px;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) scale(1);
            }
            50% {
                transform: translateY(-30px) scale(1.1);
            }
        }

        /* Floating solar icons */
        .floating-icon {
            position: absolute;
            opacity: 0.1;
            animation: spin 30s linear infinite;
        }

        .floating-icon:nth-child(4) {
            top: 10%;
            right: 10%;
            width: 60px;
            height: 60px;
            animation-duration: 40s;
        }

        .floating-icon:nth-child(5) {
            bottom: 15%;
            left: 8%;
            width: 80px;
            height: 80px;
            animation-duration: 35s;
            animation-direction: reverse;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 32px;
            box-shadow: 0 30px 80px rgba(251, 146, 60, 0.2);
            padding: 60px 50px;
            text-align: center;
            max-width: 650px;
            width: 100%;
            position: relative;
            z-index: 10;
            border: 1px solid rgba(251, 146, 60, 0.1);
            animation: slideUp 0.6s ease-out;
            margin: 40px auto;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Success checkmark animation */
        .icon-container {
            position: relative;
            width: 140px;
            height: 140px;
            margin: 0 auto 40px;
        }

        .icon-bg {
            width: 140px;
            height: 140px;
            background: linear-gradient(135deg, #f97316 0%, #fb923c 50%, #fbbf24 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 15px 40px rgba(251, 146, 60, 0.4);
            animation: pulse 2s ease-in-out infinite;
            position: relative;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 15px 40px rgba(251, 146, 60, 0.4);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 20px 50px rgba(251, 146, 60, 0.5);
            }
        }

        .icon-bg::before {
            content: '';
            position: absolute;
            inset: -10px;
            background: linear-gradient(135deg, #f97316 0%, #fbbf24 100%);
            border-radius: 50%;
            opacity: 0.3;
            filter: blur(20px);
            z-index: -1;
            animation: glow 2s ease-in-out infinite;
        }

        @keyframes glow {
            0%, 100% {
                opacity: 0.3;
            }
            50% {
                opacity: 0.6;
            }
        }

        .checkmark {
            width: 70px;
            height: 70px;
            fill: white;
            animation: checkmarkDraw 0.8s ease-out 0.3s both;
        }

        @keyframes checkmarkDraw {
            from {
                transform: scale(0) rotate(-45deg);
                opacity: 0;
            }
            to {
                transform: scale(1) rotate(0deg);
                opacity: 1;
            }
        }

        /* Confetti particles */
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            background: #fbbf24;
            animation: confettiFall 3s ease-out infinite;
        }

        .confetti:nth-child(1) { left: 10%; animation-delay: 0s; background: #f97316; }
        .confetti:nth-child(2) { left: 25%; animation-delay: 0.3s; background: #fb923c; }
        .confetti:nth-child(3) { left: 40%; animation-delay: 0.6s; background: #fbbf24; }
        .confetti:nth-child(4) { left: 55%; animation-delay: 0.9s; background: #f97316; }
        .confetti:nth-child(5) { left: 70%; animation-delay: 1.2s; background: #fb923c; }
        .confetti:nth-child(6) { left: 85%; animation-delay: 1.5s; background: #fbbf24; }

        @keyframes confettiFall {
            0% {
                top: -10%;
                transform: rotate(0deg);
                opacity: 1;
            }
            100% {
                top: 110%;
                transform: rotate(360deg);
                opacity: 0;
            }
        }

        h1 {
            color: #1f2937;
            font-size: 42px;
            font-weight: 800;
            margin: 0 0 20px 0;
            line-height: 1.2;
            animation: fadeIn 0.6s ease-out 0.3s both;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-emoji {
            font-size: 48px;
            display: block;
            margin-bottom: 15px;
            animation: bounce 1s ease-in-out 0.5s both;
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            25% {
                transform: translateY(-10px);
            }
            50% {
                transform: translateY(0);
            }
            75% {
                transform: translateY(-5px);
            }
        }

        .subtitle {
            color: #6b7280;
            font-size: 20px;
            line-height: 1.6;
            margin: 0 0 25px 0;
            animation: fadeIn 0.6s ease-out 0.5s both;
        }

        p {
            color: #4b5563;
            font-size: 18px;
            line-height: 1.8;
            margin: 0 0 35px 0;
            animation: fadeIn 0.6s ease-out 0.7s both;
        }

        /* Highlight Box */
        .highlight-box {
            background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
            border: 2px solid #fed7aa;
            border-radius: 20px;
            padding: 25px;
            margin: 35px 0;
            text-align: left;
            animation: fadeIn 0.6s ease-out 0.9s both;
        }

        .highlight-box h3 {
            color: #ea580c;
            font-size: 20px;
            font-weight: 700;
            margin: 0 0 15px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .highlight-box ul {
            margin: 0;
            padding: 0 0 0 20px;
            color: #78350f;
            font-size: 16px;
        }

        .highlight-box li {
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .highlight-box li:last-child {
            margin-bottom: 0;
        }

        /* Login Button */
        .login-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background: linear-gradient(135deg, #f97316 0%, #fb923c 50%, #fbbf24 100%);
            color: #ffffff;
            text-decoration: none;
            padding: 20px 50px;
            border-radius: 18px;
            font-size: 20px;
            font-weight: 700;
            box-shadow: 0 12px 35px rgba(251, 146, 60, 0.4);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-top: 40px;
            animation: fadeIn 0.6s ease-out 1.1s both;
        }

        .login-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .login-button:hover::before {
            left: 100%;
        }

        .login-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 18px 45px rgba(251, 146, 60, 0.5);
        }

        .login-button:active {
            transform: translateY(-1px);
        }

        .arrow-icon {
            transition: transform 0.3s ease;
        }

        .login-button:hover .arrow-icon {
            transform: translateX(5px);
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid rgba(251, 146, 60, 0.2);
            animation: fadeIn 0.6s ease-out 1.3s both;
        }

        .footer-text {
            color: #9ca3af;
            font-size: 15px;
            margin-bottom: 15px;
        }

        .footer-solar {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #f97316;
            font-weight: 600;
            font-size: 15px;
        }

        @media (max-width: 640px) {
            body {
                padding: 20px 15px;
            }

            .container {
                padding: 40px 25px;
                border-radius: 24px;
                margin: 20px auto;
            }

            h1 {
                font-size: 28px;
                line-height: 1.3;
            }

            .subtitle {
                font-size: 17px;
            }

            p {
                font-size: 15px;
            }

            .icon-container {
                width: 110px;
                height: 110px;
                margin: 0 auto 30px;
            }

            .icon-bg {
                width: 110px;
                height: 110px;
            }

            .checkmark {
                width: 55px;
                height: 55px;
            }

            .success-emoji {
                font-size: 38px;
                margin-bottom: 12px;
            }

            .login-button {
                padding: 16px 30px;
                font-size: 17px;
                margin-top: 30px;
                width: 100%;
            }

            .highlight-box {
                padding: 20px;
                border-radius: 16px;
                margin: 25px 0;
            }

            .highlight-box h3 {
                font-size: 18px;
            }

            .highlight-box ul {
                font-size: 14px;
            }

            .highlight-box li {
                margin-bottom: 8px;
            }

            .footer {
                margin-top: 30px;
                padding-top: 25px;
            }

            .footer-text {
                font-size: 14px;
            }

            .footer-solar {
                font-size: 14px;
            }

            /* Hide floating decorations on mobile for better performance */
            .floating-icon {
                display: none;
            }

            .bg-decoration:nth-child(3) {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 30px 20px;
            }

            h1 {
                font-size: 24px;
            }

            .subtitle {
                font-size: 16px;
            }

            .icon-container {
                width: 90px;
                height: 90px;
            }

            .icon-bg {
                width: 90px;
                height: 90px;
            }

            .checkmark {
                width: 45px;
                height: 45px;
            }
        }
    </style>
</head>
<body>
    <!-- Background decorations -->
    <div class="bg-decoration"></div>
    <div class="bg-decoration"></div>
    <div class="bg-decoration"></div>

    <!-- Floating icons -->
    <svg class="floating-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <circle cx="12" cy="12" r="5" fill="#f97316"/>
        <path d="M12 1v3M12 20v3M22 12h-3M5 12H2M19.07 4.93l-2.12 2.12M7.05 16.95l-2.12 2.12M19.07 19.07l-2.12-2.12M7.05 7.05L4.93 4.93" stroke="#f97316" stroke-width="2" stroke-linecap="round"/>
    </svg>

    <svg class="floating-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M13 2L3 14h8l-1 8 10-12h-8l1-8z" fill="#fbbf24"/>
    </svg>

    <div class="container">
        <!-- Confetti -->
        <div class="confetti"></div>
        <div class="confetti"></div>
        <div class="confetti"></div>
        <div class="confetti"></div>
        <div class="confetti"></div>
        <div class="confetti"></div>

        <!-- Success Icon -->
        <div class="icon-container">
            <div class="icon-bg">
                <svg class="checkmark" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                </svg>
            </div>
        </div>

        <span class="success-emoji">🎉</span>
        <h1>Email Verified Successfully!</h1>
        <p class="subtitle">Welcome to G-Tech</p>
        <p>Your email address has been confirmed. You're now part of Nigeria's leading solar energy community, powering a sustainable future together!</p>

        <!-- Highlight Box -->
        <div class="highlight-box">
            <h3>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="5" fill="currentColor"/>
                    <path d="M12 1v3M12 20v3M22 12h-3M5 12H2M19.07 4.93l-2.12 2.12M7.05 16.95l-2.12 2.12M19.07 19.07l-2.12-2.12M7.05 7.05L4.93 4.93" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                What's Waiting for You:
            </h3>
            <ul>
                <li><strong>Premium Solar Products</strong> - Browse our extensive collection of panels, inverters, batteries & more</li>
                <li><strong>Expert Guidance</strong> - Get personalized recommendations for your energy needs</li>
                <li><strong>Exclusive Discounts</strong> - Enjoy member-only deals and special offers</li>
                <li><strong>Order Tracking</strong> - Manage your purchases and track deliveries in real-time</li>
            </ul>
        </div>

        <!-- Login Button -->
        <a href="http://localhost:8080/auth" class="login-button">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Login to Continue
            <svg class="arrow-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>

        <!-- Footer -->
        <div class="footer">
            <p class="footer-text">Thank you for choosing G-Tech</p>
            <div class="footer-solar">
                <svg width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="5" fill="currentColor"/>
                    <path d="M12 1v3M12 20v3M22 12h-3M5 12H2M19.07 4.93l-2.12 2.12M7.05 16.95l-2.12 2.12M19.07 19.07l-2.12-2.12M7.05 7.05L4.93 4.93" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Powered by 100% Renewable Energy
            </div>
        </div>
    </div>
</body>
</html>