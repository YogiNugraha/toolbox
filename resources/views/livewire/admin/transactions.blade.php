<div>
    <div class="flex items-center justify-between mt-5 mb-5">
        @section('page_title', 'Semua Transaksi')
        <div></div>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 sm:gap-5 mb-6">
        <div class="card p-4">
            <h3 class="text-xs-plus uppercase tracking-wide text-slate-400 dark:text-navy-300">Total Revenue</h3>
            <p class="mt-2 text-xl font-medium text-slate-700 dark:text-navy-100">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
        </div>
        <div class="card p-4 border-l-4 border-success">
            <h3 class="text-xs-plus uppercase tracking-wide text-slate-400 dark:text-navy-300">Transaksi Sukses</h3>
            <p class="mt-2 text-xl font-medium text-slate-700 dark:text-navy-100">{{ number_format($successCount, 0, ',', '.') }}</p>
        </div>
        <div class="card p-4 border-l-4 border-warning">
            <h3 class="text-xs-plus uppercase tracking-wide text-slate-400 dark:text-navy-300">Menunggu (Pending)</h3>
            <p class="mt-2 text-xl font-medium text-slate-700 dark:text-navy-100">{{ number_format($pendingCount, 0, ',', '.') }}</p>
        </div>
        <div class="card p-4 border-l-4 border-error">
            <h3 class="text-xs-plus uppercase tracking-wide text-slate-400 dark:text-navy-300">Gagal (Expired)</h3>
            <p class="mt-2 text-xl font-medium text-slate-700 dark:text-navy-100">{{ number_format($failedCount, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="card">
        {{-- Filters --}}
        <div class="flex flex-col justify-between gap-4 px-4 py-4 sm:flex-row sm:items-center sm:px-5 border-b border-slate-200 dark:border-navy-500">
            <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                <select wire:model.live="statusFilter" class="form-select w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent sm:w-48">
                    <option value="all">Semua Status</option>
                    <option value="active">Sukses (Active)</option>
                    <option value="pending">Menunggu (Pending)</option>
                    <option value="expired">Gagal (Expired)</option>
                </select>
                
                <select wire:model.live="dateFilter" class="form-select w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent sm:w-48">
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
                    <tr class="border-y border-transparent border-b-slate-200 bg-slate-50 dark:border-b-navy-500 dark:bg-navy-800">
                        <th class="whitespace-nowrap px-4 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">User</th>
                        <th class="whitespace-nowrap px-4 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Order ID</th>
                        <th class="whitespace-nowrap px-4 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Nominal</th>
                        <th class="whitespace-nowrap px-4 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Metode / Channel</th>
                        <th class="whitespace-nowrap px-4 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Status</th>
                        <th class="whitespace-nowrap px-4 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500">
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                <div class="font-medium text-slate-700 dark:text-navy-100">{{ $transaction->user->name ?? 'User Terhapus' }}</div>
                                <div class="text-xs text-slate-400 dark:text-navy-300 mt-0.5">{{ $transaction->user->email ?? '-' }}</div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                <div class="font-medium text-primary transition-colors hover:text-primary-focus dark:text-accent-light dark:hover:text-accent" title="Order ID">{{ $transaction->midtrans_order_id ?? '-' }}</div>
                                @if($transaction->midtrans_transaction_id)
                                    <div class="text-tiny-plus text-slate-400 dark:text-navy-300 mt-0.5" title="Midtrans Transaction ID">{{ $transaction->midtrans_transaction_id }}</div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 font-medium text-slate-700 dark:text-navy-100 sm:px-5">
                                Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs uppercase text-slate-400 dark:text-navy-300 sm:px-5">
                                {{ $transaction->payment_type ?? '-' }}
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
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600 dark:text-navy-100 sm:px-5">
                                {{ $transaction->created_at->format('d M Y, H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="whitespace-nowrap px-4 py-8 text-center text-slate-400 dark:text-navy-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-12 mx-auto text-slate-300 dark:text-navy-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p>Tidak ada transaksi yang ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="border-t border-slate-200 px-4 py-4 dark:border-navy-500 sm:px-5">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
