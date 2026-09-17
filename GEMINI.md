# Antrian BPJS Online - Project Rules

This document outlines the core architecture and operational rules for the "Antrian BPJS Online" project. **Always refer to these constraints when modifying or analyzing code in this repository.**

## Technology Stack
- **Framework**: Laravel 10.x
- **Language**: PHP >= 8.1
- **Database**: MySQL / MariaDB (Driver `mysql`)
- **Queue System**: Laravel Queue (Database driver)

## Core Architecture
This system is an integration hub connecting internal SIMRS (Sistem Informasi Manajemen Rumah Sakit) with BPJS APIs:
1. **BPJS Antrol API**: Sinkronisasi Kode Booking dan Task ID antrian pelayanan.
2. **BPJS VClaim API**: Monitoring Kunjungan Rawat Jalan.

Sistem ini mendukung **Multi-Cabang (Multi-Branch)** untuk unit layanan Queen Latifa:
- `QLJ`: Queen Latifa Yogyakarta
- `QLKP`: Queen Latifa Kulon Progo
- `QLTMG`: Queen Latifa Temanggung

## Coding & Operational Constraints
- **Queue Workers**: Pengiriman data dalam jumlah besar (seperti Task ID) HARUS menggunakan background queue (`php artisan queue:work`) agar tidak menyebabkan timeout pada dashboard.
- **VClaim Security**: Response dari BPJS VClaim V2 dienkripsi (AES-256-CBC) dan dikompres (LZString). Pemrosesan dekripsi dilakukan melalui `BpjsHelper::getVclaimDataKunjungan()` (library `nullpunkt/lz-string-php`).
- **Environment Variables**: Akses ke API (URL, Cons ID, Secret, UserKey, Kode PPK) dikonfigurasi per cabang di file `.env`. Jangan men-hardcode credential di dalam kode aplikasi.

## Important Endpoints (Quick Reference)
- Background Queue Status: `GET /api/queue-status`
- Start/Stop Queue Worker: `GET /api/queue-work-start`, `GET /api/queue-work-stop`
- Clear Queue: `GET /api/queue-clear`
- VClaim Sync (Daily): `php artisan vclaim:sync-kunjungan-daily` atau `GET /api/vclaim/sync-kunjungan-jalan`

## Frontend Standards (UI/UX)
- **Framework & Styling**: Proyek ini menggunakan **AdminLTE** (Bootstrap-based) untuk antarmuka pengguna.
- **Components**: Selalu gunakan komponen standar AdminLTE (seperti `card`, `info-box`, `sidebar`, `datatable`) saat membuat atau memodifikasi tampilan Blade baru agar desain tetap konsisten.
- **Asset Compilation**: Proyek ini menggunakan **Vite**. Jika Anda diminta memodifikasi file JavaScript atau CSS kustom di `resources/`, pastikan untuk mengingatkan atau menjalankan perintah `npm run build` jika diperlukan untuk mode produksi (atau `npm run dev` untuk development).

## API Development Standards
Ketika membuat endpoint API baru di `routes/api.php` atau di dalam controller, patuhi standar format respons JSON berikut agar konsisten dengan endpoint yang sudah ada:

```json
{
  "metadata": {
    "code": 200,
    "message": "Pesan sukses atau error"
  },
  "response": {
    // Data hasil query atau array kosong jika tidak ada data/error
  }
}
```
- **Error Handling**: Gunakan `code` HTTP yang sesuai (misal 400, 404, 500) di dalam `metadata.code`. Jika terjadi error, `response` dapat dikembalikan kosong atau berisi detail error.
