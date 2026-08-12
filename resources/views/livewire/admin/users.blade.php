<div>
    <div class="flex items-center justify-between mt-5 mb-5">
        <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">Manajemen Pengguna</h2>
    </div>

    <div class="card">
        {{-- Filters --}}
        <div class="flex flex-col justify-between gap-4 px-4 py-4 sm:flex-row sm:items-center sm:px-5 border-b border-slate-200 dark:border-navy-500">
            <label class="relative flex w-full sm:w-1/3">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama atau email..."
                    class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                <span class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 dark:text-navy-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </span>
            </label>
            
            <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                <select wire:model.live="planFilter" class="form-select w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent sm:w-32">
                    <option value="all">Semua Plan</option>
                    <option value="free">Free</option>
                    <option value="pro">Pro</option>
                </select>
                
                <select wire:model.live="statusFilter" class="form-select w-full rounded-lg border border-slate-300 bg-white px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent sm:w-32">
                    <option value="all">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="banned">Banned</option>
                </select>
            </div>
        </div>

        {{-- Table --}}
        <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
            <table class="is-hoverable w-full text-left">
                <thead>
                    <tr class="border-y border-transparent border-b-slate-200 bg-slate-50 dark:border-b-navy-500 dark:bg-navy-800">
                        <th class="whitespace-nowrap px-4 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Pengguna</th>
                        <th class="whitespace-nowrap px-4 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Status Plan</th>
                        <th class="whitespace-nowrap px-4 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Total Belanja</th>
                        <th class="whitespace-nowrap px-4 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Status Akun</th>
                        <th class="whitespace-nowrap px-4 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Tanggal Daftar</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500">
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                <div class="flex items-center space-x-3">
                                    <div class="avatar size-9">
                                        @if($user->profile_photo_path)
                                            <img class="rounded-full" src="{{ Storage::url($user->profile_photo_path) }}" alt="{{ $user->name }}" />
                                        @else
                                            <div class="is-initial rounded-full bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-medium text-slate-700 dark:text-navy-100">
                                            {{ $user->name }}
                                            @if($user->is_admin) 
                                                <span class="badge ml-1 bg-slate-800 text-white dark:bg-navy-500">Admin</span> 
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-400 dark:text-navy-300 mt-0.5">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                @php
                                    $activeSub = $user->subscriptions->first();
                                @endphp
                                @if($activeSub)
                                    <div class="flex items-center space-x-2">
                                        <span class="badge bg-warning/10 text-warning dark:bg-warning/15">PRO</span>
                                        <span class="text-xs text-slate-400 dark:text-navy-300">s/d {{ \Carbon\Carbon::parse($activeSub->expires_at)->format('d M Y') }}</span>
                                    </div>
                                @else
                                    <span class="badge bg-slate-100 text-slate-600 dark:bg-navy-500 dark:text-navy-100">Free</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 font-medium text-slate-700 dark:text-navy-100 sm:px-5">
                                Rp {{ number_format($user->subscriptions_sum_amount ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                @if($user->banned_at)
                                    <span class="badge bg-error/10 text-error dark:bg-error/15">Banned</span>
                                @else
                                    <span class="badge bg-success/10 text-success dark:bg-success/15">Aktif</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600 dark:text-navy-100 sm:px-5">
                                {{ $user->created_at->format('d M Y') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right sm:px-5">
                                @if(!$user->is_admin)
                                    @if($user->banned_at)
                                        <button wire:click="unbanUser({{ $user->id }})" class="btn border border-success/30 px-3 py-1 text-xs font-medium text-success hover:bg-success/10 dark:border-success/30 dark:hover:bg-success/10">Unban</button>
                                    @else
                                        <button wire:click="confirmBan({{ $user->id }})" class="btn border border-error/30 px-3 py-1 text-xs font-medium text-error hover:bg-error/10 dark:border-error/30 dark:hover:bg-error/10">Ban</button>
                                    @endif
                                @else
                                    <span class="text-xs italic text-slate-400 dark:text-navy-300">No action</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="whitespace-nowrap px-4 py-8 text-center text-slate-400 dark:text-navy-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-12 mx-auto text-slate-300 dark:text-navy-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <p>Tidak ada pengguna yang ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="border-t border-slate-200 px-4 py-4 dark:border-navy-500 sm:px-5">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
