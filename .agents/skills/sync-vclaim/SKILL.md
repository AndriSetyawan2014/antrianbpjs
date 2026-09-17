---
name: sync-vclaim
description: >-
  Use this skill when the user asks to sync BPJS VClaim data, fetch Rawat Jalan visits,
  or manually trigger the daily VClaim sync process for the Antrian BPJS Online project.
---

# Sync BPJS VClaim Data

The "Antrian BPJS Online" project synchronizes outpatient visit data (Kunjungan Rawat Jalan) from BPJS VClaim APIs to local databases. This data is encrypted and compressed by BPJS, but the application handles decryption automatically.

You can trigger the synchronization process manually via Artisan commands or HTTP endpoints.

## 1. Daily Sync via Artisan (Recommended for general sync)

To sync the data for **today** for **all active branches** (QLJ, QLKP, QLTMG):
Run the following command from the project root (`c:\Users\tcomp\project\antrianbpjs`):
```bash
php artisan vclaim:sync-kunjungan-daily
```

## 2. Sync Specific Dates or Branches via API

If the user wants to sync a specific date or a specific branch, use the API endpoints.
Assume the application is at `http://localhost/antrianbpjs/public` (adjust `APP_URL` if needed).

### Sync Single Date
Use the `/api/vclaim/sync-kunjungan-jalan` endpoint.
Parameters:
- `tanggal` (optional, default today): Format `YYYY-MM-DD`
- `urlQL` (optional, default all): `QLJ`, `QLKP`, or `QLTMG`

```bash
# Sync specific date for all branches
curl -X GET "http://localhost/antrianbpjs/public/api/vclaim/sync-kunjungan-jalan?tanggal=2026-04-21"

# Sync specific date for a specific branch
curl -X GET "http://localhost/antrianbpjs/public/api/vclaim/sync-kunjungan-jalan?tanggal=2026-04-21&urlQL=QLJ"
```

### Sync Date Range (Multi-Date)
Use the `/api/vclaim/sync-kunjungan-jalan-range` endpoint.
Parameters:
- `tgl_mulai` (required): Format `YYYY-MM-DD`
- `tgl_akhir` (optional, default today): Format `YYYY-MM-DD`
- `urlQL` (optional, default all)
- `limit_hari` (optional, default 31, max 365)

```bash
# Sync last 7 days for all branches
curl -X GET "http://localhost/antrianbpjs/public/api/vclaim/sync-kunjungan-jalan-range?tgl_mulai=2026-04-15&tgl_akhir=2026-04-21"
```
**Warning**: Date range syncing can take a long time and might timeout if run through a browser. Using `curl` with a high max-time (`--max-time 3600`) is recommended.

## 3. Viewing the Data
Once synced, the data is available in the local database and can be viewed via the web interface at `/vclaim/kunjungan-rawat-jalan` or `/vclaim/rekap-kunjungan-rawat-jalan`.
