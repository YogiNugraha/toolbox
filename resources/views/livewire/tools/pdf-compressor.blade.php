<div>
    @section('page_description', 'Perkecil ukuran dokumen PDF secara instan tanpa merusak tata letak teks dan kualitas bacaan.')

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
                        <span>{{ $isPro ? 'Unlimited (Pro)' : 'Tanpa Batas' }}</span>
                    </div>
                @endif

                @if($file)
                    <button wire:click="resetFile" class="text-xs text-error hover:underline flex items-center space-x-1 font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span>Ganti Dokumen PDF</span>
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
                {{-- Upload Drag & Drop Area with Lineone Real-time Progress --}}
                <div x-data="{ isUploading: false, progress: 0 }"
                     x-on:livewire-upload-start="isUploading = true; progress = 0"
                     x-on:livewire-upload-finish="isUploading = false"
                     x-on:livewire-upload-error="isUploading = false"
                     x-on:livewire-upload-progress="progress = $event.detail.progress"
                     class="card p-4 sm:p-5">
                    <label x-data="{ isDropping: false }" 
                           x-on:dragover.prevent="isDropping = true"
                           x-on:dragleave.prevent="isDropping = false"
                           x-on:drop.prevent="isDropping = false; if($event.dataTransfer.files.length > 0) { $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change', { bubbles: true })) }"
                           :class="isDropping ? 'border-primary/50 bg-primary/5 dark:border-accent-light/50 dark:bg-accent-light/5' : '{{ $file ? 'border-primary/50 dark:border-accent-light/50 bg-slate-50/50 dark:bg-navy-700/30' : 'border-slate-300 dark:border-navy-450 hover:bg-slate-50 dark:hover:bg-navy-900/50' }}'"
                           class="flex flex-col items-center justify-center w-full rounded-xl border-2 border-dashed p-8 cursor-pointer transition-all text-center">
                        
                        @if ($file)
                            <div class="mask is-squircle flex size-14 items-center justify-center bg-success/10 text-success mb-3">
                                <svg class="size-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-slate-700 dark:text-navy-100 truncate px-4 max-w-md">
                                {{ $file->getClientOriginalName() }}
                            </p>
                            <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">
                                Ukuran Asli: <span class="font-semibold text-slate-600 dark:text-navy-200">{{ number_format($file->getSize() / 1024, 2) }} KB</span>
                            </p>
                            <span class="mt-3 badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light text-[11px] font-semibold px-3 py-0.5">
                                Siap Dikonfigurasi
                            </span>
                        @else
                            <div class="mask is-squircle flex size-14 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light mb-3">
                                <svg class="size-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-slate-700 dark:text-navy-100">
                                Klik untuk unggah dokumen PDF <span class="font-normal text-slate-400 dark:text-navy-300">atau tarik file ke sini</span>
                            </p>
                            <p class="mt-1 text-xs text-slate-400 dark:text-navy-300 uppercase tracking-wider">
                                File PDF (.pdf) · Maksimal 50MB
                            </p>
                        @endif
                        
                        <input x-ref="fileInput" wire:model="file" type="file" accept="application/pdf,.pdf" class="hidden" />
                    </label>

                    {{-- Lineone Upload Real-time Progress Bar --}}
                    <div x-show="isUploading" x-transition class="w-full mt-3.5 rounded-xl bg-slate-100 p-3.5 dark:bg-navy-700/60 border border-slate-200 dark:border-navy-600 space-y-2">
                        <div class="flex items-center justify-between text-xs font-semibold text-slate-700 dark:text-navy-100">
                            <span class="flex items-center space-x-2">
                                <div class="spinner size-3.5 border-2 border-primary border-r-transparent rounded-full dark:border-accent-light"></div>
                                <span>Mengunggah dokumen PDF ke server...</span>
                            </span>
                            <span class="text-primary dark:text-accent-light font-bold" x-text="progress + '%'"></span>
                        </div>
                        <div class="progress h-2 bg-slate-200 dark:bg-navy-600 rounded-full overflow-hidden">
                            <div class="is-active rounded-full bg-primary dark:bg-accent transition-all duration-150" :style="`width: ${progress}%`"></div>
                        </div>
                    </div>
                </div>

                {{-- Preset Selection Cards --}}
                @if ($file)
                    <div class="card p-4 sm:p-5 space-y-4">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-navy-100 flex items-center justify-between">
                            <span>Tingkat Kompresi Dokumen</span>
                            <span class="text-[11px] font-normal text-slate-400 dark:text-navy-300">Pilih kualitas yang diinginkan</span>
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            {{-- Option 1: Extreme --}}
                            <label class="cursor-pointer">
                                <input type="radio" wire:model.live="preset" value="extreme" class="hidden peer" />
                                <div class="p-3.5 rounded-xl border-2 transition-all peer-checked:border-primary peer-checked:bg-primary/5 dark:peer-checked:border-accent-light dark:peer-checked:bg-accent-light/5 border-slate-200 dark:border-navy-600 hover:border-slate-300 dark:hover:border-navy-500 h-full flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-xs text-slate-800 dark:text-navy-100">Kompresi Ekstrim</span>
                                            <span class="badge rounded-full bg-linear-to-r from-red-500 to-orange-500 text-white text-[9px] font-bold px-1.5 py-0.5">Terkecil</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 dark:text-navy-300 mt-1 leading-snug">
                                            Ukuran file paling kecil, cocok untuk lampiran email atau batas upload ketat.
                                        </p>
                                    </div>
                                    <div class="mt-2 text-[10px] text-slate-400 dark:text-navy-400 font-medium">
                                        Resolusi 72 DPI · Kompresi Maksimal
                                    </div>
                                </div>
                            </label>

                            {{-- Option 2: Recommended --}}
                            <label class="cursor-pointer">
                                <input type="radio" wire:model.live="preset" value="recommended" class="hidden peer" />
                                <div class="p-3.5 rounded-xl border-2 transition-all peer-checked:border-primary peer-checked:bg-primary/5 dark:peer-checked:border-accent-light dark:peer-checked:bg-accent-light/5 border-slate-200 dark:border-navy-600 hover:border-slate-300 dark:hover:border-navy-500 h-full flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-xs text-slate-800 dark:text-navy-100">Direkomendasikan</span>
                                            <span class="badge rounded-full bg-linear-to-r from-emerald-500 to-teal-500 text-white text-[9px] font-bold px-1.5 py-0.5">Seimbang</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 dark:text-navy-300 mt-1 leading-snug">
                                            Keseimbangan optimal antara pengurangan ukuran dan ketajaman teks/gambar.
                                        </p>
                                    </div>
                                    <div class="mt-2 text-[10px] text-slate-400 dark:text-navy-400 font-medium">
                                        Resolusi 150 DPI · Kualitas 75%
                                    </div>
                                </div>
                            </label>

                            {{-- Option 3: Low Compression --}}
                            <label class="cursor-pointer">
                                <input type="radio" wire:model.live="preset" value="low" class="hidden peer" />
                                <div class="p-3.5 rounded-xl border-2 transition-all peer-checked:border-primary peer-checked:bg-primary/5 dark:peer-checked:border-accent-light dark:peer-checked:bg-accent-light/5 border-slate-200 dark:border-navy-600 hover:border-slate-300 dark:hover:border-navy-500 h-full flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-xs text-slate-800 dark:text-navy-100">Kompresi Ringan</span>
                                            <span class="badge rounded-full bg-slate-150 text-slate-700 dark:bg-navy-500 dark:text-navy-200 text-[9px] font-bold px-1.5 py-0.5">Visual Tinggi</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 dark:text-navy-300 mt-1 leading-snug">
                                            Kompresi halus menjaga kualitas gambar tetap sangat tajam untuk dicetak.
                                        </p>
                                    </div>
                                    <div class="mt-2 text-[10px] text-slate-400 dark:text-navy-400 font-medium">
                                        Resolusi 200 DPI · Kualitas 85%
                                    </div>
                                </div>
                            </label>

                            {{-- Option 4: Custom Preset --}}
                            <label class="cursor-pointer">
                                <input type="radio" wire:model.live="preset" value="custom" class="hidden peer" />
                                <div class="p-3.5 rounded-xl border-2 transition-all peer-checked:border-primary peer-checked:bg-primary/5 dark:peer-checked:border-accent-light dark:peer-checked:bg-accent-light/5 border-slate-200 dark:border-navy-600 hover:border-slate-300 dark:hover:border-navy-500 h-full flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-xs text-slate-800 dark:text-navy-100">Kustom Parameter</span>
                                            @if($isCustomLocked && !$isPro)
                                                <span class="badge rounded-full bg-linear-to-r from-amber-500 to-purple-600 text-white text-[9px] font-bold px-1.5 py-0.5 flex items-center gap-0.5">
                                                    <x-lucide-crown class="size-2.5" />
                                                    <span>PRO</span>
                                                </span>
                                            @else
                                                <span class="badge rounded-full bg-slate-150 text-slate-700 dark:bg-navy-500 dark:text-navy-200 text-[9px] font-bold px-1.5 py-0.5">Manual</span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] text-slate-500 dark:text-navy-300 mt-1 leading-snug">
                                            Atur resolusi DPI dan persentase kualitas JPEG secara manual sesuai kebutuhan.
                                        </p>
                                    </div>
                                    <div class="mt-2 text-[10px] text-slate-400 dark:text-navy-400 font-medium">
                                        Slider DPI & Opsi Metadata
                                    </div>
                                </div>
                            </label>
                        </div>

                        {{-- Custom Settings Panel (If preset === 'custom') --}}
                        @if($preset === 'custom')
                            @if($isCustomLocked && !$isPro)
                                <div class="rounded-xl border border-warning/30 bg-warning/5 p-4 text-center">
                                    <x-lucide-lock class="size-6 text-warning mx-auto mb-1.5" />
                                    <h4 class="text-xs font-bold text-slate-800 dark:text-navy-100">Fitur Kustom Khusus Pengguna PRO</h4>
                                    <p class="text-[11px] text-slate-500 dark:text-navy-300 mt-1">Upgrade akun Anda untuk membuka kontrol penuh resolusi DPI dan parameter kompresi.</p>
                                    <a href="{{ route('pricing') }}" class="btn h-7 rounded-full bg-warning text-white font-bold text-[10px] px-4 mt-2.5">
                                        Upgrade ke Pro
                                    </a>
                                </div>
                            @else
                                <div class="p-4 rounded-xl bg-slate-50 dark:bg-navy-700/40 border border-slate-200 dark:border-navy-600 space-y-3.5">
                                    {{-- DPI Selection --}}
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-navy-200 mb-1">
                                            Target Resolusi Gambar (DPI): <span class="text-primary dark:text-accent-light font-bold">{{ $customDpi }} DPI</span>
                                        </label>
                                        <select wire:model.live="customDpi" class="form-select h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs dark:border-navy-500 dark:bg-navy-700 dark:text-navy-100">
                                            <option value="72">72 DPI (Kualitas Layar / Web Rendah)</option>
                                            <option value="100">100 DPI (Kualitas Medium Cepat)</option>
                                            <option value="150">150 DPI (Standar Direkomendasikan)</option>
                                            <option value="200">200 DPI (Kualitas Cetak Tajam)</option>
                                            <option value="300">300 DPI (Kualitas Arsip / High-Res)</option>
                                        </select>
                                    </div>

                                    {{-- Quality Slider --}}
                                    <div>
                                        <div class="flex justify-between text-xs font-semibold text-slate-700 dark:text-navy-200 mb-1">
                                            <span>Kualitas Gambar Raster:</span>
                                            <span class="text-primary dark:text-accent-light font-bold">{{ $customQuality }}%</span>
                                        </div>
                                        <input type="range" wire:model.live="customQuality" min="30" max="95" step="5" class="w-full accent-primary" />
                                        <div class="flex justify-between text-[10px] text-slate-400 dark:text-navy-400 mt-0.5">
                                            <span>30% (Kecil)</span>
                                            <span>60%</span>
                                            <span>95% (Maksimal)</span>
                                        </div>
                                    </div>

                                    {{-- Strip Metadata Checkbox --}}
                                    <label class="inline-flex items-center space-x-2 cursor-pointer pt-1">
                                        <input type="checkbox" wire:model.live="customStripMetadata" class="form-checkbox is-basic size-4 rounded-sm border-slate-400/70 checked:bg-primary checked:border-primary dark:border-navy-400" />
                                        <span class="text-xs text-slate-700 dark:text-navy-200 font-medium">Hapus metadata & riwayat revisi tersembunyi (Ekstra hemat ukuran)</span>
                                    </label>
                                </div>
                            @endif
                        @endif

                        {{-- Compress Action Button --}}
                        <div class="pt-2">
                            @if($preset === 'custom' && $isCustomLocked && !$isPro)
                                <a href="{{ route('pricing') }}" class="btn w-full h-11 rounded-lg bg-warning font-bold text-white shadow-lg shadow-warning/30 hover:bg-warning-focus text-xs sm:text-sm flex items-center justify-center space-x-2">
                                    <x-lucide-crown class="size-4" />
                                    <span>Upgrade ke PRO untuk Kompres Kustom</span>
                                </a>
                            @else
                                <button wire:click="compress" 
                                        wire:loading.attr="disabled"
                                        class="btn w-full h-11 rounded-lg bg-primary font-bold text-white shadow-lg shadow-primary/30 hover:bg-primary-focus dark:bg-accent dark:shadow-accent/30 dark:hover:bg-accent-focus text-xs sm:text-sm flex items-center justify-center space-x-2 transition-all disabled:opacity-75 disabled:cursor-not-allowed">
                                    <div wire:loading wire:target="compress" class="spinner size-4.5 border-2 border-white border-r-transparent rounded-full"></div>
                                    <span wire:loading.remove wire:target="compress" class="flex items-center space-x-1.5">
                                        <x-lucide-file-archive class="size-4" />
                                        <span>Mulai Kompres PDF Sekarang</span>
                                    </span>
                                    <span wire:loading wire:target="compress">Sedang Mengompres Dokumen PDF...</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
            @endif
        </div>

        {{-- Right: Result & Download Card (5 Columns) --}}
        <div class="col-span-12 lg:col-span-5 space-y-4 sm:space-y-5">
            {{-- Processing State Card (Lineone Indeterminate Progress) --}}
            <div wire:loading wire:target="compress" class="w-full">
                <div class="card flex min-h-[380px] h-full flex-col items-center justify-center p-6 text-center border border-primary/30 dark:border-accent/30 bg-primary/5 dark:bg-accent/5">
                    <div class="mask is-squircle flex size-16 items-center justify-center bg-primary/10 text-primary dark:bg-accent/10 dark:text-accent-light mb-4 shadow-sm">
                        <x-lucide-file-cog class="size-8 animate-pulse" />
                    </div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-navy-100">
                        Sedang Mengompres Dokumen PDF...
                    </h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-navy-300 max-w-xs leading-relaxed">
                        Engine sedang memadatkan stream data, menghapus objek duplikat, dan mengoptimalkan gambar secara aman.
                    </p>

                    {{-- Lineone Indeterminate Progress Bar --}}
                    <div class="w-full max-w-xs mt-5">
                        <div class="progress h-2 bg-primary/20 dark:bg-accent/20 rounded-full overflow-hidden">
                            <div class="is-indeterminate rounded-full bg-primary dark:bg-accent"></div>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-wrap items-center justify-center gap-2">
                        <span class="badge rounded-full bg-white/80 text-slate-700 dark:bg-navy-700 dark:text-navy-200 text-[10px] font-semibold px-2.5 py-0.5 shadow-xs flex items-center gap-1">
                            <x-lucide-shield-check class="size-3 text-success" />
                            <span>Privasi Dokumen Terjamin</span>
                        </span>
                        <span class="badge rounded-full bg-white/80 text-slate-700 dark:bg-navy-700 dark:text-navy-200 text-[10px] font-semibold px-2.5 py-0.5 shadow-xs flex items-center gap-1">
                            <x-lucide-zap class="size-3 text-warning" />
                            <span>Stream Deflation</span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Result Panel (Hidden during compress loading) --}}
            <div wire:loading.remove wire:target="compress" class="card p-5 sm:p-6 text-center border border-slate-200/80 dark:border-navy-700 h-full flex flex-col justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 dark:text-navy-100 mb-4 pb-3 border-b border-slate-200 dark:border-navy-700 flex items-center justify-between">
                        <span>Hasil Kompresi PDF</span>
                        @if($resultPath)
                            <span class="badge rounded-full bg-success/15 text-success text-[10px] font-bold px-2 py-0.5 flex items-center gap-1">
                                <x-lucide-check class="size-3" />
                                <span>Selesai</span>
                            </span>
                        @endif
                    </h3>

                    @if($resultPath)
                        {{-- Comparison Cards --}}
                        <div class="space-y-4">
                            {{-- Savings Hero Badge --}}
                            <div class="rounded-2xl bg-linear-to-br from-emerald-50 to-teal-50 dark:from-navy-750 dark:to-navy-700 p-5 border border-emerald-200/60 dark:border-navy-600">
                                <div class="mask is-squircle flex size-12 items-center justify-center bg-success text-white mx-auto mb-2 shadow-md shadow-success/20">
                                    <x-lucide-file-check class="size-6" />
                                </div>
                                <h4 class="text-2xl font-black text-slate-800 dark:text-navy-50">
                                    Hemat {{ $savingsPercentage }}%
                                </h4>
                                <p class="text-xs text-slate-500 dark:text-navy-300 mt-0.5">
                                    Dokumen PDF berhasil diperkecil secara signifikan
                                </p>
                            </div>

                            {{-- Size Comparison Grid --}}
                            <div class="grid grid-cols-2 gap-3 text-left">
                                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-navy-700/50 border border-slate-200 dark:border-navy-600">
                                    <p class="text-[10px] font-semibold text-slate-400 dark:text-navy-300 uppercase">Ukuran Awal</p>
                                    <p class="text-sm font-bold text-slate-700 dark:text-navy-100 mt-0.5">
                                        {{ number_format($originalSize / 1024, 1) }} KB
                                    </p>
                                </div>
                                <div class="p-3.5 rounded-xl bg-primary/5 dark:bg-accent-light/5 border border-primary/20 dark:border-accent-light/20">
                                    <p class="text-[10px] font-semibold text-primary dark:text-accent-light uppercase">Ukuran Baru</p>
                                    <p class="text-sm font-bold text-primary dark:text-accent-light mt-0.5">
                                        {{ number_format($newSize / 1024, 1) }} KB
                                    </p>
                                </div>
                            </div>

                            <div class="text-xs text-slate-400 dark:text-navy-300 flex items-center justify-center gap-2 pt-1">
                                <span>📄 Total Halaman: <strong class="text-slate-700 dark:text-navy-200">{{ $pageCount }}</strong></span>
                            </div>
                        </div>
                    @else
                        {{-- Empty Placeholder State --}}
                        <div class="py-12 px-4 flex flex-col items-center justify-center">
                            <div class="mask is-squircle flex size-16 items-center justify-center bg-slate-100 text-slate-400 dark:bg-navy-700 dark:text-navy-300 mb-3">
                                <x-lucide-file-archive class="size-8" />
                            </div>
                            <p class="text-sm font-bold text-slate-700 dark:text-navy-200">Belum Ada File Diproses</p>
                            <p class="text-xs text-slate-400 dark:text-navy-300 mt-1 max-w-xs">
                                Unggah dokumen PDF di panel sebelah kiri dan klik tombol kompres untuk melihat hasil dan mengunduh file.
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Download & Reset Buttons --}}
                @if($resultPath)
                    <div class="pt-6 space-y-2">
                        <button wire:click="download" class="btn w-full h-11 rounded-lg bg-success font-bold text-white shadow-lg shadow-success/30 hover:bg-success-focus text-xs sm:text-sm flex items-center justify-center space-x-2">
                            <x-lucide-download class="size-4.5" />
                            <span>Unduh File PDF Terkompres</span>
                        </button>
                        <button wire:click="resetFile" class="btn w-full h-9 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-100 dark:border-navy-600 dark:text-navy-200 dark:hover:bg-navy-700 text-xs font-semibold">
                            Kompres File PDF Lain
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
