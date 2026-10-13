<div>
    @section('page_description', 'Ubah dimensi ukuran gambar berdasarkan piksel (lebar/tinggi) atau persentase skala dengan mempertahankan rasio aspek.')

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
        {{-- Left: Upload Area, Resize Controls & Action (7 Columns) --}}
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
                        Tingkatkan akun Anda ke paket Pro untuk mengubah ukuran gambar tanpa batasan kuota.
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
                                <x-lucide-maximize-2 class="size-7" />
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
                                Gambar Siap Di-resize
                            </span>
                        @else
                            <div class="mask is-squircle flex size-14 items-center justify-center bg-primary/10 text-primary mb-3">
                                <x-lucide-maximize-2 class="size-7" />
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

                {{-- Resize Settings Controls --}}
                @if($file && !$resultPath)
                    <div class="card p-4 sm:p-5 space-y-4">
                        {{-- Mode Selector Tabs: Berdasarkan Pixel vs Persentase --}}
                        <div class="flex rounded-lg bg-slate-100 p-1">
                            <button type="button" 
                                    wire:click="$set('mode', 'dimensions')"
                                    class="flex flex-1 items-center justify-center space-x-2 rounded-md py-2 text-xs font-medium transition-all {{ $mode === 'dimensions' ? 'bg-white text-slate-800 shadow-xs' : 'text-slate-500 hover:text-slate-700' }}">
                                <x-lucide-move class="size-4" />
                                <span>Berdasarkan Piksel (Dimensi)</span>
                            </button>
                            <button type="button" 
                                    wire:click="$set('mode', 'percentage')"
                                    class="flex flex-1 items-center justify-center space-x-2 rounded-md py-2 text-xs font-medium transition-all {{ $mode === 'percentage' ? 'bg-white text-slate-800 shadow-xs' : 'text-slate-500 hover:text-slate-700' }}">
                                <x-lucide-percent class="size-4" />
                                <span>Berdasarkan Persentase Skala</span>
                            </button>
                        </div>

                        @if($mode === 'dimensions')
                            {{-- Dimension Inputs --}}
                            <div class="space-y-3">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    {{-- Lebar --}}
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-medium text-slate-700">Lebar (Width in px) <span class="text-error">*</span></label>
                                        <input type="number" 
                                               wire:model.live.debounce.400ms="targetWidth" 
                                               min="10" 
                                               max="10000"
                                               class="form-input h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs focus:border-primary focus:outline-hidden" />
                                    </div>

                                    {{-- Tinggi --}}
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-medium text-slate-700">Tinggi (Height in px) <span class="text-error">*</span></label>
                                        <input type="number" 
                                               wire:model.live.debounce.400ms="targetHeight" 
                                               min="10" 
                                               max="10000"
                                               class="form-input h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs focus:border-primary focus:outline-hidden" />
                                    </div>
                                </div>

                                {{-- Lock Aspect Ratio Checkbox --}}
                                <div class="flex items-center justify-between pt-1">
                                    <label class="inline-flex items-center space-x-2 cursor-pointer">
                                        <input type="checkbox" wire:model.live="maintainAspectRatio" class="form-checkbox is-outline size-4 rounded-sm border-slate-400 text-primary checked:border-primary" />
                                        <span class="text-xs font-medium text-slate-700">Kunci Rasio Aspek (Kompensasi Proporsional)</span>
                                    </label>
                                    <span class="badge rounded-full bg-slate-150 text-slate-700 text-xs font-medium">
                                        {{ $origWidth }}x{{ $origHeight }} Asli
                                    </span>
                                </div>

                                {{-- Dimension Presets --}}
                                <div class="space-y-1.5 pt-2 border-t border-slate-150">
                                    <span class="text-xs text-slate-500">Preset Standar:</span>
                                    <div class="flex flex-wrap gap-1.5">
                                        <button type="button" wire:click="setPresetDimension(1920, 1080)" class="btn h-7 rounded-full border border-slate-200 bg-slate-50 px-3 text-xs font-medium text-slate-600 hover:bg-slate-100">
                                            Full HD (1920x1080)
                                        </button>
                                        <button type="button" wire:click="setPresetDimension(1280, 720)" class="btn h-7 rounded-full border border-slate-200 bg-slate-50 px-3 text-xs font-medium text-slate-600 hover:bg-slate-100">
                                            HD (1280x720)
                                        </button>
                                        <button type="button" wire:click="setPresetDimension(1080, 1080)" class="btn h-7 rounded-full border border-slate-200 bg-slate-50 px-3 text-xs font-medium text-slate-600 hover:bg-slate-100">
                                            Instagram Square (1080x1080)
                                        </button>
                                        <button type="button" wire:click="setPresetDimension(1080, 1920)" class="btn h-7 rounded-full border border-slate-200 bg-slate-50 px-3 text-xs font-medium text-slate-600 hover:bg-slate-100">
                                            Story / Reels (1080x1920)
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- Percentage Scaling --}}
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <div class="flex justify-between text-xs">
                                        <span class="font-medium text-slate-700">Skala Ukuran:</span>
                                        <span class="font-semibold text-primary">{{ $percentage }}%</span>
                                    </div>
                                    <div class="grid grid-cols-4 gap-2">
                                        @foreach([25 => '25% (Kecil)', 50 => '50% (Separuh)', 75 => '75% (Tiga Perempat)', 150 => '150% (Perbesar)'] as $pVal => $pLabel)
                                            <button type="button" 
                                                    wire:click="setPercentage({{ $pVal }})"
                                                    class="btn h-8 rounded-lg border border-slate-200 text-xs font-medium {{ $percentage == $pVal ? 'bg-primary text-white border-primary' : 'bg-white text-slate-700 hover:bg-slate-50' }}">
                                                {{ $pLabel }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="rounded-lg bg-slate-50 p-3 text-xs text-slate-500 border border-slate-150">
                                    Hasil estimasi dimensi: <span class="font-semibold text-slate-800">{{ round($origWidth * ($percentage / 100)) }} x {{ round($origHeight * ($percentage / 100)) }} px</span>
                                </div>
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
                                <label class="text-xs font-medium text-slate-700">Kualitas Output ({{ $quality }}%)</label>
                                <select wire:model.live="quality" class="form-select h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs focus:border-primary focus:outline-hidden">
                                    <option value="95">Sangat Tinggi (95%)</option>
                                    <option value="90">Optimal (90%) - Rekomendasi</option>
                                    <option value="80">Standar (80%)</option>
                                    <option value="65">Ringan / Hemat Kuota (65%)</option>
                                </select>
                            </div>
                        </div>

                        {{-- Action Button --}}
                        <div class="pt-2">
                            <button wire:click="resize"
                                    wire:loading.attr="disabled"
                                    class="btn h-9 w-full rounded-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus text-xs px-5 shadow-xs transition-colors flex items-center justify-center space-x-2">
                                <span wire:loading.remove wire:target="resize">Resize Gambar Sekarang</span>
                                <span wire:loading wire:target="resize" class="inline-flex items-center space-x-2">
                                    <x-lucide-loader-2 class="size-4 animate-spin" />
                                    <span>Memproses Ukuran Gambar...</span>
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
                        <h3 class="text-base font-semibold text-slate-800">Gambar Berhasil Di-resize!</h3>
                        <p class="mt-1 text-xs text-slate-500">
                            Dimensi gambar telah disesuaikan secara presisi tanpa distorsi piksel.
                        </p>
                    </div>

                    {{-- Stats Summary --}}
                    <div class="rounded-lg bg-slate-50 p-3.5 space-y-2 text-left border border-slate-150">
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">Nama File:</span>
                            <span class="font-medium text-slate-800 truncate max-w-[180px]">{{ $resultFilename }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">Dimensi Baru:</span>
                            <span class="font-semibold text-primary">{{ $finalWidth }} x {{ $finalHeight }} px</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">Dimensi Semula:</span>
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
                            <span>Unduh Gambar Hasil</span>
                        </button>
                        <button wire:click="resetFile" 
                                class="btn h-9 w-full rounded-full bg-slate-150 font-medium text-slate-700 hover:bg-slate-200 text-xs px-5 transition-colors">
                            Resize Gambar Lainnya
                        </button>
                    </div>
                </div>
            @else
                {{-- Information Card --}}
                <div class="card p-5 space-y-3">
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-700">Panduan Resize Gambar</h4>
                    <ul class="space-y-2 text-xs text-slate-500">
                        <li class="flex items-start space-x-2">
                            <x-lucide-check class="size-3.5 text-success shrink-0 mt-0.5" />
                            <span><strong>Kunci Rasio</strong>: Menjaga bentuk gambar agar tidak tertarik atau gepeng.</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <x-lucide-check class="size-3.5 text-success shrink-0 mt-0.5" />
                            <span><strong>Piksel Tajam</strong>: Algoritma bilinear GD memastikan hasil gambar tetap halus.</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <x-lucide-check class="size-3.5 text-success shrink-0 mt-0.5" />
                            <span><strong>Format Fleksibel</strong>: Konversi langsung ke WebP untuk performa website optimal.</span>
                        </li>
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>
