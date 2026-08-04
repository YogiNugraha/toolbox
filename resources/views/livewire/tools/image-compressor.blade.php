<div class="bg-white border border-hairline rounded-sm p-8">
    <h2 class="text-2xl font-display font-bold text-ink mb-2">Compress Gambar</h2>
    <p class="text-ink-muted text-sm mb-8">Kurangi ukuran file gambar Anda tanpa mengurangi kualitas secara signifikan.</p>

    <!-- Error Message -->
    @if ($errorMsg)
        <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">
            {{ $errorMsg }}
        </div>
    @endif
    @error('file')
        <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">
            {{ $message }}
        </div>
    @enderror

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Left: Upload & Settings -->
        <div class="space-y-6">
            <!-- Upload Area -->
            <div class="w-full">
                <label
                    x-data="{ isDropping: false }"
                    x-on:dragover.prevent="isDropping = true"
                    x-on:dragleave.prevent="isDropping = false"
                    x-on:drop.prevent="isDropping = false; if($event.dataTransfer.files.length > 0) { $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change', { bubbles: true })) }"
                    :class="isDropping ? 'border-amber/50 bg-amber/5' : '{{ $file ? 'border-amber/50' : 'border-hairline' }}'"
                    class="flex flex-col items-center justify-center w-full h-48 border border-dashed rounded-sm cursor-pointer bg-paper hover:bg-amber/5 transition-colors">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        @if ($file)
                            <svg class="w-8 h-8 text-amber mb-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            <p class="mb-2 text-sm text-ink font-semibold truncate px-4 max-w-full">
                                {{ $file->getClientOriginalName() }}</p>
                            <p class="text-[11px] font-mono text-ink-muted">{{ number_format($file->getSize() / 1024, 2) }} KB</p>
                        @else
                            <svg class="w-8 h-8 text-ink-muted mb-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <p class="mb-2 text-sm font-medium text-ink">Klik untuk upload <span class="text-ink-muted font-normal">atau drag and drop</span></p>
                            <p class="font-mono text-[11px] text-ink-muted mt-2 tracking-wide uppercase px-2 py-0.5 border border-hairline rounded-sm bg-white">JPG · PNG · WEBP — MAX 20MB</p>
                        @endif
                    </div>
                    <input type="file" x-ref="fileInput" wire:model="file" class="hidden" accept="image/jpeg, image/png, image/webp" />
                </label>

                <div wire:loading wire:target="file" class="mt-2 text-sm text-amber font-medium">
                    Mengupload...
                </div>
            </div>

            <!-- Presets -->
            @if ($file)
                <div class="bg-paper p-6 rounded-sm border border-hairline mt-6">
                    <h3 class="text-sm font-display font-bold text-ink mb-4">Pilih Kualitas (Preset)</h3>

                    <div class="flex border-b border-hairline mb-5 overflow-x-auto">
                        <label class="px-4 py-2 text-sm font-medium cursor-pointer whitespace-nowrap {{ $preset === 'sosmed' ? 'text-ink border-b-2 border-amber' : 'text-ink-muted hover:text-ink' }}">
                            <input type="radio" wire:model.live="preset" value="sosmed" class="hidden">
                            Sosial Media
                        </label>
                        <label class="px-4 py-2 text-sm font-medium cursor-pointer whitespace-nowrap {{ $preset === 'website' ? 'text-ink border-b-2 border-amber' : 'text-ink-muted hover:text-ink' }}">
                            <input type="radio" wire:model.live="preset" value="website" class="hidden">
                            Website
                        </label>
                        <label class="px-4 py-2 text-sm font-medium cursor-pointer whitespace-nowrap {{ $preset === 'custom' ? 'text-ink border-b-2 border-amber' : 'text-ink-muted hover:text-ink' }}">
                            <input type="radio" wire:model.live="preset" value="custom" class="hidden">
                            Custom
                        </label>
                    </div>

                    <!-- Preset Options Details -->
                    <div class="min-h-[100px]">
                        @if ($preset === 'sosmed')
                            <div class="text-sm text-ink-muted">
                                <p>Maks 1080px, Kualitas 80 (Cepat & Bagus). Cocok untuk upload ke Instagram, Facebook, dsb.</p>
                            </div>
                        @elseif ($preset === 'website')
                            <div class="text-sm text-ink-muted mb-3">
                                <p>Maks 1920px, Kualitas 75 (Loading Cepat). Cocok untuk banner web.</p>
                            </div>
                            <label class="inline-flex items-center">
                                <input type="checkbox" wire:model="websiteConvertToWebp"
                                    class="rounded-sm border-hairline text-amber focus:ring-amber/50">
                                <span class="ml-2 text-xs text-ink">Konversi ke format WebP (Lebih ringan)</span>
                            </label>
                        @elseif ($preset === 'custom')
                            <div class="space-y-4 w-full">
                                <div>
                                    <label class="text-xs font-medium text-ink block mb-2">Kualitas (<span class="font-mono">{{ $customQuality }}%</span>)</label>
                                    <input type="range" wire:model.live="customQuality" min="1" max="100"
                                        class="w-full h-1 bg-hairline rounded-full appearance-none cursor-pointer accent-amber">
                                </div>

                                <div>
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" wire:model.live="customResize"
                                            class="rounded-sm border-hairline text-amber focus:ring-amber/50">
                                        <span class="ml-2 text-xs text-ink">Resize Gambar</span>
                                    </label>

                                    @if ($customResize)
                                        <div class="flex gap-3 mt-3">
                                            <input type="number" wire:model="customWidth"
                                                placeholder="Lebar (px)"
                                                class="w-1/2 rounded-sm border-hairline shadow-sm focus:border-amber focus:ring-amber/20 text-sm font-mono">
                                            <input type="number" wire:model="customHeight"
                                                placeholder="Tinggi (px)"
                                                class="w-1/2 rounded-sm border-hairline shadow-sm focus:border-amber focus:ring-amber/20 text-sm font-mono">
                                        </div>
                                    @endif
                                </div>

                                <div>
                                    <label class="text-xs font-medium text-ink block mb-2">Format Output</label>
                                    <select wire:model="customFormat"
                                        class="w-full rounded-sm border-hairline shadow-sm focus:border-amber focus:ring-amber/20 text-sm">
                                        <option value="original">Original</option>
                                        <option value="jpg">JPG</option>
                                        <option value="png">PNG</option>
                                        <option value="webp">WebP</option>
                                    </select>
                                </div>
                            </div>
                        @endif
                    </div>

                    <button wire:click="compress" wire:loading.attr="disabled"
                        class="w-full mt-6 bg-amber hover:bg-amber/90 text-ink font-medium py-2.5 px-5 rounded-sm transition-colors flex justify-center items-center gap-2">
                        <span wire:loading.remove wire:target="compress">Compress Gambar</span>
                        <span wire:loading wire:target="compress">
                            <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Memproses...
                        </span>
                    </button>
                </div>
            @endif
        </div>

        <!-- Right: Result area -->
        <div>
            @if ($resultPath)
                <div
                    class="bg-white p-6 rounded-sm border border-hairline flex flex-col h-full items-center text-center justify-center">
                    <div
                        class="w-12 h-12 border border-hairline bg-paper text-ink rounded-sm flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-amber" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                    </div>

                    <h3 class="text-lg font-display font-bold text-ink mb-1">Berhasil Dikompres</h3>

                    @php
                        $percentage = $originalSize > 0 ? round((($originalSize - $newSize) / $originalSize) * 100) : 0;
                        $savedClasses = $percentage > 0 ? 'text-amber bg-amber/15' : 'text-ink-muted bg-paper';
                    @endphp

                    <div class="mt-6 w-full max-w-sm">
                        <div class="flex justify-between items-center mb-3 pb-3 border-b border-hairline">
                            <span class="text-sm font-medium text-ink-muted">Ukuran Asli</span>
                            <span class="text-sm font-mono text-ink line-through">{{ number_format($originalSize / 1024, 2) }} KB</span>
                        </div>
                        <div class="flex justify-between items-center mb-3 pb-3 border-b border-hairline">
                            <span class="text-sm font-medium text-ink-muted">Ukuran Baru</span>
                            <span class="text-sm font-mono font-medium text-ink">{{ number_format($newSize / 1024, 2) }} KB</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-ink-muted">Hemat</span>
                            <span class="font-mono text-xs px-2 py-0.5 rounded-sm {{ $savedClasses }}">-{{ $percentage }}%</span>
                        </div>
                    </div>

                    <div class="mt-8 w-full max-w-sm">
                        <button wire:click="download"
                            class="w-full bg-amber hover:bg-amber/90 text-ink font-medium py-2.5 px-5 rounded-sm transition-colors flex justify-center items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Download Gambar
                        </button>
                    </div>
                </div>
            @elseif ($file)
                <div class="bg-paper p-6 rounded-sm border border-hairline flex flex-col h-full items-center justify-center text-center min-h-[300px] relative overflow-hidden">
                    <img src="{{ $file->temporaryUrl() }}" class="max-w-full max-h-[300px] object-contain rounded-sm" alt="Preview">
                    <div class="absolute bottom-4 left-0 right-0 flex justify-center">
                        <span class="bg-ink/80 text-white font-mono text-[10px] px-2 py-1 rounded-sm uppercase tracking-wider backdrop-blur-sm shadow-sm">
                            Ready to Compress
                        </span>
                    </div>
                </div>
            @else
                <div
                    class="bg-paper p-6 rounded-sm border border-hairline flex flex-col h-full items-center justify-center text-center min-h-[300px]">
                    <div class="w-12 h-12 border border-hairline bg-white text-ink-muted rounded-sm flex items-center justify-center mb-4">
                        <x-lucide-image class="w-5 h-5 text-ink-muted" />
                    </div>
                    <p class="font-mono text-xs text-ink-muted uppercase tracking-wide max-w-[200px]">PREVIEW AREA</p>
                </div>
            @endif
        </div>
    </div>
</div>
