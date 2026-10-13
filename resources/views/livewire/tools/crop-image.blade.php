<div>
    @section('page_description', 'Pangkas dan potong foto sesuai rasio aspek (1:1, 4:3, 16:9, pas foto 2x3, 3x4, 4x6) atau ukuran kustom secara presisi.')

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
        {{-- Left: Upload Area, Crop Controls & Action (7 Columns) --}}
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
                        <span>Ganti Gambar</span>
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
                        Tingkatkan akun Anda ke paket Pro untuk memotong gambar tanpa batasan kuota.
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
                                <x-lucide-crop class="size-7" />
                            </div>
                            <p class="text-sm font-semibold text-slate-800 truncate px-4 max-w-md">
                                {{ $file->getClientOriginalName() }}
                            </p>
                            <p class="mt-1 text-xs text-slate-500">
                                Ukuran: <span class="font-medium text-slate-700">{{ number_format($file->getSize() / 1024, 2) }} KB</span>
                                @if($origWidth && $origHeight)
                                    · Dimensi Asli: <span class="font-medium text-primary">{{ $origWidth }} x {{ $origHeight }} px</span>
                                @endif
                            </p>
                            <span class="mt-3 badge rounded-full bg-primary/10 text-primary text-xs font-medium px-3 py-0.5">
                                Gambar Siap Dipotong (Crop)
                            </span>
                        @else
                            <div class="mask is-squircle flex size-14 items-center justify-center bg-primary/10 text-primary mb-3">
                                <x-lucide-crop class="size-7" />
                            </div>
                            <p class="text-sm font-semibold text-slate-700">
                                Pilih gambar atau tarik ke sini
                            </p>
                            <p class="mt-1 text-xs text-slate-400">
                                Mendukung berkas JPG, PNG, WebP hingga ukuran {{ $maxMb }}MB
                            </p>
                            <span class="mt-4 btn h-8 rounded-full bg-slate-150 px-4 text-xs font-medium text-slate-700 hover:bg-slate-200">
                                Pilih Berkas Gambar
                            </span>
                        @endif

                        <input x-ref="fileInput" wire:model="file" type="file" accept="image/*" class="hidden" />
                    </label>

                    {{-- Uploading Progress Bar --}}
                    <div x-show="isUploading" class="mt-4 space-y-1.5" style="display: none;">
                        <div class="flex justify-between text-xs text-slate-500">
                            <span>Mengunggah gambar...</span>
                            <span x-text="progress + '%'"></span>
                        </div>
                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-200">
                            <div class="h-full bg-primary transition-all duration-150" :style="'width: ' + progress + '%'"></div>
                        </div>
                    </div>
                </div>

                {{-- Crop Settings Controls --}}
                @if($file && !$resultPath)
                    <div class="card p-4 sm:p-5 space-y-4">
                        {{-- Presets Selector --}}
                        <div class="space-y-2">
                            <label class="text-xs font-medium text-slate-700">Rasio Aspek Pemotongan</label>
                            
                            {{-- Rasio Media Sosial & Standar --}}
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                @php
                                    $presets = [
                                        '1:1' => ['label' => '1:1 Persegi', 'desc' => 'Instagram / Avatar'],
                                        '4:3' => ['label' => '4:3 Standar', 'desc' => 'Foto Standar'],
                                        '16:9' => ['label' => '16:9 Widescreen', 'desc' => 'Layar / Video'],
                                        '9:16' => ['label' => '9:16 Vertikal', 'desc' => 'Story / TikTok'],
                                        '3:2' => ['label' => '3:2 Klasik', 'desc' => 'Kamera DSLR'],
                                        'pas_foto_2x3' => ['label' => 'Pas Foto 2x3', 'desc' => 'Rasio 2:3'],
                                        'pas_foto_3x4' => ['label' => 'Pas Foto 3x4', 'desc' => 'Rasio 3:4'],
                                        'pas_foto_4x6' => ['label' => 'Pas Foto 4x6', 'desc' => 'Rasio 4:6'],
                                    ];
                                @endphp
                                @foreach($presets as $pKey => $pData)
                                    <button type="button" 
                                            wire:click="setPreset('{{ $pKey }}')"
                                            class="flex flex-col text-left p-2.5 rounded-lg border transition-all {{ $preset === $pKey ? 'bg-primary/5 border-primary text-primary' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50' }}">
                                        <span class="text-xs font-semibold">{{ $pData['label'] }}</span>
                                        <span class="text-slate-400 text-xs mt-0.5 truncate">{{ $pData['desc'] }}</span>
                                    </button>
                                @endforeach
                            </div>

                            <button type="button" 
                                    wire:click="setPreset('custom')"
                                    class="mt-1 w-full text-center py-2 rounded-lg border text-xs font-medium transition-all {{ $preset === 'custom' ? 'bg-primary text-white border-primary' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                                Pengaturan Dimensi Kustom (Piksel Bebas)
                            </button>
                        </div>

                        {{-- Custom Dimension Inputs if Preset is Custom --}}
                        @if($preset === 'custom')
                            <div class="p-3.5 rounded-lg bg-slate-50 border border-slate-200 space-y-3">
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="space-y-1">
                                        <label class="text-xs font-medium text-slate-700">Lebar Potongan (px)</label>
                                        <input type="number" wire:model.live="customWidth" min="10" max="{{ $origWidth }}" class="form-input h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs focus:border-primary focus:outline-hidden" />
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-xs font-medium text-slate-700">Tinggi Potongan (px)</label>
                                        <input type="number" wire:model.live="customHeight" min="10" max="{{ $origHeight }}" class="form-input h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs focus:border-primary focus:outline-hidden" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="space-y-1">
                                        <label class="text-xs font-medium text-slate-700">Offset X dari Kiri (px)</label>
                                        <input type="number" wire:model.live="customX" min="0" max="{{ $origWidth }}" class="form-input h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs focus:border-primary focus:outline-hidden" />
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-xs font-medium text-slate-700">Offset Y dari Atas (px)</label>
                                        <input type="number" wire:model.live="customY" min="0" max="{{ $origHeight }}" class="form-input h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs focus:border-primary focus:outline-hidden" />
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- Position Alignment --}}
                            <div class="space-y-1.5 pt-2 border-t border-slate-150">
                                <label class="text-xs font-medium text-slate-700">Titik Fokus Pemotongan (Alignment)</label>
                                <select wire:model.live="position" class="form-select h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs focus:border-primary focus:outline-hidden">
                                    <option value="center">Tengah Gambar (Center - Rekomendasi)</option>
                                    <option value="top-left">Kiri Atas (Top Left)</option>
                                    <option value="top-right">Kanan Atas (Top Right)</option>
                                    <option value="bottom-left">Kiri Bawah (Bottom Left)</option>
                                    <option value="bottom-right">Kanan Bawah (Bottom Right)</option>
                                </select>
                            </div>
                        @endif

                        {{-- Format Output & Quality --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-3 border-t border-slate-150">
                            {{-- Output Format --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-medium text-slate-700">Format Gambar</label>
                                <select wire:model.live="format" class="form-select h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs focus:border-primary focus:outline-hidden">
                                    <option value="original">Format Asli</option>
                                    <option value="jpg">JPG (Kompresi Ringan)</option>
                                    <option value="png">PNG (Lossless / Transparan)</option>
                                    <option value="webp">WebP (Modern Ringkas)</option>
                                </select>
                            </div>

                            {{-- Quality --}}
                            <div class="space-y-1.5">
                                <label class="text-xs font-medium text-slate-700">Kualitas Output</label>
                                <select wire:model.live="quality" class="form-select h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs focus:border-primary focus:outline-hidden">
                                    <option value="95">Sangat Tinggi (95%)</option>
                                    <option value="90">Optimal (90%) - Rekomendasi</option>
                                    <option value="80">Standar (80%)</option>
                                    <option value="65">Ringan (65%)</option>
                                </select>
                            </div>
                        </div>

                        {{-- Action Button --}}
                        <div class="pt-2">
                            <button wire:click="crop"
                                    wire:loading.attr="disabled"
                                    class="btn h-9 w-full rounded-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus text-xs px-5 shadow-xs transition-colors flex items-center justify-center space-x-2">
                                <span wire:loading.remove wire:target="crop">Potong Gambar Sekarang</span>
                                <span wire:loading wire:target="crop" class="inline-flex items-center space-x-2">
                                    <x-lucide-loader-2 class="size-4 animate-spin" />
                                    <span>Memotong Area Gambar...</span>
                                </span>
                            </button>
                        </div>
                    </div>
                @endif
            @endif
        </div>

        {{-- Right: Result Card / Summary Info (5 Columns) --}}
        <div class="col-span-12 lg:col-span-5 space-y-4 sm:space-y-5">
            @if ($resultPath)
                <div class="card p-5 sm:p-6 text-center space-y-4 border border-slate-150">
                    <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-success/15 text-success">
                        <x-lucide-check-circle-2 class="size-7" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-slate-800">Gambar Berhasil Dipotong!</h3>
                        <p class="mt-1 text-xs text-slate-500">
                            Foto Anda telah dipangkas sesuai rasio aspek yang dipilih secara presisi.
                        </p>
                    </div>

                    {{-- Stats Summary --}}
                    <div class="rounded-lg bg-slate-50 p-3.5 space-y-2 text-left border border-slate-150">
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">Nama File:</span>
                            <span class="font-medium text-slate-800 truncate max-w-[180px]">{{ $resultFilename }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">Dimensi Hasil:</span>
                            <span class="font-semibold text-primary">{{ $finalWidth }} x {{ $finalHeight }} px</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">Dimensi Awal:</span>
                            <span class="font-medium text-slate-600">{{ $origWidth }} x {{ $origHeight }} px</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">Ukuran Berkas:</span>
                            <span class="font-medium text-slate-800">{{ number_format($newSize / 1024, 2) }} KB</span>
                        </div>
                    </div>

                    {{-- Download Button --}}
                    <div class="pt-2 space-y-2">
                        <button wire:click="download" 
                                class="btn h-9 w-full rounded-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus text-xs px-5 shadow-xs flex items-center justify-center space-x-2 transition-colors">
                            <x-lucide-download class="size-4" />
                            <span>Unduh Foto Hasil Crop</span>
                        </button>
                        <button wire:click="resetFile" 
                                class="btn h-9 w-full rounded-full bg-slate-150 font-medium text-slate-700 hover:bg-slate-200 text-xs px-5 transition-colors">
                            Potong Foto Lainnya
                        </button>
                    </div>
                </div>
            @else
                {{-- Information Card --}}
                <div class="card p-5 space-y-3">
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-700">Panduan Crop Gambar</h4>
                    <ul class="space-y-2 text-xs text-slate-500">
                        <li class="flex items-start space-x-2">
                            <x-lucide-check class="size-3.5 text-success shrink-0 mt-0.5" />
                            <span><strong>Rasio Otomatis</strong>: Potong pas foto 2x3, 3x4, 4x6 untuk syarat dokumen resmi.</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <x-lucide-check class="size-3.5 text-success shrink-0 mt-0.5" />
                            <span><strong>Kualitas Terjaga</strong>: Menggunakan kompresi gambar berkualitas tinggi.</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <x-lucide-check class="size-3.5 text-success shrink-0 mt-0.5" />
                            <span><strong>Privasi Terjamin</strong>: Berkas otomatis dibersihkan dari server dalam 24 jam.</span>
                        </li>
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>
