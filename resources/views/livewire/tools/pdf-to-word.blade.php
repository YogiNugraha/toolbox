<div>
    @section('page_description', 'Konversi dokumen PDF Anda menjadi format Word (.docx) yang dapat diedit dengan mudah dan rapi.')

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
        {{-- Left: Upload & Convert Controls (7 Columns) --}}
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
                        <span>Ganti Dokumen</span>
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
                    <p class="mt-1 text-xs text-slate-400 dark:text-navy-300 max-w-sm mx-auto">Tingkatkan akun Anda ke Pro untuk menikmati pemrosesan dokumen tanpa batas kuota harian.</p>
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
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-slate-700 dark:text-navy-100 truncate px-4 max-w-md">
                                {{ $file->getClientOriginalName() }}
                            </p>
                            <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">
                                Ukuran File: <span class="font-semibold text-slate-700 dark:text-navy-200">{{ number_format($file->getSize() / 1024, 2) }} KB</span>
                            </p>
                            <span class="mt-3 badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light text-[11px] font-semibold px-3 py-0.5">
                                Dokumen PDF Siap Dikonversi
                            </span>
                        @else
                            <div class="mask is-squircle flex size-14 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light mb-3">
                                <svg class="size-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-slate-700 dark:text-navy-100">
                                Klik untuk unggah PDF <span class="font-normal text-slate-400 dark:text-navy-300">atau tarik dokumen ke sini</span>
                            </p>
                            <p class="mt-1 text-xs text-slate-400 dark:text-navy-300 uppercase tracking-wider">
                                FORMAT PDF — MAKSIMAL {{ $maxMb }}MB
                            </p>
                        @endif
                        
                        <input type="file" x-ref="fileInput" wire:model="file" class="hidden" accept="application/pdf" />
                    </label>

                    <div wire:loading wire:target="file" class="mt-3 flex items-center justify-center space-x-2 text-xs font-semibold text-primary dark:text-accent-light">
                        <div class="spinner size-4 animate-spin rounded-full border-2 border-current border-r-transparent"></div>
                        <span>Mengunggah dokumen PDF...</span>
                    </div>
                </div>
            @endif

            {{-- Action Convert Button --}}
            @if ($file && !$status)
                <div class="card p-4 sm:p-5">
                    <div class="rounded-xl border border-slate-150 bg-slate-50/50 p-4 dark:border-navy-600 dark:bg-navy-700/40 mb-4 text-xs text-slate-600 dark:text-navy-200">
                        <div class="flex items-center space-x-2 font-semibold text-slate-700 dark:text-navy-100 mb-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-primary dark:text-accent-light" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Informasi Konversi</span>
                        </div>
                        <p class="text-slate-500 dark:text-navy-300 pl-6">
                            Format output dokumen adalah <span class="font-bold text-slate-700 dark:text-navy-100">Microsoft Word (.docx)</span>. Tata letak, teks, dan tabel akan dipertahankan semaksimal mungkin agar mudah diedit.
                        </p>
                    </div>

                    <button wire:click="convert" wire:loading.attr="disabled" class="btn rounded-full w-full bg-primary font-bold text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus text-xs py-2.5 shadow-sm disabled:opacity-60">
                        <span wire:loading.remove wire:target="convert">Mulai Konversi ke Word (.docx)</span>
                        <div wire:loading wire:target="convert" class="flex items-center justify-center space-x-2">
                            <div class="spinner size-4 animate-spin rounded-full border-2 border-current border-r-transparent"></div>
                            <span>Memulai Proses...</span>
                        </div>
                    </button>
                </div>
            @endif
        </div>

        {{-- Right: Status & Result Area (5 Columns) --}}
        <div class="col-span-12 lg:col-span-5">
            @if ($status === 'processing')
                <div class="card flex min-h-[350px] h-full flex-col items-center justify-center p-6 text-center shadow-md" wire:poll.2s="checkStatus">
                    <div class="spinner mx-auto mb-4 size-12 animate-spin rounded-full border-3 border-primary border-r-transparent dark:border-accent-light"></div>
                    <h3 class="text-base font-bold text-slate-700 dark:text-navy-100">
                        Sedang Mengonversi Dokumen...
                    </h3>
                    <p class="mt-1 text-xs text-slate-400 dark:text-navy-300 max-w-xs">
                        Server sedang menyusun ulang struktur teks, gambar, dan tabel PDF Anda ke format Microsoft Word.
                    </p>
                    <div class="mt-6 flex items-center space-x-2 text-xs font-semibold text-primary dark:text-accent-light">
                        <span class="size-2 rounded-full bg-primary animate-ping dark:bg-accent"></span>
                        <span>Memproses di latar belakang</span>
                    </div>
                </div>
            @elseif ($status === 'completed')
                <div class="card flex h-full flex-col items-center justify-center p-6 text-center shadow-md">
                    <div class="mask is-squircle flex size-16 items-center justify-center bg-success/10 text-success mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <h3 class="text-lg font-bold text-slate-700 dark:text-navy-100">
                        Konversi PDF ke Word Berhasil!
                    </h3>
                    <p class="text-xs text-slate-400 dark:text-navy-300 mt-0.5">
                        Dokumen Word (.docx) Anda siap untuk diunduh dan diedit.
                    </p>

                    {{-- Comparison Box --}}
                    <div class="mt-5 w-full rounded-xl border border-slate-150 bg-slate-50 p-4 dark:border-navy-600 dark:bg-navy-700/60 space-y-2.5 text-xs">
                        <div class="flex items-center justify-between border-b border-slate-200/80 pb-2.5 dark:border-navy-600">
                            <span class="text-slate-500 dark:text-navy-200">Dokumen PDF Asli</span>
                            <span class="font-bold text-slate-700 dark:text-navy-100">
                                {{ number_format($originalSize / 1024, 2) }} KB
                            </span>
                        </div>
                        <div class="flex items-center justify-between pt-0.5">
                            <span class="text-slate-500 dark:text-navy-200">Hasil Word (.docx)</span>
                            <span class="font-bold text-primary dark:text-accent-light">
                                {{ number_format($newSize / 1024, 2) }} KB
                            </span>
                        </div>
                    </div>

                    {{-- Download Action --}}
                    <div class="mt-6 w-full space-y-2">
                        <button wire:click="download" class="btn rounded-full w-full space-x-2 bg-success font-bold text-white hover:bg-success-focus text-xs py-2.5 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Unduh Dokumen Word (.docx)</span>
                        </button>
                        <button wire:click="resetResult; $set('file', null)" class="btn rounded-full w-full border border-slate-300 text-slate-700 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-100 dark:hover:bg-navy-500 text-xs py-2">
                            Konversi Dokumen Lainnya
                        </button>
                    </div>
                </div>
            @elseif ($status === 'failed')
                <div class="card flex h-full flex-col items-center justify-center border-2 border-error/40 p-6 text-center shadow-md">
                    <div class="mask is-squircle flex size-16 items-center justify-center bg-error/10 text-error mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-700 dark:text-navy-100">Konversi Gagal</h3>
                    <p class="mt-1 text-xs text-error mb-5 max-w-xs">{{ $errorMsg ?? 'Terjadi kesalahan teknis saat memproses konversi file PDF.' }}</p>
                    <button wire:click="resetResult" class="btn rounded-full border border-slate-300 font-semibold text-slate-700 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-100 dark:hover:bg-navy-500 text-xs px-6 py-2">
                        Coba Lagi
                    </button>
                </div>
            @else
                <div class="card flex min-h-[350px] h-full flex-col items-center justify-center border-2 border-dashed border-slate-200 bg-slate-50/50 dark:border-navy-600 dark:bg-navy-800/50 p-6 text-center">
                    <div class="mask is-squircle mx-auto flex size-16 items-center justify-center bg-slate-200 text-slate-400 dark:bg-navy-600 dark:text-navy-300 mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                    <p class="font-bold text-slate-500 dark:text-navy-300 text-xs uppercase tracking-wider">Area Status & Hasil</p>
                    <p class="text-[11px] text-slate-400 dark:text-navy-300 mt-1 max-w-xs">
                        Unggah file PDF di panel sebelah kiri untuk memulai konversi dokumen ke format Word (.docx).
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
