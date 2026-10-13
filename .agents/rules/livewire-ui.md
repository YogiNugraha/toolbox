# Livewire & UI Standards: ToolBox

Dokumen ini mendefinisikan standar frontend, komponen Livewire, dan implementasi UI Kit Lineone + Tailwind CSS v4.

## 1. Konvensi Komponen Livewire
1. **Class-Based Components**:
   - Setiap komponen Livewire berupa kelas PHP di `App\Livewire\...` dan template Blade di `resources/views/livewire/...`.
   - Konfigurasi `config/livewire.php` menyetel `'type' => 'class'`. **Jangan membuat Single-File Component (Volt / inline Blade scripts)**.
2. **File Uploads**:
   - Selalu sertakan trait `Livewire\WithFileUploads`.
   - Gunakan `x-on:livewire-upload-progress="progress = $event.detail.progress"` untuk progress bar interaktif.
   - Bersihkan dan reset error bag ketika pengguna memilih atau menghapus berkas baru (`$this->resetErrorBag()`, `$this->resetValidation()`).
3. **Notifikasi Toast**:
   - Sertakan trait `App\Traits\LivewireLineoneAlerts`.
   - Panggil `$this->toast('Pesan berhasil', 'success')`.
   - Varian yang didukung: `success`, `error`, `warning`, `info`, `primary`, `secondary`.
4. **Single-Page Navigation**:
   - Gunakan atribut `wire:navigate` pada tag link navigasi internal untuk pengalaman SPA yang mulus tanpa reload halaman penuh.

## 2. Standar Tema & Desain Lineone (Tailwind CSS v4)
1. **Palet Warna**:
   - Gunakan palet bawaan Lineone:
     - Background terang: `bg-slate-50`, `bg-white`, `border-slate-150`, `border-slate-200`
     - Background gelap (Dark Mode): `dark:bg-navy-900`, `dark:bg-navy-800`, `dark:bg-navy-700`, `dark:border-navy-600`
     - Teks: `text-slate-800 dark:text-navy-50`, `text-slate-700 dark:text-navy-100`, `text-slate-400 dark:text-navy-300`
     - Aksen/Primer: `bg-primary hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus`
     - Secondary: `bg-secondary text-white dark:bg-secondary-light`

2. **Tipografi & Ukuran Font (Zero AI-Slop)**:
   - **Headings & Titles**: Gunakan `font-semibold text-slate-800 dark:text-navy-50` atau `font-medium tracking-wide text-slate-700 dark:text-navy-100`.
   - **DILARANG KERAS**: Menggunakan `font-black` atau `font-extrabold`. Hindari penumpukan `font-bold` berlebihan; gunakan `font-medium` atau `font-semibold` sesuai Lineone.
   - **Ukuran Font Terstandarisasi**: DILARANG menggunakan arbitrary font sizes seperti `text-[9px]`, `text-[10px]`, `text-[11px]`. Gunakan token resmi Tailwind: `text-xs`, `text-sm`, `text-base`.

3. **Komponen UI Baku**:
   - **Card**: `<div class="card p-5 sm:p-6 rounded-lg border border-slate-150 dark:border-navy-600 bg-white dark:bg-navy-700 ...">`. Dilarang menggunakan shadow berlebihan (`shadow-xl`, `shadow-2xl`) atau background gradient multi-stop.
   - **Tombol (Buttons)**:
     - Tinggi tombol standar: `h-9` atau `h-10`, rounded `rounded-lg` atau `rounded-full`, teks `font-medium text-xs sm:text-sm`.
     - Tombol Primer: `<button class="btn h-9 rounded-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus text-xs px-5 shadow-xs">`
     - Tombol Sekunder / Outline: `<button class="btn h-9 rounded-full border border-slate-300 font-medium text-slate-700 hover:bg-slate-150 dark:border-navy-450 dark:text-navy-100 dark:hover:bg-navy-500 text-xs px-4">`
     - **DILARANG**: Efek bayangan berpendar/berwarna (`shadow-primary/30`, `shadow-warning/20`). Gunakan standar `shadow-xs` atau `shadow-sm`.
   - **Badge**: Wajib mengikuti format murni Lineone UI Kit (`C:\laragon\www\lineone-laravel`):
     - Format baku: `badge rounded-full px-2.5 py-0.5 text-xs font-medium`
     - **DILARANG KERAS** menyisipkan emoji (`🌟`, `👑`, `🔥`, `✨`) atau icon (`<x-lucide-...>`) di dalam tag badge. Badge harus berupa teks murni.
     - **DILARANG** menggunakan background gradient buatan (`bg-linear-to-...`) pada badge. Gunakan palet standar solid atau soft Lineone:
       - Default / Kategori: `badge rounded-full bg-slate-150 text-slate-700 dark:bg-navy-600 dark:text-navy-200 text-xs font-medium px-2.5 py-0.5`
       - Primary / PRO: `badge rounded-full bg-primary/10 text-primary dark:bg-accent-light/15 dark:text-accent-light text-xs font-medium px-2.5 py-0.5`
       - Secondary / PRO MAX: `badge rounded-full bg-secondary/15 text-secondary dark:bg-secondary-light/15 dark:text-secondary-light text-xs font-medium px-2.5 py-0.5`
       - Warning / Featured / Maintenance: `badge rounded-full bg-warning/15 text-warning dark:bg-warning/20 dark:text-warning-light text-xs font-medium px-2.5 py-0.5`
       - Success / NEW / Selesai: `badge rounded-full bg-success/15 text-success dark:bg-success/20 dark:text-success-light text-xs font-medium px-2.5 py-0.5`
       - Error / HOT / Gagal: `badge rounded-full bg-error/15 text-error dark:bg-error/20 dark:text-error-light text-xs font-medium px-2.5 py-0.5`
       - Info / Tag: `badge rounded-full bg-info/10 text-info dark:bg-info/15 text-xs font-medium px-2.5 py-0.5`
   - **Alert**: `<div class="alert flex rounded-lg border border-error px-4 py-3 text-error ...">`
   - **Anti-AI-Slop & Anti-Redundansi**:
     - Dilarang menyisipkan dekorasi blur bola cahaya ambient melayang (`filter blur-3xl opacity-20 bg-linear-to-r`).
     - Dilarang menempelkan icon dekoratif langsung di judul/heading (`<h[1-6]><x-lucide-...> Judul</h[1-6]>`).
     - Dilarang menempelkan badge dekoratif di samping heading halaman ("Live Analytics", "System Config", "Account Settings").
     - Dilarang mengulang badge filter periode di dalam kartu metrik bila filter sudah terpampang di atas.

