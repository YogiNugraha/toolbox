---
name: toolbox-lineone-frontend
description: >-
  Use this skill when developing, refactoring, or styling frontend Blade views, Livewire components,
  tables, dropdowns, and modals in the ToolBox project, following strict Lineone UI Kit standards,
  pure Light Mode, table ellipsis action dropdowns, and snappy micro-animations.
---

# 🎨 ToolBox Frontend & Lineone UI Kit Standards

Skill ini memuat panduan komprehensif, pola baku komponen, dan arsitektur UI/UX platform **ToolBox** berbasis **Lineone UI Kit + Tailwind CSS v4 + Livewire 4 Class-Based**.

---

## 1. Prinsip Desain Inti & Kebijakan Platform

1. **Pure Light Mode (Strict)**:
   - Platform beroperasi secara **murni Light Mode**. Fitur Dark Mode dinonaktifkan (`isDarkModeEnabled: false`).
   - Jangan menambahkan tombol switcher / toggle dark mode di header atau navbar.
   - Script layout secara otomatis membersihkan class `.dark` dan key `_x_darkMode_on` dari browser.

2. **Zero AI-Slop & Desain Bersih**:
   - **DILARANG KERAS** menyisipkan bola gradasi ambient blur melayang (`filter blur-3xl opacity-20`).
   - **DILARANG KERAS** border rainbow, gradient buatan multi-stop, atau shadow raksasa (`shadow-xl`, `shadow-2xl`). Gunakan `shadow-xs` atau `shadow-sm`.
   - **DILARANG KERAS** menempelkan icon dekoratif langsung di judul/heading (`<h[1-6]><x-lucide-...> Judul</h[1-6]>`). Judul wajib teks bersih.
   - **DILARANG KERAS** menempelkan badge dekoratif melayang di samping judul halaman (misal: "Live Analytics", "System Config", "Account Settings").
   - **DILARANG KERAS** redundansi informasi (misal: mengulang badge periode filter di setiap card metrik ketika filternya sudah aktif di header).

3. **Tipografi & Font**:
   - Judul & Headings: `font-semibold text-slate-800` atau `font-medium tracking-wide text-slate-700`.
   - **ZERO TOLERANCE** untuk `font-black` dan `font-extrabold`.
   - **ZERO TOLERANCE** untuk arbitrary pixel font sizes (`text-[9px]`, `text-[10px]`, `text-[11px]`). Wajib token standar Tailwind (`text-xs`, `text-sm`, `text-base`).

---

## 2. Standar Aksi Tabel (Tabel Rows dengan > 1 Aksi)

Setiap tabel di platform (Pengguna, Tools, Paket, Transaksi, Riwayat, Billing) **WAJIB** mematuhi aturan ini:

> **Aturan Baku**: Jika sebuah baris tabel memiliki **lebih dari 1 aksi**, DILARANG menjejerkan tombol secara horizontal. Wajib diringkas menjadi tombol **elipsis titik tiga vertikal (`...`)** dengan dropdown mengambang via Lineone Popper.

### Mengapa Wajib Menggunakan Lineone Popper (`usePopper`)?
Tabel responsif memiliki container scroll `overflow-x: auto`. Dropdown biasa (`absolute`) akan **terpotong / tersembunyi** oleh batas bawah container tabel. Lineone `usePopper({ placement: 'bottom-end', offset: 4 })` menggunakan `@popperjs/core` dengan `strategy: 'fixed'`, sehingga menu dropdown mengambang bebas di atas viewport dan **tidak pernah terpotong**.

### Template Baku Dropdown Elipsis Tabel:
```blade
<td class="whitespace-nowrap px-4 py-3 text-right sm:px-5">
    <div x-data="usePopper({ placement: 'bottom-end', offset: 4 })" @click.outside="if(isShowPopper) isShowPopper = false" class="inline-flex">
        {{-- Tombol Elipsis Titik Tiga --}}
        <button x-ref="popperRef" @click="isShowPopper = !isShowPopper" 
                class="btn size-8 rounded-full p-0 text-slate-500 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 transition-colors" 
                title="Menu Aksi">
            <x-lucide-ellipsis-vertical class="size-4.5" />
        </button>

        {{-- Popper Menu Container (Fixed Strategy) --}}
        <div x-ref="popperRoot" class="popper-root fixed" :class="isShowPopper && 'show'">
            <div class="popper-box rounded-md border border-slate-150 bg-white py-1.5 font-inter shadow-lg min-w-[9.5rem] text-left">
                <ul>
                    {{-- Aksi Utama / Link --}}
                    <li>
                        <button @click="isShowPopper = false" wire:click="editItem({{ $item->id }})" 
                                class="flex h-8 w-full items-center space-x-2 px-3 text-left font-inter text-xs text-slate-700 hover:bg-slate-100 hover:text-slate-800 transition-colors">
                            <x-lucide-pencil class="size-3.5 text-primary" />
                            <span>Edit Data</span>
                        </button>
                    </li>

                    {{-- Aksi Sekunder / Navigasi --}}
                    <li>
                        <a @click="isShowPopper = false" href="{{ route('detail', $item->id) }}" 
                           class="flex h-8 w-full items-center space-x-2 px-3 text-left font-inter text-xs text-slate-700 hover:bg-slate-100 hover:text-slate-800 transition-colors">
                            <x-lucide-info class="size-3.5 text-info" />
                            <span>Lihat Detail</span>
                        </a>
                    </li>

                    {{-- Divider untuk Aksi Destruktif --}}
                    <div class="my-1 h-px bg-slate-150"></div>

                    {{-- Aksi Hapus / Bahaya --}}
                    <li>
                        <button @click="isShowPopper = false" wire:click="confirmDelete({{ $item->id }})" 
                                class="flex h-8 w-full items-center space-x-2 px-3 text-left font-inter text-xs text-error hover:bg-error/10 transition-colors">
                            <x-lucide-trash-2 class="size-3.5" />
                            <span>Hapus Data</span>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</td>
```

---

## 3. Standar Animasi & Transisi Fast / Snappy

Platform menggunakan animasi yang cepat, tajam, dan tidak membuat pengguna menunggu:

1. **Dropdown / Popper**:
   - `duration-100 ease-out`
   - Micro-offset pergeseran: `translate-y-1` atau `translate-x-1` (bukan 12px/`translate-y-3`).
2. **Modal Form & Dialog**:
   - Backdrop & container: `transition-opacity duration-150` (bukan 300ms).
   - Enter: `ease-out duration-150`, Leave: `ease-in duration-100`.
3. **Sidebar Panel**:
   - `duration-150 ease-out` tanpa delay (menghapus `delay-150`).
4. **Button & Switch**:
   - `transition-all duration-150` atau `duration-200`.

---

## 4. Standar Badge Lineone Murni

Badge wajib berupa teks bersih murni tanpa hiasan emoji atau gradient buatan:
- Format baku: `badge rounded-full px-2.5 py-0.5 text-xs font-medium`
- **DILARANG**: Menyisipkan icon (`<x-lucide-...>`) atau emoji (`✨`, `👑`, `🔥`) di dalam badge.
- **Palet Soft Lineone**:
  - Netral/Default: `bg-slate-150 text-slate-700`
  - Primer: `bg-primary/10 text-primary`
  - Sukses: `bg-success/15 text-success`
  - Peringatan: `bg-warning/15 text-warning`
  - Bahaya: `bg-error/15 text-error`
  - Info: `bg-info/10 text-info`
