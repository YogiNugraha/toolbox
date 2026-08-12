<div>
    @section('page_title', 'Admin Overview')

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 sm:gap-5">
        {{-- Total Users --}}
        <div class="card px-4 py-4 sm:px-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs-plus uppercase tracking-wide text-slate-400 dark:text-navy-300">Total Pengguna</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-700 dark:text-navy-100">{{ number_format($totalUsers, 0, ',', '.') }}</p>
                </div>
                <div class="mask is-squircle flex size-11 items-center justify-center bg-primary/10 dark:bg-accent-light/10">
                    <svg class="size-6 text-primary dark:text-accent-light" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Active Pro Users --}}
        <div class="card px-4 py-4 sm:px-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs-plus uppercase tracking-wide text-slate-400 dark:text-navy-300">Pengguna Pro</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-700 dark:text-navy-100">{{ number_format($activeProUsers, 0, ',', '.') }}</p>
                </div>
                <div class="mask is-squircle flex size-11 items-center justify-center bg-warning/10">
                    <svg class="size-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Total Revenue --}}
        <div class="card px-4 py-4 sm:px-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs-plus uppercase tracking-wide text-slate-400 dark:text-navy-300">Total Revenue</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-700 dark:text-navy-100">
                        <span class="text-sm font-medium text-slate-400 dark:text-navy-300">Rp</span>
                        {{ number_format($totalRevenue, 0, ',', '.') }}
                    </p>
                </div>
                <div class="mask is-squircle flex size-11 items-center justify-center bg-success/10">
                    <svg class="size-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Revenue This Month --}}
        <div class="card px-4 py-4 sm:px-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs-plus uppercase tracking-wide text-slate-400 dark:text-navy-300">Revenue Bulan Ini</p>
                    <p class="mt-1 text-2xl font-semibold text-slate-700 dark:text-navy-100">
                        <span class="text-sm font-medium text-slate-400 dark:text-navy-300">Rp</span>
                        {{ number_format($revenueThisMonth, 0, ',', '.') }}
                    </p>
                </div>
                <div class="mask is-squircle flex size-11 items-center justify-center bg-info/10">
                    <svg class="size-6 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Transactions --}}
    <div class="card mt-5">
        <div class="flex items-center justify-between px-4 py-4 sm:px-5">
            <h2 class="font-medium tracking-wide text-slate-700 dark:text-navy-100 lg:text-base">
                10 Transaksi Terakhir
            </h2>
            <a href="{{ route('admin.transactions') }}" wire:navigate
               class="border-b border-dotted border-current pb-0.5 text-xs-plus font-medium text-primary outline-hidden transition-colors duration-300 hover:text-primary/70 focus:text-primary/70 dark:text-accent-light dark:hover:text-accent-light/70">
                Lihat Semua
            </a>
        </div>

        <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
            <table class="is-hoverable w-full text-left">
                <thead>
                    <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500">
                        <th class="whitespace-nowrap px-3 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">User</th>
                        <th class="whitespace-nowrap px-3 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Order ID</th>
                        <th class="whitespace-nowrap px-3 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Nominal</th>
                        <th class="whitespace-nowrap px-3 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Status</th>
                        <th class="whitespace-nowrap px-3 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTransactions as $transaction)
                        <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500">
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                <div class="flex items-center space-x-3">
                                    <div class="avatar size-8">
                                        <div class="is-initial rounded-full bg-primary/10 text-xs-plus uppercase text-primary dark:bg-accent-light/10 dark:text-accent-light">
                                            {{ substr($transaction->user->name, 0, 2) }}
                                        </div>
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-700 dark:text-navy-100">{{ $transaction->user->name }}</p>
                                        <p class="text-xs text-slate-400 dark:text-navy-300">{{ $transaction->user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs-plus sm:px-5">
                                {{ $transaction->order_id }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 font-medium text-slate-700 dark:text-navy-100 sm:px-5">
                                Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                @if($transaction->status === 'active')
                                    <span class="badge bg-success/10 text-success dark:bg-success/15">Berhasil</span>
                                @elseif($transaction->status === 'pending')
                                    <span class="badge bg-warning/10 text-warning dark:bg-warning/15">Menunggu</span>
                                @elseif($transaction->status === 'expired')
                                    <span class="badge bg-slate-100 text-slate-600 dark:bg-navy-500 dark:text-navy-100">Expired</span>
                                @elseif($transaction->status === 'cancelled')
                                    <span class="badge bg-slate-100 text-slate-600 dark:bg-navy-500 dark:text-navy-100">Dibatalkan</span>
                                @elseif($transaction->status === 'failed')
                                    <span class="badge bg-error/10 text-error dark:bg-error/15">Gagal</span>
                                @else
                                    <span class="badge bg-slate-100 text-slate-600 dark:bg-navy-500 dark:text-navy-100">{{ ucfirst($transaction->status) }}</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                {{ $transaction->created_at->format('d M Y, H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400 dark:text-navy-300 sm:px-5">
                                Belum ada transaksi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
