# 🎨 DESIGN RESTYLE BRIEF — Hilangkan Kesan "AI Template"

> Fungsional sudah OK, ini murni soal styling. Jangan ubah logic/component PHP, cukup ubah class Tailwind & CSS. Terapkan ke SEMUA halaman dashboard secara konsisten (sidebar, topbar, semua tools, history, profile), bukan cuma halaman Compress Gambar.

---

## 0. Kenapa tampilan sekarang terasa "AI-generated"

Ini pola yang bikin ketauan generik (semua ada di screenshot sekarang):

- Sidebar solid warna indigo/ungu terang — warna paling default di semua admin template AI.
- Card serba `rounded-xl` + `shadow-md/lg` di mana-mana — bikin semuanya terasa "melayang", tidak ada ketegasan.
- Upload dropzone pakai cloud-upload icon generik + rounded dashed border — literally default component di 90% template.
- Avatar bulat isi inisial, breadcrumb icon rumah + chevron abu-abu default.
- Tidak ada identitas tipografi (semua satu jenis font sans default) dan tidak ada elemen yang "milik brand ini doang".

Prinsip perbaikan: **ganti shadow dengan hairline border sebagai penanda kedalaman utama**, ganti warna indigo generik dengan palet "blueprint/workbench", dan kasih 1-2 elemen tipografi/motif yang khas.

---

## 1. Setup: Warna & Font

### Tailwind config — tambahkan custom colors

```js
// tailwind.config.js
colors: {
  paper: '#F5F6F2',      // background utama, ganti dari putih polos
  ink: '#1B2430',        // teks utama & sidebar bg, ganti dari indigo
  'ink-muted': '#5B6472',// teks sekunder
  hairline: '#D7DBD1',   // border tipis, pengganti shadow
  amber: '#E8963C',      // aksen utama / active state / CTA
  steel: '#2F5D8A',      // aksen sekunder (link, info)
}
```

### Font — tambahkan ke layout head (pakai Google Fonts atau Bunny Fonts)

```html
<link rel="preconnect" href="https://fonts.bunny.net" />
<link
    href="https://fonts.bunny.net/css?family=space-grotesk:500,700|inter:400,500,600|jetbrains-mono:400,500"
    rel="stylesheet"
/>
```

```js
fontFamily: {
  display: ['"Space Grotesk"', 'sans-serif'],  // untuk judul & nama tools
  sans: ['Inter', 'sans-serif'],               // body text default
  mono: ['"JetBrains Mono"', 'monospace'],     // WAJIB untuk semua angka: ukuran file, persentase, tanggal, breadcrumb
}
```

### Body/background

```html
<body class="bg-paper text-ink font-sans"></body>
```

---

## 2. Sidebar

**Sekarang:** blok solid indigo terang, active item = pill rounded terang.

**Ganti jadi:**

```html
<aside class="bg-ink text-slate-300 w-64">
    <!-- Logo -->
    <div class="px-6 py-5 border-b border-white/10">
        <span class="font-display font-bold text-white text-lg tracking-tight"
            >ToolBox</span
        >
        <!-- ganti icon puzzle-piece generik dengan icon garis simpel (wrench/gear tipis), jangan icon filled -->
    </div>

    <!-- Menu item default -->
    <a
        class="flex items-center gap-3 px-6 py-2.5 text-sm text-slate-400 hover:text-white hover:bg-white/5 transition-colors"
    >
        Dashboard
    </a>

    <!-- Menu item ACTIVE: hilangkan rounded pill, ganti left-border accent -->
    <a
        class="flex items-center gap-3 px-6 py-2.5 text-sm text-white font-medium bg-white/5 border-l-2 border-amber"
    >
        Compress Gambar
    </a>

    <!-- Section label "TOOLS" -->
    <p
        class="px-6 pt-6 pb-2 text-[11px] font-mono uppercase tracking-widest text-slate-500 border-t border-white/10 mt-4"
    >
        Tools
    </p>
</aside>
```

Kuncinya: **hapus semua `rounded-lg`/`rounded-full` di nav item**, ganti dengan border kiri tipis warna amber untuk state aktif. Ini satu perubahan kecil tapi paling kelihatan bedanya.

---

## 3. Topbar

**Sekarang:** putih polos + shadow halus di bawahnya, avatar bulat ungu.

```html
<header
    class="bg-paper border-b border-hairline px-8 py-4 flex justify-between items-center"
>
    <h1 class="font-display font-bold text-xl text-ink">Compress Gambar</h1>
    <div class="flex items-center gap-3">
        <span class="text-sm text-ink-muted">
            Halo, <span class="font-medium text-ink">Yogi Nugraha</span>
        </span>
        <div
            class="w-8 h-8 rounded-full bg-amber/15 border border-amber/40 text-amber font-mono text-sm flex items-center justify-center"
        >
            Y
        </div>
    </div>
</header>
```

