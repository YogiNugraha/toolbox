<div>
    <h2 class="text-2xl font-display font-bold text-ink mb-6">Billing & Langganan</h2>

    @if (request()->query('status') === 'success')
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Berhasil!</strong>
            <span class="block sm:inline">Pembayaran Anda sedang diproses. Status langganan akan otomatis aktif setelah pembayaran terverifikasi oleh sistem.</span>
        </div>
    @elseif(request()->query('status') === 'pending')
        <div class="mb-6 bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Menunggu Pembayaran!</strong>
            <span class="block sm:inline">Silakan selesaikan pembayaran Anda. Status akan diperbarui otomatis setelah berhasil.</span>
        </div>
    @endif

    @if (session()->has('info'))
        <div class="mb-6 bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('info') }}</span>
        </div>
    @endif

    @if (session()->has('message'))
        <div class="mb-6 bg-slate-50 border border-slate-200 text-slate-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Current Plan Info -->
        <div class="col-span-1 md:col-span-2 bg-white border {{ $activeSubscription ? 'border-amber shadow-sm' : 'border-hairline' }} rounded-sm p-6 flex flex-col justify-between relative overflow-hidden">
            @if ($activeSubscription)
                <div class="absolute top-0 right-0 bg-amber text-ink text-xs font-bold px-3 py-1 border-b border-l border-amber">PRO</div>
            @endif
            <div>
                <h3 class="text-sm font-medium text-ink-muted uppercase tracking-wider mb-2">Paket Saat Ini</h3>
                @if ($activeSubscription)
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <span class="text-3xl font-display font-extrabold text-ink">Pro</span>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                Aktif
                            </span>
                        </div>
                        <button 
                            wire:click="confirmCancel" 
                            class="text-xs text-red-500 hover:text-red-700 underline font-medium whitespace-nowrap"
                        >
                            Berhenti Berlangganan
                        </button>
                    </div>
                    
                    @php
                        $daysRemaining = ceil(now()->diffInDays($activeSubscription->expires_at, false));
                    @endphp

                    @if($pending)
                        <div class="border border-slate-300 bg-slate-50 rounded-sm p-4 flex items-center justify-between mt-4">
                            <div>
                                <p class="text-sm text-ink font-medium">Kamu punya pembayaran yang belum diselesaikan</p>
                                <p class="font-mono text-xs text-ink-muted mt-1">Order #{{ $pending->midtrans_order_id }} · Rp {{ number_format($pending->amount, 0, ',', '.') }}</p>
                            </div>
                            <button wire:click="renew" class="bg-slate-700 text-white font-medium px-4 py-2 rounded-sm text-sm whitespace-nowrap hover:bg-slate-800 transition-colors">
                                Selesaikan Pembayaran
                            </button>
                        </div>
                    @elseif($daysRemaining > 7)
                        <p class="text-sm text-ink-muted">
                            Berlaku sampai
                            <span class="text-ink font-medium">{{ $activeSubscription->expires_at->translatedFormat('d F Y, H:i') }}</span>
                        </p>
                    @elseif($daysRemaining >= 0)
                        <div class="border border-amber/40 bg-amber/5 rounded-sm p-4 flex items-center justify-between mt-4">
                            <p class="text-sm text-ink">
                                Langganan Pro kamu berakhir dalam <span class="font-mono font-medium">{{ $daysRemaining }} hari</span> 
                                ({{ $activeSubscription->expires_at->translatedFormat('d F Y') }})
                            </p>
                            <button wire:click="renew" class="bg-amber text-ink font-medium px-4 py-2 rounded-sm text-sm whitespace-nowrap hover:bg-amber/90 transition-colors">
                                Perpanjang Sekarang
                            </button>
                        </div>
                    @else
                        <div class="border border-red-300 bg-red-50 rounded-sm p-4 flex items-center justify-between mt-4">
                            <p class="text-sm text-ink">Langganan Pro kamu sudah berakhir.</p>
                            <button wire:click="renew" class="bg-amber text-ink font-medium px-4 py-2 rounded-sm text-sm whitespace-nowrap hover:bg-amber/90 transition-colors">
                                Perpanjang Langganan
                            </button>
                        </div>
                    @endif

                @else
                    <div class="flex items-center gap-3 mb-4">
                        <span class="text-3xl font-display font-extrabold text-ink">Free</span>
                    </div>
                    <p class="text-ink-muted text-sm mb-4">
                        Kamu masih pakai paket Free.
                    </p>
                    
                    @if($pending)
                        <div class="border border-slate-300 bg-slate-50 rounded-sm p-4 flex items-center justify-between mt-4">
                            <div>
                                <p class="text-sm text-ink font-medium">Kamu punya pembayaran yang belum diselesaikan</p>
                                <p class="font-mono text-xs text-ink-muted mt-1">Order #{{ $pending->midtrans_order_id }} · Rp {{ number_format($pending->amount, 0, ',', '.') }}</p>
                            </div>
                            <button wire:click="renew" class="bg-slate-700 text-white font-medium px-4 py-2 rounded-sm text-sm whitespace-nowrap hover:bg-slate-800 transition-colors">
                                Selesaikan Pembayaran
                            </button>
                        </div>
                    @else
                        <a href="{{ route('pricing') }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent bg-amber text-ink hover:bg-amber/90 font-medium rounded-sm transition-colors shadow-sm text-sm">
                            Upgrade ke Pro
                        </a>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="bg-white border border-hairline rounded-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-hairline">
            <h3 class="text-lg font-medium text-ink">Riwayat Transaksi</h3>
        </div>
        
        @if ($history->isEmpty())
            <div class="p-8 text-center text-ink-muted">
                Belum ada riwayat transaksi.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-hairline">
                    <thead class="bg-paper">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-ink-muted uppercase tracking-wider">Order ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-ink-muted uppercase tracking-wider">Tanggal</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-ink-muted uppercase tracking-wider">Paket</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-ink-muted uppercase tracking-wider">Nominal</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-ink-muted uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-hairline">
                        @foreach ($history as $trx)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-ink-muted">
                                    <a href="{{ route('dashboard.invoice', ['order_id' => $trx->midtrans_order_id]) }}" class="hover:text-amber hover:underline transition-colors flex items-center gap-1 group">
                                        {{ $trx->midtrans_order_id }}
                                        <svg class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-ink">
                                    {{ $trx->created_at->translatedFormat('d M Y, H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-ink capitalize">
                                    {{ $trx->plan_slug }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-ink">
                                    Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($trx->status === 'active')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                                    @elseif($trx->status === 'pending')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                    @elseif($trx->status === 'expired')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Expired</span>
                                    @elseif($trx->status === 'failed')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Gagal</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ $trx->status }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
