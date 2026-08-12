<div>
    <div class="card px-5 py-8 sm:px-12 sm:py-12 print:shadow-none print:border-none print:p-0">
        {{-- Header Invoice --}}
        <div class="flex flex-col justify-between sm:flex-row">
            <div class="text-center sm:text-left">
                <h1 class="text-3xl font-bold uppercase text-primary dark:text-accent-light">{{ config('app.name') }}</h1>
                <p class="mt-1 text-sm font-medium text-slate-400 dark:text-navy-300">Sistem Alat Produktivitas</p>
            </div>
            <div class="mt-4 text-center sm:mt-0 sm:text-right">
                <h2 class="text-2xl font-semibold uppercase text-slate-700 dark:text-navy-100">Invoice</h2>
                <p class="mt-1 text-sm font-medium text-slate-400 dark:text-navy-300">#{{ $subscription->midtrans_order_id }}</p>
            </div>
        </div>

        <div class="my-6 h-px bg-slate-200 dark:bg-navy-500"></div>

        {{-- Info Grid --}}
        <div class="flex flex-col justify-between sm:flex-row">
            {{-- Info Pelanggan --}}
            <div class="text-center sm:text-left">
                <h3 class="text-xs-plus uppercase tracking-wide text-slate-400 dark:text-navy-300 mb-2">Ditagihkan Kepada:</h3>
                <p class="text-base font-medium text-slate-700 dark:text-navy-100">{{ auth()->user()->name }}</p>
                <p class="mt-1 text-sm text-slate-400 dark:text-navy-300">{{ auth()->user()->email }}</p>
                @if(auth()->user()->phone)
                    <p class="mt-1 text-sm text-slate-400 dark:text-navy-300">{{ auth()->user()->phone }}</p>
                @endif
            </div>

            {{-- Info Transaksi --}}
            <div class="mt-4 text-center sm:mt-0 sm:text-right">
                <h3 class="text-xs-plus uppercase tracking-wide text-slate-400 dark:text-navy-300 mb-2">Detail Transaksi:</h3>
                <p class="text-sm">
                    <span class="inline-block w-24 text-slate-400 dark:text-navy-300 text-left sm:text-right sm:mr-2">Tanggal:</span>
                    <span class="font-medium text-slate-700 dark:text-navy-100">{{ $subscription->created_at->translatedFormat('d F Y, H:i') }}</span>
                </p>
                <p class="mt-1 text-sm">
                    <span class="inline-block w-24 text-slate-400 dark:text-navy-300 text-left sm:text-right sm:mr-2">Status:</span>
                    <span class="font-medium capitalize text-slate-700 dark:text-navy-100">{{ $subscription->status }}</span>
                </p>
            </div>
        </div>

        {{-- Tabel Item --}}
        <div class="is-scrollbar-hidden mt-8 min-w-full overflow-x-auto rounded-lg border border-slate-200 dark:border-navy-500">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-y border-transparent border-b-slate-200 bg-slate-50 dark:border-b-navy-500 dark:bg-navy-800">
                        <th class="whitespace-nowrap px-4 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100">Deskripsi</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right font-semibold uppercase text-slate-800 dark:text-navy-100">Durasi</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right font-semibold uppercase text-slate-800 dark:text-navy-100">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="whitespace-nowrap px-4 py-4 sm:px-5">
                            <p class="font-medium text-slate-700 dark:text-navy-100">Paket {{ $subscription->plan->name ?? ucfirst($subscription->plan_slug) }}</p>
                            <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">Akses tanpa batas ke semua fitur premium.</p>
                        </td>
                        <td class="whitespace-nowrap px-4 py-4 text-right text-sm text-slate-700 dark:text-navy-100 sm:px-5">
                            {{ $subscription->plan ? ($subscription->plan->duration_days ?? 'Selamanya') . ' Hari' : '30 Hari' }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-4 text-right font-medium text-slate-700 dark:text-navy-100 sm:px-5">
                            @if($subscription->subtotal > 0 || $subscription->amount == 0)
                                Rp {{ number_format($subscription->subtotal + $subscription->discount, 0, ',', '.') }}
                            @else
                                Rp {{ number_format($subscription->amount, 0, ',', '.') }}
                            @endif
                        </td>
                    </tr>

                    @if($subscription->subtotal > 0 || $subscription->amount == 0)
                        @php
                            $basePrice = $subscription->subtotal + $subscription->discount;
                            $taxPercent = $subscription->subtotal > 0 ? round(($subscription->tax / $subscription->subtotal) * 100) : 0;
                            $discountPercent = $basePrice > 0 ? round(($subscription->discount / $basePrice) * 100) : 0;
                        @endphp
                        @if($subscription->discount > 0)
                        <tr class="border-y border-transparent border-t-slate-200 dark:border-t-navy-500">
                            <td colspan="2" class="whitespace-nowrap px-4 py-3 text-right font-medium text-success sm:px-5">Diskon ({{ $discountPercent }}%)</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right font-medium text-success sm:px-5">-Rp {{ number_format($subscription->discount, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($subscription->service_fee > 0)
                        <tr class="border-y border-transparent border-t-slate-200 dark:border-t-navy-500">
                            <td colspan="2" class="whitespace-nowrap px-4 py-3 text-right font-medium text-slate-700 dark:text-navy-100 sm:px-5">Biaya Layanan</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right font-medium text-slate-700 dark:text-navy-100 sm:px-5">Rp {{ number_format($subscription->service_fee, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($subscription->tax > 0)
                        <tr class="border-y border-transparent border-t-slate-200 dark:border-t-navy-500">
                            <td colspan="2" class="whitespace-nowrap px-4 py-3 text-right font-medium text-slate-700 dark:text-navy-100 sm:px-5">Pajak ({{ $taxPercent }}%)</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right font-medium text-slate-700 dark:text-navy-100 sm:px-5">Rp {{ number_format($subscription->tax, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                    @endif
                </tbody>
                <tfoot>
                    <tr class="border-y border-transparent border-t-slate-200 bg-slate-50 dark:border-t-navy-500 dark:bg-navy-800">
                        <td colspan="2" class="whitespace-nowrap px-4 py-4 text-right font-semibold uppercase text-slate-800 dark:text-navy-100 sm:px-5">Total Pembayaran</td>
                        <td class="whitespace-nowrap px-4 py-4 text-right text-lg font-bold text-primary dark:text-accent-light sm:px-5">
                            Rp {{ number_format($subscription->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Footer / Notes --}}
        <div class="mt-8 pt-4">
            <p class="text-sm text-slate-400 dark:text-navy-300">
                Terima kasih telah menggunakan {{ config('app.name') }}. Jika Anda memiliki pertanyaan mengenai tagihan ini, silakan hubungi dukungan pelanggan kami.
            </p>
        </div>

        {{-- Tombol Aksi (Sembunyi saat diprint) --}}
        <div class="mt-8 flex justify-end space-x-3 print:hidden">
            <a href="{{ route('dashboard.billing') }}" class="btn border border-slate-300 font-medium text-slate-800 hover:bg-slate-150 focus:bg-slate-150 active:bg-slate-150/80 dark:border-navy-450 dark:text-navy-50 dark:hover:bg-navy-500 dark:focus:bg-navy-500 dark:active:bg-navy-500/90">
                Kembali
            </a>
            <button onclick="window.print()" class="btn space-x-2 bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak Invoice</span>
            </button>
        </div>
    </div>
</div>
