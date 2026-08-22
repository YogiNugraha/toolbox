<div>
    @section('title', 'Semua Transaksi - Admin ' . config('app.name'))
    @section('page_title', 'Riwayat & Manajemen Transaksi')
    @section('page_breadcrumb', 'Transaksi')

    {{-- Header & Subtitle --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 py-4 sm:py-5">
        <div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-navy-50 lg:text-2xl">
                Riwayat & Manajemen Transaksi
            </h2>
            <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">
                Pantau seluruh catatan transaksi gateway Midtrans, rincian nominal pembayaran, status langganan, dan cetak invoice.
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <span class="badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light text-xs font-bold px-3 py-1">
                Total {{ $transactions->total() }} Transaksi
            </span>
        </div>
    </div>

    {{-- 4 Stat Metric Cards (Lineone Style) --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 sm:gap-5 mb-6">
        {{-- Total Revenue --}}
        <div class="card p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase font-bold tracking-wider text-slate-400 dark:text-navy-300">Total Pendapatan</p>
                    <p class="mt-1.5 text-2xl font-extrabold text-slate-700 dark:text-navy-100">
                        <span class="text-xs font-semibold text-slate-400 dark:text-navy-300">Rp</span>
                        {{ number_format($totalRevenue, 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-[11px] text-slate-400 dark:text-navy-300">Akumulasi seluruh transaksi</p>
                </div>
                <div class="mask is-squircle flex size-12 items-center justify-center bg-success/10 text-success">
                    <x-lucide-wallet class="size-6" />
                </div>
            </div>
        </div>

        {{-- Transaksi Sukses --}}
        <div class="card p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase font-bold tracking-wider text-slate-400 dark:text-navy-300">Transaksi Sukses</p>
                    <p class="mt-1.5 text-2xl font-extrabold text-slate-700 dark:text-navy-100">
                        {{ number_format($successCount, 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-[11px] text-slate-400 dark:text-navy-300">Pembayaran terverifikasi</p>
                </div>
                <div class="mask is-squircle flex size-12 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                    <x-lucide-check-circle-2 class="size-6" />
                </div>
            </div>
        </div>

        {{-- Pending --}}
        <div class="card p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase font-bold tracking-wider text-slate-400 dark:text-navy-300">Menunggu (Pending)</p>
                    <p class="mt-1.5 text-2xl font-extrabold text-slate-700 dark:text-navy-100">
                        {{ number_format($pendingCount, 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-[11px] text-slate-400 dark:text-navy-300">Belum diselesaikan user</p>
                </div>
                <div class="mask is-squircle flex size-12 items-center justify-center bg-warning/10 text-warning">
                    <x-lucide-clock class="size-6" />
                </div>
            </div>
        </div>

        {{-- Failed / Expired --}}
        <div class="card p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase font-bold tracking-wider text-slate-400 dark:text-navy-300">Gagal / Expired</p>
                    <p class="mt-1.5 text-2xl font-extrabold text-slate-700 dark:text-navy-100">
                        {{ number_format($failedCount, 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-[11px] text-slate-400 dark:text-navy-300">Kadaluarsa atau dibatalkan</p>
                </div>
                <div class="mask is-squircle flex size-12 items-center justify-center bg-error/10 text-error">
                    <x-lucide-x-circle class="size-6" />
                </div>
            </div>
        </div>
    </div>

    {{-- Lineone Table Advanced Container --}}
    <div class="card">
        {{-- Filters Bar --}}
        <div class="flex flex-col justify-between gap-3 p-4 sm:flex-row sm:items-center sm:px-5 border-b border-slate-150 dark:border-navy-600">
            {{-- Search Bar --}}
            <div class="relative flex w-full sm:w-80">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari Order ID, transaksi, nama, email..."
                    class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-1.5 pl-9 text-xs placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                <span class="pointer-events-none absolute flex h-full w-9 items-center justify-center text-slate-400 dark:text-navy-300">
                    <x-lucide-search class="size-4" />
                </span>
            </div>
            
            {{-- Dropdown Filters --}}
            <div class="flex items-center space-x-2.5 flex-wrap gap-y-2">
                {{-- Status Filter --}}
                <select wire:model.live="statusFilter" class="form-select rounded-lg border border-slate-300 bg-transparent px-3 py-1.5 text-xs hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent">
                    <option value="all">Semua Status</option>
                    <option value="active">Sukses (Active / Settlement)</option>
                    <option value="pending">Menunggu (Pending)</option>
                    <option value="expired">Expired</option>
                    <option value="cancelled">Dibatalkan</option>
                    <option value="failed">Gagal</option>
                </select>

                {{-- Plan Filter --}}
                <select wire:model.live="planFilter" class="form-select rounded-lg border border-slate-300 bg-transparent px-3 py-1.5 text-xs hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent">
                    <option value="all">Semua Paket</option>
                    @foreach($plans as $plan)
                        @if($plan->slug !== 'free')
                            <option value="{{ $plan->slug }}">Paket {{ $plan->name }}</option>
                        @endif
                    @endforeach
                </select>
                
                {{-- Date Filter --}}
                <select wire:model.live="dateFilter" class="form-select rounded-lg border border-slate-300 bg-transparent px-3 py-1.5 text-xs hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent">
                    <option value="all">Semua Waktu</option>
                    <option value="today">Hari Ini</option>
                    <option value="this_month">Bulan Ini</option>
                    <option value="this_year">Tahun Ini</option>
                </select>
            </div>
        </div>

        {{-- Table --}}
        <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
            <table class="is-hoverable w-full text-left">
                <thead>
                    <tr>
                        <th class="rounded-tl-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs">
                            PENGGUNA
                        </th>
                        <th class="bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs">
                            ORDER ID & PAKET
                        </th>
                        <th class="bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs text-right">
                            NOMINAL & METODE
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
                    @forelse($transactions as $transaction)
                        @php
                            $planName = $transaction->plan->name ?? ucfirst($transaction->plan_slug);
                            $isProMax = ($transaction->plan_slug === 'pro-max' || strtolower((string)$planName) === 'pro max');
                        @endphp
                        <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500">
                            {{-- User Info --}}
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                <div class="flex items-center space-x-3">
                                    <div class="avatar size-8 shrink-0">
                                        @if($transaction->user && $transaction->user->profile_photo_path)
                                            <img class="rounded-full object-cover" src="{{ Storage::url($transaction->user->profile_photo_path) }}" alt="{{ $transaction->user->name }}" />
                                        @else
                                            <div class="is-initial rounded-full bg-primary/10 text-xs font-bold uppercase text-primary dark:bg-accent-light/10 dark:text-accent-light">
                                                {{ substr($transaction->user->name ?? 'U', 0, 2) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-700 dark:text-navy-100 text-xs sm:text-sm">
                                            {{ $transaction->user->name ?? 'Pengguna Terhapus' }}
                                        </div>
                                        <div class="text-[11px] text-slate-400 dark:text-navy-300 mt-0.5">
                                            {{ $transaction->user->email ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Order ID & Plan --}}
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5 text-xs">
                                <a href="{{ route('dashboard.invoice', ['order_id' => $transaction->midtrans_order_id]) }}"
                                   class="font-mono font-bold text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent text-sm flex items-center gap-1">
                                    <span>#{{ $transaction->midtrans_order_id ?? '-' }}</span>
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

                                    @if($transaction->midtrans_transaction_id)
                                        <span class="text-[10px] text-slate-400 dark:text-navy-300 font-mono" title="Midtrans Transaction ID">
                                            {{ Str::limit($transaction->midtrans_transaction_id, 12) }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Nominal & Method --}}
                            <td class="whitespace-nowrap px-4 py-3 text-right sm:px-5">
                                <div class="font-bold text-slate-700 dark:text-navy-100 text-xs sm:text-sm">
                                    Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                </div>
                                <div class="mt-0.5">
                                    <span class="badge rounded-full bg-slate-100 text-slate-600 dark:bg-navy-600 dark:text-navy-200 text-[10px] uppercase font-semibold px-2 py-0.5">
                                        {{ $transaction->payment_type ? str_replace('_', ' ', $transaction->payment_type) : 'Midtrans' }}
                                    </span>
                                </div>
                            </td>

                            {{-- Status --}}
                            <td class="whitespace-nowrap px-4 py-3 text-center sm:px-5">
                                @if($transaction->status === 'active' || $transaction->status === 'settlement' || $transaction->status === 'capture')
                                    <span class="badge space-x-1.5 rounded-full bg-success/10 text-success text-[11px] font-bold px-2.5 py-1 inline-flex items-center">
                                        <span class="size-1.5 rounded-full bg-current"></span>
                                        <span>Berhasil</span>
                                    </span>
                                @elseif($transaction->status === 'pending')
                                    <span class="badge space-x-1.5 rounded-full bg-warning/10 text-warning text-[11px] font-bold px-2.5 py-1 inline-flex items-center">
                                        <span class="size-1.5 rounded-full bg-current animate-ping"></span>
                                        <span>Menunggu</span>
                                    </span>
                                @elseif($transaction->status === 'expired')
                                    <span class="badge space-x-1.5 rounded-full bg-slate-150 text-slate-600 dark:bg-navy-500 dark:text-navy-200 text-[11px] font-bold px-2.5 py-1 inline-flex items-center">
                                        <span class="size-1.5 rounded-full bg-current"></span>
                                        <span>Expired</span>
                                    </span>
                                @elseif($transaction->status === 'cancelled')
                                    <span class="badge space-x-1.5 rounded-full bg-info/10 text-info dark:bg-info/15 text-[11px] font-bold px-2.5 py-1 inline-flex items-center">
                                        <span class="size-1.5 rounded-full bg-current"></span>
                                        <span>Dibatalkan</span>
                                    </span>
                                @elseif($transaction->status === 'failed')
                                    <span class="badge space-x-1.5 rounded-full bg-error/10 text-error text-[11px] font-bold px-2.5 py-1 inline-flex items-center">
                                        <span class="size-1.5 rounded-full bg-current"></span>
                                        <span>Gagal</span>
                                    </span>
                                @else
                                    <span class="badge rounded-full bg-slate-150 text-slate-600 dark:bg-navy-500 dark:text-navy-200 text-[11px] font-bold px-2.5 py-1">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                @endif
                            </td>

                            {{-- Date --}}
                            <td class="whitespace-nowrap px-4 py-3 text-right text-xs text-slate-500 dark:text-navy-300 sm:px-5">
                                <div>{{ $transaction->created_at->translatedFormat('d M Y') }}</div>
                                <div class="text-[10px] text-slate-400 dark:text-navy-400 mt-0.5">{{ $transaction->created_at->format('H:i') }} WIB</div>
                            </td>

                            {{-- Actions --}}
                            <td class="whitespace-nowrap px-4 py-3 text-right sm:px-5">
                                <div class="flex items-center justify-end space-x-1.5">
                                    <button wire:click="openDetailModal({{ $transaction->id }})" title="Rincian Transaksi" class="btn h-7 rounded-md border border-slate-300 px-2 text-[11px] font-semibold text-slate-700 hover:bg-slate-100 dark:border-navy-500 dark:text-navy-100 flex items-center space-x-1">
                                        <x-lucide-info class="size-3.5" />
                                        <span>Detail</span>
                                    </button>

                                    @if($transaction->midtrans_order_id)
                                        <a href="{{ route('dashboard.invoice', ['order_id' => $transaction->midtrans_order_id]) }}"
                                           class="btn h-7 space-x-1 rounded-md bg-primary/10 text-primary hover:bg-primary hover:text-white dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent dark:hover:text-white px-2.5 text-xs font-semibold transition-colors flex items-center">
                                            <x-lucide-receipt class="size-3.5" />
                                            <span>Invoice</span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="whitespace-nowrap px-4 py-8 text-center text-slate-400 dark:text-navy-300 text-xs">
                                <x-lucide-receipt-text class="size-10 mx-auto text-slate-300 dark:text-navy-400 mb-2" />
                                <p>Tidak ada transaksi yang cocok dengan kriteria pencarian.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($transactions->hasPages())
            <div class="flex flex-col justify-between gap-4 p-4 sm:flex-row sm:items-center sm:px-5 border-t border-slate-150 dark:border-navy-600">
                <div class="text-xs text-slate-400 dark:text-navy-300">
                    Menampilkan <strong>{{ $transactions->firstItem() }}</strong> sampai <strong>{{ $transactions->lastItem() }}</strong> dari <strong>{{ $transactions->total() }}</strong> transaksi
                </div>
                <div>
                    {{ $transactions->links('components.lineone-pagination') }}
                </div>
            </div>
        @endif
    </div>

    {{-- Lineone Transaction Detail Modal --}}
    @if($showDetailModal && $selectedTransaction)
        @php
            $modalPlanName = $selectedTransaction->plan->name ?? ucfirst($selectedTransaction->plan_slug);
            $modalIsProMax = ($selectedTransaction->plan_slug === 'pro-max' || strtolower((string)$modalPlanName) === 'pro max');
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-300"
             x-data
             x-on:keydown.escape.window="$wire.closeDetailModal()">
            <div class="relative w-full max-w-lg rounded-2xl bg-white dark:bg-navy-700 shadow-2xl border border-slate-200 dark:border-navy-600 overflow-hidden flex flex-col max-h-[90vh]">
                
                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-150 dark:border-navy-600 bg-slate-50 dark:bg-navy-800">
                    <div class="flex items-center space-x-3">
                        <div class="mask is-squircle flex size-10 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light font-bold">
                            <x-lucide-receipt class="size-5" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800 dark:text-navy-100 flex items-center gap-2">
                                <span>Rincian Transaksi</span>
                            </h3>
                            <p class="text-xs text-slate-400 dark:text-navy-300 font-mono">
                                #{{ $selectedTransaction->midtrans_order_id ?? $selectedTransaction->id }}
                            </p>
                        </div>
                    </div>
                    <button wire:click="closeDetailModal" class="btn size-8 rounded-full p-0 text-slate-400 hover:bg-slate-200 hover:text-slate-700 dark:hover:bg-navy-600 dark:hover:text-navy-100">
                        <x-lucide-x class="size-4.5" />
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-5 space-y-4 overflow-y-auto is-scrollbar-hidden flex-1 text-xs">
                    
                    {{-- User & Status Summary --}}
                    <div class="rounded-xl border border-slate-150 dark:border-navy-600 p-3.5 bg-slate-50/50 dark:bg-navy-800/40 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 dark:text-navy-300">Status Pembayaran</span>
                            @if($selectedTransaction->status === 'active' || $selectedTransaction->status === 'settlement' || $selectedTransaction->status === 'capture')
                                <span class="badge space-x-1.5 rounded-full bg-success/15 text-success font-bold text-[11px] px-2.5 py-0.5 inline-flex items-center">
                                    <span class="size-1.5 rounded-full bg-current"></span>
                                    <span>Berhasil</span>
                                </span>
                            @elseif($selectedTransaction->status === 'pending')
                                <span class="badge space-x-1.5 rounded-full bg-warning/15 text-warning font-bold text-[11px] px-2.5 py-0.5 inline-flex items-center">
                                    <span class="size-1.5 rounded-full bg-current animate-ping"></span>
                                    <span>Menunggu</span>
                                </span>
                            @elseif($selectedTransaction->status === 'expired')
                                <span class="badge space-x-1.5 rounded-full bg-slate-150 text-slate-600 dark:bg-navy-500 dark:text-navy-200 text-[11px] font-bold px-2.5 py-0.5 inline-flex items-center">
                                    <span class="size-1.5 rounded-full bg-current"></span>
                                    <span>Expired</span>
                                </span>
                            @elseif($selectedTransaction->status === 'cancelled')
                                <span class="badge space-x-1.5 rounded-full bg-info/15 text-info dark:bg-info/20 text-[11px] font-bold px-2.5 py-0.5 inline-flex items-center">
                                    <span class="size-1.5 rounded-full bg-current"></span>
                                    <span>Dibatalkan</span>
                                </span>
                            @elseif($selectedTransaction->status === 'failed')
                                <span class="badge space-x-1.5 rounded-full bg-error/15 text-error dark:bg-error/20 text-[11px] font-bold px-2.5 py-0.5 inline-flex items-center">
                                    <span class="size-1.5 rounded-full bg-current"></span>
                                    <span>Gagal</span>
                                </span>
                            @else
                                <span class="badge rounded-full bg-slate-200 dark:bg-navy-500 text-slate-700 dark:text-navy-100 font-bold text-[11px] px-2.5 py-0.5">
                                    {{ ucfirst($selectedTransaction->status) }}
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 dark:text-navy-300">Pembeli / Pengguna</span>
                            <span class="font-bold text-slate-700 dark:text-navy-100">
                                {{ $selectedTransaction->user->name ?? 'User Terhapus' }} ({{ $selectedTransaction->user->email ?? '-' }})
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 dark:text-navy-300">Waktu Order</span>
                            <span class="text-slate-600 dark:text-navy-200">
                                {{ $selectedTransaction->created_at->translatedFormat('d F Y, H:i') }} WIB
                            </span>
                        </div>
                    </div>

                    {{-- Item & Price Breakdown --}}
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-navy-300 mb-2">
                            Rincian Pembelian Paket
                        </h4>
                        <div class="rounded-xl border border-slate-150 dark:border-navy-600 overflow-hidden divide-y divide-slate-150 dark:divide-navy-600">
                            <div class="p-3 bg-slate-50 dark:bg-navy-800 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    @if($modalIsProMax)
                                        <x-lucide-crown class="size-4 text-warning" />
                                    @else
                                        <x-lucide-star class="size-4 text-amber-500 fill-current" />
                                    @endif
                                    <span class="font-bold text-slate-700 dark:text-navy-100">
                                        Paket {{ $modalPlanName }}
                                    </span>
                                </div>
                                <span class="font-bold text-slate-700 dark:text-navy-100">
                                    Rp {{ number_format($selectedTransaction->subtotal ?? $selectedTransaction->amount, 0, ',', '.') }}
                                </span>
                            </div>

                            @if(($selectedTransaction->discount ?? 0) > 0)
                                <div class="p-2.5 px-3 flex items-center justify-between text-success">
                                    <span>Potongan Diskon Promo</span>
                                    <span>- Rp {{ number_format($selectedTransaction->discount, 0, ',', '.') }}</span>
                                </div>
                            @endif

                            @if(($selectedTransaction->service_fee ?? 0) > 0)
                                <div class="p-2.5 px-3 flex items-center justify-between text-slate-500 dark:text-navy-300">
                                    <span>Biaya Layanan Gateway</span>
                                    <span>Rp {{ number_format($selectedTransaction->service_fee, 0, ',', '.') }}</span>
                                </div>
                            @endif

                            @if(($selectedTransaction->tax ?? 0) > 0)
                                <div class="p-2.5 px-3 flex items-center justify-between text-slate-500 dark:text-navy-300">
                                    <span>Pajak (PPN)</span>
                                    <span>Rp {{ number_format($selectedTransaction->tax, 0, ',', '.') }}</span>
                                </div>
                            @endif

                            <div class="p-3 bg-primary/5 dark:bg-accent/5 flex items-center justify-between font-bold text-sm text-primary dark:text-accent-light">
                                <span>Total Pembayaran</span>
                                <span>Rp {{ number_format($selectedTransaction->amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Technical & Gateway Details --}}
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-navy-300 mb-2">
                            Informasi Gateway Midtrans
                        </h4>
                        <div class="rounded-xl border border-slate-150 dark:border-navy-600 p-3 space-y-1.5 font-mono text-[11px] bg-slate-50/50 dark:bg-navy-800/30">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Metode Bayar:</span>
                                <span class="text-slate-700 dark:text-navy-100 uppercase">{{ $selectedTransaction->payment_type ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Midtrans Tx ID:</span>
                                <span class="text-slate-700 dark:text-navy-100">{{ $selectedTransaction->midtrans_transaction_id ?? '-' }}</span>
                            </div>
                            @if($selectedTransaction->starts_at)
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Masa Aktif:</span>
                                    <span class="text-slate-700 dark:text-navy-100">{{ $selectedTransaction->starts_at->translatedFormat('d M Y') }} s/d {{ $selectedTransaction->expires_at ? $selectedTransaction->expires_at->translatedFormat('d M Y') : 'Selamanya' }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-between px-5 py-3.5 border-t border-slate-150 dark:border-navy-600 bg-slate-50 dark:bg-navy-800">
                    <div>
                        @if($selectedTransaction->status !== 'active')
                            <button wire:click="manualActivate({{ $selectedTransaction->id }})" type="button" class="btn h-8 rounded-lg bg-success text-white text-xs font-semibold px-3 shadow-xs hover:bg-success-focus flex items-center gap-1">
                                <x-lucide-check-circle-2 class="size-3.5" />
                                <span>Aktivasi Manual</span>
                            </button>
                        @endif
                    </div>
                    <div class="flex items-center space-x-2">
                        @if($selectedTransaction->midtrans_order_id)
                            <a href="{{ route('dashboard.invoice', $selectedTransaction->midtrans_order_id) }}" target="_blank" class="btn h-8 rounded-lg border border-slate-300 dark:border-navy-450 px-3 text-xs font-semibold text-slate-700 dark:text-navy-100 hover:bg-slate-150 dark:hover:bg-navy-600 flex items-center gap-1">
                                <x-lucide-printer class="size-3.5" />
                                <span>Cetak Invoice</span>
                            </a>
                        @endif
                        <button wire:click="closeDetailModal" type="button" class="btn h-8 rounded-lg bg-primary px-4 text-xs font-semibold text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus">
                            Tutup
                        </button>
                    </div>
                </div>

            </div>
        </div>
    @endif
</div>
