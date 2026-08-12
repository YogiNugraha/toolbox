<div>
    <div class="flex items-center justify-between mt-5 mb-5">
        <h2 class="text-xl font-medium text-slate-800 dark:text-navy-50 lg:text-2xl">Kelola Paket</h2>
        <button wire:click="create" class="btn bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Paket
        </button>
    </div>

    {{-- Global Pricing Settings --}}
    <div class="card mb-6 p-4 sm:p-5">
        <div class="mb-5 border-b border-slate-200 pb-5 dark:border-navy-500">
            <h2 class="text-lg font-medium tracking-wide text-slate-700 dark:text-navy-100">Pengaturan Pajak & Biaya Layanan</h2>
            <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">Diterapkan secara global untuk semua pembayaran paket.</p>
        </div>

        <form wire:submit="saveSettings">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                {{-- Tax Settings --}}
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-navy-500 dark:bg-navy-700">
                    <div class="flex items-center justify-between mb-4">
                        <span class="font-medium text-slate-700 dark:text-navy-100">Aktifkan Pajak (PPN)</span>
                        <label class="inline-flex cursor-pointer items-center space-x-2">
                            <input class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white" type="checkbox" wire:model="is_tax_enabled" />
                        </label>
                    </div>

                    @if($is_tax_enabled)
                    <div class="mt-4 border-t border-slate-200 pt-4 border-dashed dark:border-navy-500">
                        <label class="block">
                            <span class="font-medium text-slate-700 dark:text-navy-100 mb-1.5 block">Pajak PPN (%)</span>
                            <input wire:model="tax_percent" type="number" min="0" max="100" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                        </label>
                        @error('tax_percent') <span class="text-tiny-plus text-error mt-1">{{ $message }}</span> @enderror
                    </div>
                    @endif
                </div>

                {{-- Service Fee Settings --}}
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-navy-500 dark:bg-navy-700">
                    <div class="flex items-center justify-between mb-4">
                        <span class="font-medium text-slate-700 dark:text-navy-100">Aktifkan Biaya Layanan</span>
                        <label class="inline-flex cursor-pointer items-center space-x-2">
                            <input class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white" type="checkbox" wire:model="is_service_fee_enabled" />
                        </label>
                    </div>

                    @if($is_service_fee_enabled)
                    <div class="mt-4 space-y-4 border-t border-slate-200 pt-4 border-dashed dark:border-navy-500">
                        <div>
                            <label class="block">
                                <span class="font-medium text-slate-700 dark:text-navy-100 mb-1.5 block">Tipe Biaya Layanan</span>
                                <select wire:model="service_fee_type" class="form-select w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent">
                                    <option value="fixed">Nominal Tetap (Rp)</option>
                                    <option value="percent">Persentase (%)</option>
                                </select>
                            </label>
                            @error('service_fee_type') <span class="text-tiny-plus text-error mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block">
                                <span class="font-medium text-slate-700 dark:text-navy-100 mb-1.5 block">Nilai Biaya Layanan</span>
                                <input wire:model="service_fee_value" type="number" min="0" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                            </label>
                            @error('service_fee_value') <span class="text-tiny-plus text-error mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="btn bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                    <span wire:loading.remove wire:target="saveSettings">Simpan Pengaturan Biaya</span>
                    <span wire:loading wire:target="saveSettings">Menyimpan...</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Plans Table --}}
    <div class="card">
        <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
            <table class="is-hoverable w-full text-left">
                <thead>
                    <tr class="border-y border-transparent border-b-slate-200 bg-slate-50 dark:border-b-navy-500 dark:bg-navy-800">
                        <th class="whitespace-nowrap px-4 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Nama / Slug</th>
                        <th class="whitespace-nowrap px-4 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Harga</th>
                        <th class="whitespace-nowrap px-4 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Diskon</th>
                        <th class="whitespace-nowrap px-4 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Durasi</th>
                        <th class="whitespace-nowrap px-4 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Urutan</th>
                        <th class="whitespace-nowrap px-4 py-3 font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Status</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right font-semibold uppercase text-slate-800 dark:text-navy-100 lg:px-5">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $plan)
                        <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500">
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                <div class="font-medium text-slate-700 dark:text-navy-100">{{ $plan->name }}</div>
                                <div class="text-xs text-slate-400 dark:text-navy-300 mt-0.5">{{ $plan->slug }}</div>
                                @if($plan->is_default)
                                    <span class="badge mt-1 bg-success/10 text-success dark:bg-success/15">Default (Free)</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                @php
                                    $breakdown = app(\App\Services\PriceCalculator::class)->breakdown($plan);
                                @endphp
                                @if($breakdown['discount'] > 0)
                                    <div class="text-xs line-through text-slate-400 dark:text-navy-300">Rp {{ number_format($breakdown['basePrice'], 0, ',', '.') }}</div>
                                @endif
                                <span class="font-medium text-slate-700 dark:text-navy-100">Rp {{ number_format($breakdown['subtotal'], 0, ',', '.') }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5 text-sm font-medium text-success">
                                @if($plan->discount_type === 'percent')
                                    {{ $plan->discount_value }}%
                                @elseif($plan->discount_type === 'fixed')
                                    Rp {{ number_format($plan->discount_value, 0, ',', '.') }}
                                @else
                                    <span class="text-slate-400 dark:text-navy-300">-</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5 text-slate-600 dark:text-navy-100">
                                {{ $plan->duration_days ? $plan->duration_days . ' hari' : 'Selamanya' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5 text-slate-600 dark:text-navy-100">
                                {{ $plan->sort_order }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                <label class="inline-flex cursor-pointer items-center space-x-2">
                                    <input class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white" type="checkbox" wire:click="toggleActive({{ $plan->id }})" {{ $plan->is_active ? 'checked' : '' }} />
                                </label>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5 text-right space-x-2">
                                <button wire:click="edit({{ $plan->id }})" class="btn size-8 p-0 text-info hover:bg-info/20 focus:bg-info/20 active:bg-info/25">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <button wire:click="confirmDelete({{ $plan->id }})" class="btn size-8 p-0 text-error hover:bg-error/20 focus:bg-error/20 active:bg-error/25">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="whitespace-nowrap px-4 py-8 text-center text-slate-400 dark:text-navy-300">Belum ada paket.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Form --}}
    @if($isModalOpen)
    <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
        <div class="card w-full max-w-3xl max-h-[90vh] flex flex-col overflow-hidden rounded-lg bg-white dark:bg-navy-700 shadow-xl">
            
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-4 dark:border-navy-500 sm:px-5">
                <h3 class="text-lg font-medium text-slate-700 dark:text-navy-100">{{ $planId ? 'Edit Paket' : 'Tambah Paket Baru' }}</h3>
                <button wire:click="$set('isModalOpen', false)" class="btn size-8 p-0 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 dark:hover:bg-navy-300/20 dark:focus:bg-navy-300/20 dark:active:bg-navy-300/25">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div class="is-scrollbar-hidden overflow-y-auto px-4 py-6 sm:px-5">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5 mb-6">
                    <div>
                        <label class="block">
                            <span class="font-medium text-slate-700 dark:text-navy-100 mb-1.5 block">Nama Paket</span>
                            <input wire:model="name" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="text" />
                        </label>
                        @error('name') <span class="text-tiny-plus text-error mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block">
                            <span class="font-medium text-slate-700 dark:text-navy-100 mb-1.5 block">Slug (Identifier)</span>
                            <input wire:model="slug" {{ $planId ? 'readonly' : '' }} class="form-input w-full rounded-lg border border-slate-300 {{ $planId ? 'bg-slate-100 text-slate-500 dark:bg-navy-600 dark:text-navy-300' : 'bg-transparent text-slate-700 dark:text-navy-100' }} px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="text" />
                        </label>
                        @error('slug') <span class="text-tiny-plus text-error mt-1">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block">
                            <span class="font-medium text-slate-700 dark:text-navy-100 mb-1.5 block">Harga (Rp)</span>
                            <input wire:model="price" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="number" />
                        </label>
                        @error('price') <span class="text-tiny-plus text-error mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block">
                            <span class="font-medium text-slate-700 dark:text-navy-100 mb-1.5 block">Durasi (Hari)</span>
                            <input wire:model="duration_days" placeholder="Kosong = Selamanya" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="number" />
                        </label>
                        @error('duration_days') <span class="text-tiny-plus text-error mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5 mb-6">
                    <div>
                        <label class="block">
                            <span class="font-medium text-slate-700 dark:text-navy-100 mb-1.5 block">Jenis Diskon</span>
                            <select wire:model.live="discount_type" class="form-select w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent">
                                <option value="none">Tidak Ada</option>
                                <option value="percent">Persentase (%)</option>
                                <option value="fixed">Nominal Tetap (Rp)</option>
                            </select>
                        </label>
                        @error('discount_type') <span class="text-tiny-plus text-error mt-1">{{ $message }}</span> @enderror
                    </div>
                    @if($discount_type !== 'none')
                    <div>
                        <label class="block">
                            <span class="font-medium text-slate-700 dark:text-navy-100 mb-1.5 block">Nilai Diskon</span>
                            <input wire:model="discount_value" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="number" />
                        </label>
                        @error('discount_value') <span class="text-tiny-plus text-error mt-1">{{ $message }}</span> @enderror
                    </div>
                    @endif
                </div>

                <div class="mb-6">
                    <label class="block">
                        <span class="font-medium text-slate-700 dark:text-navy-100 mb-1.5 block">Deskripsi Singkat</span>
                        <textarea wire:model="description" rows="2" class="form-textarea w-full rounded-lg border border-slate-300 bg-transparent p-2.5 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"></textarea>
                    </label>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 sm:gap-5 mb-8">
                    <div>
                        <label class="block">
                            <span class="font-medium text-slate-700 dark:text-navy-100 mb-1.5 block">Urutan Tampil</span>
                            <input wire:model="sort_order" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="number" />
                        </label>
                    </div>
                    <div class="flex items-center sm:mt-8">
                        <label class="inline-flex items-center space-x-2">
                            <input wire:model="is_default" class="form-checkbox is-outline size-5 rounded border-slate-400/70 before:bg-primary checked:border-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:before:bg-accent dark:checked:border-accent dark:hover:border-accent dark:focus:border-accent" type="checkbox" />
                            <span class="font-medium text-slate-700 dark:text-navy-100">Jadikan Default (Free)</span>
                        </label>
                    </div>
                    <div class="flex items-center sm:mt-8">
                        <label class="inline-flex items-center space-x-2">
                            <input wire:model="is_active" class="form-checkbox is-outline size-5 rounded border-slate-400/70 before:bg-primary checked:border-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:before:bg-accent dark:checked:border-accent dark:hover:border-accent dark:focus:border-accent" type="checkbox" />
                            <span class="font-medium text-slate-700 dark:text-navy-100">Paket Aktif</span>
                        </label>
                    </div>
                </div>

                <div class="my-6 h-px bg-slate-200 dark:bg-navy-500"></div>
                
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-slate-700 dark:text-navy-100">Fitur Tambahan</h3>
                    <button wire:click.prevent="addFeature" type="button" class="btn h-8 rounded-lg bg-primary/10 px-3 text-xs font-medium text-primary hover:bg-primary/20 focus:bg-primary/20 active:bg-primary/25 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 dark:focus:bg-accent-light/20 dark:active:bg-accent-light/25">
                        + Tambah Fitur
                    </button>
                </div>
                
                <div class="space-y-3 mb-8">
                    @forelse($features as $index => $feature)
                        <div class="flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <input type="text" wire:model="features.{{ $index }}" placeholder="Contoh: Priority Support" class="form-input w-full flex-1 rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400/70 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent">
                            <button wire:click.prevent="removeFeature({{ $index }})" type="button" class="btn size-8 p-0 text-error hover:bg-error/20 focus:bg-error/20 active:bg-error/25">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @empty
                        <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-4 text-center dark:border-navy-450 dark:bg-navy-700">
                            <p class="text-sm text-slate-400 dark:text-navy-300">Belum ada fitur tambahan. Klik tombol di atas untuk menambahkan.</p>
                        </div>
                    @endforelse
                </div>

                <div class="my-6 h-px bg-slate-200 dark:bg-navy-500"></div>
                
                <h3 class="text-lg font-medium text-slate-700 dark:text-navy-100 mb-4">Batasan Tools (Limits)</h3>

                @foreach($toolsConfig as $tool)
                    <div class="mb-6 rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-navy-500 dark:bg-navy-700/50">
                        <h4 class="font-medium text-primary dark:text-accent-light mb-4">{{ $tool['name'] }}</h4>
                        
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block">
                                    <span class="font-medium text-slate-700 dark:text-navy-100 mb-1.5 block">Kuota Harian <span class="font-normal text-slate-400">(Kosong = Unlimited)</span></span>
                                    <input type="number" wire:model="limits.{{ $tool['slug'] }}.daily_quota" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent">
                                </label>
                            </div>
                            
                            @if($tool['slug'] === 'pdf-to-word')
                            <div>
                                <label class="block">
                                    <span class="font-medium text-slate-700 dark:text-navy-100 mb-1.5 block">Max Ukuran File (MB)</span>
                                    <input type="number" wire:model="limits.{{ $tool['slug'] }}.max_file_size_mb" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent">
                                </label>
                            </div>
                            @endif
                        </div>

                        @if($tool['slug'] === 'compress-image')
                        <div class="mt-4">
                            <span class="font-medium text-slate-700 dark:text-navy-100 mb-1.5 block">Fitur Terkunci (Check untuk mengunci)</span>
                            <label class="inline-flex items-center space-x-2">
                                <input type="checkbox" wire:model="limits.{{ $tool['slug'] }}.locked_features" value="preset_custom" class="form-checkbox is-outline size-5 rounded border-slate-400/70 before:bg-primary checked:border-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:before:bg-accent dark:checked:border-accent dark:hover:border-accent dark:focus:border-accent" />
                                <span>Preset Custom</span>
                            </label>
                        </div>
                        @endif
                    </div>
                @endforeach

            </div>
            
            <div class="flex items-center justify-end space-x-3 border-t border-slate-200 bg-slate-50 px-4 py-4 dark:border-navy-500 dark:bg-navy-800 sm:px-5">
                <button wire:click="$set('isModalOpen', false)" class="btn border border-slate-300 font-medium text-slate-800 hover:bg-slate-150 focus:bg-slate-150 active:bg-slate-150/80 dark:border-navy-450 dark:text-navy-50 dark:hover:bg-navy-500 dark:focus:bg-navy-500 dark:active:bg-navy-500/90">Batal</button>
                <button wire:click="save" class="btn bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">Simpan Paket</button>
            </div>
            
        </div>
    </div>
    @endif
</div>
