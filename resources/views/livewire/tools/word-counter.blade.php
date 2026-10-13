<div>
    @section('page_description', 'Hitung jumlah kata, karakter, kalimat, dan paragraf serta ubah format teks (UPPERCASE, lowercase, Title Case, camelCase) secara instan.')

    {{-- Error Flash Alert --}}
    @if (!empty($errorMsg))
        <div class="alert flex rounded-lg border border-error px-4 py-3 text-error sm:px-5 mb-6">
            <x-lucide-alert-circle class="size-5 shrink-0 mr-2.5" />
            <span class="text-xs">{{ $errorMsg }}</span>
        </div>
    @endif
    @error('fileUpload')
        <div class="alert flex rounded-lg border border-error px-4 py-3 text-error sm:px-5 mb-6">
            <x-lucide-alert-circle class="size-5 shrink-0 mr-2.5" />
            <span class="text-xs">{{ $message }}</span>
        </div>
    @enderror

    <div class="space-y-4 sm:space-y-5 lg:space-y-6">
        
        {{-- Quota Notification Pill & Quick Stats Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                @if(auth()->check() && auth()->user()->is_admin)
                    <div class="badge rounded-full bg-primary/10 text-primary px-3 py-1 text-xs font-medium">
                        Akses: Unlimited (Admin)
                    </div>
                @elseif($remainingQuota !== null)
                    <div class="badge rounded-full space-x-2 bg-slate-150 px-3 py-1 text-slate-700 text-xs font-medium">
                        <span>Sisa Kuota Unduh:</span>
                        <span class="font-semibold text-primary">{{ $remainingQuota }} / {{ $monthlyLimit ?? 30 }}</span>
                    </div>
                @else
                    <div class="badge rounded-full bg-success/15 text-success px-3 py-1 text-xs font-medium">
                        Kuota: {{ $isPro ? 'Unlimited (Pro)' : 'Tanpa Batas' }}
                    </div>
                @endif
            </div>

            <div class="flex items-center space-x-2 text-xs text-slate-500">
                <span class="inline-block size-2 rounded-full bg-success"></span>
                <span>Analisis teks realtime (Live)</span>
            </div>
        </div>

        {{-- Top 6 Realtime Metrics Bar --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
            {{-- Kata --}}
            <div class="card p-3 sm:p-4 text-center">
                <span class="text-xs text-slate-500 font-medium block">Jumlah Kata</span>
                <span class="text-xl sm:text-2xl font-semibold text-slate-800 block mt-1">
                    {{ number_format($stats['words']) }}
                </span>
            </div>

            {{-- Karakter dgn Spasi --}}
            <div class="card p-3 sm:p-4 text-center">
                <span class="text-xs text-slate-500 font-medium block">Karakter (+Spasi)</span>
                <span class="text-xl sm:text-2xl font-semibold text-slate-800 block mt-1">
                    {{ number_format($stats['characters_with_spaces']) }}
                </span>
            </div>

            {{-- Karakter tanpa Spasi --}}
            <div class="card p-3 sm:p-4 text-center">
                <span class="text-xs text-slate-500 font-medium block">Karakter (-Spasi)</span>
                <span class="text-xl sm:text-2xl font-semibold text-slate-800 block mt-1">
                    {{ number_format($stats['characters_no_spaces']) }}
                </span>
            </div>

            {{-- Kalimat --}}
            <div class="card p-3 sm:p-4 text-center">
                <span class="text-xs text-slate-500 font-medium block">Jumlah Kalimat</span>
                <span class="text-xl sm:text-2xl font-semibold text-slate-800 block mt-1">
                    {{ number_format($stats['sentences']) }}
                </span>
            </div>

            {{-- Paragraf --}}
            <div class="card p-3 sm:p-4 text-center">
                <span class="text-xs text-slate-500 font-medium block">Jumlah Paragraf</span>
                <span class="text-xl sm:text-2xl font-semibold text-slate-800 block mt-1">
                    {{ number_format($stats['paragraphs']) }}
                </span>
            </div>

            {{-- Waktu Baca --}}
            <div class="card p-3 sm:p-4 text-center">
                <span class="text-xs text-slate-500 font-medium block">Waktu Baca</span>
                <span class="text-xl sm:text-2xl font-semibold text-primary block mt-1">
                    {{ $stats['reading_time'] }}
                </span>
                <span class="text-xs text-slate-400 block mt-0.5">Bicara: {{ $stats['speaking_time'] }}</span>
            </div>
        </div>

        {{-- Main Layout (12 Columns): Left Editor, Right Transformations --}}
        <div class="grid grid-cols-1 gap-4 sm:gap-5 lg:grid-cols-12 lg:gap-6">
            
            {{-- Left Area: Main Text Editor (8 Columns) --}}
            <div class="col-span-12 lg:col-span-8 space-y-4">
                <div class="card p-4 sm:p-5">
                    
                    {{-- Editor Toolbar Header --}}
                    <div class="flex flex-wrap items-center justify-between gap-2.5 pb-3.5 mb-3.5 border-b border-slate-200">
                        <div class="flex items-center space-x-2">
                            <h3 class="text-sm font-semibold text-slate-800">Editor Teks</h3>
                            <span class="badge rounded-full bg-slate-150 text-slate-700 px-2 py-0.5 text-xs font-medium">
                                UTF-8
                            </span>
                        </div>

                        {{-- Action Buttons Toolbar --}}
                        <div class="flex flex-wrap items-center gap-1.5">
                            {{-- Undo Button --}}
                            @if($canUndo)
                                <button type="button" 
                                        wire:click="undo"
                                        class="btn h-8 rounded-full border border-slate-300 font-medium text-slate-700 hover:bg-slate-50 text-xs px-3 flex items-center space-x-1 cursor-pointer transition-all"
                                        title="Kembalikan perubahan sebelumnya">
                                    <x-lucide-undo-2 class="size-3.5" />
                                    <span>Undo</span>
                                </button>
                            @endif

                            {{-- Upload File --}}
                            <label class="btn h-8 rounded-full border border-slate-300 font-medium text-slate-700 hover:bg-slate-50 text-xs px-3 flex items-center space-x-1 cursor-pointer transition-all"
                                   title="Buka dokumen teks (.txt, .md, .csv)">
                                <x-lucide-upload class="size-3.5" />
                                <span>Unggah Berkas</span>
                                <input type="file" wire:model="fileUpload" accept=".txt,.md,.csv,.json" class="hidden" />
                            </label>

                            {{-- Copy to Clipboard --}}
                            <button type="button" 
                                    x-data 
                                    x-on:click="if($wire.text.trim().length > 0) { navigator.clipboard.writeText($wire.text); $wire.toast('Teks berhasil disalin ke clipboard!', 'success'); } else { $wire.toast('Teks masih kosong.', 'warning'); }"
                                    class="btn h-8 rounded-full border border-slate-300 font-medium text-slate-700 hover:bg-slate-50 text-xs px-3 flex items-center space-x-1 cursor-pointer transition-all"
                                    title="Salin seluruh teks ke papan klip">
                                <x-lucide-copy class="size-3.5" />
                                <span>Salin Teks</span>
                            </button>

                            {{-- Clear Text --}}
                            @if(trim($text) !== '')
                                <button type="button" 
                                        wire:click="clearText"
                                        class="btn h-8 rounded-full border border-error/30 text-error hover:bg-error/10 text-xs px-3 flex items-center space-x-1 cursor-pointer transition-all"
                                        title="Kosongkan seluruh teks editor">
                                    <x-lucide-trash-2 class="size-3.5" />
                                    <span>Hapus</span>
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Textarea Editor --}}
                    <div class="relative">
                        <textarea wire:model.live.debounce.150ms="text"
                                  placeholder="Ketik atau tempelkan (paste) teks Anda di sini untuk menghitung jumlah kata, karakter, dan mengubah format huruf secara instan..."
                                  class="form-textarea w-full rounded-xl border border-slate-200 bg-white p-4 text-xs sm:text-sm text-slate-700 placeholder:text-slate-400 focus:border-primary focus:ring-1 focus:ring-primary min-h-[380px] sm:min-h-[440px] leading-relaxed resize-y font-normal transition-all"></textarea>
                    </div>

                    {{-- Footer Status Bar --}}
                    <div class="mt-3 flex flex-wrap items-center justify-between text-xs text-slate-500 pt-2 border-t border-slate-100">
                        <div class="flex items-center space-x-3">
                            <span>{{ number_format($stats['words']) }} kata</span>
                            <span class="text-slate-300">•</span>
                            <span>{{ number_format($stats['characters_with_spaces']) }} karakter</span>
                            <span class="text-slate-300">•</span>
                            <span>{{ number_format($stats['sentences']) }} kalimat</span>
                        </div>
                        <div class="text-xs text-slate-400">
                            Waktu baca rata-rata: {{ $stats['reading_time'] }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Area: Transformations & Utilities (4 Columns) --}}
            <div class="col-span-12 lg:col-span-4 space-y-4">
                
                {{-- Card 1: Case Converter Palette --}}
                <div class="card p-4 sm:p-5">
                    <div class="border-b border-slate-200 pb-3 mb-3.5">
                        <h4 class="text-xs font-semibold text-slate-800">Ubah Format Huruf (Case Converter)</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Ubah kapitalisasi teks secara otomatis</p>
                    </div>

                    <div class="space-y-2">
                        {{-- UPPERCASE --}}
                        <button type="button" 
                                wire:click="transform('upper')"
                                class="btn h-9 w-full rounded-lg border border-slate-200 hover:border-primary hover:bg-primary/5 font-medium text-slate-700 hover:text-primary text-xs px-3.5 flex items-center justify-between transition-all cursor-pointer">
                            <span>HURUF BESAR (UPPERCASE)</span>
                            <span class="text-xs text-slate-400 uppercase">ABC</span>
                        </button>

                        {{-- lowercase --}}
                        <button type="button" 
                                wire:click="transform('lower')"
                                class="btn h-9 w-full rounded-lg border border-slate-200 hover:border-primary hover:bg-primary/5 font-medium text-slate-700 hover:text-primary text-xs px-3.5 flex items-center justify-between transition-all cursor-pointer">
                            <span>huruf kecil (lowercase)</span>
                            <span class="text-xs text-slate-400 lowercase">abc</span>
                        </button>

                        {{-- Title Case --}}
                        <button type="button" 
                                wire:click="transform('title')"
                                class="btn h-9 w-full rounded-lg border border-slate-200 hover:border-primary hover:bg-primary/5 font-medium text-slate-700 hover:text-primary text-xs px-3.5 flex items-center justify-between transition-all cursor-pointer">
                            <span>Title Case (Kapital Tiap Kata)</span>
                            <span class="text-xs text-slate-400">Abc</span>
                        </button>

                        {{-- Sentence case --}}
                        <button type="button" 
                                wire:click="transform('sentence')"
                                class="btn h-9 w-full rounded-lg border border-slate-200 hover:border-primary hover:bg-primary/5 font-medium text-slate-700 hover:text-primary text-xs px-3.5 flex items-center justify-between transition-all cursor-pointer">
                            <span>Sentence case (Awal Kalimat)</span>
                            <span class="text-xs text-slate-400">A. b.</span>
                        </button>

                        {{-- camelCase --}}
                        <button type="button" 
                                wire:click="transform('camel')"
                                class="btn h-9 w-full rounded-lg border border-slate-200 hover:border-primary hover:bg-primary/5 font-medium text-slate-700 hover:text-primary text-xs px-3.5 flex items-center justify-between transition-all cursor-pointer">
                            <span>camelCase (Format Coding)</span>
                            <span class="text-xs text-slate-400">abCd</span>
                        </button>

                        {{-- snake_case --}}
                        <button type="button" 
                                wire:click="transform('snake')"
                                class="btn h-9 w-full rounded-lg border border-slate-200 hover:border-primary hover:bg-primary/5 font-medium text-slate-700 hover:text-primary text-xs px-3.5 flex items-center justify-between transition-all cursor-pointer">
                            <span>snake_case (Format Database)</span>
                            <span class="text-xs text-slate-400">ab_cd</span>
                        </button>

                        {{-- kebab-case --}}
                        <button type="button" 
                                wire:click="transform('kebab')"
                                class="btn h-9 w-full rounded-lg border border-slate-200 hover:border-primary hover:bg-primary/5 font-medium text-slate-700 hover:text-primary text-xs px-3.5 flex items-center justify-between transition-all cursor-pointer">
                            <span>kebab-case (Format URL/Slug)</span>
                            <span class="text-xs text-slate-400">ab-cd</span>
                        </button>
                    </div>
                </div>

                {{-- Card 2: Text Cleaner Utilities --}}
                <div class="card p-4 sm:p-5">
                    <div class="border-b border-slate-200 pb-3 mb-3.5">
                        <h4 class="text-xs font-semibold text-slate-800">Pembersih Teks (Text Cleaner)</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Rapikan format spasi dan baris</p>
                    </div>

                    <div class="space-y-2">
                        <button type="button" 
                                wire:click="transform('clean_spaces')"
                                class="btn h-9 w-full rounded-lg border border-slate-200 hover:border-primary hover:bg-primary/5 font-medium text-slate-700 hover:text-primary text-xs px-3.5 flex items-center justify-between transition-all cursor-pointer">
                            <span>Hapus Spasi Berlebih</span>
                            <x-lucide-space class="size-4 text-slate-400" />
                        </button>

                        <button type="button" 
                                wire:click="transform('clean_empty_lines')"
                                class="btn h-9 w-full rounded-lg border border-slate-200 hover:border-primary hover:bg-primary/5 font-medium text-slate-700 hover:text-primary text-xs px-3.5 flex items-center justify-between transition-all cursor-pointer">
                            <span>Hapus Baris Kosong Ganda</span>
                            <x-lucide-wrap-text class="size-4 text-slate-400" />
                        </button>
                    </div>
                </div>

                {{-- Card 3: Export & Download Action --}}
                <div class="card p-4 sm:p-5">
                    <div class="border-b border-slate-200 pb-3 mb-3.5">
                        <h4 class="text-xs font-semibold text-slate-800">Ekspor Hasil</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Unduh teks hasil olahan ke komputer</p>
                    </div>

                    <button type="button" 
                            wire:click="downloadTxt"
                            wire:loading.attr="disabled"
                            class="btn h-9 w-full rounded-full bg-primary font-medium text-white shadow-xs text-xs px-5 hover:bg-primary-focus cursor-pointer flex items-center justify-center space-x-2 transition-all">
                        <x-lucide-download class="size-4" />
                        <span wire:loading.remove wire:target="downloadTxt">Unduh File Teks (.TXT)</span>
                        <span wire:loading wire:target="downloadTxt">Mengekspor...</span>
                    </button>
                    <span class="text-xs text-slate-400 mt-1.5 block text-center">Tersimpan dalam enkripsi UTF-8 standar</span>
                </div>

            </div>
        </div>
    </div>
</div>
