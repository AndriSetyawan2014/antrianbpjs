# Antrian BPJS Online

Sistem pemantauan dan pengelolaan data bridging antrian BPJS Kesehatan berbasis web, dibangun dengan **Laravel 10.x**. Aplikasi ini mengelola pengiriman **Kode Booking**, **Task ID**, dan **Monitoring Kunjungan VClaim** untuk beberapa unit layanan Queen Latifa:

| Kode    | Unit                     |
| ------- | ------------------------ |
| `QLJ`   | Queen Latifa Yogyakarta  |
| `QLKP`  | Queen Latifa Kulon Progo |
| `QLTMG` | Queen Latifa Temanggung  |

**Fitur utama:**

- Monitoring data Kode Booking & Task ID per unit
- Rekap data dengan filter tanggal
- Export Excel
- Pengiriman otomatis ke BPJS Antrol API
- Dashboard ringkasan data per unit
- **[BARU] Monitoring Kunjungan Rawat Jalan via BPJS VClaim API** (multi-branch, dengan sync multi-tanggal)

---

## Persyaratan

- PHP >= 8.1
- Composer
- MySQL / MariaDB
- XAMPP / Laragon / server Linux dengan Apache/Nginx

---

## Cara Install

### 1. Clone repository

```bash
git clone https://github.com/AndriSetyawan2014/antrianbpjs.git
cd antrianbpjs
```

### 2. Install dependensi PHP

```bash
composer install
```

### 3. Salin file environment

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Konfigurasi `.env`

Edit file `.env` dan sesuaikan:

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=root
DB_PASSWORD=

# APP URL (sesuaikan dengan URL server produksi)
APP_URL=http://172.100.10.40/antrianonline/public

# Daftar cabang QL yang aktif
BPJS_AVAILABLE_URLQL="QLJ,QLKP,QLTMG"

# BPJS Antrol via SIRS (per cabang)
BPJS_ANTROL_SIRS_URL_QLJ=http://ip-sirs-qlj/sirstql/api/
BPJS_ANTROL_SIRS_CONS_ID=your_cons_id
BPJS_ANTROL_SIRS_SECRET=your_secret
BPJS_ANTROL_SIRS_JENIS_KONEKSI=VPN

# BPJS VClaim (per cabang)
BPJS_VCLAIM_BASE_URL=https://apijkn.bpjs-kesehatan.go.id/vclaim-rest

BPJS_VCLAIM_CONS_ID_QLJ=your_cons_id_qlj
BPJS_VCLAIM_SECRET_QLJ=your_secret_qlj
BPJS_VCLAIM_USERKEY_QLJ=your_userkey_qlj
BPJS_VCLAIM_KODEPPK_QLJ=0179R014

BPJS_VCLAIM_CONS_ID_QLKP=your_cons_id_qlkp
BPJS_VCLAIM_SECRET_QLKP=your_secret_qlkp
BPJS_VCLAIM_USERKEY_QLKP=your_userkey_qlkp
BPJS_VCLAIM_KODEPPK_QLKP=0176R009

