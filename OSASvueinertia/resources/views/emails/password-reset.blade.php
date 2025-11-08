<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset - LSPU ORBIT</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }
        .logo {
            width: 80px;
            height: 80px;
            margin-bottom: 15px;
        }
        .title {
            color: #2563eb;
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }
        .subtitle {
            color: #6b7280;
            font-size: 16px;
            margin: 5px 0 0 0;
        }
        .content {
            margin-bottom: 30px;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff !important;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            margin: 20px 0;
            transition: all 0.3s ease;
            border: none;
            font-size: 16px;
            line-height: 1.5;
        }
        .button:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            transform: translateY(-1px);
            color: #ffffff !important;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            color: #6b7280;
            font-size: 14px;
        }
        .warning {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #92400e;
        }
        .info {
            background-color: #dbeafe;
            border: 1px solid #3b82f6;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #1e40af;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('images/lspu_logo_better.webp') }}" alt="LSPU Logo" class="logo">
            <h1 class="title">Password Reset Request</h1>
            <p class="subtitle">LSPU ORBIT System</p>
        </div>

        <div class="content">
            <p>Hello <strong>{{ $user->name }}</strong>,</p>
            
            <p>We received a request to reset your password for your LSPU ORBIT account. If you didn't make this request, you can safely ignore this email.</p>

            <div class="info">
                <strong>Account Details:</strong><br>
                Email: {{ $user->email }}<br>
                Organization: {{ $user->organization_name ?? 'N/A' }}
            </div>

            <p>To reset your password, click the button below:</p>

            <div style="text-align: center;">
                <a href="{{ $url }}" class="button">Reset Password</a>
            </div>

            <div class="warning">
                <strong>⚠️ Security Notice:</strong><br>
                This password reset link will expire in 60 minutes for security reasons. If you don't reset your password within this time, you'll need to request a new link.
            </div>

            <p>If the button above doesn't work, you can copy and paste the following link into your browser:</p>
            <p style="word-break: break-all; color: #2563eb;">{{ $url }}</p>

            <p>If you have any questions or need assistance, please contact your system administrator.</p>
        </div>

        <div class="footer">
            <p><strong>Laguna State Polytechnic University</strong></p>
            <p>ORBIT - Organization Recognition and Business Information Technology</p>
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>© 2025 LSPU. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
