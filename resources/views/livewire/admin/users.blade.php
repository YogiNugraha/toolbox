<div>
    @section('title', 'Manajemen Pengguna - Admin ' . config('app.name'))
    @section('page_title', 'Manajemen Pengguna')
    @section('page_breadcrumb', 'Pengguna')

    {{-- Header & Subtitle --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 py-4 sm:py-5">
        <div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-navy-50 lg:text-2xl">
                Daftar & Manajemen Pengguna
            </h2>
            <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">
                Kelola data akun, reset password, atur paket langganan secara instan, verifikasi email, hingga status ban & hapus pengguna.
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <span class="badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light text-xs font-bold px-3 py-1">
                Total {{ $users->total() }} Pengguna
            </span>
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
                    <x-lucide-search class="size-4" />
                </span>
            </div>
            
            {{-- Dropdown Filters --}}
            <div class="flex items-center space-x-2.5">
                <select wire:model.live="planFilter" class="form-select rounded-lg border border-slate-300 bg-transparent px-3 py-1.5 text-xs hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent">
                    <option value="all">Semua Paket</option>
                    <option value="free">Paket Free</option>
                    @foreach($plans as $plan)
                        @if($plan->slug !== 'free')
                            <option value="{{ $plan->slug }}">Paket {{ $plan->name }}</option>
                        @endif
                    @endforeach
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
                                <div class="flex items-center space-x-3 cursor-pointer" wire:click="openUserModal({{ $user->id }})">
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
                                        <div class="font-bold text-slate-700 hover:text-primary dark:text-navy-100 dark:hover:text-accent-light text-xs sm:text-sm flex items-center transition-colors">
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
                                    $planName = $activeSub ? ($activeSub->plan->name ?? ucfirst($activeSub->plan_slug)) : null;
                                    $isProMax = $activeSub && ($activeSub->plan_slug === 'pro-max' || strtolower((string)$planName) === 'pro max');
                                @endphp
                                @if($activeSub)
                                    <div>
                                        @if($isProMax)
                                            <span class="badge rounded-full bg-linear-to-r from-amber-500 via-purple-600 to-indigo-600 text-white font-black text-[10px] px-2.5 py-0.5 shadow-xs uppercase tracking-wider inline-flex items-center gap-1">
                                                <x-lucide-crown class="size-3 stroke-[2.5]" />
                                                <span>{{ $planName }}</span>
                                            </span>
                                        @else
                                            <span class="badge rounded-full bg-linear-to-r from-amber-500 to-orange-500 text-white font-black text-[10px] px-2.5 py-0.5 shadow-xs uppercase tracking-wider inline-flex items-center gap-1">
                                                <x-lucide-star class="size-3 stroke-[2.5] fill-current" />
                                                <span>{{ $planName }}</span>
                                            </span>
                                        @endif
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
                                <div class="flex items-center justify-end space-x-1.5">
                                    <button wire:click="openUserModal({{ $user->id }})" class="btn rounded-lg bg-primary/10 text-primary hover:bg-primary/20 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 px-3 py-1.5 text-xs font-semibold shadow-xs flex items-center space-x-1">
                                        <x-lucide-user-cog class="size-3.5" />
                                        <span>Kelola Akun</span>
                                    </button>

                                    @if(!$user->is_admin && $user->id !== auth()->id())
                                        @if($user->banned_at)
                                            <button wire:click="unbanUser({{ $user->id }})" title="Buka Ban" class="btn size-7 rounded-lg border border-success/30 p-0 text-xs font-semibold text-success hover:bg-success/10 shadow-xs">
                                                <x-lucide-shield-check class="size-3.5" />
                                            </button>
                                        @else
                                            <button wire:click="confirmBan({{ $user->id }})" title="Ban Pengguna" class="btn size-7 rounded-lg border border-warning/30 p-0 text-xs font-semibold text-warning hover:bg-warning/10 shadow-xs">
                                                <x-lucide-shield-alert class="size-3.5" />
                                            </button>
                                        @endif
                                        <button wire:click="confirmDeleteUser({{ $user->id }})" title="Hapus Pengguna" class="btn size-7 rounded-lg border border-error/30 p-0 text-xs font-semibold text-error hover:bg-error/10 shadow-xs">
                                            <x-lucide-trash-2 class="size-3.5" />
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="whitespace-nowrap px-4 py-8 text-center text-slate-400 dark:text-navy-300 text-xs">
                                <x-lucide-users class="size-10 mx-auto text-slate-300 dark:text-navy-400 mb-2" />
                                <p>Tidak ada pengguna yang cocok dengan pencarian.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
            <div class="flex flex-col justify-between gap-4 p-4 sm:flex-row sm:items-center sm:px-5 border-t border-slate-150 dark:border-navy-600">
                <div class="text-xs text-slate-400 dark:text-navy-300">
                    Menampilkan <strong>{{ $users->firstItem() }}</strong> sampai <strong>{{ $users->lastItem() }}</strong> dari <strong>{{ $users->total() }}</strong> pengguna
                </div>
                <div>
                    {{ $users->links('components.lineone-pagination') }}
                </div>
            </div>
        @endif
    </div>

    {{-- Lineone Edit / Manage User Modal --}}
    @if($showUserModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-300"
             x-data
             x-on:keydown.escape.window="$wire.closeUserModal()">
            <div class="relative w-full max-w-2xl rounded-2xl bg-white dark:bg-navy-700 shadow-2xl border border-slate-200 dark:border-navy-600 overflow-hidden flex flex-col max-h-[90vh]">
                
                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-150 dark:border-navy-600 bg-slate-50 dark:bg-navy-800">
                    <div class="flex items-center space-x-3">
                        <div class="mask is-squircle flex size-10 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light font-bold">
                            <x-lucide-user-cog class="size-5" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800 dark:text-navy-100 flex items-center gap-2">
                                <span>Kelola Akun Pengguna</span>
                                @if($editIsBanned)
                                    <span class="badge rounded-full bg-error/15 text-error text-[10px] font-bold px-2 py-0.5">Banned</span>
                                @endif
                                @if($editIsAdmin)
                                    <span class="badge rounded-full bg-slate-800 text-white dark:bg-navy-500 text-[10px] font-bold px-2 py-0.5">Admin</span>
                                @endif
                            </h3>
                            <p class="text-xs text-slate-400 dark:text-navy-300">
                                ID: #{{ $selectedUserId }} · {{ $editEmail }}
                            </p>
                        </div>
                    </div>
                    <button wire:click="closeUserModal" class="btn size-8 rounded-full p-0 text-slate-400 hover:bg-slate-200 hover:text-slate-700 dark:hover:bg-navy-600 dark:hover:text-navy-100">
                        <x-lucide-x class="size-4.5" />
                    </button>
                </div>

                {{-- Modal Body (Scrollable) --}}
                <div class="p-5 space-y-6 overflow-y-auto is-scrollbar-hidden flex-1">
                    
                    {{-- 1. IDENTITAS & PROFIL --}}
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-primary dark:text-accent-light mb-3 flex items-center gap-1.5">
                            <x-lucide-user class="size-4" />
                            <span>1. Identitas & Informasi Akun</span>
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-navy-100 mb-1">Nama Lengkap</label>
                                <input wire:model="editName" type="text" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-xs hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" required />
                                @error('editName') <span class="text-[11px] text-error mt-0.5 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-navy-100 mb-1">Alamat Email</label>
                                <input wire:model="editEmail" type="email" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-xs hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" required />
                                @error('editEmail') <span class="text-[11px] text-error mt-0.5 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mt-3.5 grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="rounded-xl border border-slate-200 dark:border-navy-600 p-3 bg-slate-50/50 dark:bg-navy-800/40">
                                <span class="text-[11px] text-slate-400 dark:text-navy-300 block mb-1">Status Verifikasi</span>
                                @if($editEmailVerified)
                                    <span class="badge rounded-full bg-success/15 text-success text-[10px] font-bold px-2 py-0.5 inline-flex items-center gap-1">
                                        <x-lucide-check class="size-3" />
                                        <span>Terverifikasi</span>
                                    </span>
                                @else
                                    <div class="flex items-center justify-between">
                                        <span class="badge rounded-full bg-warning/15 text-warning text-[10px] font-bold px-2 py-0.5">Belum</span>
                                        <button wire:click="verifyEmailNow" type="button" class="text-[10px] text-primary hover:underline dark:text-accent-light font-bold">
                                            Verifikasi Sekarang
                                        </button>
                                    </div>
                                @endif
                            </div>

                            <div class="rounded-xl border border-slate-200 dark:border-navy-600 p-3 bg-slate-50/50 dark:bg-navy-800/40">
                                <span class="text-[11px] text-slate-400 dark:text-navy-300 block mb-1">Hak Akses Role</span>
                                <label class="inline-flex items-center space-x-2 cursor-pointer">
                                    <input wire:model="editIsAdmin" type="checkbox" class="form-checkbox is-basic size-4 rounded border-slate-400/70 checked:bg-primary checked:border-primary dark:border-navy-400 dark:checked:bg-accent dark:checked:border-accent" @if($selectedUserId === auth()->id()) disabled @endif />
                                    <span class="text-xs font-semibold text-slate-700 dark:text-navy-100">Administrator</span>
                                </label>
                            </div>

                            <div class="rounded-xl border border-slate-200 dark:border-navy-600 p-3 bg-slate-50/50 dark:bg-navy-800/40">
                                <span class="text-[11px] text-slate-400 dark:text-navy-300 block mb-1">Status Blokir (Ban)</span>
                                <label class="inline-flex items-center space-x-2 cursor-pointer">
                                    <input wire:model="editIsBanned" type="checkbox" class="form-checkbox is-basic size-4 rounded border-slate-400/70 checked:bg-error checked:border-error dark:border-navy-400 dark:checked:bg-error dark:checked:border-error" @if($selectedUserId === auth()->id()) disabled @endif />
                                    <span class="text-xs font-semibold text-error">Banned / Ditangguhkan</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="h-px bg-slate-150 dark:bg-navy-600"></div>

                    {{-- 2. PENGATURAN PAKET & LANGGANAN BEBAS --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-primary dark:text-accent-light flex items-center gap-1.5">
                                <x-lucide-crown class="size-4" />
                                <span>2. Pengaturan Paket Langganan (Tanpa Bayar)</span>
                            </h4>
                            <span class="badge rounded-full bg-info/10 text-info text-[10px] font-bold px-2 py-0.5">
                                Direct Admin Grant
                            </span>
                        </div>

                        <div class="rounded-xl border border-primary/20 dark:border-accent/20 bg-primary/5 dark:bg-accent/5 p-4 space-y-3.5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-navy-100 mb-1">Pilih Paket</label>
                                    <select wire:model.live="editPlanSlug" class="form-select w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-800 dark:hover:border-navy-400 dark:focus:border-accent font-semibold">
                                        <option value="free">Free Plan (Tanpa Langganan)</option>
                                        @foreach($plans as $plan)
                                            @if($plan->slug !== 'free')
                                                <option value="{{ $plan->slug }}">Paket {{ $plan->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                @if($editPlanSlug !== 'free')
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-navy-100 mb-1">Durasi Langganan</label>
                                        <select wire:model.live="editDurationType" class="form-select w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-800 dark:hover:border-navy-400 dark:focus:border-accent">
                                            <option value="30">1 Bulan (30 Hari)</option>
                                            <option value="90">3 Bulan (90 Hari)</option>
                                            <option value="180">6 Bulan (180 Hari)</option>
                                            <option value="365">1 Tahun (365 Hari)</option>
                                            <option value="lifetime">Selamanya / Lifetime (50 Tahun)</option>
                                            <option value="custom">Kustom (Tentukan Tanggal)</option>
                                        </select>
                                    </div>
                                @endif
                            </div>

                            @if($editPlanSlug !== 'free')
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-1">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-navy-100 mb-1">Masa Aktif Berakhir Pada</label>
                                        <input wire:model="editExpiresAt" type="date" class="form-input w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:bg-navy-800 dark:hover:border-navy-400 dark:focus:border-accent" required />
                                    </div>
                                    <div class="flex items-center pt-5">
                                        <p class="text-[11px] text-slate-500 dark:text-navy-300 leading-relaxed">
                                            ✓ Paket akan langsung aktif di akun pengguna dengan kuota fitur tanpa batas tanpa perlu proses checkout/bayar.
                                        </p>
                                    </div>
                                </div>
                            @else
                                <p class="text-[11px] text-slate-500 dark:text-navy-300">
                                    Jika memilih Free Plan, maka langganan berbayar aktif saat ini (jika ada) akan otomatis dibatalkan.
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="h-px bg-slate-150 dark:bg-navy-600"></div>

                    {{-- 3. GANTI / RESET PASSWORD --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-primary dark:text-accent-light flex items-center gap-1.5">
                                <x-lucide-key-round class="size-4" />
                                <span>3. Ganti / Reset Password</span>
                            </h4>
                            <button wire:click="generateRandomPassword" type="button" class="btn h-7 rounded-lg bg-slate-150 px-2.5 text-[11px] font-semibold text-slate-700 hover:bg-slate-200 dark:bg-navy-600 dark:text-navy-100 dark:hover:bg-navy-500 shadow-xs flex items-center gap-1">
                                <x-lucide-wand-2 class="size-3" />
                                <span>Buat Password Acak</span>
                            </button>
                        </div>

                        @if($generatedPassword)
                            <div class="mb-3 rounded-lg bg-info/10 border border-info/30 p-2.5 flex items-center justify-between text-xs text-info">
                                <span>Password dibuat: <strong class="font-mono text-slate-800 dark:text-navy-100 text-sm select-all">{{ $generatedPassword }}</strong></span>
                                <span class="text-[10px] text-slate-500">Salin sebelum menutup modal</span>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-navy-100 mb-1">Password Baru (Kosongkan jika tidak ingin diubah)</label>
                                <input wire:model="newPassword" type="text" placeholder="Masukkan password baru..." class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-xs hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                                @error('newPassword') <span class="text-[11px] text-error mt-0.5 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-navy-100 mb-1">Konfirmasi Password Baru</label>
                                <input wire:model="newPasswordConfirmation" type="text" placeholder="Ulangi password baru..." class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-xs hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                                @error('newPasswordConfirmation') <span class="text-[11px] text-error mt-0.5 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- 4. DANGER ZONE (HAPUS AKUN) --}}
                    @if($selectedUserId !== auth()->id())
                        <div class="h-px bg-slate-150 dark:bg-navy-600"></div>
                        <div class="rounded-xl border border-error/20 bg-error/5 p-3.5 flex items-center justify-between">
                            <div>
                                <h5 class="text-xs font-bold text-error">Hapus Akun Pengguna</h5>
                                <p class="text-[11px] text-slate-500 dark:text-navy-300">Menghapus akun beserta semua riwayat dan langganan secara permanen.</p>
                            </div>
                            <button wire:click="confirmDeleteUser({{ $selectedUserId }})" type="button" class="btn h-8 rounded-lg bg-error text-white text-xs font-semibold px-3 shadow-xs hover:bg-error-focus">
                                Hapus Akun
                            </button>
                        </div>
                    @endif

                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-end space-x-2.5 px-5 py-3.5 border-t border-slate-150 dark:border-navy-600 bg-slate-50 dark:bg-navy-800">
                    <button wire:click="closeUserModal" type="button" class="btn h-9 rounded-lg border border-slate-300 px-4 text-xs font-semibold text-slate-700 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-100 dark:hover:bg-navy-600">
                        Batal
                    </button>
                    <button wire:click="saveUser" type="button" class="btn h-9 rounded-lg bg-primary px-5 text-xs font-semibold text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus shadow-sm flex items-center space-x-1.5">
                        <span wire:loading.remove wire:target="saveUser">Simpan Perubahan</span>
                        <span wire:loading wire:target="saveUser">Menyimpan...</span>
                    </button>
                </div>

            </div>
        </div>
    @endif
</div>