BPJS_VCLAIM_CONS_ID_QLTMG=your_cons_id_qltmg
BPJS_VCLAIM_SECRET_QLTMG=your_secret_qltmg
BPJS_VCLAIM_USERKEY_QLTMG=your_userkey_qltmg
BPJS_VCLAIM_KODEPPK_QLTMG=0163R005
```

### 5. Jalankan migrasi database

```bash
php artisan migrate
```

### 6. (Opsional) Setel permission storage — khusus Linux

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## Cara Menjalankan

### Development (lokal dengan Laragon/XAMPP)

Pastikan Apache & MySQL sudah berjalan, lalu akses:

```
http://localhost/antrianbpjs/public
```

### Production (Apache dengan subfolder)

Jika aplikasi diakses via subfolder (misal `/antrianonline/public/`), pastikan:

1. `APP_URL` di `.env` sudah sesuai:
   ```env
   APP_URL=http://172.100.10.40/antrianonline/public
   ```

2. `public/.htaccess` menggunakan `RewriteBase` yang sesuai:
   ```apache
   RewriteBase /antrianonline/public/
   ```

3. Apache `Alias` di `sites-enabled/00-default.conf`:
   ```apache
   Alias /antrianonline/public "C:/laragon/www/antrianbpjs/public"
   <Directory "C:/laragon/www/antrianbpjs/public">
       AllowOverride All
       Require all granted
   </Directory>
   ```

4. Setelah konfigurasi, clear cache:
   ```bash
   php artisan config:clear
   php artisan view:clear
   ```

---

## Struktur Endpoint Web

| URL                                 | Keterangan                                        |
| ----------------------------------- | ------------------------------------------------- |
| `/dashboard`                        | Halaman utama ringkasan data                      |
| `/data_kodebooking`                 | Data Kode Booking QLJ                             |
| `/rekap_kodebooking`                | Rekap Kode Booking QLJ                            |
| `/TaskID`                           | Data Task ID QLJ                                  |
| `/qlkp_data_kodebooking`            | Data Kode Booking QLKP                            |
| `/qlkp_TaskID`                      | Data Task ID QLKP                                 |
| `/qltmg_data_kodebooking`           | Data Kode Booking QLTMG                           |
| `/qltmg_TaskID`                     | Data Task ID QLTMG                                |
| `/vclaim/kunjungan-rawat-jalan`     | **[BARU]** Monitoring Detail Kunjungan Rawat Jalan |
| `/vclaim/rekap-kunjungan-rawat-jalan` | **[BARU]** Rekap Kunjungan Rawat Jalan Bulanan   |

---

## Struktur Endpoint API (Sinkronisasi Antrol)

| Endpoint                                                              | Method    | Keterangan                                          |
| --------------------------------------------------------------------- | --------- | --------------------------------------------------- |
| `/api/taskid-by-ql?urlQL={KODE}&tanggal={YYYY-MM-DD}`                 | GET, POST | Sinkronisasi Task ID untuk satu unit (opsional tgl) |
| `/api/run-taskid-ql?urlQL={KODE}&tanggal={YYYY-MM-DD}`                | GET, POST | Eksekusi otomatis Task ID dengan filter tanggal     |
| `/api/taskid-all-ql`                                                 | GET       | Sinkronisasi Task ID Semua QL (pengaturan tgl di kode) |
| `/api/kodebooking-by-ql?urlQL={KODE}&tanggal={YYYY-MM-DD}`             | GET, POST | Sinkronisasi Kode Booking untuk satu unit (ops. tgl)|
| `/api/kodebooking-all-ql`                                            | GET       | Sinkronisasi Kode Booking Semua QL (pengaturan tgl di kode)|
| `/api/queue-status`                                                   | GET       | **[BARU]** Cek jumlah antrean yang tersisa di database |
| `/api/queue-work-start`                                               | GET       | **[BARU]** Jalankan Queue Worker via browser (Background) |
| `/api/queue-work-stop`                                                | GET       | **[BARU]** Hentikan Queue Worker via browser (Restart) |
| `/api/queue-clear`                                                    | GET       | **[BARU]** Hapus/Bersihkan semua antrean di database |



**Contoh Penggunaan (cURL):**

```bash
# Sinkronisasi Task ID untuk Queen Latifa Temanggung (Hari ini)
curl -X GET "http://localhost/antrianbpjs/public/api/taskid-by-ql?urlQL=QLTMG"

# Sinkronisasi Task ID untuk tanggal spesifik
curl -X GET "http://localhost/antrianbpjs/public/api/taskid-by-ql?urlQL=QLTMG&tanggal=2026-04-14"

# Eksekusi Otomatis Task ID untuk tanggal spesifik
curl -X GET "http://localhost/antrianbpjs/public/api/run-taskid-ql?urlQL=QLJ&tanggal=2026-04-21"

# Sinkronisasi Kode Booking untuk Queen Latifa Kulon Progo (Hari ini)
curl -X GET "http://localhost/antrianbpjs/public/api/kodebooking-by-ql?urlQL=QLKP"

# Sinkronisasi Kode Booking untuk tanggal spesifik
curl -X GET "http://localhost/antrianbpjs/public/api/kodebooking-by-ql?urlQL=QLKP&tanggal=2026-04-14"

