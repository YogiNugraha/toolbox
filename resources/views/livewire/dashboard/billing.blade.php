<div>
    <h2 class="text-2xl font-display font-bold text-ink mb-6">Billing & Langganan</h2>

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

    <div class="mb-8">
        <!-- Current Plan Info -->
        <div class="bg-white border {{ $activeSubscription ? 'border-amber shadow-sm' : 'border-hairline' }} rounded-sm p-6 flex flex-col justify-between relative overflow-hidden">
            @if ($activeSubscription)
                <div class="absolute top-0 right-0 bg-amber text-ink text-xs font-bold px-3 py-1 border-b border-l border-amber">{{ strtoupper($activeSubscription->plan->name ?? $activeSubscription->plan_slug) }}</div>
            @endif
            <div>
                <h3 class="text-sm font-medium text-ink-muted uppercase tracking-wider mb-2">Paket Saat Ini</h3>
                @if ($activeSubscription)
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <span class="text-3xl font-display font-extrabold text-ink">{{ $activeSubscription->plan->name ?? ucfirst($activeSubscription->plan_slug) }}</span>
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
                            <div class="flex items-center gap-2">
                                <button wire:click="syncPayment('{{ $pending->midtrans_order_id }}')" class="bg-amber/10 text-amber hover:bg-amber/20 font-medium px-4 py-2 rounded-sm text-sm whitespace-nowrap transition-colors border border-amber/20">
                                    <span wire:loading.remove wire:target="syncPayment('{{ $pending->midtrans_order_id }}')">Cek Status</span>
                                    <span wire:loading wire:target="syncPayment('{{ $pending->midtrans_order_id }}')">Mengecek...</span>
                                </button>
                                <button wire:click="renew" class="bg-slate-700 text-white font-medium px-4 py-2 rounded-sm text-sm whitespace-nowrap hover:bg-slate-800 transition-colors">
                                    Selesaikan
                                </button>
                            </div>
                        </div>
                    @elseif($daysRemaining > 7)
                        <p class="text-sm text-ink-muted">
                            Berlaku sampai
                            <span class="text-ink font-medium">{{ $activeSubscription->expires_at->translatedFormat('d F Y, H:i') }}</span>
                        </p>
                    @elseif($daysRemaining >= 0)
                        <div class="border border-amber/40 bg-amber/5 rounded-sm p-4 flex items-center justify-between mt-4">
                            <p class="text-sm text-ink">
                                Langganan <strong>{{ $activeSubscription->plan->name ?? 'Premium' }}</strong> kamu berakhir dalam <span class="font-mono font-medium">{{ $daysRemaining }} hari</span> 
                                ({{ $activeSubscription->expires_at->translatedFormat('d F Y') }})
                            </p>
                            <button wire:click="renew" class="bg-amber text-ink font-medium px-4 py-2 rounded-sm text-sm whitespace-nowrap hover:bg-amber/90 transition-colors">
                                Perpanjang Sekarang
                            </button>
                        </div>
                    @else
                        <div class="border border-red-300 bg-red-50 rounded-sm p-4 flex items-center justify-between mt-4">
                            <p class="text-sm text-ink">Langganan <strong>{{ $activeSubscription->plan->name ?? 'Premium' }}</strong> kamu sudah berakhir.</p>
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
                            <div class="flex items-center gap-2">
                                <button wire:click="syncPayment('{{ $pending->midtrans_order_id }}')" class="bg-amber/10 text-amber hover:bg-amber/20 font-medium px-4 py-2 rounded-sm text-sm whitespace-nowrap transition-colors border border-amber/20">
                                    <span wire:loading.remove wire:target="syncPayment('{{ $pending->midtrans_order_id }}')">Cek Status</span>
                                    <span wire:loading wire:target="syncPayment('{{ $pending->midtrans_order_id }}')">Mengecek...</span>
                                </button>
                                <button wire:click="renew" class="bg-slate-700 text-white font-medium px-4 py-2 rounded-sm text-sm whitespace-nowrap hover:bg-slate-800 transition-colors">
                                    Selesaikan
                                </button>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('pricing') }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent bg-amber text-ink hover:bg-amber/90 font-medium rounded-sm transition-colors shadow-sm text-sm">
                            Upgrade Paket
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
                                    <a href="{{ route('dashboard.invoice', ['order_id' => $trx->midtrans_order_id]) }}" class="hover:text-amber hover:underline transition-colors flex items-center gap-1 group" title="Order ID">
                                        {{ $trx->midtrans_order_id }}
                                        <svg class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    </a>
                                    @if($trx->midtrans_transaction_id)
                                        <div class="text-[10px] mt-0.5" title="Midtrans Transaction ID">{{ $trx->midtrans_transaction_id }}</div>
                                    @endif
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
                                        <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-sm">Berhasil</span>
                                    @elseif($trx->status === 'pending')
                                        <span class="px-2 py-1 text-xs font-semibold bg-amber/20 text-amber rounded-sm">Menunggu</span>
                                    @elseif($trx->status === 'expired')
                                        <span class="px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-700 rounded-sm">Expired</span>
                                    @elseif($trx->status === 'cancelled')
                                        <span class="px-2 py-1 text-xs font-semibold bg-slate-100 text-slate-700 rounded-sm">Dibatalkan</span>
                                    @elseif($trx->status === 'failed')
                                        <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-700 rounded-sm">Gagal</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-700 rounded-sm">{{ ucfirst($trx->status) }}</span>
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
