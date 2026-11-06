---
name: blade-linter
description: >-
  Gunakan skill ini ketika membuat, mengedit, mereview, atau mendiagnosis error pada file Blade template (.blade.php) di project Antrian BPJS Online. Memeriksa dan memperbaiki secara otomatis kesalahan sintaks Blade yang sering memicu error produksi seperti unescaped comment braces ({@-- atau {--), mismatch directive (@endpushOnce tanpa @pushOnce), atau evaluasi directive liar di dalam komentar.
---

# Blade Template Linter & Auto-Fixer

Skill ini dibuat khusus untuk mencegah dan memperbaiki kesalahan sintaks pada file Blade template (`resources/views/**/*.blade.php`).

## 🛑 Akar Masalah yang Sangat Sering Terjadi

1. **Tag Komentar Non-Standar `{@--` dan `--@}`**:
   - Blade hanya mengenali `{{--` dan `--}}` sebagai komentar.
   - Jika ditulis `{@-- ... --@}`, Blade mengira teks di dalamnya adalah kode PHP/Blade aktif. Direktif seperti `@section`, `@push`, atau `@vite` di dalam komentar tersebut akan dieksekusi oleh compiler Blade!
   - Dampak:
     - `Undefined property: Illuminate\View\Factory::$startSection` (karena `@section` di dalam komentar dieksekusi tanpa parameter).
     - `Too few arguments to function Illuminate\Foundation\Vite::__invoke()` (karena kata `@vite` di dalam komentar dieksekusi sebagai fungsi).

2. **Kurung Kurawal Ganjil `{--` dan `--}`**:
   - Kurang 1 kurung kurawal pembuka atau penutup membuat string komentar dianggap ekspresi Blade yang bocor.

3. **Mismatch `@endpushOnce` vs `@pushOnce`**:
   - Blok dibuka dengan `@push('styles')`, tetapi ditutup dengan `@endpushOnce`.
   - Compiler Blade mengompilasi `@endpushOnce` menjadi statement `endif;` internal, memicu error:
     `syntax error, unexpected token "endif", expecting end of file`.

---

## ⚡ Perbaikan Cepat (1 Perintah)

Kapan pun mengedit Blade atau setelah terjadi error ViewException, jalankan script pemeriksa & perbaikan otomatis ini:

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File .agents/skills/blade-linter/scripts/fix-blade-syntax.ps1
```

Untuk mode hanya cek (tanpa mengubah file):
```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File .agents/skills/blade-linter/scripts/fix-blade-syntax.ps1 -CheckOnly
```

---

## 📋 Checklist Validasi Blade Sebelum Commit/Push

Sebelum melakukan commit atau push file `.blade.php`:

- [ ] Jalankan script pemindai sintaks Blade:
  `powershell.exe -NoProfile -ExecutionPolicy Bypass -File .agents/skills/blade-linter/scripts/fix-blade-syntax.ps1 -CheckOnly`
- [ ] Pastikan semua komentar menggunakan format resmi:
  ```blade
  {{-- Komentar valid di sini --}}
  ```
- [ ] Pastikan pasangan directive konsisten:
  - `@push('styles')` ➔ `@endpush`
  - `@pushOnce('styles')` ➔ `@endpushOnce`
  - `@section('content')` ➔ `@endsection`
  - `@if(...)` ➔ `@endif`
- [ ] Tidak menulis direktif Blade (misal `@section`, `@vite`) di dalam teks bebas tanpa dibungkus `{{-- ... --}}`.

---

## 🔄 Pembersihan Cache di Server Setelah Perbaikan

Jika perubahan sudah di-push ke server, view cache lama di server produksi harus dibersihkan agar template baru yang sudah diperbaiki langsung di-compile ulang:

1. Akses via browser:
   `http://172.100.10.40/antrianonline/public/clear-view-cache`
2. Atau via terminal server:
   ```bash
   php artisan view:clear
   php artisan optimize:clear
   ```

