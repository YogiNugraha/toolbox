<div>
    @section('title', 'Kelola Paket & Harga - ' . config('app.name'))
    @section('page_title', 'Kelola Paket')
    @section('page_breadcrumb', 'Kelola Paket')

    {{-- Top Action Toolbar --}}
    <div class="flex flex-col justify-between gap-4 py-4 sm:flex-row sm:items-center sm:py-5">
        <div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-navy-50 lg:text-2xl flex items-center gap-2.5">
                <span>Kelola Paket & Langganan</span>
            </h2>
            <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">
                Atur skema harga, durasi, diskon, dan batasan kuota fitur tiap paket langganan.
            </p>
        </div>
        <button wire:click="create" class="btn h-9 rounded-full bg-primary px-4 text-xs font-bold text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus shadow-sm flex items-center space-x-1.5">
            <x-lucide-plus class="size-4" />
            <span>Tambah Paket Baru</span>
        </button>
    </div>

    {{-- Global Pricing Settings (Tax & Service Fee) --}}
    <div class="card mb-6 p-4 sm:p-5">
        <div class="mb-4 border-b border-slate-150 pb-3 dark:border-navy-600 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold tracking-wide text-slate-700 dark:text-navy-100 uppercase flex items-center gap-2">
                    <x-lucide-receipt class="size-4 text-primary dark:text-accent-light" />
                    <span>Pengaturan Pajak & Biaya Layanan Gateway</span>
                </h3>
                <p class="mt-0.5 text-xs text-slate-400 dark:text-navy-300">Diterapkan secara otomatis pada checkout pembayaran langganan.</p>
            </div>
        </div>

        <form wire:submit="saveSettings">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5">
                {{-- Tax Settings --}}
                <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 dark:border-navy-600 dark:bg-navy-700/40">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <x-lucide-percent class="size-4 text-primary dark:text-accent-light" />
                            <span class="font-bold text-slate-700 dark:text-navy-100 text-xs">Pajak Pertambahan Nilai (PPN)</span>
                        </div>
                        <label class="inline-flex cursor-pointer items-center">
                            <input class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white" type="checkbox" wire:model="is_tax_enabled" />
                        </label>
                    </div>

                    @if($is_tax_enabled)
                    <div class="mt-3.5 border-t border-slate-200 pt-3.5 border-dashed dark:border-navy-600">
                        <label class="block">
                            <span class="font-semibold text-slate-600 dark:text-navy-200 text-xs mb-1 block">Persentase Pajak (%)</span>
                            <input wire:model="tax_percent" type="number" min="0" max="100" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-1.5 text-xs placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                        </label>
                        @error('tax_percent') <span class="text-[11px] text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    @endif
                </div>

                {{-- Service Fee Settings --}}
                <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 dark:border-navy-600 dark:bg-navy-700/40">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <x-lucide-credit-card class="size-4 text-primary dark:text-accent-light" />
                            <span class="font-bold text-slate-700 dark:text-navy-100 text-xs">Biaya Penanganan / Layanan Gateway</span>
                        </div>
                        <label class="inline-flex cursor-pointer items-center">
                            <input class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white" type="checkbox" wire:model="is_service_fee_enabled" />
                        </label>
                    </div>

                    @if($is_service_fee_enabled)
                    <div class="mt-3.5 space-y-3 border-t border-slate-200 pt-3.5 border-dashed dark:border-navy-600">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block">
                                    <span class="font-semibold text-slate-600 dark:text-navy-200 text-xs mb-1 block">Tipe Biaya</span>
                                    <select wire:model="service_fee_type" class="form-select w-full rounded-lg border border-slate-300 bg-transparent px-3 py-1.5 text-xs hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent">
                                        <option value="fixed">Nominal Tetap (Rp)</option>
                                        <option value="percent">Persentase (%)</option>
                                    </select>
                                </label>
                                @error('service_fee_type') <span class="text-[11px] text-error mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block">
                                    <span class="font-semibold text-slate-600 dark:text-navy-200 text-xs mb-1 block">Nilai Biaya</span>
                                    <input wire:model="service_fee_value" type="number" min="0" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-1.5 text-xs placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                                </label>
                                @error('service_fee_value') <span class="text-[11px] text-error mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="mt-4 flex justify-end">
                <button type="submit" class="btn h-8 rounded-full bg-primary px-4 text-xs font-bold text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus shadow-sm flex items-center space-x-1.5">
                    <span wire:loading.remove wire:target="saveSettings">Simpan Pengaturan Biaya</span>
                    <span wire:loading wire:target="saveSettings" class="flex items-center space-x-1">
                        <x-lucide-loader-2 class="size-3.5 animate-spin" />
                        <span>Menyimpan...</span>
                    </span>
                </button>
            </div>
        </form>
    </div>

    {{-- Plans Table --}}
    <div class="card">
        <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
            <table class="is-hoverable w-full text-left">
                <thead>
                    <tr>
                        <th class="rounded-tl-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs">
                            NAMA / SLUG
                        </th>
                        <th class="bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs">
                            HARGA
                        </th>
                        <th class="bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs">
                            DISKON
                        </th>
                        <th class="bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs">
                            DURASI
                        </th>
                        <th class="bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs text-center">
                            URUTAN
                        </th>
                        <th class="bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs text-center">
                            STATUS
                        </th>
                        <th class="rounded-tr-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs text-right">
                            AKSI
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-150 dark:divide-navy-500">
                    @forelse($plans as $plan)
                        @php
                            $isProMax = ($plan->slug === 'pro-max' || strtolower($plan->name) === 'pro max');
                        @endphp
                        <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500 hover:bg-slate-50/80 dark:hover:bg-navy-700/50 transition-colors">
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                <div class="flex items-center gap-2">
                                    @if($isProMax)
                                        <span class="badge rounded-full bg-linear-to-r from-amber-500 via-purple-600 to-indigo-600 text-white font-black text-[10px] px-2.5 py-0.5 shadow-xs uppercase tracking-wider inline-flex items-center gap-1">
                                            <x-lucide-crown class="size-3 stroke-[2.5]" />
                                            <span>{{ $plan->name }}</span>
                                        </span>
                                    @elseif(!$plan->is_default)
                                        <span class="badge rounded-full bg-linear-to-r from-amber-500 to-orange-500 text-white font-black text-[10px] px-2.5 py-0.5 shadow-xs uppercase tracking-wider inline-flex items-center gap-1">
                                            <x-lucide-star class="size-3 stroke-[2.5] fill-current" />
                                            <span>{{ $plan->name }}</span>
                                        </span>
                                    @else
                                        <span class="font-bold text-slate-700 dark:text-navy-100 text-xs sm:text-sm">{{ $plan->name }}</span>
                                    @endif

                                    @if($plan->is_default)
                                        <span class="badge rounded-full bg-success/10 text-success text-[10px] font-bold px-2 py-0.5">
                                            Default Free
                                        </span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-slate-400 dark:text-navy-300 font-mono mt-0.5">{{ $plan->slug }}</div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                @php
                                    $breakdown = app(\App\Services\PriceCalculator::class)->breakdown($plan);
                                @endphp
                                @if($breakdown['discount'] > 0)
                                    <div class="text-[11px] line-through text-slate-400 dark:text-navy-300">
                                        Rp {{ number_format($breakdown['basePrice'], 0, ',', '.') }}
                                    </div>
                                @endif
                                <span class="font-bold text-slate-700 dark:text-navy-100 text-xs sm:text-sm">
                                    Rp {{ number_format($breakdown['subtotal'], 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5 text-xs font-bold text-success">
                                @if($plan->discount_type === 'percent')
                                    <span class="badge rounded-full bg-success/10 text-success text-xs px-2 py-0.5 font-bold">
                                        {{ $plan->discount_value }}% OFF
                                    </span>
                                @elseif($plan->discount_type === 'fixed')
                                    <span class="badge rounded-full bg-success/10 text-success text-xs px-2 py-0.5 font-bold">
                                        -Rp {{ number_format($plan->discount_value, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-slate-400 dark:text-navy-300 font-normal">-</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5 text-xs text-slate-600 dark:text-navy-200 font-medium">
                                {{ $plan->duration_days ? $plan->duration_days . ' Hari' : 'Selamanya' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5 text-xs text-slate-600 dark:text-navy-200 text-center font-bold">
                                {{ $plan->sort_order }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5 text-center">
                                <label class="inline-flex cursor-pointer items-center">
                                    <input class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white" type="checkbox" wire:click="toggleActive({{ $plan->id }})" {{ $plan->is_active ? 'checked' : '' }} />
                                </label>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5 text-right space-x-1.5">
                                <button wire:click="edit({{ $plan->id }})" class="btn size-7 rounded-md bg-primary/10 text-primary hover:bg-primary/20 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 p-0 text-xs font-semibold shadow-xs" title="Edit Paket">
                                    <x-lucide-pencil class="size-3.5" />
                                </button>
                                @if(!$plan->is_default)
                                <button wire:click="confirmDelete({{ $plan->id }})" class="btn size-7 rounded-md bg-error/10 text-error hover:bg-error/20 p-0 text-xs font-semibold shadow-xs" title="Hapus Paket">
                                    <x-lucide-trash-2 class="size-3.5" />
                                </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="whitespace-nowrap px-4 py-8 text-center text-slate-400 dark:text-navy-300 text-xs">Belum ada paket yang terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Form (Lineone Modal Card) --}}
    @if($isModalOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-300"
         x-data
         x-on:keydown.escape.window="$wire.set('isModalOpen', false)">
        <div class="card w-full max-w-3xl max-h-[90vh] flex flex-col overflow-hidden rounded-2xl bg-white dark:bg-navy-700 shadow-2xl border border-slate-200 dark:border-navy-600">
            
            {{-- Modal Header --}}
            <div class="flex items-center justify-between border-b border-slate-150 px-5 py-4 dark:border-navy-600 bg-slate-50 dark:bg-navy-800">
                <div class="flex items-center space-x-3">
                    <div class="mask is-squircle flex size-10 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light font-bold">
                        <x-lucide-tag class="size-5" />
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800 dark:text-navy-100">
                            {{ $planId ? 'Edit Paket Langganan' : 'Tambah Paket Baru' }}
                        </h3>
                        <p class="text-xs text-slate-400 dark:text-navy-300">
                            {{ $planId ? 'Perbarui harga, diskon, durasi, dan batasan kuota fitur.' : 'Daftarkan paket langganan baru untuk pengguna.' }}
                        </p>
                    </div>
                </div>
                <button wire:click="$set('isModalOpen', false)" class="btn size-8 rounded-full p-0 text-slate-400 hover:bg-slate-200 hover:text-slate-700 dark:hover:bg-navy-600 dark:hover:text-navy-100">
                    <x-lucide-x class="size-4.5" />
                </button>
            </div>
            
            {{-- Modal Body --}}
            <div class="is-scrollbar-hidden overflow-y-auto px-5 py-5 space-y-5 text-xs">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5">
                    <div>
                        <label class="block">
                            <span class="font-semibold text-slate-700 dark:text-navy-100 mb-1 block">Nama Paket <span class="text-error">*</span></span>
                            <input wire:model.live="name" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="text" placeholder="Contoh: Pro Max Bulanan" />
                        </label>
                        @error('name') <span class="text-[11px] text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block">
                            <span class="font-semibold text-slate-700 dark:text-navy-100 mb-1 block">Slug (Identifier) <span class="text-error">*</span></span>
                            <input wire:model="slug" {{ $planId ? 'readonly' : '' }} class="form-input w-full rounded-lg border border-slate-300 {{ $planId ? 'bg-slate-100 text-slate-500 dark:bg-navy-600 dark:text-navy-300' : 'bg-transparent text-slate-700 dark:text-navy-100' }} px-3 py-2 font-mono text-primary dark:text-accent-light placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="text" placeholder="pro-max" />
                        </label>
                        @error('slug') <span class="text-[11px] text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block">
                            <span class="font-semibold text-slate-700 dark:text-navy-100 mb-1 block">Harga Normal (Rp) <span class="text-error">*</span></span>
                            <input wire:model="price" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="number" />
                        </label>
                        @error('price') <span class="text-[11px] text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block">
                            <span class="font-semibold text-slate-700 dark:text-navy-100 mb-1 block">Durasi (Hari)</span>
                            <input wire:model="duration_days" placeholder="Kosongkan jika selamanya" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="number" />
                        </label>
                        @error('duration_days') <span class="text-[11px] text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5">
                    <div>
                        <label class="block">
                            <span class="font-semibold text-slate-700 dark:text-navy-100 mb-1 block">Jenis Diskon</span>
                            <select wire:model.live="discount_type" class="form-select w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent">
                                <option value="none">Tidak Ada Diskon</option>
                                <option value="percent">Persentase (%)</option>
                                <option value="fixed">Nominal Tetap (Rp)</option>
                            </select>
                        </label>
                        @error('discount_type') <span class="text-[11px] text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    @if($discount_type !== 'none')
                    <div>
                        <label class="block">
                            <span class="font-semibold text-slate-700 dark:text-navy-100 mb-1 block">Nilai Diskon</span>
                            <input wire:model="discount_value" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="number" />
                        </label>
                        @error('discount_value') <span class="text-[11px] text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    @endif
                </div>

                <div>
                    <label class="block">
                        <span class="font-semibold text-slate-700 dark:text-navy-100 mb-1 block">Deskripsi Singkat</span>
                        <textarea wire:model="description" rows="2" class="form-textarea w-full rounded-lg border border-slate-300 bg-transparent p-2.5 placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" placeholder="Contoh: Pilihan ideal untuk kebutuhan kerja harian tanpa batas."></textarea>
                    </label>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 sm:gap-5">
                    <div>
                        <label class="block">
                            <span class="font-semibold text-slate-700 dark:text-navy-100 mb-1 block">Urutan Tampil</span>
                            <input wire:model="sort_order" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" type="number" />
                        </label>
                    </div>
                    <div class="flex items-center sm:pt-5">
                        <label class="inline-flex items-center space-x-2 cursor-pointer">
                            <input wire:model="is_default" class="form-checkbox is-outline size-4.5 rounded border-slate-400/70 before:bg-primary checked:border-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:before:bg-accent dark:checked:border-accent" type="checkbox" />
                            <span class="font-semibold text-slate-700 dark:text-navy-100">Jadikan Default (Free)</span>
                        </label>
                    </div>
                    <div class="flex items-center sm:pt-5">
                        <label class="inline-flex items-center space-x-2 cursor-pointer">
                            <input wire:model="is_active" class="form-checkbox is-outline size-4.5 rounded border-slate-400/70 before:bg-primary checked:border-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:before:bg-accent dark:checked:border-accent" type="checkbox" />
                            <span class="font-semibold text-slate-700 dark:text-navy-100">Paket Aktif Dijual</span>
                        </label>
                    </div>
                </div>

                <div class="my-4 h-px bg-slate-150 dark:bg-navy-600"></div>
                
                {{-- Features List Form --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-bold text-slate-700 dark:text-navy-100 uppercase">Fitur Tambahan (Checklist)</h4>
                        <button wire:click.prevent="addFeature" type="button" class="btn h-7 rounded-full bg-primary/10 px-3 text-[11px] font-bold text-primary hover:bg-primary/20 dark:bg-accent-light/10 dark:text-accent-light flex items-center space-x-1">
                            <x-lucide-plus class="size-3" />
                            <span>Tambah Fitur</span>
                        </button>
                    </div>
                    
                    <div class="space-y-2.5">
                        @forelse($features as $index => $feature)
                            <div class="flex items-center space-x-2">
                                <div class="flex size-5 shrink-0 items-center justify-center rounded-full bg-success/10 text-success">
                                    <x-lucide-check class="size-3.5 stroke-[2.5]" />
                                </div>
                                <input type="text" wire:model="features.{{ $index }}" placeholder="Contoh: Akses tanpa batas kuota harian" class="form-input w-full flex-1 rounded-lg border border-slate-300 bg-transparent px-3 py-1.5 placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent">
                                <button wire:click.prevent="removeFeature({{ $index }})" type="button" class="btn size-7 rounded-full p-0 text-error hover:bg-error/10">
                                    <x-lucide-trash-2 class="size-3.5" />
                                </button>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-4 text-center dark:border-navy-600 dark:bg-navy-700/40">
                                <p class="text-slate-400 dark:text-navy-300 text-xs">Belum ada fitur checklist. Klik tombol "+ Tambah Fitur" di atas.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="my-4 h-px bg-slate-150 dark:bg-navy-600"></div>
                
                {{-- Tool Limits Configuration --}}
                <div>
                    <h4 class="font-bold text-slate-700 dark:text-navy-100 uppercase mb-3">Batasan Kuota Tools</h4>

                    <div class="space-y-3">
                        @foreach($toolsConfig as $tool)
                            <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-3.5 dark:border-navy-600 dark:bg-navy-700/40">
                                <div class="flex items-center space-x-2 font-bold text-primary dark:text-accent-light mb-2.5">
                                    <span class="size-2 rounded-full bg-primary dark:bg-accent"></span>
                                    <span>{{ $tool['name'] }}</span>
                                    <span class="badge rounded-full bg-slate-100 text-slate-600 dark:bg-navy-600 dark:text-navy-200 text-[9px] px-1.5 py-0.5">{{ $tool['category'] ?? 'General' }}</span>
                                </div>
                                
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="block">
                                            <span class="font-semibold text-slate-600 dark:text-navy-200 text-xs mb-1 block">Kuota Harian <span class="font-normal text-slate-400">(Kosong = Unlimited)</span></span>
                                            <input type="number" wire:model="limits.{{ $tool['slug'] }}.daily_quota" placeholder="Unlimited" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-1.5 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent">
                                        </label>
                                    </div>
                                    
                                    <div>
                                        <label class="block">
                                            <span class="font-semibold text-slate-600 dark:text-navy-200 text-xs mb-1 block">Max Ukuran File (MB) <span class="font-normal text-slate-400">(Kosong = Unlimited)</span></span>
                                            <input type="number" wire:model="limits.{{ $tool['slug'] }}.max_file_size_mb" placeholder="Contoh: 50" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-1.5 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent">
                                        </label>
                                    </div>
                                </div>

                                @if(!empty($tool['lockable_features']))
                                <div class="mt-2.5 pt-2 border-t border-slate-200 dark:border-navy-600 space-y-2">
                                    @foreach($tool['lockable_features'] as $featureKey => $featureLabel)
                                    <label class="inline-flex items-center space-x-2 cursor-pointer">
                                        <input type="checkbox" wire:model="limits.{{ $tool['slug'] }}.locked_features" value="{{ $featureKey }}" class="form-checkbox is-outline size-4.5 rounded border-slate-400/70 before:bg-primary checked:border-primary hover:border-primary focus:border-primary dark:border-navy-400 dark:before:bg-accent dark:checked:border-accent" />
                                        <span class="font-semibold text-slate-700 dark:text-navy-100 text-xs">{{ $featureLabel }}</span>
                                    </label>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
            
            {{-- Modal Footer --}}
            <div class="flex items-center justify-end space-x-2 border-t border-slate-150 bg-slate-50 px-5 py-3.5 dark:border-navy-600 dark:bg-navy-800">
                <button wire:click="$set('isModalOpen', false)" class="btn rounded-full border border-slate-300 font-semibold text-slate-700 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-100 dark:hover:bg-navy-500 text-xs px-4 py-2">
                    Batal
                </button>
                <button wire:click="save" class="btn rounded-full bg-primary font-bold text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus text-xs px-5 py-2 shadow-sm flex items-center space-x-1.5">
                    <span wire:loading.remove wire:target="save">Simpan Paket</span>
                    <span wire:loading wire:target="save" class="flex items-center space-x-1">
                        <x-lucide-loader-2 class="size-3.5 animate-spin" />
                        <span>Menyimpan...</span>
                    </span>
                </button>
            </div>
            
        </div>
    </div>
    @endif
</div>
