<div>
    @section('page_description', 'Konversi dokumen PDF Anda menjadi format Word (.docx) yang dapat diedit dengan mudah.')

    {{-- Error Message --}}
    @if ($errorMsg)
        <div class="alert flex rounded-lg border border-error px-4 py-4 text-error sm:px-5 mb-6">
            {{ $errorMsg }}
        </div>
    @endif
    @error('file')
        <div class="alert flex rounded-lg border border-error px-4 py-4 text-error sm:px-5 mb-6">
            {{ $message }}
        </div>
    @enderror

    <div class="grid grid-cols-1 gap-4 sm:gap-5 lg:grid-cols-2 lg:gap-6">
        {{-- Left: Upload & Settings --}}
        <div class="space-y-4 sm:space-y-5 lg:space-y-6">
            @if($remainingQuota !== null)
            <div class="badge space-x-2 bg-slate-150 text-slate-800 dark:bg-navy-500 dark:text-navy-100">
                <span>Sisa kuota hari ini:</span>
                <span class="font-medium text-primary dark:text-accent-light">{{ $remainingQuota }} / {{ $dailyLimit }}</span>
            </div>
            @endif

            @if($remainingQuota !== null && $remainingQuota <= 0)
                <div class="card p-5 text-center">
                    <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-warning/10 text-warning">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-slate-700 dark:text-navy-100">Kuota harian kamu sudah habis</h3>
                    <p class="mt-1 text-sm text-slate-400 dark:text-navy-300">Upgrade ke Pro untuk pemakaian unlimited di semua tools.</p>
                    <a href="{{ route('pricing') }}" class="btn mt-6 bg-warning font-medium text-white hover:bg-warning-focus focus:bg-warning-focus active:bg-warning-focus/90">
                        Upgrade ke Pro
                    </a>
                </div>
            @else
                <div class="card p-4 sm:p-5">
                    <label x-data="{ isDropping: false }" x-on:dragover.prevent="isDropping = true"
                        x-on:dragleave.prevent="isDropping = false"
                        x-on:drop.prevent="isDropping = false; if($event.dataTransfer.files.length > 0) { $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change', { bubbles: true })) }"
                        :class="isDropping ? 'border-primary/50 bg-primary/5 dark:border-accent-light/50 dark:bg-accent-light/5' : '{{ $file ? 'border-primary/50 dark:border-accent-light/50' : 'border-slate-300 dark:border-navy-450 hover:bg-slate-50 dark:hover:bg-navy-900/50' }}'"
                        class="flex flex-col items-center justify-center w-full rounded-lg border-2 border-dashed p-10 cursor-pointer transition-colors text-center">
                        
                        @if ($file)
                            <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-success/10 text-success mb-4">
                                <svg class="size-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-slate-700 dark:text-navy-100 truncate px-4 w-full">
                                {{ $file->getClientOriginalName() }}
                            </p>
                            <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">
                                {{ number_format($file->getSize() / 1024, 2) }} KB
                            </p>
                        @else
                            <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-slate-150 text-slate-500 dark:bg-navy-500 dark:text-navy-200 mb-4">
                                <svg class="size-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                </svg>
                            </div>
                            <p class="font-medium text-slate-700 dark:text-navy-100">Klik untuk upload <span class="font-normal text-slate-400 dark:text-navy-300">atau drag and drop</span></p>
                            <p class="mt-2 text-xs text-slate-400 dark:text-navy-300 uppercase tracking-wide">
                                PDF SAJA — MAX {{ $maxMb }}MB
                            </p>
                        @endif
                        
                        <input type="file" x-ref="fileInput" wire:model="file" class="hidden" accept="application/pdf" />
                    </label>

                    <div wire:loading wire:target="file" class="mt-3 flex items-center justify-center space-x-2 text-sm font-medium text-primary dark:text-accent-light">
                        <div class="spinner size-4 animate-spin rounded-full border-[2px] border-current border-r-transparent"></div>
                        <span>Mengupload...</span>
                    </div>
                </div>
            @endif

            @if ($file && !$status)
                <button wire:click="convert" wire:loading.attr="disabled" class="btn w-full space-x-2 bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90 disabled:opacity-70 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="convert">Mulai Konversi</span>
                    <div wire:loading wire:target="convert" class="flex items-center space-x-2">
                        <div class="spinner size-4 animate-spin rounded-full border-[2px] border-current border-r-transparent"></div>
                        <span>Memproses...</span>
                    </div>
                </button>
            @endif
        </div>

        {{-- Right: Status & Result --}}
        <div>
            @if ($status === 'processing')
                <div class="card flex min-h-[400px] h-full flex-col items-center justify-center p-6 text-center" wire:poll.2s="checkStatus">
                    <div class="spinner mx-auto mb-6 size-12 animate-spin rounded-full border-[3px] border-primary border-r-transparent dark:border-accent-light"></div>
                    <h3 class="text-lg font-medium text-slate-700 dark:text-navy-100">Sedang Mengkonversi...</h3>
                    <p class="mt-2 text-sm text-slate-400 dark:text-navy-300">Mohon tunggu, proses ini mungkin memakan waktu beberapa saat tergantung ukuran PDF Anda.</p>
                </div>
            @elseif ($status === 'completed')
                <div class="card flex h-full flex-col items-center justify-center p-6 text-center">
                    <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-success/10 text-success mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <h3 class="text-xl font-semibold text-slate-700 dark:text-navy-100">Konversi Berhasil</h3>

                    <div class="mt-6 w-full max-w-sm rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-navy-500 dark:bg-navy-800">
                        <div class="flex items-center justify-between border-b border-slate-200 pb-3 dark:border-navy-500 mb-3">
                            <span class="text-sm font-medium text-slate-500 dark:text-navy-200">PDF Asli</span>
                            <span class="text-sm font-semibold text-slate-700 dark:text-navy-100">
                                {{ number_format($originalSize / 1024, 2) }} KB
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-500 dark:text-navy-200">Word (DOCX)</span>
                            <span class="text-sm font-semibold text-primary dark:text-accent-light">
                                {{ number_format($newSize / 1024, 2) }} KB
                            </span>
                        </div>
                    </div>

                    <div class="mt-8 w-full max-w-sm">
                        <button wire:click="download" class="btn w-full space-x-2 bg-success font-medium text-white hover:bg-success-focus focus:bg-success-focus active:bg-success-focus/90">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Download Word</span>
                        </button>
                    </div>
                </div>
            @elseif ($status === 'failed')
                <div class="card flex h-full flex-col items-center justify-center border-2 border-error p-6 text-center">
                    <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-error/10 text-error mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-700 dark:text-navy-100">Konversi Gagal</h3>
                    <p class="mt-2 text-sm text-error mb-6">{{ $errorMsg }}</p>
                    <button wire:click="resetResult" class="btn border border-slate-300 font-medium text-slate-800 hover:bg-slate-150 focus:bg-slate-150 active:bg-slate-150/80 dark:border-navy-450 dark:text-navy-50 dark:hover:bg-navy-500 dark:focus:bg-navy-500 dark:active:bg-navy-500/90">
                        Coba Lagi
                    </button>
                </div>
            @else
                <div class="card flex min-h-[400px] h-full flex-col items-center justify-center border-2 border-dashed border-slate-200 bg-slate-50 dark:border-navy-500 dark:bg-navy-800 p-6 text-center">
                    <div class="mx-auto flex size-20 items-center justify-center rounded-full bg-slate-200 text-slate-400 dark:bg-navy-600 dark:text-navy-300 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <p class="font-medium text-slate-500 dark:text-navy-300 uppercase tracking-wide">Preview Area</p>
                </div>
            @endif
        </div>
    </div>
</div>
