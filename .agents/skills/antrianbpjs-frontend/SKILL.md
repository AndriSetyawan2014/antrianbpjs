---
name: antrianbpjs-frontend
description: >-
  Panduan standar frontend AdminLTE, struktur layout Blade, sistem Top Header (Opsi B), dynamic branch badges, reusable DRY partials, dan kompilasi Vite di project Antrian BPJS Online. Gunakan skill ini saat membuat atau mengubah halaman Blade, komponen UI, tabel, CSS, atau JavaScript.
---

# Antrian BPJS Online — Frontend Standards & Architecture

Dokumen ini adalah acuan resmi UI/UX, tata letak Blade, dan arsitektur frontend proyek Antrian BPJS Online.

---

## 1. Arsitektur Layout Utama (`resources/views/layouts/admin.blade.php`)

Aplikasi menggunakan layout tunggal berbasis **AdminLTE** + **Bootstrap 5** dengan konsep modern:

### A. Top Header Standard (Opsi B)
- **Top Header Navbar** (`<header class="top-header">`) memuat:
  1. Tombol hamburger sidebar `#btnSidebarToggle`.
  2. Judul sistem: `<h1 class="system-title"><i class="fas fa-hospital me-2"></i><span>Antrian BPJS Online</span>...</h1>`.
  3. **Badge Cabang Dinamis**: Ditentukan otomatis berdasarkan URL rute aktif:
     - `QLJ` (Yogyakarta): Warna Primary Badge.
     - `QLKP` (Kulon Progo): Warna Primary Badge.
     - `QLTMG` (Temanggung): Warna Primary Badge.
     - General/VClaim/Setting: Queen Latifa.
  4. **Judul Halaman (`@yield('page_title')`)**: Diposisikan di samping badge cabang dengan separator garis vertikal `border-start border-2 border-primary`.
  5. Bagian Kanan: Jam realtime (`#headerDateTime`), info user login, dan tombol logout.
- **Content Area Bersih**: Tidak lagi menggunakan banner `.page-header` ganda di dalam wrapper body agar halaman langsung menampilkan filter card & data table secara leluasa (Opsi B).

### B. Aturan Penulisan `@section('page_title')` di View / Partial
**JANGAN gunakan tag `<h1>` di dalam `@section('page_title')`**, karena akan merusak line-height dan tinggi topbar.
Format yang benar:
```blade
@section('page_title')
    <i class="fas fa-calendar-check me-2 text-primary"></i>{{ $pageTitle }}
@endsection

@section('page_subtitle')
    Deskripsi singkat halaman
@endsection
```

---

## 2. Reusable DRY Partials (`resources/views/partials/`)

Untuk menghindari duplikasi kode antar 3 cabang, tampilan dibuat terpusat menggunakan Blade Partial:

| File Partial | Digunakan Oleh Halaman | Kegunaan |
| :--- | :--- | :--- |
| `taskid-page.blade.php` | Jogja, QLKP, QLTMG Task ID | Monitoring data Task ID, modal detail payload JSON, tombol re-upload |
| `kodebooking-page.blade.php` | Jogja, QLKP, QLTMG Kode Booking | Monitoring kode booking, status verifikasi BPJS |
| `rekap-taskid-page.blade.php` | Jogja, QLKP, QLTMG Rekap Task ID | Agregasi statistik task ID per status code & message |
| `rekap-kodebooking-page.blade.php`| Jogja, QLKP, QLTMG Rekap Booking | Rekap harian pendaftaran pasien mode ringkas & detail |
| `kesesuaian-jadwal-page.blade.php`| Jogja, QLKP, QLTMG Kesesuaian | Perbandingan jadwal praktik dokter HFIS vs Task ID 4 |
| `dashboard-region.blade.php`| Dashboard utama per wilayah | Kartu summary metrik per cabang |

### Pola Pemanggilan Partial di View Induk:
```blade
@extends('layouts.admin')

@section('content')
    @include('partials.taskid-page', [
        'pageTitle'    => 'Data Task ID - Kulon Progo',
        'filterAction' => route('qlkp.taskid.filter'),
        'items'        => $dataTaskid,
        'urlQL'        => 'QLKP',
        ...
    ])
@endsection
```

---

## 3. Komponen UI Standar

1. **Card Filter (`.filter-card` / `.filter-box`)**:
   - Gunakan form horizontal rapi dengan tombol aksi (Filter, Reset, Export Excel).
2. **Badge Status Response BPJS**:
   - `Code 200`: `<span class="badge bg-success">200 - Sukses</span>`
   - `Code 201/208`: `<span class="badge bg-info">208 - Sudah Terkirim</span>`
   - `Code 500 / Error`: `<span class="badge bg-danger">500 - Error</span>`
3. **Modal JSON Viewer**:
   - Gunakan format pre-formatted code block: `<pre class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;"><code>...</code></pre>`.
4. **DataTables**:
   - Gunakan inisialisasi standar AdminLTE dengan pagination, pencarian, dan scroll horizontal jika kolom lebar.

---

## 4. Asset Compilation (Vite)

- Konfigurasi aset ada di `vite.config.js`.
- File sumber CSS: `resources/css/` (`variables.css`, `app.css`, `index.css`, `vclaim.css`).
- File sumber JS: `resources/js/app.js`.
- Jalankan `npm run build` setelah mengubah file CSS atau JS pada `resources/`.
