<div>
    @section('page_description', 'Tambahkan stempel teks atau logo transparan pada setiap halaman dokumen PDF untuk legalitas dan hak cipta.')

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
        {{-- Left: Upload Area, Watermark Controls & Action (7 Columns) --}}
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
                        <span class="font-semibold text-primary">{{ $remainingQuota }} / {{ $monthlyLimit ?? 5 }}</span>
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
                        Tingkatkan akun Anda ke paket Pro untuk memberi watermark pada dokumen tanpa batasan kuota.
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
                            <div class="mask is-squircle flex size-14 items-center justify-center bg-primary/10 text-primary mb-3">
                                <x-lucide-stamp class="size-7" />
                            </div>
                            <p class="text-sm font-semibold text-slate-800 truncate px-4 max-w-md">
                                {{ $file->getClientOriginalName() }}
                            </p>
                            <p class="mt-1 text-xs text-slate-500">
                                Ukuran: <span class="font-medium text-slate-700">{{ number_format($file->getSize() / 1024, 2) }} KB</span>
                                @if($detectedPages)
                                    · <span class="font-medium text-primary">{{ $detectedPages }} Halaman</span>
                                @endif
                            </p>
                            <span class="mt-3 badge rounded-full bg-primary/10 text-primary text-xs font-medium px-3 py-0.5">
                                File PDF Siap Diberi Watermark
                            </span>
                        @else
                            <div class="mask is-squircle flex size-14 items-center justify-center bg-primary/10 text-primary mb-3">
                                <x-lucide-stamp class="size-7" />
                            </div>
                            <p class="text-sm font-semibold text-slate-700">
                                Pilih dokumen PDF atau tarik ke sini
                            </p>
                            <p class="mt-1 text-xs text-slate-400">
                                Mendukung berkas .PDF hingga ukuran {{ $maxMb }}MB
                            </p>
                            <span class="mt-4 btn h-8 rounded-full bg-slate-150 px-4 text-xs font-medium text-slate-700 hover:bg-slate-200">
                                Pilih Berkas PDF
                            </span>
                        @endif

                        <input x-ref="fileInput" wire:model="file" type="file" accept=".pdf,application/pdf" class="hidden" />
                    </label>

                    {{-- Uploading Progress Bar --}}
                    <div x-show="isUploading" class="mt-4 space-y-1.5" style="display: none;">
                        <div class="flex justify-between text-xs text-slate-500">
                            <span>Mengunggah dokumen...</span>
                            <span x-text="progress + '%'"></span>
                        </div>
                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-200">
                            <div class="h-full bg-primary transition-all duration-150" :style="'width: ' + progress + '%'"></div>
                        </div>
                    </div>
                </div>

                {{-- Watermark Configuration Card --}}
                @if($file && !$resultPath)
                    <div class="card p-4 sm:p-5 space-y-5">
                        {{-- Mode Selector Tabs: Teks vs Gambar --}}
                        <div class="flex rounded-lg bg-slate-100 p-1">
                            <button type="button" 
                                    wire:click="$set('type', 'text')"
                                    class="flex flex-1 items-center justify-center space-x-2 rounded-md py-2 text-xs font-medium transition-all {{ $type === 'text' ? 'bg-white text-slate-800 shadow-xs' : 'text-slate-500 hover:text-slate-700' }}">
                                <x-lucide-type class="size-4" />
                                <span>Watermark Teks</span>
                            </button>
                            <button type="button" 
                                    wire:click="$set('type', 'image')"
                                    class="flex flex-1 items-center justify-center space-x-2 rounded-md py-2 text-xs font-medium transition-all {{ $type === 'image' ? 'bg-white text-slate-800 shadow-xs' : 'text-slate-500 hover:text-slate-700' }}">
                                <x-lucide-image class="size-4" />
                                <span>Watermark Logo / Gambar</span>
                            </button>
                        </div>

                        {{-- Section: Text Watermark Controls --}}
                        @if($type === 'text')
                            <div class="space-y-4">
                                {{-- Input Teks --}}
                                <div class="space-y-1.5">
                                    <label class="text-xs font-medium text-slate-700">Teks Stempel Watermark <span class="text-error">*</span></label>
                                    <input type="text" 
                                           wire:model.live="text" 
                                           placeholder="Contoh: CONFIDENTIAL, DRAFT, RAHASIA..."
                                           class="form-input h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs placeholder:text-slate-400 focus:border-primary focus:outline-hidden" />
                                    @error('text')
                                        <p class="text-xs text-error mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Preset Cepat Teks --}}
                                <div class="space-y-1.5">
                                    <span class="text-xs text-slate-500">Preset Cepat:</span>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach(['CONFIDENTIAL', 'DRAFT', 'RAHASIA', 'SALINAN', 'SAMPLE', 'ASLI'] as $p)
                                            <button type="button" 
                                                    wire:click="setPreset('{{ $p }}')"
                                                    class="btn h-7 rounded-full border border-slate-200 px-3 text-xs font-medium transition-colors {{ $text === $p ? 'bg-primary text-white border-primary' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}">
                                                {{ $p }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Pilihan Warna & Ukuran Font --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-150">
                                    {{-- Warna Teks --}}
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-medium text-slate-700">Warna Teks</label>
                                        <div class="flex items-center space-x-2">
                                            @php
                                                $colors = [
                                                    'red' => ['bg' => 'bg-red-500', 'name' => 'Merah'],
                                                    'slate' => ['bg' => 'bg-slate-500', 'name' => 'Abu-abu'],
                                                    'blue' => ['bg' => 'bg-blue-500', 'name' => 'Biru'],
                                                    'black' => ['bg' => 'bg-black', 'name' => 'Hitam'],
                                                    'orange' => ['bg' => 'bg-orange-500', 'name' => 'Oranye'],
                                                ];
                                            @endphp
                                            @foreach($colors as $k => $c)
                                                <button type="button" 
                                                        wire:click="$set('colorPreset', '{{ $k }}')"
                                                        title="{{ $c['name'] }}"
                                                        class="size-6 rounded-full {{ $c['bg'] }} ring-offset-2 transition-transform {{ $colorPreset === $k ? 'ring-2 ring-primary scale-110' : 'hover:scale-105 opacity-80' }}">
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- Ukuran Font --}}
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-medium text-slate-700">Ukuran Teks</label>
                                        <select wire:model.live="fontsize" class="form-select h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs focus:border-primary focus:outline-hidden">
                                            <option value="28">Kecil (28 pt)</option>
                                            <option value="38">Sedang (38 pt)</option>
                                            <option value="48">Besar (48 pt)</option>
                                            <option value="64">Ekstra Besar (64 pt)</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Rotasi & Posisi Teks --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-150">
                                    {{-- Rotasi --}}
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-medium text-slate-700">Sudut Kemiringan</label>
                                        <div class="grid grid-cols-3 gap-1.5">
                                            <button type="button" 
                                                    wire:click="$set('rotation', 45)"
                                                    class="btn h-8 rounded-lg border border-slate-200 text-xs font-medium {{ $rotation == 45 ? 'bg-primary text-white border-primary' : 'bg-white text-slate-700 hover:bg-slate-50' }}">
                                                45° Miring
                                            </button>
                                            <button type="button" 
                                                    wire:click="$set('rotation', 0)"
                                                    class="btn h-8 rounded-lg border border-slate-200 text-xs font-medium {{ $rotation == 0 ? 'bg-primary text-white border-primary' : 'bg-white text-slate-700 hover:bg-slate-50' }}">
                                                0° Lurus
                                            </button>
                                            <button type="button" 
                                                    wire:click="$set('rotation', -45)"
                                                    class="btn h-8 rounded-lg border border-slate-200 text-xs font-medium {{ $rotation == -45 ? 'bg-primary text-white border-primary' : 'bg-white text-slate-700 hover:bg-slate-50' }}">
                                                -45°
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Posisi Teks --}}
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-medium text-slate-700">Tata Letak (Posisi)</label>
                                        <select wire:model.live="position" class="form-select h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs focus:border-primary focus:outline-hidden">
                                            <option value="center">Tengah Dokumen (Center)</option>
                                            <option value="top">Bagian Atas (Header)</option>
                                            <option value="bottom">Bagian Bawah (Footer)</option>
                                            <option value="tiled">Pola Berulang (3x3 Grid Pola Aman)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- Section: Image / Logo Watermark Controls --}}
                            <div class="space-y-4">
                                <div class="space-y-1.5">
                                    <label class="text-xs font-medium text-slate-700">Upload Gambar / Logo <span class="text-error">*</span></label>
                                    <input type="file" 
                                           wire:model="watermarkImage" 
                                           accept="image/png,image/jpeg,image/webp"
                                           class="form-input block w-full text-xs text-slate-500 file:mr-3 file:h-8 file:rounded-full file:border-0 file:bg-slate-150 file:px-4 file:text-xs file:font-medium file:text-slate-700 hover:file:bg-slate-200" />
                                    @error('watermarkImage')
                                        <p class="text-xs text-error mt-1">{{ $message }}</p>
                                    @enderror
                                    <p class="text-xs text-slate-400">Rekomendasi gunakan file PNG transparan (tanpa background) untuk hasil maksimal.</p>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-150">
                                    {{-- Ukuran Logo --}}
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-medium text-slate-700">Ukuran Logo</label>
                                        <select wire:model.live="imageScale" class="form-select h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs focus:border-primary focus:outline-hidden">
                                            <option value="0.20">Kecil (20% Lebar Halaman)</option>
                                            <option value="0.35">Sedang (35% Lebar Halaman)</option>
                                            <option value="0.55">Besar (55% Lebar Halaman)</option>
                                        </select>
                                    </div>

                                    {{-- Posisi Logo --}}
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-medium text-slate-700">Posisi Logo</label>
                                        <select wire:model.live="imagePosition" class="form-select h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs focus:border-primary focus:outline-hidden">
                                            <option value="center">Tengah (Center)</option>
                                            <option value="bottom-right">Sudut Kanan Bawah</option>
                                            <option value="bottom-left">Sudut Kiri Bawah</option>
                                            <option value="top-right">Sudut Kanan Atas</option>
                                            <option value="top-left">Sudut Kiri Atas</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Pengaturan Opasitas & Halaman (Bersama) --}}
                        <div class="pt-2 border-t border-slate-150 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Tingkat Transparansi --}}
                                <div class="space-y-1.5">
                                    <label class="text-xs font-medium text-slate-700">
                                        Transparansi / Opasitas ({{ round($opacity * 100) }}%)
                                    </label>
                                    <div class="grid grid-cols-4 gap-1">
                                        @foreach([0.15 => '15%', 0.25 => '25%', 0.50 => '50%', 0.75 => '75%'] as $opVal => $opLabel)
                                            <button type="button" 
                                                    wire:click="$set('opacity', {{ $opVal }})"
                                                    class="btn h-7 rounded-md border border-slate-200 text-xs font-medium {{ (string)$opacity === (string)$opVal ? 'bg-primary text-white border-primary' : 'bg-white text-slate-600 hover:bg-slate-50' }}">
                                                {{ $opLabel }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Layer Posisi --}}
                                <div class="space-y-1.5">
                                    <label class="text-xs font-medium text-slate-700">Lapisan Stempel</label>
                                    <select wire:model.live="layer" class="form-select h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs focus:border-primary focus:outline-hidden">
                                        <option value="over">Di Atas Konten (Foreground)</option>
                                        <option value="under">Di Bawah Konten (Background)</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Cakupan Halaman --}}
                            <div class="space-y-2 pt-2 border-t border-slate-150">
                                <label class="text-xs font-medium text-slate-700">Cakupan Halaman Watermark</label>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" 
                                            wire:click="$set('pageMode', 'all')"
                                            class="btn h-8 rounded-lg border border-slate-200 px-3 text-xs font-medium {{ $pageMode === 'all' ? 'bg-primary text-white border-primary' : 'bg-white text-slate-700 hover:bg-slate-50' }}">
                                        Semua Halaman ({{ $detectedPages }})
                                    </button>
                                    <button type="button" 
                                            wire:click="$set('pageMode', 'first')"
                                            class="btn h-8 rounded-lg border border-slate-200 px-3 text-xs font-medium {{ $pageMode === 'first' ? 'bg-primary text-white border-primary' : 'bg-white text-slate-700 hover:bg-slate-50' }}">
                                        Halaman Pertama Saja
                                    </button>
                                    <button type="button" 
                                            wire:click="$set('pageMode', 'range')"
                                            class="btn h-8 rounded-lg border border-slate-200 px-3 text-xs font-medium {{ $pageMode === 'range' ? 'bg-primary text-white border-primary' : 'bg-white text-slate-700 hover:bg-slate-50' }}">
                                        Rentang Kustom
                                    </button>
                                </div>

                                @if($pageMode === 'range')
                                    <div class="pt-1.5">
                                        <input type="text" 
                                               wire:model="pageRange" 
                                               placeholder="Contoh: 1-3, 5 (maks: {{ $detectedPages }})"
                                               class="form-input h-9 w-full sm:w-64 rounded-lg border border-slate-300 bg-white px-3 text-xs placeholder:text-slate-400 focus:border-primary focus:outline-hidden" />
                                        @error('pageRange')
                                            <p class="text-xs text-error mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Action Button --}}
                        <div class="pt-2">
                            <button wire:click="applyWatermark"
                                    wire:loading.attr="disabled"
                                    class="btn h-9 w-full rounded-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus text-xs px-5 shadow-xs transition-colors flex items-center justify-center space-x-2">
                                <span wire:loading.remove wire:target="applyWatermark">Terapkan Watermark Sekarang</span>
                                <span wire:loading wire:target="applyWatermark" class="inline-flex items-center space-x-2">
                                    <x-lucide-loader-2 class="size-4 animate-spin" />
                                    <span>Menempelkan Watermark pada Dokumen...</span>
                                </span>
                            </button>
                        </div>
                    </div>
                @endif
            @endif
        </div>

        {{-- Right: Live Preview Box & Result Card (5 Columns) --}}
        <div class="col-span-12 lg:col-span-5 space-y-4 sm:space-y-5">
            @if ($resultPath)
                <div class="card p-5 sm:p-6 text-center space-y-4 border border-slate-150">
                    <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-success/15 text-success">
                        <x-lucide-check-circle-2 class="size-7" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-slate-800">Watermark Berhasil Ditempel!</h3>
                        <p class="mt-1 text-xs text-slate-500">
                            Dokumen PDF Anda telah berhasil diberi stempel legalitas sesuai pengaturan yang Anda tentukan.
                        </p>
                    </div>

                    {{-- Stats Summary --}}
                    <div class="rounded-lg bg-slate-50 p-3.5 space-y-2 text-left border border-slate-150">
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">Nama File:</span>
                            <span class="font-medium text-slate-800 truncate max-w-[180px]">{{ $resultFilename }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">Halaman Diberi Watermark:</span>
                            <span class="font-medium text-slate-800">{{ $watermarkedPages }} dari {{ $totalPages }} Halaman</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">Ukuran Dokumen:</span>
                            <span class="font-medium text-slate-800">{{ number_format($newSize / 1024, 2) }} KB</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">Tipe Stempel:</span>
                            <span class="badge rounded-full bg-primary/10 text-primary text-xs font-medium uppercase">{{ $type }}</span>
                        </div>
                    </div>

                    {{-- Download Button --}}
                    <div class="pt-2 space-y-2">
                        <button wire:click="download" 
                                class="btn h-9 w-full rounded-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus text-xs px-5 shadow-xs flex items-center justify-center space-x-2 transition-colors">
                            <x-lucide-download class="size-4" />
                            <span>Unduh PDF dengan Watermark</span>
                        </button>
                        <button wire:click="resetFile" 
                                class="btn h-9 w-full rounded-full bg-slate-150 font-medium text-slate-700 hover:bg-slate-200 text-xs px-5 transition-colors">
                            Proses Dokumen Lainnya
                        </button>
                    </div>
                </div>
            @else
                {{-- Live Visual Preview Box --}}
                <div class="card p-5 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-150 pb-2.5">
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-700">Simulasi Tampilan Lembar</h4>
                        <span class="badge rounded-full bg-slate-150 text-slate-700 text-xs font-medium">Pratinjau Simbolis</span>
                    </div>

                    {{-- Mock A4 Paper Sheet --}}
                    <div class="relative mx-auto flex h-64 w-44 items-center justify-center overflow-hidden rounded-md border border-slate-300 bg-white shadow-xs p-3 select-none">
                        {{-- Mock Text Lines (Placeholder Page Content) --}}
                        <div class="w-full space-y-2 opacity-25">
                            <div class="h-2 w-3/4 rounded-sm bg-slate-400"></div>
                            <div class="h-1.5 w-full rounded-sm bg-slate-300"></div>
                            <div class="h-1.5 w-5/6 rounded-sm bg-slate-300"></div>
                            <div class="h-1.5 w-full rounded-sm bg-slate-300"></div>
                            <div class="h-1.5 w-4/5 rounded-sm bg-slate-300"></div>
                            <div class="h-1.5 w-full rounded-sm bg-slate-300"></div>
                            <div class="h-1.5 w-3/4 rounded-sm bg-slate-300"></div>
                            <div class="h-1.5 w-full rounded-sm bg-slate-300"></div>
                            <div class="h-1.5 w-2/3 rounded-sm bg-slate-300"></div>
                        </div>

                        {{-- Watermark Simulation Layer --}}
                        @if($type === 'text')
                            @php
                                $colorHex = match($colorPreset) {
                                    'red' => '#ef4444',
                                    'blue' => '#3b82f6',
                                    'black' => '#000000',
                                    'orange' => '#f97316',
                                    default => '#64748b'
                                };
                            @endphp
                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                <span class="font-bold tracking-widest text-center whitespace-nowrap"
                                      style="color: {{ $colorHex }}; opacity: {{ max(0.15, $opacity) }}; transform: rotate({{ $rotation }}deg); font-size: {{ max(10, min(18, $fontsize / 3.2)) }}px;">
                                    {{ $text ?: 'CONFIDENTIAL' }}
                                </span>
                            </div>
                        @else
                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                <div class="size-14 rounded-full border-2 border-dashed border-primary/40 flex items-center justify-center text-primary/40 text-xs font-semibold"
                                     style="opacity: {{ max(0.2, $opacity) }};">
                                    LOGO
                                </div>
                            </div>
                        @endif
                    </div>

                    <p class="text-center text-xs text-slate-400 pt-1">
                        Posisi dan ukuran stempel akan disesuaikan otomatis dengan dimensi asli halaman dokumen.
                    </p>
                </div>

                {{-- Security & Features Info --}}
                <div class="card p-5 space-y-3">
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-700">Fitur Utama Watermark</h4>
                    <ul class="space-y-2 text-xs text-slate-500">
                        <li class="flex items-start space-x-2">
                            <x-lucide-check class="size-3.5 text-success shrink-0 mt-0.5" />
                            <span><strong>Vektor Lossless</strong>: Teks dicetak tajam tanpa pecah saat di-zoom.</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <x-lucide-check class="size-3.5 text-success shrink-0 mt-0.5" />
                            <span><strong>Pilihan Layer</strong>: Letakkan stempel di depan atau di balik teks dokumen.</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <x-lucide-check class="size-3.5 text-success shrink-0 mt-0.5" />
                            <span><strong>Dukungan Multi-halaman</strong>: Terapkan ke semua atau rentang halaman tertentu.</span>
                        </li>
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>