# Sinkronisasi Semua Cabang (Batch)
# Pengaturan tanggal dilakukan di dalam TambahAntrianOnlineController.php
curl -X GET "http://localhost/antrianbpjs/public/api/kodebooking-all-ql"
```

---

## Konfigurasi Sinkronisasi Batch (All QL)

Untuk endpoint `/api/kodebooking-all-ql`, Anda dapat mengatur rentang tanggal pengambilan data secara internal di dalam file `app/Http/Controllers/Api/TambahAntrianOnlineController.php` pada fungsi `kodebooking_get()`:

```php
// --- Settingan Filter Tanggal (Edit di sini jika diperlukan) ---
$dari    = Carbon::now()->toDateString();    // Hari ini
$sampai  = Carbon::now()->addDay()->toDateString(); // Besok (Default)
// --------------------------------------------------------------
```

Ini memudahkan sinkronisasi otomatis via Cron Job tanpa perlu mengirimkan parameter dinamis.

---

---

## Optimasi Pengiriman Task ID (Background Queue)

Untuk menangani pengiriman data dalam jumlah besar (ribuan data) tanpa membuat dashboard melambat atau *timeout*, sistem kini menggunakan **Laravel Queue**. Data yang didata dari SIRS akan dimasukkan ke antrean dan dikirim ke BPJS oleh proses latar belakang (*background worker*).

### 1. Konfigurasi `.env`

Pastikan konfigurasi queue di file `.env` sudah menggunakan driver `database`:

```env
QUEUE_CONNECTION=database
```

### 2. Menjalankan Worker (WAJIB)

Agar data yang masuk antrean terkirim ke BPJS, Anda harus menjalankan perintah ini di terminal server (Laragon/XAMPP):

```bash
# Jalankan di terminal
php artisan queue:work --queue=default --sleep=3 --tries=3 --timeout=90
```

### 3. Pengiriman Paralel (Untuk Data > 5.000)

Jika data sangat banyak, Anda bisa menjalankan **beberapa worker sekaligus** untuk mempercepat pengiriman (Parallel Processing). Caranya:
- Buka 5 jendela terminal/CMD sekaligus.
- Jalankan perintah `php artisan queue:work ...` di setiap jendela tersebut.
- Kecepatan pengiriman akan meningkat secara linear (5 worker = 5x lebih cepat).

### 4. Memantau Antrean via Browser (Baru)

Anda bisa mengecek sisa antrean secara real-time melalui browser tanpa perlu membuka terminal:
- Akses URL: `http://[domain-anda]/api/queue-status`
- Data yang ditampilkan meliputi total antrean dan rincian per jalur (*queue*).

### 5. Mengontrol Worker via Web (Baru)

Sekarang Anda bisa menjalankan atau menghentikan pengiriman data langsung dari browser:
- **Jalankan Worker (Start):** Akses `/api/queue-work-start`. Sistem akan menjalankan worker di background.
- **Hentikan Worker (Stop):** Akses `/api/queue-work-stop`. Sistem akan mengirim sinyal berhenti ke semua worker.

### 6. Membersihkan Antrean via Web (Baru)

Jika antrean terlalu menumpuk atau data lama ingin dibatalkan:
- **Hapus Semua Antrean:** Akses `/api/queue-clear`.
- **Manual via Terminal:** Jalankan `php artisan queue:clear`.

---

## Manajemen Antrean (Queue Management)

Jika antrean pengiriman Task ID menumpuk atau perlu dibersihkan, gunakan perintah berikut di terminal server:

### 1. Cek Status Antrean
Menampilkan jumlah antrean yang tersisa dan total data yang sudah diproses:
- **Via Browser:** `http://[domain-anda]/api/queue-status`
- **Via API:** `GET /api/queue-status`

### 2. Menghentikan Worker
Jika worker sedang berjalan di terminal, tekan **`Ctrl + C`**. 
Atau untuk memberitahu worker berhenti setelah job selesai:
```bash
php artisan queue:restart
```

### 3. Membersihkan Antrean (Queue Clear)
Menghapus semua job yang ada di dalam antrean (Gunakan koneksi `database` sesuai setting `.env`):
```bash
# Menghapus queue sync_lokal
php artisan queue:clear database --queue=sync_lokal

# Menghapus queue default
php artisan queue:clear database --queue=default
```

### 4. Membersihkan Antrean Gagal (Queue Flush)
Menghapus semua daftar job yang gagal dikirim:
```bash
php artisan queue:flush
```

### 5. Reset Status di Database
Jika ingin membatalkan pengiriman data yang sudah terlanjur ditandai untuk dikirim ulang (reupload=1):
```bash
php artisan tinker --execute="DB::table('data_taskids')->where('reupload', 1)->update(['reupload' => 0])"
```

### 6. Sinkronisasi Spesifik (Per Kode Booking)
Jika Anda ingin memproses ulang pengiriman Task ID hanya untuk satu kode booking tertentu secara instan:

- **Via Browser/API:** 
  `GET /api/run-taskid-by-kodebooking?urlQL=[CABANG]&kodebooking=[NOMOR_BOOKING]`

