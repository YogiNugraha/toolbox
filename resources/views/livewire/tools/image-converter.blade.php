<div>
    @section('page_description', 'Ubah format gambar Anda dari dan ke JPG, PNG, WebP, GIF, dan format lainnya secara instan.')

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
        {{-- Left: Upload & Format Options (7 Columns) --}}
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
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-slate-700 dark:text-navy-100 truncate px-4 max-w-md">
                                {{ $file->getClientOriginalName() }}
                            </p>
                            <p class="mt-1 text-xs text-slate-400 dark:text-navy-300 uppercase">
                                Format: <span class="font-bold text-slate-700 dark:text-navy-200">{{ strtoupper($file->getClientOriginalExtension()) }}</span> • {{ number_format($file->getSize() / 1024, 2) }} KB
                            </p>
                            <span class="mt-3 badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light text-[11px] font-semibold px-3 py-0.5">
                                File Siap Dikonversi
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
                                JPG · PNG · WEBP · GIF · BMP (Maksimal 10MB)
                            </p>
                        @endif
                        
                        <input type="file" x-ref="fileInput" wire:model="file" class="hidden" accept="image/jpeg, image/png, image/webp, image/gif, image/bmp" />
                    </label>

                    {{-- Lineone Upload Real-time Progress Bar --}}
                    <div x-show="isUploading" x-transition class="w-full mt-3.5 rounded-xl bg-slate-100 p-3.5 dark:bg-navy-700/60 border border-slate-200 dark:border-navy-600 space-y-2">
                        <div class="flex items-center justify-between text-xs font-semibold text-slate-700 dark:text-navy-100">
                            <span class="flex items-center space-x-2">
                                <div class="spinner size-3.5 border-2 border-primary border-r-transparent rounded-full dark:border-accent-light"></div>
                                <span>Mengunggah file gambar ke server...</span>
                            </span>
                            <span class="text-primary dark:text-accent-light font-bold" x-text="progress + '%'"></span>
                        </div>
                        <div class="progress h-2 bg-slate-200 dark:bg-navy-600 rounded-full overflow-hidden">
                            <div class="is-active rounded-full bg-primary dark:bg-accent transition-all duration-150" :style="`width: ${progress}%`"></div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Format Target Options --}}
            @if ($file)
                <div class="card p-4 sm:p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-700 dark:text-navy-100 uppercase tracking-wide">
                            Pilih Format Tujuan
                        </h3>
                        <span class="text-xs text-slate-400 dark:text-navy-300">Target Format</span>
                    </div>

                    {{-- Format Selection Grid --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                        @foreach([
                            'jpg' => 'JPG / JPEG',
                            'png' => 'PNG (Transparan)',
                            'webp' => 'WebP (Modern)',
                            'gif' => 'GIF'
                        ] as $format => $label)
                            <label class="cursor-pointer">
                                <input type="radio" wire:model.live="outputFormat" value="{{ $format }}" class="peer sr-only" />
                                <div class="rounded-xl border p-3 text-center transition-all peer-checked:border-primary peer-checked:bg-primary/10 peer-checked:text-primary peer-checked:shadow-xs dark:peer-checked:border-accent dark:peer-checked:bg-accent/10 dark:peer-checked:text-accent-light {{ $outputFormat === $format ? 'border-primary bg-primary/10 text-primary dark:border-accent dark:bg-accent/10 dark:text-accent-light' : 'border-slate-200 text-slate-600 dark:border-navy-500 dark:text-navy-200 hover:border-slate-300 dark:hover:border-navy-400' }}">
                                    <span class="block font-bold text-sm uppercase">{{ $format }}</span>
                                    <span class="block text-[10px] text-slate-400 dark:text-navy-300 mt-0.5">{{ $label }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    {{-- Action Button --}}
                    <button wire:click="convert" 
                            wire:loading.attr="disabled" 
                            class="btn w-full h-11 rounded-lg bg-primary font-bold text-white shadow-lg shadow-primary/30 hover:bg-primary-focus dark:bg-accent dark:shadow-accent/30 dark:hover:bg-accent-focus text-xs sm:text-sm flex items-center justify-center space-x-2 transition-all disabled:opacity-75 disabled:cursor-not-allowed">
                        <div wire:loading wire:target="convert" class="spinner size-4.5 border-2 border-white border-r-transparent rounded-full"></div>
                        <span wire:loading.remove wire:target="convert" class="flex items-center space-x-1.5">
                            <x-lucide-arrow-left-right class="size-4" />
                            <span>Ubah Format Gambar Sekarang</span>
                        </span>
                        <span wire:loading wire:target="convert">Sedang Mengonversi Gambar...</span>
                    </button>
                </div>
            @endif
        </div>

        {{-- Right: Result & Preview Area (5 Columns) --}}
        <div class="col-span-12 lg:col-span-5 space-y-4 sm:space-y-5">
            {{-- Processing State Card (Lineone Indeterminate Progress) --}}
            <div wire:loading wire:target="convert" class="w-full">
                <div class="card flex min-h-[380px] h-full flex-col items-center justify-center p-6 text-center border border-primary/30 dark:border-accent/30 bg-primary/5 dark:bg-accent/5">
                    <div class="mask is-squircle flex size-16 items-center justify-center bg-primary/10 text-primary dark:bg-accent/10 dark:text-accent-light mb-4 shadow-sm">
                        <x-lucide-refresh-cw class="size-8 animate-spin" />
                    </div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-navy-100">
                        Sedang Mengonversi Format Gambar...
                    </h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-navy-300 max-w-xs leading-relaxed">
                        Sistem sedang menyusun ulang kanal warna piksel dan metadata ke format target <strong class="uppercase text-primary dark:text-accent-light">.{{ $outputFormat }}</strong>.
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
                            <span>Kualitas Terjaga</span>
                        </span>
                        <span class="badge rounded-full bg-white/80 text-slate-700 dark:bg-navy-700 dark:text-navy-200 text-[10px] font-semibold px-2.5 py-0.5 shadow-xs flex items-center gap-1">
                            <x-lucide-zap class="size-3 text-warning" />
                            <span>High Fidelity</span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Result Panel (Hidden during convert loading) --}}
            <div wire:loading.remove wire:target="convert">
                @if ($resultPath)
                    <div class="card flex h-full flex-col items-center justify-center p-6 text-center border border-slate-200/80 dark:border-navy-700 shadow-md">
                        <div class="mask is-squircle flex size-16 items-center justify-center bg-success/10 text-success mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>

                        <h3 class="text-lg font-bold text-slate-700 dark:text-navy-100">
                            Konversi Format Berhasil!
                        </h3>
                        <p class="text-xs text-slate-400 dark:text-navy-300 mt-0.5">
                            File berhasil diubah ke format <span class="font-bold uppercase text-primary dark:text-accent-light">.{{ $resultExtension }}</span>.
                        </p>

                        {{-- Comparison Box --}}
                        <div class="mt-5 w-full rounded-xl border border-slate-150 bg-slate-50 p-4 dark:border-navy-600 dark:bg-navy-700/60 space-y-2.5 text-xs">
                            <div class="flex items-center justify-between border-b border-slate-200/80 pb-2.5 dark:border-navy-600">
                                <span class="text-slate-500 dark:text-navy-200">Format Asli</span>
                                <span class="font-bold text-slate-700 dark:text-navy-100 uppercase">
                                    {{ strtoupper($file->getClientOriginalExtension()) }} ({{ number_format($originalSize / 1024, 2) }} KB)
                                </span>
                            </div>
                            <div class="flex items-center justify-between pt-0.5">
                                <span class="text-slate-500 dark:text-navy-200">Format Baru</span>
                                <span class="font-bold text-primary dark:text-accent-light uppercase">
                                    {{ strtoupper($resultExtension) }} ({{ number_format($newSize / 1024, 2) }} KB)
                                </span>
                            </div>
                        </div>

                        {{-- Download Action --}}
                        <div class="mt-6 w-full space-y-2">
                            <button wire:click="download" class="btn w-full h-11 rounded-lg bg-success font-bold text-white shadow-lg shadow-success/30 hover:bg-success-focus text-xs sm:text-sm flex items-center justify-center space-x-2">
                                <x-lucide-download class="size-4.5" />
                                <span>Unduh File (.{{ $resultExtension }})</span>
                            </button>
                            <button wire:click="resetFile" class="btn w-full h-9 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-100 dark:border-navy-600 dark:text-navy-200 dark:hover:bg-navy-700 text-xs font-semibold">
                                Konversi Gambar Lainnya
                            </button>
                        </div>
                    </div>
                @elseif ($file && !$errors->has('file'))
                    <div class="card flex min-h-[350px] h-full flex-col items-center justify-center p-4 relative overflow-hidden group border border-slate-200/80 dark:border-navy-700">
                        <img src="{{ $file->temporaryUrl() }}" class="max-h-[420px] max-w-full rounded-lg object-contain shadow-sm" alt="Preview Gambar">
                        <div class="absolute bottom-4 left-0 right-0 flex justify-center">
                            <span class="badge rounded-full bg-slate-800/80 text-white backdrop-blur-xs shadow-md px-3 py-1 text-xs font-semibold">
                                Preview File Siap Konversi
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
                            Unggah gambar di panel sebelah kiri untuk melihat pratinjau dan hasil konversi di sini.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
