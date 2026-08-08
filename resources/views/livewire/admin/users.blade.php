<div>
    @section('page_title', 'Manajemen Pengguna')

    <div class="bg-white border border-hairline rounded-sm">
        <!-- Filters -->
        <div class="p-6 border-b border-hairline flex flex-col md:flex-row gap-4 justify-between items-center bg-paper/30">
            <div class="relative w-full md:w-1/3">
                <x-lucide-search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-ink-muted" />
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama atau email..." 
                    class="w-full pl-9 pr-4 py-2 bg-white border border-hairline rounded-sm focus:outline-none focus:border-amber focus:ring-1 focus:ring-amber text-sm transition-colors">
            </div>
            
            <div class="flex gap-3 w-full md:w-auto">
                <select wire:model.live="planFilter" class="w-full md:w-auto px-4 py-2 bg-white border border-hairline rounded-sm focus:outline-none focus:border-amber focus:ring-1 focus:ring-amber text-sm transition-colors cursor-pointer appearance-none">
                    <option value="all">Semua Plan</option>
                    <option value="free">Free</option>
                    <option value="pro">Pro</option>
                </select>
                
                <select wire:model.live="statusFilter" class="w-full md:w-auto px-4 py-2 bg-white border border-hairline rounded-sm focus:outline-none focus:border-amber focus:ring-1 focus:ring-amber text-sm transition-colors cursor-pointer appearance-none">
                    <option value="all">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="banned">Banned</option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-paper/50 text-ink-muted border-b border-hairline uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Pengguna</th>
                        <th class="px-6 py-4 font-semibold">Status Plan</th>
                        <th class="px-6 py-4 font-semibold">Total Belanja</th>
                        <th class="px-6 py-4 font-semibold">Status Akun</th>
                        <th class="px-6 py-4 font-semibold">Tanggal Daftar</th>
                        <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline text-ink">
                    @forelse($users as $user)
                        <tr class="hover:bg-paper/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-amber/15 border border-amber/40 text-amber font-mono text-sm flex items-center justify-center overflow-hidden shrink-0">
                                        @if($user->profile_photo_path)
                                            <img src="{{ Storage::url($user->profile_photo_path) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                        @else
                                            <span>{{ substr($user->name, 0, 1) }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-medium text-ink">{{ $user->name }} @if($user->is_admin) <span class="ml-1 text-[10px] bg-ink text-white px-1.5 py-0.5 rounded-sm uppercase tracking-wider">Admin</span> @endif</div>
                                        <div class="text-xs text-ink-muted mt-0.5">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $activeSub = $user->subscriptions->first();
                                @endphp
                                @if($activeSub)
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 text-xs font-bold bg-amber/20 text-amber border border-amber/30 rounded-sm">PRO</span>
                                        <span class="text-xs text-ink-muted">s/d {{ \Carbon\Carbon::parse($activeSub->expires_at)->format('d M Y') }}</span>
                                    </div>
                                @else
                                    <span class="px-2 py-0.5 text-xs font-medium bg-slate-100 text-slate-600 rounded-sm">Free</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-mono">
                                Rp {{ number_format($user->subscriptions_sum_amount ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4">
                                @if($user->banned_at)
                                    <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-700 rounded-sm">Banned</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-sm">Aktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-ink-muted">
                                {{ $user->created_at->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if(!$user->is_admin)
                                    @if($user->banned_at)
                                        <button wire:click="unbanUser({{ $user->id }})" class="text-sm text-ink-muted hover:text-green-600 font-medium transition-colors">Unban</button>
                                    @else
                                        <button wire:click="confirmBan({{ $user->id }})" class="text-sm text-ink-muted hover:text-red-600 font-medium transition-colors">Ban</button>
                                    @endif
                                @else
                                    <span class="text-xs text-slate-300 italic">No action</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-ink-muted">
                                <x-lucide-users class="w-12 h-12 mx-auto text-slate-300 mb-3" />
                                <p>Tidak ada pengguna yang ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-hairline">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