4. **Ikon**:
   - Gunakan library Lucide Blade Icons: `<x-lucide-{icon-name} class="size-4" />` atau `size-4.5`.
   - Hindari icon emoji mentah untuk kontrol UI inti; gunakan Lucide icon yang presisi dan konsisten.

## 3. Standar Aksi Tabel (Elipsis Titik Tiga `...`)
Jika sebuah baris tabel memiliki **lebih dari 1 aksi**, DILARANG menjejerkan tombol secara horizontal. Wajib diringkas menjadi tombol **elipsis titik tiga vertikal (`...`)** dengan dropdown mengambang via Lineone Popper:
```blade
<div x-data="usePopper({ placement: 'bottom-end', offset: 4 })" @click.outside="if(isShowPopper) isShowPopper = false" class="inline-flex">
    <button x-ref="popperRef" @click="isShowPopper = !isShowPopper" class="btn size-8 rounded-full p-0 text-slate-500 hover:bg-slate-300/20 focus:bg-slate-300/20 active:bg-slate-300/25 transition-colors" title="Menu Aksi">
        <x-lucide-ellipsis-vertical class="size-4.5" />
    </button>
    <div x-ref="popperRoot" class="popper-root fixed" :class="isShowPopper && 'show'">
        <div class="popper-box rounded-md border border-slate-150 bg-white py-1.5 font-inter shadow-lg min-w-[9.5rem] text-left">
            <ul>
                ...
            </ul>
        </div>
    </div>
</div>
```
*Catatan: Lineone `usePopper` dengan `strategy: 'fixed'` menjamin menu dropdown tidak terpotong oleh `overflow-x: auto` tabel.*

## 4. Standar Animasi & Transisi Fast Snappy
- **Dropdown / Popper**: `duration-100 ease-out` dengan micro-offset `translate-y-1`.
- **Modal Panel**: `duration-150 ease-out` enter / `duration-100 ease-in` leave.
- **Sidebar Panel**: `duration-150 ease-out` tanpa delay.

## 5. Kebijakan Pure Light Mode
- Platform berjalan murni **Light Mode**. Fitur Dark Mode dinonaktifkan (`isDarkModeEnabled: false`).
- Dilarang membuat tombol switcher dark mode di header atau navbar.

## 6. Anatomi Halaman Tool
Setiap view tool harus mengimplementasikan 3 state:
```blade
<div>
    {{-- Error Alert --}}
    @if (!empty($errorMsg)) ... @endif

    {{-- 1. Empty/Upload State (Belum ada berkas) --}}
    @if (!$file && !$resultPath)
        <div class="card p-6 sm:p-8 max-w-3xl mx-auto space-y-6">
            ... Drag & drop zone dengan Alpine.js upload progress ...
        </div>
    @endif

    {{-- 2. Configuration & Preview State (Berkas siap diproses) --}}
    @if ($file && !$resultPath)
        <div class="card p-6 sm:p-8 max-w-4xl mx-auto space-y-6">
            ... Opsi pengaturan alat, thumbnail per halaman, tombol proses ...
        </div>
    @endif

    {{-- 3. Result & Download State (Berkas selesai diolah) --}}
    @if ($resultPath)
        <div class="card p-6 sm:p-8 max-w-2xl mx-auto text-center space-y-6">
            ... Hasil statistik perbandingan ukuran, tombol download, tombol reset ...
        </div>
    @endif
</div>
```
