<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $subscription->midtrans_order_id }}</title>
    <style>
        @page {
            margin: 0px;
        }
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #111827; /* ink */
            font-size: 14px;
            line-height: 1.5;
            margin: 0;
            padding: 40px;
            background-color: #ffffff;
        }
        .container {
            max-width: 100%;
            margin: 0 auto;
        }
        /* Header */
        table.header-table {
            width: 100%;
            border-bottom: 1px solid #e5e7eb; /* hairline */
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        table.header-table td {
            vertical-align: top;
        }
        .brand-title {
            font-size: 28px;
            font-weight: 900;
            color: #f59e0b; /* amber */
            margin: 0;
            padding: 0;
        }
        .brand-subtitle {
            font-size: 14px;
            color: #6b7280; /* ink-muted */
            margin: 5px 0 0 0;
        }
        .invoice-title {
            font-size: 24px;
            font-family: monospace;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0;
            text-align: right;
        }
        .invoice-number {
            font-size: 14px;
            color: #6b7280;
            text-align: right;
            margin: 5px 0 0 0;
        }
        
        /* Info Grid */
        table.info-table {
            width: 100%;
            margin-bottom: 30px;
        }
        table.info-table td {
            width: 50%;
            vertical-align: top;
        }
        .info-label {
            font-size: 12px;
            font-weight: bold;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        .info-value {
            font-weight: 500;
            color: #111827;
            margin: 0 0 4px 0;
        }
        .info-value-muted {
            font-size: 14px;
            color: #6b7280;
            margin: 0 0 4px 0;
        }
        
        .transaction-row {
            margin-bottom: 4px;
        }
        .transaction-label {
            display: inline-block;
            width: 80px;
            color: #6b7280;
            font-size: 14px;
        }
        .transaction-val {
            font-weight: 500;
            color: #111827;
            font-size: 14px;
        }
        
        /* Items Table */
        .items-container {
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 30px;
        }
        table.items-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.items-table th {
            background-color: #f9fafb; /* paper/50 */
            padding: 12px 16px;
            text-align: left;
            font-size: 12px;
            font-weight: bold;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #e5e7eb;
        }
        table.items-table th.right {
            text-align: right;
        }
        table.items-table td {
            padding: 16px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }
        table.items-table td.right {
            text-align: right;
        }
        
        .item-name {
            font-weight: 500;
            color: #111827;
            margin: 0 0 4px 0;
        }
        .item-desc {
            font-size: 14px;
            color: #6b7280;
            margin: 0;
        }
        .item-val {
            font-size: 14px;
            color: #111827;
        }
        .item-price {
            font-weight: 500;
            color: #111827;
        }
        
        table.items-table tr.total td {
            border-bottom: none;
        }
        .total-label {
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .total-val {
            font-weight: bold;
            font-size: 18px;
            color: #f59e0b;
        }
        
        /* Footer */
        .footer {
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
            font-size: 14px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <table class="header-table" cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    <h1 class="brand-title">{{ config('app.name') }}</h1>
                    <p class="brand-subtitle">Sistem Alat Produktivitas</p>
                </td>
                <td style="text-align: right;">
                    <h2 class="invoice-title">INVOICE</h2>
                    <p class="invoice-number">#{{ $subscription->midtrans_order_id }}</p>
                </td>
            </tr>
        </table>
        
        <!-- Info Grid -->
        <table class="info-table" cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    <div class="info-label">Ditagihkan Kepada:</div>
                    <p class="info-value">{{ $subscription->user->name }}</p>
                    <p class="info-value-muted">{{ $subscription->user->email }}</p>
                    @if($subscription->user->phone)
                        <p class="info-value-muted">{{ $subscription->user->phone }}</p>
                    @endif
                </td>
                <td style="text-align: right;">
                    <div class="info-label" style="text-align: right;">Detail Transaksi:</div>
                    <div style="text-align: right;">
                        <div class="transaction-row">
                            <span class="transaction-label" style="text-align: left;">Tanggal</span>
                            <span class="transaction-val">: {{ $subscription->created_at->translatedFormat('d F Y, H:i') }}</span>
                        </div>
                        <div class="transaction-row">
                            <span class="transaction-label" style="text-align: left;">Status</span>
                            <span class="transaction-val" style="text-transform: capitalize;">: LUNAS ({{ $subscription->status }})</span>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        
        <!-- Tabel Item -->
        <div class="items-container">
            <table class="items-table" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th>Deskripsi</th>
                        <th class="right">Durasi</th>
                        <th class="right">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <p class="item-name">Paket {{ $subscription->plan->name ?? ucfirst($subscription->plan_slug) }}</p>
                            <p class="item-desc">Akses tanpa batas ke semua fitur premium.</p>
                        </td>
                        <td class="right item-val">
                            {{ $subscription->plan ? ($subscription->plan->duration_days ?? 'Selamanya') . ' Hari' : '30 Hari' }}
                        </td>
                        <td class="right item-price">
                            Rp {{ number_format($subscription->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr class="total">
                        <td colspan="2" class="right total-label">
                            Total Pembayaran
                        </td>
                        <td class="right total-val">
                            Rp {{ number_format($subscription->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>Terima kasih telah menggunakan {{ config('app.name') }}. Jika Anda memiliki pertanyaan mengenai tagihan ini, silakan hubungi dukungan pelanggan kami.</p>
        </div>
    </div>
</body>
</html>
