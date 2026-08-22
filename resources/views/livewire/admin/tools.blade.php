<div>
    @section('title', 'Kelola Tools & Layanan - ' . config('app.name'))
    @section('page_title', 'Kelola Tools')
    @section('page_breadcrumb', 'Kelola Tools')

    {{-- Top Action Toolbar --}}
    <div class="flex flex-col justify-between gap-4 py-4 sm:flex-row sm:items-center sm:py-5">
        <div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-navy-50 lg:text-2xl flex items-center gap-2.5">
                <span>Kelola Tools & Layanan</span>
                <span class="badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light text-xs font-bold px-2.5 py-0.5">
                    {{ $totalTools }} Tools
                </span>
            </h2>
            <p class="mt-1 text-xs text-slate-400 dark:text-navy-300">
                Atur status aktif, mode pemeliharaan, label promosi, gambar thumbnail, dan urutan seluruh tools platform.
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <button wire:click="syncFromConfigAction" title="Sinkronkan tool bawaan dari konfigurasi" class="btn h-9 rounded-full border border-slate-300 px-3.5 text-xs font-semibold text-slate-700 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-100 dark:hover:bg-navy-500 shadow-xs flex items-center space-x-1.5">
                <x-lucide-refresh-cw class="size-3.5" />
                <span>Sync Config</span>
            </button>
            <button wire:click="create" class="btn h-9 rounded-full bg-primary px-4 text-xs font-bold text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus shadow-sm flex items-center space-x-1.5">
                <x-lucide-plus class="size-4" />
                <span>Tambah Tool Baru</span>
            </button>
        </div>
    </div>

    {{-- 4 Stat Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 sm:gap-5 mb-6">
        {{-- Total Tools --}}
        <div class="card p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-navy-300">Total Tools</span>
                <div class="mask is-squircle flex size-10 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light font-bold">
                    <x-lucide-wrench class="size-5" />
                </div>
            </div>
            <div class="mt-3">
                <p class="text-2xl font-bold text-slate-800 dark:text-navy-100">{{ $totalTools }}</p>
                <p class="text-[11px] text-slate-400 dark:text-navy-300 mt-0.5">Seluruh tools terdaftar di sistem</p>
            </div>
        </div>

        {{-- Active Tools --}}
        <div class="card p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-navy-300">Tools Aktif</span>
                <div class="mask is-squircle flex size-10 items-center justify-center bg-success/10 text-success font-bold">
                    <x-lucide-check-circle-2 class="size-5" />
                </div>
            </div>
            <div class="mt-3">
                <p class="text-2xl font-bold text-success">{{ $activeToolsCount }}</p>
                <p class="text-[11px] text-slate-400 dark:text-navy-300 mt-0.5">Dapat digunakan pengguna</p>
            </div>
        </div>

        {{-- Maintenance Tools --}}
        <div class="card p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-navy-300">Mode Maintenance</span>
                <div class="mask is-squircle flex size-10 items-center justify-center bg-warning/10 text-warning font-bold">
                    <x-lucide-hammer class="size-5" />
                </div>
            </div>
            <div class="mt-3">
                <p class="text-2xl font-bold text-warning">{{ $maintenanceToolsCount }}</p>
                <p class="text-[11px] text-slate-400 dark:text-navy-300 mt-0.5">Sedang dalam perbaikan</p>
            </div>
        </div>

        {{-- Total Processed Files --}}
        <div class="card p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-navy-300">Total Penggunaan</span>
                <div class="mask is-squircle flex size-10 items-center justify-center bg-info/10 text-info font-bold">
                    <x-lucide-sparkles class="size-5" />
                </div>
            </div>
            <div class="mt-3">
                <p class="text-2xl font-bold text-slate-800 dark:text-navy-100">{{ number_format($totalProcessed) }}</p>
                <p class="text-[11px] text-slate-400 dark:text-navy-300 mt-0.5">File & dokumen diproses</p>
            </div>
        </div>
    </div>

    {{-- Main Tools Card --}}
    <div class="card">
        {{-- Filters Bar --}}
        <div class="flex flex-col justify-between gap-3 p-4 sm:flex-row sm:items-center sm:px-5 border-b border-slate-150 dark:border-navy-600">
            {{-- Search Bar --}}
            <div class="relative flex w-full sm:w-80">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama tool, slug, kategori..."
                    class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-1.5 pl-9 text-xs placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                <span class="pointer-events-none absolute flex h-full w-9 items-center justify-center text-slate-400 dark:text-navy-300">
                    <x-lucide-search class="size-4" />
                </span>
            </div>

            {{-- Multi Filters --}}
            <div class="flex flex-wrap items-center gap-2">
                {{-- Category Filter --}}
                <select wire:model.live="selectedCategory" class="form-select rounded-lg border border-slate-300 bg-transparent px-3 py-1.5 text-xs hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>

                {{-- Status Filter --}}
                <select wire:model.live="statusFilter" class="form-select rounded-lg border border-slate-300 bg-transparent px-3 py-1.5 text-xs hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif & Normal</option>
                    <option value="highlighted">🌟 Highlight di Beranda</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="inactive">Non-Aktif (Hidden)</option>
                </select>

                @if($search || $selectedCategory || $statusFilter)
                    <button wire:click="resetFilters" class="btn h-8 rounded-lg bg-slate-150 px-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-200 dark:bg-navy-500 dark:text-navy-100 dark:hover:bg-navy-450">
                        Reset
                    </button>
                @endif
            </div>
        </div>

        {{-- Table --}}
        <div class="is-scrollbar-hidden min-w-full overflow-x-auto">
            <table class="is-hoverable w-full text-left">
                <thead>
                    <tr>
                        <th class="rounded-tl-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs">
                            TOOL & KATEGORI
                        </th>
                        <th class="bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs text-center">
                            BADGE
                        </th>
                        <th class="bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs text-center">
                            BERANDA
                        </th>
                        <th class="bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs text-center">
                            PENGGUNAAN
                        </th>
                        <th class="bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs text-center">
                            URUTAN
                        </th>
                        <th class="bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs text-center">
                            STATUS OPERASIONAL
                        </th>
                        <th class="rounded-tr-lg bg-slate-200 px-4 py-3 font-semibold uppercase text-slate-800 dark:bg-navy-800 dark:text-navy-100 lg:px-5 text-xs text-right">
                            AKSI
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-150 dark:divide-navy-500">
                    @forelse($tools as $tool)
                        <tr class="border-y border-transparent border-b-slate-200 dark:border-b-navy-500 hover:bg-slate-50/80 dark:hover:bg-navy-700/50 transition-colors">
                            {{-- Tool Identity --}}
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                <div class="flex items-center space-x-3">
                                    @if($tool->image_url)
                                        <div class="size-10 rounded-lg overflow-hidden bg-slate-100 dark:bg-navy-700 p-0.5 border border-slate-200 dark:border-navy-600 shrink-0">
                                            <img src="{{ $tool->image_url }}" alt="{{ $tool->name }}" class="size-full object-contain" />
                                        </div>
                                    @else
                                        <div class="mask is-squircle flex size-10 shrink-0 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light font-bold">
                                            @if($tool->category === 'Image')
                                                <x-lucide-image class="size-5" />
                                            @elseif($tool->category === 'PDF' || $tool->category === 'Document')
                                                <x-lucide-file-text class="size-5" />
                                            @else
                                                <x-lucide-wrench class="size-5" />
                                            @endif
                                        </div>
                                    @endif
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-700 dark:text-navy-100 text-xs sm:text-sm">
                                                {{ $tool->name }}
                                            </span>
                                            <span class="badge rounded-full bg-slate-100 text-slate-600 dark:bg-navy-600 dark:text-navy-200 text-[10px] font-semibold px-2 py-0.5">
                                                {{ $tool->category }}
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-slate-400 dark:text-navy-300 font-mono mt-0.5 flex items-center gap-1.5">
                                            <span>/tool/{{ $tool->slug }}</span>
                                            @if($tool->component)
                                                <span class="text-slate-300 dark:text-navy-500">•</span>
                                                <span class="text-[10px] text-slate-400">{{ $tool->component }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Badge Label --}}
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5 text-center">
                                @if($tool->badge === 'HOT')
                                    <span class="badge rounded-full bg-linear-to-r from-red-500 to-orange-500 text-white text-[10px] font-black px-2.5 py-0.5 uppercase shadow-xs">
                                        🔥 HOT
                                    </span>
                                @elseif($tool->badge === 'NEW')
                                    <span class="badge rounded-full bg-linear-to-r from-emerald-500 to-teal-500 text-white text-[10px] font-black px-2.5 py-0.5 uppercase shadow-xs">
                                        ✨ NEW
                                    </span>
                                @elseif($tool->badge === 'PRO')
                                    <span class="badge rounded-full bg-linear-to-r from-amber-500 to-purple-600 text-white text-[10px] font-black px-2.5 py-0.5 uppercase shadow-xs">
                                        👑 PRO
                                    </span>
                                @elseif($tool->badge === 'BETA')
                                    <span class="badge rounded-full bg-info/15 text-info text-[10px] font-bold px-2.5 py-0.5 uppercase">
                                        🧪 BETA
                                    </span>
                                @elseif($tool->badge)
                                    <span class="badge rounded-full bg-slate-150 text-slate-700 dark:bg-navy-500 dark:text-navy-200 text-[10px] font-bold px-2.5 py-0.5 uppercase">
                                        {{ $tool->badge }}
                                    </span>
                                @else
                                    <span class="text-slate-300 dark:text-navy-400 text-xs">-</span>
                                @endif
                            </td>

                            {{-- Highlighted on Landing Page Toggle --}}
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5 text-center">
                                <button wire:click="toggleHighlighted({{ $tool->id }})" 
                                        title="{{ $tool->is_highlighted ? 'Aktif di Highlight Beranda (Klik untuk batalkan)' : 'Tidak di-highlight (Klik untuk tampilkan di Beranda)' }}"
                                        class="btn size-7 rounded-full p-0 transition-all {{ $tool->is_highlighted ? 'bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-300 shadow-xs' : 'bg-slate-100 text-slate-400 dark:bg-navy-600 dark:text-navy-300 hover:text-amber-500' }}">
                                    <x-lucide-star class="size-3.5 {{ $tool->is_highlighted ? 'fill-current' : '' }}" />
                                </button>
                            </td>

                            {{-- Total Usage Count --}}
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5 text-center">
                                <span class="font-bold text-slate-700 dark:text-navy-100 text-xs">
                                    {{ number_format($tool->total_usage_count) }}
                                </span>
                                <span class="text-[10px] text-slate-400 dark:text-navy-300 block">kali</span>
                            </td>

                            {{-- Sort Order --}}
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5 text-center font-bold text-slate-600 dark:text-navy-200 text-xs">
                                {{ $tool->sort_order }}
                            </td>

                            {{-- Operational Status --}}
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                <div class="flex items-center justify-center space-x-3">
                                    {{-- Active Toggle --}}
                                    <div class="flex items-center space-x-1.5" title="{{ $tool->is_active ? 'Tool Aktif' : 'Tool Dinonaktifkan' }}">
                                        <label class="inline-flex cursor-pointer items-center">
                                            <input class="form-switch h-4.5 w-8 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white" type="checkbox" wire:click="toggleActive({{ $tool->id }})" {{ $tool->is_active ? 'checked' : '' }} />
                                        </label>
                                        <span class="text-[11px] font-semibold {{ $tool->is_active ? 'text-success' : 'text-slate-400 dark:text-navy-400' }}">
                                            {{ $tool->is_active ? 'Aktif' : 'Off' }}
                                        </span>
                                    </div>

                                    <div class="h-4 w-px bg-slate-200 dark:bg-navy-600"></div>

                                    {{-- Maintenance Toggle --}}
                                    <div class="flex items-center space-x-1.5" title="{{ $tool->is_maintenance ? 'Mode Pemeliharaan Aktif' : 'Normal' }}">
                                        <label class="inline-flex cursor-pointer items-center">
                                            <input class="form-switch h-4.5 w-8 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-warning checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-warning dark:checked:before:bg-white" type="checkbox" wire:click="toggleMaintenance({{ $tool->id }})" {{ $tool->is_maintenance ? 'checked' : '' }} />
                                        </label>
                                        <span class="text-[11px] font-semibold {{ $tool->is_maintenance ? 'text-warning' : 'text-slate-400 dark:text-navy-400' }}">
                                            {{ $tool->is_maintenance ? 'Maint' : 'Normal' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            {{-- Actions --}}
                            <td class="whitespace-nowrap px-4 py-3 text-right sm:px-5">
                                <div class="flex items-center justify-end space-x-1.5">
                                    <button wire:click="edit({{ $tool->id }})" title="Edit Detail Tool" class="btn size-7 rounded-md bg-primary/10 text-primary hover:bg-primary/20 dark:bg-accent-light/10 dark:text-accent-light dark:hover:bg-accent-light/20 p-0 text-xs font-semibold shadow-xs">
                                        <x-lucide-pencil class="size-3.5" />
                                    </button>
                                    <button wire:click="confirmDelete({{ $tool->id }})" title="Hapus Tool" class="btn size-7 rounded-md bg-error/10 text-error hover:bg-error/20 p-0 text-xs font-semibold shadow-xs">
                                        <x-lucide-trash-2 class="size-3.5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="whitespace-nowrap px-4 py-8 text-center text-slate-400 dark:text-navy-300 text-xs">
                                <x-lucide-wrench class="size-10 mx-auto text-slate-300 dark:text-navy-400 mb-2" />
                                <p>Tidak ada tool yang cocok dengan kriteria pencarian.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($tools->hasPages())
            <div class="flex flex-col justify-between gap-4 p-4 sm:flex-row sm:items-center sm:px-5 border-t border-slate-150 dark:border-navy-600">
                <div class="text-xs text-slate-400 dark:text-navy-300">
                    Menampilkan <strong>{{ $tools->firstItem() }}</strong> sampai <strong>{{ $tools->lastItem() }}</strong> dari <strong>{{ $tools->total() }}</strong> tools
                </div>
                <div>
                    {{ $tools->links('components.lineone-pagination') }}
                </div>
            </div>
        @endif
    </div>

    {{-- Lineone Modal Form: Tambah / Edit Tool --}}
    @if($isModalOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity duration-300"
         x-data
         x-on:keydown.escape.window="$wire.set('isModalOpen', false)">
        <div class="relative w-full max-w-2xl rounded-2xl bg-white dark:bg-navy-700 shadow-2xl border border-slate-200 dark:border-navy-600 overflow-hidden flex flex-col max-h-[90vh]">
            
            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-150 dark:border-navy-600 bg-slate-50 dark:bg-navy-800">
                <div class="flex items-center space-x-3">
                    <div class="mask is-squircle flex size-10 items-center justify-center bg-primary/10 text-primary dark:bg-accent-light/10 dark:text-accent-light font-bold">
                        <x-lucide-wrench class="size-5" />
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800 dark:text-navy-100">
                            {{ $toolId ? 'Edit Konfigurasi Tool' : 'Tambah Tool Baru' }}
                        </h3>
                        <p class="text-xs text-slate-400 dark:text-navy-300">
                            {{ $toolId ? 'Perbarui informasi publik, gambar thumbnail, dan status operasional tool.' : 'Daftarkan tool baru ke dalam katalog sistem.' }}
                        </p>
                    </div>
                </div>
                <button wire:click="$set('isModalOpen', false)" class="btn size-8 rounded-full p-0 text-slate-400 hover:bg-slate-200 hover:text-slate-700 dark:hover:bg-navy-600 dark:hover:text-navy-100">
                    <x-lucide-x class="size-4.5" />
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="p-5 space-y-4 overflow-y-auto is-scrollbar-hidden flex-1 text-xs">
                
                {{-- Row 1: Name & Slug --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block">
                            <span class="font-semibold text-slate-700 dark:text-navy-100 mb-1 block">Nama Tool <span class="text-error">*</span></span>
                            <input wire:model.live="name" type="text" placeholder="Contoh: PDF ke Word" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                        </label>
                        @error('name') <span class="text-[11px] text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block">
                            <span class="font-semibold text-slate-700 dark:text-navy-100 mb-1 block">Slug Rute <span class="text-error">*</span></span>
                            <input wire:model="slug" type="text" placeholder="pdf-to-word" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 font-mono text-primary dark:text-accent-light placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                        </label>
                        @error('slug') <span class="text-[11px] text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Row 2: Category & Livewire Component --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block">
                            <span class="font-semibold text-slate-700 dark:text-navy-100 mb-1 block">Kategori <span class="text-error">*</span></span>
                            <input wire:model="category" list="categoryOptions" type="text" placeholder="Image, PDF, Document, Text, dll." class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                            <datalist id="categoryOptions">
                                <option value="Image">
                                <option value="PDF">
                                <option value="Document">
                                <option value="Text">
                                <option value="Developer">
                                <option value="Utility">
                            </datalist>
                        </label>
                        @error('category') <span class="text-[11px] text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block">
                            <span class="font-semibold text-slate-700 dark:text-navy-100 mb-1 block">Nama Komponen Livewire</span>
                            <input wire:model="component" type="text" placeholder="tools.pdf-to-word" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 font-mono text-slate-600 dark:text-navy-200 placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                        </label>
                        @error('component') <span class="text-[11px] text-error mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Row 3: Image Upload & Thumbnail --}}
                <div class="rounded-xl border border-slate-200 dark:border-navy-600 p-3.5 bg-slate-50/50 dark:bg-navy-800/40">
                    <span class="font-semibold text-slate-700 dark:text-navy-100 mb-1.5 block">Gambar Thumbnail / Ilustrasi Tool (Opsional)</span>
                    <div class="flex items-center space-x-4">
                        {{-- Preview Box --}}
                        <div class="size-16 rounded-xl border border-dashed border-slate-300 dark:border-navy-500 bg-white dark:bg-navy-700 p-1 flex items-center justify-center shrink-0 overflow-hidden">
                            @if($imageFile)
                                <img src="{{ $imageFile->temporaryUrl() }}" alt="Preview" class="size-full object-contain" />
                            @elseif($image)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->exists($image) ? \Illuminate\Support\Facades\Storage::url($image) : (str_starts_with($image, 'http') ? $image : asset($image)) }}" alt="Existing" class="size-full object-contain" />
                            @else
                                <x-lucide-image class="size-6 text-slate-300 dark:text-navy-400" />
                            @endif
                        </div>

                        <div class="flex-1 space-y-1.5">
                            <input wire:model="imageFile" type="file" accept="image/png,image/jpeg,image/svg+xml,image/webp" class="block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 dark:file:bg-accent-light/10 dark:file:text-accent-light cursor-pointer" />
                            <div class="flex items-center justify-between text-[10px] text-slate-400">
                                <span>PNG, JPG, SVG, WebP (Maks 2MB)</span>
                                @if($image || $imageFile)
                                    <button wire:click="removeImage" type="button" class="text-error font-semibold hover:underline">Hapus Gambar</button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @error('imageFile') <span class="text-[11px] text-error mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Row 4: Description --}}
                <div>
                    <label class="block">
                        <span class="font-semibold text-slate-700 dark:text-navy-100 mb-1 block">Deskripsi Layanan</span>
                        <textarea wire:model="description" rows="2" placeholder="Jelaskan kegunaan tool ini kepada pengguna..." class="form-textarea w-full rounded-lg border border-slate-300 bg-transparent p-2.5 placeholder:text-slate-400 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent"></textarea>
                    </label>
                    @error('description') <span class="text-[11px] text-error mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Row 5: Badge & Sort Order --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block">
                            <span class="font-semibold text-slate-700 dark:text-navy-100 mb-1 block">Label Badge Khusus</span>
                            <select wire:model="badge" class="form-select w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent">
                                <option value="">Tanpa Badge (Normal)</option>
                                <option value="HOT">🔥 HOT / Populer</option>
                                <option value="NEW">✨ NEW / Baru</option>
                                <option value="PRO">👑 PRO Only</option>
                                <option value="BETA">🧪 BETA</option>
                            </select>
                        </label>
                    </div>
                    <div>
                        <label class="block">
                            <span class="font-semibold text-slate-700 dark:text-navy-100 mb-1 block">Urutan Tampilan</span>
                            <input wire:model="sort_order" type="number" class="form-input w-full rounded-lg border border-slate-300 bg-transparent px-3 py-2 hover:border-slate-400 focus:border-primary dark:border-navy-450 dark:hover:border-navy-400 dark:focus:border-accent" />
                        </label>
                    </div>
                </div>

                {{-- Operational Settings Box --}}
                <div class="rounded-xl border border-slate-150 dark:border-navy-600 p-4 bg-slate-50/50 dark:bg-navy-800/40 space-y-3">
                    <h4 class="font-bold text-slate-700 dark:text-navy-100 uppercase tracking-wider text-[11px]">
                        Kontrol Operasional & Visibilitas
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        {{-- Active Switch --}}
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 dark:border-navy-600 p-3 bg-white dark:bg-navy-700">
                            <div>
                                <p class="font-bold text-slate-700 dark:text-navy-100">Status Aktif</p>
                                <p class="text-[10px] text-slate-400">Tampilkan di sistem</p>
                            </div>
                            <label class="inline-flex cursor-pointer items-center">
                                <input class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-primary checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-accent dark:checked:before:bg-white" type="checkbox" wire:model="is_active" />
                            </label>
                        </div>

                        {{-- Highlighted on Landing Page Switch --}}
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 dark:border-navy-600 p-3 bg-white dark:bg-navy-700">
                            <div>
                                <p class="font-bold text-slate-700 dark:text-navy-100">Highlight Beranda</p>
                                <p class="text-[10px] text-slate-400">Katalog Welcome Page</p>
                            </div>
                            <label class="inline-flex cursor-pointer items-center">
                                <input class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-amber-500 checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-amber-500 dark:checked:before:bg-white" type="checkbox" wire:model="is_highlighted" />
                            </label>
                        </div>

                        {{-- Maintenance Switch --}}
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 dark:border-navy-600 p-3 bg-white dark:bg-navy-700">
                            <div>
                                <p class="font-bold text-slate-700 dark:text-navy-100">Mode Maint</p>
                                <p class="text-[10px] text-slate-400">Kunci pemeliharaan</p>
                            </div>
                            <label class="inline-flex cursor-pointer items-center">
                                <input class="form-switch h-5 w-10 rounded-full bg-slate-300 before:rounded-full before:bg-slate-50 checked:bg-warning checked:before:bg-white dark:bg-navy-900 dark:before:bg-navy-300 dark:checked:bg-warning dark:checked:before:bg-white" type="checkbox" wire:model="is_maintenance" />
                            </label>
                        </div>
                    </div>

                    @if($is_maintenance)
                    <div class="pt-2">
                        <label class="block">
                            <span class="font-semibold text-slate-700 dark:text-navy-100 mb-1 block">Pesan Maintenance Khusus (Opsional)</span>
                            <input wire:model="maintenance_message" type="text" placeholder="Layanan ini sedang ditingkatkan performanya. Silakan coba beberapa saat lagi." class="form-input w-full rounded-lg border border-warning/50 bg-white dark:bg-navy-700 px-3 py-2 text-xs" />
                        </label>
                    </div>
                    @endif
                </div>

            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-end space-x-2 border-t border-slate-150 bg-slate-50 px-5 py-3.5 dark:border-navy-600 dark:bg-navy-800">
                <button wire:click="$set('isModalOpen', false)" class="btn rounded-full border border-slate-300 font-semibold text-slate-700 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-100 dark:hover:bg-navy-500 text-xs px-4 py-2">
                    Batal
                </button>
                <button wire:click="save" class="btn rounded-full bg-primary font-bold text-white hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus text-xs px-5 py-2 shadow-sm flex items-center space-x-1.5">
                    <span wire:loading.remove wire:target="save,imageFile">Simpan Tool</span>
                    <span wire:loading wire:target="save,imageFile" class="flex items-center space-x-1">
                        <x-lucide-loader-2 class="size-3.5 animate-spin" />
                        <span>Menyimpan...</span>
                    </span>
                </button>
            </div>

        </div>
    </div>
    @endif
</div>
