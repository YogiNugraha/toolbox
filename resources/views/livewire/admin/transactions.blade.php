<div>
    @section('page_title', 'Semua Transaksi')

    <!-- Summary -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-sm border border-hairline p-4">
            <h3 class="text-xs font-semibold text-ink-muted uppercase tracking-wider mb-1">Total Revenue</h3>
            <p class="font-mono text-xl font-bold text-ink">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-sm border border-hairline p-4 border-l-4 border-l-green-500">
            <h3 class="text-xs font-semibold text-ink-muted uppercase tracking-wider mb-1">Transaksi Sukses</h3>
            <p class="font-mono text-xl font-bold text-ink">{{ number_format($successCount, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-sm border border-hairline p-4 border-l-4 border-l-amber">
            <h3 class="text-xs font-semibold text-ink-muted uppercase tracking-wider mb-1">Menunggu (Pending)</h3>
            <p class="font-mono text-xl font-bold text-ink">{{ number_format($pendingCount, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-sm border border-hairline p-4 border-l-4 border-l-red-500">
            <h3 class="text-xs font-semibold text-ink-muted uppercase tracking-wider mb-1">Gagal (Asli)</h3>
            <p class="font-mono text-xl font-bold text-ink">{{ number_format($failedCount, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="bg-white border border-hairline rounded-sm">
        <!-- Filters -->
        <div class="p-6 border-b border-hairline flex flex-col md:flex-row gap-4 justify-between items-center bg-paper/30">
            <div class="flex gap-3 w-full md:w-auto">
                <select wire:model.live="statusFilter" class="w-full md:w-auto px-4 py-2 bg-white border border-hairline rounded-sm focus:outline-none focus:border-amber focus:ring-1 focus:ring-amber text-sm transition-colors cursor-pointer appearance-none">
                    <option value="all">Semua Status</option>
                    <option value="active">Sukses (Active)</option>
                    <option value="pending">Menunggu (Pending)</option>
                    <option value="expired">Gagal (Expired)</option>
                </select>
                
                <select wire:model.live="dateFilter" class="w-full md:w-auto px-4 py-2 bg-white border border-hairline rounded-sm focus:outline-none focus:border-amber focus:ring-1 focus:ring-amber text-sm transition-colors cursor-pointer appearance-none">
                    <option value="all">Semua Waktu</option>
                    <option value="today">Hari Ini</option>
                    <option value="this_month">Bulan Ini</option>
                    <option value="this_year">Tahun Ini</option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-paper/50 text-ink-muted border-b border-hairline uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-6 py-4 font-semibold">User</th>
                        <th class="px-6 py-4 font-semibold">Order ID</th>
                        <th class="px-6 py-4 font-semibold">Nominal</th>
                        <th class="px-6 py-4 font-semibold">Metode / Channel</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline text-ink">
                    @forelse($transactions as $transaction)
                        <tr class="hover:bg-paper/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-ink">{{ $transaction->user->name ?? 'User Terhapus' }}</div>
                                <div class="text-xs text-ink-muted mt-0.5">{{ $transaction->user->email ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-ink-muted">
                                {{ $transaction->order_id }}
                                @if($transaction->midtrans_transaction_id)
                                    <div class="text-[10px] mt-0.5" title="Midtrans Transaction ID">{{ $transaction->midtrans_transaction_id }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-mono">
                                Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-ink-muted text-xs uppercase">
                                {{ $transaction->payment_type ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($transaction->status === 'active')
                                    <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-sm">Berhasil</span>
                                @elseif($transaction->status === 'pending')
                                    <span class="px-2 py-1 text-xs font-semibold bg-amber/20 text-amber rounded-sm">Pending</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold bg-slate-100 text-slate-600 rounded-sm">{{ ucfirst($transaction->status) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-ink-muted">
                                {{ $transaction->created_at->format('d M Y, H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-ink-muted">
                                <x-lucide-receipt class="w-12 h-12 mx-auto text-slate-300 mb-3" />
                                <p>Tidak ada transaksi yang ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="px-6 py-4 border-t border-hairline">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
