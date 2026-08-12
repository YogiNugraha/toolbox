<div>
    @section('page_title', 'Profil Saya')

    <div class="grid grid-cols-1 gap-4 sm:gap-5 lg:grid-cols-3 lg:gap-6">
        {{-- Kolom Kiri: Info Profil & Statistik --}}
        <div class="col-span-1 space-y-4 sm:space-y-5 lg:space-y-6">
            <div class="card px-4 py-4 sm:px-5">
                <div class="flex flex-col items-center text-center">
                    <div class="group relative flex size-24 shrink-0 overflow-hidden rounded-full border border-primary/20 bg-primary/10 dark:border-accent-light/20 dark:bg-accent-light/10">
                        @if(auth()->user()->profile_photo_path)
                            <img src="{{ Storage::url(auth()->user()->profile_photo_path) }}" alt="{{ auth()->user()->name }}" class="size-full object-cover">
                        @else
                            <div class="flex size-full items-center justify-center font-semibold uppercase text-primary dark:text-accent-light text-4xl">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                        @endif

                        {{-- Hover Overlay --}}
                        <label class="absolute inset-0 flex cursor-pointer flex-col items-center justify-center bg-slate-900/50 opacity-0 transition-opacity group-hover:opacity-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                            <span class="mt-1 text-tiny-plus font-medium uppercase tracking-wider text-white">Ubah</span>
                            <input type="file" wire:model.live="photo" class="hidden" accept="image/*">
                        </label>

                        {{-- Loading Indicator --}}
                        <div wire:loading wire:target="photo" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-900/70">
                            <div class="spinner size-6 animate-spin rounded-full border-[3px] border-white border-r-transparent"></div>
                        </div>
                    </div>
                    @error('photo') <span class="mt-2 text-tiny-plus text-error">{{ $message }}</span> @enderror

                    <div class="mt-4">
                        <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50">{{ auth()->user()->name }}</h2>
                        <p class="text-sm text-slate-400 dark:text-navy-300">{{ auth()->user()->email }}</p>
                        <p class="mt-2 text-tiny-plus font-semibold uppercase tracking-wide text-slate-400 dark:text-navy-300">Bergabung sejak {{ auth()->user()->created_at->format('d M Y') }}</p>
                    </div>
                </div>

                <div class="my-5 h-px bg-slate-200 dark:bg-navy-500"></div>

                <div>
                    <h3 class="font-medium tracking-wide text-slate-700 dark:text-navy-100 mb-4">Statistik Penggunaan</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 p-3 dark:border-navy-500 dark:bg-navy-600">
                            <span class="text-sm text-slate-500 dark:text-navy-200">Total Aktivitas</span>
                            <span class="text-lg font-semibold text-slate-800 dark:text-navy-100">{{ auth()->user()->activities()->count() }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 p-3 dark:border-navy-500 dark:bg-navy-600">
                            <span class="text-sm text-slate-500 dark:text-navy-200">Aktivitas Selesai</span>
                            <span class="text-lg font-semibold text-slate-800 dark:text-navy-100">{{ auth()->user()->activities()->where('status', 'completed')->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Form Edit --}}
        <div class="col-span-1 lg:col-span-2 space-y-4 sm:space-y-5 lg:space-y-6">
            {{-- Form Informasi Profil --}}
            <div class="card p-4 sm:p-5">
                <div class="mb-5 border-b border-slate-200 pb-5 dark:border-navy-500">
                    <h3 class="text-lg font-medium text-slate-700 dark:text-navy-100">Informasi Profil</h3>
                    <p class="mt-1 text-sm text-slate-400 dark:text-navy-300">Perbarui informasi profil dan alamat email akun Anda.</p>
                </div>

                @if(auth()->user()->pending_email)
                <div class="alert flex items-center justify-between rounded-lg border border-info px-4 py-3 text-info sm:px-5 mb-6"
                    x-data="{ cooldown: 0 }"
                    x-on:cooldown-start.window="cooldown = $event.detail.seconds; let t = setInterval(() => { cooldown--; if (cooldown <= 0) clearInterval(t); }, 1000);">
                    <div class="flex items-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p>Menunggu konfirmasi ke <strong class="font-medium">{{ auth()->user()->pending_email }}</strong></p>
                    </div>
                    <div class="flex space-x-3">
                        <button wire:click="cancelPendingEmail" type="button" class="text-xs underline hover:text-info/80">Batalkan</button>
                        <button wire:click="resendPendingEmail" type="button" :disabled="cooldown > 0" class="text-xs font-semibold underline hover:text-info/80 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="resendPendingEmail" x-show="cooldown === 0">Kirim Ulang</span>
                            <span wire:loading wire:target="resendPendingEmail">Mengirim...</span>
                            <span wire:loading.remove wire:target="resendPendingEmail" x-show="cooldown > 0" x-text="'Terkirim (' + cooldown + 's)'" style="display: none;"></span>
                        </button>
                    </div>
                </div>
                @endif

                <form wire:submit="updateProfile" class="space-y-4">
                    <div>
                        <label class="block">
                            <span class="font-medium text-slate-700 dark:text-navy-100">Nama Lengkap</span>
                            <input wire:model="name" class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="text" />
                        </label>
                        @error('name') <span class="mt-1 text-tiny-plus text-error">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block">
                            <span class="font-medium text-slate-700 dark:text-navy-100">Email</span>
                            <input wire:model="email" class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="email" />
                        </label>
                        @error('email') <span class="mt-1 text-tiny-plus text-error">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block">
                            <span class="font-medium text-slate-700 dark:text-navy-100">Nomor HP <span class="text-slate-400 dark:text-navy-300 font-normal">(Opsional)</span></span>
                            <div class="mt-1.5 flex -space-x-px">
                                <select wire:model="country_code" class="form-select w-24 rounded-l-lg border border-slate-300 bg-slate-50 px-3 py-2 hover:z-10 hover:border-slate-400 focus:z-10 focus:border-primary dark:border-navy-450 dark:bg-navy-700 dark:hover:border-navy-400 dark:focus:border-accent">
                                    <option value="+62">🇮🇩 +62</option>
                                    <option value="+1">🇺🇸 +1</option>
                                    <option value="+44">🇬🇧 +44</option>
                                    <option value="+60">🇲🇾 +60</option>
                                    <option value="+65">🇸🇬 +65</option>
                                    <option value="+61">🇦🇺 +61</option>
                                </select>
                                <input wire:model="phone" oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^0+/, '')" class="form-input w-full rounded-r-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:z-10 hover:border-slate-400 focus:z-10 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" placeholder="81234567890" type="text" />
                            </div>
                        </label>
                        @error('phone') <span class="mt-1 text-tiny-plus text-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="btn bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                            <span wire:loading.remove wire:target="updateProfile">Simpan Perubahan</span>
                            <span wire:loading wire:target="updateProfile">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Form Ubah Password --}}
            <div class="card p-4 sm:p-5">
                <div class="mb-5 border-b border-slate-200 pb-5 dark:border-navy-500">
                    <h3 class="text-lg font-medium text-slate-700 dark:text-navy-100">Ubah Password</h3>
                    <p class="mt-1 text-sm text-slate-400 dark:text-navy-300">Pastikan akun Anda menggunakan password yang panjang dan acak agar tetap aman.</p>
                </div>
                <form wire:submit="updatePassword" class="space-y-4">
                    <div>
                        <label class="block">
                            <span class="font-medium text-slate-700 dark:text-navy-100">Password Saat Ini</span>
                            <input wire:model="current_password" class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="password" />
                        </label>
                        @error('current_password') <span class="mt-1 text-tiny-plus text-error">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block">
                            <span class="font-medium text-slate-700 dark:text-navy-100">Password Baru</span>
                            <input wire:model="password" class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="password" />
                        </label>
                        @error('password') <span class="mt-1 text-tiny-plus text-error">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block">
                            <span class="font-medium text-slate-700 dark:text-navy-100">Konfirmasi Password Baru</span>
                            <input wire:model="password_confirmation" class="form-input mt-1.5 w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="password" />
                        </label>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="btn bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                            <span wire:loading.remove wire:target="updatePassword">Ubah Password</span>
                            <span wire:loading wire:target="updatePassword">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
