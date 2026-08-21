<div>
    @section('title', 'Pengaturan Global - ' . config('app.name'))
    @section('page_title', 'Pengaturan Website')
    @section('page_breadcrumb', 'Pengaturan')

    {{-- Header Toolbar --}}
    <div class="flex items-center justify-between py-4 sm:py-5">
        <div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-navy-50 lg:text-2xl">
                Pengaturan Global Website
            </h2>
            <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">
                Konfigurasi identitas platform, integrasi gateway Midtrans, dan status sistem.
            </p>
        </div>
        <div class="badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light text-xs font-bold px-3 py-1">
            System Config
        </div>
    </div>

    {{-- 12-Column Responsive Grid Layout --}}
    <form wire:submit="save">
        <div class="grid grid-cols-12 gap-4 sm:gap-5 lg:gap-6">
            {{-- Left Column: Main Form & Settings (8 Columns) --}}
            <div class="col-span-12 lg:col-span-8 space-y-4 sm:space-y-5 lg:space-y-6">
                
                {{-- Card 1: Identitas Platform --}}
                <div class="card p-4 sm:p-5">
                    <div class="flex items-center space-x-3 border-b border-slate-150 pb-3 dark:border-navy-600 mb-4">
                        <div class="mask is-squircle flex size-9 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold tracking-wide text-slate-700 dark:text-navy-100 uppercase">
                                Identitas & Branding Platform
                            </h3>
                            <p class="text-[11px] text-slate-400 dark:text-navy-300">Nama brand, slogan, dan kontak resmi website.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 text-xs">
                        <div>
                            <label class="block">
                                <span class="font-semibold text-slate-700 dark:text-navy-100 mb-1 block">Nama Brand / Website</span>
                                <input wire:model="brand_name" type="text" placeholder="Mudah Kerja" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-xs placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                            </label>
                            @error('brand_name') <span class="text-[11px] text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block">
                                <span class="font-semibold text-slate-700 dark:text-navy-100 mb-1 block">Email Dukungan Pelanggan (Support)</span>
                                <input wire:model="support_email" type="email" placeholder="support@mudahkerja.com" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-xs placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                            </label>
                            @error('support_email') <span class="text-[11px] text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block">
                                <span class="font-semibold text-slate-700 dark:text-navy-100 mb-1 block">Slogan / Tagline Website</span>
                                <input wire:model="brand_tagline" type="text" placeholder="Platform Konversi & Optimasi Dokumen Digital" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-xs placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                            </label>
                            @error('brand_tagline') <span class="text-[11px] text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                {{-- Card 2: Status Integrasi Midtrans Payment Gateway --}}
                <div class="card p-4 sm:p-5">
                    <div class="flex items-center space-x-3 border-b border-slate-150 pb-3 dark:border-navy-600 mb-4">
                        <div class="mask is-squircle flex size-9 items-center justify-center bg-info/10 text-info">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold tracking-wide text-slate-700 dark:text-navy-100 uppercase">
                                Integrasi Payment Gateway Midtrans
                            </h3>
                            <p class="text-[11px] text-slate-400 dark:text-navy-300">Status koneksi gateway pembayaran otomatis QRIS & Bank Transfer.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 text-xs">
                        <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-3.5 dark:border-navy-600 dark:bg-navy-700/40 space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-slate-700 dark:text-navy-100">Environment Mode</span>
                                <span class="badge rounded-full {{ config('services.midtrans.is_production') ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning' }} text-[10px] font-bold px-2 py-0.5">
                                    {{ config('services.midtrans.is_production') ? 'PRODUCTION' : 'SANDBOX / TESTING' }}
                                </span>
                            </div>
                            <p class="text-slate-400 dark:text-navy-300 text-[11px]">Dikonfigurasi melalui file <code class="bg-slate-200 px-1 py-0.5 rounded dark:bg-navy-600">.env</code> (MIDTRANS_IS_PRODUCTION)</p>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-3.5 dark:border-navy-600 dark:bg-navy-700/40 space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-slate-700 dark:text-navy-100">Status API Keys</span>
                                <span class="badge rounded-full bg-success/10 text-success text-[10px] font-bold px-2 py-0.5">
                                    {{ config('services.midtrans.server_key') ? 'TERHUBUNG' : 'BELUM DIATUR' }}
                                </span>
                            </div>
                            <p class="text-slate-400 dark:text-navy-300 text-[11px]">Server Key & Client Key aktif di sistem.</p>
                        </div>

                        <div class="sm:col-span-2 rounded-xl border border-slate-200 bg-slate-50/50 p-3.5 dark:border-navy-600 dark:bg-navy-700/40">
                            <span class="font-bold text-slate-700 dark:text-navy-100 block mb-1">Webhook Callback URL</span>
                            <div class="flex items-center space-x-2">
                                <input type="text" readonly value="{{ route('webhook.midtrans') }}" class="form-input w-full rounded-lg border border-slate-300 bg-white dark:bg-navy-800 px-3 py-1.5 text-xs text-slate-600 dark:text-navy-200" />
                                <span class="badge rounded-full bg-primary/10 text-primary text-[11px] font-bold px-2.5 py-1 shrink-0">
                                    POST Endpoint
                                </span>
                            </div>
                            <p class="text-slate-400 dark:text-navy-300 text-[11px] mt-1.5">
                                Salin URL ini ke pengaturan <strong>Payment Notification URL</strong> di dashboard Midtrans Anda.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Card 3: Keamanan Sesi & Antrean Sistem --}}
                <div class="card p-4 sm:p-5">
                    <div class="flex items-center space-x-3 border-b border-slate-150 pb-3 dark:border-navy-600 mb-4">
                        <div class="mask is-squircle flex size-9 items-center justify-center bg-success/10 text-success">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold tracking-wide text-slate-700 dark:text-navy-100 uppercase">
                                Keamanan Sesi & Pemrosesan Latar Belakang
                            </h3>
                            <p class="text-[11px] text-slate-400 dark:text-navy-300">Proteksi akun multi-device dan queue worker pemrosesan PDF.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 text-xs">
                        <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-3.5 dark:border-navy-600 dark:bg-navy-700/40 space-y-1.5">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-slate-700 dark:text-navy-100">Single Session Login</span>
                                <span class="badge rounded-full bg-success/10 text-success text-[10px] font-bold px-2 py-0.5">AKTIF</span>
                            </div>
                            <p class="text-slate-400 dark:text-navy-300 text-[11px]">
                                Sesi lama akan otomatis logout jika pengguna login dari perangkat baru.
                            </p>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-3.5 dark:border-navy-600 dark:bg-navy-700/40 space-y-1.5">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-slate-700 dark:text-navy-100">Background Queue Worker</span>
                                <span class="badge rounded-full bg-info/10 text-info text-[10px] font-bold px-2 py-0.5">RUNNING</span>
                            </div>
                            <p class="text-slate-400 dark:text-navy-300 text-[11px]">
                                Konversi PDF dan email invoice diproses secara otomatis di latar belakang.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Actions & System Information (4 Columns) --}}
            <div class="col-span-12 lg:col-span-4 space-y-4 sm:space-y-5 lg:space-y-6">
                
                {{-- Quick Save Box --}}
                <div class="card p-4 sm:p-5">
                    <h3 class="text-sm font-bold tracking-wide text-slate-700 dark:text-navy-100 uppercase mb-3">
                        Aksi Pengaturan
                    </h3>
                    <p class="text-xs text-slate-400 dark:text-navy-300 mb-4">
                        Pastikan semua perubahan identitas brand dan konfigurasi telah benar sebelum menyimpan.
                    </p>
                    <button type="submit" class="btn rounded-full w-full bg-primary font-bold text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus text-xs py-2.5 shadow-sm">
                        <span wire:loading.remove wire:target="save">Simpan Perubahan</span>
                        <div wire:loading wire:target="save" class="flex items-center justify-center space-x-2">
                            <div class="spinner size-4 animate-spin rounded-full border-2 border-current border-r-transparent"></div>
                            <span>Menyimpan...</span>
                        </div>
                    </button>
                </div>

                {{-- Server & Environment Stats --}}
                <div class="card p-4 sm:p-5">
                    <h3 class="text-sm font-bold tracking-wide text-slate-700 dark:text-navy-100 uppercase mb-3">
                        Informasi Server & Sistem
                    </h3>
                    <div class="space-y-2.5 text-xs">
                        <div class="flex items-center justify-between border-b border-slate-150 pb-2 dark:border-navy-600">
                            <span class="text-slate-500 dark:text-navy-300">PHP Version</span>
                            <span class="font-bold text-slate-700 dark:text-navy-100">{{ PHP_VERSION }}</span>
                        </div>
                        <div class="flex items-center justify-between border-b border-slate-150 pb-2 dark:border-navy-600">
                            <span class="text-slate-500 dark:text-navy-300">Laravel Version</span>
                            <span class="font-bold text-slate-700 dark:text-navy-100">{{ app()->version() }}</span>
                        </div>
                        <div class="flex items-center justify-between border-b border-slate-150 pb-2 dark:border-navy-600">
                            <span class="text-slate-500 dark:text-navy-300">Environment</span>
                            <span class="badge rounded-full bg-slate-150 text-slate-700 dark:bg-navy-600 dark:text-navy-200 text-[10px] font-bold px-2 py-0.5">
                                {{ strtoupper(config('app.env')) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between border-b border-slate-150 pb-2 dark:border-navy-600">
                            <span class="text-slate-500 dark:text-navy-300">Queue Connection</span>
                            <span class="font-bold text-slate-700 dark:text-navy-100 uppercase">{{ config('queue.default') }}</span>
                        </div>
                        <div class="flex items-center justify-between pt-0.5">
                            <span class="text-slate-500 dark:text-navy-300">Timezone</span>
                            <span class="font-bold text-slate-700 dark:text-navy-100">{{ config('app.timezone') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Fast Navigation Links --}}
                <div class="card p-4 sm:p-5">
                    <h3 class="text-sm font-bold tracking-wide text-slate-700 dark:text-navy-100 uppercase mb-3">
                        Pintasan Menu Admin
                    </h3>
                    <div class="space-y-2 text-xs">
                        <a href="{{ route('admin.plans') }}" wire:navigate class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-navy-600 text-slate-700 dark:text-navy-200 transition-colors">
                            <span class="font-semibold">Kelola Paket Langganan</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ route('admin.users') }}" wire:navigate class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-navy-600 text-slate-700 dark:text-navy-200 transition-colors">
                            <span class="font-semibold">Manajemen Pengguna</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ route('admin.transactions') }}" wire:navigate class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-navy-600 text-slate-700 dark:text-navy-200 transition-colors">
                            <span class="font-semibold">Riwayat Transaksi</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
