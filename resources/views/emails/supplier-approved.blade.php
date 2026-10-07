<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সাপ্লায়ার অ্যাকাউন্ট অনুমোদিত হয়েছে</title>
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        body { margin: 0; padding: 0; width: 100% !important; background-color: #f1f5f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif, 'Noto Sans Bengali'; }
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
                                        🎉 অভিনন্দন, {{ $supplier->display_name }}!
                                    </h1>
                                    <p style="color: rgba(255, 255, 255, 0.9); margin: 8px 0 0; font-size: 14px; font-weight: 500;">
                                        আপনার সাপ্লায়ার অ্যাকাউন্ট সফলভাবে অনুমোদিত হয়েছে
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
                            প্রিয় <strong>{{ $supplier->name }}</strong>,
                        </p>
                        <p style="color: #334155; font-size: 14px; line-height: 1.7; margin: 0 0 22px;">
                            আমাদের প্ল্যাটফর্মে সাপ্লায়ার (মার্কেটপ্লেস পার্টনার) হিসেবে যুক্ত হওয়ার জন্য আন্তরিক ধন্যবাদ। অত্যন্ত আনন্দের সাথে জানাচ্ছি যে অ্যাডমিন কর্তৃক আপনার প্রোফাইল ও আবেদনটি সফলভাবে যাচাই করে অনুমোদন দেওয়া হয়েছে। এখন আপনি সরাসরি লগইন করে আপনার পণ্যসমূহ আপলোড করতে পারবেন এবং বিক্রি শুরু করতে পারবেন!
                        </p>

                        {{-- Account Summary Box --}}
                        <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; border-radius: 14px; border: 1px solid #e2e8f0; margin-bottom: 25px;">
                            <tr>
                                <td style="padding: 18px 20px;">
                                    <div style="font-size: 12px; font-weight: bold; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;">
                                        আপনার সাপ্লায়ার অ্যাকাউন্টের বিবরণ
                                    </div>
                                    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #64748b; width: 40%;">নাম:</td>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #0f172a; font-weight: 600;">{{ $supplier->name }}</td>
                                        </tr>
                                        @if(!empty($supplier->company_name))
                                        <tr>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #64748b;">কোম্পানি / শপ:</td>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #0f172a; font-weight: 600;">{{ $supplier->company_name }}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #64748b;">ইমেইল (লগইন):</td>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #0f172a; font-weight: 600;">{{ $supplier->email }}</td>
                                        </tr>
                                        @if(!empty($supplier->phone))
                                        <tr>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #64748b;">ফোন:</td>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #0f172a; font-weight: 600;">{{ $supplier->phone }}</td>
                                        </tr>
                                        @endif
                                        @if(!empty($supplier->address))
                                        <tr>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #64748b;">ঠিকানা:</td>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #0f172a; font-weight: 600;">{{ $supplier->address }}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #64748b;">স্ট্যাটাস:</td>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #16a34a; font-weight: bold;">
                                                <span style="display: inline-block; background-color: #dcfce7; color: #15803d; padding: 2px 8px; border-radius: 6px; font-size: 12px;">✅ Active (অনুমোদিত)</span>
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
                                        🚀 সাপ্লায়ার ড্যাশবোর্ডে লগইন করুন
                                    </a>
                                </td>
                            </tr>
                        </table>

                        {{-- Next Steps Guide --}}
                        <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 14px; padding: 18px 20px; margin-top: 25px;">
                            <div style="font-size: 13px; font-weight: bold; color: #166534; margin-bottom: 8px;">
                                💡 পরবর্তী করণীয় ও ব্যবসা পরিচালনা:
                            </div>
                            <ol style="margin: 0; padding-left: 20px; color: #15803d; font-size: 13px; line-height: 1.8;">
                                <li><strong>পণ্য আপলোড করুন:</strong> সাপ্লায়ার ড্যাশবোর্ডে গিয়ে সহজেই পণ্যের নাম, ক্যাটাগরি, মূল্য, ছবি ও স্টক যোগ করুন।</li>
                                <li><strong>অর্ডার পরিচালনা:</strong> আপনার পণ্যে কোনো কাস্টমার  অর্ডার দিলে তা তাৎক্ষণিকভাবে ড্যাশবোর্ডে দেখতে পাবেন।</li>
                                <li><strong>উপার্জন বুঝে নিন:</strong> ডেলিভারি সম্পন্ন হলে পণ্যের নির্ধারিত টাকা আপনার ওয়ালেটে যুক্ত হবে এবং বিকাশ/ব্যাংকে তুলতে পারবেন।</li>
                            </ol>
                        </div>

                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td style="background-color: #f8fafc; padding: 22px 30px; border-top: 1px solid #e2e8f0; text-align: center;">
                        <p style="color: #64748b; font-size: 12px; line-height: 1.6; margin: 0 0 6px;">
                            যেকোনো প্রশ্ন বা সহায়তার জন্য আমাদের সাথে যোগাযোগ করুন:
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
