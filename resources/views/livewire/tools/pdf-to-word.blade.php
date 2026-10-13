<div>
    @section('page_description', 'Konversi dokumen PDF Anda menjadi format Word (.docx) yang dapat diedit dengan mudah dan rapi.')

    {{-- Error Flash Alert --}}
    @if (!empty($errorMsg))
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
        {{-- Left: Upload & Convert Controls (7 Columns) --}}
        <div class="col-span-12 lg:col-span-7 space-y-4 sm:space-y-5 lg:space-y-6">
            
            {{-- Quota Notification Pill --}}
            <div class="flex items-center justify-between">
                @if(auth()->check() && auth()->user()->is_admin)
                    <div class="badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/15 dark:text-accent-light px-3 py-1 text-xs font-medium">
                        Akses: Unlimited (Admin)
                    </div>
                @elseif($remainingQuota !== null)
                    <div class="badge rounded-full space-x-2 bg-slate-150 px-3 py-1 text-slate-800 dark:bg-navy-600 dark:text-navy-100 text-xs font-medium">
                        <span>Sisa Kuota Bulan Ini:</span>
                        <span class="text-primary dark:text-accent-light font-semibold">{{ $remainingQuota }} / {{ $monthlyLimit ?? $dailyLimit }}</span>
                    </div>
                @else
                    <div class="badge rounded-full space-x-2 bg-success/10 px-3 py-1 text-success text-xs font-medium">
                        <span>Kuota:</span>
                        <span>{{ $isPro ? 'Unlimited (Pro)' : 'Tanpa Batas' }}</span>
                    </div>
                @endif

                @if($file)
                    <button wire:click="resetFile" class="text-xs text-error hover:underline flex items-center space-x-1 font-medium cursor-pointer">
                        <x-lucide-trash-2 class="size-3.5" />
                        <span>Ganti Dokumen</span>
                    </button>
                @endif
            </div>

            @if($remainingQuota !== null && $remainingQuota <= 0)
                <div class="card p-6 text-center">
                    <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-warning/10 text-warning">
                        <x-lucide-alert-triangle class="size-7" />
                    </div>
                    <h3 class="mt-4 text-base font-semibold text-slate-800 dark:text-navy-100">Kuota Bulanan Anda Telah Habis</h3>
                    <p class="mt-1 text-xs text-slate-400 dark:text-navy-300 max-w-sm mx-auto">Tingkatkan akun Anda ke Pro untuk menikmati pemrosesan dokumen tanpa batas kuota bulanan.</p>
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
                                <x-lucide-file-text class="size-7" />
                            </div>
                            <p class="text-sm font-semibold text-slate-700 dark:text-navy-100 truncate px-4 max-w-md">
                                {{ $file->getClientOriginalName() }}
                            </p>
                            <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">
                                Ukuran File: <span class="font-medium text-slate-700 dark:text-navy-200">{{ number_format($file->getSize() / 1024, 2) }} KB</span>
                            </p>
                            <span class="mt-3 badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light text-xs font-medium px-3 py-0.5">
                                Dokumen PDF Siap Dikonversi
                            </span>
                        @else
                            <div class="mask is-squircle flex size-14 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light mb-3">
                                <x-lucide-file-text class="size-7" />
                            </div>
                            <p class="text-sm font-medium text-slate-700 dark:text-navy-100">
                                Klik untuk unggah PDF <span class="font-normal text-slate-400 dark:text-navy-300">atau tarik dokumen ke sini</span>
                            </p>
                            <p class="mt-1 text-xs text-slate-400 dark:text-navy-300 uppercase tracking-wider">
                                FORMAT PDF — MAKSIMAL {{ $maxMb }}MB
                            </p>
                        @endif
                        
                        <input type="file" x-ref="fileInput" wire:model="file" class="hidden" accept="application/pdf" />
                    </label>

                    {{-- Lineone Upload Real-time Progress Bar --}}
                    <div x-show="isUploading" x-transition class="w-full mt-3.5 rounded-xl bg-slate-100 p-3.5 dark:bg-navy-700/60 border border-slate-200 dark:border-navy-600 space-y-2">
                        <div class="flex items-center justify-between text-xs font-medium text-slate-700 dark:text-navy-100">
                            <span class="flex items-center space-x-2">
                                <div class="spinner size-3.5 border-2 border-primary border-r-transparent rounded-full dark:border-accent-light"></div>
                                <span>Mengunggah dokumen PDF ke server...</span>
                            </span>
                            <span class="text-primary dark:text-accent-light font-semibold" x-text="progress + '%'"></span>
                        </div>
                        <div class="progress h-2 bg-slate-200 dark:bg-navy-600 rounded-full overflow-hidden">
                            <div class="is-active rounded-full bg-primary dark:bg-accent transition-all duration-150" :style="`width: ${progress}%`"></div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Action Convert Button --}}
            @if ($file && !$status)
                <div class="card p-4 sm:p-5">
                    <button wire:click="convert" 
                            wire:loading.attr="disabled" 
                            class="btn w-full h-10 rounded-lg bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus text-xs sm:text-sm flex items-center justify-center space-x-2 transition-all disabled:opacity-75 disabled:cursor-not-allowed cursor-pointer">
                        <div wire:loading wire:target="convert" class="spinner size-4.5 border-2 border-white border-r-transparent rounded-full"></div>
                        <span wire:loading.remove wire:target="convert" class="flex items-center space-x-1.5">
                            <x-lucide-file-text class="size-4" />
                            <span>Mulai Konversi ke Word (.docx)</span>
                        </span>
                        <span wire:loading wire:target="convert">Memulai Proses Konversi...</span>
                    </button>
                </div>
            @endif
        </div>

        {{-- Right: Status & Result Area (5 Columns) --}}
        <div class="col-span-12 lg:col-span-5 space-y-4 sm:space-y-5">
            @if ($status === 'processing')
                <div class="card flex min-h-[380px] h-full flex-col items-center justify-center p-6 text-center border border-primary/20 dark:border-accent/20 bg-primary/5 dark:bg-accent/5" wire:poll.2s="checkStatus">
                    <div class="mask is-squircle flex size-14 items-center justify-center bg-primary/10 text-primary dark:bg-accent/10 dark:text-accent-light mb-4">
                        <x-lucide-file-cog class="size-7 animate-pulse" />
                    </div>
                    <h3 class="text-base font-semibold text-slate-800 dark:text-navy-100">
                        Sedang Mengonversi Dokumen PDF ke Word...
                    </h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-navy-300 max-w-xs leading-relaxed">
                        Server sedang merekonstruksi teks, gaya huruf, gambar, dan tata letak tabel ke format Word (.docx).
                    </p>

                    {{-- Lineone Indeterminate Progress Bar --}}
                    <div class="w-full max-w-xs mt-5">
                        <div class="progress h-2 bg-primary/20 dark:bg-accent/20 rounded-full overflow-hidden">
                            <div class="is-indeterminate rounded-full bg-primary dark:bg-accent"></div>
                        </div>
                    </div>
                </div>
            @elseif ($status === 'completed')
                <div class="card flex h-full flex-col items-center justify-center p-6 text-center border border-slate-150 dark:border-navy-600">
                    <div class="mask is-squircle flex size-14 items-center justify-center bg-success/10 text-success mb-3">
                        <x-lucide-check-circle class="size-7" />
                    </div>

                    <h3 class="text-base font-semibold text-slate-800 dark:text-navy-100">
                        Konversi PDF ke Word Berhasil!
                    </h3>
                    <p class="text-xs text-slate-400 dark:text-navy-300 mt-0.5">
                        Dokumen Word (.docx) Anda siap untuk diunduh dan diedit.
                    </p>

                    {{-- Comparison Box --}}
                    <div class="mt-5 w-full rounded-lg border border-slate-150 bg-slate-50 p-4 dark:border-navy-600 dark:bg-navy-700/60 space-y-2.5 text-xs">
                        <div class="flex items-center justify-between border-b border-slate-200/80 pb-2.5 dark:border-navy-600">
                            <span class="text-slate-500 dark:text-navy-200">Dokumen PDF Asli</span>
                            <span class="font-semibold text-slate-700 dark:text-navy-100">
                                {{ number_format($originalSize / 1024, 2) }} KB
                            </span>
                        </div>
                        <div class="flex items-center justify-between pt-0.5">
                            <span class="text-slate-500 dark:text-navy-200">Hasil Word (.docx)</span>
                            <span class="font-semibold text-primary dark:text-accent-light">
                                {{ number_format($newSize / 1024, 2) }} KB
                            </span>
                        </div>
                    </div>

                    {{-- Download Action --}}
                    <div class="mt-6 w-full space-y-2">
                        <button wire:click="download" class="btn w-full h-10 rounded-lg bg-success font-medium text-white hover:bg-success-focus focus:bg-success-focus text-xs sm:text-sm flex items-center justify-center space-x-2 cursor-pointer">
                            <x-lucide-download class="size-4.5" />
                            <span>Unduh Dokumen Word (.docx)</span>
                        </button>
                        <button wire:click="resetFile" class="btn w-full h-9 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-100 dark:border-navy-600 dark:text-navy-200 dark:hover:bg-navy-700 text-xs font-medium cursor-pointer">
                            Konversi Dokumen Lainnya
                        </button>
                    </div>
                </div>
            @elseif ($status === 'failed')
                <div class="card flex h-full flex-col items-center justify-center border border-error/30 p-6 text-center">
                    <div class="mask is-squircle flex size-14 items-center justify-center bg-error/10 text-error mb-3">
                        <x-lucide-alert-circle class="size-7" />
                    </div>
                    <h3 class="text-base font-semibold text-slate-800 dark:text-navy-100">Konversi Gagal</h3>
                    <p class="mt-1 text-xs text-error mb-5 max-w-xs">{{ $errorMsg ?? 'Terjadi kesalahan teknis saat memproses konversi file PDF.' }}</p>
                    <button wire:click="resetResult" class="btn rounded-full border border-slate-300 font-medium text-slate-700 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-100 dark:hover:bg-navy-500 text-xs px-6 py-2 cursor-pointer">
                        Coba Lagi
                    </button>
                </div>
            @else
                <div class="card flex min-h-[350px] h-full flex-col items-center justify-center border border-dashed border-slate-200 bg-slate-50/50 dark:border-navy-600 dark:bg-navy-800/50 p-6 text-center">
                    <div class="mask is-squircle mx-auto flex size-14 items-center justify-center bg-slate-100 text-slate-400 dark:bg-navy-700 dark:text-navy-300 mb-3">
                        <x-lucide-file-text class="size-7" />
                    </div>
                    <p class="font-semibold text-slate-700 dark:text-navy-200 text-xs tracking-wide">Area Status & Hasil</p>
                    <p class="text-xs text-slate-400 dark:text-navy-300 mt-1 max-w-xs">
                        Unggah file PDF di panel sebelah kiri untuk memulai konversi dokumen ke format Word (.docx).
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