**Contoh:**
`http://[domain]/api/run-taskid-by-kodebooking?urlQL=QLJ&kodebooking=20260428069021`

**Kelebihan Fitur Ini:**
1. **Prioritas Queue**: Data langsung dikirim ke background job untuk diproses.
2. **Aturan Sekuensial**: Sistem otomatis memastikan Task dikirim berurutan (misal: Task 6 harus menunggu Task 5 sukses).
3. **Efisien**: Hanya memproses kode booking yang diminta, sangat berguna untuk perbaikan data satuan.

---

## [BARU] Fitur VClaim — Monitoring Kunjungan Rawat Jalan

Fitur ini mengambil data kunjungan rawat jalan dari **BPJS VClaim API** dan menyimpannya ke database lokal untuk monitoring terpusat semua cabang QL.

> **Catatan Teknis:** Response API BPJS VClaim V2 dikembalikan dalam format **terenkripsi AES-256-CBC** dan **dikompres LZString**. Proses decrypt + decompress ditangani otomatis oleh `BpjsHelper::getVclaimDataKunjungan()` menggunakan library `nullpunkt/lz-string-php`.

### Sumber API BPJS VClaim

```
GET {BPJS_VCLAIM_BASE_URL}/Monitoring/Kunjungan/Tanggal/{tanggal}/JnsPelayanan/{jns}
```

- `JnsPelayanan = 2` → Rawat Jalan
- `JnsPelayanan = 1` → Rawat Inap

### Cara Menggunakan (via Web UI)

#### 1. Halaman Detail Kunjungan (`/vclaim/kunjungan-rawat-jalan`)
1. Buka halaman: `/vclaim/kunjungan-rawat-jalan`
2. Pilih **Tanggal Kunjungan** (default: hari ini)
3. Pilih **Cabang QL** — atau kosongkan untuk semua cabang sekaligus
4. Klik **Sync BPJS** → data diambil dari API VClaim lalu disimpan ke database
5. Tabel menampilkan: No SEP, No Kartu, Nama Pasien, Poli, Diagnosa ICD-10, Kelas Rawat, dan waktu sync

> Tombol **Sync BPJS** dapat digunakan berulang kali — data yang sudah ada akan di-update (`updateOrCreate` berdasarkan `kode_ql` + `no_sep`).

#### 2. Halaman Rekap Bulanan (`/vclaim/rekap-kunjungan-rawat-jalan`)
1. Buka menu **Monitoring Rawat Jalan** > **Rekap**
2. Pilih **Bulan** dan **Tahun** untuk melihat total kunjungan harian per cabang
3. Anda bisa mengeklik baris tanggal manapun pada tabel rekap untuk melompat langsung ke halaman detail kunjungan di tanggal tersebut.

### Sinkronisasi Otomatis (Cron Job)

Data kunjungan rawat jalan kini diatur agar tersinkronisasi secara otomatis ke DB Lokal setiap harinya pada pukul **23:45** untuk semua cabang.
Pastikan *cron job* dasar Laravel sudah terpasang di *server* produksi:
```bash
* * * * * cd /path/ke/folder/antrianbpjs && php artisan schedule:run >> /dev/null 2>&1
```
Atau Anda bisa memicu *job* sinkronisasi harian ini secara manual via terminal:
```bash
php artisan vclaim:sync-kunjungan-daily
```

### Endpoint API VClaim

| Endpoint | Method | Keterangan |
| --- | --- | --- |
| `/api/vclaim/sync-kunjungan-jalan` | GET, POST | **Sync satu tanggal** dari BPJS VClaim → simpan ke DB |
| `/api/vclaim/sync-kunjungan-jalan-range` | GET, POST | **Sync multi-tanggal** (date range) → simpan ke DB |
| `/api/vclaim/kunjungan-jalan` | GET | Baca data dari database lokal (JSON, paginasi) |
| `/api/vclaim/kunjungan-jalan-direct` | GET | Fetch langsung dari BPJS tanpa menyimpan ke DB |

---

### 1. Sync Satu Tanggal

```
GET /api/vclaim/sync-kunjungan-jalan
```

| Parameter | Wajib | Default | Keterangan |
| --- | --- | --- | --- |
| `tanggal` | ❌ | Hari ini | Format `YYYY-MM-DD` |
| `urlQL` | ❌ | Semua QL | Filter satu cabang: `QLJ`, `QLKP`, `QLTMG` |

**Contoh:**

