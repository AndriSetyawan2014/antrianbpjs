# Antrian BPJS Online

Sistem pemantauan dan pengelolaan data bridging antrian BPJS Kesehatan berbasis web, dibangun dengan **Laravel 9.x**. Aplikasi ini mengelola pengiriman **Kode Booking** dan **Task ID** ke sistem BPJS Antrol untuk beberapa unit layanan Queen Latifa:

| Kode    | Unit                     |
| ------- | ------------------------ |
| `QLJ`   | Queen Latifa Yogyakarta  |
| `QLKP`  | Queen Latifa Kulon Progo |
| `QLTMG` | Queen Latifa Temanggung  |

Fitur utama:

- Monitoring data Kode Booking & Task ID per unit
- Rekap data dengan filter tanggal
- Export Excel
- Pengiriman otomatis ke BPJS Antrol API
- Dashboard ringkasan data per unit

---

## Persyaratan

- PHP >= 8.0
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

# BPJS Antrol - Yogyakarta (QLJ)
ANTROL_BASE_URL=https://api-url-qlj/
ANTROL_CONS_ID=your_cons_id
ANTROL_SECRET_KEY=your_secret_key
ANTROL_JENIS_KONEKSI=VPN

# BPJS Antrol - Kulon Progo (QLKP)
ANTROL_BASE_URL_QLKP=https://api-url-qlkp/

# BPJS Antrol - Temanggung (QLTMG)
ANTROL_BASE_URL_QLTMG=https://api-url-qltmg/
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

### Development (lokal dengan XAMPP)

Pastikan Apache & MySQL sudah berjalan di XAMPP, lalu akses:

```
http://localhost/antrianbpjs/public
```

Atau gunakan built-in Laravel server:

```bash
php artisan serve
```

Akses di: `http://127.0.0.1:8000`

### Production (Linux Server)

1. Upload file ke server (via SFTP/Git)
2. Jalankan `composer install --no-dev --optimize-autoloader`
3. Pastikan `APP_ENV=production` dan `APP_DEBUG=false` di `.env`
4. Jalankan `php artisan config:cache && php artisan route:cache`
5. Set document root ke folder `public/`

---

## Struktur Endpoint Web

| URL                       | Keterangan                   |
| ------------------------- | ---------------------------- |
| `/dashboard`              | Halaman utama ringkasan data |
| `/data_kodebooking`       | Data Kode Booking QLJ        |
| `/rekap_kodebooking`      | Rekap Kode Booking QLJ       |
| `/TaskID`                 | Data Task ID QLJ             |
| `/qlkp_data_kodebooking`  | Data Kode Booking QLKP       |
| `/qlkp_TaskID`            | Data Task ID QLKP            |
| `/qltmg_data_kodebooking` | Data Kode Booking QLTMG      |
| `/qltmg_TaskID`           | Data Task ID QLTMG           |

---

## Lisensi

Dikembangkan oleh [@andrisetyawan](https://github.com/AndriSetyawan2014)
