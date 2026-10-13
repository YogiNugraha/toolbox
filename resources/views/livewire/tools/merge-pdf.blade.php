<div>
    @section('page_description', 'Gabungkan beberapa file dokumen PDF menjadi satu file PDF yang berurutan, rapi, dan berkualitas tinggi secara instan.')

    {{-- Error Flash Alert --}}
    @if (!empty($errorMsg))
        <div class="alert flex rounded-lg border border-error px-4 py-4 text-error sm:px-5 mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0 mr-2.5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span>{{ $errorMsg }}</span>
        </div>
    @endif
    @error('files')
        <div class="alert flex rounded-lg border border-error px-4 py-4 text-error sm:px-5 mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0 mr-2.5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span>{{ $message }}</span>
        </div>
    @enderror

    <div class="grid grid-cols-1 gap-4 sm:gap-5 lg:grid-cols-12 lg:gap-6">
        {{-- Left: Upload Area, File List Reordering & Controls (7 Columns) --}}
        <div class="col-span-12 lg:col-span-7 space-y-4 sm:space-y-5 lg:space-y-6">
            
            {{-- Quota Notification Pill --}}
            <div class="flex items-center justify-between">
                @if(auth()->check() && auth()->user()->is_admin)
                    <div class="badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/15 dark:text-accent-light px-3 py-1 text-xs font-medium">
                        Akses: Unlimited (Admin)
                    </div>
                @elseif($remainingQuota !== null)
                    <div class="badge rounded-full space-x-2 bg-slate-150 px-3 py-1 text-slate-800 dark:bg-navy-600 dark:text-navy-100 text-xs font-semibold">
                        <span>Sisa Kuota Bulan Ini:</span>
                        <span class="text-primary dark:text-accent-light">{{ $remainingQuota }} / {{ $monthlyLimit ?? 5 }}</span>
                    </div>
                @else
                    <div class="badge rounded-full space-x-2 bg-success/10 px-3 py-1 text-success text-xs font-semibold">
                        <span>Kuota:</span>
                        <span>{{ $isPro ? 'Unlimited (Pro)' : 'Tanpa Batas' }}</span>
                    </div>
                @endif

                @if(count($files) > 0)
                    <button wire:click="resetFiles" class="text-xs text-error hover:underline flex items-center space-x-1 font-medium cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span>Hapus Semua File</span>
                    </button>
                @endif
            </div>

            @if($remainingQuota !== null && $remainingQuota <= 0 && !(auth()->check() && auth()->user()->is_admin))
                <div class="card p-6 text-center">
                    <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-warning/10 text-warning">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-slate-700 dark:text-navy-100">Kuota Bulanan Anda Telah Habis</h3>
                    <p class="mt-1 text-xs text-slate-400 dark:text-navy-300 max-w-sm mx-auto">Tingkatkan akun Anda ke Pro untuk menikmati penggabungan file PDF tanpa batas kuota bulanan.</p>
                    <a href="{{ route('pricing') }}" class="btn rounded-full mt-5 bg-warning font-medium text-white hover:bg-warning-focus shadow-sm text-xs px-6 py-2">
                        Upgrade ke Pro Sekarang
                    </a>
                </div>
            @else
                {{-- Multi-File Upload Drag & Drop Area --}}
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
                           :class="isDropping ? 'border-primary/50 bg-primary/5 dark:border-accent-light/50 dark:bg-accent-light/5' : 'border-slate-300 dark:border-navy-450 hover:bg-slate-50 dark:hover:bg-navy-900/50'"
                           class="flex flex-col items-center justify-center w-full rounded-xl border-2 border-dashed p-7 cursor-pointer transition-all text-center">
                        
                        <div class="mask is-squircle flex size-14 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light mb-3">
                            <x-lucide-files class="size-7" />
                        </div>
                        <p class="text-sm font-semibold text-slate-700 dark:text-navy-100">
                            Klik untuk unggah beberapa file PDF <span class="font-normal text-slate-400 dark:text-navy-300">atau tarik dokumen ke sini</span>
                        </p>
                        <p class="mt-1 text-xs text-slate-400 dark:text-navy-300 uppercase tracking-wider">
                            PILIH MINIMAL 2 FILE PDF (Maksimal {{ $maxMb }}MB per file)
                        </p>
                        
                        <input x-ref="fileInput" wire:model="files" type="file" multiple accept="application/pdf,.pdf" class="hidden" />
                    </label>

                    {{-- Upload Real-time Progress Bar --}}
                    <div x-show="isUploading" x-transition class="w-full mt-3.5 rounded-xl bg-slate-100 p-3.5 dark:bg-navy-700/60 border border-slate-200 dark:border-navy-600 space-y-2">
                        <div class="flex items-center justify-between text-xs font-semibold text-slate-700 dark:text-navy-100">
                            <span class="flex items-center space-x-2">
                                <div class="spinner size-3.5 border-2 border-primary border-r-transparent rounded-full dark:border-accent-light"></div>
                                <span>Mengunggah file PDF ke server...</span>
                            </span>
                            <span class="text-primary dark:text-accent-light font-bold" x-text="progress + '%'"></span>
                        </div>
                        <div class="progress h-2 bg-slate-200 dark:bg-navy-600 rounded-full overflow-hidden">
                            <div class="is-active rounded-full bg-primary dark:bg-accent transition-all duration-150" :style="`width: ${progress}%`"></div>
                        </div>
                    </div>
                </div>

                {{-- Selected Files List & Reordering Controls --}}
                @if (count($files) > 0)
                    <div class="card p-4 sm:p-5 space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-navy-700">
                            <div class="flex items-center gap-2">
                                <h4 class="text-xs font-semibold text-slate-700 dark:text-navy-100 uppercase tracking-wider">
                                    Urutan Dokumen ({{ count($files) }} File)
                                </h4>
                                <span class="badge rounded-full bg-primary/10 text-primary dark:bg-accent/15 dark:text-accent font-medium text-xs px-2.5 py-0.5">
                                    Gunakan tombol ↑ / ↓ untuk atur urutan
                                </span>
                            </div>
                        </div>

                        <div class="space-y-2 max-h-[340px] overflow-y-auto is-scrollbar-hidden pr-1">
                            @foreach($files as $index => $fileItem)
                                <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 dark:bg-navy-750/50 border border-slate-150 dark:border-navy-600 transition-all hover:border-primary/40 group">
                                    <div class="flex items-center space-x-3 overflow-hidden">
                                        <span class="flex size-6 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light text-xs font-medium">
                                            {{ $index + 1 }}
                                        </span>
                                        <div class="overflow-hidden">
                                            <p class="text-xs font-medium text-slate-800 dark:text-navy-100 truncate max-w-xs sm:max-w-sm">
                                                {{ $fileItem->getClientOriginalName() }}
                                            </p>
                                            <p class="text-xs text-slate-400 dark:text-navy-300">
                                                {{ number_format($fileItem->getSize() / 1024, 1) }} KB · Dokumen PDF
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-1 shrink-0">
                                        {{-- Move Up Button --}}
                                        <button 
                                            wire:click="moveUp({{ $index }})"
                                            type="button" 
                                            @if($index === 0) disabled @endif
                                            class="btn size-7 p-0 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed dark:border-navy-600 dark:text-navy-200 dark:hover:bg-navy-600 cursor-pointer"
                                            title="Geser ke Atas"
                                        >
                                            <x-lucide-arrow-up class="size-3.5" />
                                        </button>

                                        {{-- Move Down Button --}}
                                        <button 
                                            wire:click="moveDown({{ $index }})"
                                            type="button" 
                                            @if($index === count($files) - 1) disabled @endif
                                            class="btn size-7 p-0 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed dark:border-navy-600 dark:text-navy-200 dark:hover:bg-navy-600 cursor-pointer"
                                            title="Geser ke Bawah"
                                        >
                                            <x-lucide-arrow-down class="size-3.5" />
                                        </button>

                                        {{-- Remove Button --}}
                                        <button 
                                            wire:click="removeFile({{ $index }})"
                                            type="button" 
                                            class="btn size-7 p-0 rounded-lg border border-error/30 text-error hover:bg-error/10 dark:border-error/40 cursor-pointer"
                                            title="Hapus dari Daftar"
                                        >
                                            <x-lucide-trash-2 class="size-3.5" />
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Action Merge Button --}}
                        <div class="pt-3">
                            <button wire:click="merge" 
                                    wire:loading.attr="disabled"
                                    @if(count($files) < 2) disabled @endif
                                    class="btn w-full h-10 rounded-lg bg-primary font-medium text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus text-xs sm:text-sm flex items-center justify-center space-x-2 transition-all disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer">
                                <div wire:loading wire:target="merge" class="spinner size-4 border-2 border-white border-r-transparent rounded-full"></div>
                                <span wire:loading.remove wire:target="merge" class="flex items-center space-x-1.5">
                                    <x-lucide-combine class="size-4" />
                                    <span>Gabungkan {{ count($files) }} Dokumen PDF</span>
                                </span>
                                <span wire:loading wire:target="merge">Sedang Menggabungkan Dokumen...</span>
                            </button>
                        </div>
                    </div>
                @endif
            @endif
        </div>

        {{-- Right: Result & Download Card (5 Columns) --}}
        <div class="col-span-12 lg:col-span-5 space-y-4 sm:space-y-5">
            {{-- Processing State Card (Lineone Indeterminate Progress) --}}
            <div wire:loading wire:target="merge" class="w-full">
                <div class="card flex min-h-[380px] h-full flex-col items-center justify-center p-6 text-center border border-primary/30 dark:border-accent/30 bg-primary/5 dark:bg-accent/5">
                    <div class="mask is-squircle flex size-16 items-center justify-center bg-primary/10 text-primary dark:bg-accent/10 dark:text-accent-light mb-4">
                        <x-lucide-combine class="size-8 animate-pulse" />
                    </div>
                    <h3 class="text-base font-medium text-slate-800 dark:text-navy-100">
                        Sedang Menggabungkan PDF...
                    </h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-navy-300 max-w-xs leading-relaxed">
                        Server sedang menyatukan seluruh halaman dokumen PDF sesuai urutan yang Anda pilih secara berurutan dan rapi.
                    </p>

                    {{-- Lineone Indeterminate Progress Bar --}}
                    <div class="w-full max-w-xs mt-5">
                        <div class="progress h-2 bg-primary/20 dark:bg-accent/20 rounded-full overflow-hidden">
                            <div class="is-indeterminate rounded-full bg-primary dark:bg-accent"></div>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-wrap items-center justify-center gap-2">
                        <span class="badge rounded-full bg-slate-100 text-slate-700 dark:bg-navy-600 dark:text-navy-200 text-xs font-medium px-2.5 py-0.5">
                            Kualitas Vektor Terjaga
                        </span>
                        <span class="badge rounded-full bg-slate-100 text-slate-700 dark:bg-navy-600 dark:text-navy-200 text-xs font-medium px-2.5 py-0.5">
                            Proses Cepat
                        </span>
                    </div>
                </div>
            </div>

            {{-- Result Panel (Hidden during merge loading) --}}
            <div wire:loading.remove wire:target="merge" class="card p-5 sm:p-6 text-center border border-slate-150 dark:border-navy-600 h-full flex flex-col justify-between">
                <div>
                    <h3 class="text-sm font-medium text-slate-700 dark:text-navy-100 mb-4 pb-3 border-b border-slate-150 dark:border-navy-600 flex items-center justify-between">
                        <span>Hasil PDF Gabungan</span>
                        @if($resultPath)
                            <span class="badge rounded-full bg-success/15 text-success text-xs font-medium px-2 py-0.5">
                                Selesai
                            </span>
                        @endif
                    </h3>

                    @if($resultPath)
                        {{-- Comparison Cards --}}
                        <div class="space-y-4">
                            {{-- Success Hero Badge --}}
                            <div class="rounded-lg bg-slate-50 dark:bg-navy-750/60 p-5 border border-slate-150 dark:border-navy-600">
                                <div class="mask is-squircle flex size-12 items-center justify-center bg-primary text-white mx-auto mb-2">
                                    <x-lucide-combine class="size-6" />
                                </div>
                                <h4 class="text-xl font-semibold text-slate-800 dark:text-navy-100">
                                    PDF Berhasil Digabung!
                                </h4>
                                <p class="text-xs text-slate-500 dark:text-navy-300 mt-0.5">
                                    {{ $filesCount }} file PDF telah disatukan secara berurutan
                                </p>
                            </div>

                            {{-- Size Comparison Grid --}}
                            <div class="grid grid-cols-2 gap-3 text-left">
                                <div class="p-3 rounded-lg bg-white dark:bg-navy-700 border border-slate-150 dark:border-navy-600">
                                    <p class="text-xs font-medium text-slate-400 dark:text-navy-300 uppercase">Total File Awal</p>
                                    <p class="text-sm font-semibold text-slate-700 dark:text-navy-100 mt-0.5">
                                        {{ number_format($originalSize / 1024, 1) }} KB
                                    </p>
                                </div>
                                <div class="p-3 rounded-lg bg-primary/5 dark:bg-accent-light/5 border border-primary/20 dark:border-accent-light/20">
                                    <p class="text-xs font-medium text-primary dark:text-accent-light uppercase">Ukuran Gabungan</p>
                                    <p class="text-sm font-semibold text-primary dark:text-accent-light mt-0.5">
                                        {{ number_format($newSize / 1024, 1) }} KB
                                    </p>
                                </div>
                            </div>

                            <div class="text-xs text-slate-400 dark:text-navy-300 flex items-center justify-center gap-3 pt-1">
                                <span>📑 Dokumen: <strong class="text-slate-700 dark:text-navy-200 font-medium">{{ $filesCount }} File</strong></span>
                                <span>•</span>
                                <span>📄 Total: <strong class="text-slate-700 dark:text-navy-200 font-medium">{{ $pageCount }} Halaman</strong></span>
                            </div>
                        </div>
                    @else
                        {{-- Empty Placeholder State --}}
                        <div class="py-12 px-4 flex flex-col items-center justify-center">
                            <div class="mask is-squircle flex size-16 items-center justify-center bg-slate-100 text-slate-400 dark:bg-navy-700 dark:text-navy-300 mb-3">
                                <x-lucide-combine class="size-8" />
                            </div>
                            <p class="text-sm font-medium text-slate-700 dark:text-navy-200">Belum Ada File Digabung</p>
                            <p class="text-xs text-slate-400 dark:text-navy-300 mt-1 max-w-xs">
                                Unggah minimal 2 file dokumen PDF di panel sebelah kiri, atur urutan yang diinginkan, dan klik tombol gabungkan.
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Download & Reset Buttons --}}
                @if($resultPath)
                    <div class="pt-6 space-y-2">
                        <button wire:click="download" class="btn w-full h-10 rounded-lg bg-primary font-medium text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus text-xs sm:text-sm flex items-center justify-center space-x-2 cursor-pointer">
                            <x-lucide-download class="size-4" />
                            <span>Unduh PDF Gabungan (.pdf)</span>
                        </button>
                        <button wire:click="resetFiles" class="btn w-full h-9 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 dark:border-navy-450 dark:text-navy-200 dark:hover:bg-navy-700 text-xs font-medium cursor-pointer">
                            Gabungkan Dokumen Lain
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
