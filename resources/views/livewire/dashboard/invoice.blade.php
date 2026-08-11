<div>
    <div class="max-w-3xl mx-auto bg-white p-8 md:p-12 shadow-sm border border-hairline rounded-sm print:shadow-none print:border-none print:p-0">
        <!-- Header Invoice -->
        <div class="flex justify-between items-start border-b border-hairline pb-6 mb-6">
            <div>
                <h1 class="text-3xl font-display font-black text-amber">{{ config('app.name') }}</h1>
                <p class="text-sm text-ink-muted mt-1">Sistem Alat Produktivitas</p>
            </div>
            <div class="text-right">
                <h2 class="text-2xl font-mono font-bold text-ink uppercase tracking-widest">INVOICE</h2>
                <p class="text-sm text-ink-muted mt-1">#{{ $subscription->midtrans_order_id }}</p>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="grid grid-cols-2 gap-8 mb-8">
            <!-- Info Pelanggan -->
            <div>
                <h3 class="text-xs font-bold text-ink-muted uppercase tracking-wider mb-2">Ditagihkan Kepada:</h3>
                <p class="font-medium text-ink">{{ auth()->user()->name }}</p>
                <p class="text-sm text-ink-muted">{{ auth()->user()->email }}</p>
                @if(auth()->user()->phone)
                    <p class="text-sm text-ink-muted">{{ auth()->user()->phone }}</p>
                @endif
            </div>
            
            <!-- Info Transaksi -->
            <div class="text-right">
                <h3 class="text-xs font-bold text-ink-muted uppercase tracking-wider mb-2">Detail Transaksi:</h3>
                <p class="text-sm text-ink-muted">
                    <span class="inline-block w-24 text-left">Tanggal</span> 
                    <span class="font-medium text-ink">: {{ $subscription->created_at->translatedFormat('d F Y, H:i') }}</span>
                </p>
                <p class="text-sm text-ink-muted mt-1">
                    <span class="inline-block w-24 text-left">Status</span> 
                    <span class="font-medium text-ink capitalize">: {{ $subscription->status }}</span>
                </p>
            </div>
        </div>

        <!-- Tabel Item -->
        <div class="mb-8 overflow-hidden rounded-sm border border-hairline">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-paper/50">
                        <th class="py-3 px-4 text-xs font-bold text-ink-muted uppercase tracking-wider border-b border-hairline">Deskripsi</th>
                        <th class="py-3 px-4 text-xs font-bold text-ink-muted uppercase tracking-wider border-b border-hairline text-right">Durasi</th>
                        <th class="py-3 px-4 text-xs font-bold text-ink-muted uppercase tracking-wider border-b border-hairline text-right">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="py-4 px-4 border-b border-hairline">
                            <p class="font-medium text-ink">Paket {{ $subscription->plan->name ?? ucfirst($subscription->plan_slug) }}</p>
                            <p class="text-sm text-ink-muted">Akses tanpa batas ke semua fitur premium.</p>
                        </td>
                        <td class="py-4 px-4 border-b border-hairline text-right text-sm text-ink">
                            {{ $subscription->plan ? ($subscription->plan->duration_days ?? 'Selamanya') . ' Hari' : '30 Hari' }}
                        </td>
                        <td class="py-4 px-4 border-b border-hairline text-right font-medium text-ink">
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
                        <tr>
                            <td colspan="2" class="py-3 px-4 text-right text-xs font-bold text-green-600 uppercase tracking-wider border-b border-hairline">Diskon ({{ $discountPercent }}%)</td>
                            <td class="py-3 px-4 text-right font-medium text-green-600 border-b border-hairline">-Rp {{ number_format($subscription->discount, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($subscription->service_fee > 0)
                        <tr>
                            <td colspan="2" class="py-3 px-4 text-right text-xs font-bold text-ink-muted uppercase tracking-wider border-b border-hairline">Biaya Layanan</td>
                            <td class="py-3 px-4 text-right font-medium text-ink border-b border-hairline">Rp {{ number_format($subscription->service_fee, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($subscription->tax > 0)
                        <tr>
                            <td colspan="2" class="py-3 px-4 text-right text-xs font-bold text-ink-muted uppercase tracking-wider border-b border-hairline">Pajak ({{ $taxPercent }}%)</td>
                            <td class="py-3 px-4 text-right font-medium text-ink border-b border-hairline">Rp {{ number_format($subscription->tax, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="py-4 px-4 text-right font-bold text-ink uppercase tracking-wider">Total Pembayaran</td>
                        <td class="py-4 px-4 text-right font-bold text-lg text-amber">
                            Rp {{ number_format($subscription->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Footer / Notes -->
        <div class="text-sm text-ink-muted border-t border-hairline pt-6">
            <p>Terima kasih telah menggunakan {{ config('app.name') }}. Jika Anda memiliki pertanyaan mengenai tagihan ini, silakan hubungi dukungan pelanggan kami.</p>
        </div>

        <!-- Tombol Aksi (Sembunyi saat diprint) -->
        <div class="mt-8 flex justify-end gap-4 print:hidden">
            <a href="{{ route('dashboard.billing') }}" class="px-6 py-2 text-sm font-medium text-ink-muted hover:text-ink transition-colors">
                Kembali
            </a>
            <button onclick="window.print()" class="bg-amber hover:bg-amber/90 text-ink font-medium py-2 px-6 rounded-sm transition-colors text-sm shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak Invoice
            </button>
        </div>
    </div>
</div>
