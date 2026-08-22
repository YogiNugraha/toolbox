<div>
    @section('title', 'Billing & Langganan - ' . config('app.name'))
    @section('page_title', 'Billing & Langganan')
    @section('page_breadcrumb', 'Billing')

    <div class="space-y-6">
        {{-- Flash Messages --}}
        @if (session()->has('info'))
            <div class="alert flex rounded-lg border border-info px-4 py-3.5 text-info sm:px-5">
                {{ session('info') }}
            </div>
        @endif

        @if (session()->has('message'))
            <div class="alert flex rounded-lg border border-success px-4 py-3.5 text-success sm:px-5">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert flex rounded-lg border border-error px-4 py-3.5 text-error sm:px-5">
                {{ session('error') }}
            </div>
        @endif

        {{-- Current Plan Card (Lineone Card) --}}
        @php
            $isSub = (bool) $activeSubscription;
            $planName = $isSub ? ($activeSubscription->plan->name ?? ucfirst($activeSubscription->plan_slug)) : 'Free Plan';
            $isProMax = $isSub && ($activeSubscription->plan_slug === 'pro-max' || strtolower((string)$planName) === 'pro max');
        @endphp
        <div class="card p-5 sm:p-6 border {{ $isSub ? ($isProMax ? 'border-purple-500/50 bg-linear-to-r from-purple-50/40 via-white to-white dark:from-purple-950/20 dark:via-navy-700 dark:to-navy-700 shadow-md shadow-purple-500/10' : 'border-amber-400/50 bg-linear-to-r from-amber-50/40 via-white to-white dark:from-amber-950/20 dark:via-navy-700 dark:to-navy-700 shadow-md shadow-amber-400/10') : 'border-slate-200/80 dark:border-navy-600' }}">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-slate-150 dark:border-navy-500">
                <div class="flex items-center space-x-3">
                    <div class="mask is-squircle flex size-12 items-center justify-center {{ $isSub ? ($isProMax ? 'bg-linear-to-tr from-purple-600 to-indigo-500 text-white' : 'bg-linear-to-tr from-amber-400 to-orange-500 text-white') : 'bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light' }} shadow-xs">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <h3 class="text-xl font-bold text-slate-800 dark:text-navy-100">
                                {{ $planName }}
                            </h3>
                            @if($isSub)
                                @if($isProMax)
                                    <span class="badge rounded-full bg-linear-to-r from-amber-500 via-purple-600 to-indigo-600 text-white text-[10px] font-black px-2.5 py-0.5 shadow-xs uppercase tracking-wider inline-flex items-center gap-1">
                                        <x-lucide-crown class="size-3 stroke-[2.5]" />
                                        <span>{{ $planName }} (Aktif)</span>
                                    </span>
                                @else
                                    <span class="badge rounded-full bg-linear-to-r from-amber-500 to-orange-500 text-white text-[10px] font-black px-2.5 py-0.5 shadow-xs uppercase tracking-wider inline-flex items-center gap-1">
                                        <x-lucide-star class="size-3 stroke-[2.5] fill-current" />
                                        <span>{{ $planName }} (Aktif)</span>
                                    </span>
                                @endif
                            @else
                                <span class="badge rounded-full bg-slate-100 text-slate-600 dark:bg-navy-500 dark:text-navy-200 text-xs font-semibold px-2.5 py-0.5">
                                    Dasar
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-400 dark:text-navy-300 mt-0.5">
                            @if($isSub)
                                Akses tanpa batas ke semua fitur dan alat pemrosesan.
                            @else
                                Kuota standar harian untuk pemrosesan file.
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    @if($activeSubscription)
                        <button
                            wire:click="confirmCancel"
                            class="btn h-9 rounded-lg border border-error/30 text-error hover:bg-error/10 text-xs font-semibold px-3"
                        >
                            Berhenti Berlangganan
                        </button>
                        <a href="{{ route('pricing') }}" class="btn h-9 rounded-lg bg-primary text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus text-xs font-semibold px-4 shadow-sm">
                            Ganti / Perpanjang Paket
                        </a>
                    @else
                        <a href="{{ route('pricing') }}" class="btn h-9 rounded-lg bg-primary text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus text-xs font-semibold px-5 shadow-sm">
                            Upgrade ke Pro
                        </a>
                    @endif
                </div>
            </div>

            {{-- Subscription Details / Pending Box --}}
            @if ($pending)
                <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-xl border border-warning/40 bg-warning/5 p-4 dark:border-warning/30 dark:bg-warning/10">
                    <div class="flex items-start space-x-3">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-warning/15 text-warning">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-800 dark:text-navy-100">
                                Anda memiliki pembayaran yang belum diselesaikan
                            </p>
                            <p class="text-xs text-slate-500 dark:text-navy-300 mt-0.5">
                                Order <strong>#{{ $pending->midtrans_order_id }}</strong> · Total: <strong>Rp {{ number_format($pending->amount, 0, ',', '.') }}</strong>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 shrink-0">
                        <button
                            wire:click="syncPayment('{{ $pending->midtrans_order_id }}')"
                            class="btn h-8 rounded-lg border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-700 hover:bg-slate-100 dark:border-navy-450 dark:bg-navy-700 dark:text-navy-100"
                        >
                            <span wire:loading.remove wire:target="syncPayment('{{ $pending->midtrans_order_id }}')">Cek Status</span>
                            <span wire:loading wire:target="syncPayment('{{ $pending->midtrans_order_id }}')">Mengecek...</span>
                        </button>
                        <button
                            wire:click="renew"
                            class="btn h-8 rounded-lg bg-primary px-3 text-xs font-semibold text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus"
                        >
                            Selesaikan
                        </button>
                    </div>
                </div>
            @elseif($activeSubscription)
                @php $daysRemaining = ceil(now()->diffInDays($activeSubscription->expires_at, false)); @endphp
                <div class="mt-4 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500 dark:text-navy-300">
                    <div>
                        <span>Mulai: <strong class="text-slate-700 dark:text-navy-100">{{ $activeSubscription->starts_at ? $activeSubscription->starts_at->translatedFormat('d M Y') : '-' }}</strong></span>
                        <span class="mx-2">&bull;</span>
                        <span>Berakhir: <strong class="text-slate-700 dark:text-navy-100">{{ $activeSubscription->expires_at ? $activeSubscription->expires_at->translatedFormat('d M Y, H:i') : 'Selamanya' }}</strong></span>
                    </div>
                    <div>
                        @if($daysRemaining > 7)
                            <span class="badge rounded-full bg-slate-100 text-slate-600 dark:bg-navy-600 dark:text-navy-200 px-2.5 py-0.5 font-medium">
                                Tersisa {{ $daysRemaining }} hari lagi
                            </span>
                        @elseif($daysRemaining >= 0)
                            <span class="badge rounded-full bg-warning/15 text-warning px-2.5 py-0.5 font-bold">
                                Berakhir dalam {{ $daysRemaining }} hari
                            </span>
                        @else
                            <span class="badge rounded-full bg-error/15 text-error px-2.5 py-0.5 font-bold">
                                Langganan Kedaluwarsa
                            </span>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Transactions Table (Aligned with Lineone Admin Transactions) --}}
        <div class="mt-6">
            <div class="card">
                {{-- Filters Bar --}}
                <div class="flex flex-col justify-between gap-3 p-4 sm:flex-row sm:items-center sm:px-5 border-b border-slate-150 dark:border-navy-600">
                    {{-- Search Bar --}}
                    <div class="relative flex w-full sm:w-80">
                        <input wire:model.live.debounce.300ms="searchTrx" type="text" placeholder="Cari Order ID, nama paket..."
                            class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-1.5 pl-9 text-xs placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                        <span class="pointer-events-none absolute flex h-full w-9 items-center justify-center text-slate-400 dark:text-navy-300">
                            <x-lucide-search class="size-4" />
                        </span>
                    </div>
                    
                    {{-- Status Filter --}}
                    <div class="flex items-center space-x-2.5">
                        <select wire:model.live="statusFilter" class="form-select rounded-lg border border-slate-300 bg-transparent px-3 py-1.5 text-xs hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent">
                            <option value="">Semua Status</option>
                            <option value="active">Sukses (Active)</option>
                            <option value="pending">Menunggu (Pending)</option>
                            <option value="expired">Expired</option>
                            <option value="cancelled">Dibatalkan</option>
                            <option value="failed">Gagal</option>
                        </select>
                    </div>
                </div>

                {{-- Table --}}
                <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
                    <table class="is-hoverable w-full text-left">
                        <thead>
                            <tr>
                                <th class="rounded-tl-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs">
                                    ORDER ID & PAKET
                                </th>
                                <th class="bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs text-right">
                                    NOMINAL
                                </th>
                                <th class="bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs text-center">
                                    STATUS
                                </th>
                                <th class="bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs text-right">
                                    TANGGAL
                                </th>
                                <th class="rounded-tr-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs text-right">
                                    AKSI
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-150 dark:divide-navy-500">
                            @forelse ($history as $trx)
                                @php
                                    $planName = $trx->plan->name ?? ucfirst($trx->plan_slug);
                                    $isProMax = ($trx->plan_slug === 'pro-max' || strtolower((string)$planName) === 'pro max');
                                @endphp
                                <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500 hover:bg-slate-50/80 dark:hover:bg-navy-700/50 transition-colors">
                                    {{-- Order ID & Plan --}}
                                    <td class="whitespace-nowrap px-4 py-3 sm:px-5 text-xs">
                                        <a href="{{ route('dashboard.invoice', ['order_id' => $trx->midtrans_order_id]) }}"
                                           class="font-mono font-bold text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent text-sm flex items-center gap-1">
                                            <span>#{{ $trx->midtrans_order_id ?? '-' }}</span>
                                        </a>
                                        <div class="mt-1 flex items-center gap-1.5">
                                            @if($isProMax)
                                                <span class="badge rounded-full bg-linear-to-r from-amber-500 via-purple-600 to-indigo-600 text-white font-black text-[9px] px-2 py-0.5 shadow-xs uppercase tracking-wider inline-flex items-center gap-1">
                                                    <x-lucide-crown class="size-2.5 stroke-[2.5]" />
                                                    <span>{{ $planName }}</span>
                                                </span>
                                            @else
                                                <span class="badge rounded-full bg-linear-to-r from-amber-500 to-orange-500 text-white font-black text-[9px] px-2 py-0.5 shadow-xs uppercase tracking-wider inline-flex items-center gap-1">
                                                    <x-lucide-star class="size-2.5 stroke-[2.5] fill-current" />
                                                    <span>{{ $planName }}</span>
                                                </span>
                                            @endif

                                            @if($trx->midtrans_transaction_id)
                                                <span class="text-[10px] text-slate-400 dark:text-navy-300 font-mono" title="Midtrans Transaction ID">
                                                    {{ Str::limit($trx->midtrans_transaction_id, 12) }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Nominal --}}
                                    <td class="whitespace-nowrap px-4 py-3 text-right sm:px-5">
                                        <div class="font-bold text-slate-700 dark:text-navy-100 text-xs sm:text-sm">
                                            Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                        </div>
                                        <div class="mt-0.5">
                                            <span class="badge rounded-full bg-slate-100 text-slate-600 dark:bg-navy-600 dark:text-navy-200 text-[10px] uppercase font-semibold px-2 py-0.5">
                                                {{ $trx->payment_type ? str_replace('_', ' ', $trx->payment_type) : 'Midtrans' }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Status --}}
                                    <td class="whitespace-nowrap px-4 py-3 text-center sm:px-5">
                                        @if($trx->status === 'active' || $trx->status === 'settlement' || $trx->status === 'capture')
                                            <span class="badge space-x-1.5 rounded-full bg-success/10 text-success text-[11px] font-bold px-2.5 py-1 inline-flex items-center">
                                                <span class="size-1.5 rounded-full bg-current"></span>
                                                <span>Berhasil</span>
                                            </span>
                                        @elseif($trx->status === 'pending')
                                            <span class="badge space-x-1.5 rounded-full bg-warning/10 text-warning text-[11px] font-bold px-2.5 py-1 inline-flex items-center">
                                                <span class="size-1.5 rounded-full bg-current animate-ping"></span>
                                                <span>Menunggu</span>
                                            </span>
                                        @elseif($trx->status === 'expired')
                                            <span class="badge space-x-1.5 rounded-full bg-slate-150 text-slate-600 dark:bg-navy-500 dark:text-navy-200 text-[11px] font-bold px-2.5 py-1 inline-flex items-center">
                                                <span class="size-1.5 rounded-full bg-current"></span>
                                                <span>Expired</span>
                                            </span>
                                        @elseif($trx->status === 'cancelled')
                                            <span class="badge space-x-1.5 rounded-full bg-info/10 text-info dark:bg-info/15 text-[11px] font-bold px-2.5 py-1 inline-flex items-center">
                                                <span class="size-1.5 rounded-full bg-current"></span>
                                                <span>Dibatalkan</span>
                                            </span>
                                        @elseif($trx->status === 'failed')
                                            <span class="badge space-x-1.5 rounded-full bg-error/10 text-error dark:bg-error/15 text-[11px] font-bold px-2.5 py-1 inline-flex items-center">
                                                <span class="size-1.5 rounded-full bg-current"></span>
                                                <span>Gagal</span>
                                            </span>
                                        @else
                                            <span class="badge rounded-full bg-slate-150 text-slate-600 dark:bg-navy-500 dark:text-navy-200 text-[11px] font-bold px-2.5 py-1">
                                                {{ ucfirst($trx->status) }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Tanggal --}}
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-xs text-slate-500 dark:text-navy-300 sm:px-5">
                                        <div>{{ $trx->created_at->translatedFormat('d M Y') }}</div>
                                        <div class="text-[10px] text-slate-400 dark:text-navy-400 mt-0.5">{{ $trx->created_at->format('H:i') }} WIB</div>
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="whitespace-nowrap px-4 py-3 text-right sm:px-5">
                                        <div class="flex items-center justify-end space-x-1.5">
                                            @if($trx->status === 'pending')
                                                <button
                                                    wire:click="syncPayment('{{ $trx->midtrans_order_id }}')"
                                                    class="btn h-7 rounded-md border border-slate-300 px-2 text-[11px] font-semibold text-slate-700 hover:bg-slate-100 dark:border-navy-500 dark:text-navy-100"
                                                    title="Cek Status Pembayaran"
                                                >
                                                    Cek
                                                </button>
                                            @endif
                                            <a
                                                href="{{ route('dashboard.invoice', ['order_id' => $trx->midtrans_order_id]) }}"
                                                class="btn h-7 space-x-1 rounded-md bg-primary/10 text-primary hover:bg-primary hover:text-white dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent dark:hover:text-white px-2.5 text-xs font-semibold transition-colors flex items-center"
                                            >
                                                <x-lucide-receipt class="size-3.5" />
                                                <span>Invoice</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="whitespace-nowrap px-4 py-8 text-center text-slate-400 dark:text-navy-300 text-xs">
                                        <x-lucide-receipt-text class="size-10 mx-auto text-slate-300 dark:text-navy-400 mb-2" />
                                        <p>Tidak ada riwayat transaksi yang ditemukan.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($history->hasPages())
                    <div class="flex flex-col justify-between gap-4 p-4 sm:flex-row sm:items-center sm:px-5 border-t border-slate-150 dark:border-navy-600">
                        <div class="text-xs text-slate-400 dark:text-navy-300">
                            Menampilkan <strong>{{ $history->firstItem() }}</strong> sampai <strong>{{ $history->lastItem() }}</strong> dari <strong>{{ $history->total() }}</strong> transaksi
                        </div>
                        <div>
                            {{ $history->links('components.lineone-pagination') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
