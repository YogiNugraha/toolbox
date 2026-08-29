<div>
    @section('page_description', 'Studio Editor PDF Interaktif: Edit & timpa teks, tutup teks lama (whiteout), gambar bebas, stabilo highlighter, import gambar/stempel, dan bubuhkan tanda tangan langsung di dokumen PDF.')

    {{-- CDN Script for Fabric.js --}}
    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
    @endpush

    {{-- Error Flash Alert --}}
    @if (!empty($errorMsg))
        <div class="alert flex rounded-lg border border-error px-4 py-4 text-error sm:px-5 mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 shrink-0 mr-2.5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span>{{ $errorMsg }}</span>
        </div>
    @endif

    {{-- Initial Upload View (Shown before a file is chosen) --}}
    @if (!$file && !$currentPageImage)
        <div class="card p-6 sm:p-8 max-w-3xl mx-auto space-y-6">
            <div class="text-center space-y-2">
                <div class="mask is-squircle flex size-20 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light mx-auto mb-3 shadow-md shadow-primary/10">
                    <x-lucide-file-edit class="size-10" />
                </div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-navy-50">
                    Visual PDF Studio & Editor Interaktif
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-navy-300 max-w-lg mx-auto">
                    Edit dokumen PDF Anda secara visual layaknya Adobe Acrobat: ketik teks baru, hapus teks lama, coret bebas, beri stabilo, tambahkan cap stempel, dan tanda tangan digital.
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
                        <span>Sisa Kuota Bulan Ini:</span>
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
                       :class="isDropping ? 'border-primary/50 bg-primary/5 dark:border-accent-light/50 dark:bg-accent-light/5' : 'border-slate-300 dark:border-navy-450 hover:bg-slate-50 dark:hover:bg-navy-900/50'"
                       class="flex flex-col items-center justify-center w-full rounded-2xl border-2 border-dashed p-10 cursor-pointer transition-all text-center">
                    
                    <div class="flex size-16 items-center justify-center rounded-2xl bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light mb-3">
                        <x-lucide-upload-cloud class="size-8" />
                    </div>
                    <p class="text-base font-bold text-slate-700 dark:text-navy-100">
                        Klik untuk memilih dokumen PDF <span class="font-normal text-slate-400 dark:text-navy-300">atau seret file ke sini</span>
                    </p>
                    <p class="mt-1 text-xs text-slate-400 dark:text-navy-300 uppercase tracking-wider">
                        DOKUMEN PDF (Maksimal {{ $maxMb }}MB)
                    </p>
                    
                    <input x-ref="fileInput" wire:model="file" type="file" accept="application/pdf,.pdf" class="hidden" />
                </label>

                {{-- Upload Progress Bar --}}
                <div x-show="isUploading" x-transition class="w-full mt-4 rounded-xl bg-slate-100 p-4 dark:bg-navy-700/60 border border-slate-200 dark:border-navy-600 space-y-2">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-700 dark:text-navy-100">
                        <span class="flex items-center space-x-2">
                            <div class="spinner size-3.5 border-2 border-primary border-r-transparent rounded-full dark:border-accent-light"></div>
                            <span>Mengunggah dokumen PDF ke Studio Editor...</span>
                        </span>
                        <span class="text-primary dark:text-accent-light font-bold" x-text="progress + '%'"></span>
                    </div>
                    <div class="progress h-2 bg-slate-200 dark:bg-navy-600 rounded-full overflow-hidden">
                        <div class="is-active rounded-full bg-primary dark:bg-accent transition-all duration-150" :style="`width: ${progress}%`"></div>
                    </div>
                </div>
            </div>

            {{-- Feature Badges --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2 text-center text-xs">
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-navy-750/60 border border-slate-200/70 dark:border-navy-650">
                    <span class="text-lg">🔤</span>
                    <p class="font-bold text-slate-700 dark:text-navy-100 mt-1">Ketik & Edit Teks</p>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-navy-750/60 border border-slate-200/70 dark:border-navy-650">
                    <span class="text-lg">⬜</span>
                    <p class="font-bold text-slate-700 dark:text-navy-100 mt-1">Whiteout / Hapus Teks</p>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-navy-750/60 border border-slate-200/70 dark:border-navy-650">
                    <span class="text-lg">✏️</span>
                    <p class="font-bold text-slate-700 dark:text-navy-100 mt-1">Draw & Stabilo</p>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-navy-750/60 border border-slate-200/70 dark:border-navy-650">
                    <span class="text-lg">🖼️</span>
                    <p class="font-bold text-slate-700 dark:text-navy-100 mt-1">Gambar & Tanda Tangan</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Result / Download State Modal / Card --}}
    @if ($resultPath)
        <div class="card p-6 sm:p-8 max-w-xl mx-auto text-center space-y-5 my-6 border border-success/30 bg-success/5">
            <div class="mask is-squircle flex size-16 items-center justify-center bg-success text-white mx-auto shadow-lg shadow-success/30">
                <x-lucide-check-circle class="size-8" />
            </div>
            <div>
                <h3 class="text-xl font-black text-slate-800 dark:text-navy-50">
                    Dokumen PDF Berhasil Diedit & Disimpan!
                </h3>
                <p class="text-xs text-slate-500 dark:text-navy-300 mt-1">
                    Seluruh penambahan teks, gambar, tanda tangan, dan modifikasi visual telah disatukan secara rapi.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 text-left">
                <div class="p-3 rounded-xl bg-white dark:bg-navy-700 border border-slate-200 dark:border-navy-600">
                    <p class="text-[10px] uppercase font-bold text-slate-400 dark:text-navy-300">Ukuran Asli</p>
                    <p class="text-sm font-bold text-slate-700 dark:text-navy-100 mt-0.5">
                        {{ number_format($originalSize / 1024, 1) }} KB
                    </p>
                </div>
                <div class="p-3 rounded-xl bg-primary/5 dark:bg-accent-light/5 border border-primary/20 dark:border-accent-light/20">
                    <p class="text-[10px] uppercase font-bold text-primary dark:text-accent-light">Ukuran Hasil</p>
                    <p class="text-sm font-bold text-primary dark:text-accent-light mt-0.5">
                        {{ number_format($newSize / 1024, 1) }} KB
                    </p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-2.5 pt-2">
                <button wire:click="download" class="btn w-full h-11 rounded-lg bg-success font-bold text-white shadow-lg shadow-success/30 hover:bg-success-focus text-xs sm:text-sm flex items-center justify-center space-x-2 cursor-pointer">
                    <x-lucide-download class="size-4.5" />
                    <span>Unduh Dokumen PDF (.pdf)</span>
                </button>
                <button wire:click="resetFile" class="btn w-full sm:w-auto h-11 px-5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 dark:border-navy-600 dark:text-navy-200 dark:hover:bg-navy-700 text-xs font-semibold cursor-pointer">
                    Edit Dokumen Baru
                </button>
            </div>
        </div>
    @endif

    {{-- Interactive Visual PDF Studio Canvas Workspace --}}
    <div x-data="pdfStudioEditor()"
         x-init="init()"
         x-show="currentPageImage && !resultPath"
         x-cloak
         class="space-y-4">
        
        {{-- Top Adobe Acrobat Style Toolbar --}}
        <div class="card p-2.5 sm:p-3 sticky top-4 z-30 shadow-xl border border-slate-200 dark:border-navy-700 bg-white/95 dark:bg-navy-800/95 backdrop-blur-md">
            <div class="flex flex-wrap items-center justify-between gap-2">
                
                {{-- Tools Left Group --}}
                <div class="flex flex-wrap items-center gap-1">
                    {{-- Select / Pan --}}
                    <button @click="setTool('select')" :class="currentTool === 'select' ? 'bg-primary text-white dark:bg-accent shadow-xs' : 'text-slate-600 hover:bg-slate-100 dark:text-navy-200 dark:hover:bg-navy-700'" class="btn size-9 p-0 rounded-lg transition-all cursor-pointer" title="Pilih & Geser Objek (V)">
                        <x-lucide-mouse-pointer class="size-4.5" />
                    </button>

                    <div class="h-6 w-px bg-slate-200 dark:bg-navy-600 mx-1"></div>

                    {{-- Add Text --}}
                    <button @click="setTool('text')" :class="currentTool === 'text' ? 'bg-primary text-white dark:bg-accent shadow-xs' : 'text-slate-600 hover:bg-slate-100 dark:text-navy-200 dark:hover:bg-navy-700'" class="btn h-9 px-2.5 rounded-lg text-xs font-bold space-x-1.5 transition-all cursor-pointer" title="Tambah / Ketik Teks Baru (T)">
                        <x-lucide-type class="size-4.5" />
                        <span class="hidden sm:inline">Teks</span>
                    </button>

                    {{-- Whiteout / Erase Text --}}
                    <button @click="setTool('whiteout')" :class="currentTool === 'whiteout' ? 'bg-primary text-white dark:bg-accent shadow-xs' : 'text-slate-600 hover:bg-slate-100 dark:text-navy-200 dark:hover:bg-navy-700'" class="btn h-9 px-2.5 rounded-lg text-xs font-bold space-x-1.5 transition-all cursor-pointer" title="Tarik Kotak untuk Tutup / Sensor Teks Lama (Whiteout)">
                        <x-lucide-eraser class="size-4.5 text-warning" />
                        <span class="hidden sm:inline">Whiteout</span>
                    </button>

                    {{-- Freehand Draw Pen --}}
                    <button @click="setTool('draw')" :class="currentTool === 'draw' ? 'bg-primary text-white dark:bg-accent shadow-xs' : 'text-slate-600 hover:bg-slate-100 dark:text-navy-200 dark:hover:bg-navy-700'" class="btn h-9 px-2.5 rounded-lg text-xs font-bold space-x-1.5 transition-all cursor-pointer" title="Pena Gambar / Coretan Bebas (P)">
                        <x-lucide-pen-tool class="size-4.5" />
                        <span class="hidden sm:inline">Pena</span>
                    </button>

                    {{-- Highlighter (Stabilo) --}}
                    <button @click="setTool('highlighter')" :class="currentTool === 'highlighter' ? 'bg-primary text-white dark:bg-accent shadow-xs' : 'text-slate-600 hover:bg-slate-100 dark:text-navy-200 dark:hover:bg-navy-700'" class="btn h-9 px-2.5 rounded-lg text-xs font-bold space-x-1.5 transition-all cursor-pointer" title="Stabilo / Highlighter Sorotan">
                        <x-lucide-highlighter class="size-4.5 text-amber-500" />
                        <span class="hidden sm:inline">Stabilo</span>
                    </button>

                    {{-- Shapes Dropdown or Rect --}}
                    <button @click="addShape('rect')" class="btn size-9 p-0 rounded-lg text-slate-600 hover:bg-slate-100 dark:text-navy-200 dark:hover:bg-navy-700 cursor-pointer" title="Tambah Kotak / Rectangle">
                        <x-lucide-square class="size-4.5" />
                    </button>
                    <button @click="addShape('circle')" class="btn size-9 p-0 rounded-lg text-slate-600 hover:bg-slate-100 dark:text-navy-200 dark:hover:bg-navy-700 cursor-pointer" title="Tambah Lingkaran / Circle">
                        <x-lucide-circle class="size-4.5" />
                    </button>

                    <div class="h-6 w-px bg-slate-200 dark:bg-navy-600 mx-1"></div>

                    {{-- Import Image / Stamp --}}
                    <label class="btn h-9 px-2.5 rounded-lg text-xs font-bold space-x-1.5 text-slate-600 hover:bg-slate-100 dark:text-navy-200 dark:hover:bg-navy-700 transition-all cursor-pointer" title="Import Foto / Logo / Cap Stempel">
                        <x-lucide-image-plus class="size-4.5 text-info" />
                        <span class="hidden sm:inline">Gambar</span>
                        <input type="file" @change="handleImageUpload($event)" accept="image/*" class="hidden" />
                    </label>

                    {{-- Signature Modal Trigger --}}
                    <button @click="openSignatureModal()" type="button" class="btn h-9 px-2.5 rounded-lg text-xs font-bold space-x-1.5 text-slate-600 hover:bg-slate-100 dark:text-navy-200 dark:hover:bg-navy-700 transition-all cursor-pointer" title="Buat & Tempel Tanda Tangan Digital">
                        <x-lucide-signature class="size-4.5 text-indigo-500" />
                        <span class="hidden sm:inline">Tanda Tangan</span>
                    </button>
                </div>

                {{-- Action Right Group --}}
                <div class="flex items-center gap-2">
                    {{-- Delete Selected Object --}}
                    <button @click="deleteSelected()" type="button" class="btn size-9 p-0 rounded-lg border border-error/30 text-error hover:bg-error/10 dark:border-error/40 cursor-pointer" title="Hapus Objek Terpilih (Delete / Backspace)">
                        <x-lucide-trash-2 class="size-4.5" />
                    </button>

                    {{-- Save & Export PDF Button --}}
                    <button @click="saveAndDownloadPdf()" 
                            :disabled="isSaving"
                            type="button" 
                            class="btn h-9 px-4 rounded-lg bg-primary font-bold text-white shadow-md shadow-primary/30 hover:bg-primary-focus dark:bg-accent dark:shadow-accent/30 dark:hover:bg-accent-focus text-xs space-x-1.5 cursor-pointer disabled:opacity-75">
                        <div x-show="isSaving" class="spinner size-4 border-2 border-white border-r-transparent rounded-full"></div>
                        <x-lucide-download x-show="!isSaving" class="size-4" />
                        <span x-text="isSaving ? 'Menyimpan...' : 'Simpan & Unduh PDF'"></span>
                    </button>

                    {{-- Reset / Close Button --}}
                    <button wire:click="resetFile" type="button" class="btn size-9 p-0 rounded-lg border border-slate-300 text-slate-500 hover:bg-slate-100 dark:border-navy-600 dark:text-navy-300 dark:hover:bg-navy-700 cursor-pointer" title="Tutup / Ganti Dokumen">
                        <x-lucide-x class="size-4.5" />
                    </button>
                </div>
            </div>

            {{-- Contextual Formatting Bar & Quick Adjust Controls --}}
            <div class="mt-2.5 pt-2 border-t border-slate-200 dark:border-navy-700 flex flex-wrap items-center justify-between gap-3 text-xs">
                {{-- Colors & Styles --}}
                <div class="flex flex-wrap items-center gap-3">
                    {{-- Color Palette --}}
                    <div class="flex items-center gap-1.5">
                        <span class="text-[11px] font-bold text-slate-500 dark:text-navy-300">Warna:</span>
                        <div class="flex items-center gap-1">
                            <template x-for="c in ['#000000', '#dc2626', '#2563eb', '#16a34a', '#eab308', '#ffffff']" :key="c">
                                <button @click="setColor(c)" 
                                        :style="`background-color: ${c}`" 
                                        :class="activeColor === c ? 'ring-2 ring-primary ring-offset-2 scale-110' : 'border border-slate-300 dark:border-navy-500'" 
                                        class="size-5.5 rounded-full cursor-pointer transition-all shadow-xs"></button>
                            </template>
                            <input type="color" x-model="activeColor" @input="updateActiveColor()" class="size-6 rounded-md border-0 p-0 cursor-pointer bg-transparent" />
                        </div>
                    </div>

                    {{-- Font Size Slider (When text or active tool is text) --}}
                    <div class="flex items-center gap-1.5">
                        <span class="text-[11px] font-bold text-slate-500 dark:text-navy-300">Ukuran Teks:</span>
                        <select x-model="fontSize" @change="updateFontSize()" class="form-select h-7.5 rounded-md border border-slate-300 bg-white px-2 text-xs dark:border-navy-500 dark:bg-navy-700 dark:text-navy-100">
                            <option value="12">12 pt (Kecil)</option>
                            <option value="14">14 pt</option>
                            <option value="16">16 pt (Standar)</option>
                            <option value="18">18 pt</option>
                            <option value="22">22 pt (Sedang)</option>
                            <option value="28">28 pt (Besar)</option>
                            <option value="36">36 pt (Judul)</option>
                        </select>
                    </div>

                    {{-- Stroke Width Slider (For Pen & Shapes) --}}
                    <div class="flex items-center gap-1.5">
                        <span class="text-[11px] font-bold text-slate-500 dark:text-navy-300">Tebal Garis:</span>
                        <input type="range" min="1" max="15" x-model="strokeWidth" @input="updateStrokeWidth()" class="w-20 accent-primary cursor-pointer" />
                        <span class="text-[10px] font-semibold text-slate-400" x-text="strokeWidth + 'px'"></span>
                    </div>

                    {{-- Edit Text Button (When Text is selected) --}}
                    <div x-show="activeObjectType === 'textbox' || activeObjectType === 'i-text'" class="pl-2 border-l border-slate-200 dark:border-navy-600">
                        <button @click="editTextContent()" type="button" class="btn h-7 px-2.5 text-[11px] font-bold rounded-lg bg-primary/10 text-primary hover:bg-primary/20 dark:bg-accent-light/10 dark:text-accent-light flex items-center gap-1.5 cursor-pointer shadow-2xs">
                            <x-lucide-edit-3 class="size-3.5" />
                            <span>Edit Teks (Klik 2x)</span>
                        </button>
                    </div>

                    {{-- Quick Size Stretch Buttons for Selected Object --}}
                    <div x-show="hasActiveObject && activeObjectType !== 'textbox'" class="flex items-center gap-1 pl-2 border-l border-slate-200 dark:border-navy-600">
                        <span class="text-[10px] font-bold text-slate-400">Atur Ukuran:</span>
                        <button @click="adjustSelectedSize(30, 0)" type="button" class="btn h-6 px-1.5 text-[10px] font-bold rounded border border-slate-200 hover:bg-slate-100 dark:border-navy-600 dark:text-navy-200" title="Lebarkan Kotak / Teks">
                            ↔️ +Lebar
                        </button>
                        <button @click="adjustSelectedSize(-30, 0)" type="button" class="btn h-6 px-1.5 text-[10px] font-bold rounded border border-slate-200 hover:bg-slate-100 dark:border-navy-600 dark:text-navy-200" title="Sempitkan Kotak / Teks">
                            ↔️ -Lebar
                        </button>
                        <button @click="adjustSelectedSize(0, 10)" type="button" class="btn h-6 px-1.5 text-[10px] font-bold rounded border border-slate-200 hover:bg-slate-100 dark:border-navy-600 dark:text-navy-200" title="Tinggikan Kotak">
                            ↕️ +Tinggi
                        </button>
                        <button @click="adjustSelectedSize(0, -10)" type="button" class="btn h-6 px-1.5 text-[10px] font-bold rounded border border-slate-200 hover:bg-slate-100 dark:border-navy-600 dark:text-navy-200" title="Pendekkan Kotak">
                            ↕️ -Tinggi
                        </button>
                    </div>
                </div>

                {{-- Status & Tips --}}
                <div class="text-[11px] text-slate-400 dark:text-navy-300 flex items-center gap-1.5">
                    <span class="badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light text-[10px] font-bold px-2 py-0.5" x-text="'Mode: ' + currentTool.toUpperCase()"></span>
                    <span class="hidden lg:inline">· Klik 2x pada teks untuk mengetik / mengedit</span>
                </div>
            </div>
        </div>

        {{-- Center PDF Canvas Workspace --}}
        <div class="card p-4 sm:p-6 bg-slate-100 dark:bg-navy-900 border border-slate-200 dark:border-navy-700 min-h-[600px] flex flex-col items-center justify-center overflow-x-auto relative">
            
            {{-- Canvas Container with Shadow and Aspect Ratio --}}
            <div id="pdf-workspace-wrapper" class="relative bg-white shadow-2xl rounded-sm border border-slate-300 dark:border-navy-600 transition-all mx-auto overflow-hidden">
                <canvas id="fabric-overlay-canvas"></canvas>
            </div>

            {{-- Loading Page Spinner --}}
            <div x-show="isPageRendering" class="absolute inset-0 bg-white/70 dark:bg-navy-900/70 backdrop-blur-xs flex flex-col items-center justify-center z-20">
                <div class="spinner size-8 border-3 border-primary border-r-transparent rounded-full dark:border-accent-light mb-2"></div>
                <p class="text-xs font-bold text-slate-700 dark:text-navy-100">Memuat Halaman PDF...</p>
            </div>
        </div>

        {{-- Bottom Floating Page & Zoom Navigator --}}
        <div class="card p-2 sm:p-2.5 max-w-xl mx-auto sticky bottom-4 z-30 shadow-xl border border-slate-200 dark:border-navy-700 bg-white/95 dark:bg-navy-800/95 backdrop-blur-md flex items-center justify-between gap-2 text-xs">
            {{-- Page Navigation --}}
            <div class="flex items-center gap-1.5">
                <button @click="prevPage()" :disabled="currentPage <= 1 || isPageRendering" class="btn size-8 p-0 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed dark:border-navy-600 dark:text-navy-200 dark:hover:bg-navy-700 cursor-pointer" title="Halaman Sebelumnya">
                    <x-lucide-chevron-left class="size-4" />
                </button>
                <span class="font-bold text-slate-700 dark:text-navy-100 px-2">
                    Hal <span x-text="currentPage"></span> / <span x-text="totalPages"></span>
                </span>
                <button @click="nextPage()" :disabled="currentPage >= totalPages || isPageRendering" class="btn size-8 p-0 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed dark:border-navy-600 dark:text-navy-200 dark:hover:bg-navy-700 cursor-pointer" title="Halaman Selanjutnya">
                    <x-lucide-chevron-right class="size-4" />
                </button>
            </div>

            {{-- Zoom Controls --}}
            <div class="flex items-center gap-1">
                <button @click="zoomOut()" class="btn size-8 p-0 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 dark:border-navy-600 dark:text-navy-200 dark:hover:bg-navy-700 cursor-pointer" title="Perkecil (-)">
                    <x-lucide-zoom-out class="size-4" />
                </button>
                <span class="font-bold text-slate-700 dark:text-navy-100 px-1.5" x-text="Math.round(zoomFactor * 100) + '%'"></span>
                <button @click="zoomIn()" class="btn size-8 p-0 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 dark:border-navy-600 dark:text-navy-200 dark:hover:bg-navy-700 cursor-pointer" title="Perbesar (+)">
                    <x-lucide-zoom-in class="size-4" />
                </button>
                <button @click="fitWidth()" class="btn h-8 px-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 dark:border-navy-600 dark:text-navy-200 dark:hover:bg-navy-700 text-[11px] font-semibold cursor-pointer">
                    Fit
                </button>
            </div>
        </div>

        {{-- Digital Signature Modal --}}
        <div x-show="showSignatureModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="showSignatureModal = false" class="card p-6 w-full max-w-md bg-white dark:bg-navy-800 shadow-2xl border border-slate-200 dark:border-navy-700 rounded-2xl space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-navy-700">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-navy-100 flex items-center gap-2">
                        <x-lucide-signature class="size-4.5 text-primary dark:text-accent-light" />
                        <span>Bubuhkan Tanda Tangan Digital</span>
                    </h3>
                    <button @click="showSignatureModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                        <x-lucide-x class="size-4.5" />
                    </button>
                </div>

                <div class="space-y-2">
                    <p class="text-xs text-slate-500 dark:text-navy-300">Goreskan tanda tangan Anda di area kotak di bawah ini:</p>
                    <div class="rounded-xl border-2 border-dashed border-slate-300 dark:border-navy-500 bg-slate-50 dark:bg-navy-700/50 p-1 flex justify-center">
                        <canvas id="signature-pad" width="380" height="160" class="cursor-crosshair bg-white dark:bg-navy-800 rounded-lg shadow-inner"></canvas>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <button @click="clearSignaturePad()" type="button" class="btn h-9 px-3 text-xs font-semibold text-slate-500 hover:bg-slate-100 dark:text-navy-300 dark:hover:bg-navy-700 rounded-lg cursor-pointer">
                        Bersihkan Goresan
                    </button>
                    <div class="flex items-center gap-2">
                        <button @click="showSignatureModal = false" type="button" class="btn h-9 px-4 text-xs font-semibold border border-slate-300 dark:border-navy-600 text-slate-600 dark:text-navy-200 rounded-lg cursor-pointer">
                            Batal
                        </button>
                        <button @click="applySignature()" type="button" class="btn h-9 px-4 text-xs font-bold bg-primary text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus rounded-lg shadow-md cursor-pointer">
                            Tempel ke Dokumen
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Alpine Studio Editor Controller Engine --}}
    <script>
        function pdfStudioEditor() {
            return {
                currentPageImage: @entangle('currentPageImage'),
                totalPages: @entangle('detectedPages'),
                currentPage: @entangle('currentPage'),
                resultPath: @entangle('resultPath'),
                pageDimensions: @entangle('pageDimensions'),
                zoomFactor: 1.0,
                currentTool: 'select',
                activeColor: '#000000',
                fontSize: '16',
                strokeWidth: 3,
                isPageRendering: false,
                isSaving: false,
                hasActiveObject: false,
                activeObjectType: null,
                showSignatureModal: false,
                fabricCanvas: null,
                pageOverlays: {}, // Stores fabric canvas JSON or images per page
                rawPageWidth: 800,
                rawPageHeight: 1100,
                isDrawingRect: false,
                drawRectStart: null,
                activeDrawRectObj: null,

                async init() {
                    await this.ensureFabricLoaded();

                    // Global Fabric control styling: Large circle handles, visible side handles for easy width & height stretch
                    if (window.fabric) {
                        fabric.Object.prototype.set({
                            cornerColor: '#2563eb',
                            cornerStrokeColor: '#ffffff',
                            cornerStyle: 'circle',
                            cornerSize: 12,
                            touchCornerSize: 24,
                            transparentCorners: false,
                            borderColor: '#2563eb',
                            borderScaleFactor: 2,
                            borderDashArray: [4, 4],
                            padding: 2,
                            lockUniScaling: false,
                            centeredScaling: false
                        });
                    }

                    this.$wire.$on('pdf-loaded', (event) => {
                        const data = event[0] || event;
                        this.currentPage = data.page || 1;
                        this.totalPages = data.totalPages || 1;
                        this.rawPageWidth = data.width || 800;
                        this.rawPageHeight = data.height || 1100;
                        this.pageOverlays = {};
                        this.loadCurrentPageToCanvas(data.image, this.rawPageWidth, this.rawPageHeight);
                    });

                    this.$wire.$on('page-rendered', (event) => {
                        const data = event[0] || event;
                        this.currentPage = data.page || this.currentPage;
                        this.rawPageWidth = data.width || this.rawPageWidth;
                        this.rawPageHeight = data.height || this.rawPageHeight;
                        this.loadCurrentPageToCanvas(data.image, this.rawPageWidth, this.rawPageHeight);
                    });

                    this.$wire.$on('pdf-reset', () => {
                        if (this.fabricCanvas) {
                            this.fabricCanvas.dispose();
                            this.fabricCanvas = null;
                        }
                        this.pageOverlays = {};
                        this.hasActiveObject = false;
                        this.activeObjectType = null;
                    });

                    if (this.currentPageImage) {
                        this.rawPageWidth = this.pageDimensions?.width || 800;
                        this.rawPageHeight = this.pageDimensions?.height || 1100;
                        this.loadCurrentPageToCanvas(this.currentPageImage, this.rawPageWidth, this.rawPageHeight);
                    }

                    // Keyboard shortcuts (Delete / Backspace to remove selected object)
                    window.addEventListener('keydown', (e) => {
                        if ((e.key === 'Delete' || e.key === 'Backspace') && this.fabricCanvas) {
                            const activeObj = this.fabricCanvas.getActiveObject();
                            if (activeObj && !activeObj.isEditing) {
                                this.deleteSelected();
                            }
                        }
                    });
                },

                async ensureFabricLoaded() {
                    if (!window.fabric) {
                        await new Promise((resolve, reject) => {
                            const s = document.createElement('script');
                            s.src = 'https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js';
                            s.onload = resolve;
                            s.onerror = reject;
                            document.head.appendChild(s);
                        });
                    }
                },

                async loadCurrentPageToCanvas(imgSrc, origW, origH) {
                    await this.ensureFabricLoaded();
                    if (!imgSrc) return;
                    this.isPageRendering = true;

                    const baseDisplayWidth = Math.min(850, window.innerWidth - 60);
                    const scaleRatio = (baseDisplayWidth / origW) * this.zoomFactor;
                    const canvasW = Math.round(origW * scaleRatio);
                    const canvasH = Math.round(origH * scaleRatio);

                    const wrapper = document.getElementById('pdf-workspace-wrapper');
                    if (wrapper) {
                        wrapper.style.width = canvasW + 'px';
                        wrapper.style.height = canvasH + 'px';
                    }

                    if (!this.fabricCanvas) {
                        this.fabricCanvas = new fabric.Canvas('fabric-overlay-canvas', {
                            width: canvasW,
                            height: canvasH,
                            selection: true,
                            uniScaleTransform: true, // Enables free horizontal/vertical stretching without Shift key
                        });

                        // Selection change listeners
                        const updateActiveState = () => {
                            const active = this.fabricCanvas.getActiveObject();
                            this.hasActiveObject = !!active;
                            this.activeObjectType = active ? active.type : null;
                        };

                        this.fabricCanvas.on('selection:created', updateActiveState);
                        this.fabricCanvas.on('selection:updated', updateActiveState);
                        this.fabricCanvas.on('selection:cleared', () => {
                            this.hasActiveObject = false;
                            this.activeObjectType = null;
                        });

                        // Explicit Double-Click Handler on Textbox
                        this.fabricCanvas.on('mouse:dblclick', (opt) => {
                            if (opt.target && (opt.target.type === 'textbox' || opt.target.type === 'i-text')) {
                                this.setTool('select');
                                opt.target.set({
                                    editable: true,
                                    selectable: true,
                                    evented: true,
                                });
                                opt.target.enterEditing();
                                opt.target.selectAll();
                                if (opt.target.hiddenTextarea) {
                                    opt.target.hiddenTextarea.focus();
                                }
                                this.fabricCanvas.renderAll();
                            }
                        });

                        // Drag-to-draw / Click listener
                        this.fabricCanvas.on('mouse:down', (opt) => {
                            if (this.currentTool === 'text') {
                                if (opt.target && (opt.target.type === 'textbox' || opt.target.type === 'i-text')) {
                                    this.setTool('select');
                                    opt.target.enterEditing();
                                    opt.target.selectAll();
                                    if (opt.target.hiddenTextarea) {
                                        opt.target.hiddenTextarea.focus();
                                    }
                                    this.fabricCanvas.renderAll();
                                } else {
                                    this.addTextAtPosition(opt.pointer.x, opt.pointer.y);
                                }
                            } else if (this.currentTool === 'whiteout') {
                                this.isDrawingRect = true;
                                this.drawRectStart = { x: opt.pointer.x, y: opt.pointer.y };
                                this.activeDrawRectObj = new fabric.Rect({
                                    left: opt.pointer.x,
                                    top: opt.pointer.y,
                                    width: 1,
                                    height: 1,
                                    fill: '#ffffff',
                                    stroke: '#cbd5e1',
                                    strokeWidth: 1,
                                    lockUniScaling: false,
                                    centeredScaling: false,
                                    cornerColor: '#2563eb',
                                    cornerStrokeColor: '#ffffff',
                                    cornerStyle: 'circle',
                                    cornerSize: 12,
                                    touchCornerSize: 24,
                                    transparentCorners: false
                                });
                                this.enableAllControls(this.activeDrawRectObj);
                                this.fabricCanvas.add(this.activeDrawRectObj);
                            }
                        });

                        this.fabricCanvas.on('mouse:move', (opt) => {
                            if (this.isDrawingRect && this.activeDrawRectObj && this.drawRectStart) {
                                const w = Math.abs(opt.pointer.x - this.drawRectStart.x);
                                const h = Math.abs(opt.pointer.y - this.drawRectStart.y);
                                const left = Math.min(this.drawRectStart.x, opt.pointer.x);
                                const top = Math.min(this.drawRectStart.y, opt.pointer.y);
                                this.activeDrawRectObj.set({ left, top, width: Math.max(5, w), height: Math.max(5, h) });
                                this.fabricCanvas.renderAll();
                            }
                        });

                        this.fabricCanvas.on('mouse:up', () => {
                            if (this.isDrawingRect && this.activeDrawRectObj) {
                                this.isDrawingRect = false;
                                if (this.activeDrawRectObj.width < 15 && this.activeDrawRectObj.height < 15) {
                                    // Default click fallback size
                                    this.activeDrawRectObj.set({ width: 220, height: 32 });
                                }
                                this.activeDrawRectObj.setCoords();
                                this.fabricCanvas.setActiveObject(this.activeDrawRectObj);
                                this.fabricCanvas.renderAll();
                                this.activeDrawRectObj = null;
                                this.drawRectStart = null;
                                this.setTool('select');
                            }
                        });

                    } else {
                        this.fabricCanvas.setWidth(canvasW);
                        this.fabricCanvas.setHeight(canvasH);
                        this.fabricCanvas.clear();
                    }

                    fabric.Image.fromURL(imgSrc, (img) => {
                        img.set({
                            scaleX: scaleRatio,
                            scaleY: scaleRatio,
                            originX: 'left',
                            originY: 'top',
                            selectable: false,
                            evented: false,
                        });

                        this.fabricCanvas.setBackgroundImage(img, () => {
                            // Restore overlays if any saved for this page
                            if (this.pageOverlays[this.currentPage]) {
                                this.fabricCanvas.loadFromJSON(this.pageOverlays[this.currentPage], () => {
                                    this.fabricCanvas.getObjects().forEach(obj => {
                                        this.enableAllControls(obj);
                                        if (obj.type === 'textbox' || obj.type === 'i-text') {
                                            obj.set({ editable: true });
                                        }
                                    });
                                    this.fabricCanvas.renderAll();
                                    this.applyToolMode();
                                    this.isPageRendering = false;
                                });
                            } else {
                                this.fabricCanvas.renderAll();
                                this.applyToolMode();
                                this.isPageRendering = false;
                            }
                        });
                    });
                },

                enableAllControls(obj) {
                    if (!obj) return;
                    obj.set({
                        lockUniScaling: false,
                        centeredScaling: false,
                        hasControls: true,
                        hasBorders: true,
                        cornerColor: '#2563eb',
                        cornerStrokeColor: '#ffffff',
                        cornerStyle: 'circle',
                        cornerSize: 12,
                        touchCornerSize: 24,
                        transparentCorners: false
                    });
                    obj.setControlVisible('mt', true);
                    obj.setControlVisible('mb', true);
                    obj.setControlVisible('ml', true);
                    obj.setControlVisible('mr', true);
                    obj.setControlVisible('tl', true);
                    obj.setControlVisible('tr', true);
                    obj.setControlVisible('bl', true);
                    obj.setControlVisible('br', true);
                    obj.setControlVisible('mtr', true);
                },

                editTextContent() {
                    const activeObj = this.fabricCanvas?.getActiveObject();
                    if (activeObj && (activeObj.type === 'textbox' || activeObj.type === 'i-text')) {
                        activeObj.set({ editable: true });
                        activeObj.enterEditing();
                        activeObj.selectAll();
                        if (activeObj.hiddenTextarea) {
                            activeObj.hiddenTextarea.focus();
                        }
                        this.fabricCanvas.renderAll();
                    }
                },

                adjustSelectedSize(dw, dh) {
                    if (!this.fabricCanvas) return;
                    const activeObj = this.fabricCanvas.getActiveObject();
                    if (!activeObj) return;

                    if (dw !== 0) {
                        const currentW = activeObj.getScaledWidth();
                        const newW = Math.max(20, currentW + dw);
                        if (activeObj.scaleX) {
                            activeObj.scaleToWidth(newW);
                        } else {
                            activeObj.set('width', newW);
                        }
                    }

                    if (dh !== 0) {
                        const currentH = activeObj.getScaledHeight();
                        const newH = Math.max(10, currentH + dh);
                        if (activeObj.scaleY) {
                            activeObj.scaleToHeight(newH);
                        } else {
                            activeObj.set('height', newH);
                        }
                    }

                    activeObj.setCoords();
                    this.fabricCanvas.renderAll();
                },

                saveCurrentPageOverlayState() {
                    if (this.fabricCanvas) {
                        const json = this.fabricCanvas.toJSON();
                        if (json.objects && json.objects.length > 0) {
                            this.pageOverlays[this.currentPage] = json;
                        } else {
                            delete this.pageOverlays[this.currentPage];
                        }
                    }
                },

                setTool(tool) {
                    this.currentTool = tool;
                    this.applyToolMode();
                },

                applyToolMode() {
                    if (!this.fabricCanvas) return;

                    this.fabricCanvas.isDrawingMode = false;
                    this.fabricCanvas.selection = (this.currentTool === 'select');

                    if (this.currentTool === 'draw') {
                        this.fabricCanvas.isDrawingMode = true;
                        this.fabricCanvas.freeDrawingBrush.color = this.activeColor;
                        this.fabricCanvas.freeDrawingBrush.width = parseInt(this.strokeWidth) || 3;
                    } else if (this.currentTool === 'highlighter') {
                        this.fabricCanvas.isDrawingMode = true;
                        this.fabricCanvas.freeDrawingBrush.color = 'rgba(250, 204, 21, 0.45)'; // Translucent yellow
                        this.fabricCanvas.freeDrawingBrush.width = 18;
                    }
                },

                setColor(color) {
                    this.activeColor = color;
                    this.updateActiveColor();
                },

                updateActiveColor() {
                    if (!this.fabricCanvas) return;
                    if (this.currentTool === 'draw') {
                        this.fabricCanvas.freeDrawingBrush.color = this.activeColor;
                    }
                    const activeObj = this.fabricCanvas.getActiveObject();
                    if (activeObj) {
                        if (activeObj.type === 'textbox' || activeObj.type === 'i-text') {
                            activeObj.set('fill', this.activeColor);
                        } else if (activeObj.type === 'rect' || activeObj.type === 'circle') {
                            activeObj.set('stroke', this.activeColor);
                        }
                        this.fabricCanvas.renderAll();
                    }
                },

                updateFontSize() {
                    if (!this.fabricCanvas) return;
                    const activeObj = this.fabricCanvas.getActiveObject();
                    if (activeObj && (activeObj.type === 'textbox' || activeObj.type === 'i-text')) {
                        activeObj.set('fontSize', parseInt(this.fontSize));
                        this.fabricCanvas.renderAll();
                    }
                },

                updateStrokeWidth() {
                    if (!this.fabricCanvas) return;
                    if (this.currentTool === 'draw') {
                        this.fabricCanvas.freeDrawingBrush.width = parseInt(this.strokeWidth);
                    }
                    const activeObj = this.fabricCanvas.getActiveObject();
                    if (activeObj && activeObj.stroke) {
                        activeObj.set('strokeWidth', parseInt(this.strokeWidth));
                        this.fabricCanvas.renderAll();
                    }
                },

                addTextAtPosition(x, y) {
                    const text = new fabric.Textbox('Ketik teks di sini...', {
                        left: x,
                        top: y,
                        width: 240,
                        fontFamily: 'Instrument Sans, Arial, sans-serif',
                        fontSize: parseInt(this.fontSize) || 16,
                        fill: this.activeColor === '#ffffff' ? '#000000' : this.activeColor,
                        padding: 4,
                        editable: true,
                        cursorColor: '#2563eb',
                        cursorWidth: 2,
                    });
                    this.enableAllControls(text);
                    this.fabricCanvas.add(text);
                    this.fabricCanvas.setActiveObject(text);
                    text.enterEditing();
                    text.selectAll();
                    if (text.hiddenTextarea) {
                        text.hiddenTextarea.focus();
                    }
                    this.setTool('select');
                    this.fabricCanvas.renderAll();
                },

                addShape(shapeType) {
                    if (!this.fabricCanvas) return;
                    let shape;
                    if (shapeType === 'rect') {
                        shape = new fabric.Rect({
                            left: 100,
                            top: 100,
                            width: 180,
                            height: 100,
                            fill: 'transparent',
                            stroke: this.activeColor,
                            strokeWidth: parseInt(this.strokeWidth) || 3,
                        });
                    } else if (shapeType === 'circle') {
                        shape = new fabric.Circle({
                            left: 100,
                            top: 100,
                            radius: 50,
                            fill: 'transparent',
                            stroke: this.activeColor,
                            strokeWidth: parseInt(this.strokeWidth) || 3,
                        });
                    }
                    if (shape) {
                        this.enableAllControls(shape);
                        this.fabricCanvas.add(shape);
                        this.fabricCanvas.setActiveObject(shape);
                        this.setTool('select');
                    }
                },

                handleImageUpload(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    const reader = new FileReader();
                    reader.onload = (e) => {
                        fabric.Image.fromURL(e.target.result, (img) => {
                            if (img.width > 250) {
                                img.scaleToWidth(250);
                            }
                            img.set({
                                left: 100,
                                top: 100,
                            });
                            this.enableAllControls(img);
                            this.fabricCanvas.add(img);
                            this.fabricCanvas.setActiveObject(img);
                            this.setTool('select');
                        });
                    };
                    reader.readAsDataURL(file);
                    event.target.value = '';
                },

                deleteSelected() {
                    if (!this.fabricCanvas) return;
                    const activeObjects = this.fabricCanvas.getActiveObjects();
                    if (activeObjects && activeObjects.length > 0) {
                        activeObjects.forEach(obj => this.fabricCanvas.remove(obj));
                        this.fabricCanvas.discardActiveObject();
                        this.fabricCanvas.renderAll();
                        this.hasActiveObject = false;
                        this.activeObjectType = null;
                    }
                },

                prevPage() {
                    if (this.currentPage > 1) {
                        this.saveCurrentPageOverlayState();
                        const targetPage = this.currentPage - 1;
                        this.$wire.loadPage(targetPage);
                    }
                },

                nextPage() {
                    if (this.currentPage < this.totalPages) {
                        this.saveCurrentPageOverlayState();
                        const targetPage = this.currentPage + 1;
                        this.$wire.loadPage(targetPage);
                    }
                },

                zoomIn() {
                    this.zoomFactor = Math.min(2.0, this.zoomFactor + 0.15);
                    this.loadCurrentPageToCanvas(this.currentPageImage, this.rawPageWidth, this.rawPageHeight);
                },

                zoomOut() {
                    this.zoomFactor = Math.max(0.6, this.zoomFactor - 0.15);
                    this.loadCurrentPageToCanvas(this.currentPageImage, this.rawPageWidth, this.rawPageHeight);
                },

                fitWidth() {
                    this.zoomFactor = 1.0;
                    this.loadCurrentPageToCanvas(this.currentPageImage, this.rawPageWidth, this.rawPageHeight);
                },

                // Digital Signature Modal Logic
                openSignatureModal() {
                    this.showSignatureModal = true;
                    this.$nextTick(() => {
                        this.initSignaturePad();
                    });
                },

                initSignaturePad() {
                    const canvas = document.getElementById('signature-pad');
                    if (!canvas) return;
                    const ctx = canvas.getContext('2d');
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    ctx.strokeStyle = '#1e3a8a';
                    ctx.lineWidth = 2.5;
                    ctx.lineCap = 'round';

                    let drawing = false;

                    canvas.onmousedown = (e) => {
                        drawing = true;
                        ctx.beginPath();
                        ctx.moveTo(e.offsetX, e.offsetY);
                    };
                    canvas.onmousemove = (e) => {
                        if (drawing) {
                            ctx.lineTo(e.offsetX, e.offsetY);
                            ctx.stroke();
                        }
                    };
                    canvas.onmouseup = () => { drawing = false; };
                    canvas.onmouseleave = () => { drawing = false; };
                },

                clearSignaturePad() {
                    const canvas = document.getElementById('signature-pad');
                    if (!canvas) return;
                    const ctx = canvas.getContext('2d');
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                },

                applySignature() {
                    const canvas = document.getElementById('signature-pad');
                    if (!canvas) return;
                    const dataUrl = canvas.toDataURL('image/png');

                    fabric.Image.fromURL(dataUrl, (img) => {
                        img.scaleToWidth(180);
                        img.set({
                            left: 120,
                            top: 120,
                        });
                        this.enableAllControls(img);
                        this.fabricCanvas.add(img);
                        this.fabricCanvas.setActiveObject(img);
                        this.showSignatureModal = false;
                        this.setTool('select');
                    });
                },

                // Export & Save PDF
                async saveAndDownloadPdf() {
                    if (this.isSaving) return;
                    this.isSaving = true;

                    // 1. Save current page modifications
                    this.saveCurrentPageOverlayState();

                    // 2. Convert all page overlays into high-res PNG data URLs without background
                    const finalOverlays = {};

                    for (let pNum = 1; pNum <= this.totalPages; pNum++) {
                        const pageJson = this.pageOverlays[pNum];
                        if (pageJson && pageJson.objects && pageJson.objects.length > 0) {
                            if (pNum === this.currentPage && this.fabricCanvas) {
                                const bg = this.fabricCanvas.backgroundImage;
                                this.fabricCanvas.setBackgroundImage(null, () => {
                                    this.fabricCanvas.renderAll();
                                    finalOverlays[pNum] = this.fabricCanvas.toDataURL({
                                        format: 'png',
                                        multiplier: 2 // High-DPI export
                                    });
                                    this.fabricCanvas.setBackgroundImage(bg, () => {
                                        this.fabricCanvas.renderAll();
                                    });
                                });
                            } else {
                                // Offscreen canvas export
                                const offCanvasEl = document.createElement('canvas');
                                const offCanvas = new fabric.Canvas(offCanvasEl, {
                                    width: this.fabricCanvas ? this.fabricCanvas.width : 800,
                                    height: this.fabricCanvas ? this.fabricCanvas.height : 1100
                                });
                                await new Promise((resolve) => {
                                    offCanvas.loadFromJSON(pageJson, () => {
                                        offCanvas.setBackgroundImage(null, () => {
                                            offCanvas.renderAll();
                                            finalOverlays[pNum] = offCanvas.toDataURL({
                                                format: 'png',
                                                multiplier: 2
                                            });
                                            offCanvas.dispose();
                                            resolve();
                                        });
                                    });
                                });
                            }
                        }
                    }

                    // 3. Dispatch Livewire save method
                    try {
                        await this.$wire.saveEditedPdf({
                            overlays: finalOverlays,
                            rotations: {},
                            deletedPages: [],
                            metadata: {}
                        });
                    } catch (err) {
                        console.error('Failed to save edited PDF:', err);
                    } finally {
                        this.isSaving = false;
                    }
                }
            };
        }
    </script>
</div>
