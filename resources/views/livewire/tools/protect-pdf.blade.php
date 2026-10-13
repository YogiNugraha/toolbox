<div>
    @section('page_description', 'Kunci dokumen PDF dengan enkripsi password militer (AES-256) untuk mencegah akses tidak sah.')

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
        {{-- Left: Upload Area, Security Options & Action (7 Columns) --}}
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
                        Tingkatkan akun Anda ke paket Pro untuk memproteksi dan mengonversi dokumen tanpa batasan kuota.
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
                                <x-lucide-file-lock-2 class="size-7" />
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
                                File PDF Siap Diproteksi
                            </span>
                        @else
                            <div class="mask is-squircle flex size-14 items-center justify-center bg-primary/10 text-primary mb-3">
                                <x-lucide-lock class="size-7" />
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

                {{-- Password & Security Configuration Form --}}
                @if($file && !$resultPath)
                    <div x-data="{ showPass: false, showConfirm: false }" class="card p-4 sm:p-5 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-150 pb-3">
                            <div class="flex items-center space-x-2">
                                <x-lucide-shield class="size-4 text-primary" />
                                <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-700">Pengaturan Enkripsi & Keamanan</h4>
                            </div>
                            <span class="badge rounded-full bg-success/15 text-success text-xs font-medium">AES-256 Bit</span>
                        </div>

                        {{-- Password Pembuka Input --}}
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium text-slate-700">
                                Password Pembuka Dokumen (User Password) <span class="text-error">*</span>
                            </label>
                            <div class="relative">
                                <input :type="showPass ? 'text' : 'password'"
                                       wire:model="userPassword"
                                       placeholder="Masukkan kata sandi untuk membuka PDF..."
                                       class="form-input h-9 w-full rounded-lg border border-slate-300 bg-white px-3 pr-10 text-xs placeholder:text-slate-400 focus:border-primary focus:outline-hidden" />
                                <button type="button" 
                                        @click="showPass = !showPass"
                                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                                    <template x-if="!showPass">
                                        <x-lucide-eye class="size-4" />
                                    </template>
                                    <template x-if="showPass">
                                        <x-lucide-eye-off class="size-4" />
                                    </template>
                                </button>
                            </div>
                            @error('userPassword')
                                <p class="text-xs text-error mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Konfirmasi Password Input --}}
                        <div class="space-y-1.5">
                            <label class="text-xs font-medium text-slate-700">
                                Konfirmasi Password <span class="text-error">*</span>
                            </label>
                            <div class="relative">
                                <input :type="showConfirm ? 'text' : 'password'"
                                       wire:model="passwordConfirmation"
                                       placeholder="Ulangi kata sandi di atas..."
                                       class="form-input h-9 w-full rounded-lg border border-slate-300 bg-white px-3 pr-10 text-xs placeholder:text-slate-400 focus:border-primary focus:outline-hidden" />
                                <button type="button" 
                                        @click="showConfirm = !showConfirm"
                                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                                    <template x-if="!showConfirm">
                                        <x-lucide-eye class="size-4" />
                                    </template>
                                    <template x-if="showConfirm">
                                        <x-lucide-eye-off class="size-4" />
                                    </template>
                                </button>
                            </div>
                            @error('passwordConfirmation')
                                <p class="text-xs text-error mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Hak Akses Dokumen (Permissions) --}}
                        <div class="pt-2 border-t border-slate-150 space-y-3">
                            <p class="text-xs font-medium text-slate-700">Hak Akses Pembaca Dokumen:</p>

                            <div class="flex items-center justify-between py-1">
                                <div>
                                    <span class="text-xs font-medium text-slate-700">Izinkan Cetak (Printing)</span>
                                    <p class="text-xs text-slate-400">Pembaca dapat mencetak dokumen PDF ke kertas</p>
                                </div>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="allowPrint" class="form-checkbox is-outline size-4 rounded-sm border-slate-400/70 text-primary checked:border-primary" />
                                </label>
                            </div>

                            <div class="flex items-center justify-between py-1">
                                <div>
                                    <span class="text-xs font-medium text-slate-700">Izinkan Salin Teks & Gambar (Copy)</span>
                                    <p class="text-xs text-slate-400">Pembaca dapat memilih dan menyalin isi konten teks</p>
                                </div>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="allowCopy" class="form-checkbox is-outline size-4 rounded-sm border-slate-400/70 text-primary checked:border-primary" />
                                </label>
                            </div>
                        </div>

                        {{-- Action Button --}}
                        <div class="pt-2">
                            <button wire:click="protect"
                                    wire:loading.attr="disabled"
                                    class="btn h-9 w-full rounded-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus text-xs px-5 shadow-xs transition-colors flex items-center justify-center space-x-2">
                                <span wire:loading.remove wire:target="protect">Kunci PDF Sekarang</span>
                                <span wire:loading wire:target="protect" class="inline-flex items-center space-x-2">
                                    <x-lucide-loader-2 class="size-4 animate-spin" />
                                    <span>Menerapkan Enkripsi AES-256...</span>
                                </span>
                            </button>
                        </div>
                    </div>
                @endif
            @endif
        </div>

        {{-- Right: Result Card / Info Panel (5 Columns) --}}
        <div class="col-span-12 lg:col-span-5 space-y-4 sm:space-y-5">
            @if ($resultPath)
                <div class="card p-5 sm:p-6 text-center space-y-4 border border-slate-150">
                    <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-success/15 text-success">
                        <x-lucide-shield-check class="size-7" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-slate-800">PDF Berhasil Diproteksi!</h3>
                        <p class="mt-1 text-xs text-slate-500">
                            Dokumen PDF Anda kini terkunci dengan enkripsi AES-256 bit dan membutuhkan password untuk dibuka.
                        </p>
                    </div>

                    {{-- Stats Summary --}}
                    <div class="rounded-lg bg-slate-50 p-3.5 space-y-2 text-left border border-slate-150">
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">Nama File:</span>
                            <span class="font-medium text-slate-800 truncate max-w-[180px]">{{ $resultFilename }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">Total Halaman:</span>
                            <span class="font-medium text-slate-800">{{ $totalPages }} Halaman</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">Ukuran Terenkripsi:</span>
                            <span class="font-medium text-slate-800">{{ number_format($newSize / 1024, 2) }} KB</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">Tipe Enkripsi:</span>
                            <span class="badge rounded-full bg-success/15 text-success text-xs font-medium">AES-256 Standard</span>
                        </div>
                    </div>

                    {{-- Download Button --}}
                    <div class="pt-2 space-y-2">
                        <button wire:click="download" 
                                class="btn h-9 w-full rounded-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus text-xs px-5 shadow-xs flex items-center justify-center space-x-2 transition-colors">
                            <x-lucide-download class="size-4" />
                            <span>Unduh PDF Terproteksi</span>
                        </button>
                        <button wire:click="resetFile" 
                                class="btn h-9 w-full rounded-full bg-slate-150 font-medium text-slate-700 hover:bg-slate-200 text-xs px-5 transition-colors">
                            Kunci Dokumen Lainnya
                        </button>
                    </div>
                </div>
            @else
                {{-- Information Card --}}
                <div class="card p-5 space-y-4">
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-700">Keamanan Standar Industri</h4>
                    <ul class="space-y-3 text-xs text-slate-500">
                        <li class="flex items-start space-x-2.5">
                            <x-lucide-shield-alert class="size-4 text-primary shrink-0 mt-0.5" />
                            <span><strong>Enkripsi AES-256 Bit</strong>: Standar keamanan militer yang tidak dapat ditembus dengan metode serangan biasa.</span>
                        </li>
                        <li class="flex items-start space-x-2.5">
                            <x-lucide-lock class="size-4 text-primary shrink-0 mt-0.5" />
                            <span><strong>Izin Fleksibel</strong>: Batasi kemampuan pencetakan dan penyalinan teks sesuai kebutuhan dokumen rahasia Anda.</span>
                        </li>
                        <li class="flex items-start space-x-2.5">
                            <x-lucide-clock class="size-4 text-primary shrink-0 mt-0.5" />
                            <span><strong>Penghapusan Otomatis</strong>: Seluruh file sementara akan dihapus secara permanen dari server dalam 24 jam.</span>
                        </li>
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>
