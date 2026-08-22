<div>
    @section('title', 'Admin Dashboard - ' . config('app.name'))
    @section('page_title', 'Admin Overview')
    @section('page_breadcrumb', 'Admin Dashboard')

    {{-- Top Header / Welcome --}}
    <div class="flex items-center justify-between py-4 sm:py-5">
        <div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-navy-50 lg:text-2xl">
                Ringkasan Admin Panel
            </h2>
            <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">
                Pantau performa pengguna, langganan aktif, dan arus pendapatan sistem secara real-time.
            </p>
        </div>
        <div class="hidden sm:flex space-x-2">
            <a href="{{ route('admin.plans') }}" wire:navigate class="btn h-8 rounded-full border border-slate-300 px-3.5 text-xs font-semibold text-slate-700 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-100 dark:hover:bg-navy-500">
                Kelola Paket
            </a>
            <a href="{{ route('admin.transactions') }}" wire:navigate class="btn h-8 rounded-full bg-primary px-3.5 text-xs font-semibold text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus">
                Semua Transaksi
            </a>
        </div>
    </div>

    {{-- 4 Stat Metric Cards (Lineone Style) --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 sm:gap-5">
        {{-- Total Users --}}
        <div class="card p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase font-bold tracking-wider text-slate-400 dark:text-navy-300">Total Pengguna</p>
                    <p class="mt-1.5 text-2xl sm:text-3xl font-extrabold text-slate-700 dark:text-navy-100">
                        {{ number_format($totalUsers, 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-[11px] text-slate-400 dark:text-navy-300">Terdaftar di platform</p>
                </div>
                <div class="mask is-squircle flex size-12 items-center justify-center bg-primary/10 dark:bg-accent-light/10">
                    <svg class="size-6 text-primary dark:text-accent-light" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Active Subscribed Users --}}
        <div class="card p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase font-bold tracking-wider text-slate-400 dark:text-navy-300">Member Berlangganan</p>
                    <p class="mt-1.5 text-2xl sm:text-3xl font-extrabold text-slate-700 dark:text-navy-100">
                        {{ number_format($activeProUsers, 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-[11px] text-slate-400 dark:text-navy-300">Langganan belum kadaluarsa</p>
                </div>
                <div class="mask is-squircle flex size-12 items-center justify-center bg-warning/10">
                    <svg class="size-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Total Revenue --}}
        <div class="card p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase font-bold tracking-wider text-slate-400 dark:text-navy-300">Total Revenue</p>
                    <p class="mt-1.5 text-2xl sm:text-3xl font-extrabold text-slate-700 dark:text-navy-100">
                        <span class="text-xs font-semibold text-slate-400 dark:text-navy-300">Rp</span>
                        {{ number_format($totalRevenue, 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-[11px] text-slate-400 dark:text-navy-300">Akumulasi pendapatan</p>
                </div>
                <div class="mask is-squircle flex size-12 items-center justify-center bg-success/10">
                    <svg class="size-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Revenue This Month --}}
        <div class="card p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase font-bold tracking-wider text-slate-400 dark:text-navy-300">Bulan Ini</p>
                    <p class="mt-1.5 text-2xl sm:text-3xl font-extrabold text-slate-700 dark:text-navy-100">
                        <span class="text-xs font-semibold text-slate-400 dark:text-navy-300">Rp</span>
                        {{ number_format($revenueThisMonth, 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-[11px] text-slate-400 dark:text-navy-300">{{ now()->translatedFormat('F Y') }}</p>
                </div>
                <div class="mask is-squircle flex size-12 items-center justify-center bg-info/10">
                    <svg class="size-6 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Transactions (Exact Lineone Table Advanced Styling) --}}
    <div class="mt-6">
        <div class="flex items-center justify-between pb-3">
            <div class="flex items-center space-x-2">
                <h2 class="text-base font-bold tracking-wide text-slate-700 dark:text-navy-100">
                    10 Transaksi Terakhir
                </h2>
                <span class="badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light text-xs font-bold px-2.5 py-0.5">
                    {{ $recentTransactions->count() }} Data
                </span>
            </div>
            <a href="{{ route('admin.transactions') }}" wire:navigate
               class="border-b border-dotted border-current pb-0.5 text-xs font-semibold text-primary outline-hidden transition-colors duration-300 hover:text-primary/70 dark:text-accent-light">
                Lihat Semua Transaksi &rarr;
            </a>
        </div>

        <div class="card">
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
                        @forelse($recentTransactions as $transaction)
                            @php
                                $planName = $transaction->plan->name ?? ucfirst($transaction->plan_slug);
                                $isProMax = ($transaction->plan_slug === 'pro-max' || strtolower((string)$planName) === 'pro max');
                            @endphp
                            <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500 hover:bg-slate-50/80 dark:hover:bg-navy-700/50 transition-colors">
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
                                    <div class="flex items-center justify-end">
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
                                <td colspan="6" class="px-4 py-8 text-center text-slate-400 dark:text-navy-300 sm:px-5 text-xs">
                                    <x-lucide-receipt-text class="size-10 mx-auto text-slate-300 dark:text-navy-400 mb-2" />
                                    <p>Belum ada catatan transaksi yang masuk.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
