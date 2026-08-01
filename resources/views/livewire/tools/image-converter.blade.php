<div class="bg-white border border-hairline rounded-sm p-8">
    <h2 class="text-2xl font-display font-bold text-ink mb-2">Convert Format Gambar</h2>
    <p class="text-ink-muted text-sm mb-8">Ubah format gambar Anda dari dan ke JPG, PNG, WebP, dan format lainnya.</p>

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
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                            <p class="mb-1 text-sm text-ink font-semibold truncate px-4 max-w-full">
                                {{ $file->getClientOriginalName() }}</p>
                            <p class="font-mono text-[11px] text-ink-muted mt-1 uppercase">{{ strtoupper($file->getClientOriginalExtension()) }} •
                                {{ number_format($file->getSize() / 1024, 2) }} KB</p>
                        @else
                            <svg class="w-8 h-8 text-ink-muted mb-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <p class="mb-2 text-sm font-medium text-ink">Klik untuk upload <span class="text-ink-muted font-normal">atau drag and drop</span></p>
                            <p class="font-mono text-[11px] text-ink-muted mt-2 tracking-wide uppercase px-2 py-0.5 border border-hairline rounded-sm bg-white">JPG · PNG · WEBP · GIF — MAX 10MB</p>
                        @endif
                    </div>
                    <input type="file" x-ref="fileInput" wire:model="file" class="hidden"
                        accept="image/jpeg, image/png, image/webp, image/gif, image/bmp" />
                </label>

                <div wire:loading wire:target="file" class="mt-2 text-sm text-amber font-medium">
                    Mengupload...
                </div>
            </div>

            @if ($file)
                <div class="bg-paper p-6 rounded-sm border border-hairline mt-6">
                    <h3 class="text-sm font-display font-bold text-ink mb-4">Pilih Format Output</h3>

                    <div class="flex flex-wrap gap-2 mb-6">
                        @foreach(['jpg', 'png', 'webp', 'gif'] as $format)
                            <label class="px-4 py-2 text-sm font-mono font-medium cursor-pointer border rounded-sm transition-colors {{ $outputFormat === $format ? 'text-amber border-amber bg-amber/5' : 'text-ink-muted border-hairline bg-white hover:border-amber/50' }}">
                                <input type="radio" wire:model.live="outputFormat" value="{{ $format }}" class="hidden">
                                {{ strtoupper($format) }}
                            </label>
                        @endforeach
                    </div>

                    <button wire:click="convert" wire:loading.attr="disabled"
                        class="w-full mt-4 bg-amber hover:bg-amber/90 text-ink font-medium py-2.5 px-5 rounded-sm transition-colors flex justify-center items-center gap-2">
                        <span wire:loading.remove wire:target="convert">Ubah Format</span>
                        <span wire:loading wire:target="convert">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-ink" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="3"></circle>
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

                    <h3 class="text-lg font-display font-bold text-ink mb-1">Konversi Berhasil</h3>

                    <div class="mt-6 w-full max-w-sm">
                        <div class="flex justify-between items-center mb-3 pb-3 border-b border-hairline">
                            <span class="text-sm font-medium text-ink-muted">Format Asli</span>
                            <span
                                class="text-sm font-mono text-ink">{{ strtoupper($file->getClientOriginalExtension()) }}
                                ({{ number_format($originalSize / 1024, 2) }} KB)</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-ink-muted">Format Baru</span>
                            <span class="text-sm font-mono font-medium text-amber">{{ strtoupper($resultExtension) }}
                                ({{ number_format($newSize / 1024, 2) }} KB)</span>
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
            @else
                <div
                    class="bg-paper p-6 rounded-sm border border-hairline flex flex-col h-full items-center justify-center text-center min-h-[300px]">
                    <div class="w-12 h-12 border border-hairline bg-white text-ink-muted rounded-sm flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                    <p class="font-mono text-xs text-ink-muted uppercase tracking-wide max-w-[200px]">PREVIEW AREA</p>
                </div>
            @endif
        </div>
    </div>
</div>
