<div>
    @section('page_description', 'Hapus satu atau beberapa halaman tertentu dari dokumen PDF secara visual dengan grid thumbnail atau input rentang halaman.')

    {{-- Error Flash Alert --}}
    @if (!empty($errorMsg))
        <div class="alert flex rounded-lg border border-error px-4 py-3 text-error sm:px-5 mb-6">
            <x-lucide-alert-circle class="size-5 shrink-0 mr-2.5" />
            <span class="text-xs">{{ $errorMsg }}</span>
        </div>
    @endif
    @error('file')
        <div class="alert flex rounded-lg border border-error px-4 py-3 text-error sm:px-5 mb-6">
            <x-lucide-alert-circle class="size-5 shrink-0 mr-2.5" />
            <span class="text-xs">{{ $message }}</span>
        </div>
    @enderror

    <div class="space-y-4 sm:space-y-5 lg:space-y-6">
        
        {{-- Quota Notification Pill & Document Info Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                @if(auth()->check() && auth()->user()->is_admin)
                    <div class="badge rounded-full bg-primary/10 text-primary px-3 py-1 text-xs font-medium">
                        Akses: Unlimited (Admin)
                    </div>
                @elseif($remainingQuota !== null)
                    <div class="badge rounded-full space-x-2 bg-slate-150 px-3 py-1 text-slate-700 text-xs font-medium">
                        <span>Sisa Kuota:</span>
                        <span class="font-semibold text-primary">{{ $remainingQuota }} / {{ $monthlyLimit ?? 10 }}</span>
                    </div>
                @else
                    <div class="badge rounded-full bg-success/15 text-success px-3 py-1 text-xs font-medium">
                        Kuota: {{ $isPro ? 'Unlimited (Pro)' : 'Tanpa Batas' }}
                    </div>
                @endif
            </div>

            @if($file)
                <button wire:click="resetFile" class="text-xs text-error hover:underline flex items-center space-x-1 font-medium cursor-pointer self-start sm:self-auto">
                    <x-lucide-trash-2 class="size-3.5" />
                    <span>Ganti Dokumen PDF</span>
                </button>
            @endif
        </div>

        @if($remainingQuota !== null && $remainingQuota <= 0 && !(auth()->check() && auth()->user()->is_admin))
            <div class="card p-6 text-center">
                <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-warning/15 text-warning mb-3">
                    <x-lucide-alert-triangle class="size-7" />
                </div>
                <h3 class="text-base font-semibold text-slate-800">Kuota Bulanan Anda Telah Habis</h3>
                <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto leading-relaxed">
                    Tingkatkan akun Anda ke paket Pro untuk menghapus halaman dokumen PDF tanpa batasan kuota.
                </p>
                <a href="{{ route('pricing') }}" class="btn h-9 rounded-full mt-4 bg-primary font-medium text-white shadow-xs text-xs px-6">
                    Upgrade ke Pro Sekarang
                </a>
            </div>
        @else

            {{-- Upload Area (Shown when no file is uploaded yet) --}}
            @if (!$file)
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
                           :class="isDropping ? 'border-primary/50 bg-primary/5' : 'border-slate-300 hover:bg-slate-50'"
                           class="flex flex-col items-center justify-center w-full rounded-xl border-2 border-dashed p-10 cursor-pointer transition-all text-center">
                        
                        <div class="flex size-14 items-center justify-center rounded-full bg-slate-100 text-slate-600 mb-3">
                            <x-lucide-upload-cloud class="size-7" />
                        </div>
                        <p class="text-sm font-medium text-slate-700">
                            Seret dan lepas berkas PDF di sini, atau <span class="text-primary font-semibold">pilih berkas</span>
                        </p>
                        <p class="mt-1 text-xs text-slate-400">Mendukung dokumen PDF hingga 50MB</p>
                        <input x-ref="fileInput" type="file" wire:model="file" accept="application/pdf" class="hidden" />
                    </label>

                    {{-- Upload Progress Bar --}}
                    <div x-show="isUploading" class="mt-4">
                        <div class="flex justify-between text-xs text-slate-500 mb-1">
                            <span>Mengunggah dokumen PDF...</span>
                            <span x-text="`${progress}%`"></span>
                        </div>
                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full bg-primary transition-all duration-150" :style="`width: ${progress}%`"></div>
                        </div>
                    </div>
                </div>
            @else

                {{-- Result / Download State (Shown when pages pruned successfully) --}}
                @if ($resultPath)
                    <div class="card p-5 sm:p-6 border-2 border-primary/20 bg-primary/5">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-primary/10">
                            <div class="flex items-center space-x-3">
                                <div class="flex size-10 items-center justify-center rounded-full bg-primary text-white shrink-0">
                                    <x-lucide-check class="size-5" />
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-slate-800">Halaman Berhasil Dihapus!</h3>
                                    <p class="text-xs text-slate-500 mt-0.5">Dokumen baru telah dipangkas dan siap diunduh</p>
                                </div>
                            </div>
                            <span class="badge rounded-full bg-primary/10 text-primary px-3 py-1 text-xs font-medium self-start sm:self-auto">
                                Sisa {{ $totalPagesAfter }} Halaman
                            </span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 my-4">
                            <div class="bg-white p-3 rounded-xl border border-slate-200/60 text-center">
                                <span class="text-xs text-slate-400 block">Halaman Awal</span>
                                <span class="text-base font-semibold text-slate-700 block mt-0.5">{{ $totalPagesBefore }} Hal</span>
                            </div>
                            <div class="bg-white p-3 rounded-xl border border-slate-200/60 text-center">
                                <span class="text-xs text-error block">Dihapus</span>
                                <span class="text-base font-semibold text-error block mt-0.5">-{{ $removedCount }} Hal</span>
                            </div>
                            <div class="bg-white p-3 rounded-xl border border-slate-200/60 text-center">
                                <span class="text-xs text-success block">Halaman Akhir</span>
                                <span class="text-base font-semibold text-slate-800 block mt-0.5">{{ $totalPagesAfter }} Hal</span>
                            </div>
                            <div class="bg-white p-3 rounded-xl border border-slate-200/60 text-center">
                                <span class="text-xs text-slate-400 block">Ukuran Berkas</span>
                                <span class="text-base font-semibold text-slate-700 block mt-0.5">{{ round($newSize / 1024, 1) }} KB</span>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center gap-2.5 pt-2">
                            <button type="button" 
                                    wire:click="download"
                                    class="btn h-9 w-full sm:w-auto rounded-full bg-primary font-medium text-white shadow-xs text-xs px-6 hover:bg-primary-focus cursor-pointer flex items-center justify-center space-x-2 transition-all">
                                <x-lucide-download class="size-4" />
                                <span>Unduh PDF Hasil Pemangkasan</span>
                            </button>

                            <button type="button" 
                                    wire:click="resetResult"
                                    class="btn h-9 w-full sm:w-auto rounded-full border border-slate-300 font-medium text-slate-700 hover:bg-white text-xs px-4 cursor-pointer flex items-center justify-center space-x-1.5 transition-all">
                                <x-lucide-rotate-ccw class="size-3.5 text-slate-500" />
                                <span>Pilih Ulang Halaman</span>
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Interactive Visual Selection & Configuration Grid --}}
                @if (!$isEncrypted && !$resultPath)
                    <div class="grid grid-cols-1 gap-4 sm:gap-5 lg:grid-cols-12 lg:gap-6">
                        
                        {{-- Left Column: Visual Thumbnails Grid (8 Columns) --}}
                        <div class="col-span-12 lg:col-span-8 space-y-4">
                            <div class="card p-4 sm:p-5">
                                
                                {{-- Grid Header Bar & Quick Filters --}}
                                <div class="flex flex-wrap items-center justify-between gap-2.5 pb-3.5 mb-4 border-b border-slate-200">
                                    <div>
                                        <h3 class="text-sm font-semibold text-slate-800">Pilih Halaman yang Ingin Dihapus</h3>
                                        <p class="text-xs text-slate-500 mt-0.5">Klik pada kartu halaman untuk menandai halaman yang ingin dibuang</p>
                                    </div>

                                    {{-- Quick Selection Buttons --}}
                                    <div class="flex items-center space-x-1.5">
                                        <button type="button" 
                                                wire:click="selectOdd"
                                                class="btn h-7 rounded-full border border-slate-200 text-xs px-2.5 font-medium text-slate-600 hover:bg-slate-50 cursor-pointer transition-all"
                                                title="Tandai semua halaman ganjil">
                                            <span>Ganjil</span>
                                        </button>

                                        <button type="button" 
                                                wire:click="selectEven"
                                                class="btn h-7 rounded-full border border-slate-200 text-xs px-2.5 font-medium text-slate-600 hover:bg-slate-50 cursor-pointer transition-all"
                                                title="Tandai semua halaman genap">
                                            <span>Genap</span>
                                        </button>

                                        @if(count($selectedPages) > 0)
                                            <button type="button" 
                                                    wire:click="clearSelection"
                                                    class="btn h-7 rounded-full border border-slate-200 text-xs px-2.5 font-medium text-error hover:bg-error/10 cursor-pointer transition-all"
                                                    title="Batalkan semua pilihan">
                                                <span>Reset</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                {{-- Page Thumbnails Grid --}}
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 sm:gap-4">
                                    @for ($pageNum = 1; $pageNum <= $thumbnailsCount; $pageNum++)
                                        @php
                                            $isMarked = in_array($pageNum, $selectedPages);
                                        @endphp
                                        <div wire:click="togglePage({{ $pageNum }})"
                                             class="group relative rounded-xl border-2 p-2 transition-all cursor-pointer select-none text-center {{ $isMarked ? 'border-error bg-error/5 ring-1 ring-error/30' : 'border-slate-200 bg-white hover:border-slate-300 hover:shadow-xs' }}">
                                            
                                            {{-- Thumbnail Image Container --}}
                                            <div class="relative overflow-hidden rounded-lg bg-slate-100 aspect-[3/4] flex items-center justify-center">
                                                <img src="{{ asset('storage/temp_thumbs/' . $thumbBatch . '/thumb_page_' . $pageNum . '.png') }}" 
                                                     alt="Halaman {{ $pageNum }}" 
                                                     loading="lazy"
                                                     class="h-full w-full object-contain transition-transform duration-150 group-hover:scale-[1.02] {{ $isMarked ? 'opacity-40 grayscale' : '' }}" />

                                                {{-- Deletion Overlay Marker --}}
                                                @if ($isMarked)
                                                    <div class="absolute inset-0 flex flex-col items-center justify-center bg-error/20 backdrop-blur-[0.5px]">
                                                        <div class="flex size-8 items-center justify-center rounded-full bg-error text-white shadow-xs mb-1">
                                                            <x-lucide-trash-2 class="size-4" />
                                                        </div>
                                                        <span class="text-xs font-semibold text-error px-1.5 py-0.5 rounded bg-white/90">
                                                            Dihapus
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- Page Label --}}
                                            <div class="mt-2 flex items-center justify-between px-1 text-xs">
                                                <span class="font-medium {{ $isMarked ? 'text-error line-through' : 'text-slate-700' }}">
                                                    Hal {{ $pageNum }}
                                                </span>
                                                <span class="size-4 rounded-full flex items-center justify-center {{ $isMarked ? 'bg-error text-white' : 'border border-slate-300 text-transparent group-hover:border-slate-400' }}">
                                                    <x-lucide-check class="size-2.5 stroke-[3]" />
                                                </span>
                                            </div>
                                        </div>
                                    @endfor
                                </div>

                                @if ($totalPages > $thumbnailsCount)
                                    <div class="mt-4 p-3 bg-slate-50 rounded-lg text-center text-xs text-slate-500">
                                        Menampilkan {{ $thumbnailsCount }} halaman pertama dari total {{ $totalPages }} halaman. Untuk halaman lainnya, Anda dapat menggunakan kolom input rentang halaman di sebelah kanan.
                                    </div>
                                @endif

                            </div>
                        </div>

                        {{-- Right Column: Control Panel & Summary (4 Columns - Sticky) --}}
                        <div class="col-span-12 lg:col-span-4 space-y-4">
                            <div class="lg:sticky lg:top-20 space-y-4">
                                
                                {{-- Summary Card --}}
                                <div class="card p-4 sm:p-5">
                                    <h4 class="text-xs font-semibold text-slate-800 mb-3 pb-2 border-b border-slate-200">
                                        Ringkasan Penghapusan
                                    </h4>

                                    <div class="space-y-2.5 text-xs text-slate-600 mb-4">
                                        <div class="flex justify-between py-1 border-b border-slate-100">
                                            <span>Total Halaman Asli:</span>
                                            <span class="font-medium text-slate-800">{{ $totalPages }} Halaman</span>
                                        </div>
                                        <div class="flex justify-between py-1 border-b border-slate-100">
                                            <span>Halaman Akan Dihapus:</span>
                                            <span class="font-semibold text-error">{{ count($selectedPages) }} Halaman</span>
                                        </div>
                                        <div class="flex justify-between py-1">
                                            <span>Halaman Tersisa:</span>
                                            <span class="font-semibold text-primary">{{ max(0, $totalPages - count($selectedPages)) }} Halaman</span>
                                        </div>
                                    </div>

                                    {{-- Manual Range Input Field --}}
                                    <div class="pt-2 border-t border-slate-200/60 mb-4">
                                        <label class="block text-xs font-medium text-slate-700 mb-1.5">
                                            Rentang Halaman Tertulis
                                        </label>
                                        <div class="relative">
                                            <input type="text" 
                                                   wire:model.live.debounce.300ms="pagesInput"
                                                   placeholder="Contoh: 2, 4-6, 9"
                                                   class="form-input h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-700 placeholder:text-slate-400 focus:border-primary focus:ring-1 focus:ring-primary font-mono" />
                                        </div>
                                        <span class="text-xs text-slate-400 mt-1 block leading-relaxed">
                                            Tersinkronisasi otomatis dengan pilihan kartu di samping.
                                        </span>
                                    </div>

                                    {{-- Action Button --}}
                                    <button type="button" 
                                            wire:click="process"
                                            wire:loading.attr="disabled"
                                            @if(empty($selectedPages) || count($selectedPages) >= $totalPages) disabled @endif
                                            class="btn h-9 w-full rounded-full bg-error font-medium text-white shadow-xs text-xs px-5 hover:bg-error/90 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center space-x-2 transition-all">
                                        <span wire:loading.remove wire:target="process" class="flex items-center space-x-2">
                                            <x-lucide-trash-2 class="size-4" />
                                            <span>Hapus {{ count($selectedPages) }} Halaman Terpilih</span>
                                        </span>
                                        <span wire:loading wire:target="process" class="flex items-center space-x-2">
                                            <x-lucide-loader-2 class="size-4 animate-spin" />
                                            <span>Memproses PDF...</span>
                                        </span>
                                    </button>

                                    @if(count($selectedPages) >= $totalPages)
                                        <span class="text-xs text-warning mt-2 block text-center">
                                            Minimal 1 halaman harus disisakan pada dokumen.
                                        </span>
                                    @endif
                                </div>

                                {{-- Panduan Box --}}
                                <div class="card p-4 sm:p-5">
                                    <h4 class="text-xs font-semibold text-slate-800 mb-2 flex items-center space-x-1.5">
                                        <x-lucide-info class="size-4 text-primary" />
                                        <span>Tips Cepat</span>
                                    </h4>
                                    <ul class="space-y-1.5 text-xs text-slate-600 leading-relaxed">
                                        <li>• Klik langsung kartu halaman untuk menandai/membatalkan.</li>
                                        <li>• Gunakan tombol <strong>Ganjil</strong> atau <strong>Genap</strong> untuk dokumen bolak-balik.</li>
                                        <li>• Dokumen diproses instan tanpa merusak tautan atau resolusi vektor asli.</li>
                                    </ul>
                                </div>

                            </div>
                        </div>

                    </div>
                @endif

            @endif

        @endif

    </div>
</div>
