<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $siteName = \App\Models\Setting::get('site_name', \App\Models\Setting::get('brand_name', config('app.name')));
        $siteTagline = \App\Models\Setting::get('site_tagline', \App\Models\Setting::get('brand_tagline', 'Platform Konversi & Optimasi Dokumen Digital'));
        $siteLogo = \App\Models\Setting::get('site_logo');
        $supportEmail = \App\Models\Setting::get('support_email', 'support@mudahkerja.com');
        $footerCopyright = \App\Models\Setting::get('footer_copyright', '© ' . date('Y') . ' ' . $siteName . '. All rights reserved.');
    @endphp
    <title>Verifikasi Alamat Email - {{ $siteName }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #334155; font-size: 14px; line-height: 1.6;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8fafc; padding: 30px 15px;">
        <tr>
            <td align="center">
                <!-- Main Card Wrapper (Lineone Style) -->
                <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 580px; background-color: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                    
                    <!-- Header Bar -->
                    <tr>
                        <td style="padding: 28px 32px 20px 32px; border-bottom: 1px solid #f1f5f9; text-align: center;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <h1 style="margin: 0; font-size: 20px; font-weight: 800; color: #4f46e5; text-transform: uppercase; letter-spacing: 0.5px;">
                                            {{ $siteName }}
                                        </h1>
                                        <p style="margin: 3px 0 0 0; font-size: 11px; color: #94a3b8; font-weight: 500;">
                                            {{ $siteTagline }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 32px;">
                            <!-- Info Pill Badge -->
                            <div style="text-align: center; margin-bottom: 20px;">
                                <span style="display: inline-block; background-color: #e0e7ff; color: #4338ca; font-size: 11px; font-weight: 800; padding: 4px 14px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.5px;">
                                    Selamat Datang &bull; Verifikasi Email
                                </span>
                            </div>

                            <h2 style="margin: 0 0 12px 0; font-size: 18px; font-weight: 700; color: #0f172a; text-align: center;">
                                Halo, {{ $user->name }}!
                            </h2>
                            <p style="margin: 0 0 20px 0; font-size: 13px; color: #475569; text-align: center; line-height: 1.6;">
                                Terima kasih telah mendaftar di <strong>{{ $siteName }}</strong>. Silakan klik tombol di bawah ini untuk memverifikasi alamat email Anda dan mengaktifkan akun Anda:
                            </p>

                            <!-- CTA Button (Lineone Capsule Style) -->
                            <div style="text-align: center; margin-bottom: 24px;">
                                <a href="{{ $url }}" style="background-color: #4f46e5; color: #ffffff; font-size: 13px; font-weight: 700; text-decoration: none; padding: 12px 32px; border-radius: 9999px; display: inline-block; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.25);">
                                    Verifikasi Email Saya &rarr;
                                </a>
                            </div>

                            <!-- Security Notice Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 24px;">
                                <tr>
                                    <td style="padding: 14px; font-size: 11px; color: #64748b; line-height: 1.5; text-align: center;">
                                        Jika Anda tidak merasa mendaftar akun di {{ $siteName }}, Anda dapat mengabaikan email ini.
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0; font-size: 11px; color: #94a3b8; text-align: center; word-break: break-all;">
                                Tombol bermasalah? Salin URL berikut ke browser Anda:<br>
                                <a href="{{ $url }}" style="color: #4f46e5;">{{ $url }}</a>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 20px 32px; background-color: #f8fafc; border-top: 1px solid #f1f5f9; text-align: center; font-size: 11px; color: #94a3b8;">
                            <p style="margin: 0 0 4px 0;">{{ $siteName }} &bull; Solusi Pengolahan File Digital Cepat</p>
                            <p style="margin: 0;">{{ $footerCopyright }}</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
