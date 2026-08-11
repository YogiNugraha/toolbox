<div>
    @section('page_title', 'Admin Overview')

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Users -->
        <div
            class="bg-white rounded-sm border border-hairline p-6 relative overflow-hidden group hover:border-amber/30 transition-colors">
            <div
                class="absolute top-0 right-0 w-24 h-24 bg-linear-to-br from-amber/5 to-transparent rounded-bl-full -mr-4 -mt-4 opacity-50 group-hover:scale-110 transition-transform duration-500">
            </div>
            <div class="flex items-center gap-4 mb-4">
                <div
                    class="w-10 h-10 rounded-sm bg-paper border border-hairline flex items-center justify-center shrink-0">
                    <x-lucide-users class="w-5 h-5 text-ink-muted group-hover:text-amber transition-colors" />
                </div>
                <h3 class="text-sm font-medium text-ink-muted">Total Pengguna</h3>
            </div>
            <div class="flex items-baseline gap-2">
                <span
                    class="font-mono text-3xl font-bold text-ink tracking-tight">{{ number_format($totalUsers, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Active Pro Users -->
        <div
            class="bg-white rounded-sm border border-hairline p-6 relative overflow-hidden group hover:border-amber/30 transition-colors">
            <div
                class="absolute top-0 right-0 w-24 h-24 bg-linear-to-br from-amber/5 to-transparent rounded-bl-full -mr-4 -mt-4 opacity-50 group-hover:scale-110 transition-transform duration-500">
            </div>
            <div class="flex items-center gap-4 mb-4">
                <div
                    class="w-10 h-10 rounded-sm bg-paper border border-hairline flex items-center justify-center shrink-0">
                    <x-lucide-award class="w-5 h-5 text-ink-muted group-hover:text-amber transition-colors" />
                </div>
                <h3 class="text-sm font-medium text-ink-muted">Pengguna Pro</h3>
            </div>
            <div class="flex items-baseline gap-2">
                <span
                    class="font-mono text-3xl font-bold text-ink tracking-tight">{{ number_format($activeProUsers, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Total Revenue -->
        <div
            class="bg-white rounded-sm border border-hairline p-6 relative overflow-hidden group hover:border-amber/30 transition-colors">
            <div
                class="absolute top-0 right-0 w-24 h-24 bg-linear-to-br from-amber/5 to-transparent rounded-bl-full -mr-4 -mt-4 opacity-50 group-hover:scale-110 transition-transform duration-500">
            </div>
            <div class="flex items-center gap-4 mb-4">
                <div
                    class="w-10 h-10 rounded-sm bg-paper border border-hairline flex items-center justify-center shrink-0">
                    <x-lucide-wallet class="w-5 h-5 text-ink-muted group-hover:text-amber transition-colors" />
                </div>
                <h3 class="text-sm font-medium text-ink-muted">Total Revenue</h3>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-sm text-ink-muted font-medium">Rp</span>
                <span
                    class="font-mono text-2xl font-bold text-ink tracking-tight">{{ number_format($totalRevenue, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Revenue This Month -->
        <div
            class="bg-white rounded-sm border border-hairline p-6 relative overflow-hidden group hover:border-amber/30 transition-colors">
            <div
                class="absolute top-0 right-0 w-24 h-24 bg-linear-to-br from-amber/5 to-transparent rounded-bl-full -mr-4 -mt-4 opacity-50 group-hover:scale-110 transition-transform duration-500">
            </div>
            <div class="flex items-center gap-4 mb-4">
                <div
                    class="w-10 h-10 rounded-sm bg-paper border border-hairline flex items-center justify-center shrink-0">
                    <x-lucide-trending-up class="w-5 h-5 text-ink-muted group-hover:text-amber transition-colors" />
                </div>
                <h3 class="text-sm font-medium text-ink-muted">Revenue Bulan Ini</h3>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-sm text-ink-muted font-medium">Rp</span>
                <span
                    class="font-mono text-2xl font-bold text-ink tracking-tight">{{ number_format($revenueThisMonth, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white border border-hairline rounded-sm">
        <div class="px-6 py-5 border-b border-hairline flex items-center justify-between">
            <h2 class="text-lg font-bold text-ink font-display">10 Transaksi Terakhir</h2>
            <a href="{{ route('admin.transactions') }}" wire:navigate
                class="text-sm text-amber hover:text-amber/80 font-medium transition-colors">Lihat Semua</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-paper/50 text-ink-muted border-b border-hairline uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-6 py-4 font-semibold">User</th>
                        <th class="px-6 py-4 font-semibold">Order ID</th>
                        <th class="px-6 py-4 font-semibold">Nominal</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline text-ink">
                    @forelse($recentTransactions as $transaction)
                        <tr class="hover:bg-paper/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-ink">{{ $transaction->user->name }}</div>
                                <div class="text-xs text-ink-muted mt-0.5">{{ $transaction->user->email }}</div>
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-ink-muted">
                                {{ $transaction->order_id }}
                            </td>
                            <td class="px-6 py-4 font-mono">
                                Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4">
                                @if($transaction->status === 'active')
                                    <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-sm">Berhasil</span>
                                @elseif($transaction->status === 'pending')
                                    <span class="px-2 py-1 text-xs font-semibold bg-amber/20 text-amber rounded-sm">Menunggu</span>
                                @elseif($transaction->status === 'expired')
                                    <span class="px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-700 rounded-sm">Expired</span>
                                @elseif($transaction->status === 'cancelled')
                                    <span class="px-2 py-1 text-xs font-semibold bg-slate-100 text-slate-700 rounded-sm">Dibatalkan</span>
                                @elseif($transaction->status === 'failed')
                                    <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-700 rounded-sm">Gagal</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-700 rounded-sm">{{ ucfirst($transaction->status) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-ink-muted">
                                {{ $transaction->created_at->format('d M Y, H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-ink-muted">
                                Belum ada transaksi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
