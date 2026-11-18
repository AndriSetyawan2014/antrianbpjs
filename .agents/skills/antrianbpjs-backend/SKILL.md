---
name: antrianbpjs-backend
description: >-
  Panduan arsitektur backend Laravel 10, controller API vs Web, routing patterns, error handling, background jobs, queue worker, dan validasi data pada Antrian BPJS Online. Gunakan skill ini saat membuat atau mengubah controller, route, request validation, job, command, atau logic bisnis backend.
---

# Antrian BPJS Online — Backend Architecture & Guidelines

Dokumen ini adalah pedoman implementasi backend untuk Controller, Routing, API, Jobs, dan Command di Laravel 10.

---

## 1. Pemisahan Controller: Web vs API

Aplikasi memisahkan logic penampil antarmuka (Web) dan logic pertukaran data (API):

### A. Web Controllers (`app/Http/Controllers/`)
- `AntrianController.php`: Controller utama untuk melayani seluruh halaman web (Dashboard, Task ID, Kode Booking, Rekap, Kesesuaian Jadwal) untuk cabang QLJ, QLKP, dan QLTMG.
- `AuthController.php`: Otentikasi sesi pengguna (login & logout).
- `WABlastController.php`: Penampil halaman form WhatsApp blast.

### B. API Controllers (`app/Http/Controllers/Api/`)
- `PengirimanTaskIDController.php`: Menangani penerimaan dan pengiriman Task ID (1-7, 99) dari SIMRS ke BPJS Antrol.
- `TambahAntrianOnlineController.php`: Menangani registrasi kode booking / antrean baru ke BPJS Antrol.
- `VclaimController.php`: Menangani sinkronisasi dan pencarian data VClaim (Kunjungan, Peserta, Rujukan, SEP).
- `WABlastControllerapi.php`: Menangani pengiriman pesan WhatsApp via API gateway.

---

## 2. Standar Respons API (Wajib)

Semua endpoint API (`routes/api.php`) WAJIB mengembalikan format JSON konsisten:

```json
{
  "metadata": {
    "code": 200,
    "message": "Deskripsi status sukses atau alasan error"
  },
  "response": {
    // Array atau objek data. Kirim [] atau null jika error/tidak ada data.
  }
}
```

Contoh implementasi controller:
```php
return response()->json([
    'metadata' => [
        'code'    => 200,
        'message' => 'Data berhasil diproses'
    ],
    'response' => $data
], 200);
```

Untuk response error (misal 400 atau 404):
```php
return response()->json([
    'metadata' => [
        'code'    => 404,
        'message' => 'Data tidak ditemukan'
    ],
    'response' => null
], 404);
```

---

## 3. Kompatibilitas Versi PHP

- Host development terkadang memiliki PHP CLI versi lokal (misal PHP 7.4) sementara server target menggunakan PHP 8.1+.
- **PENTING**:
  - Hindari penggunaan sintaks modern yang tidak backward-compatible pada controller inti jika CLI lokal belum di-upgrade, seperti sintaks `match (...)` (gunakan `switch` atau `if / elseif / else`).
  - Selalu verifikasi sintaks setiap perubahan PHP dengan:
    ```bash
    php -l path/to/file.php
    ```

---

## 4. Background Job & Antrian (Queue Processing)

- **Koneksi**: `database` (tabel `jobs` dan `failed_jobs`).
- **Pekerjaan Berat**: Operasi pengiriman batch ratusan data ke BPJS atau sinkronisasi harian **TIDAK BOLEH** dijalankan langsung secara sinkron di web request, melainkan didispatch ke Queue:
  - `SendTaskIdBatchJob`: Pengiriman Task ID berulang / antrian banyak.
  - `SendKodeBookingBatchJob`: Pengiriman antrean kode booking.
  - `SyncVclaimKunjunganJob`: Sinkronisasi kunjungan VClaim.
  - `SyncAntreanPerTanggalJob`: Sinkronisasi antrol per tanggal.

### Pemanggilan Job:
```php
SendTaskIdBatchJob::dispatch($branchCode, $date);
```

---

## 5. Logging & Error Handling

- Gunakan `Log::info()`, `Log::warning()`, atau `Log::error()` dengan konteks yang jelas:
  ```php
  Log::channel('daily')->error("[TaskID][{$branch}] Gagal kirim Task ID {$taskId}: " . $exception->getMessage(), [
      'kodebooking' => $kodebooking,
      'payload'     => $payload
  ]);
  ```
- Selalu tangani exception dari API eksternal BPJS dengan `try-catch` agar tidak menyebabkan HTTP 500 fatal tanpa log.
