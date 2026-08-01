<div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8 bg-white shadow-xl rounded-2xl">
    <h2 class="text-2xl font-bold text-gray-800 mb-2">Compress Gambar</h2>
    <p class="text-gray-600 mb-8">Kurangi ukuran file gambar Anda tanpa mengurangi kualitas secara signifikan.</p>

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
                    class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed rounded-xl cursor-pointer bg-gray-50 hover:bg-gray-100 {{ $file ? 'border-indigo-500' : 'border-gray-300' }} transition-colors">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        @if ($file)
                            <svg class="w-10 h-10 text-indigo-500 mb-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            <p class="mb-2 text-sm text-gray-700 font-semibold truncate px-4 max-w-full">
                                {{ $file->getClientOriginalName() }}</p>
                            <p class="text-xs text-gray-500">{{ number_format($file->getSize() / 1024, 2) }} KB</p>
                        @else
                            <svg class="w-10 h-10 text-gray-400 mb-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                </path>
                            </svg>
                            <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Klik untuk upload</span>
                                atau drag and drop</p>
                            <p class="text-xs text-gray-400">JPG, PNG, WEBP (Max 10MB)</p>
                        @endif
                    </div>
                    <input type="file" wire:model="file" class="hidden" accept="image/jpeg, image/png, image/webp" />
                </label>

                <div wire:loading wire:target="file" class="mt-2 text-sm text-indigo-600 font-medium">
                    Mengupload...
                </div>
            </div>

            <!-- Presets -->
            @if ($file)
                <div class="bg-gray-50 p-5 rounded-xl border border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Pilih Kualitas (Preset)</h3>

                    <div class="space-y-3">
                        <!-- Sosmed -->
                        <label
                            class="flex items-start p-3 bg-white border rounded-lg cursor-pointer hover:border-indigo-500 {{ $preset === 'sosmed' ? 'border-indigo-500 ring-1 ring-indigo-500' : 'border-gray-200' }}">
                            <div class="shrink-0 mt-0.5">
                                <input type="radio" wire:model.live="preset" value="sosmed"
                                    class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                            </div>
                            <div class="ml-3">
                                <span class="block text-sm font-medium text-gray-900">📱 Sosial Media</span>
                                <span class="block text-xs text-gray-500">Maks 1080px, Kualitas 80 (Cepat &
                                    Bagus)</span>
                            </div>
                        </label>

                        <!-- Website -->
                        <label
                            class="flex items-start p-3 bg-white border rounded-lg cursor-pointer hover:border-indigo-500 {{ $preset === 'website' ? 'border-indigo-500 ring-1 ring-indigo-500' : 'border-gray-200' }}">
                            <div class="shrink-0 mt-0.5">
                                <input type="radio" wire:model.live="preset" value="website"
                                    class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                            </div>
                            <div class="ml-3">
                                <span class="block text-sm font-medium text-gray-900">🌐 Website</span>
                                <span class="block text-xs text-gray-500">Maks 1920px, Kualitas 75 (Loading
                                    Cepat)</span>

                                @if ($preset === 'website')
                                    <div class="mt-2 pl-1">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" wire:model="websiteConvertToWebp"
                                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <span class="ml-2 text-xs text-gray-700">Konversi ke format WebP (Lebih
                                                ringan)</span>
                                        </label>
                                    </div>
                                @endif
                            </div>
                        </label>

                        <!-- Custom -->
                        <label
                            class="flex items-start p-3 bg-white border rounded-lg cursor-pointer hover:border-indigo-500 {{ $preset === 'custom' ? 'border-indigo-500 ring-1 ring-indigo-500' : 'border-gray-200' }}">
                            <div class="shrink-0 mt-0.5">
                                <input type="radio" wire:model.live="preset" value="custom"
                                    class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                            </div>
                            <div class="ml-3 w-full">
                                <span class="block text-sm font-medium text-gray-900">⚙️ Custom</span>
                                <span class="block text-xs text-gray-500">Atur manual kualitas dan resolusi</span>

                                @if ($preset === 'custom')
                                    <div class="mt-3 space-y-3 w-full pr-2">
                                        <div>
                                            <label class="text-xs text-gray-600 block mb-1">Kualitas
                                                ({{ $customQuality }}%)</label>
                                            <input type="range" wire:model="customQuality" min="1"
                                                max="100"
                                                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                                        </div>

                                        <div>
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" wire:model.live="customResize"
                                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                <span class="ml-2 text-xs text-gray-700">Resize Gambar</span>
                                            </label>

                                            @if ($customResize)
                                                <div class="flex gap-2 mt-2">
                                                    <input type="number" wire:model="customWidth"
                                                        placeholder="Lebar (px)"
                                                        class="w-1/2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                                    <input type="number" wire:model="customHeight"
                                                        placeholder="Tinggi (px)"
                                                        class="w-1/2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                                </div>
                                            @endif
                                        </div>

                                        <div>
                                            <label class="text-xs text-gray-600 block mb-1">Format Output</label>
                                            <select wire:model="customFormat"
                                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                                <option value="original">Original</option>
                                                <option value="jpg">JPG</option>
                                                <option value="png">PNG</option>
                                                <option value="webp">WebP</option>
                                            </select>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </label>
                    </div>

                    <button wire:click="compress" wire:loading.attr="disabled"
                        class="w-full mt-5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-xl shadow-sm transition-all flex justify-center items-center gap-2">
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
                    class="bg-indigo-50 p-6 rounded-2xl border border-indigo-100 flex flex-col h-full items-center text-center">
                    <div
                        class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                    </div>

                    <h3 class="text-lg font-bold text-gray-800 mb-1">Berhasil Dikompres!</h3>

                    @php
                        $percentage = $originalSize > 0 ? round((($originalSize - $newSize) / $originalSize) * 100) : 0;
                        $savedClasses = $percentage > 0 ? 'text-green-600 font-bold' : 'text-gray-600 font-bold';
                    @endphp

                    <div class="mt-4 bg-white p-4 rounded-xl w-full border border-indigo-50 shadow-sm">
                        <div class="flex justify-between items-center mb-2 pb-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500">Ukuran Asli</span>
                            <span
                                class="text-sm font-semibold text-gray-700">{{ number_format($originalSize / 1024, 2) }}
                                KB</span>
                        </div>
                        <div class="flex justify-between items-center mb-2 pb-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500">Ukuran Baru</span>
                            <span class="text-sm font-semibold text-gray-900">{{ number_format($newSize / 1024, 2) }}
                                KB</span>
                        </div>
                        <div class="flex justify-between items-center pt-1">
                            <span class="text-sm font-medium text-gray-600">Hemat</span>
                            <span class="text-lg {{ $savedClasses }}">{{ $percentage }}%</span>
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
                    <p class="text-gray-500 text-sm">Upload dan compress gambar untuk melihat hasilnya di sini.</p>
                </div>
            @endif
        </div>
    </div>
</div>
