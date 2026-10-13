<div>
    @section('page_description', 'Buat kode QR custom berkualitas tinggi untuk URL, WhatsApp, Wi-Fi, vCard, atau teks dengan opsi logo dan download PNG/SVG.')

    {{-- Error Flash Alert --}}
    @if (!empty($errorMsg))
        <div class="alert flex rounded-lg border border-error px-4 py-3 text-error sm:px-5 mb-6">
            <x-lucide-alert-circle class="size-5 shrink-0 mr-2.5" />
            <span class="text-xs">{{ $errorMsg }}</span>
        </div>
    @endif
    @error('logo')
        <div class="alert flex rounded-lg border border-error px-4 py-3 text-error sm:px-5 mb-6">
            <x-lucide-alert-circle class="size-5 shrink-0 mr-2.5" />
            <span class="text-xs">{{ $message }}</span>
        </div>
    @enderror

    <div class="grid grid-cols-1 gap-4 sm:gap-5 lg:grid-cols-12 lg:gap-6">
        {{-- Left: Configuration & Input Controls (7 Columns) --}}
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
                        <span class="font-semibold text-primary">{{ $remainingQuota }} / {{ $monthlyLimit ?? 15 }}</span>
                    </div>
                @else
                    <div class="badge rounded-full bg-success/15 text-success px-3 py-1 text-xs font-medium">
                        Kuota: {{ $isPro ? 'Unlimited (Pro)' : 'Tanpa Batas' }}
                    </div>
                @endif

                @if($resultPath)
                    <button wire:click="resetResult" class="text-xs text-primary hover:underline flex items-center space-x-1 font-medium cursor-pointer">
                        <x-lucide-refresh-cw class="size-3.5" />
                        <span>Buat QR Baru</span>
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
                        Tingkatkan akun Anda ke paket Pro untuk men-generate kode QR tanpa batasan kuota.
                    </p>
                    <a href="{{ route('pricing') }}" class="btn h-9 rounded-full mt-4 bg-primary font-medium text-white shadow-xs text-xs px-6">
                        Upgrade ke Pro Sekarang
                    </a>
                </div>
            @else

                {{-- Step 1: Content Type Selection --}}
                <div class="card p-4 sm:p-5">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-3 mb-4">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">1. Pilih Tipe Konten QR</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Tentukan jenis data yang ingin dimasukkan ke dalam QR Code</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                        {{-- URL --}}
                        <button type="button" 
                                wire:click="setContentType('url')"
                                class="flex items-center space-x-2.5 p-3 rounded-lg border text-left transition-all cursor-pointer {{ $contentType === 'url' ? 'border-primary bg-primary/5 text-primary font-medium' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                            <div class="flex size-8 items-center justify-center rounded-lg {{ $contentType === 'url' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600' }}">
                                <x-lucide-globe class="size-4" />
                            </div>
                            <div>
                                <span class="text-xs block font-medium">Link URL</span>
                                <span class="text-xs text-slate-400 block">Situs web</span>
                            </div>
                        </button>

                        {{-- WhatsApp --}}
                        <button type="button" 
                                wire:click="setContentType('whatsapp')"
                                class="flex items-center space-x-2.5 p-3 rounded-lg border text-left transition-all cursor-pointer {{ $contentType === 'whatsapp' ? 'border-primary bg-primary/5 text-primary font-medium' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                            <div class="flex size-8 items-center justify-center rounded-lg {{ $contentType === 'whatsapp' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600' }}">
                                <x-lucide-message-circle class="size-4" />
                            </div>
                            <div>
                                <span class="text-xs block font-medium">WhatsApp</span>
                                <span class="text-xs text-slate-400 block">Chat langsung</span>
                            </div>
                        </button>

                        {{-- Wi-Fi --}}
                        <button type="button" 
                                wire:click="setContentType('wifi')"
                                class="flex items-center space-x-2.5 p-3 rounded-lg border text-left transition-all cursor-pointer {{ $contentType === 'wifi' ? 'border-primary bg-primary/5 text-primary font-medium' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                            <div class="flex size-8 items-center justify-center rounded-lg {{ $contentType === 'wifi' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600' }}">
                                <x-lucide-wifi class="size-4" />
                            </div>
                            <div>
                                <span class="text-xs block font-medium">Jaringan Wi-Fi</span>
                                <span class="text-xs text-slate-400 block">Koneksi otomatis</span>
                            </div>
                        </button>

                        {{-- vCard --}}
                        <button type="button" 
                                wire:click="setContentType('vcard')"
                                class="flex items-center space-x-2.5 p-3 rounded-lg border text-left transition-all cursor-pointer {{ $contentType === 'vcard' ? 'border-primary bg-primary/5 text-primary font-medium' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                            <div class="flex size-8 items-center justify-center rounded-lg {{ $contentType === 'vcard' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600' }}">
                                <x-lucide-contact class="size-4" />
                            </div>
                            <div>
                                <span class="text-xs block font-medium">Kontak (vCard)</span>
                                <span class="text-xs text-slate-400 block">Kartu nama digital</span>
                            </div>
                        </button>

                        {{-- Text --}}
                        <button type="button" 
                                wire:click="setContentType('text')"
                                class="flex items-center space-x-2.5 p-3 rounded-lg border text-left transition-all cursor-pointer {{ $contentType === 'text' ? 'border-primary bg-primary/5 text-primary font-medium' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                            <div class="flex size-8 items-center justify-center rounded-lg {{ $contentType === 'text' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600' }}">
                                <x-lucide-type class="size-4" />
                            </div>
                            <div>
                                <span class="text-xs block font-medium">Teks Bebas</span>
                                <span class="text-xs text-slate-400 block">Pesan atau catatan</span>
                            </div>
                        </button>

                        {{-- Email --}}
                        <button type="button" 
                                wire:click="setContentType('email')"
                                class="flex items-center space-x-2.5 p-3 rounded-lg border text-left transition-all cursor-pointer {{ $contentType === 'email' ? 'border-primary bg-primary/5 text-primary font-medium' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                            <div class="flex size-8 items-center justify-center rounded-lg {{ $contentType === 'email' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600' }}">
                                <x-lucide-mail class="size-4" />
                            </div>
                            <div>
                                <span class="text-xs block font-medium">Kirim Email</span>
                                <span class="text-xs text-slate-400 block">Mailto template</span>
                            </div>
                        </button>
                    </div>
                </div>

                {{-- Step 2: Input Data Form --}}
                <div class="card p-4 sm:p-5">
                    <div class="border-b border-slate-200 pb-3 mb-4">
                        <h3 class="text-sm font-semibold text-slate-800">2. Masukkan Informasi QR Code</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Isi data yang akan ditampilkan saat QR Code dipindai</p>
                    </div>

                    {{-- Form: URL --}}
                    @if($contentType === 'url')
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1.5">Alamat URL Web</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                    <x-lucide-link class="size-4" />
                                </div>
                                <input type="url" 
                                       wire:model.live.debounce.300ms="url"
                                       placeholder="https://example.com"
                                       class="form-input h-9 w-full rounded-lg border border-slate-300 bg-white pl-9 pr-3 text-xs text-slate-700 placeholder:text-slate-400 focus:border-primary focus:ring-1 focus:ring-primary" />
                            </div>
                            <span class="text-xs text-slate-400 mt-1 block">Pastikan menyertakan awalan https:// atau http://</span>
                        </div>
                    @endif

                    {{-- Form: WhatsApp --}}
                    @if($contentType === 'whatsapp')
                        <div class="space-y-3.5">
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1.5">Nomor WhatsApp</label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                        <x-lucide-phone class="size-4" />
                                    </div>
                                    <input type="text" 
                                           wire:model.live.debounce.300ms="waPhone"
                                           placeholder="6281234567890"
                                           class="form-input h-9 w-full rounded-lg border border-slate-300 bg-white pl-9 pr-3 text-xs text-slate-700 placeholder:text-slate-400 focus:border-primary focus:ring-1 focus:ring-primary" />
                                </div>
                                <span class="text-xs text-slate-400 mt-1 block">Gunakan kode negara tanpa tanda tambah (contoh: 6281234567890)</span>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1.5">Pesan Awal (Opsional)</label>
                                <textarea wire:model.live.debounce.300ms="waMessage"
                                          rows="3"
                                          placeholder="Halo, saya ingin menanyakan..."
                                          class="form-textarea w-full rounded-lg border border-slate-300 bg-white p-3 text-xs text-slate-700 placeholder:text-slate-400 focus:border-primary focus:ring-1 focus:ring-primary"></textarea>
                            </div>
                        </div>
                    @endif

                    {{-- Form: Wi-Fi --}}
                    @if($contentType === 'wifi')
                        <div class="space-y-3.5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Nama Jaringan (SSID)</label>
                                    <input type="text" 
                                           wire:model.live.debounce.300ms="wifiSsid"
                                           placeholder="Nama WiFi Anda"
                                           class="form-input h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-700 placeholder:text-slate-400 focus:border-primary focus:ring-1 focus:ring-primary" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Tipe Enkripsi</label>
                                    <select wire:model.live="wifiEncryption"
                                            class="form-select h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-700 focus:border-primary focus:ring-1 focus:ring-primary">
                                        <option value="WPA">WPA / WPA2 (Umum)</option>
                                        <option value="WEP">WEP</option>
                                        <option value="nopass">Tanpa Password</option>
                                    </select>
                                </div>
                            </div>
                            @if($wifiEncryption !== 'nopass')
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Password Wi-Fi</label>
                                    <div class="relative">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                            <x-lucide-key class="size-4" />
                                        </div>
                                        <input type="text" 
                                               wire:model.live.debounce.300ms="wifiPassword"
                                               placeholder="Kata sandi WiFi"
                                               class="form-input h-9 w-full rounded-lg border border-slate-300 bg-white pl-9 pr-3 text-xs text-slate-700 placeholder:text-slate-400 focus:border-primary focus:ring-1 focus:ring-primary" />
                                    </div>
                                    <span class="text-xs text-slate-400 mt-1 block">Perangkat yang memindai akan langsung terhubung ke jaringan tanpa ketik sandi manual.</span>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Form: vCard --}}
                    @if($contentType === 'vcard')
                        <div class="space-y-3.5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Nama Lengkap</label>
                                    <input type="text" 
                                           wire:model.live.debounce.300ms="vcardName"
                                           placeholder="Budi Santoso"
                                           class="form-input h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-700 placeholder:text-slate-400 focus:border-primary focus:ring-1 focus:ring-primary" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Perusahaan / Organisasi</label>
                                    <input type="text" 
                                           wire:model.live.debounce.300ms="vcardOrg"
                                           placeholder="ToolBox Studio"
                                           class="form-input h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-700 placeholder:text-slate-400 focus:border-primary focus:ring-1 focus:ring-primary" />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Nomor Telepon</label>
                                    <input type="text" 
                                           wire:model.live.debounce.300ms="vcardPhone"
                                           placeholder="+6281234567890"
                                           class="form-input h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-700 placeholder:text-slate-400 focus:border-primary focus:ring-1 focus:ring-primary" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1.5">Alamat Email</label>
                                    <input type="email" 
                                           wire:model.live.debounce.300ms="vcardEmail"
                                           placeholder="budi@example.com"
                                           class="form-input h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-700 placeholder:text-slate-400 focus:border-primary focus:ring-1 focus:ring-primary" />
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Form: Text --}}
                    @if($contentType === 'text')
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1.5">Teks Pesan</label>
                            <textarea wire:model.live.debounce.300ms="text"
                                      rows="4"
                                      placeholder="Ketik teks atau catatan yang ingin disimpan dalam kode QR..."
                                      class="form-textarea w-full rounded-lg border border-slate-300 bg-white p-3 text-xs text-slate-700 placeholder:text-slate-400 focus:border-primary focus:ring-1 focus:ring-primary"></textarea>
                        </div>
                    @endif

                    {{-- Form: Email --}}
                    @if($contentType === 'email')
                        <div class="space-y-3.5">
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1.5">Email Penerima</label>
                                <input type="email" 
                                       wire:model.live.debounce.300ms="emailTo"
                                       placeholder="tujuan@example.com"
                                       class="form-input h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-700 placeholder:text-slate-400 focus:border-primary focus:ring-1 focus:ring-primary" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1.5">Subjek Email</label>
                                <input type="text" 
                                       wire:model.live.debounce.300ms="emailSubject"
                                       placeholder="Pertanyaan seputar..."
                                       class="form-input h-9 w-full rounded-lg border border-slate-300 bg-white px-3 text-xs text-slate-700 placeholder:text-slate-400 focus:border-primary focus:ring-1 focus:ring-primary" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1.5">Isi Pesan</label>
                                <textarea wire:model.live.debounce.300ms="emailBody"
                                          rows="3"
                                          placeholder="Tuliskan draf isi pesan..."
                                          class="form-textarea w-full rounded-lg border border-slate-300 bg-white p-3 text-xs text-slate-700 placeholder:text-slate-400 focus:border-primary focus:ring-1 focus:ring-primary"></textarea>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Step 3: Design & Export Options --}}
                <div class="card p-4 sm:p-5">
                    <div class="border-b border-slate-200 pb-3 mb-4">
                        <h3 class="text-sm font-semibold text-slate-800">3. Kustomisasi Desain & Format</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Pilih gaya modul, resolusi, format berkas, dan sematkan logo tengah</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        {{-- Dot Style --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-2">Bentuk Modul Titik</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" 
                                        wire:click="$set('dotStyle', 'round')"
                                        class="flex items-center justify-center space-x-2 p-2.5 rounded-lg border text-xs transition-all cursor-pointer {{ $dotStyle === 'round' ? 'border-primary bg-primary/5 text-primary font-medium' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                                    <span class="size-3.5 rounded-full bg-current shrink-0"></span>
                                    <span>Titik Bulat</span>
                                </button>
                                <button type="button" 
                                        wire:click="$set('dotStyle', 'square')"
                                        class="flex items-center justify-center space-x-2 p-2.5 rounded-lg border text-xs transition-all cursor-pointer {{ $dotStyle === 'square' ? 'border-primary bg-primary/5 text-primary font-medium' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                                    <span class="size-3.5 rounded-xs bg-current shrink-0"></span>
                                    <span>Kotak Klasik</span>
                                </button>
                            </div>
                        </div>

                        {{-- Output Format --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-2">Format Berkas</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" 
                                        wire:click="$set('format', 'png')"
                                        class="flex items-center justify-center space-x-1.5 p-2.5 rounded-lg border text-xs transition-all cursor-pointer {{ $format === 'png' ? 'border-primary bg-primary/5 text-primary font-medium' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                                    <x-lucide-image class="size-3.5" />
                                    <span>PNG (Raster)</span>
                                </button>
                                <button type="button" 
                                        wire:click="$set('format', 'svg')"
                                        class="flex items-center justify-center space-x-1.5 p-2.5 rounded-lg border text-xs transition-all cursor-pointer {{ $format === 'svg' ? 'border-primary bg-primary/5 text-primary font-medium' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                                    <x-lucide-code-2 class="size-3.5" />
                                    <span>SVG (Vektor)</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Resolution Size & Center Logo Row --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5 mt-4 pt-4 border-t border-slate-100">
                        {{-- Resolution / Size --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-2">Resolusi Hasil Unduhan</label>
                            <div class="grid grid-cols-3 gap-1.5">
                                <button type="button" 
                                        wire:click="$set('size', 'small')"
                                        class="py-2 text-center rounded-lg border text-xs transition-all cursor-pointer {{ $size === 'small' ? 'border-primary bg-primary/5 text-primary font-medium' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                                    <span class="block">Kecil</span>
                                    <span class="text-xs text-slate-400 block">~350 px</span>
                                </button>
                                <button type="button" 
                                        wire:click="$set('size', 'medium')"
                                        class="py-2 text-center rounded-lg border text-xs transition-all cursor-pointer {{ $size === 'medium' ? 'border-primary bg-primary/5 text-primary font-medium' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                                    <span class="block">Sedang</span>
                                    <span class="text-xs text-slate-400 block">~600 px</span>
                                </button>
                                <button type="button" 
                                        wire:click="$set('size', 'large')"
                                        class="py-2 text-center rounded-lg border text-xs transition-all cursor-pointer {{ $size === 'large' ? 'border-primary bg-primary/5 text-primary font-medium' : 'border-slate-200 hover:bg-slate-50 text-slate-700' }}">
                                    <span class="block">Besar</span>
                                    <span class="text-xs text-slate-400 block">~900 px</span>
                                </button>
                            </div>
                        </div>

                        {{-- Center Logo Upload --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-2">Sematkan Logo Tengah (Opsional)</label>
                            @if ($logo)
                                <div class="flex items-center justify-between p-2.5 rounded-lg border border-slate-200 bg-slate-50">
                                    <div class="flex items-center space-x-2.5">
                                        <div class="flex size-9 items-center justify-center rounded bg-white border border-slate-200 overflow-hidden">
                                            <img src="{{ $logo->temporaryUrl() }}" class="size-8 object-contain" alt="Logo preview" />
                                        </div>
                                        <div class="overflow-hidden">
                                            <span class="text-xs font-medium text-slate-700 block truncate max-w-[130px]">Logo Siap Disematkan</span>
                                            <span class="text-xs text-success block font-medium" title="Error Correction Level H (30%) menjamin QR tetap terbaca walau tertutup logo">Koreksi: Level H (Aman)</span>
                                        </div>
                                    </div>
                                    <button type="button" 
                                            wire:click="removeLogo"
                                            class="btn size-7 rounded-full p-0 text-error hover:bg-error/10 cursor-pointer"
                                            title="Hapus Logo">
                                        <x-lucide-trash-2 class="size-4" />
                                    </button>
                                </div>
                            @else
                                <div x-data="{ isUploading: false, progress: 0 }"
                                     x-on:livewire-upload-start="isUploading = true; progress = 0"
                                     x-on:livewire-upload-finish="isUploading = false"
                                     x-on:livewire-upload-error="isUploading = false"
                                     x-on:livewire-upload-progress="progress = $event.detail.progress">
                                    <label class="flex items-center justify-center space-x-2 p-2.5 rounded-lg border border-dashed border-slate-300 hover:border-primary hover:bg-slate-50 cursor-pointer transition-all text-xs text-slate-600">
                                        <x-lucide-upload-cloud class="size-4 text-primary" />
                                        <span>Unggah Logo (PNG/JPG)</span>
                                        <input type="file" wire:model="logo" accept="image/png,image/jpeg,image/webp" class="hidden" />
                                    </label>
                                    <div x-show="isUploading" class="mt-1.5 flex items-center space-x-2">
                                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-200">
                                            <div class="h-full bg-primary transition-all duration-150" :style="`width: ${progress}%`"></div>
                                        </div>
                                        <span class="text-xs text-slate-500 font-medium" x-text="`${progress}%`"></span>
                                    </div>
                                </div>
                            @endif
                            <span class="text-xs text-slate-400 mt-1 block">Tersedia pada format PNG. Koreksi kesalahan otomatis disetel ke Level H (30%) agar QR tetap terbaca dengan logo di tengah.</span>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div class="mt-6 pt-4 border-t border-slate-200 flex items-center justify-between">
                        <div class="text-xs text-slate-500">
                            Hasil render instan & siap diunduh
                        </div>
                        <button type="button" 
                                wire:click="generate"
                                wire:loading.attr="disabled"
                                class="btn h-9 rounded-full bg-primary font-medium text-white shadow-xs text-xs px-6 hover:bg-primary-focus cursor-pointer disabled:opacity-50 flex items-center space-x-2 transition-all">
                            <span wire:loading.remove wire:target="generate" class="flex items-center space-x-2">
                                <x-lucide-qr-code class="size-4" />
                                <span>Generate QR Code</span>
                            </span>
                            <span wire:loading wire:target="generate" class="flex items-center space-x-2">
                                <x-lucide-loader-2 class="size-4 animate-spin" />
                                <span>Memproses...</span>
                            </span>
                        </button>
                    </div>
                </div>

            @endif
        </div>

        {{-- Right: Sticky Live Preview & Result Area (5 Columns) --}}
        <div class="col-span-12 lg:col-span-5 space-y-4 sm:space-y-5 lg:space-y-6">
            <div class="lg:sticky lg:top-20 space-y-4 sm:space-y-5">
                
                {{-- Live Preview Card --}}
                <div class="card p-4 sm:p-5">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-3 mb-4">
                        <div class="flex items-center space-x-2">
                            <h3 class="text-sm font-semibold text-slate-800">Pratinjau QR Code</h3>
                        </div>
                        <div class="badge rounded-full bg-success/15 text-success px-2.5 py-0.5 text-xs font-medium">
                            Live Sync
                        </div>
                    </div>

                    {{-- QR Code Display Box --}}
                    <div class="relative flex items-center justify-center p-6 bg-slate-50 rounded-xl border border-slate-200 min-h-[280px]">
                        
                        {{-- Loading Overlay --}}
                        <div wire:loading wire:target="updated, setContentType, removeLogo, logo" 
                             class="absolute inset-0 bg-white/75 backdrop-blur-[1px] rounded-xl flex items-center justify-center z-10 transition-all">
                            <div class="flex flex-col items-center space-y-1.5">
                                <x-lucide-loader-2 class="size-6 text-primary animate-spin" />
                                <span class="text-xs font-medium text-slate-600">Sinkronisasi Pratinjau...</span>
                            </div>
                        </div>

                        {{-- Preview Image --}}
                        @if($previewUri)
                            <div class="relative bg-white p-3.5 rounded-xl shadow-xs border border-slate-100">
                                <img src="{{ $previewUri }}" 
                                     alt="Pratinjau Kode QR" 
                                     class="size-52 sm:size-60 object-contain mx-auto transition-all duration-150" />
                            </div>
                        @else
                            <div class="text-center py-10">
                                <x-lucide-qr-code class="size-12 text-slate-300 mx-auto mb-2" />
                                <span class="text-xs text-slate-400 block">Menunggu input...</span>
                            </div>
                        @endif
                    </div>

                    {{-- Active Config Summary Tags --}}
                    <div class="mt-3.5 flex flex-wrap items-center justify-center gap-1.5">
                        <span class="badge rounded-full bg-slate-150 text-slate-700 px-2.5 py-0.5 text-xs font-medium">
                            Tipe: {{ strtoupper($contentType) }}
                        </span>
                        <span class="badge rounded-full bg-slate-150 text-slate-700 px-2.5 py-0.5 text-xs font-medium">
                            Gaya: {{ $dotStyle === 'round' ? 'Bulat' : 'Kotak' }}
                        </span>
                        <span class="badge rounded-full bg-slate-150 text-slate-700 px-2.5 py-0.5 text-xs font-medium">
                            Format: {{ strtoupper($format) }}
                        </span>
                        @if($logo)
                            <span class="badge rounded-full bg-primary/10 text-primary px-2.5 py-0.5 text-xs font-medium">
                                + Logo Tengah
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Result / Download Card (Appears when Generated) --}}
                @if($resultPath)
                    <div class="card p-4 sm:p-5 border-2 border-primary/20 bg-primary/5">
                        <div class="flex items-center justify-between pb-3 border-b border-primary/10 mb-3.5">
                            <div class="flex items-center space-x-2">
                                <div class="flex size-6 items-center justify-center rounded-full bg-primary text-white">
                                    <x-lucide-check class="size-3.5" />
                                </div>
                                <h4 class="text-xs font-semibold text-slate-800">QR Code Siap Diunduh</h4>
                            </div>
                            <span class="badge rounded-full bg-primary/10 text-primary px-2.5 py-0.5 text-xs font-medium">
                                Resolusi Penuh
                            </span>
                        </div>

                        <div class="space-y-2 mb-4 text-xs text-slate-600">
                            <div class="flex justify-between py-1 border-b border-slate-200/50">
                                <span>Format Berkas:</span>
                                <span class="font-medium text-slate-800 uppercase">{{ $resultFormat }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-200/50">
                                <span>Ukuran File:</span>
                                <span class="font-medium text-slate-800">{{ round($resultSize / 1024, 1) }} KB</span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span>Nama Berkas:</span>
                                <span class="font-medium text-slate-800 truncate max-w-[180px]">{{ $resultFilename }}</span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <button type="button" 
                                    wire:click="download"
                                    class="btn h-9 w-full rounded-full bg-primary font-medium text-white shadow-xs text-xs px-5 hover:bg-primary-focus cursor-pointer flex items-center justify-center space-x-2 transition-all">
                                <x-lucide-download class="size-4" />
                                <span>Unduh Sekarang ({{ strtoupper($resultFormat) }})</span>
                            </button>

                            <button type="button" 
                                    wire:click="resetResult"
                                    class="btn h-9 w-full rounded-full border border-slate-300 font-medium text-slate-700 hover:bg-white text-xs px-4 cursor-pointer flex items-center justify-center space-x-1.5 transition-all">
                                <x-lucide-rotate-ccw class="size-3.5 text-slate-500" />
                                <span>Ubah Data & Buat Ulang</span>
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Information & Best Practice Box --}}
                <div class="card p-4 sm:p-5">
                    <h4 class="text-xs font-semibold text-slate-800 mb-2.5 flex items-center space-x-1.5">
                        <x-lucide-info class="size-4 text-primary" />
                        <span>Panduan & Rekomendasi</span>
                    </h4>
                    <ul class="space-y-2 text-xs text-slate-600 leading-relaxed">
                        <li class="flex items-start space-x-2">
                            <span class="size-1.5 rounded-full bg-primary mt-1.5 shrink-0"></span>
                            <span><strong>Vektor SVG:</strong> Pilihan terbaik untuk cetak spanduk, banner, atau kartu nama fisik karena tidak akan pecah pada resolusi berapa pun.</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="size-1.5 rounded-full bg-primary mt-1.5 shrink-0"></span>
                            <span><strong>Raster PNG:</strong> Format standar praktis yang siap langsung di-share ke WhatsApp, Instagram, web, atau dokumen digital.</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="size-1.5 rounded-full bg-primary mt-1.5 shrink-0"></span>
                            <span><strong>Proteksi Logo:</strong> Saat logo diunggah, toleransi kesalahan (Error Correction) otomatis ditingkatkan ke level 30% (Level H) sehingga barcode tetap terbaca sempurna.</span>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </div>
</div>
