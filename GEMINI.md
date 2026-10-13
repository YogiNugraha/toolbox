# 🧰 GEMINI.md — Aturan dan Standar AI untuk Proyek ToolBox

File ini melengkapi [AGENTS.md](file:///c:/laragon/www/toolbox/AGENTS.md) sebagai panduan perilaku agen AI di seluruh direktori repositori ini.

## Prinsip Kerja Utama:
1. **Livewire 4 Class-Based**: Komponen selalu berupa class PHP di `app/Livewire/` dan template Blade terpisah di `resources/views/livewire/`. Jangan gunakan sintaks single-file Volt.
2. **Kepatuhan UI Lineone**: Gunakan kelas Tailwind CSS v4 dari tema Lineone (palet `navy`, komponen `badge`, `card`, `btn`, `alert`).
3. **Penyampaian Feedback Interaktif**: Selalu gunakan trait `LivewireLineoneAlerts` (`$this->toast(...)`) untuk notifikasi aksi pengguna.
4. **Pemisahan Logika Alat**: Livewire menangani interaksi pengguna dan upload; Service di `app/Services/Tools/` menangani proses; skrip Python di `storage/scripts/` melakukan operasi komputasi berkas.
5. **Perlindungan Kuota & Pro**: Setiap alat wajib memeriksa kuota melalui `EntitlementService` dan mencatat aktivitas di tabel `activities`.
