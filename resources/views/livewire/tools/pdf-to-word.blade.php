<div class="max-w-4xl mx-auto p-4 sm:p-6 lg:p-8 bg-white shadow-xl rounded-2xl">
    <h2 class="text-2xl font-bold text-gray-800 mb-2">PDF ke Word</h2>
    <p class="text-gray-600 mb-8">Konversi dokumen PDF Anda menjadi format Word (.docx) yang dapat diedit dengan mudah.
    </p>

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
        <!-- Left: Upload Area -->
        <div class="space-y-6">
            <div class="w-full">
                <label
                    class="flex flex-col items-center justify-center w-full h-56 border-2 border-dashed rounded-xl cursor-pointer bg-gray-50 hover:bg-gray-100 {{ $file ? 'border-indigo-500' : 'border-gray-300' }} transition-colors">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        @if ($file)
                            <svg class="w-12 h-12 text-indigo-500 mb-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            <p class="mb-2 text-sm text-gray-700 font-semibold truncate px-4 max-w-full">
                                {{ $file->getClientOriginalName() }}</p>
                            <p class="text-xs text-gray-500">{{ number_format($file->getSize() / 1024 / 1024, 2) }} MB
                            </p>
                        @else
                            <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                </path>
                            </svg>
                            <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Klik untuk upload</span>
                                atau drag and drop</p>
                            <p class="text-xs text-gray-400">Hanya PDF (Max 20MB)</p>
                        @endif
                    </div>
                    <input type="file" wire:model="file" class="hidden" accept="application/pdf" />
                </label>

                <div wire:loading wire:target="file" class="mt-2 text-sm text-indigo-600 font-medium">
                    Mengupload...
                </div>
            </div>

            @if ($file && !$status)
                <button wire:click="convert" wire:loading.attr="disabled"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-xl shadow transition-all flex justify-center items-center gap-2">
                    <span wire:loading.remove wire:target="convert">Mulai Konversi</span>
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
            @endif
        </div>

        <!-- Right: Status & Result -->
        <div class="h-full">
            @if ($status === 'processing')
                <div class="bg-indigo-50 p-6 rounded-2xl border border-indigo-100 flex flex-col h-full items-center justify-center text-center"
                    wire:poll.2s="checkStatus">
                    <svg class="animate-spin w-12 h-12 text-indigo-500 mb-4" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Sedang Mengkonversi...</h3>
                    <p class="text-sm text-gray-600">Mohon tunggu, proses ini mungkin memakan waktu beberapa saat
                        tergantung ukuran PDF Anda.</p>
                </div>
            @elseif ($status === 'completed')
                <div
                    class="bg-green-50 p-6 rounded-2xl border border-green-100 flex flex-col h-full items-center justify-center text-center">
                    <div
                        class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Konversi Berhasil!</h3>

                    <div class="mt-4 bg-white p-4 rounded-xl w-full border border-green-100 shadow-sm mb-6">
                        <div class="flex justify-between items-center mb-2 pb-2 border-b border-gray-100">
                            <span class="text-sm text-gray-500">PDF Asli</span>
                            <span
                                class="text-sm font-semibold text-gray-700">{{ number_format($originalSize / 1024, 2) }}
                                KB</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Word (DOCX)</span>
                            <span class="text-sm font-semibold text-gray-900">{{ number_format($newSize / 1024, 2) }}
                                KB</span>
                        </div>
                    </div>

                    <button wire:click="download"
                        class="w-full bg-green-500 hover:bg-green-600 text-white font-semibold py-3 px-4 rounded-xl shadow transition-colors flex justify-center items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download Word
                    </button>
                </div>
            @elseif ($status === 'failed')
                <div
                    class="bg-red-50 p-6 rounded-2xl border border-red-100 flex flex-col h-full items-center justify-center text-center">
                    <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Konversi Gagal</h3>
                    <p class="text-sm text-red-600 mb-4">{{ $errorMsg }}</p>
                    <button wire:click="resetResult"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg text-sm font-medium transition-colors">Coba
                        Lagi</button>
                </div>
            @else
                <div
                    class="bg-gray-50 p-6 rounded-2xl border border-dashed border-gray-200 flex flex-col h-full items-center justify-center text-center min-h-75">
                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    <p class="text-gray-500 text-sm">Upload PDF dan klik tombol konversi untuk melihat hasilnya di
                        sini.</p>
                </div>
            @endif
        </div>
    </div>
</div>
