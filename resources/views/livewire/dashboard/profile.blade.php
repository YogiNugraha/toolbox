<div>
    @section('page_title', 'Profil Saya')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom Kiri: Info Profil & Statistik -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-sm border border-hairline p-6">
                <div class="flex flex-col items-center text-center">
                    <div class="relative w-24 h-24 rounded-full bg-amber/15 flex items-center justify-center text-amber font-mono font-bold text-4xl border border-amber/40 mb-4 group overflow-hidden">
                        @if(auth()->user()->profile_photo_path)
                            <img src="{{ Storage::url(auth()->user()->profile_photo_path) }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                        @else
                            {{ substr(auth()->user()->name, 0, 1) }}
                        @endif
                        
                        <!-- Hover Overlay -->
                        <label class="absolute inset-0 bg-ink/50 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                            <x-lucide-pencil class="w-6 h-6 text-white mb-1" />
                            <span class="text-[10px] text-white font-medium uppercase tracking-wider">Ubah</span>
                            <input type="file" wire:model.live="photo" class="hidden" accept="image/*">
                        </label>

                        <!-- Loading Indicator -->
                        <div wire:loading wire:target="photo" class="absolute inset-0 bg-ink/70 flex flex-col items-center justify-center">
                            <svg class="animate-spin h-6 w-6 text-white mb-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>
                    @error('photo') <span class="text-red-500 text-xs mb-3 block">{{ $message }}</span> @enderror
                    
                    <h2 class="text-xl font-display font-bold text-ink">{{ auth()->user()->name }}</h2>
                    <p class="text-ink-muted text-sm">{{ auth()->user()->email }}</p>
                    <p class="text-[11px] text-ink-muted mt-2 font-mono uppercase tracking-wider">Bergabung sejak {{ auth()->user()->created_at->format('d M Y') }}</p>
                </div>

                <div class="border-t border-hairline mt-6 pt-6">
                    <h3 class="text-sm font-medium text-ink mb-4">Statistik Penggunaan</h3>
                    <div class="space-y-3">
                        <div class="bg-paper p-3 rounded-sm border border-hairline flex justify-between items-center">
                            <span class="text-sm text-ink-muted">Total Aktivitas</span>
                            <span class="text-lg font-mono font-bold text-ink">{{ auth()->user()->activities()->count() }}</span>
                        </div>
                        <div class="bg-paper p-3 rounded-sm border border-hairline flex justify-between items-center">
                            <span class="text-sm text-ink-muted">Aktivitas Selesai</span>
                            <span class="text-lg font-mono font-bold text-ink">{{ auth()->user()->activities()->where('status', 'completed')->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Form Edit -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Form Informasi Profil -->
            <div class="bg-white rounded-sm border border-hairline">
                <div class="p-6 border-b border-hairline">
                    <h3 class="text-lg font-display font-bold text-ink">Informasi Profil</h3>
                    <p class="text-sm text-ink-muted">Perbarui informasi profil dan alamat email akun Anda.</p>
                </div>
                <div class="p-6">
                    @if (session()->has('profile_message'))
                        <div class="mb-4 p-3 bg-green-50 text-green-700 rounded-sm border border-green-200 text-sm">
                            {{ session('profile_message') }}
                        </div>
                    @endif

                    <form wire:submit="updateProfile" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-ink mb-1">Nama Lengkap</label>
                            <input type="text" wire:model="name" class="w-full rounded-sm border-hairline shadow-sm focus:border-amber focus:ring-amber/20 text-sm">
                            @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink mb-1">Email</label>
                            <input type="email" wire:model="email" class="w-full rounded-sm border-hairline shadow-sm focus:border-amber focus:ring-amber/20 text-sm">
                            @error('email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink mb-1">Nomor HP <span class="text-ink-muted font-normal">(Opsional)</span></label>
                            <div class="flex shadow-sm rounded-sm">
                                <select wire:model="country_code" class="w-1/3 md:w-1/4 rounded-l-sm border-hairline focus:border-amber focus:ring-amber/20 text-sm bg-paper/50">
                                    <option value="+62">🇮🇩 +62</option>
                                    <option value="+1">🇺🇸 +1</option>
                                    <option value="+44">🇬🇧 +44</option>
                                    <option value="+60">🇲🇾 +60</option>
                                    <option value="+65">🇸🇬 +65</option>
                                    <option value="+61">🇦🇺 +61</option>
                                </select>
                                <input type="text" wire:model="phone" oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^0+/, '')" class="w-2/3 md:w-3/4 rounded-r-sm border-l-0 border-hairline focus:border-amber focus:ring-amber/20 text-sm" placeholder="81234567890">
                            </div>
                            @error('phone') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div class="pt-2 flex justify-end">
                            <button type="submit" class="bg-amber hover:bg-amber/90 text-ink font-medium py-2 px-6 rounded-sm transition-colors text-sm flex items-center gap-2">
                                <span wire:loading.remove wire:target="updateProfile">Simpan Perubahan</span>
                                <span wire:loading wire:target="updateProfile">Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Form Ubah Password -->
            <div class="bg-white rounded-sm border border-hairline">
                <div class="p-6 border-b border-hairline">
                    <h3 class="text-lg font-display font-bold text-ink">Ubah Password</h3>
                    <p class="text-sm text-ink-muted">Pastikan akun Anda menggunakan password yang panjang dan acak agar tetap aman.</p>
                </div>
                <div class="p-6">
                    @if (session()->has('password_message'))
                        <div class="mb-4 p-3 bg-green-50 text-green-700 rounded-sm border border-green-200 text-sm">
                            {{ session('password_message') }}
                        </div>
                    @endif

                    <form wire:submit="updatePassword" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-ink mb-1">Password Saat Ini</label>
                            <input type="password" wire:model="current_password" class="w-full rounded-sm border-hairline shadow-sm focus:border-amber focus:ring-amber/20 text-sm">
                            @error('current_password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink mb-1">Password Baru</label>
                            <input type="password" wire:model="password" class="w-full rounded-sm border-hairline shadow-sm focus:border-amber focus:ring-amber/20 text-sm">
                            @error('password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-ink mb-1">Konfirmasi Password Baru</label>
                            <input type="password" wire:model="password_confirmation" class="w-full rounded-sm border-hairline shadow-sm focus:border-amber focus:ring-amber/20 text-sm">
                        </div>
                        <div class="pt-2 flex justify-end">
                            <button type="submit" class="bg-amber hover:bg-amber/90 text-ink font-medium py-2 px-6 rounded-sm transition-colors text-sm flex items-center gap-2">
                                <span wire:loading.remove wire:target="updatePassword">Ubah Password</span>
                                <span wire:loading wire:target="updatePassword">Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
