<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Alert</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 20px; color: #1e293b; }
        .container { max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; }
        .header { background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); padding: 30px 24px; text-align: center; color: #ffffff; }
        .badge { display: inline-block; background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; }
        .title { margin: 0; font-size: 22px; font-weight: 800; line-height: 1.3; }
        .content { padding: 30px 24px; }
        .alert-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 16px; margin-bottom: 24px; color: #991b1b; font-size: 14px; line-height: 1.5; }
        .card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; margin-bottom: 26px; }
        .row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px dashed #e2e8f0; font-size: 13px; }
        .row:last-child { border-bottom: none; }
        .label { color: #64748b; font-weight: 600; }
        .val { color: #0f172a; font-weight: 700; text-align: right; max-width: 65%; word-break: break-all; }
        .ip-highlight { font-family: monospace; background: #fee2e2; color: #b91c1c; padding: 2px 6px; border-radius: 4px; font-weight: 800; }
        .btn-container { text-align: center; margin: 28px 0 16px; }
        .block-btn { display: inline-block; background: #dc2626; color: #ffffff !important; text-decoration: none; font-size: 15px; font-weight: 700; padding: 14px 28px; border-radius: 12px; box-shadow: 0 4px 14px rgba(220, 38, 38, 0.4); text-align: center; }
        .footer { background: #f1f5f9; padding: 20px 24px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="badge">Security Warning</span>
            <h1 class="title">Failed Admin Login Detected</h1>
        </div>
        <div class="content">
            <div class="alert-box">
                <strong>Attention Required:</strong> Someone has entered incorrect admin login credentials <strong>{{ $attempts }} times</strong> consecutively from the IP address below.
            </div>

            <div class="card">
                <table style="width: 100%; border-collapse: collapse; font-size: 13.5px;">
                    <tr style="border-bottom: 1px dashed #e2e8f0;">
                        <td style="padding: 10px 0; color: #64748b; font-weight: 600;">IP Address:</td>
                        <td style="padding: 10px 0; text-align: right;"><span class="ip-highlight">{{ $ip }}</span></td>
                    </tr>
                    <tr style="border-bottom: 1px dashed #e2e8f0;">
                        <td style="padding: 10px 0; color: #64748b; font-weight: 600;">Approx. Location:</td>
                        <td style="padding: 10px 0; text-align: right; color: #0f172a; font-weight: 600;">{{ $location ?: 'Unknown' }}</td>
                    </tr>
                    <tr style="border-bottom: 1px dashed #e2e8f0;">
                        <td style="padding: 10px 0; color: #64748b; font-weight: 600;">Attempted Username/Email:</td>
                        <td style="padding: 10px 0; text-align: right; color: #0f172a; font-weight: 700;">{{ $attemptedEmail ?: 'N/A' }}</td>
                    </tr>
                    <tr style="border-bottom: 1px dashed #e2e8f0;">
                        <td style="padding: 10px 0; color: #64748b; font-weight: 600;">Date & Time:</td>
                        <td style="padding: 10px 0; text-align: right; color: #0f172a;">{{ $timestamp }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; color: #64748b; font-weight: 600;">Device & Browser:</td>
                        <td style="padding: 10px 0; text-align: right; color: #334155; font-size: 11.5px; line-height: 1.4;">{{ $userAgent ?: 'Unknown' }}</td>
                    </tr>
                </table>
            </div>

            <div class="btn-container">
                <a href="{{ $blockUrl }}" class="block-btn" target="_blank">
                    ⛔ Block This IP Address (1-Click)
                </a>
            </div>

            <p style="font-size: 12px; color: #64748b; text-align: center; margin-top: 16px; line-height: 1.4;">
                Clicking the button above will instantly block this IP from accessing the website. You can view, manage, or unblock it anytime from the <strong>Admin Panel &gt; IP Block</strong>.
            </p>
        </div>
        <div class="footer">
            This is an automated security notification generated by your system security monitor.
        </div>
    </div>
</body>
</html>

