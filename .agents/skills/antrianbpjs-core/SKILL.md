---
name: antrianbpjs-core
description: >-
  Use this skill when implementing new features, API endpoints, controllers, jobs, models, or frontend views in the Antrian BPJS Online project. Covers API response standards, multi-branch configuration, queue usage, model/database patterns, and frontend standards to speed up implementation, ensure accuracy, and save tokens.
---

# Antrian BPJS Online — Core Implementation Skill

This skill encapsulates the core implementation patterns of the Antrian BPJS Online project. Load this before writing any new code so you don't need to re-read large files (README.md, GEMINI.md, controllers) repeatedly.

## 1. Technology Stack

- **Framework**: Laravel 10.x, PHP >= 8.1
- **Database**: MySQL / MariaDB
- **Queue**: Laravel Queue (database driver)
- **Frontend**: AdminLTE (Bootstrap-based) + Vite
- **Multi-branch**: QLJ, QLKP, QLTMG (extensible via `.env`)

## 2. API Response Format (MANDATORY)

All API endpoints in `routes/api.php` must use this format:

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

- `metadata.code`: use appropriate HTTP code (200, 201, 400, 404, 422, 500, 503)
- On error, `response` can be empty or contain error details
- Do NOT use a different format (e.g. `{"data": ..., "error": ...}`)

## 3. Multi-Branch Configuration

Branches are defined in `.env` via `BPJS_AVAILABLE_URLQL`. Default: `QLJ,QLKP,QLTLG`.

```php
$availableQLs = \App\Helpers\BpjsHelper::getUrlQLOptions();
// Returns: ['QLJ', 'QLKP', 'QLTMG']
```

Always validate branch code against `$availableQLs` before processing.

### Adding a New Branch (e.g. QLBR)

1. Add to `.env`:
   ```
   BPJS_VCLAIM_CONS_ID_QLBR=your_cons_id
   BPJS_VCLAIM_SECRET_QLBR=your_secret
   BPJS_VCLAIM_USERKEY_QLBR=your_userkey
   BPJS_VCLAIM_KODEPPK_QLBR=01XXR0XX
   ```
2. Append `QLBR` to `BPJS_AVAILABLE_URLQL` in `.env`
3. No new table needed — one table per concept, differentiated by `kode_ql` column

## 4. Queue / Background Processing

- Driver: `database` (`QUEUE_CONNECTION=database` in `.env`)
- Large data transfers MUST use queue to avoid dashboard timeout
- Job class must implement `ShouldQueue`
- Example job: `App\Jobs\SyncVclaimKunjunganJob` (timeout 120s, tries 2)
- Dispatch: `JobClass::dispatch($param1, $param2)`
- Queue management endpoints:
  - `GET /api/queue-status` — check pending jobs
  - `GET /api/queue-work-start` — start worker via web
  - `GET /api/queue-work-stop` — stop worker via web
  - `GET /api/queue-clear` — clear all jobs

## 5. Model & Database Patterns

- One table per concept, differentiated by `kode_ql` column (no separate table per branch)
- `vclaim_kunjungan`: unique key `(kode_ql, no_sep)`, index `(kode_ql, tgl_kunjungan, jns_pelayanan)`
- Use `upsert()` for bulk insert/update (reduces N queries to 1)
- Model scopes (see `App\Models\VclaimKunjungan`):
  - `scopeByQL($query, $kodeQL)`
  - `scopeRawatJalan($query)` — jns_pelayanan = 2
  - `scopeRawatInap($query)` — jns_pelayanan = 1
  - `scopeByTanggal($query, $tanggal)`

## 6. Frontend Standards

- AdminLTE components: `card`, `info-box`, `sidebar`, `datatable`
- Blade views in `resources/views/`
- Asset compilation: `npm run dev` (development) or `npm run build` (production)
- Always keep design consistent with existing AdminLTE pages

## 7. Console Commands

- Located in `app/Console/Commands/`
- Register in `app/Console/Kernel.php` schedule
- Example signature: `vclaim:sync-kunjungan-daily`
- Run: `php artisan vclaim:sync-kunjungan-daily`

## 8. Implementation Checklist

- [ ] Credentials in `.env`, never hardcoded in code
- [ ] API response uses `metadata` + `response` format
- [ ] BPJS API calls via `BpjsHelper`, not direct Http client
- [ ] Large data uses queue (`ShouldQueue`)
- [ ] Multi-branch support via `BpjsHelper::getUrlQLOptions()`
- [ ] Request validation with Laravel Validation
- [ ] Logging with `Log::info/error/warning`
- [ ] Branch code validated against available QL list