```bash
# Sync semua cabang untuk hari ini
curl "http://172.100.10.40/antrianonline/public/api/vclaim/sync-kunjungan-jalan?tanggal=2026-04-21"

# Sync hanya QLJ
curl "http://172.100.10.40/antrianonline/public/api/vclaim/sync-kunjungan-jalan?tanggal=2026-04-21&urlQL=QLJ"
```

**Contoh Response:**

```json
{
  "metadata": { "code": 200, "message": "Proses sync selesai", "tanggal": "2026-04-21" },
  "response": {
    "QLJ":   { "status": "success", "message": "Berhasil sync 191 data kunjungan rawat jalan", "count": 191 },
    "QLKP":  { "status": "empty",   "message": "Tidak ada data kunjungan rawat jalan", "count": 0 },
    "QLTMG": { "status": "success", "message": "Berhasil sync 87 data kunjungan rawat jalan", "count": 87 }
  }
}
```

---

### 2. Sync Multi-Tanggal (Date Range)

```
GET /api/vclaim/sync-kunjungan-jalan-range
```

| Parameter | Wajib | Default | Keterangan |
| --- | --- | --- | --- |
| `tgl_mulai` | ✅ | — | Tanggal awal, format `YYYY-MM-DD` |
| `tgl_akhir` | ❌ | Hari ini | Tanggal akhir, format `YYYY-MM-DD` |
| `urlQL` | ❌ | Semua QL | Filter satu cabang: `QLJ`, `QLKP`, `QLTMG` |
| `limit_hari` | ❌ | `31` | Batas maksimal rentang (max `365`) |

> **Catatan:** Endpoint ini melakukan request ke BPJS VClaim sebanyak **`total_hari × jumlah_QL`** kali. Untuk rentang panjang, jalankan via cURL/Postman dengan timeout besar, bukan dari browser.

**Contoh:**

```bash
# Sync seluruh bulan April 2026, semua cabang (21 hari × 3 QL = 63 request)
curl --max-time 3600 \
  "http://172.100.10.40/antrianonline/public/api/vclaim/sync-kunjungan-jalan-range?tgl_mulai=2026-04-01"

# Sync 7 hari terakhir, semua cabang (tanpa parameter urlQL)
curl "http://172.100.10.40/antrianonline/public/api/vclaim/sync-kunjungan-jalan-range?tgl_mulai=2026-04-15&tgl_akhir=2026-04-21"

# Sync 7 hari terakhir, hanya QLJ (tambahkan &urlQL=QLJ)
curl "http://172.100.10.40/antrianonline/public/api/vclaim/sync-kunjungan-jalan-range?tgl_mulai=2026-04-15&tgl_akhir=2026-04-21&urlQL=QLJ"

# Sync 3 bulan (naikkan limit_hari), semua cabang
curl --max-time 7200 \
  "http://172.100.10.40/antrianonline/public/api/vclaim/sync-kunjungan-jalan-range?tgl_mulai=2026-02-01&limit_hari=90"
```

**Contoh Response:**

```json
{
  "metadata": { "code": 200, "message": "Sync range selesai" },
  "summary": {
    "tgl_mulai": "2026-04-01",
    "tgl_akhir": "2026-04-21",
    "total_hari": 21,
    "target_ql": ["QLJ", "QLKP", "QLTMG"],
    "total_records": 4032,
    "total_error": 0
  },
  "details": {
    "2026-04-01": {
      "QLJ":   { "status": "success", "count": 195 },
      "QLKP":  { "status": "success", "count": 32 },
      "QLTMG": { "status": "empty",   "count": 0 }
    },
    "2026-04-02": { "...": "..." }
  }
}
```

---

### 3. Baca Data dari DB Lokal

```
GET /api/vclaim/kunjungan-jalan
```

| Parameter | Keterangan |
| --- | --- |
| `tanggal` | Filter tanggal kunjungan |
| `urlQL` | Filter satu cabang |
| `per_page` | Jumlah data per halaman (default: 50) |
| `page` | Nomor halaman |

```bash
# Baca data dari DB lokal (50 per halaman, halaman 2)
curl "http://172.100.10.40/antrianonline/public/api/vclaim/kunjungan-jalan?tanggal=2026-04-21&urlQL=QLKP&per_page=50&page=2"
```

---

### Desain Database: `vclaim_kunjungan`

Satu tabel untuk semua cabang — dibedakan via kolom `kode_ql`:

