<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #2563eb; color: white; padding: 20px; border-radius: 8px 8px 0 0; }
        .header h1 { margin: 0; font-size: 20px; }
        .body { background: #f9fafb; padding: 20px; border: 1px solid #e5e7eb; }
        .field { margin-bottom: 16px; }
        .label { font-weight: bold; color: #374151; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
        .value { margin-top: 4px; padding: 10px; background: white; border-radius: 6px; border: 1px solid #e5e7eb; }
        .category-badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; background: #dbeafe; color: #1e40af; }
        .message-box { white-space: pre-wrap; }
        .footer { padding: 16px 20px; background: #f3f4f6; border-radius: 0 0 8px 8px; border: 1px solid #e5e7eb; border-top: none; font-size: 12px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📬 New Feedback from ORBIT</h1>
    </div>
    <div class="body">
        <div class="field">
            <div class="label">From</div>
            <div class="value">{{ $senderName }} &lt;{{ $senderEmail }}&gt;</div>
        </div>
        <div class="field">
            <div class="label">Category</div>
            <div class="value"><span class="category-badge">{{ $category }}</span></div>
        </div>
        <div class="field">
            <div class="label">Subject</div>
            <div class="value">{{ $feedbackSubject }}</div>
        </div>
        <div class="field">
            <div class="label">Message</div>
            <div class="value message-box">{{ $feedbackMessage }}</div>
        </div>
    </div>
    <div class="footer">
        This message was sent via the ORBIT Contact &amp; Feedback page. Reply directly to respond to the sender.
    </div>
</body>
</html>
