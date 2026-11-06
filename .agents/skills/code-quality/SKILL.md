---
name: code-quality
description: >-
  Use this skill when editing, creating, or reviewing PHP files in the Antrian BPJS Online project. Enforces PHP syntax verification (php -l), coding standards, and quality gates to prevent syntax errors from reaching production.
---

# Code Quality — PHP Syntax & Standards

This skill enforces code quality gates for all PHP file changes in this project. Load this before editing any `.php` file.

## 🔴 WAJIB: Verifikasi Sintaks PHP (`php -l`)

Setiap kali Anda mengedit, membuat, atau memindahkan file `.php`, **harus** menjalankan:

```bash
php -l path/to/file.php
```

**Output yang diharapkan:**
```
No syntax errors detected in path/to/file.php
```

Jika ada error, **jangan** commit atau push. Perbaiki terlebih dahulu dan jalankan ulang `php -l` hingga bersih.

### Contoh workflow yang benar:

```powershell
# 1. Edit file (contoh via write/edit tool)
# 2. Verifikasi sintaks
php -l app/Http/Controllers/AntrianController.php

# 3. Jika ada error, perbaiki lalu ulangi
php -l app/Http/Controllers/AntrianController.php

# 4. Baru commit dan push
git add app/Http/Controllers/AntrianController.php
git commit -m "Fix: ..."
git push origin main
```

## 📋 Code Quality Checklist

- [ ] `php -l` menunjukkan `No syntax errors detected` untuk semua file yang diubah
- [ ] Tidak ada fitur bahasa PHP yang tidak didukung oleh versi server (cek `php -v` di server)
- [ ] Tidak ada variabel yang tidak digunakan yang menyebabkan warning
- [ ] Kode mengikuti PSR-4 autoloading (namespace sesuai dengan path file)
- [ ] Tidak ada hardcoded credentials (gunakan `.env`)
- [ ] Error handling dengan try-catch untuk panggilan eksternal (API, database, filesystem)
- [ ] Logging dengan `Log::info/error/warning` untuk traceability

## ⚠️ Perhatian: Perbedaan Versi PHP

Jika environment lokal menggunakan PHP 7.4 tetapi server produksi PHP 8.1+:

- Fitur seperti `match` expression, named arguments, attributes, dll **tidak bisa diverifikasi dengan `php -l` di lokal**
- Solusi: Verifikasi final harus dilakukan di server produksi atau menggunakan container Docker dengan PHP versi yang sama

## 🚫 Dilarang

- Commit file `.php` tanpa menjalankan `php -l` terlebih dahulu
- Push ke repository jika ada file yang gagal `php -l`
- Mengabaikan warning syntax dari output `php -l`

---

## 🔗 Skill Terkait

- `antrianbpjs-core` — Pola implementasi inti project ini
- `bpjs-api-integration` — Pemanggilan API BPJS