| Kolom                 | Tipe         | Keterangan                                               |
| --------------------- | ------------ | -------------------------------------------------------- |
| `id`                  | bigint (PK)  | Auto-increment                                           |
| `kode_ql`             | varchar(10)  | `QLJ`, `QLKP`, `QLTMG`, dst                             |
| `kode_ppk`            | varchar(20)  | Kode PPK dari `BPJS_VCLAIM_KODEPPK_{kode_ql}`            |
| `tgl_kunjungan`       | date         | Tanggal parameter request API                            |
| `jns_pelayanan`       | tinyint      | `1` = Rawat Inap, `2` = Rawat Jalan                      |
| `no_sep`              | varchar(30)  | Nomor SEP (unik per cabang)                              |
| `no_kartu`            | varchar(20)  | Nomor kartu JKN                                          |
| `no_rujukan`          | varchar(30)  | Nomor rujukan                                            |
| `nama`                | varchar(100) | Nama peserta BPJS                                        |
| `diagnosa`            | varchar(20)  | Kode ICD-10                                              |
| `poli`                | varchar(50)  | Nama poli tujuan                                         |
| `kelas_rawat`         | varchar(5)   | `1`, `2`, atau `3`                                       |
| `jns_pelayanan_label` | varchar(20)  | Label dari API: `R.Jalan` / `R.Inap`                     |
| `tgl_sep`             | date         | Tanggal SEP diterbitkan                                  |
| `tgl_plg_sep`         | date         | Tanggal pulang (rawat inap)                              |
| `raw_response`        | json         | Raw data JSON record dari BPJS                           |
| `sync_status`         | varchar(20)  | `success` / `error` / `partial`                          |
| `sync_message`        | text         | Pesan error jika sync gagal                              |
| `synced_at`           | timestamp    | Waktu terakhir sync dari API BPJS                        |
| `created_at`          | timestamp    | Waktu record dibuat                                      |
| `updated_at`          | timestamp    | Waktu record diperbarui                                  |

**Index:** `(kode_ql, tgl_kunjungan, jns_pelayanan)`, `no_sep`, `no_kartu`  
**Unique:** `(kode_ql, no_sep)` — mencegah duplikasi per cabang

> **Menambah cabang baru:** Tambahkan variabel `.env` baru (`BPJS_VCLAIM_CONS_ID_QLBR`, dst) dan update `BPJS_AVAILABLE_URLQL`. Tidak perlu membuat tabel baru.

---

## Troubleshooting

### CSS / Asset 404 atau 403

Pastikan `APP_URL` di `.env` sesuai URL akses, `RewriteBase` di `public/.htaccess` benar, dan Apache Alias sudah dikonfigurasi. Lihat bagian **Production** di atas.

### Fatal error: Class "config" does not exist

```bash
find vendor -name "*.php" -empty -type f -delete
composer reinstall "*" --no-scripts
php artisan package:discover
```

### cURL timeout saat sync ke SIRSTQL

Timeout diset 120 detik. Jika server SIRSTQL tidak merespons, aplikasi log error dan return HTTP 503. Periksa konektivitas jaringan ke IP SIRSTQL.

### VClaim sync tidak mengembalikan data (status `empty`)

Periksa:
1. Konfigurasi `BPJS_VCLAIM_CONS_ID_*`, `BPJS_VCLAIM_SECRET_*`, `BPJS_VCLAIM_USERKEY_*` di `.env` sudah benar
2. Server dapat mengakses `apijkn.bpjs-kesehatan.go.id`
3. Tanggal yang diminta tidak melebihi batas retensi data BPJS
4. Cek log di `storage/logs/laravel.log` — cari baris `[BpjsHelper::getVclaimDataKunjungan]`

### VClaim sync timeout (cURL error 28)

Response BPJS VClaim untuk hari dengan banyak pasien bisa mencapai **18KB+**. HTTP timeout telah diset ke **120 detik**. Jika masih timeout:
- Pastikan koneksi server ke internet stabil
- Coba sync per cabang (`&urlQL=QLJ`) alih-alih semua sekaligus
- Untuk sync multi-tanggal, gunakan cURL dengan `--max-time 3600`

### VClaim sync multi-tanggal melebihi batas

Jika muncul error `422 Rentang tanggal melebihi batas X hari`, tambahkan parameter `limit_hari`:

```bash
# Sync 3 bulan
curl "...?tgl_mulai=2026-02-01&limit_hari=90"
```

---

## Lisensi

Dikembangkan oleh [@andrisetyawan](https://github.com/AndriSetyawan2014)