Ganti `shadow-sm` di header (kalau ada) dengan `border-b border-hairline` saja — jangan pakai shadow.

---

## 4. Breadcrumb

**Sekarang:** icon rumah default + chevron abu-abu, gaya breadcrumb template umum.

```html
<nav
    class="font-mono text-xs uppercase tracking-wider text-ink-muted flex items-center gap-2"
>
    <a href="/dashboard" class="hover:text-ink">Dashboard</a>
    <span class="text-hairline">/</span>
    <span class="text-ink">Compress Gambar</span>
</nav>
```

Hilangkan icon rumah & chevron bergaya default, ganti separator jadi `/` monospace simpel. Uppercase + tracking-wider + font mono ini yang kasih rasa "teknis" bukan "template SaaS".

---

## 5. Card / Panel Konten

**Sekarang:** `rounded-xl` + `shadow-lg` putih.

```html
<div class="bg-white border border-hairline rounded-sm p-8">
    <h2 class="font-display font-bold text-2xl text-ink mb-1">
        Compress Gambar
    </h2>
    <p class="text-ink-muted text-sm mb-6">
        Kurangi ukuran file gambar tanpa mengurangi kualitas secara signifikan.
    </p>
    <!-- konten -->
</div>
```

Aturan: `rounded-sm` (bukan `rounded-xl`), `border border-hairline` menggantikan `shadow-lg`. Terapkan ini ke SEMUA card di seluruh dashboard (stat card, tool card, history table container, dll) — konsistensi ini yang bikin terasa "sistem" bukan kumpulan komponen acak.

---

## 6. Upload Dropzone (paling kelihatan generiknya)

**Sekarang:** dashed rounded box + cloud-upload icon abu-abu — ini literally default komponen di hampir semua UI kit AI.

```html
<div
    class="border-2 border-dashed border-hairline rounded-sm p-12 text-center hover:border-amber/50 hover:bg-amber/5 transition-colors cursor-pointer"
>
    <!-- ganti cloud-icon dengan icon garis tipis custom, atau minimal ukuran lebih kecil & warna ink-muted bukan abu generik -->
    <p class="font-medium text-ink">
        Klik untuk upload
        <span class="text-ink-muted font-normal">atau drag and drop</span>
    </p>
    <p class="font-mono text-xs text-ink-muted mt-2 tracking-wide">
        JPG · PNG · WEBP — MAX 10MB
    </p>
</div>
```

Tambahkan badge kecil format file bergaya tag monospace (bukan teks polos abu-abu) — detail kecil ini yang kasih nuansa "teknis presisi" sesuai konsep blueprint.

Untuk panel preview hasil di sebelahnya (yang sekarang kosong dengan icon gambar generik): pakai border style sama (`border border-hairline`, bukan bg abu-abu solid), teks placeholder pakai `font-mono text-xs text-ink-muted uppercase`.

---

## 7. Tombol & Elemen Interaktif (untuk preset kualitas, tombol download, dst — belum kelihatan di screenshot tapi pasti dipakai)

```html
<!-- Primary button -->
<button
    class="bg-amber text-ink font-medium px-5 py-2.5 rounded-sm hover:bg-amber/90 transition-colors"
>
    Compress Sekarang
</button>

<!-- Preset selector (bukan rounded pill/tab bulat, pakai underline style) -->
<div class="flex border-b border-hairline">
    <button
        class="px-4 py-2 text-sm font-medium text-ink border-b-2 border-amber"
    >
        Sosial Media
    </button>
    <button class="px-4 py-2 text-sm text-ink-muted hover:text-ink">
        Website
    </button>
    <button class="px-4 py-2 text-sm text-ink-muted hover:text-ink">
        Custom
    </button>
</div>
```

Untuk angka hasil compress (ukuran file, persentase hemat) — **selalu** pakai `font-mono`, contoh:

```html
<span class="font-mono text-sm text-ink-muted line-through">2.4 MB</span>
<span class="font-mono text-sm font-medium text-ink">312 KB</span>
<span class="font-mono text-xs bg-amber/15 text-amber px-2 py-0.5 rounded-sm"
    >-86%</span
>
```

---

## 8. Konsistensi Spacing — Padding, Margin, Lebar Konten

**Masalah yang terlihat sekarang** (dari screenshot terbaru): setiap halaman punya lebar & padding konten yang beda-beda sendiri-sendiri. Halaman Dashboard kontennya lebar penuh, tapi halaman Compress Gambar cardnya nyempit di tengah dengan gutter kiri-kanan yang besar dan **tidak simetris** (kiri lebih lebar dari kanan). Ini yang bikin terasa berantakan/tidak profesional — bukan soal "kurang space", tapi soal **tidak konsisten** antar halaman.

