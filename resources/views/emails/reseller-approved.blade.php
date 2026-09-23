<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>রিসেলার অ্যাকাউন্ট অনুমোদিত হয়েছে</title>
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

                {{-- Header with Vibrant Gradient --}}
                <tr>
                    <td style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #db2777 100%); padding: 36px 30px; text-align: center;">
                        <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td align="center">
                                    <div style="display: inline-block; background-color: rgba(255, 255, 255, 0.2); backdrop-filter: blur(8px); padding: 8px 18px; border-radius: 30px; margin-bottom: 12px; border: 1px solid rgba(255, 255, 255, 0.3);">
                                        <span style="color: #ffffff; font-size: 11px; font-weight: bold; letter-spacing: 1.5px; text-transform: uppercase;">RESELLER PARTNER NETWORK</span>
                                    </div>
                                    <h1 style="color: #ffffff; margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -0.5px; line-height: 1.3;">
                                        🎉 অভিনন্দন, {{ $reseller->name }}!
                                    </h1>
                                    <p style="color: rgba(255, 255, 255, 0.9); margin: 8px 0 0; font-size: 14px; font-weight: 500;">
                                        আপনার রিসেলার অ্যাকাউন্ট সফলভাবে অনুমোদিত হয়েছে
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
                            প্রিয় <strong>{{ $reseller->name }}</strong>,
                        </p>
                        <p style="color: #334155; font-size: 14px; line-height: 1.7; margin: 0 0 22px;">
                            আমাদের প্ল্যাটফর্মে রিসেলার হিসেবে যুক্ত হওয়ার জন্য ধন্যবাদ। আনন্দের সাথে জানাচ্ছি যে অ্যাডমিন কর্তৃক আপনার অ্যাকাউন্টটি যাচাই করে অনুমোদন দেওয়া হয়েছে। এখন থেকে আপনি কোনো বড় বিনিয়োগ ছাড়াই ঘরে বসে আমাদের হাজার হাজার পণ্যের মাধ্যমে নিজের ব্যবসা পরিচালনা শুরু করতে পারবেন!
                        </p>

                        {{-- Account Summary Box --}}
                        <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; border-radius: 14px; border: 1px solid #e2e8f0; margin-bottom: 25px;">
                            <tr>
                                <td style="padding: 18px 20px;">
                                    <div style="font-size: 12px; font-weight: bold; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;">
                                        আপনার অ্যাকাউন্টের বিবরণ
                                    </div>
                                    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #64748b; width: 40%;">নাম:</td>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #0f172a; font-weight: 600;">{{ $reseller->name }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #64748b;">ইমেইল:</td>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #0f172a; font-weight: 600;">{{ $reseller->email }}</td>
                                        </tr>
                                        @if(!empty($reseller->phone))
                                        <tr>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #64748b;">ফোন:</td>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #0f172a; font-weight: 600;">{{ $reseller->phone }}</td>
                                        </tr>
                                        @endif
                                        @if(!empty($reseller->business_name))
                                        <tr>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #64748b;">ব্যবসার নাম:</td>
                                            <td style="padding: 5px 0; font-size: 13.5px; color: #0f172a; font-weight: 600;">{{ $reseller->business_name }}</td>
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
                                    <a href="{{ route('reseller.login') }}" target="_blank"
                                       style="display: inline-block; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: #ffffff; text-decoration: none; padding: 14px 34px; border-radius: 12px; font-size: 15px; font-weight: bold; box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);">
                                        🚀 রিসেলার ড্যাশবোর্ডে লগইন করুন
                                    </a>
                                </td>
                            </tr>
                        </table>

                        {{-- How to start selling guide --}}
                        <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 14px; padding: 18px 20px; margin-top: 25px;">
                            <div style="font-size: 13px; font-weight: bold; color: #166534; margin-bottom: 8px;">
                                💡 কীভাবে ব্যবসা শুরু করবেন?
                            </div>
                            <ol style="margin: 0; padding-left: 20px; color: #15803d; font-size: 13px; line-height: 1.8;">
                                <li><strong>পণ্য নির্বাচন করুন:</strong> ড্যাশবোর্ডে লগইন করে আপনার পছন্দের ট্রেন্ডিং প্রোডাক্ট বেছে নিন।</li>
                                <li><strong>মার্জিন নির্ধারণ করুন:</strong> পণ্যের নিজস্ব বিক্রয়মূল্য নির্ধারণ করে আপনার কাঙ্ক্ষিত মুনাফা সেট করুন।</li>
                                <li><strong>অর্ডার প্লেস করুন:</strong> কাস্টমারের ঠিকানা দিয়ে অর্ডার কনফার্ম করুন, ডেলিভারি ও কালেকশন আমরা করব!</li>
                                <li><strong>মুনাফা বুঝে নিন:</strong> ডেলিভারি সম্পন্ন হলে আপনার অ্যাকাউন্টে সরাসরি প্রফিট যোগ হবে।</li>
                            </ol>
                        </div>

                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td style="background-color: #f8fafc; padding: 22px 30px; border-top: 1px solid #e2e8f0; text-align: center;">
                        <p style="color: #64748b; font-size: 12px; line-height: 1.6; margin: 0 0 6px;">
                            যেকোনো প্রশ্ন বা সহায়তার জন্য আমাদের সাথে যোগাযোগ করুন:
                            <a href="mailto:support@arsglobaltrading.com" style="color: #4f46e5; font-weight: 600; text-decoration: none;">support@arsglobaltrading.com</a>
                        </p>
                        <p style="color: #94a3b8; font-size: 11px; margin: 0;">
                            © {{ date('Y') }} {{ config('app.name', 'Online Store') }}. All rights reserved.
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
