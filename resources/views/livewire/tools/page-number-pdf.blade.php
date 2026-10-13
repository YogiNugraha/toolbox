<div>
    @section('page_description', 'Tambahkan nomor halaman otomatis pada dokumen PDF dengan pilihan format penomoran, posisi margin, dan gaya huruf.')

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

    <div class="grid grid-cols-1 gap-4 sm:gap-5 lg:grid-cols-12 lg:gap-6">
        
        {{-- Left: Upload Area, Controls & Action (7 Columns) --}}
        <div class="col-span-12 lg:col-span-7 space-y-4 sm:space-y-5 lg:space-y-6">
            
            {{-- Quota Notification Pill --}}
            <div class="flex items-center justify-between">
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

                @if($file)
                    <button wire:click="resetFile" class="text-xs text-error hover:underline flex items-center space-x-1 font-medium cursor-pointer">
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
                        Tingkatkan akun Anda ke paket Pro untuk memberi nomor halaman pada dokumen tanpa batasan kuota.
                    </p>
                    <a href="{{ route('pricing') }}" class="btn h-9 rounded-full mt-4 bg-primary font-medium text-white shadow-xs text-xs px-6">
                        Upgrade ke Pro Sekarang
                    </a>
                </div>
            @else

                {{-- Upload Drag & Drop Area with Alpine Progress --}}
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
                           :class="isDropping ? 'border-primary/50 bg-primary/5' : '{{ $file ? 'border-primary/40 bg-slate-50/50' : 'border-slate-300 hover:bg-slate-50' }}'"
                           class="flex flex-col items-center justify-center w-full rounded-xl border-2 border-dashed p-8 cursor-pointer transition-all text-center">
                        
                        @if ($file)
                            <div class="flex size-14 items-center justify-center rounded-xl bg-primary/10 text-primary mb-3">
                                <x-lucide-file-text class="size-7" />
                            </div>
                            <p class="text-sm font-semibold text-slate-800 truncate px-4 max-w-md">
                                {{ $file->getClientOriginalName() }}
                            </p>
                            <div class="mt-1 flex items-center space-x-2 text-xs text-slate-500">
                                <span>{{ round($file->getSize() / 1024, 1) }} KB</span>
                                <span>•</span>
                                <span class="font-medium text-primary">{{ $totalPages }} Halaman</span>
                            </div>
                            <span class="mt-3 text-xs text-primary font-medium hover:underline">Klik untuk mengganti berkas</span>
                        @else
                            <div class="flex size-14 items-center justify-center rounded-full bg-slate-100 text-slate-600 mb-3">
                                <x-lucide-upload-cloud class="size-7" />
                            </div>
                            <p class="text-sm font-medium text-slate-700">
                                Seret dan lepas berkas PDF di sini, atau <span class="text-primary font-semibold">pilih berkas</span>
                            </p>
                            <p class="mt-1 text-xs text-slate-400">Mendukung dokumen PDF hingga 50MB</p>
                        @endif

                        <input x-ref="fileInput" type="file" wire:model="file" accept="application/pdf" class="hidden" />
                    </label>

                    {{-- Upload Progress Bar --}}
                    <div x-show="isUploading" class="mt-4">
                        <div class="flex justify-between text-xs text-slate-500 mb-1">
                            <span>Mengunggah dokumen...</span>
                            <span x-text="`${progress}%`"></span>
                        </div>
                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full bg-primary transition-all duration-150" :style="`width: ${progress}%`"></div>
                        </div>
                    </div>
                </div>

                {{-- Pagination Options Card (Visible after file uploaded) --}}
                @if ($file && !$isEncrypted)
                    <div class="card p-4 sm:p-5 space-y-4 sm:space-y-5">
                        <div class="border-b border-slate-200 pb-3">
                            <h3 class="text-sm font-semibold text-slate-800">Pengaturan Nomor Halaman</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Tentukan posisi, format angka, dan nomor awal pada dokumen</p>
                        </div>

                        {{-- Step 1: Position Grid --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-2">Posisi Nomor Halaman</label>
                            <div class="grid grid-cols-3 gap-2">
                                {{-- Top Left --}}
                                <button type="button" 
                                        wire:click="$set('position', 'top-left')"
                                        class="p-2.5 rounded-lg border text-xs text-center transition-all cursor-pointer {{ $position === 'top-left' ? 'border-primary bg-primary/5 text-primary font-medium' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                                    <span>Atas Kiri</span>
                                </button>
                                {{-- Top Center --}}
                                <button type="button" 
                                        wire:click="$set('position', 'top-center')"
                                        class="p-2.5 rounded-lg border text-xs text-center transition-all cursor-pointer {{ $position === 'top-center' ? 'border-primary bg-primary/5 text-primary font-medium' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                                    <span>Atas Tengah</span>
                                </button>
                                {{-- Top Right --}}
                                <button type="button" 
                                        wire:click="$set('position', 'top-right')"
                                        class="p-2.5 rounded-lg border text-xs text-center transition-all cursor-pointer {{ $position === 'top-right' ? 'border-primary bg-primary/5 text-primary font-medium' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                                    <span>Atas Kanan</span>
                                </button>
                                {{-- Bottom Left --}}
                                <button type="button" 
                                        wire:click="$set('position', 'bottom-left')"
                                        class="p-2.5 rounded-lg border text-xs text-center transition-all cursor-pointer {{ $position === 'bottom-left' ? 'border-primary bg-primary/5 text-primary font-medium' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                                    <span>Bawah Kiri</span>
                                </button>
                                {{-- Bottom Center (Recommended) --}}
                                <button type="button" 
                                        wire:click="$set('position', 'bottom-center')"
                                        class="p-2.5 rounded-lg border text-xs text-center transition-all cursor-pointer {{ $position === 'bottom-center' ? 'border-primary bg-primary/5 text-primary font-medium ring-1 ring-primary' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                                    <span>Bawah Tengah</span>
                                </button>
                                {{-- Bottom Right --}}
                                <button type="button" 
                                        wire:click="$set('position', 'bottom-right')"
                                        class="p-2.5 rounded-lg border text-xs text-center transition-all cursor-pointer {{ $position === 'bottom-right' ? 'border-primary bg-primary/5 text-primary font-medium' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                                    <span>Bawah Kanan</span>
                                </button>
                            </div>
                        </div>

                        {{-- Step 2: Format Style --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-2">Format Teks Penomoran</label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                <button type="button" 
                                        wire:click="$set('format', 'page_n_of_total')"
                                        class="p-2.5 rounded-lg border text-xs text-left transition-all cursor-pointer {{ $format === 'page_n_of_total' ? 'border-primary bg-primary/5 text-primary font-medium' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                                    <span class="block">Halaman X dari Y</span>
                                    <span class="text-xs text-slate-400 block mt-0.5">Hal 1 dari {{ $totalPages }}</span>
                                </button>

                                <button type="button" 
                                        wire:click="$set('format', 'n_slash_total')"
                                        class="p-2.5 rounded-lg border text-xs text-left transition-all cursor-pointer {{ $format === 'n_slash_total' ? 'border-primary bg-primary/5 text-primary font-medium' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                                    <span class="block">X / Y</span>
                                    <span class="text-xs text-slate-400 block mt-0.5">1 / {{ $totalPages }}</span>
                                </button>

                                <button type="button" 
                                        wire:click="$set('format', 'page_n')"
                                        class="p-2.5 rounded-lg border text-xs text-left transition-all cursor-pointer {{ $format === 'page_n' ? 'border-primary bg-primary/5 text-primary font-medium' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                                    <span class="block">Halaman X</span>
                                    <span class="text-xs text-slate-400 block mt-0.5">Halaman 1</span>
                                </button>

                                <button type="button" 
                                        wire:click="$set('format', 'n')"
                                        class="p-2.5 rounded-lg border text-xs text-left transition-all cursor-pointer {{ $format === 'n' ? 'border-primary bg-primary/5 text-primary font-medium' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                                    <span class="block">Hanya Angka</span>
                                    <span class="text-xs text-slate-400 block mt-0.5">1, 2, 3...</span>
                                </button>

                                <button type="button" 
                                        wire:click="$set('format', 'roman_lower')"
                                        class="p-2.5 rounded-lg border text-xs text-left transition-all cursor-pointer {{ $format === 'roman_lower' ? 'border-primary bg-primary/5 text-primary font-medium' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                                    <span class="block">Romawi Kecil</span>
                                    <span class="text-xs text-slate-400 block mt-0.5">i, ii, iii...</span>
                                </button>

                                <button type="button" 
                                        wire:click="$set('format', 'roman_upper')"
                                        class="p-2.5 rounded-lg border text-xs text-left transition-all cursor-pointer {{ $format === 'roman_upper' ? 'border-primary bg-primary/5 text-primary font-medium' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                                    <span class="block">Romawi Besar</span>
                                    <span class="text-xs text-slate-400 block mt-0.5">I, II, III...</span>
                                </button>
                            </div>
                        </div>

                        {{-- Step 3: Start Page & Number Row --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-3 border-t border-slate-100">
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1.5">Mulai dari Halaman ke-</label>
                                <input type="number" 
                                       wire:model.live.debounce.300ms="startPage"
                                       min="1" 
                                       max="{{ $totalPages }}"
                                       class="form-input h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-700 focus:border-primary focus:ring-1 focus:ring-primary" />
                                <span class="text-xs text-slate-400 mt-1 block">Atur ke 2 jika halaman 1 adalah cover/sampul</span>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1.5">Nomor Awal Ditampilkan</label>
                                <input type="number" 
                                       wire:model.live.debounce.300ms="firstNumber"
                                       min="1"
                                       class="form-input h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-700 focus:border-primary focus:ring-1 focus:ring-primary" />
                                <span class="text-xs text-slate-400 mt-1 block">Angka pertama yang tertulis di halaman</span>
                            </div>
                        </div>

                        {{-- Step 4: Font Size & Color --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-3 border-t border-slate-100">
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1.5">Ukuran Huruf (Font Size)</label>
                                <select wire:model.live="fontSize"
                                        class="form-select h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-700 focus:border-primary focus:ring-1 focus:ring-primary">
                                    <option value="8">8 pt (Sangat Kecil)</option>
                                    <option value="9">9 pt (Kecil)</option>
                                    <option value="10">10 pt (Standar / Rekomendasi)</option>
                                    <option value="11">11 pt (Sedang)</option>
                                    <option value="12">12 pt (Besar)</option>
                                    <option value="14">14 pt (Sangat Besar)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1.5">Warna Teks</label>
                                <div class="flex items-center space-x-2">
                                    <input type="color" 
                                           wire:model.live="fontColor"
                                           class="size-9 rounded-lg border border-slate-300 p-0.5 cursor-pointer bg-white" />
                                    <input type="text" 
                                           wire:model.live.debounce.300ms="fontColor"
                                           class="form-input h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-700 focus:border-primary focus:ring-1 focus:ring-primary uppercase font-mono" />
                                </div>
                            </div>
                        </div>

                        {{-- Action Submit Button --}}
                        <div class="pt-4 border-t border-slate-200 flex items-center justify-between">
                            <span class="text-xs text-slate-500">
                                Proses instan & lossless
                            </span>
                            <button type="button" 
                                    wire:click="process"
                                    wire:loading.attr="disabled"
                                    class="btn h-9 rounded-full bg-primary font-medium text-white shadow-xs text-xs px-6 hover:bg-primary-focus cursor-pointer disabled:opacity-50 flex items-center space-x-2 transition-all">
                                <span wire:loading.remove wire:target="process" class="flex items-center space-x-2">
                                    <x-lucide-hash class="size-4" />
                                    <span>Terapkan Nomor Halaman</span>
                                </span>
                                <span wire:loading wire:target="process" class="flex items-center space-x-2">
                                    <x-lucide-loader-2 class="size-4 animate-spin" />
                                    <span>Memproses PDF...</span>
                                </span>
                            </button>
                        </div>
                    </div>
                @endif

            @endif
        </div>

        {{-- Right: Live Preview & Result Area (5 Columns) --}}
        <div class="col-span-12 lg:col-span-5 space-y-4 sm:space-y-5 lg:space-y-6">
            <div class="lg:sticky lg:top-20 space-y-4 sm:space-y-5">
                
                {{-- Live Preview Card --}}
                <div class="card p-4 sm:p-5">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-3 mb-4">
                        <div class="flex items-center space-x-2">
                            <h3 class="text-sm font-semibold text-slate-800">Pratinjau Halaman</h3>
                        </div>
                        <div class="badge rounded-full bg-success/15 text-success px-2.5 py-0.5 text-xs font-medium">
                            Live Sync
                        </div>
                    </div>

                    {{-- PDF Page Canvas Box --}}
                    <div class="relative flex items-center justify-center p-4 bg-slate-100 rounded-xl border border-slate-200 min-h-[320px]">
                        
                        {{-- Loading Overlay --}}
                        <div wire:loading wire:target="updated, setPreviewPage, file" 
                             class="absolute inset-0 bg-white/75 backdrop-blur-[1px] rounded-xl flex items-center justify-center z-10 transition-all">
                            <div class="flex flex-col items-center space-y-1.5">
                                <x-lucide-loader-2 class="size-6 text-primary animate-spin" />
                                <span class="text-xs font-medium text-slate-600">Merender Pratinjau...</span>
                            </div>
                        </div>

                        {{-- Preview Image --}}
                        @if ($previewUri)
                            <div class="relative bg-white shadow-md rounded border border-slate-200 max-h-[420px] overflow-hidden">
                                <img src="{{ $previewUri }}" 
                                     alt="Pratinjau Halaman PDF" 
                                     class="max-h-[410px] w-auto mx-auto object-contain transition-all duration-150" />
                            </div>
                        @else
                            <div class="text-center py-12">
                                <x-lucide-file-text class="size-12 text-slate-300 mx-auto mb-2" />
                                <span class="text-xs text-slate-400 block">Unggah PDF untuk melihat pratinjau</span>
                            </div>
                        @endif
                    </div>

                    {{-- Page Navigation Controls --}}
                    @if ($totalPages > 1)
                        <div class="mt-3.5 flex items-center justify-between pt-2 border-t border-slate-100">
                            <button type="button" 
                                    wire:click="setPreviewPage({{ $previewPage - 1 }})"
                                    @if($previewPage <= 1) disabled @endif
                                    class="btn h-8 rounded-full border border-slate-200 text-xs px-3 font-medium text-slate-600 hover:bg-slate-50 disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer flex items-center space-x-1">
                                <x-lucide-chevron-left class="size-3.5" />
                                <span>Sebelumnya</span>
                            </button>

                            <span class="text-xs font-medium text-slate-700">
                                Halaman {{ $previewPage }} dari {{ $totalPages }}
                            </span>

                            <button type="button" 
                                    wire:click="setPreviewPage({{ $previewPage + 1 }})"
                                    @if($previewPage >= $totalPages) disabled @endif
                                    class="btn h-8 rounded-full border border-slate-200 text-xs px-3 font-medium text-slate-600 hover:bg-slate-50 disabled:opacity-30 disabled:cursor-not-allowed cursor-pointer flex items-center space-x-1">
                                <span>Berikutnya</span>
                                <x-lucide-chevron-right class="size-3.5" />
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Result / Download Card (Appears when Completed) --}}
                @if ($resultPath)
                    <div class="card p-4 sm:p-5 border-2 border-primary/20 bg-primary/5">
                        <div class="flex items-center justify-between pb-3 border-b border-primary/10 mb-3.5">
                            <div class="flex items-center space-x-2">
                                <div class="flex size-6 items-center justify-center rounded-full bg-primary text-white">
                                    <x-lucide-check class="size-3.5" />
                                </div>
                                <h4 class="text-xs font-semibold text-slate-800">Nomor Halaman Berhasil Diterapkan</h4>
                            </div>
                            <span class="badge rounded-full bg-primary/10 text-primary px-2.5 py-0.5 text-xs font-medium">
                                Siap Diunduh
                            </span>
                        </div>

                        <div class="space-y-2 mb-4 text-xs text-slate-600">
                            <div class="flex justify-between py-1 border-b border-slate-200/50">
                                <span>Halaman Diberi Nomor:</span>
                                <span class="font-medium text-slate-800">{{ $pagesProcessed }} Halaman</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-200/50">
                                <span>Ukuran Berkas:</span>
                                <span class="font-medium text-slate-800">{{ round($newSize / 1024, 1) }} KB</span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span>Nama File:</span>
                                <span class="font-medium text-slate-800 truncate max-w-[180px]">{{ $resultFilename }}</span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <button type="button" 
                                    wire:click="download"
                                    class="btn h-9 w-full rounded-full bg-primary font-medium text-white shadow-xs text-xs px-5 hover:bg-primary-focus cursor-pointer flex items-center justify-center space-x-2 transition-all">
                                <x-lucide-download class="size-4" />
                                <span>Unduh PDF Sekarang</span>
                            </button>

                            <button type="button" 
                                    wire:click="resetResult"
                                    class="btn h-9 w-full rounded-full border border-slate-300 font-medium text-slate-700 hover:bg-white text-xs px-4 cursor-pointer flex items-center justify-center space-x-1.5 transition-all">
                                <x-lucide-rotate-ccw class="size-3.5 text-slate-500" />
                                <span>Ubah Pengaturan & Proses Ulang</span>
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Panduan & Rekomendasi Box --}}
                <div class="card p-4 sm:p-5">
                    <h4 class="text-xs font-semibold text-slate-800 mb-2.5 flex items-center space-x-1.5">
                        <x-lucide-info class="size-4 text-primary" />
                        <span>Panduan Penomoran Dokumen</span>
                    </h4>
                    <ul class="space-y-2 text-xs text-slate-600 leading-relaxed">
                        <li class="flex items-start space-x-2">
                            <span class="size-1.5 rounded-full bg-primary mt-1.5 shrink-0"></span>
                            <span><strong>Lewati Halaman Cover:</strong> Jika dokumen memiliki halaman judul atau sampul tanpa nomor, atur <em>Mulai dari Halaman ke-</em> menjadi <strong>2</strong>.</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="size-1.5 rounded-full bg-primary mt-1.5 shrink-0"></span>
                            <span><strong>Lossless Vector:</strong> Penomoran disematkan langsung sebagai objek teks vektor asli tanpa rasterisasi gambar sehingga teks tetap tajam sempurna saat dicetak.</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="size-1.5 rounded-full bg-primary mt-1.5 shrink-0"></span>
                            <span><strong>Standar Skripsi / Laporan:</strong> Format <em>Halaman X dari Y</em> atau <em>X / Y</em> di posisi <strong>Bawah Tengah</strong> adalah standar resmi untuk dokumen formal.</span>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </div>
</div>
