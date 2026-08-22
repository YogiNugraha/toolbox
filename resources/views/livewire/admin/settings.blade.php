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
                Konfigurasi identitas branding, kontak, logo, banner pengumuman, dan integrasi sistem.
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
                
                {{-- Card 1: Identitas & Branding Platform --}}
                <div class="card p-4 sm:p-5">
                    <div class="flex items-center space-x-3 border-b border-slate-150 pb-3 dark:border-navy-600 mb-4">
                        <div class="mask is-squircle flex size-9 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold tracking-wide text-slate-700 dark:text-navy-100 uppercase">
                                Identitas & Branding Website
                            </h3>
                            <p class="text-[11px] text-slate-400 dark:text-navy-300">Nama brand, slogan, dan logo resmi website.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 text-xs">
                        {{-- Nama Site --}}
                        <div>
                            <label class="block">
                                <span class="font-semibold text-slate-700 dark:text-navy-100 mb-1 block">Nama Website (Site Name) <span class="text-error">*</span></span>
                                <div class="relative flex items-center">
                                    <input wire:model.live="site_name" type="text" placeholder="Mudah Kerja" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 text-xs placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                                    <span class="pointer-events-none absolute left-3 flex items-center justify-center text-slate-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    </span>
                                </div>
                            </label>
                            @error('site_name') <span class="text-[11px] text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Tagline --}}
                        <div>
                            <label class="block">
                                <span class="font-semibold text-slate-700 dark:text-navy-100 mb-1 block">Tagline / Slogan Singkat</span>
                                <input wire:model.live="site_tagline" type="text" placeholder="Platform Konversi & Optimasi Dokumen Digital" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-xs placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                            </label>
                            @error('site_tagline') <span class="text-[11px] text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Deskripsi Website --}}
                        <div class="sm:col-span-2">
                            <label class="block">
                                <span class="font-semibold text-slate-700 dark:text-navy-100 mb-1 block">Deskripsi Website (Meta Description & Footer)</span>
                                <textarea wire:model="site_description" rows="3" placeholder="Deskripsi ringkas mengenai layanan dan fungsi platform..." class="form-textarea w-full rounded-lg border border-slate-300 bg-transparent p-3 text-xs placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"></textarea>
                            </label>
                            @error('site_description') <span class="text-[11px] text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Upload Logo Website --}}
                        <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 dark:border-navy-600 dark:bg-navy-700/40">
                            <span class="font-bold text-slate-700 dark:text-navy-100 block mb-2">Logo Website</span>
                            <div class="flex items-center space-x-4">
                                {{-- Logo Preview --}}
                                <div class="flex size-16 shrink-0 items-center justify-center rounded-xl bg-white dark:bg-navy-800 border border-slate-200 dark:border-navy-600 overflow-hidden shadow-xs">
                                    @if ($logo)
                                        <img src="{{ $logo->temporaryUrl() }}" class="size-full object-contain p-1" alt="Preview Logo" />
                                    @elseif ($existing_logo)
                                        <img src="{{ Storage::url($existing_logo) }}" class="size-full object-contain p-1" alt="Logo Website" />
                                    @else
                                        <div class="flex size-full items-center justify-center bg-slate-100 text-slate-400 dark:bg-navy-700 dark:text-navy-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <div class="space-y-2">
                                    <label class="btn h-8 rounded-full bg-primary px-3.5 text-xs font-semibold text-white shadow-xs hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus cursor-pointer inline-flex items-center space-x-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>Unggah Logo</span>
                                        <input wire:model="logo" type="file" accept="image/*" class="hidden" />
                                    </label>
                                    
                                    @if ($logo || $existing_logo)
                                        <button type="button" wire:click="removeLogo" class="btn h-8 rounded-full border border-error/30 text-error hover:bg-error/10 px-3 text-[11px] font-semibold block">
                                            Hapus Logo
                                        </button>
                                    @endif

                                    <p class="text-[10px] text-slate-400 dark:text-navy-300">Format: PNG, SVG, JPG. Maksimal 2MB.</p>
                                </div>
                            </div>
                            @error('logo') <span class="text-[11px] text-error mt-2 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Upload Favicon Website --}}
                        <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 dark:border-navy-600 dark:bg-navy-700/40">
                            <span class="font-bold text-slate-700 dark:text-navy-100 block mb-2">Favicon Website (Browser Tab)</span>
                            <div class="flex items-center space-x-4">
                                {{-- Favicon Preview --}}
                                <div class="flex size-12 shrink-0 items-center justify-center rounded-lg bg-white dark:bg-navy-800 border border-slate-200 dark:border-navy-600 overflow-hidden shadow-xs">
                                    @if ($favicon)
                                        <img src="{{ $favicon->temporaryUrl() }}" class="size-8 object-contain" alt="Preview Favicon" />
                                    @elseif ($existing_favicon)
                                        <img src="{{ Storage::url($existing_favicon) }}" class="size-8 object-contain" alt="Favicon" />
                                    @else
                                        <div class="flex size-full items-center justify-center bg-slate-100 text-slate-400 dark:bg-navy-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <div class="space-y-2">
                                    <label class="btn h-8 rounded-full bg-slate-200 text-slate-700 hover:bg-slate-300 dark:bg-navy-500 dark:text-navy-100 dark:hover:bg-navy-450 px-3.5 text-xs font-semibold cursor-pointer inline-flex items-center space-x-1.5 shadow-xs">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                        </svg>
                                        <span>Unggah Favicon</span>
                                        <input wire:model="favicon" type="file" accept=".ico,.png,.jpg,.svg" class="hidden" />
                                    </label>

                                    @if ($favicon || $existing_favicon)
                                        <button type="button" wire:click="removeFavicon" class="btn h-8 rounded-full border border-error/30 text-error hover:bg-error/10 px-3 text-[11px] font-semibold block">
                                            Hapus Favicon
                                        </button>
                                    @endif

                                    <p class="text-[10px] text-slate-400 dark:text-navy-300">Format: ICO, PNG (32x32 atau 64x64). Maks 1MB.</p>
                                </div>
                            </div>
                            @error('favicon') <span class="text-[11px] text-error mt-2 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                {{-- Card 2: Kontak & Bantuan Layanan --}}
                <div class="card p-4 sm:p-5">
                    <div class="flex items-center space-x-3 border-b border-slate-150 pb-3 dark:border-navy-600 mb-4">
                        <div class="mask is-squircle flex size-9 items-center justify-center bg-info/10 text-info">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold tracking-wide text-slate-700 dark:text-navy-100 uppercase">
                                Kontak Layanan & Footer
                            </h3>
                            <p class="text-[11px] text-slate-400 dark:text-navy-300">Email support, WhatsApp helpdesk, dan teks hak cipta footer.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 text-xs">
                        {{-- Email Support --}}
                        <div>
                            <label class="block">
                                <span class="font-semibold text-slate-700 dark:text-navy-100 mb-1 block">Email Dukungan Pelanggan</span>
                                <div class="relative flex items-center">
                                    <input wire:model="support_email" type="email" placeholder="support@mudahkerja.com" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 text-xs placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                                    <span class="pointer-events-none absolute left-3 flex items-center justify-center text-slate-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                        </svg>
                                    </span>
                                </div>
                            </label>
                            @error('support_email') <span class="text-[11px] text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- WhatsApp CS --}}
                        <div>
                            <label class="block">
                                <span class="font-semibold text-slate-700 dark:text-navy-100 mb-1 block">Nomor WhatsApp / CS</span>
                                <div class="relative flex items-center">
                                    <input wire:model="support_whatsapp" type="text" placeholder="+6281234567890" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 pl-9 text-xs placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                                    <span class="pointer-events-none absolute left-3 flex items-center justify-center text-slate-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                    </span>
                                </div>
                            </label>
                            @error('support_whatsapp') <span class="text-[11px] text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Teks Footer Copyright --}}
                        <div class="sm:col-span-2">
                            <label class="block">
                                <span class="font-semibold text-slate-700 dark:text-navy-100 mb-1 block">Teks Hak Cipta Footer (Copyright Notice)</span>
                                <input wire:model="footer_copyright" type="text" placeholder="© 2026 Mudah Kerja. All rights reserved." class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-xs placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                            </label>
                            @error('footer_copyright') <span class="text-[11px] text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                {{-- Card 3: Banner Pengumuman Global (Announcement Bar) --}}
                <div class="card p-4 sm:p-5">
                    <div class="flex items-center justify-between border-b border-slate-150 pb-3 dark:border-navy-600 mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="mask is-squircle flex size-9 items-center justify-center bg-warning/10 text-warning">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold tracking-wide text-slate-700 dark:text-navy-100 uppercase">
                                    Banner Pengumuman Global
                                </h3>
                                <p class="text-[11px] text-slate-400 dark:text-navy-300">Tampilkan bar notifikasi di bagian paling atas halaman website.</p>
                            </div>
                        </div>

                        {{-- Toggle Switch --}}
                        <label class="inline-flex items-center space-x-2 cursor-pointer">
                            <input wire:model.live="announcement_enabled" type="checkbox" class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent" />
                            <span class="text-xs font-bold {{ $announcement_enabled ? 'text-primary dark:text-accent-light' : 'text-slate-400' }}">
                                {{ $announcement_enabled ? 'AKTIF' : 'NONAKTIF' }}
                            </span>
                        </label>
                    </div>

                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block">
                                <span class="font-semibold text-slate-700 dark:text-navy-100 mb-1 block">Teks Pengumuman</span>
                                <input wire:model.live="announcement_text" type="text" placeholder="Dapatkan Diskon 20% Paket Pro untuk kuota tanpa batas!" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 text-xs placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                            </label>
                            @error('announcement_text') <span class="text-[11px] text-error mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <span class="font-semibold text-slate-700 dark:text-navy-100 mb-2 block">Tipe Warna Banner</span>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                <label class="flex items-center space-x-2 p-2 rounded-lg border cursor-pointer {{ $announcement_type === 'primary' ? 'border-primary bg-primary/10 text-primary dark:border-accent' : 'border-slate-200 dark:border-navy-600' }}">
                                    <input type="radio" wire:model.live="announcement_type" value="primary" class="form-radio size-4 text-primary dark:text-accent" />
                                    <span class="font-semibold">Primary (Ungu/Biru)</span>
                                </label>
                                <label class="flex items-center space-x-2 p-2 rounded-lg border cursor-pointer {{ $announcement_type === 'info' ? 'border-info bg-info/10 text-info' : 'border-slate-200 dark:border-navy-600' }}">
                                    <input type="radio" wire:model.live="announcement_type" value="info" class="form-radio size-4 text-info" />
                                    <span class="font-semibold">Info (Biru)</span>
                                </label>
                                <label class="flex items-center space-x-2 p-2 rounded-lg border cursor-pointer {{ $announcement_type === 'warning' ? 'border-warning bg-warning/10 text-warning' : 'border-slate-200 dark:border-navy-600' }}">
                                    <input type="radio" wire:model.live="announcement_type" value="warning" class="form-radio size-4 text-warning" />
                                    <span class="font-semibold">Warning (Kuning)</span>
                                </label>
                                <label class="flex items-center space-x-2 p-2 rounded-lg border cursor-pointer {{ $announcement_type === 'success' ? 'border-success bg-success/10 text-success' : 'border-slate-200 dark:border-navy-600' }}">
                                    <input type="radio" wire:model.live="announcement_type" value="success" class="form-radio size-4 text-success" />
                                    <span class="font-semibold">Success (Hijau)</span>
                                </label>
                            </div>
                        </div>

                        {{-- Live Banner Preview --}}
                        @if($announcement_enabled)
                            <div class="mt-3 p-3 rounded-lg border text-center text-xs font-semibold {{ $announcement_type === 'primary' ? 'bg-primary text-white dark:bg-accent' : ($announcement_type === 'info' ? 'bg-info text-white' : ($announcement_type === 'warning' ? 'bg-warning text-slate-900' : 'bg-success text-white')) }}">
                                <span>{{ $announcement_text ?: 'Teks pengumuman akan tampil di sini' }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Card 4: Status Integrasi Midtrans Payment Gateway --}}
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
                            <p class="text-slate-400 dark:text-navy-300 text-[11px]">Dikonfigurasi melalui file <code class="bg-slate-200 px-1 py-0.5 rounded dark:bg-navy-600">.env</code></p>
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

                {{-- Card 5: Keamanan Sesi & Pemrosesan Latar Belakang --}}
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
                            <p class="text-[11px] text-slate-400 dark:text-navy-300">Proteksi akun multi-device dan status queue worker.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 text-xs">
                        <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-3.5 dark:border-navy-600 dark:bg-navy-700/40 space-y-1.5">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-slate-700 dark:text-navy-100">Single Session Login</span>
                                <span class="badge rounded-full bg-success/10 text-success text-[10px] font-bold px-2 py-0.5">AKTIF</span>
                            </div>
                            <p class="text-slate-400 dark:text-navy-300 text-[11px]">
                                Sesi lama otomatis logout jika akun login di perangkat baru.
                            </p>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-3.5 dark:border-navy-600 dark:bg-navy-700/40 space-y-1.5">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-slate-700 dark:text-navy-100">Background Queue Worker</span>
                                <span class="badge rounded-full bg-info/10 text-info text-[10px] font-bold px-2 py-0.5">RUNNING</span>
                            </div>
                            <p class="text-slate-400 dark:text-navy-300 text-[11px]">
                                Konversi file dan pengiriman email faktur berjalan di latar belakang.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Actions & System Information (4 Columns) --}}
            <div class="col-span-12 lg:col-span-4 space-y-4 sm:space-y-5 lg:space-y-6">
                
                {{-- Quick Save Box --}}
                <div class="card p-4 sm:p-5 sticky top-24 z-10">
                    <h3 class="text-sm font-bold tracking-wide text-slate-700 dark:text-navy-100 uppercase mb-2">
                        Simpan Pengaturan
                    </h3>
                    <p class="text-xs text-slate-400 dark:text-navy-300 mb-4">
                        Perubahan akan langsung diterapkan secara global ke seluruh halaman website.
                    </p>
                    <button type="submit" class="btn rounded-full w-full bg-primary font-bold text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus text-xs py-2.5 shadow-md shadow-primary/30">
                        <span wire:loading.remove wire:target="save">Simpan Perubahan</span>
                        <div wire:loading wire:target="save" class="flex items-center justify-center space-x-2">
                            <div class="spinner size-4 animate-spin rounded-full border-2 border-current border-r-transparent"></div>
                            <span>Menyimpan...</span>
                        </div>
                    </button>
                </div>

                {{-- Live Branding Preview Card --}}
                <div class="card p-4 sm:p-5">
                    <h3 class="text-sm font-bold tracking-wide text-slate-700 dark:text-navy-100 uppercase mb-3">
                        Pratinjau Branding
                    </h3>
                    <div class="rounded-xl border border-slate-150 bg-slate-50/50 p-3.5 dark:border-navy-600 dark:bg-navy-800/60 space-y-3">
                        <div class="flex items-center space-x-3">
                            @if ($logo)
                                <div class="flex size-10 shrink-0 items-center justify-center overflow-hidden">
                                    <img src="{{ $logo->temporaryUrl() }}" class="size-full object-contain" alt="Logo" />
                                </div>
                            @elseif ($existing_logo)
                                <div class="flex size-10 shrink-0 items-center justify-center overflow-hidden">
                                    <img src="{{ Storage::url($existing_logo) }}" class="size-full object-contain" alt="Logo" />
                                </div>
                            @else
                                <div class="mask is-squircle flex size-10 shrink-0 items-center justify-center bg-primary text-white dark:bg-accent shadow-xs">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                            @endif
                            <div>
                                <h4 class="text-sm font-extrabold text-slate-800 dark:text-navy-50 uppercase leading-none">
                                    {{ $site_name ?: config('app.name') }}
                                </h4>
                                <p class="text-[10px] text-slate-400 dark:text-navy-300 font-semibold tracking-wider uppercase mt-0.5">
                                    {{ $site_tagline ?: 'Online Web Tools' }}
                                </p>
                            </div>
                        </div>

                        <p class="text-[11px] text-slate-500 dark:text-navy-300 line-clamp-2 leading-relaxed">
                            {{ $site_description ?: 'Deskripsi platform akan muncul di sini.' }}
                        </p>
                    </div>
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
