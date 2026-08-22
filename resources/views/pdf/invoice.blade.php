<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    @php
        $siteName = \App\Models\Setting::get('site_name', \App\Models\Setting::get('brand_name', config('app.name')));
        $siteTagline = \App\Models\Setting::get('site_tagline', \App\Models\Setting::get('brand_tagline', 'Platform Konversi & Optimasi Dokumen Digital'));
        $siteLogo = \App\Models\Setting::get('site_logo');
        $supportEmail = \App\Models\Setting::get('support_email', 'support@mudahkerja.com');
        $supportWhatsapp = \App\Models\Setting::get('support_whatsapp', '+6281234567890');
        $footerCopyright = \App\Models\Setting::get('footer_copyright', '© ' . date('Y') . ' ' . $siteName . '. All rights reserved.');
    @endphp
    <title>Invoice - {{ $subscription->midtrans_order_id }}</title>
    <style>
        @page {
            margin: 0px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b; /* slate-800 */
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
            padding: 40px;
            background-color: #ffffff;
        }
        .container {
            max-width: 100%;
            margin: 0 auto;
        }
        
        /* Header Table */
        table.header-table {
            width: 100%;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        table.header-table td {
            vertical-align: middle;
        }
        .brand-title {
            font-size: 22px;
            font-weight: 800;
            color: #4f46e5; /* Lineone Indigo Primary */
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
            padding: 0;
        }
        .brand-subtitle {
            font-size: 11px;
            color: #64748b; /* slate-500 */
            margin: 4px 0 0 0;
            font-weight: 500;
        }
        .invoice-badge {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a; /* slate-900 */
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
            text-align: right;
        }
        .invoice-number {
            font-size: 12px;
            font-weight: bold;
            color: #4f46e5;
            text-align: right;
            margin: 4px 0 0 0;
        }

        /* Info Grid Table */
        table.info-table {
            width: 100%;
            margin-bottom: 25px;
            background-color: #f8fafc; /* slate-50 */
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
        }
        table.info-table td {
            width: 50%;
            vertical-align: top;
        }
        .section-label {
            font-size: 10px;
            font-weight: 800;
            color: #94a3b8; /* slate-400 */
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }
        .customer-name {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 2px 0;
        }
        .customer-detail {
            font-size: 12px;
            color: #475569;
            margin: 0 0 2px 0;
        }

        .meta-row {
            margin-bottom: 3px;
        }
        .meta-label {
            display: inline-block;
            width: 90px;
            color: #64748b;
            font-size: 12px;
        }
        .meta-val {
            font-weight: 600;
            color: #0f172a;
            font-size: 12px;
        }
        .status-paid {
            display: inline-block;
            background-color: #dcfce7; /* emerald-100 */
            color: #15803d; /* emerald-700 */
            font-weight: 800;
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 9999px;
            text-transform: uppercase;
        }

        /* Items Table */
        .table-wrapper {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 25px;
        }
        table.items-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.items-table th {
            background-color: #f1f5f9; /* slate-100 */
            padding: 10px 14px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e2e8f0;
        }
        table.items-table th.right, table.items-table td.right {
            text-align: right;
        }
        table.items-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 12px;
            color: #334155;
        }
        .item-title {
            font-weight: 700;
            color: #0f172a;
            font-size: 13px;
            margin: 0 0 2px 0;
        }
        .item-desc {
            font-size: 11px;
            color: #64748b;
            margin: 0;
        }

        /* Summary Breakdown */
        .subtotal-label {
            font-weight: 600;
            color: #64748b;
            font-size: 11px;
            text-transform: uppercase;
        }
        .subtotal-val {
            font-weight: 600;
            color: #1e293b;
        }
        .discount-label {
            font-weight: 600;
            color: #16a34a;
            font-size: 11px;
            text-transform: uppercase;
        }
        .discount-val {
            font-weight: 700;
            color: #16a34a;
        }
        tr.grand-total td {
            background-color: #f8fafc;
            border-top: 2px solid #e2e8f0;
            border-bottom: none;
            padding: 14px;
        }
        .grand-total-label {
            font-size: 12px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .grand-total-val {
            font-size: 16px;
            font-weight: 800;
            color: #4f46e5; /* Primary */
        }

        /* Footer & Notes */
        .footer-table {
            width: 100%;
            margin-top: 20px;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
        }
        .footer-table td {
            vertical-align: top;
            font-size: 11px;
            color: #64748b;
        }
        .contact-info {
            font-size: 11px;
            color: #475569;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <table class="header-table" cellpadding="0" cellspacing="0">
            <tr>
                <td style="width: 60%;">
                    @if($siteLogo && file_exists(public_path('storage/' . $siteLogo)))
                        <img src="{{ public_path('storage/' . $siteLogo) }}" style="max-height: 45px; max-width: 180px; margin-bottom: 6px;" alt="{{ $siteName }}" />
                    @else
                        <h1 class="brand-title">{{ $siteName }}</h1>
                    @endif
                    <p class="brand-subtitle">{{ $siteTagline }}</p>
                </td>
                <td style="width: 40%; text-align: right;">
                    <h2 class="invoice-badge">INVOICE RESMI</h2>
                    <p class="invoice-number">#{{ $subscription->midtrans_order_id }}</p>
                </td>
            </tr>
        </table>
        
        <!-- Info Grid (Billed To & Transaction Details) -->
        <table class="info-table" cellpadding="0" cellspacing="0">
            <tr>
                <td style="width: 50%;">
                    <div class="section-label">DITAGIHKAN KEPADA:</div>
                    <p class="customer-name">{{ $subscription->user->name }}</p>
                    <p class="customer-detail">{{ $subscription->user->email }}</p>
                    @if($subscription->user->phone)
                        <p class="customer-detail">{{ $subscription->user->phone }}</p>
                    @endif
                </td>
                <td style="width: 50%; text-align: right;">
                    <div class="section-label" style="text-align: right;">RINCIAN TRANSAKSI:</div>
                    <div style="text-align: right;">
                        <div class="meta-row">
                            <span class="meta-label">Tanggal Terbit</span>
                            <span class="meta-val">: {{ $subscription->created_at->translatedFormat('d F Y, H:i') }}</span>
                        </div>
                        <div class="meta-row">
                            <span class="meta-label">Masa Aktif</span>
                            <span class="meta-val">: {{ $subscription->expires_at ? $subscription->expires_at->translatedFormat('d F Y') : '-' }}</span>
                        </div>
                        <div class="meta-row" style="margin-top: 4px;">
                            <span class="meta-label">Status Tagihan</span>
                            <span class="meta-val">: <span class="status-paid">LUNAS</span></span>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        
        <!-- Tabel Item Pembelian -->
        <div class="table-wrapper">
            <table class="items-table" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th style="width: 55%;">Deskripsi Layanan</th>
                        <th class="right" style="width: 20%;">Durasi</th>
                        <th class="right" style="width: 25%;">Harga Satuan</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $basePrice = $subscription->subtotal > 0 || $subscription->amount == 0 ? ($subscription->subtotal + $subscription->discount) : $subscription->amount;
                        $taxPercent = $subscription->subtotal > 0 ? round(($subscription->tax / $subscription->subtotal) * 100) : 0;
                        $discountPercent = $basePrice > 0 ? round(($subscription->discount / $basePrice) * 100) : 0;
                    @endphp
                    <tr>
                        <td>
                            <p class="item-title">Paket Berlangganan {{ $subscription->plan->name ?? ucfirst($subscription->plan_slug) }}</p>
                            <p class="item-desc">{{ $subscription->plan->description ?? 'Akses tanpa batas ke semua fitur pengolahan dokumen digital' }}</p>
                        </td>
                        <td class="right" style="font-weight: 500;">
                            {{ $subscription->plan ? ($subscription->plan->duration_days ? $subscription->plan->duration_days . ' Hari' : '1 Bulan') : '30 Hari' }}
                        </td>
                        <td class="right" style="font-weight: 600;">
                            Rp {{ number_format($basePrice, 0, ',', '.') }}
                        </td>
                    </tr>
                    
                    {{-- Breakdown Diskon & Biaya Tambahan --}}
                    @if($subscription->discount > 0)
                    <tr>
                        <td colspan="2" class="right discount-label">Diskon Promo ({{ $discountPercent }}%)</td>
                        <td class="right discount-val">-Rp {{ number_format($subscription->discount, 0, ',', '.') }}</td>
                    </tr>
                    @endif

                    @if($subscription->service_fee > 0)
                    <tr>
                        <td colspan="2" class="right subtotal-label">Biaya Layanan Gateway</td>
                        <td class="right subtotal-val">Rp {{ number_format($subscription->service_fee, 0, ',', '.') }}</td>
                    </tr>
                    @endif

                    @if($subscription->tax > 0)
                    <tr>
                        <td colspan="2" class="right subtotal-label">Pajak Pertambahan Nilai (PPN {{ $taxPercent }}%)</td>
                        <td class="right subtotal-val">Rp {{ number_format($subscription->tax, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    
                    {{-- Total Pembayaran --}}
                    <tr class="grand-total">
                        <td colspan="2" class="right grand-total-label">
                            TOTAL PEMBAYARAN
                        </td>
                        <td class="right grand-total-val">
                            Rp {{ number_format($subscription->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Footer & Contact Support -->
        <table class="footer-table" cellpadding="0" cellspacing="0">
            <tr>
                <td style="width: 60%;">
                    <p style="font-weight: 700; color: #0f172a; margin: 0 0 4px 0;">Catatan & Dukungan Layanan:</p>
                    <p class="contact-info">
                        Invoice ini diterbitkan secara otomatis dan sah tanpa tanda tangan basah.<br>
                        Butuh bantuan? Hubungi kami di <strong>{{ $supportEmail }}</strong> @if($supportWhatsapp) atau WhatsApp: <strong>{{ $supportWhatsapp }}</strong>@endif.
                    </p>
                </td>
                <td style="width: 40%; text-align: right;">
                    <p style="font-weight: 600; color: #64748b; margin: 0;">{{ $siteName }}</p>
                    <p style="color: #94a3b8; font-size: 10px; margin: 2px 0 0 0;">{{ $footerCopyright }}</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
