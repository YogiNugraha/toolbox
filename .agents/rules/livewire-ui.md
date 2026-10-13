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
     - Background terang: `bg-slate-50`, `bg-white`, `border-slate-200`
     - Background gelap (Dark Mode): `dark:bg-navy-900`, `dark:bg-navy-800`, `dark:border-navy-700`
     - Teks: `text-slate-800 dark:text-navy-100`, `text-slate-400 dark:text-navy-300`
     - Aksen/Primer: `bg-primary hover:bg-primary-focus dark:bg-accent dark:hover:bg-accent-focus`
2. **Komponen UI Baku**:
   - **Card**: `<div class="card p-6 sm:p-8 ...">`
   - **Tombol**: `<button class="btn bg-primary text-white hover:bg-primary-focus ...">`
   - **Badge**: `<span class="badge rounded-full bg-primary/10 text-primary ...">`
   - **Alert**: `<div class="alert flex rounded-lg border border-error px-4 py-4 text-error ...">`
3. **Ikon**:
   - Gunakan library Lucide Blade Icons: `<x-lucide-{icon-name} class="size-5" />`.
   - Hindari icon emoji mentah untuk kontrol UI inti; gunakan Lucide icon yang presisi dan konsisten.

## 3. Anatomi Halaman Tool
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
