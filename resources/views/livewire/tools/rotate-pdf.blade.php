<div>
    @section('page_description', 'Putar dokumen PDF secara permanen dan presisi. Putar semua halaman sekaligus atau pilih halaman tertentu (90°, 180°, 270°) dengan pratinjau instan.')

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

    {{-- ==================== 1. UPLOAD VIEW (Empty State) ==================== --}}
    @if (!$file && !$resultPath)
        <div class="card p-6 sm:p-8 max-w-3xl mx-auto space-y-6">
            <div class="text-center space-y-2">
                <div class="mask is-squircle flex size-20 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light mx-auto mb-3 shadow-md shadow-primary/10">
                    <x-lucide-rotate-cw class="size-10" />
                </div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-navy-50">
                    Rotate PDF
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-navy-300 max-w-lg mx-auto">
                    Putar halaman PDF Anda ke orientasi yang benar (90°, 180°, 270°). Putar semua halaman atau per halaman dengan pratinjau interaktif real-time.
                </p>
            </div>

            {{-- Quota Badge --}}
            <div class="flex items-center justify-center">
                @if(auth()->check() && auth()->user()->is_admin)
                    <div class="badge rounded-full space-x-1.5 bg-linear-to-r from-rose-500/15 via-purple-500/15 to-indigo-500/15 text-rose-600 dark:text-rose-400 border border-rose-500/20 px-3.5 py-1 text-xs font-bold shadow-xs flex items-center">
                        <x-lucide-shield-check class="size-4" />
                        <span>Akses: Unlimited (Admin VIP)</span>
                    </div>
                @elseif($remainingQuota !== null)
                    <div class="badge rounded-full space-x-2 bg-slate-150 px-3.5 py-1 text-slate-800 dark:bg-navy-600 dark:text-navy-100 text-xs font-semibold">
                        <span>Sisa Kuota:</span>
                        <span class="text-primary dark:text-accent-light font-bold">{{ $remainingQuota }} / {{ $monthlyLimit ?? 5 }}</span>
                    </div>
                @else
                    <div class="badge rounded-full space-x-2 bg-success/10 px-3.5 py-1 text-success text-xs font-semibold">
                        <span>Kuota:</span>
                        <span>{{ $isPro ? 'Unlimited (Pro)' : 'Tanpa Batas' }}</span>
                    </div>
                @endif
            </div>

            {{-- Upload Area --}}
            <div x-data="{ isUploading: false, progress: 0 }"
                 x-on:livewire-upload-start="isUploading = true; progress = 0"
                 x-on:livewire-upload-finish="isUploading = false"
                 x-on:livewire-upload-error="isUploading = false"
                 x-on:livewire-upload-progress="progress = $event.detail.progress">
                <label x-data="{ isDropping: false }"
                       x-on:dragover.prevent="isDropping = true"
                       x-on:dragleave.prevent="isDropping = false"
                       x-on:drop.prevent="isDropping = false; if($event.dataTransfer.files.length > 0) { $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change', { bubbles: true })) }"
                       :class="isDropping ? 'border-primary/50 bg-primary/5' : 'border-slate-300 dark:border-navy-450 hover:bg-slate-50 dark:hover:bg-navy-900/50'"
                       class="flex flex-col items-center justify-center w-full rounded-2xl border-2 border-dashed p-10 cursor-pointer transition-all text-center">
                    <div class="flex size-16 items-center justify-center rounded-2xl bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light mb-3">
                        <x-lucide-upload-cloud class="size-8" />
                    </div>
                    <p class="text-base font-bold text-slate-700 dark:text-navy-100">
                        Klik untuk memilih PDF <span class="font-normal text-slate-400">atau seret ke sini</span>
                    </p>
                    <p class="mt-1 text-xs text-slate-400 uppercase tracking-wider">PDF (Maks {{ $maxMb }}MB)</p>
                    <input x-ref="fileInput" wire:model="file" type="file" accept="application/pdf,.pdf" class="hidden" />
                </label>

                <div x-show="isUploading" x-transition class="w-full mt-4 rounded-xl bg-slate-100 p-4 dark:bg-navy-700/60 border border-slate-200 dark:border-navy-600 space-y-2">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-700 dark:text-navy-100">
                        <span class="flex items-center space-x-2">
                            <div class="spinner size-3.5 border-2 border-primary border-r-transparent rounded-full"></div>
                            <span>Mengunggah dokumen PDF...</span>
                        </span>
                        <span class="text-primary font-bold" x-text="progress + '%'"></span>
                    </div>
                    <div class="progress h-2 bg-slate-200 rounded-full overflow-hidden">
                        <div class="rounded-full bg-primary transition-all duration-150" :style="`width: ${progress}%`"></div>
                    </div>
                </div>
            </div>

            {{-- Feature Highlights --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-2 text-center text-xs">
                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-navy-750/60 border border-slate-200/70 dark:border-navy-650">
                    <span class="text-xl">🔄</span>
                    <p class="font-bold text-slate-700 dark:text-navy-100 mt-1">Putar Massal</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">Putar seluruh dokumen sekaligus</p>
                </div>
                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-navy-750/60 border border-slate-200/70 dark:border-navy-650">
                    <span class="text-xl">📑</span>
                    <p class="font-bold text-slate-700 dark:text-navy-100 mt-1">Per Halaman</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">Rotasi presisi halaman tertentu</p>
                </div>
                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-navy-750/60 border border-slate-200/70 dark:border-navy-650 col-span-2 sm:col-span-1">
                    <span class="text-xl">⚡</span>
                    <p class="font-bold text-slate-700 dark:text-navy-100 mt-1">Kualitas 100% Asli</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">Lossless tanpa penurunan teks/vektor</p>
                </div>
            </div>
        </div>
    @endif

    {{-- ==================== 2. RESULT VIEW (Success State) ==================== --}}
    @if ($resultPath)
        <div class="card p-6 sm:p-8 max-w-xl mx-auto text-center space-y-5 my-6 border border-success/30 bg-success/5">
            <div class="mask is-squircle flex size-16 items-center justify-center bg-success text-white mx-auto shadow-lg shadow-success/30">
                <x-lucide-check-circle class="size-8" />
            </div>
            <h3 class="text-xl font-black text-slate-800 dark:text-navy-50">PDF Berhasil Diputar!</h3>
            <p class="text-xs text-slate-500 dark:text-navy-300">
                Dokumen PDF telah diputar sesuai orientasi yang Anda pilih dan siap diunduh.
            </p>

            <div class="grid grid-cols-2 gap-3 text-left">
                <div class="p-3 rounded-xl bg-white dark:bg-navy-700 border border-slate-200 dark:border-navy-600">
                    <p class="text-[10px] uppercase font-bold text-slate-400">Total Halaman</p>
                    <p class="text-sm font-bold text-slate-700 dark:text-navy-100 mt-0.5">{{ $totalPages ?? $detectedPages }} Halaman</p>
                </div>
                <div class="p-3 rounded-xl bg-primary/5 border border-primary/20">
                    <p class="text-[10px] uppercase font-bold text-primary">Ukuran File</p>
                    <p class="text-sm font-bold text-primary mt-0.5">{{ number_format($newSize / 1024, 1) }} KB</p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-2.5 pt-2">
                <button wire:click="download" class="btn w-full h-11 rounded-lg bg-success font-bold text-white shadow-lg shadow-success/30 hover:bg-success-focus text-sm flex items-center justify-center space-x-2 cursor-pointer">
                    <x-lucide-download class="size-4.5" />
                    <span>Unduh PDF Hasil Rotasi</span>
                </button>
                <button wire:click="resetFile" class="btn w-full sm:w-auto h-11 px-5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 dark:border-navy-600 dark:text-navy-200 text-xs font-semibold cursor-pointer">
                    Putar Dokumen Lain
                </button>
            </div>
        </div>
    @endif

    {{-- ==================== 3. INTERACTIVE ROTATION WORKSPACE ==================== --}}
    @if ($file && !$resultPath)
        <div class="space-y-4">
            {{-- Top Control Toolbar --}}
            <div class="card p-3 sm:p-4 sticky top-4 z-20 shadow-lg border border-slate-200 dark:border-navy-700 bg-white/95 dark:bg-navy-800/95 backdrop-blur-md">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
                    {{-- Left: Document Info & Status --}}
                    <div class="flex items-center space-x-3">
                        <div class="mask is-squircle flex size-10 shrink-0 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light">
                            <x-lucide-file-text class="size-5" />
                        </div>
                        <div>
                            <div class="flex items-center space-x-2">
                                <h4 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-navy-100 truncate max-w-xs sm:max-w-sm">
                                    {{ $file->getClientOriginalName() }}
                                </h4>
                                <span class="badge rounded-full bg-slate-100 dark:bg-navy-600 text-slate-700 dark:text-navy-200 text-[10px] font-bold px-2 py-0.5">
                                    {{ $detectedPages }} Halaman
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-400 dark:text-navy-300">
                                @if($activeRotationsCount > 0)
                                    <span class="font-semibold text-primary dark:text-accent-light">{{ $activeRotationsCount }} halaman</span> siap diputar
                                @else
                                    Klik tombol rotasi untuk mengubah orientasi
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- Center / Right: Bulk Action Controls & Save Button --}}
                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 w-full md:w-auto justify-end">
                        {{-- Rotate All Left (90 CCW) --}}
                        <button wire:click="rotateAll('left')" 
                                type="button" 
                                class="btn h-9 px-2.5 sm:px-3 rounded-lg border border-slate-200 dark:border-navy-600 text-slate-700 dark:text-navy-200 hover:bg-slate-100 dark:hover:bg-navy-700 text-xs font-semibold space-x-1.5 cursor-pointer"
                                title="Putar Semua Halaman ke Kiri 90°">
                            <x-lucide-rotate-ccw class="size-3.5 text-primary dark:text-accent-light" />
                            <span>Kiri Semua</span>
                        </button>

                        {{-- Rotate All Right (90 CW) --}}
                        <button wire:click="rotateAll('right')" 
                                type="button" 
                                class="btn h-9 px-2.5 sm:px-3 rounded-lg border border-slate-200 dark:border-navy-600 text-slate-700 dark:text-navy-200 hover:bg-slate-100 dark:hover:bg-navy-700 text-xs font-semibold space-x-1.5 cursor-pointer"
                                title="Putar Semua Halaman ke Kanan 90°">
                            <x-lucide-rotate-cw class="size-3.5 text-primary dark:text-accent-light" />
                            <span>Kanan Semua</span>
                        </button>

                        {{-- Rotate 180 --}}
                        <button wire:click="setAllAngle(180)" 
                                type="button" 
                                class="btn h-9 px-2.5 sm:px-3 rounded-lg border border-slate-200 dark:border-navy-600 text-slate-700 dark:text-navy-200 hover:bg-slate-100 dark:hover:bg-navy-700 text-xs font-semibold space-x-1 cursor-pointer hidden sm:flex"
                                title="Putar 180° (Terbalik)">
                            <x-lucide-refresh-cw class="size-3.5 text-slate-500" />
                            <span>180°</span>
                        </button>

                        {{-- Reset All --}}
                        @if($activeRotationsCount > 0)
                            <button wire:click="resetAllRotations" 
                                    type="button" 
                                    class="btn size-9 p-0 rounded-lg border border-slate-200 dark:border-navy-600 text-slate-500 hover:text-error hover:bg-error/10 text-xs cursor-pointer"
                                    title="Reset Semua ke 0°">
                                <x-lucide-undo-2 class="size-4" />
                            </button>
                        @endif

                        <div class="h-6 w-px bg-slate-200 dark:bg-navy-600 mx-1 hidden sm:block"></div>

                        {{-- Save / Apply Button --}}
                        <button wire:click="rotate" 
                                wire:loading.attr="disabled"
                                class="btn h-9 px-4 rounded-lg bg-primary font-bold text-white shadow-md shadow-primary/30 hover:bg-primary-focus dark:bg-accent dark:shadow-accent/30 dark:hover:bg-accent-focus text-xs space-x-1.5 transition-all disabled:opacity-75 cursor-pointer">
                            <div wire:loading wire:target="rotate" class="spinner size-3.5 border-2 border-white border-r-transparent rounded-full"></div>
                            <span wire:loading.remove wire:target="rotate" class="flex items-center space-x-1.5">
                                <x-lucide-save class="size-3.5" />
                                <span>Terapkan & Simpan PDF</span>
                            </span>
                            <span wire:loading wire:target="rotate">Memproses...</span>
                        </button>

                        {{-- Change file button --}}
                        <button wire:click="resetFile" 
                                class="btn size-9 p-0 rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-100 dark:border-navy-600 dark:text-navy-300 cursor-pointer"
                                title="Ganti File PDF">
                            <x-lucide-x class="size-4" />
                        </button>
                    </div>
                </div>
            </div>

            {{-- Pages Grid Area --}}
            <div class="card p-4 sm:p-6 bg-slate-50/50 dark:bg-navy-900/50 border border-slate-200 dark:border-navy-700 min-h-[400px]">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-xs font-bold text-slate-600 dark:text-navy-200 uppercase tracking-wider">
                        Pratinjau Halaman Dokumen ({{ count($pages) }} Halaman):
                    </p>
                    <span class="text-[11px] text-slate-400 dark:text-navy-300">
                        Klik tombol ↺ / ↻ pada setiap kartu untuk memutar halaman tertentu
                    </span>
                </div>

                {{-- Responsive Grid --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                    @foreach ($pages as $p)
                        @php
                            $pageRot = $p['rotation'] ?? 0;
                            $isRotated = ($pageRot % 360 !== 0);
                        @endphp
                        <div class="group relative flex flex-col rounded-xl border {{ $isRotated ? 'border-primary dark:border-accent ring-2 ring-primary/20 dark:ring-accent/20 bg-primary/5 dark:bg-accent/5' : 'border-slate-200 dark:border-navy-600 bg-white dark:bg-navy-800' }} p-3 shadow-xs hover:shadow-md transition-all">
                            {{-- Card Header: Page Number & Angle Badge --}}
                            <div class="flex items-center justify-between mb-2">
                                <span class="badge rounded-md {{ $isRotated ? 'bg-primary text-white dark:bg-accent' : 'bg-slate-100 text-slate-700 dark:bg-navy-700 dark:text-navy-200' }} px-2 py-0.5 text-[10px] font-bold">
                                    Hal {{ $p['page'] }}
                                </span>
                                
                                <span class="badge rounded-md {{ $isRotated ? 'bg-primary/20 text-primary dark:bg-accent/20 dark:text-accent-light font-black' : 'text-slate-400 dark:text-navy-400 font-semibold' }} text-[10px] px-1.5 py-0.5">
                                    {{ $pageRot }}°
                                </span>
                            </div>

                            {{-- Card Body: Thumbnail Image with Smooth CSS Rotation Transition --}}
                            <div class="relative flex-1 flex items-center justify-center min-h-[160px] sm:min-h-[190px] p-2 bg-slate-100 dark:bg-navy-900 rounded-lg overflow-hidden border border-slate-200/60 dark:border-navy-700/60">
                                @if(!empty($p['thumbnail']))
                                    <img 
                                        src="{{ $p['thumbnail'] }}" 
                                        alt="Halaman {{ $p['page'] }}" 
                                        style="transform: rotate({{ $pageRot }}deg); transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);"
                                        class="max-h-[150px] sm:max-h-[175px] max-w-full object-contain rounded shadow-xs select-none pointer-events-none"
                                    />
                                @else
                                    <div class="flex flex-col items-center justify-center text-slate-400 text-xs">
                                        <x-lucide-file-text class="size-8 mb-1" />
                                        <span>Hal {{ $p['page'] }}</span>
                                    </div>
                                @endif

                                {{-- Quick Rotation Overlay on Hover --}}
                                <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2 backdrop-blur-[2px] rounded-lg">
                                    <button 
                                        wire:click="rotatePage({{ $p['page'] }}, 'left')" 
                                        type="button" 
                                        class="size-8 rounded-full bg-white text-slate-800 shadow-md hover:bg-slate-100 flex items-center justify-center cursor-pointer transition-transform active:scale-90"
                                        title="Putar Kiri 90°">
                                        <x-lucide-rotate-ccw class="size-4 text-primary" />
                                    </button>
                                    <button 
                                        wire:click="rotatePage({{ $p['page'] }}, 'right')" 
                                        type="button" 
                                        class="size-8 rounded-full bg-white text-slate-800 shadow-md hover:bg-slate-100 flex items-center justify-center cursor-pointer transition-transform active:scale-90"
                                        title="Putar Kanan 90°">
                                        <x-lucide-rotate-cw class="size-4 text-primary" />
                                    </button>
                                </div>
                            </div>

                            {{-- Card Footer: Action Buttons --}}
                            <div class="mt-2 pt-2 border-t border-slate-100 dark:border-navy-700 flex items-center justify-between gap-1">
                                <button 
                                    wire:click="rotatePage({{ $p['page'] }}, 'left')" 
                                    type="button" 
                                    class="btn h-7 px-2 rounded-md bg-slate-100 dark:bg-navy-700 text-slate-600 dark:text-navy-200 hover:bg-slate-200 text-[11px] font-semibold flex-1 flex items-center justify-center space-x-1 cursor-pointer"
                                    title="Putar Kiri 90°">
                                    <x-lucide-rotate-ccw class="size-3" />
                                    <span>-90°</span>
                                </button>

                                <button 
                                    wire:click="rotatePage({{ $p['page'] }}, 'right')" 
                                    type="button" 
                                    class="btn h-7 px-2 rounded-md bg-slate-100 dark:bg-navy-700 text-slate-600 dark:text-navy-200 hover:bg-slate-200 text-[11px] font-semibold flex-1 flex items-center justify-center space-x-1 cursor-pointer"
                                    title="Putar Kanan 90°">
                                    <x-lucide-rotate-cw class="size-3" />
                                    <span>+90°</span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Bottom Sticky Bar for Mobile / Convenience --}}
            <div class="card p-3.5 flex flex-col sm:flex-row items-center justify-between gap-3 border border-slate-200 dark:border-navy-700 bg-white dark:bg-navy-800 shadow-lg">
                <div class="text-xs text-slate-500 dark:text-navy-300 text-center sm:text-left">
                    <span class="font-bold text-slate-700 dark:text-navy-100">{{ count($pages) }} Halaman terdeteksi.</span>
                    @if($activeRotationsCount > 0)
                        Perubahan rotasi akan diterapkan pada <strong class="text-primary dark:text-accent-light">{{ $activeRotationsCount }} halaman</strong>.
                    @else
                        Semua halaman dalam orientasi awal (0°).
                    @endif
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button wire:click="resetFile" class="btn h-10 px-4 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 dark:border-navy-600 dark:text-navy-200 text-xs font-semibold flex-1 sm:flex-initial cursor-pointer">
                        Batal
                    </button>
                    <button wire:click="rotate" 
                            wire:loading.attr="disabled"
                            class="btn h-10 px-6 rounded-lg bg-primary font-bold text-white shadow-lg shadow-primary/30 hover:bg-primary-focus dark:bg-accent dark:shadow-accent/30 dark:hover:bg-accent-focus text-xs flex items-center justify-center space-x-2 flex-1 sm:flex-initial cursor-pointer disabled:opacity-75">
                        <div wire:loading wire:target="rotate" class="spinner size-3.5 border-2 border-white border-r-transparent rounded-full"></div>
                        <x-lucide-save wire:loading.remove wire:target="rotate" class="size-4" />
                        <span wire:loading.remove wire:target="rotate">Putar & Simpan PDF</span>
                        <span wire:loading wire:target="rotate">Menyimpan...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
