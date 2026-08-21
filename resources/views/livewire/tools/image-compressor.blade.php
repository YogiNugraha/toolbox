<div>
    @section('page_description', 'Kurangi ukuran file gambar Anda tanpa mengurangi kualitas visual secara signifikan.')

    {{-- Error Flash Alert --}}
    @if ($errorMsg)
        <div class="alert flex rounded-lg border border-error px-4 py-4 text-error sm:px-5 mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0 mr-2.5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span>{{ $errorMsg }}</span>
        </div>
    @endif
    @error('file')
        <div class="alert flex rounded-lg border border-error px-4 py-4 text-error sm:px-5 mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0 mr-2.5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span>{{ $message }}</span>
        </div>
    @enderror

    <div class="grid grid-cols-1 gap-4 sm:gap-5 lg:grid-cols-12 lg:gap-6">
        {{-- Left: Upload Area & Compression Options (7 Columns) --}}
        <div class="col-span-12 lg:col-span-7 space-y-4 sm:space-y-5 lg:space-y-6">
            
            {{-- Quota Notification Pill --}}
            <div class="flex items-center justify-between">
                @if($remainingQuota !== null)
                    <div class="badge rounded-full space-x-2 bg-slate-150 px-3 py-1 text-slate-800 dark:bg-navy-600 dark:text-navy-100 text-xs font-semibold">
                        <span>Sisa Kuota Hari Ini:</span>
                        <span class="text-primary dark:text-accent-light">{{ $remainingQuota }} / {{ $dailyLimit }}</span>
                    </div>
                @else
                    <div class="badge rounded-full space-x-2 bg-success/10 px-3 py-1 text-success text-xs font-semibold">
                        <span>Kuota:</span>
                        <span>Unlimited (Pro)</span>
                    </div>
                @endif

                @if($file)
                    <button wire:click="resetResult; $set('file', null)" class="text-xs text-error hover:underline flex items-center space-x-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span>Ganti Gambar</span>
                    </button>
                @endif
            </div>

            @if($remainingQuota !== null && $remainingQuota <= 0)
                <div class="card p-6 text-center">
                    <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-warning/10 text-warning">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-slate-700 dark:text-navy-100">Kuota Harian Anda Telah Habis</h3>
                    <p class="mt-1 text-xs text-slate-400 dark:text-navy-300 max-w-sm mx-auto">Tingkatkan akun Anda ke Pro untuk menikmati pemrosesan file tanpa batas kuota harian.</p>
                    <a href="{{ route('pricing') }}" class="btn rounded-full mt-5 bg-warning font-medium text-white hover:bg-warning-focus shadow-sm text-xs px-6 py-2">
                        Upgrade ke Pro Sekarang
                    </a>
                </div>
            @else
                {{-- Upload Drag & Drop Area --}}
                <div class="card p-4 sm:p-5">
                    <label x-data="{ isDropping: false }" 
                           x-on:dragover.prevent="isDropping = true"
                           x-on:dragleave.prevent="isDropping = false"
                           x-on:drop.prevent="isDropping = false; if($event.dataTransfer.files.length > 0) { $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change', { bubbles: true })) }"
                           :class="isDropping ? 'border-primary/50 bg-primary/5 dark:border-accent-light/50 dark:bg-accent-light/5' : '{{ $file ? 'border-primary/50 dark:border-accent-light/50 bg-slate-50/50 dark:bg-navy-700/30' : 'border-slate-300 dark:border-navy-450 hover:bg-slate-50 dark:hover:bg-navy-900/50' }}'"
                           class="flex flex-col items-center justify-center w-full rounded-xl border-2 border-dashed p-8 cursor-pointer transition-all text-center">
                        
                        @if ($file)
                            <div class="mask is-squircle flex size-14 items-center justify-center bg-success/10 text-success mb-3">
                                <svg class="size-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-slate-700 dark:text-navy-100 truncate px-4 max-w-md">
                                {{ $file->getClientOriginalName() }}
                            </p>
                            <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">
                                Ukuran: <span class="font-semibold text-slate-600 dark:text-navy-200">{{ number_format($file->getSize() / 1024, 2) }} KB</span>
                            </p>
                            <span class="mt-3 badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light text-[11px] font-semibold px-3 py-0.5">
                                Siap Dikonfigurasi
                            </span>
                        @else
                            <div class="mask is-squircle flex size-14 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light mb-3">
                                <svg class="size-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-slate-700 dark:text-navy-100">
                                Klik untuk unggah gambar <span class="font-normal text-slate-400 dark:text-navy-300">atau tarik file ke sini</span>
                            </p>
                            <p class="mt-1 text-xs text-slate-400 dark:text-navy-300 uppercase tracking-wider">
                                JPG · JPEG · PNG · WEBP (Maksimal 20MB)
                            </p>
                        @endif
                        
                        <input type="file" x-ref="fileInput" wire:model="file" class="hidden" accept="image/jpeg, image/png, image/webp" />
                    </label>

                    <div wire:loading wire:target="file" class="mt-3 flex items-center justify-center space-x-2 text-xs font-semibold text-primary dark:text-accent-light">
                        <div class="spinner size-4 animate-spin rounded-full border-2 border-current border-r-transparent"></div>
                        <span>Mengunggah file...</span>
                    </div>
                </div>
            @endif

            {{-- Presets & Optimization Settings (Lineone Segmented Tabs) --}}
            @if ($file)
                <div class="card p-4 sm:p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-700 dark:text-navy-100 uppercase tracking-wide">
                            Pilih Kualitas (Preset)
                        </h3>
                        <span class="text-xs text-slate-400 dark:text-navy-300">Preset Kualitas</span>
                    </div>

                    {{-- Lineone Segmented Control Tabs --}}
                    <div class="is-scrollbar-hidden flex space-x-1.5 rounded-xl bg-slate-150 p-1.5 dark:bg-navy-800">
                        <button type="button" 
                                wire:click="$set('preset', 'sosmed')" 
                                class="flex-1 rounded-lg py-2 text-xs font-semibold transition-all {{ $preset === 'sosmed' ? 'bg-white text-primary shadow-sm dark:bg-navy-600 dark:text-accent-light' : 'text-slate-600 hover:text-slate-900 dark:text-navy-300 dark:hover:text-navy-50' }}">
                            Sosial Media
                        </button>
                        <button type="button" 
                                wire:click="$set('preset', 'website')" 
                                class="flex-1 rounded-lg py-2 text-xs font-semibold transition-all {{ $preset === 'website' ? 'bg-white text-primary shadow-sm dark:bg-navy-600 dark:text-accent-light' : 'text-slate-600 hover:text-slate-900 dark:text-navy-300 dark:hover:text-navy-50' }}">
                            Website / Banner
                        </button>
                        <button type="button" 
                                wire:click="$set('preset', 'custom')" 
                                class="flex-1 rounded-lg py-2 text-xs font-semibold transition-all flex items-center justify-center space-x-1 {{ $preset === 'custom' ? 'bg-white text-primary shadow-sm dark:bg-navy-600 dark:text-accent-light' : 'text-slate-600 hover:text-slate-900 dark:text-navy-300 dark:hover:text-navy-50' }} {{ $isCustomLocked ? 'opacity-60' : '' }}">
                            <span>Kustom</span>
                            @if($isCustomLocked)
                                <span class="badge rounded-full bg-warning/20 text-warning px-1.5 py-0.5 text-[9px] font-bold">PRO</span>
                            @endif
                        </button>
                    </div>

                    {{-- Preset Options Container --}}
                    <div class="rounded-xl border border-slate-150 bg-slate-50/50 p-4 dark:border-navy-600 dark:bg-navy-700/40">
                        @if ($preset === 'sosmed')
                            <div class="space-y-1 text-xs text-slate-600 dark:text-navy-200">
                                <div class="flex items-center space-x-2 font-semibold text-slate-700 dark:text-navy-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-primary dark:text-accent-light" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Preset Standar Sosial Media</span>
                                </div>
                                <p class="text-slate-500 dark:text-navy-300 pl-6">
                                    Maksimal dimensi 1080px, rasio kualitas 80%. Sangat optimal untuk Instagram, Facebook, LinkedIn, dan WhatsApp tanpa pecah.
                                </p>
                            </div>
                        @elseif ($preset === 'website')
                            <div class="space-y-3 text-xs">
                                <div class="space-y-1 text-slate-600 dark:text-navy-200">
                                    <div class="flex items-center space-x-2 font-semibold text-slate-700 dark:text-navy-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Preset Optimal Website / Blog</span>
                                    </div>
                                    <p class="text-slate-500 dark:text-navy-300 pl-6">
                                        Maksimal lebar 1920px, kualitas 75% untuk kecepatan loading halaman maksimal (SEO friendly).
                                    </p>
                                </div>
                                <div class="pt-2 border-t border-slate-200 dark:border-navy-600 pl-6">
                                    <label class="inline-flex items-center space-x-2 cursor-pointer">
                                        <input wire:model="websiteConvertToWebp" class="form-checkbox is-outline size-4.5 rounded border-slate-400/70 before:bg-primary checked:border-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:before:bg-accent dark:checked:border-accent" type="checkbox" />
                                        <span class="font-medium text-slate-700 dark:text-navy-100">Konversi ke format WebP generasi baru (Super Ringan)</span>
                                    </label>
                                </div>
                            </div>
                        @elseif ($preset === 'custom')
                            @if($isCustomLocked)
                                <div class="text-center py-3">
                                    <p class="text-xs font-semibold text-slate-700 dark:text-navy-100 mb-2">Fitur Kustom (Resolusi Manual & Konversi Format) khusus pengguna Pro.</p>
                                    <a href="{{ route('pricing') }}" class="btn rounded-full bg-warning font-semibold text-white hover:bg-warning-focus text-xs px-4 py-1.5 shadow-sm">
                                        Upgrade ke Pro
                                    </a>
                                </div>
                            @else
                                <div class="space-y-4">
                                    {{-- Quality Slider --}}
                                    <div>
                                        <div class="flex justify-between items-center mb-1.5">
                                            <label class="font-semibold text-slate-700 dark:text-navy-100 text-xs">Tingkat Kualitas Kompresi</label>
                                            <span class="badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light text-xs font-bold px-2 py-0.5">{{ $customQuality }}%</span>
                                        </div>
                                        <input type="range" wire:model.live="customQuality" min="1" max="100" class="form-range text-primary dark:text-accent w-full cursor-pointer" />
                                    </div>

                                    {{-- Resize Options --}}
                                    <div class="pt-2 border-t border-slate-200 dark:border-navy-600">
                                        <label class="inline-flex items-center space-x-2 cursor-pointer mb-2.5">
                                            <input wire:model.live="customResize" class="form-checkbox is-outline size-4.5 rounded border-slate-400/70 before:bg-primary checked:border-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:before:bg-accent dark:checked:border-accent" type="checkbox" />
                                            <span class="font-semibold text-slate-700 dark:text-navy-100 text-xs">Ubah Dimensi / Resolusi Gambar</span>
                                        </label>

                                        @if ($customResize)
                                            <div class="grid grid-cols-2 gap-3 mt-2">
                                                <div>
                                                    <label class="block text-[11px] text-slate-500 dark:text-navy-300 mb-1">Lebar (px)</label>
                                                    <input wire:model="customWidth" type="number" placeholder="Contoh: 1200" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-1.5 text-xs placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                                                </div>
                                                <div>
                                                    <label class="block text-[11px] text-slate-500 dark:text-navy-300 mb-1">Tinggi (px)</label>
                                                    <input wire:model="customHeight" type="number" placeholder="Contoh: 800" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-1.5 text-xs placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Format Dropdown --}}
                                    <div class="pt-2 border-t border-slate-200 dark:border-navy-600">
                                        <label class="block">
                                            <span class="font-semibold text-slate-700 dark:text-navy-100 mb-1.5 block text-xs">Format Output File</span>
                                            <select wire:model="customFormat" class="form-select w-full rounded-lg border border-slate-300 bg-transparent px-3 py-1.5 text-xs hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent">
                                                <option value="original">Pertahankan Format Asli</option>
                                                <option value="jpg">Format JPG</option>
                                                <option value="png">Format PNG</option>
                                                <option value="webp">Format WebP</option>
                                            </select>
                                        </label>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>

                    {{-- Action Button --}}
                    <button wire:click="compress" wire:loading.attr="disabled" class="btn rounded-full w-full bg-primary font-bold text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus text-xs py-2.5 shadow-sm disabled:opacity-60">
                        <span wire:loading.remove wire:target="compress">Mulai Kompres Gambar</span>
                        <div wire:loading wire:target="compress" class="flex items-center justify-center space-x-2">
                            <div class="spinner size-4 animate-spin rounded-full border-2 border-current border-r-transparent"></div>
                            <span>Memproses Gambar...</span>
                        </div>
                    </button>
                </div>
            @endif
        </div>

        {{-- Right: Result & Preview Area (5 Columns) --}}
        <div class="col-span-12 lg:col-span-5">
            @if ($resultPath)
                <div class="card flex h-full flex-col items-center justify-center p-6 text-center shadow-md">
                    <div class="mask is-squircle flex size-16 items-center justify-center bg-success/10 text-success mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <h3 class="text-lg font-bold text-slate-700 dark:text-navy-100">
                        Gambar Berhasil Dikompres!
                    </h3>
                    <p class="text-xs text-slate-400 dark:text-navy-300 mt-0.5">
                        File siap untuk diunduh dan digunakan.
                    </p>

                    @php
                        $percentage = $originalSize > 0 ? round((($originalSize - $newSize) / $originalSize) * 100) : 0;
                    @endphp

                    {{-- Stats Comparison Box --}}
                    <div class="mt-5 w-full rounded-xl border border-slate-150 bg-slate-50 p-4 dark:border-navy-600 dark:bg-navy-700/60 space-y-2.5 text-xs">
                        <div class="flex items-center justify-between border-b border-slate-200/80 pb-2.5 dark:border-navy-600">
                            <span class="text-slate-500 dark:text-navy-200">Ukuran Asli</span>
                            <span class="font-medium text-slate-400 line-through dark:text-navy-300">
                                {{ number_format($originalSize / 1024, 2) }} KB
                            </span>
                        </div>
                        <div class="flex items-center justify-between border-b border-slate-200/80 pb-2.5 dark:border-navy-600">
                            <span class="text-slate-500 dark:text-navy-200">Ukuran Baru</span>
                            <span class="font-bold text-slate-800 dark:text-navy-100">
                                {{ number_format($newSize / 1024, 2) }} KB
                            </span>
                        </div>
                        <div class="flex items-center justify-between pt-0.5">
                            <span class="font-semibold text-slate-700 dark:text-navy-100">Hemat Ruang</span>
                            <span class="badge rounded-full bg-success/15 text-success font-bold px-2.5 py-0.5 text-xs">
                                -{{ $percentage }}%
                            </span>
                        </div>
                    </div>

                    {{-- Download Action --}}
                    <div class="mt-6 w-full space-y-2">
                        <button wire:click="download" class="btn rounded-full w-full space-x-2 bg-success font-bold text-white hover:bg-success-focus text-xs py-2.5 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Unduh Gambar (.{{ $resultExtension }})</span>
                        </button>
                        <button wire:click="resetResult; $set('file', null)" class="btn rounded-full w-full border border-slate-300 text-slate-700 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-100 dark:hover:bg-navy-500 text-xs py-2">
                            Kompres Gambar Lainnya
                        </button>
                    </div>
                </div>
            @elseif ($file && !$errors->has('file'))
                <div class="card flex min-h-[350px] h-full flex-col items-center justify-center p-4 relative overflow-hidden group">
                    <img src="{{ $file->temporaryUrl() }}" class="max-h-[420px] max-w-full rounded-lg object-contain shadow-sm" alt="Preview Gambar">
                    <div class="absolute bottom-4 left-0 right-0 flex justify-center">
                        <span class="badge rounded-full bg-slate-800/80 text-white backdrop-blur-xs shadow-md px-3 py-1 text-xs font-semibold">
                            Preview File Siap Kompres
                        </span>
                    </div>
                </div>
            @else
                <div class="card flex min-h-[350px] h-full flex-col items-center justify-center border-2 border-dashed border-slate-200 bg-slate-50/50 dark:border-navy-600 dark:bg-navy-800/50 p-6 text-center">
                    <div class="mask is-squircle mx-auto flex size-16 items-center justify-center bg-slate-200 text-slate-400 dark:bg-navy-600 dark:text-navy-300 mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <p class="font-bold text-slate-500 dark:text-navy-300 text-xs uppercase tracking-wider">Area Pratinjau & Hasil</p>
                    <p class="text-[11px] text-slate-400 dark:text-navy-300 mt-1 max-w-xs">
                        Unggah gambar di panel sebelah kiri untuk melihat pratinjau dan hasil kompresi di sini.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
