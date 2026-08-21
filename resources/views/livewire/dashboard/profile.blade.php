<div>
    @section('title', 'Profil Akun - ' . config('app.name'))
    @section('page_title', 'Profil Akun')
    @section('page_breadcrumb', 'Profil Akun')

    {{-- Breadcrumb / Header --}}
    <div class="flex items-center justify-between py-4 sm:py-5">
        <div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-navy-50 lg:text-2xl">
                Pengaturan Akun & Profil
            </h2>
            <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">
                Kelola informasi identitas pribadi, kontak, foto profil, dan keamanan password Anda.
            </p>
        </div>
        <div class="badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light text-xs font-bold px-3 py-1">
            Account Settings
        </div>
    </div>

    {{-- 12-Column Layout matching Lineone forms-layout-v5 --}}
    <div class="grid grid-cols-12 gap-4 sm:gap-5 lg:gap-6">
        
        {{-- Left Sidebar Column (4 Cols) --}}
        <div class="col-span-12 lg:col-span-4 space-y-4 sm:space-y-5 lg:space-y-6">
            {{-- User Summary Card --}}
            <div class="card p-4 sm:p-5">
                <div class="flex items-center space-x-4">
                    <div class="avatar size-14 shrink-0">
                        @if(auth()->user()->profile_photo_path)
                            <img class="mask is-squircle object-cover" src="{{ Storage::url(auth()->user()->profile_photo_path) }}" alt="{{ auth()->user()->name }}" />
                        @else
                            <div class="mask is-squircle flex size-14 items-center justify-center bg-primary/10 text-xl font-bold text-primary dark:bg-accent-light/10 dark:text-accent-light">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-700 dark:text-navy-100">
                            {{ auth()->user()->name }}
                        </h3>
                        <p class="text-xs text-slate-400 dark:text-navy-300">{{ auth()->user()->email }}</p>
                        <div class="mt-1.5">
                            @if(auth()->user()->subscriptions()->where('status', 'active')->where('expires_at', '>', now())->exists())
                                <span class="badge rounded-full bg-warning/15 text-warning font-bold text-[10px] px-2 py-0.5">
                                    PRO MEMBER
                                </span>
                            @else
                                <span class="badge rounded-full bg-slate-150 text-slate-600 dark:bg-navy-600 dark:text-navy-200 font-semibold text-[10px] px-2 py-0.5">
                                    FREE PLAN
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Fast Navigation Nav List --}}
                <ul class="mt-6 space-y-1.5 font-medium text-xs">
                    <li>
                        <a class="flex items-center space-x-2.5 rounded-xl bg-primary px-4 py-2.5 tracking-wide text-white outline-hidden transition-all dark:bg-accent shadow-xs" href="#">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="font-semibold">Informasi Akun</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('history') }}" wire:navigate class="group flex items-center space-x-2.5 rounded-xl px-4 py-2.5 tracking-wide text-slate-600 outline-hidden transition-all hover:bg-slate-100 hover:text-slate-800 dark:text-navy-200 dark:hover:bg-navy-600 dark:hover:text-navy-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5 text-slate-400 group-hover:text-slate-500 dark:text-navy-300 dark:group-hover:text-navy-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Riwayat Aktivitas</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('dashboard.billing') }}" wire:navigate class="group flex items-center space-x-2.5 rounded-xl px-4 py-2.5 tracking-wide text-slate-600 outline-hidden transition-all hover:bg-slate-100 hover:text-slate-800 dark:text-navy-200 dark:hover:bg-navy-600 dark:hover:text-navy-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5 text-slate-400 group-hover:text-slate-500 dark:text-navy-300 dark:group-hover:text-navy-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            <span>Paket & Tagihan</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('pricing') }}" wire:navigate class="group flex items-center space-x-2.5 rounded-xl px-4 py-2.5 tracking-wide text-slate-600 outline-hidden transition-all hover:bg-slate-100 hover:text-slate-800 dark:text-navy-200 dark:hover:bg-navy-600 dark:hover:text-navy-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5 text-slate-400 group-hover:text-slate-500 dark:text-navy-300 dark:group-hover:text-navy-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Upgrade ke Pro</span>
                        </a>
                    </li>
                </ul>

                <div class="my-5 h-px bg-slate-150 dark:bg-navy-600"></div>

                {{-- Activity Statistics --}}
                <div>
                    <h4 class="font-bold text-slate-700 dark:text-navy-100 uppercase tracking-wide text-xs mb-3">
                        Statistik Penggunaan
                    </h4>
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between rounded-xl border border-slate-150 bg-slate-50/60 p-3 dark:border-navy-600 dark:bg-navy-700/40">
                            <span class="text-slate-500 dark:text-navy-300">Total File Diproses</span>
                            <span class="font-bold text-slate-800 dark:text-navy-100 text-sm">{{ auth()->user()->activities()->count() }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-xl border border-slate-150 bg-slate-50/60 p-3 dark:border-navy-600 dark:bg-navy-700/40">
                            <span class="text-slate-500 dark:text-navy-300">Berhasil Diselesaikan</span>
                            <span class="font-bold text-success text-sm">{{ auth()->user()->activities()->where('status', 'completed')->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Main Column: forms-layout-v5 Style (8 Cols) --}}
        <div class="col-span-12 lg:col-span-8 space-y-4 sm:space-y-5 lg:space-y-6">
            
            {{-- Profile Settings Card --}}
            <div class="card">
                <div class="flex items-center justify-between border-b border-slate-150 p-4 dark:border-navy-600 sm:px-5">
                    <h2 class="text-base font-bold tracking-wide text-slate-700 dark:text-navy-100">
                        Pengaturan Profil
                    </h2>
                    <div class="flex items-center space-x-2">
                        <button type="button" wire:click="updateProfile" class="btn min-w-[7rem] rounded-full bg-primary font-bold text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus text-xs py-2 shadow-sm">
                            <span wire:loading.remove wire:target="updateProfile">Simpan Profil</span>
                            <div wire:loading wire:target="updateProfile" class="flex items-center space-x-1.5">
                                <div class="spinner size-3.5 animate-spin rounded-full border-2 border-current border-r-transparent"></div>
                                <span>Menyimpan...</span>
                            </div>
                        </button>
                    </div>
                </div>

                <div class="p-4 sm:p-5">
                    {{-- Avatar Section with Squircle and Edit Action --}}
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-slate-700 dark:text-navy-100 uppercase tracking-wide">
                            Foto Profil (Avatar)
                        </span>
                        <div class="avatar mt-2 size-20 relative">
                            @if(auth()->user()->profile_photo_path)
                                <img class="mask is-squircle object-cover size-full" src="{{ Storage::url(auth()->user()->profile_photo_path) }}" alt="{{ auth()->user()->name }}" />
                            @else
                                <div class="mask is-squircle flex size-full items-center justify-center bg-primary/10 text-2xl font-bold uppercase text-primary dark:bg-accent-light/10 dark:text-accent-light">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                            @endif
                            
                            {{-- Change Avatar Button Overlay --}}
                            <label class="absolute -bottom-1 -right-1 flex size-7 cursor-pointer items-center justify-center rounded-full bg-white shadow-sm border border-slate-200 hover:bg-slate-50 dark:border-navy-500 dark:bg-navy-700 dark:hover:bg-navy-600 transition-colors" title="Ubah Foto">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5 text-slate-600 dark:text-navy-200" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                </svg>
                                <input type="file" wire:model.live="photo" class="hidden" accept="image/*">
                            </label>

                            <div wire:loading wire:target="photo" class="mask is-squircle absolute inset-0 flex items-center justify-center bg-slate-900/60">
                                <div class="spinner size-5 animate-spin rounded-full border-2 border-white border-r-transparent"></div>
                            </div>
                        </div>
                        @error('photo') <span class="mt-1 text-[11px] text-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="my-6 h-px bg-slate-150 dark:bg-navy-600"></div>

                    {{-- Pending Email Alert if Any --}}
                    @if(auth()->user()->pending_email)
                        <div class="alert flex items-center justify-between rounded-xl border border-info/30 bg-info/10 px-4 py-3 text-info text-xs mb-5"
                            x-data="{ cooldown: 0 }"
                            x-on:cooldown-start.window="cooldown = $event.detail.seconds; let t = setInterval(() => { cooldown--; if (cooldown <= 0) clearInterval(t); }, 1000);">
                            <div class="flex items-center space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Menunggu konfirmasi ke <strong>{{ auth()->user()->pending_email }}</strong></span>
                            </div>
                            <div class="flex space-x-2">
                                <button wire:click="cancelPendingEmail" type="button" class="underline font-medium hover:opacity-80">Batalkan</button>
                                <button wire:click="resendPendingEmail" type="button" :disabled="cooldown > 0" class="font-bold underline hover:opacity-80 disabled:opacity-50">
                                    <span wire:loading.remove wire:target="resendPendingEmail" x-show="cooldown === 0">Kirim Ulang</span>
                                    <span wire:loading wire:target="resendPendingEmail">Mengirim...</span>
                                    <span wire:loading.remove wire:target="resendPendingEmail" x-show="cooldown > 0" x-text="'Terkirim (' + cooldown + 's)'" style="display: none;"></span>
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- Form Inputs with Rounded Full and Leading Icons (forms-layout-v5 Style) --}}
                    <form wire:submit="updateProfile" class="space-y-4">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 text-xs">
                            {{-- Full Name Input --}}
                            <label class="block">
                                <span class="font-semibold text-slate-700 dark:text-navy-100">Nama Lengkap</span>
                                <span class="relative mt-1.5 flex">
                                    <input wire:model="name" class="form-input peer w-full rounded-full border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent text-xs" placeholder="Masukkan nama lengkap" type="text" />
                                    <span class="pointer-events-none absolute flex h-full w-9 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </span>
                                </span>
                                @error('name') <span class="text-[11px] text-error mt-1 block">{{ $message }}</span> @enderror
                            </label>

                            {{-- Email Address Input --}}
                            <label class="block">
                                <span class="font-semibold text-slate-700 dark:text-navy-100">Alamat Email</span>
                                <span class="relative mt-1.5 flex">
                                    <input wire:model="email" class="form-input peer w-full rounded-full border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent text-xs" placeholder="Masukkan email" type="email" />
                                    <span class="pointer-events-none absolute flex h-full w-9 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </span>
                                </span>
                                @error('email') <span class="text-[11px] text-error mt-1 block">{{ $message }}</span> @enderror
                            </label>

                            {{-- Phone Number Input --}}
                            <div class="sm:col-span-2">
                                <label class="block">
                                    <span class="font-semibold text-slate-700 dark:text-navy-100">Nomor WhatsApp / HP</span>
                                    <div class="mt-1.5 flex -space-x-px">
                                        <select wire:model="country_code" class="form-select w-28 rounded-l-full border border-slate-300 bg-slate-50 px-3 py-2 text-xs hover:z-10 hover:border-slate-400 focus:z-10 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                                            <option value="+62">🇮🇩 +62</option>
                                            <option value="+1">🇺🇸 +1</option>
                                            <option value="+44">🇬🇧 +44</option>
                                            <option value="+60">🇲🇾 +60</option>
                                            <option value="+65">🇸🇬 +65</option>
                                            <option value="+61">🇦🇺 +61</option>
                                        </select>
                                        <span class="relative flex flex-1">
                                            <input wire:model="phone" oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^0+/, '')" class="form-input peer w-full rounded-r-full border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent text-xs" placeholder="81234567890" type="text" />
                                            <span class="pointer-events-none absolute flex h-full w-9 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                                </svg>
                                            </span>
                                        </span>
                                    </div>
                                    @error('phone') <span class="text-[11px] text-error mt-1 block">{{ $message }}</span> @enderror
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="btn rounded-full bg-primary font-bold text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus text-xs px-6 py-2 shadow-sm">
                                <span wire:loading.remove wire:target="updateProfile">Simpan Informasi Profil</span>
                                <span wire:loading wire:target="updateProfile">Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Security & Password Card (forms-layout-v5 Style) --}}
            <div class="card">
                <div class="border-b border-slate-150 p-4 dark:border-navy-600 sm:px-5">
                    <h2 class="text-base font-bold tracking-wide text-slate-700 dark:text-navy-100">
                        Keamanan & Kata Sandi
                    </h2>
                    <p class="text-xs text-slate-400 dark:text-navy-300 mt-0.5">
                        Pastikan akun Anda menggunakan password yang kuat dan tidak digunakan di situs lain.
                    </p>
                </div>

                <div class="p-4 sm:p-5">
                    <form wire:submit="updatePassword" class="space-y-4 text-xs">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            {{-- Current Password --}}
                            <label class="block">
                                <span class="font-semibold text-slate-700 dark:text-navy-100">Password Saat Ini</span>
                                <span class="relative mt-1.5 flex">
                                    <input wire:model="current_password" class="form-input peer w-full rounded-full border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent text-xs" type="password" placeholder="••••••••" />
                                    <span class="pointer-events-none absolute flex h-full w-9 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </span>
                                </span>
                                @error('current_password') <span class="text-[11px] text-error mt-1 block">{{ $message }}</span> @enderror
                            </label>

                            {{-- New Password --}}
                            <label class="block">
                                <span class="font-semibold text-slate-700 dark:text-navy-100">Password Baru</span>
                                <span class="relative mt-1.5 flex">
                                    <input wire:model="password" class="form-input peer w-full rounded-full border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent text-xs" type="password" placeholder="Minimal 8 karakter" />
                                    <span class="pointer-events-none absolute flex h-full w-9 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                        </svg>
                                    </span>
                                </span>
                                @error('password') <span class="text-[11px] text-error mt-1 block">{{ $message }}</span> @enderror
                            </label>

                            {{-- Password Confirmation --}}
                            <label class="block">
                                <span class="font-semibold text-slate-700 dark:text-navy-100">Ulangi Password Baru</span>
                                <span class="relative mt-1.5 flex">
                                    <input wire:model="password_confirmation" class="form-input peer w-full rounded-full border border-slate-300 bg-transparent px-3 py-2 pl-9 placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent text-xs" type="password" placeholder="Ulangi password baru" />
                                    <span class="pointer-events-none absolute flex h-full w-9 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </span>
                                </span>
                            </label>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="btn rounded-full bg-primary font-bold text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus text-xs px-6 py-2 shadow-sm">
                                <span wire:loading.remove wire:target="updatePassword">Perbarui Kata Sandi</span>
                                <span wire:loading wire:target="updatePassword">Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
