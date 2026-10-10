<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Account Approved</title>
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        body { margin: 0; padding: 0; width: 100% !important; background-color: #f1f5f9; font-family: 'SolaimanLipi', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9;">

<table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f1f5f9; padding: 30px 10px;">
    <tr>
        <td align="center">
            {{-- Main Container Card --}}
            <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">

                {{-- Header with Emerald / Teal Gradient --}}
                <tr>
                    <td style="background: linear-gradient(135deg, #059669 0%, #0d9488 50%, #064e3b 100%); padding: 36px 30px; text-align: center;">
                        <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td align="center">
                                    <div style="display: inline-block; background-color: rgba(255, 255, 255, 0.2); backdrop-filter: blur(8px); padding: 8px 18px; border-radius: 30px; margin-bottom: 12px; border: 1px solid rgba(255, 255, 255, 0.3);">
                                        <span style="color: #ffffff; font-size: 11px; font-weight: bold; letter-spacing: 1.5px; text-transform: uppercase;">VERIFIED SUPPLIER PARTNER</span>
                                    </div>
                                    <h1 style="color: #ffffff; margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -0.5px; line-height: 1.3;">
                                        🎉 Congratulations, {{ $supplier->display_name }}!
                                    </h1>
                                    <p style="color: rgba(255, 255, 255, 0.9); margin: 8px 0 0; font-size: 14px; font-weight: 500;">
                                        Your supplier partner account has been approved
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Body Content --}}
                <tr>
                    <td style="padding: 35px 30px 25px;">
                        <p style="color: #1e293b; font-size: 15px; line-height: 1.6; margin: 0 0 18px;">
                            Dear <strong>{{ $supplier->name }}</strong>,
                        </p>
                        <p style="color: #334155; font-size: 14px; line-height: 1.7; margin: 0 0 22px;">
                            Thank you for partnering with us as a verified marketplace supplier. We are pleased to inform you that your profile and application have been reviewed and approved by our administration team. You can now log in, upload your product catalog, and begin selling!
                        </p>

                        {{-- Account Summary Box --}}
                        <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; border-radius: 14px; border: 1px solid #e2e8f0; margin-bottom: 25px;">
                            <tr>
                                <td style="padding: 18px 20px;">
                                    <div style="font-size: 12px; font-weight: bold; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;">
                                        Supplier Account Details
                                    </div>
                                    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #64748b; width: 40%;">Name:</td>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #0f172a; font-weight: 600;">{{ $supplier->name }}</td>
                                        </tr>
                                        @if(!empty($supplier->company_name))
                                        <tr>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #64748b;">Company / Store:</td>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #0f172a; font-weight: 600;">{{ $supplier->company_name }}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #64748b;">Email (Login):</td>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #0f172a; font-weight: 600;">{{ $supplier->email }}</td>
                                        </tr>
                                        @if(!empty($supplier->phone))
                                        <tr>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #64748b;">Phone:</td>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #0f172a; font-weight: 600;">{{ $supplier->phone }}</td>
                                        </tr>
                                        @endif
                                        @if(!empty($supplier->address))
                                        <tr>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #64748b;">Address:</td>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #0f172a; font-weight: 600;">{{ $supplier->address }}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #64748b;">Status:</td>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #16a34a; font-weight: bold;">
                                                <span style="display: inline-block; background-color: #dcfce7; color: #15803d; padding: 2px 8px; border-radius: 6px; font-size: 12px;">✅ Active (Approved)</span>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        {{-- Call to Action Button --}}
                        <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="margin: 28px 0;">
                            <tr>
                                <td align="center">
                                    <a href="{{ route('supplier.login') }}" target="_blank"
                                       style="display: inline-block; background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #ffffff; text-decoration: none; padding: 14px 34px; border-radius: 12px; font-size: 15px; font-weight: bold; box-shadow: 0 4px 14px rgba(5, 150, 105, 0.35);">
                                        🚀 Log In to Supplier Dashboard
                                    </a>
                                </td>
                            </tr>
                        </table>

                        {{-- Next Steps Guide --}}
                        <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 14px; padding: 18px 20px; margin-top: 25px;">
                            <div style="font-size: 13px; font-weight: bold; color: #166534; margin-bottom: 8px;">
                                💡 Next Steps & Getting Started:
                            </div>
                            <ol style="margin: 0; padding-left: 20px; color: #15803d; font-size: 13px; line-height: 1.8;">
                                <li><strong>Upload Products:</strong> Access your supplier dashboard to list products with titles, categories, pricing, images, and inventory.</li>
                                <li><strong>Manage Orders:</strong> Receive real-time order alerts and process order fulfillment smoothly from your portal.</li>
                                <li><strong>Receive Earnings:</strong> Once orders are delivered, your funds are credited to your balance and can be withdrawn anytime.</li>
                            </ol>
                        </div>

                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td style="background-color: #f8fafc; padding: 22px 30px; border-top: 1px solid #e2e8f0; text-align: center;">
                        <p style="color: #64748b; font-size: 12px; line-height: 1.6; margin: 0 0 6px;">
                            If you have any questions or need assistance, feel free to contact us:
                            <a href="mailto:{{ config('mail.from.address', 'support@arsglobaltrading.com') }}" style="color: #059669; font-weight: 600; text-decoration: none;">{{ config('mail.from.address', 'support@arsglobaltrading.com') }}</a>
                        </p>
                        <p style="color: #94a3b8; font-size: 11px; margin: 0;">
                            © {{ date('Y') }} {{ config('app.name', 'Marketplace') }}. All rights reserved.
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
