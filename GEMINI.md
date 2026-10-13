# 🧰 GEMINI.md — Aturan dan Standar AI untuk Proyek ToolBox

File ini melengkapi [AGENTS.md](file:///c:/laragon/www/toolbox/AGENTS.md) sebagai panduan perilaku agen AI di seluruh direktori repositori ini.

## Prinsip Kerja Utama:
1. **Livewire 4 Class-Based**: Komponen selalu berupa class PHP di `app/Livewire/` dan template Blade terpisah di `resources/views/livewire/`. Jangan gunakan sintaks single-file Volt.
2. **Kepatuhan UI Lineone Murni**: Gunakan kelas Tailwind CSS v4 dari tema Lineone (`C:\laragon\www\lineone-laravel`) dengan palet `slate` / `navy` (`dark:bg-navy-900`, `dark:bg-navy-800`), komponen `card`, `btn`, `badge`, `alert`.
3. **Standar Tipografi (Zero AI-Slop)**:
   - Gunakan `font-semibold` atau `font-medium` untuk judul dan heading.
   - **DILARANG KERAS**: `font-black` dan `font-extrabold`. Hindari pemakaian `font-bold` berlebihan.
   - **DILARANG** menggunakan arbitrary pixel font sizes seperti `text-[9px]`, `text-[10px]`, `text-[11px]`. Selalu gunakan token baku Tailwind: `text-xs`, `text-sm`, `text-base`.
4. **Standar Tombol (Buttons)**:
   - Tinggi tombol standar: `h-9` atau `h-10`, rounded: `rounded-full` atau `rounded-lg`, teks: `font-medium text-xs sm:text-sm`.
   - Tombol utama: `btn h-9 rounded-full bg-primary font-medium text-white hover:bg-primary-focus dark:bg-accent text-xs px-5 shadow-xs`.
   - **DILARANG**: Bayangan berwarna/berpendar (`shadow-primary/*`, `shadow-warning/*`). Gunakan `shadow-xs` atau `shadow-sm`.
5. **Kepatuhan Murni Badge Lineone**:
   - Format: `badge rounded-full px-2.5 py-0.5 text-xs font-medium`.
   - **DILARANG KERAS** menyisipkan emoji/icon (`🌟`, `👑`, `🔥`, `✨`, `<x-lucide-...>`) di dalam badge. Wajib teks bersih murni.
   - **DILARANG** background gradient buatan (`bg-linear-to-...`). Wajib palet soft Lineone (`bg-primary/10 text-primary`, `bg-secondary/15 text-secondary`, `bg-warning/15 text-warning`, `bg-success/15 text-success`, `bg-error/15 text-error`, `bg-slate-150 text-slate-700`).
6. **Anti-AI-Slop & Desain Bersih**:
   - Dilarang membuat ornamen bola cahaya melayang / ambient blur orbs (`filter blur-3xl opacity-20`).
   - Dilarang membuat border gradient pelangi atau shadow raksasa (`shadow-2xl`).
7. **Penyampaian Feedback Interaktif**: Selalu gunakan trait `LivewireLineoneAlerts` (`$this->toast(...)`) untuk notifikasi aksi pengguna.
8. **Pemisahan Logika Alat**: Livewire menangani interaksi pengguna dan upload; Service di `app/Services/Tools/` menangani proses; skrip Python di `storage/scripts/` melakukan operasi komputasi berkas.
9. **Perlindungan Kuota & Pro**: Setiap alat wajib memeriksa kuota melalui `EntitlementService` dan mencatat aktivitas di tabel `activities`.

