<div>
    @section('title', 'Manajemen Pengguna - ' . config('app.name'))
    @section('page_title', 'Manajemen Pengguna')
    @section('page_breadcrumb', 'Manajemen Pengguna')

    {{-- Top Action Toolbar --}}
    <div class="flex items-center justify-between py-4 sm:py-5">
        <div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-navy-50 lg:text-2xl">
                Daftar & Manajemen Pengguna
            </h2>
            <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">
                Kelola status akun, verifikasi paket langganan, dan riwayat belanja pengguna terdaftar.
            </p>
        </div>
        <div class="badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light text-xs font-bold px-3 py-1">
            Total {{ $users->total() }} Pengguna
        </div>
    </div>

    {{-- Lineone Table Advanced Container --}}
    <div class="card">
        {{-- Filters Bar --}}
        <div class="flex flex-col justify-between gap-3 p-4 sm:flex-row sm:items-center sm:px-5 border-b border-slate-150 dark:border-navy-600">
            {{-- Search Bar --}}
            <div class="relative flex w-full sm:w-80">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama atau email pengguna..."
                    class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-1.5 pl-9 text-xs placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                <span class="pointer-events-none absolute flex h-full w-9 items-center justify-center text-slate-400 dark:text-navy-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </span>
            </div>
            
            {{-- Dropdown Filters --}}
            <div class="flex items-center space-x-2.5">
                <select wire:model.live="planFilter" class="form-select rounded-lg border border-slate-300 bg-transparent px-3 py-1.5 text-xs hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent">
                    <option value="all">Semua Paket</option>
                    <option value="free">Paket Free</option>
                    <option value="pro">Paket Pro</option>
                </select>
                
                <select wire:model.live="statusFilter" class="form-select rounded-lg border border-slate-300 bg-transparent px-3 py-1.5 text-xs hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent">
                    <option value="all">Semua Status</option>
                    <option value="active">Akun Aktif</option>
                    <option value="banned">Akun Banned</option>
                </select>
            </div>
        </div>

        {{-- Table (Exact Table Advanced Lineone) --}}
        <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
            <table class="is-hoverable w-full text-left">
                <thead>
                    <tr>
                        <th class="rounded-tl-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs">
                            PENGGUNA
                        </th>
                        <th class="bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs">
                            STATUS PAKET
                        </th>
                        <th class="bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs">
                            TOTAL BELANJA
                        </th>
                        <th class="bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs text-center">
                            STATUS AKUN
                        </th>
                        <th class="bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs">
                            TERDAFTAR
                        </th>
                        <th class="rounded-tr-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs text-right">
                            AKSI
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-150 dark:divide-navy-500">
                    @forelse($users as $user)
                        <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500">
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                <div class="flex items-center space-x-3">
                                    <div class="avatar size-8 shrink-0">
                                        @if($user->profile_photo_path)
                                            <img class="rounded-full object-cover" src="{{ Storage::url($user->profile_photo_path) }}" alt="{{ $user->name }}" />
                                        @else
                                            <div class="is-initial rounded-full bg-primary/10 text-xs font-bold uppercase text-primary dark:bg-accent-light/10 dark:text-accent-light">
                                                {{ substr($user->name, 0, 2) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-700 dark:text-navy-100 text-xs sm:text-sm flex items-center">
                                            <span>{{ $user->name }}</span>
                                            @if($user->is_admin) 
                                                <span class="badge rounded-full ml-1.5 bg-slate-800 text-white dark:bg-navy-500 text-[10px] font-bold px-2 py-0.5">Admin</span> 
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-400 dark:text-navy-300 mt-0.5">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                @php
                                    $activeSub = $user->subscriptions->first();
                                @endphp
                                @if($activeSub)
                                    <div>
                                        <span class="badge rounded-full bg-warning/15 text-warning font-bold text-[11px] px-2.5 py-0.5">
                                            PRO MEMBER
                                        </span>
                                        <p class="text-[10px] text-slate-400 dark:text-navy-300 mt-0.5">
                                            s/d {{ \Carbon\Carbon::parse($activeSub->expires_at)->translatedFormat('d M Y') }}
                                        </p>
                                    </div>
                                @else
                                    <span class="badge rounded-full bg-slate-150 text-slate-600 dark:bg-navy-500 dark:text-navy-200 text-[11px] font-semibold px-2.5 py-0.5">
                                        Free Plan
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 font-bold text-slate-700 dark:text-navy-100 sm:px-5 text-xs">
                                Rp {{ number_format($user->subscriptions_sum_amount ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5 text-center">
                                @if($user->banned_at)
                                    <span class="badge space-x-1.5 rounded-full bg-error/10 text-error text-[11px] font-bold px-2.5 py-0.5">
                                        <span class="size-1.5 rounded-full bg-current"></span>
                                        <span>Banned</span>
                                    </span>
                                @else
                                    <span class="badge space-x-1.5 rounded-full bg-success/10 text-success text-[11px] font-bold px-2.5 py-0.5">
                                        <span class="size-1.5 rounded-full bg-current"></span>
                                        <span>Aktif</span>
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-500 dark:text-navy-300 sm:px-5">
                                {{ $user->created_at->translatedFormat('d M Y') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right sm:px-5">
                                @if(!$user->is_admin)
                                    @if($user->banned_at)
                                        <button wire:click="unbanUser({{ $user->id }})" class="btn rounded-full border border-success/30 px-3 py-1 text-xs font-semibold text-success hover:bg-success/10 shadow-xs">
                                            Buka Ban
                                        </button>
                                    @else
                                        <button wire:click="confirmBan({{ $user->id }})" class="btn rounded-full border border-error/30 px-3 py-1 text-xs font-semibold text-error hover:bg-error/10 shadow-xs">
                                            Ban User
                                        </button>
                                    @endif
                                @else
                                    <span class="text-[11px] italic text-slate-400 dark:text-navy-400">Tidak ada aksi</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="whitespace-nowrap px-4 py-8 text-center text-slate-400 dark:text-navy-300 text-xs">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-10 mx-auto text-slate-300 dark:text-navy-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <p>Tidak ada pengguna yang cocok dengan pencarian.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
            <div class="border-t border-slate-150 px-4 py-3 dark:border-navy-600 sm:px-5">
                {{ $users->links('components.lineone-pagination') }}
            </div>
        @endif
    </div>
</div>
