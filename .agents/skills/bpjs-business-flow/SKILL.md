---
name: bpjs-business-flow
description: >-
  Alur bisnis inti sistem integrasi Antrian BPJS Online (SIMRS - BPJS Antrol & VClaim). Meliputi alur Task ID (1 s/d 7, 99), siklus Kode Booking, VClaim kunjungan & SEP, kesesuaian jadwal dokter, logic multi-cabang (QLJ, QLKP, QLTMG), serta trigger otomatis vs manual. Gunakan skill ini untuk memahami alur kerja sistem tanpa membaca controller beribu baris.
---

# Alur Bisnis Sistem Antrian BPJS Online

Sistem ini berfungsi sebagai **Integration & Monitoring Hub** antara SIMRS internal Rumah Sakit Queen Latifa (3 cabang: QLJ, QLKP, QLTMG) dengan API BPJS Kesehatan (Antrol SIRSTQL & VClaim).

---

## 1. Tiga Cabang Unit Layanan (Multi-Branch)

Setiap unit rumah sakit memiliki koneksi database tabel lokal dan konfigurasi API BPJS tersendiri:

| Kode Cabang | Nama Cabang | Tabel Kode Booking | Tabel Task ID | Endpoint / Prefix Web |
| :--- | :--- | :--- | :--- | :--- |
| **QLJ** | Queen Latifa Yogyakarta | `data_kodebookings` | `data_taskids` | `/TaskID`, `/data_kodebooking_jogja`, `/kesesuaian_jadwal` |
| **QLKP** | Queen Latifa Kulon Progo | `qlkp_data_kodebookings` | `qlkp_data_taskids` | `/qlkp_taskid`, `/qlkp_data_kodebooking`, `/qlkp_kesesuaian_jadwal` |
| **QLTMG** | Queen Latifa Temanggung | `qltmg_data_kodebookings` | `qltmg_data_taskids` | `/qltmg_taskid`, `/qltmg_data_kodebooking`, `/qltmg_kesesuaian_jadwal` |

*Catatan untuk VClaim:* Disimpan dalam 1 tabel terpusat `vclaim_kunjungan` dengan kolom pembeda `kode_ql`.

---

## 2. Alur Task ID (Milestone Pelayanan Pasien)

Task ID merepresentasikan tahapan waktu pelayanan pasien dari check-in antrian hingga penyerahan obat:

| Task ID | Nama Milestone | Waktu / Trigger Kejadian |
| :---: | :--- | :--- |
| **1** | Mulai Antre Admisi | Pasien mengambil nomor antrean di mesin anjungan / mulai tunggu admisi |
| **2** | Pelayanan Admisi | Petugas pendaftaran mulai melayani pasien di loket |
| **3** | Selesai Admisi / Tunggu Poli | Pendaftaran admisi selesai, pasien diarahkan menunggu di ruang tunggu poli |
| **4** | Mulai Pelayanan Dokter | Dokter menekan panggil antrean / mulai periksa pasien di ruang poli |
| **5** | Selesai Pelayanan Poli | Dokter selesai input resume medis / klik selesai pemeriksaan poli |
| **6** | Mulai Antre Farmasi | Resep masuk antrean depo farmasi / peracikan obat dimulai |
| **7** | Selesai Farmasi / Selesai Pelayanan | Pasien menerima obat, selesai seluruh rangkaian pelayanan |
| **99** | Pembatalan Antrean | Pasien membatalkan janji atau poli tutup / tidak datang |

### Logic Pengiriman Task ID:
1. **Trigger Otomatis**: SIMRS lokal memanggil endpoint webhook API internal:
   - `POST /api/taskid` (Jogja)
   - `POST /api/qlkp/taskid` (Kulon Progo)
   - `POST /api/qltmg/taskid` (Temanggung)
2. **Controller Penangan**: `App\Http\Controllers\Api\PengirimanTaskIDController`
3. **Penyimpanan Lokal**: Disimpan atau diperbarui di tabel task ID masing-masing cabang (`data_taskids`, `qlkp_data_taskids`, `qltmg_data_taskids`).
4. **Validasi & Pengiriman ke BPJS**:
   - Jika dikirim secara live: dikirim langsung via HTTP API Antrol (`/antrean/updatewaktu`).
   - Jika menggunakan background queue / batch: diproses melalui job `App\Jobs\SendTaskIdBatchJob`.
   - Kode respons BPJS disimpan di kolom `code` (200 = sukses) dan `message` untuk audit rekap.

---

## 3. Alur Kode Booking (Pendaftaran Antrean)

1. **Sumber Data**: Berasal dari pendaftaran Online (Mobile JKN, Web RS, atau SIMRS).
2. **Sinkronisasi**:
   - SIMRS mengirim payload ke `POST /api/antrean/add` atau `TambahAntrianOnlineController`.
   - Dapat ditarik via command berkala: `FetchPendingData` (`php artisan fetch:pending-data`).
3. **Data Lokal**: Disimpan di `data_kodebookings`, `qlkp_data_kodebookings`, atau `qltmg_data_kodebookings`.
4. **Status Booking**: Kolom `status` atau `code` mencatat keberhasilan registrasi antrean di BPJS Antrol.

---

## 4. Alur BPJS VClaim (Kunjungan & SEP Rawat Jalan)

1. **Tujuan**: Memonitoring seluruh penerbitan SEP (Surat Eligibilitas Peserta) dan kunjungan harian yang tercatat di server BPJS VClaim.
2. **Proses Sinkronisasi**:
   - Terjadwal via cron daily: `php artisan vclaim:sync-kunjungan-daily` (`SyncVclaimDaily`).
   - Trigger manual lewat tombol UI atau endpoint: `GET /api/vclaim/sync-kunjungan-jalan?kode_ql=...`
3. **Job Worker**: `App\Jobs\SyncVclaimKunjunganJob` memanggil `BpjsHelper::getVclaimDataKunjungan()` (mendekripsi data AES-256 + LZString).
4. **Penyimpanan**: Disimpan di tabel `vclaim_kunjungan` menggunakan `upsert` berdasarkan unique key `(kode_ql, no_sep)` dan dicatat log-nya di `vclaim_sync_logs`.

---

## 5. Alur Kesesuaian Jadwal Dokter

1. **Tujuan**: Membandingkan jam praktik dokter yang terdaftar di HFIS BPJS (`BpjsHelper::getRequest('/jadwaldokter/...')`) dengan jam aktual dokter mulai melayani pasien (Task ID 4).
2. **Perhitungan Status**:
   - **Sesuai**: Jam pelayanan dokter berada dalam toleransi rentang jadwal HFIS.
   - **Tidak Sesuai / Terlambat**: Jam pelayanan melewati batas toleransi mulai buka poli.
3. **Halaman Monitoring**:
   - Yogyakarta: `/kesesuaian_jadwal`
   - Kulon Progo: `/qlkp_kesesuaian_jadwal`
   - Temanggung: `/qltmg_kesesuaian_jadwal`
   - Render menggunakan partial DRY: `partials/kesesuaian-jadwal-page.blade.php`.
