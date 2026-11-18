---
name: antrianbpjs-database
description: >-
  Panduan struktur database, tabel per cabang, indexing performa, audit trail (created_by/updated_by), dan query patterns (upsert, dynamic table binding) pada project Antrian BPJS Online. Gunakan skill ini untuk operasi database, migration, query optimization, dan pemodelan data.
---

# Antrian BPJS Online — Database Architecture & Patterns

Dokumen ini memetakan seluruh tabel, struktur kolom, dan pola akses data untuk menghindari kesalahan penulisan query atau migration yang tidak sinkron antar cabang.

---

## 1. Pembagian Tabel: Multi-Table vs Single-Table

Di project ini terdapat dua pendekatan arsitektur penyimpanan:

### A. Pola Multi-Table per Cabang (Antrol: Kode Booking & Task ID)
Karena volume data antrol harian yang tinggi dan independensi masing-masing RS:
- **Yogyakarta (QLJ)**:
  - `data_kodebookings`
  - `data_taskids`
- **Kulon Progo (QLKP)**:
  - `qlkp_data_kodebookings`
  - `qlkp_data_taskids`
- **Temanggung (QLTMG)**:
  - `qltmg_data_kodebookings`
  - `qltmg_data_taskids`

### B. Pola Single-Table Terpusat (VClaim & System Logs)
Menggunakan satu tabel dengan kolom pembeda `kode_ql` (`QLJ`, `QLKP`, `QLTMG`):
- `vclaim_kunjungan` (data kunjungan & SEP VClaim)
- `vclaim_sync_logs` (log sinkronisasi VClaim)
- `antrean_per_tanggal_logs`
- `antrean_per_tanggal_sync_logs`
- `jobs` & `failed_jobs` (antrian background queue)
- `users` (otentikasi aplikasi)

---

## 2. Struktur Kunci Tabel Task ID

Tabel `data_taskids`, `qlkp_data_taskids`, dan `qltmg_data_taskids` memiliki skema yang identik:

| Kolom | Tipe Data | Deskripsi |
| :--- | :--- | :--- |
| `id` | bigint (PK, auto-increment) | ID baris |
| `kodebooking` | varchar(255), Indexed | Kode booking antrian |
| `taskid` | int, Indexed | Nilai Task ID (1, 2, 3, 4, 5, 6, 7, 99) |
| `waktu` | bigint | Waktu kejadian dalam milidetik (timestamp * 1000) |
| `tanggal` | date, Indexed | Tanggal pelayanan (YYYY-MM-DD) |
| `jam` | time | Jam pelayanan (HH:MM:SS) |
| `code` | int / varchar(10) | HTTP status response BPJS (200, 201, 208, 500) |
| `message` | text / varchar | Pesan response dari BPJS (sukses/gagal) |
| `request` | longtext | JSON payload yang dikirim ke BPJS |
| `response` | longtext | JSON respons lengkap yang diterima dari BPJS |
| `idpendaftaran` | varchar(255), nullable | ID pendaftaran lokal dari SIMRS |
| `norm` | varchar(50), nullable | Nomor Rekam Medis pasien |
| `reupload` | int (default 0) | Flag / counter pengiriman ulang |
| `created_by` | varchar(100), nullable | Petugas/sistem yang membuat data |
| `updated_by` | varchar(100), nullable | Petugas/sistem yang mengupdate data |
| `created_at` / `updated_at` | timestamp | Timestamp bawaan Laravel |

> **Unique Index Constraint:**
> `unique_taskid_kodebooking` pada `(kodebooking, taskid)` untuk mencegah duplikasi pengiriman milestone yang sama.

---

## 3. Dynamic Model Binding Pattern (`data_taskid.php`)

Model `App\Models\data_taskid` dapat berpindah tabel secara dinamis tergantung cabang:

```php
// Menggunakan tabel default (Jogja: data_taskids)
$task = data_taskid::where('kodebooking', $kodebooking)->first();

// Mengganti tabel secara dinamis ke cabang lain:
$taskModel = new data_taskid();
$taskModel->setTable('qlkp_data_taskids');
$results = $taskModel->where('tanggal', $tgl)->get();

// Atau menggunakan query builder langsung:
DB::table('qltmg_data_taskids')->where(...)->get();
```

---

## 4. Struktur Kunci Tabel VClaim Kunjungan

Tabel `vclaim_kunjungan`:
- **Unique Key**: `(kode_ql, no_sep)`
- **Index Penting**:
  - `(kode_ql, tgl_kunjungan, jns_pelayanan)`
  - `(kode_ql, tgl_sep)`
  - `(no_kartu)`
- **Bulk Insert / Update Pattern**:
  Gunakan `upsert` untuk mencegah N+1 query:
  ```php
  VclaimKunjungan::upsert(
      $batchData,
      ['kode_ql', 'no_sep'], // Unique keys
      ['nama', 'diagnosa', 'poli', 'tgl_sep', 'tgl_pulang', 'updated_at'] // Kolom yang di-update
  );
  ```

---

## 5. Standar Indexing & Performa Query

1. **Pencarian Rentang Tanggal**: Selalu manfaatkan kolom `tanggal` yang sudah diindeks.
2. **Hindari Query SELECT * pada Longtext**:
   Saat menampilkan tabel list/datatable, **JANGAN** lakukan `SELECT *` jika ada kolom `request` dan `response` yang berisi JSON besar. Cukup ambil kolom metadata (`id`, `kodebooking`, `taskid`, `tanggal`, `jam`, `code`, `message`, `reupload`). Ambil `request` & `response` hanya saat user membuka modal detail JSON!
3. **Audit Trail Helper**:
   Model `data_taskid::ensureAuditColumnsExist()` memastikan kolom `created_by` dan `updated_by` otomatis terpasang jika belum ada.
