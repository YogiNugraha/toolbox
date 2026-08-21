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
        <div class="card p-5 sm:p-6 border {{ $activeSubscription ? 'border-primary/50 dark:border-accent/50 shadow-md' : 'border-slate-200/80 dark:border-navy-600' }}">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-slate-150 dark:border-navy-500">
                <div class="flex items-center space-x-3">
                    <div class="mask is-squircle flex size-12 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <h3 class="text-xl font-bold text-slate-800 dark:text-navy-100">
                                @if($activeSubscription)
                                    {{ $activeSubscription->plan->name ?? ucfirst($activeSubscription->plan_slug) }}
                                @else
                                    Free Plan
                                @endif
                            </h3>
                            @if($activeSubscription)
                                <span class="badge rounded-full bg-success/10 text-success dark:bg-success/15 text-xs font-semibold px-2.5 py-0.5">
                                    Aktif
                                </span>
                            @else
                                <span class="badge rounded-full bg-slate-100 text-slate-600 dark:bg-navy-500 dark:text-navy-200 text-xs font-semibold px-2.5 py-0.5">
                                    Dasar
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-400 dark:text-navy-300 mt-0.5">
                            @if($activeSubscription)
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

        {{-- Transactions Table (Table Advanced from Lineone) --}}
        <div>
            {{-- Toolbar Header --}}
            <div class="flex items-center justify-between">
                <h2 class="text-base font-medium tracking-wide text-slate-700 line-clamp-1 dark:text-navy-100">
                    Riwayat Transaksi & Tagihan
                </h2>

                <div class="flex items-center space-x-2">
                    {{-- Search Input --}}
                    <div class="flex items-center" x-data="{ isInputActive: {{ !empty($searchTrx) ? 'true' : 'false' }} }">
                        <label class="block">
                            <input
                                x-effect="isInputActive === true && $nextTick(() => { $el.focus() });"
                                :class="isInputActive ? 'w-36 lg:w-56' : 'w-0'"
                                class="form-input bg-transparent px-1 text-right transition-all duration-100 placeholder:text-slate-500 dark:placeholder:text-navy-200 text-sm"
                                placeholder="Cari Order ID, paket..."
                                type="text"
                                wire:model.live.debounce.300ms="searchTrx"
                            />
                        </label>
                        <button
                            @click="isInputActive = !isInputActive"
                            class="btn size-8 rounded-full p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25"
                            title="Pencarian"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>

                    {{-- Filter Dropdown --}}
                    <div
                        x-data="usePopper({ placement: 'bottom-end', offset: 4 })"
                        @click.outside="if(isShowPopper) isShowPopper = false"
                        class="inline-flex"
                    >
                        <button
                            x-ref="popperRef"
                            @click="isShowPopper = !isShowPopper"
                            class="btn size-8 rounded-full p-0 {{ $statusFilter ? 'bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light' : 'text-slate-500 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:text-navy-200 dark:hover:bg-navy-300/20' }}"
                            title="Filter Status"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                            </svg>
                        </button>
                        <div
                            x-ref="popperRoot"
                            class="popper-root"
                            :class="isShowPopper && 'show'"
                        >
                            <div class="popper-box rounded-md border border-slate-150 bg-white p-4 font-inter dark:border-navy-500 dark:bg-navy-700 w-60 shadow-xl text-left">
                                <div class="flex items-center justify-between pb-2 border-b border-slate-150 dark:border-navy-500 mb-3">
                                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-navy-300">Filter Transaksi</p>
                                    @if($statusFilter || $searchTrx)
                                        <button wire:click="resetFilters" class="text-xs text-primary dark:text-accent-light hover:underline">Reset</button>
                                    @endif
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-slate-600 dark:text-navy-200">Status Pembayaran</label>
                                    <select wire:model.live="statusFilter" class="form-select mt-1 h-8 w-full rounded-md border border-slate-300 bg-white px-2.5 text-xs hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-800 dark:hover:border-navy-400 dark:focus:border-accent">
                                        <option value="">Semua Status</option>
                                        <option value="active">Berhasil (Aktif)</option>
                                        <option value="pending">Menunggu</option>
                                        <option value="failed">Gagal / Ditolak</option>
                                        <option value="cancelled">Dibatalkan</option>
                                        <option value="expired">Expired</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table Container --}}
            <div class="card mt-3">
                @if ($history->count() > 0)
                    <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
                        <table class="is-hoverable w-full text-left">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap rounded-tl-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs">
                                        Order ID
                                    </th>
                                    <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs">
                                        Tanggal
                                    </th>
                                    <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs">
                                        Paket
                                    </th>
                                    <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs">
                                        Nominal
                                    </th>
                                    <th class="whitespace-nowrap bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs">
                                        Status
                                    </th>
                                    <th class="whitespace-nowrap rounded-tr-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs text-right">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-150 dark:divide-navy-500">
                                @foreach ($history as $trx)
                                    <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500 hover:bg-slate-50/80 dark:hover:bg-navy-700/50 transition-colors">
                                        {{-- Order ID --}}
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            <a href="{{ route('dashboard.invoice', ['order_id' => $trx->midtrans_order_id]) }}"
                                               class="font-semibold text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent text-sm">
                                                {{ $trx->midtrans_order_id }}
                                            </a>
                                            @if($trx->midtrans_transaction_id)
                                                <p class="text-[10px] text-slate-400 dark:text-navy-300 font-mono mt-0.5">
                                                    {{ $trx->midtrans_transaction_id }}
                                                </p>
                                            @endif
                                        </td>

                                        {{-- Tanggal --}}
                                        <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-600 dark:text-navy-200 sm:px-5">
                                            {{ $trx->created_at->translatedFormat('d M Y, H:i') }}
                                        </td>

                                        {{-- Paket --}}
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5 font-medium text-slate-800 dark:text-navy-100 capitalize text-sm">
                                            {{ $trx->plan->name ?? $trx->plan_slug }}
                                        </td>

                                        {{-- Nominal --}}
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5 font-bold text-slate-800 dark:text-navy-50 text-sm">
                                            Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                        </td>

                                        {{-- Status --}}
                                        <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                            @if($trx->status === 'active')
                                                <span class="badge rounded-full bg-success/10 text-success dark:bg-success/15 px-2.5 py-0.5 text-xs font-semibold">
                                                    Berhasil
                                                </span>
                                            @elseif($trx->status === 'pending')
                                                <span class="badge rounded-full bg-warning/10 text-warning dark:bg-warning/15 px-2.5 py-0.5 text-xs font-semibold animate-pulse">
                                                    Menunggu
                                                </span>
                                            @elseif($trx->status === 'expired')
                                                <span class="badge rounded-full bg-slate-150 text-slate-700 dark:bg-navy-500 dark:text-navy-100 px-2.5 py-0.5 text-xs font-semibold">
                                                    Expired
                                                </span>
                                            @elseif($trx->status === 'cancelled')
                                                <span class="badge rounded-full bg-slate-150 text-slate-700 dark:bg-navy-500 dark:text-navy-100 px-2.5 py-0.5 text-xs font-semibold">
                                                    Dibatalkan
                                                </span>
                                            @else
                                                <span class="badge rounded-full bg-error/10 text-error dark:bg-error/15 px-2.5 py-0.5 text-xs font-semibold">
                                                    Gagal
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Aksi --}}
                                        <td class="whitespace-nowrap px-4 py-3 text-right sm:px-5">
                                            <div class="flex items-center justify-end space-x-2">
                                                @if($trx->status === 'pending')
                                                    <button
                                                        wire:click="syncPayment('{{ $trx->midtrans_order_id }}')"
                                                        class="btn h-7 rounded-md border border-slate-300 px-2 text-[11px] font-semibold text-slate-700 hover:bg-slate-100 dark:border-navy-500 dark:text-navy-100"
                                                        title="Cek Pembayaran"
                                                    >
                                                        Cek
                                                    </button>
                                                @endif
                                                <a
                                                    href="{{ route('dashboard.invoice', ['order_id' => $trx->midtrans_order_id]) }}"
                                                    class="btn h-7 space-x-1 rounded-md bg-primary/10 text-primary hover:bg-primary hover:text-white dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent dark:hover:text-white px-2.5 text-xs font-semibold transition-colors"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    <span>Invoice</span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Table Advanced Footer (Show entries + Pagination) --}}
                    <div class="flex flex-col justify-between space-y-4 px-4 py-4 sm:flex-row sm:items-center sm:space-y-0 sm:px-5 border-t border-slate-150 dark:border-navy-500">
                        {{-- Per Page Select --}}
                        <div class="flex items-center space-x-2 text-xs">
                            <span>Tampilkan</span>
                            <label class="block">
                                <select
                                    wire:model.live="perPage"
                                    class="form-select rounded-full border border-slate-300 bg-white px-2 py-1 pr-6 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent text-xs"
                                >
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                            </label>
                            <span>transaksi per halaman</span>
                        </div>

                        {{-- Pagination Links --}}
                        <div>
                            {{ $history->links('components.lineone-pagination') }}
                        </div>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-16 px-4 text-center">
                        <div class="mask is-squircle size-16 bg-slate-100 text-slate-400 dark:bg-navy-700 dark:text-navy-300 flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                            </svg>
                        </div>
                        <h4 class="text-base font-bold text-slate-700 dark:text-navy-100">Tidak Ada Transaksi</h4>
                        <p class="mt-1 text-xs text-slate-400 dark:text-navy-300 max-w-sm">
                            @if($searchTrx || $statusFilter)
                                Tidak ditemukan transaksi dengan filter yang dipilih.
                            @else
                                Anda belum memiliki riwayat transaksi langganan.
                            @endif
                        </p>
                        @if($searchTrx || $statusFilter)
                            <button wire:click="resetFilters" class="btn mt-4 bg-primary text-white dark:bg-accent h-8 px-4 rounded-lg text-xs font-semibold">
                                Reset Filter
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
