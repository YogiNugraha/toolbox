<div>
    <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">Billing & Langganan</h2>

    @if (session()->has('info'))
        <div class="mt-4 rounded-lg bg-info/10 px-4 py-3 text-sm text-info dark:bg-info/15">
            {{ session('info') }}
        </div>
    @endif

    @if (session()->has('message'))
        <div class="mt-4 rounded-lg bg-slate-100 px-4 py-3 text-sm text-slate-600 dark:bg-navy-600 dark:text-navy-100">
            {{ session('message') }}
        </div>
    @endif

    {{-- Current Plan Info --}}
    <div class="card mt-5 px-4 py-4 sm:px-5 {{ $activeSubscription ? 'border border-primary dark:border-accent' : '' }}">
        @if ($activeSubscription)
            <div class="absolute top-0 right-0 rounded-bl-lg bg-primary px-3 py-1 text-xs font-semibold text-white dark:bg-accent">
                {{ strtoupper($activeSubscription->plan->name ?? $activeSubscription->plan_slug) }}
            </div>
        @endif

        <p class="text-xs-plus uppercase tracking-wide text-slate-400 dark:text-navy-300">Paket Saat Ini</p>

        @if ($activeSubscription)
            <div class="mt-3 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <h3 class="text-2xl font-semibold text-slate-700 dark:text-navy-100">
                        {{ $activeSubscription->plan->name ?? ucfirst($activeSubscription->plan_slug) }}
                    </h3>
                    <span class="badge bg-success/10 text-success dark:bg-success/15">Aktif</span>
                </div>
                <button wire:click="confirmCancel"
                    class="text-xs font-medium text-error underline transition-colors hover:text-error/70">
                    Berhenti Berlangganan
                </button>
            </div>

            @php $daysRemaining = ceil(now()->diffInDays($activeSubscription->expires_at, false)); @endphp

            @if($pending)
                <div class="mt-4 flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-navy-500 dark:bg-navy-600">
                    <div>
                        <p class="text-sm font-medium text-slate-700 dark:text-navy-100">Kamu punya pembayaran yang belum diselesaikan</p>
                        <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">Order #{{ $pending->midtrans_order_id }} · Rp {{ number_format($pending->amount, 0, ',', '.') }}</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button wire:click="syncPayment('{{ $pending->midtrans_order_id }}')"
                            class="btn border border-slate-300 px-4 py-2 text-xs font-medium text-slate-800 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-50 dark:hover:bg-navy-500">
                            <span wire:loading.remove wire:target="syncPayment('{{ $pending->midtrans_order_id }}')">Cek Status</span>
                            <span wire:loading wire:target="syncPayment('{{ $pending->midtrans_order_id }}')">Mengecek...</span>
                        </button>
                        <button wire:click="renew"
                            class="btn bg-primary px-4 py-2 text-xs font-medium text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus">
                            Selesaikan
                        </button>
                    </div>
                </div>
            @elseif($daysRemaining > 7)
                <p class="mt-3 text-sm text-slate-400 dark:text-navy-300">
                    Berlaku sampai <span class="font-medium text-slate-700 dark:text-navy-100">{{ $activeSubscription->expires_at->translatedFormat('d F Y, H:i') }}</span>
                </p>
            @elseif($daysRemaining >= 0)
                <div class="mt-4 flex items-center justify-between rounded-lg border border-warning/40 bg-warning/5 p-4 dark:border-warning/30 dark:bg-warning/10">
                    <p class="text-sm text-slate-700 dark:text-navy-100">
                        Langganan <strong>{{ $activeSubscription->plan->name ?? 'Premium' }}</strong> berakhir dalam <span class="font-semibold">{{ $daysRemaining }} hari</span>
                    </p>
                    <button wire:click="renew" class="btn bg-warning px-4 py-2 text-xs font-medium text-white hover:bg-warning-focus">Perpanjang Sekarang</button>
                </div>
            @else
                <div class="mt-4 flex items-center justify-between rounded-lg border border-error/30 bg-error/5 p-4 dark:border-error/20 dark:bg-error/10">
                    <p class="text-sm text-slate-700 dark:text-navy-100">Langganan <strong>{{ $activeSubscription->plan->name ?? 'Premium' }}</strong> kamu sudah berakhir.</p>
                    <button wire:click="renew" class="btn bg-primary px-4 py-2 text-xs font-medium text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus">Perpanjang Langganan</button>
                </div>
            @endif
        @else
            <div class="mt-3 flex items-center space-x-3">
                <h3 class="text-2xl font-semibold text-slate-700 dark:text-navy-100">Free</h3>
            </div>
            <p class="mt-1 text-sm text-slate-400 dark:text-navy-300">Kamu masih pakai paket Free.</p>

            @if($pending)
                <div class="mt-4 flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-navy-500 dark:bg-navy-600">
                    <div>
                        <p class="text-sm font-medium text-slate-700 dark:text-navy-100">Kamu punya pembayaran yang belum diselesaikan</p>
                        <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">Order #{{ $pending->midtrans_order_id }} · Rp {{ number_format($pending->amount, 0, ',', '.') }}</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button wire:click="syncPayment('{{ $pending->midtrans_order_id }}')"
                            class="btn border border-slate-300 px-4 py-2 text-xs font-medium text-slate-800 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-50 dark:hover:bg-navy-500">
                            <span wire:loading.remove wire:target="syncPayment('{{ $pending->midtrans_order_id }}')">Cek Status</span>
                            <span wire:loading wire:target="syncPayment('{{ $pending->midtrans_order_id }}')">Mengecek...</span>
                        </button>
                        <button wire:click="renew"
                            class="btn bg-primary px-4 py-2 text-xs font-medium text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus">
                            Selesaikan
                        </button>
                    </div>
                </div>
            @else
                <a href="{{ route('pricing') }}"
                    class="btn mt-4 bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                    Upgrade Paket
                </a>
            @endif
        @endif
    </div>

    {{-- Transaction History --}}
    <div class="card mt-5">
        <div class="flex items-center justify-between px-4 py-4 sm:px-5">
            <h2 class="font-medium tracking-wide text-slate-700 dark:text-navy-100 lg:text-base">Riwayat Transaksi</h2>
        </div>

        @if ($history->isEmpty())
            <div class="flex flex-col items-center justify-center px-4 py-12 sm:px-5">
                <p class="text-slate-400 dark:text-navy-300">Belum ada riwayat transaksi.</p>
            </div>
        @else
            <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
                <table class="is-hoverable w-full text-left">
                    <thead>
                        <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500">
                            <th class="whitespace-nowrap px-3 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Order ID</th>
                            <th class="whitespace-nowrap px-3 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Tanggal</th>
                            <th class="whitespace-nowrap px-3 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Paket</th>
                            <th class="whitespace-nowrap px-3 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Nominal</th>
                            <th class="whitespace-nowrap px-3 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($history as $trx)
                            <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500">
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    <a href="{{ route('dashboard.invoice', ['order_id' => $trx->midtrans_order_id]) }}"
                                       class="font-medium text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent">
                                        {{ $trx->midtrans_order_id }}
                                    </a>
                                    @if($trx->midtrans_transaction_id)
                                        <p class="mt-0.5 text-tiny-plus text-slate-400 dark:text-navy-300">{{ $trx->midtrans_transaction_id }}</p>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    {{ $trx->created_at->translatedFormat('d M Y, H:i') }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 capitalize sm:px-5">
                                    {{ $trx->plan_slug }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 font-medium text-slate-700 dark:text-navy-100 sm:px-5">
                                    Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                    @if($trx->status === 'active')
                                        <span class="badge bg-success/10 text-success dark:bg-success/15">Berhasil</span>
                                    @elseif($trx->status === 'pending')
                                        <span class="badge bg-warning/10 text-warning dark:bg-warning/15">Menunggu</span>
                                    @elseif($trx->status === 'expired')
                                        <span class="badge bg-slate-100 text-slate-600 dark:bg-navy-500 dark:text-navy-100">Expired</span>
                                    @elseif($trx->status === 'cancelled')
                                        <span class="badge bg-slate-100 text-slate-600 dark:bg-navy-500 dark:text-navy-100">Dibatalkan</span>
                                    @elseif($trx->status === 'failed')
                                        <span class="badge bg-error/10 text-error dark:bg-error/15">Gagal</span>
                                    @else
                                        <span class="badge bg-slate-100 text-slate-600 dark:bg-navy-500 dark:text-navy-100">{{ ucfirst($trx->status) }}</span>
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