**Solusi: satu wrapper konten yang dipakai ulang di SEMUA halaman dashboard**, jangan tiap halaman styling manual sendiri-sendiri.

### Wrapper utama — pasang di layout, bukan di tiap halaman

```html
<!-- resources/views/layouts/dashboard.blade.php -->
<main class="flex-1 overflow-y-auto">
    <div class="max-w-6xl px-8 py-8">{{ $slot }}</div>
</main>
```

Semua halaman (Dashboard, Compress Gambar, Riwayat, dst) render KONTEN-nya saja di dalam wrapper ini — jangan bikin `max-w-*` atau `px-*` sendiri-sendiri di masing-masing halaman. Ini yang menjamin lebar & gutter kiri-kanan selalu sama persis di semua halaman.

### Skala spacing baku — pakai angka ini konsisten, jangan asal pilih

| Elemen                                                              | Class                                                                        |
| ------------------------------------------------------------------- | ---------------------------------------------------------------------------- |
| Padding halaman (wrapper di atas)                                   | `px-8 py-8`                                                                  |
| Padding dalam card/panel                                            | `p-6` (bukan `p-8`/`p-10`/`p-12` beda-beda tiap card)                        |
| Jarak antar section (Stat cards → Akses Cepat → Aktivitas Terakhir) | `space-y-8` di container utama, konsisten, jangan margin manual tiap section |
| Jarak antar card dalam satu grid                                    | `gap-6`                                                                      |
| Jarak judul ke deskripsi dalam card                                 | `mb-1` (judul), `mb-6` (deskripsi sebelum konten berikutnya)                 |
| Padding tombol                                                      | `px-5 py-2.5` (button biasa), `px-4 py-2` (tab/preset selector)              |

### Perbaikan spesifik di stat card (Dashboard)

Sekarang icon kotak abu-abu "mengambang" jauh dari angka/label dengan jarak kosong berlebihan di tengah card. Perbaiki jadi:

```html
<div
    class="bg-white border border-hairline rounded-sm p-6 flex items-start justify-between"
>
    <div>
        <p class="text-sm text-ink-muted mb-2">Total File Diproses</p>
        <p class="font-display font-bold text-3xl text-ink">3</p>
    </div>
    <div
        class="w-9 h-9 rounded-sm bg-paper border border-hairline flex items-center justify-center text-ink-muted"
    >
        <!-- icon kecil, 16-18px -->
    </div>
</div>
```

Icon jadi kecil & nempel di pojok kanan atas card (`items-start`), bukan icon besar yang ditaruh sejajar vertikal di tengah dengan jarak kosong lebar.

### Aturan umum biar tidak "renggang berlebihan"

- **Jangan** pakai padding besar (`p-10`, `p-12`, `p-16`) di card biasa — itu cocok untuk hero section, bukan card data/dashboard. Maksimal `p-8` hanya untuk card konten utama (misal card "Compress Gambar" yang isinya form), card-card kecil (stat, tool shortcut) cukup `p-6`.
- **Jangan** biarkan tinggi header/topbar dan padding-top konten beda-beda antar halaman — topbar tingginya harus identik di semua halaman (`py-4` tetap), dan jarak topbar ke konten pertama juga sama (diatur dari `py-8` di wrapper utama, bukan margin manual per halaman).
- Cek ulang: apakah ada halaman yang pakai `container mx-auto` bawaan Tailwind sementara halaman lain pakai `max-w-6xl` custom? Samakan semua ke satu wrapper (lihat bagian atas section ini).

---

## 9. Checklist Verifikasi Setelah Restyle

- [ ] Tidak ada lagi warna indigo/ungu solid di sidebar — sudah jadi `bg-ink` (#1B2430)
- [ ] Tidak ada satupun `shadow-md`/`shadow-lg` yang tersisa di card — semua diganti `border border-hairline`
- [ ] Tidak ada `rounded-xl`/`rounded-2xl` di card & button — maksimal `rounded-sm`
- [ ] Semua angka (ukuran file, persentase, tanggal) memakai `font-mono`
- [ ] Judul halaman & nama tools memakai `font-display` (Space Grotesk), bukan font default
- [ ] Breadcrumb & label section ("TOOLS") memakai gaya monospace uppercase tracked
- [ ] Active nav item di sidebar pakai left-border accent, bukan rounded pill block
- [ ] Konsisten di SEMUA halaman (Dashboard, Riwayat, ketiga tools, Profil) — bukan cuma satu halaman
- [ ] **Semua halaman pakai satu wrapper konten yang sama** (lebar & padding identik) — dicek dengan buka Dashboard lalu Compress Gambar bergantian, lebar & gutter kiri-kanan harus sama persis
- [ ] Tidak ada card yang pakai padding besar (`p-10`+) kecuali card konten utama
- [ ] Icon di stat card kecil & nempel pojok, tidak mengambang dengan jarak kosong berlebihan
