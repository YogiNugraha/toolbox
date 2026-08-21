<div>
    @section('title', 'Invoice #' . $subscription->midtrans_order_id . ' - ' . config('app.name'))
    @section('page_title', 'Invoice')
    @section('page_breadcrumb', 'Invoice #' . $subscription->midtrans_order_id)

    {{-- Top Action Toolbar (Lineone Style) --}}
    <div class="flex items-center justify-between pb-5 lg:pb-6 print:hidden">
        <h2 class="text-xl font-medium text-slate-700 line-clamp-1 dark:text-navy-50 lg:text-2xl">
            Invoice
        </h2>

        <div class="flex space-x-2">
            <a href="{{ route('dashboard.billing') }}"
                class="btn h-9 rounded-full border border-slate-300 px-4 text-xs font-semibold text-slate-700 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-100 dark:hover:bg-navy-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Billing
            </a>
            <button @click="window.print()"
                class="btn size-9 rounded-full bg-primary p-0 text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus shadow-sm"
                title="Cetak / Download PDF">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Invoice Main Card (Exact Lineone layouts/invoice-1 style) --}}
    <div class="grid grid-cols-1">
        <div class="card px-5 py-12 sm:px-18 print:shadow-none print:border-none print:p-0">
            
            {{-- Header Invoice --}}
            <div class="flex flex-col justify-between sm:flex-row">
                <div class="text-center sm:text-left">
                    <h2 class="text-2xl font-bold uppercase text-primary dark:text-accent-light tracking-wide">
                        {{ config('app.name') }}
                    </h2>
                    <div class="space-y-1 pt-2 text-xs text-slate-500 dark:text-navy-300">
                        <p class="font-medium text-slate-700 dark:text-navy-100">MudahKerja Productivity Suite</p>
                        <p>Platform Alat Bantu Dokumen & Gambar Digital</p>
                        <p>support@mudahkerja.com</p>
                    </div>
                </div>
                <div class="mt-4 text-center sm:m-0 sm:text-right">
                    <h2 class="text-2xl font-semibold uppercase text-primary dark:text-accent-light">
                        INVOICE
                    </h2>
                    <div class="space-y-1 pt-2 text-xs text-slate-500 dark:text-navy-300">
                        <p>Nomor Invoice: <span class="font-semibold text-slate-800 dark:text-navy-100">#{{ $subscription->midtrans_order_id }}</span></p>
                        <p>
                            Diterbitkan: <span class="font-semibold text-slate-800 dark:text-navy-100">{{ $subscription->created_at->translatedFormat('d F Y, H:i') }}</span>
                        </p>
                        <p>
                            Status: 
                            @if($subscription->status === 'active' || $subscription->status === 'settlement' || $subscription->status === 'capture')
                                <span class="badge rounded-full bg-success/10 text-success text-[11px] font-bold px-2.5 py-0.5">
                                    LUNAS
                                </span>
                            @elseif($subscription->status === 'pending')
                                <span class="badge rounded-full bg-warning/10 text-warning text-[11px] font-bold px-2.5 py-0.5">
                                    MENUNGGU PEMBAYARAN
                                </span>
                            @else
                                <span class="badge rounded-full bg-error/10 text-error text-[11px] font-bold px-2.5 py-0.5 uppercase">
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
                    <p class="text-lg font-medium text-slate-600 dark:text-navy-100">
                        Ditagihkan Kepada:
                    </p>
                    <div class="space-y-1 pt-2 text-xs text-slate-500 dark:text-navy-300">
                        <p class="font-bold text-slate-800 dark:text-navy-100 text-sm">{{ auth()->user()->name }}</p>
                        <p>{{ auth()->user()->email }}</p>
                        @if(auth()->user()->phone)
                            <p>{{ auth()->user()->phone }}</p>
                        @endif
                    </div>
                </div>
                <div class="mt-4 text-center sm:m-0 sm:text-right">
                    <p class="text-lg font-medium text-slate-600 dark:text-navy-100">
                        Metode Pembayaran:
                    </p>
                    <div class="space-y-1 pt-2 text-xs text-slate-500 dark:text-navy-300">
                        <p class="font-bold text-slate-800 dark:text-navy-100">Midtrans Gateway (QRIS / Transfer / E-Wallet)</p>
                        <p>Order ID: {{ $subscription->midtrans_order_id }}</p>
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
                                DESKRIPSI
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
                            $basePrice = $subscription->subtotal > 0 ? ($subscription->subtotal + $subscription->discount) : $subscription->amount;
                            $taxPercent = $subscription->subtotal > 0 ? round(($subscription->tax / $subscription->subtotal) * 100) : 0;
                            $discountPercent = $basePrice > 0 ? round(($subscription->discount / $basePrice) * 100) : 0;
                        @endphp
                        <tr>
                            <td class="whitespace-nowrap rounded-l-lg px-4 py-3 sm:px-5 font-medium text-slate-600 dark:text-navy-100">
                                1
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                <div>
                                    <p class="font-bold text-slate-700 dark:text-navy-100">
                                        Paket {{ $subscription->plan->name ?? ucfirst($subscription->plan_slug) }}
                                    </p>
                                    <p class="text-xs text-slate-400 dark:text-navy-300">
                                        Akses tanpa batas ke seluruh tools konversi & optimasi dokumen / gambar.
                                    </p>
                                </div>
                            </td>
                            <td class="w-2/12 whitespace-nowrap px-4 py-3 text-right sm:px-5 font-medium text-slate-600 dark:text-navy-100">
                                {{ $subscription->plan ? ($subscription->plan->duration_days ?? '30') . ' Hari' : '30 Hari' }}
                            </td>
                            <td class="w-2/12 whitespace-nowrap px-4 py-3 text-right sm:px-5 font-medium text-slate-600 dark:text-navy-100">
                                Rp {{ number_format($basePrice, 0, ',', '.') }}
                            </td>
                            <td class="w-2/12 whitespace-nowrap rounded-r-lg px-4 py-3 text-right font-semibold text-slate-800 dark:text-navy-100 sm:px-5">
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
                    <p class="text-lg font-medium text-slate-600 dark:text-navy-100">
                        Rincian Pembayaran:
                    </p>
                    <div class="space-y-1.5 pt-2 text-xs text-slate-600 dark:text-navy-200">
                        <p>Harga Paket: <span class="font-semibold text-slate-800 dark:text-navy-100">Rp {{ number_format($basePrice, 0, ',', '.') }}</span></p>
                        @if($subscription->discount > 0)
                            <p class="text-success">Diskon ({{ $discountPercent }}%): <span class="font-semibold">-Rp {{ number_format($subscription->discount, 0, ',', '.') }}</span></p>
                        @endif
                        @if($subscription->service_fee > 0)
                            <p>Biaya Layanan: <span class="font-semibold text-slate-800 dark:text-navy-100">Rp {{ number_format($subscription->service_fee, 0, ',', '.') }}</span></p>
                        @endif
                        @if($subscription->tax > 0)
                            <p>Pajak ({{ $taxPercent }}%): <span class="font-semibold text-slate-800 dark:text-navy-100">Rp {{ number_format($subscription->tax, 0, ',', '.') }}</span></p>
                        @endif
                        <div class="pt-2">
                            <p class="text-xl font-bold text-primary dark:text-accent-light">
                                Total: Rp {{ number_format($subscription->amount, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notes Footer --}}
            <div class="mt-8 rounded-lg bg-slate-50 p-4 dark:bg-navy-600/50 text-xs text-slate-500 dark:text-navy-300">
                <p class="font-medium text-slate-700 dark:text-navy-100 mb-1">Catatan:</p>
                <p>Terima kasih atas kepercayaan Anda menggunakan layanan {{ config('app.name') }}. Simpan invoice ini sebagai bukti transaksi resmi langganan Anda.</p>
            </div>
        </div>
    </div>
</div>
