<div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8 bg-white shadow-xl rounded-2xl">
    <h2 class="text-2xl font-bold text-gray-800 mb-2">Convert Format Gambar</h2>
    <p class="text-gray-600 mb-8">Ubah format gambar Anda dari dan ke JPG, PNG, WebP, dan format lainnya.</p>

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
                    class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed rounded-xl cursor-pointer bg-gray-50 hover:bg-gray-100 {{ $file ? 'border-indigo-500' : 'border-gray-300' }} transition-colors">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        @if ($file)
                            <svg class="w-10 h-10 text-indigo-500 mb-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                            <p class="mb-1 text-sm text-gray-700 font-semibold truncate px-4 max-w-full">
                                {{ $file->getClientOriginalName() }}</p>
                            <p class="text-xs text-gray-500">{{ strtoupper($file->getClientOriginalExtension()) }} •
                                {{ number_format($file->getSize() / 1024, 2) }} KB</p>
                        @else
                            <svg class="w-10 h-10 text-gray-400 mb-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Klik untuk upload</span>
                                atau drag and drop</p>
                            <p class="text-xs text-gray-400">JPG, PNG, WEBP, GIF (Max 10MB)</p>
                        @endif
                    </div>
                    <input type="file" wire:model="file" class="hidden"
                        accept="image/jpeg, image/png, image/webp, image/gif, image/bmp" />
                </label>

                <div wire:loading wire:target="file" class="mt-2 text-sm text-indigo-600 font-medium">
                    Mengupload...
                </div>
            </div>

            @if ($file)
                <div class="bg-gray-50 p-5 rounded-xl border border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Format Output</h3>

                    <div class="grid grid-cols-2 gap-3">
                        <label
                            class="flex items-center p-3 bg-white border rounded-lg cursor-pointer hover:border-indigo-500 {{ $outputFormat === 'jpg' ? 'border-indigo-500 ring-1 ring-indigo-500' : 'border-gray-200' }}">
                            <input type="radio" wire:model.live="outputFormat" value="jpg"
                                class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                            <span class="ml-2 text-sm font-medium text-gray-900">JPG</span>
                        </label>
                        <label
                            class="flex items-center p-3 bg-white border rounded-lg cursor-pointer hover:border-indigo-500 {{ $outputFormat === 'png' ? 'border-indigo-500 ring-1 ring-indigo-500' : 'border-gray-200' }}">
                            <input type="radio" wire:model.live="outputFormat" value="png"
                                class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                            <span class="ml-2 text-sm font-medium text-gray-900">PNG</span>
                        </label>
                        <label
                            class="flex items-center p-3 bg-white border rounded-lg cursor-pointer hover:border-indigo-500 {{ $outputFormat === 'webp' ? 'border-indigo-500 ring-1 ring-indigo-500' : 'border-gray-200' }}">
                            <input type="radio" wire:model.live="outputFormat" value="webp"
                                class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                            <span class="ml-2 text-sm font-medium text-gray-900">WebP</span>
                        </label>
                        <label
                            class="flex items-center p-3 bg-white border rounded-lg cursor-pointer hover:border-indigo-500 {{ $outputFormat === 'gif' ? 'border-indigo-500 ring-1 ring-indigo-500' : 'border-gray-200' }}">
                            <input type="radio" wire:model.live="outputFormat" value="gif"
                                class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                            <span class="ml-2 text-sm font-medium text-gray-900">GIF</span>
                        </label>
                    </div>

                    <button wire:click="convert" wire:loading.attr="disabled"
                        class="w-full mt-5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-xl shadow-sm transition-all flex justify-center items-center gap-2">
                        <span wire:loading.remove wire:target="convert">Ubah Format</span>
                        <span wire:loading wire:target="convert">
                            <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
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
                    class="bg-indigo-50 p-6 rounded-2xl border border-indigo-100 flex flex-col h-full items-center text-center">
                    <div
                        class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                    </div>

                    <h3 class="text-lg font-bold text-gray-800 mb-1">Konversi Berhasil!</h3>

                    <div class="mt-4 bg-white p-4 rounded-xl w-full border border-indigo-50 shadow-sm">
                        <div class="flex justify-between items-center mb-2 pb-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500">Format Asli</span>
                            <span
                                class="text-sm font-semibold text-gray-700">{{ strtoupper($file->getClientOriginalExtension()) }}
                                ({{ number_format($originalSize / 1024, 2) }} KB)</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Format Baru</span>
                            <span class="text-sm font-bold text-indigo-600">{{ strtoupper($resultExtension) }}
                                ({{ number_format($newSize / 1024, 2) }} KB)</span>
                        </div>
                    </div>

                    <div class="mt-6 w-full">
                        <button wire:click="download"
                            class="w-full bg-green-500 hover:bg-green-600 text-white font-semibold py-3 px-4 rounded-xl shadow transition-colors flex justify-center items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Download Gambar
                        </button>
                    </div>
                </div>
            @else
                <div
                    class="bg-gray-50 p-6 rounded-2xl border border-dashed border-gray-200 flex flex-col h-full items-center justify-center text-center min-h-75">
                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    <p class="text-gray-500 text-sm">Upload gambar dan pilih format baru untuk melihat hasilnya di
                        sini.</p>
                </div>
            @endif
        </div>
    </div>
</div>
