<div>
    @php
        $siteName = \App\Models\Setting::get('site_name', \App\Models\Setting::get('brand_name', config('app.name')));
        $siteTagline = \App\Models\Setting::get('site_tagline', \App\Models\Setting::get('brand_tagline', 'Platform Konversi & Optimasi Dokumen Digital'));
        $siteLogo = \App\Models\Setting::get('site_logo');
        $supportEmail = \App\Models\Setting::get('support_email', 'support@mudahkerja.com');
        $supportWhatsapp = \App\Models\Setting::get('support_whatsapp');
    @endphp

    @section('title', 'Invoice #' . $subscription->midtrans_order_id . ' - ' . $siteName)
    @section('page_title', 'Invoice')
    @section('page_breadcrumb', 'Invoice #' . $subscription->midtrans_order_id)

    {{-- Top Action Toolbar (Lineone Style) --}}
    <div class="flex items-center justify-between pb-5 lg:pb-6 print:hidden">
        <h2 class="text-xl font-medium text-slate-700 line-clamp-1 dark:text-navy-50 lg:text-2xl">
            Invoice Resmi
        </h2>

        <div class="flex space-x-2">
            @if(auth()->check() && auth()->user()->is_admin)
                <a href="{{ route('admin.transactions') }}"
                    class="btn h-9 rounded-full border border-slate-300 px-4 text-xs font-semibold text-slate-700 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-100 dark:hover:bg-navy-500">
                    <x-lucide-arrow-left class="size-4 mr-1.5" />
                    Kembali ke Transaksi
                </a>
            @else
                <a href="{{ route('dashboard.billing') }}"
                    class="btn h-9 rounded-full border border-slate-300 px-4 text-xs font-semibold text-slate-700 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-100 dark:hover:bg-navy-500">
                    <x-lucide-arrow-left class="size-4 mr-1.5" />
                    Kembali ke Billing
                </a>
            @endif
            <button @click="window.print()"
                class="btn size-9 rounded-full bg-primary p-0 text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus shadow-sm"
                title="Cetak / Download PDF">
                <x-lucide-printer class="size-4.5" />
            </button>
        </div>
    </div>

    {{-- Invoice Main Card (Exact Lineone layouts/invoice-1 style) --}}
    <div class="grid grid-cols-1">
        <div class="card px-5 py-10 sm:px-16 print:shadow-none print:border-none print:p-0">
            
            {{-- Header Invoice --}}
            <div class="flex flex-col justify-between sm:flex-row">
                <div class="text-center sm:text-left">
                    <div class="flex items-center space-x-3 justify-center sm:justify-start">
                        @if($siteLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($siteLogo))
                            <div class="flex size-10 shrink-0 items-center justify-center">
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($siteLogo) }}" class="size-full object-contain" alt="{{ $siteName }}" />
                            </div>
                        @else
                            <div class="mask is-squircle flex size-10 shrink-0 items-center justify-center bg-primary text-white dark:bg-accent shadow-xs">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                        @endif
                        <h2 class="text-2xl font-bold uppercase text-primary dark:text-accent-light tracking-wide">
                            {{ $siteName }}
                        </h2>
                    </div>
                    <div class="space-y-1 pt-2 text-xs text-slate-500 dark:text-navy-300">
                        <p class="font-medium text-slate-700 dark:text-navy-100">{{ $siteTagline }}</p>
                        <p>{{ $supportEmail }} @if($supportWhatsapp) | WA: {{ $supportWhatsapp }} @endif</p>
                    </div>
                </div>
                <div class="mt-4 text-center sm:m-0 sm:text-right">
                    <h2 class="text-2xl font-bold uppercase text-slate-800 dark:text-navy-100">
                        INVOICE
                    </h2>
                    <div class="space-y-1 pt-2 text-xs text-slate-500 dark:text-navy-300">
                        <p>Nomor Invoice: <span class="font-bold text-primary dark:text-accent-light">#{{ $subscription->midtrans_order_id }}</span></p>
                        <p>
                            Diterbitkan: <span class="font-semibold text-slate-800 dark:text-navy-100">{{ $subscription->created_at->translatedFormat('d F Y, H:i') }}</span>
                        </p>
                        <p>
                            Masa Aktif: <span class="font-semibold text-slate-800 dark:text-navy-100">{{ $subscription->expires_at ? $subscription->expires_at->translatedFormat('d F Y') : '-' }}</span>
                        </p>
                        <p class="pt-1">
                            @if($subscription->status === 'active' || $subscription->status === 'settlement' || $subscription->status === 'capture')
                                <span class="badge rounded-full bg-success/15 text-success dark:bg-success/20 text-[11px] font-bold px-3 py-0.5">
                                    LUNAS
                                </span>
                            @elseif($subscription->status === 'pending')
                                <span class="badge rounded-full bg-warning/15 text-warning dark:bg-warning/20 text-[11px] font-bold px-3 py-0.5">
                                    MENUNGGU PEMBAYARAN
                                </span>
                            @elseif($subscription->status === 'expired')
                                <span class="badge rounded-full bg-slate-200 text-slate-700 dark:bg-navy-500 dark:text-navy-100 text-[11px] font-bold px-3 py-0.5">
                                    EXPIRED
                                </span>
                            @elseif($subscription->status === 'cancelled')
                                <span class="badge rounded-full bg-info/15 text-info dark:bg-info/20 text-[11px] font-bold px-3 py-0.5">
                                    DIBATALKAN
                                </span>
                            @else
                                <span class="badge rounded-full bg-error/15 text-error dark:bg-error/20 text-[11px] font-bold px-3 py-0.5 uppercase">
                                    {{ $subscription->status }}
                                </span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="my-7 h-px bg-slate-200 dark:bg-navy-500"></div>

            {{-- Invoiced To & Payment Method --}}
            <div class="flex flex-col justify-between sm:flex-row">
                <div class="text-center sm:text-left">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-navy-300">
                        DITAGIHKAN KEPADA:
                    </p>
                    <div class="space-y-0.5 pt-2 text-xs text-slate-500 dark:text-navy-300">
                        <p class="font-bold text-slate-800 dark:text-navy-100 text-sm">{{ $subscription->user->name ?? 'Pengguna' }}</p>
                        <p>{{ $subscription->user->email ?? '-' }}</p>
                        @if($subscription->user && $subscription->user->phone)
                            <p>{{ $subscription->user->phone }}</p>
                        @endif
                    </div>
                </div>
                <div class="mt-4 text-center sm:m-0 sm:text-right">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-navy-300">
                        METODE PEMBAYARAN:
                    </p>
                    <div class="space-y-0.5 pt-2 text-xs text-slate-500 dark:text-navy-300">
                        <p class="font-bold text-slate-800 dark:text-navy-100">Midtrans Payment Gateway</p>
                        <p>QRIS / Virtual Account / E-Wallet</p>
                    </div>
                </div>
            </div>

            <div class="my-7 h-px bg-slate-200 dark:bg-navy-500"></div>

            {{-- Zebra Table (Exact Lineone layouts-invoice-1 style) --}}
            <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
                <table class="is-zebra w-full text-left">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap rounded-l-lg bg-slate-200 px-3 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs">
                                #
                            </th>
                            <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs">
                                DESKRIPSI LAYANAN
                            </th>
                            <th class="whitespace-nowrap bg-slate-200 px-3 py-3 text-right font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs">
                                DURASI
                            </th>
                            <th class="whitespace-nowrap bg-slate-200 px-3 py-3 text-right font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs">
                                HARGA
                            </th>
                            <th class="whitespace-nowrap rounded-r-lg bg-slate-200 px-3 py-3 text-right font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs">
                                SUBTOTAL
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $basePrice = $subscription->subtotal > 0 || $subscription->amount == 0 ? ($subscription->subtotal + $subscription->discount) : $subscription->amount;
                            $taxPercent = $subscription->subtotal > 0 ? round(($subscription->tax / $subscription->subtotal) * 100) : 0;
                            $discountPercent = $basePrice > 0 ? round(($subscription->discount / $basePrice) * 100) : 0;
                        @endphp
                        <tr>
                            <td class="whitespace-nowrap rounded-l-lg px-4 py-3 sm:px-5 font-medium text-slate-600 dark:text-navy-100 text-xs">
                                1
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                <div>
                                    <p class="font-bold text-slate-700 dark:text-navy-100 text-xs">
                                        Paket {{ $subscription->plan->name ?? ucfirst($subscription->plan_slug) }}
                                    </p>
                                    <p class="text-[11px] text-slate-400 dark:text-navy-300">
                                        {{ $subscription->plan->description ?? 'Akses tanpa batas ke seluruh tools konversi & optimasi dokumen / gambar.' }}
                                    </p>
                                </div>
                            </td>
                            <td class="w-2/12 whitespace-nowrap px-4 py-3 text-right sm:px-5 font-medium text-slate-600 dark:text-navy-100 text-xs">
                                {{ $subscription->plan ? ($subscription->plan->duration_days ? $subscription->plan->duration_days . ' Hari' : '1 Bulan') : '30 Hari' }}
                            </td>
                            <td class="w-2/12 whitespace-nowrap px-4 py-3 text-right sm:px-5 font-medium text-slate-600 dark:text-navy-100 text-xs">
                                Rp {{ number_format($basePrice, 0, ',', '.') }}
                            </td>
                            <td class="w-2/12 whitespace-nowrap rounded-r-lg px-4 py-3 text-right font-semibold text-slate-800 dark:text-navy-100 sm:px-5 text-xs">
                                Rp {{ number_format($basePrice, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="my-7 h-px bg-slate-200 dark:bg-navy-500"></div>

            {{-- Summary Breakdown --}}
            <div class="flex flex-col justify-end sm:flex-row">
                <div class="mt-4 text-center sm:m-0 sm:text-right">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-navy-300">
                        RINCIAN PEMBAYARAN:
                    </p>
                    <div class="space-y-1.5 pt-2 text-xs text-slate-600 dark:text-navy-200">
                        <p>Harga Paket: <span class="font-semibold text-slate-800 dark:text-navy-100">Rp {{ number_format($basePrice, 0, ',', '.') }}</span></p>
                        @if($subscription->discount > 0)
                            <p class="text-success font-semibold">Diskon Promo ({{ $discountPercent }}%): <span>-Rp {{ number_format($subscription->discount, 0, ',', '.') }}</span></p>
                        @endif
                        @if($subscription->service_fee > 0)
                            <p>Biaya Layanan: <span class="font-semibold text-slate-800 dark:text-navy-100">Rp {{ number_format($subscription->service_fee, 0, ',', '.') }}</span></p>
                        @endif
                        @if($subscription->tax > 0)
                            <p>Pajak Pertambahan Nilai ({{ $taxPercent }}%): <span class="font-semibold text-slate-800 dark:text-navy-100">Rp {{ number_format($subscription->tax, 0, ',', '.') }}</span></p>
                        @endif
                        <div class="pt-2 border-t border-slate-150 dark:border-navy-600 mt-2">
                            <p class="text-xl font-bold text-primary dark:text-accent-light">
                                Total: Rp {{ number_format($subscription->amount, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notes Footer --}}
            <div class="mt-8 rounded-xl bg-slate-50 p-4 dark:bg-navy-700/50 text-xs text-slate-500 dark:text-navy-300 border border-slate-150 dark:border-navy-600">
                <p class="font-bold text-slate-700 dark:text-navy-100 mb-1">Catatan Transaksi:</p>
                <p>Terima kasih atas kepercayaan Anda menggunakan layanan {{ $siteName }}. Simpan invoice ini sebagai bukti transaksi resmi langganan Anda.</p>
            </div>
        </div>
    </div>
</div>
