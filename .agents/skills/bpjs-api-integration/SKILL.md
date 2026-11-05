---
name: bpjs-api-integration
description: >-
  Use this skill when calling BPJS APIs (Antrol/SIRSTQL and VClaim) in the Antrian BPJS Online project. Covers authentication signatures, headers, encryption/decryption, endpoint patterns, and error handling to speed up implementation, ensure accuracy, and save tokens by avoiding re-reading BpjsHelper.php (689 lines).
---

# BPJS API Integration Skill

This skill encapsulates all patterns for calling BPJS APIs in this project. All BPJS HTTP calls MUST go through `App\Helpers\BpjsHelper` — never call Guzzle/Http directly.

## 1. Authentication Signature Pattern (ALL BPJS calls)

```php
$timestamp = strval(time());
$signature = base64_encode(hash_hmac('sha256', $consId . '&' . $timestamp, $secret, true));
```

Headers required on every BPJS request:
- `X-cons-id` / `X-Cons-Id`: Cons ID
- `X-timestamp` / `X-Timestamp`: Unix timestamp string
- `X-signature` / `X-Signature`: HMAC-SHA256 signature (base64)
- `user_key`: UserKey (VClaim only)
- `Accept`: `application/json`
- `Content-Type`: `application/json` or `application/json; charset=utf-8`

## 2. BPJS Antrol (SIRSTQL) — Appointment/Queue API

Helper methods: `getRequest($urlQL, $endpoint, $params)`, `postRequest($urlQL, $endpoint, $data)`

- **Base URL**: `BPJS_ANTROL_SIRS_URL_QLJ` (per branch, e.g. `http://ip-sirs-qlj/sirstql/api/`)
- **ConsId/Secret**: Global (not per branch): `BPJS_ANTROL_SIRS_CONS_ID`, `BPJS_ANTROL_SIRS_SECRET`
- **Connection type**: `BPJS_ANTROL_SIRS_JENIS_KONEKSI` (e.g. `VPN`)
- **Headers**: `X-Cons-Id`, `X-Timestamp`, `X-Signature`, `X-Jeniskoneksi`
- **Timeout**: 300s (GET), 120s (POST)
- **Returns**: Raw response body (string), or `null` on failure

Common endpoints:
- `/data_pending_kodebooking` — pending kode booking
- `/data_pending_taskid` — pending task ID
- `/ref/poli` — poli reference
- `/jadwaldokter/kodepoli/{kode}/tanggal/{tgl}` — doctor schedule
- `/antrean/add` — add antrean
- `/antrean/batal` — cancel antrean
- `/antrean/getlisttask` — get task list
- `/antrean/pendaftaran/tanggal/{tgl}` — antrean by date
- `/antrean/pendaftaran/kodebooking/{kb}` — antrean by kode booking

## 3. BPJS VClaim — Patient Visit API

Helper methods: `getVclaimDataKunjungan($urlQL, $tanggal, $jnsPelayanan)`, `getVclaimPesertaNik`, `getVclaimRujukan`, `getVclaimSep`, `getVclaimRiwayatPelayanan`, etc.

- **Base URL**: `BPJS_VCLAIM_BASE_URL` (default: `https://apijkn.bpjs-kesehatan.go.id/vclaim-rest`)
- **ConsId/Secret/UserKey**: Per branch:
  - `BPJS_VCLAIM_CONS_ID_QLJ`, `BPJS_VCLAIM_SECRET_QLJ`, `BPJS_VCLAIM_USERKEY_QLJ`
  - `BPJS_VCLAIM_CONS_ID_QLKP`, ...
  - `BPJS_VCLAIM_CONS_ID_QLTMG`, ...
- **Kode PPK**: Per branch: `BPJS_VCLAIM_KODEPPK_QLJ`, etc.
- **Headers**: `X-cons-id`, `X-timestamp`, `X-signature`, `user_key`, `Accept`, `Content-Type`
- **Timeout**: 120s
- **Response**: Encrypted (AES-256-CBC) + compressed (LZString). Decrypted automatically by `decryptResponse()`.
- **Returns**: Decrypted data array, or `null` on failure

### VClaim Endpoints (via `getVclaimDataKunjungan`)
- `GET /Monitoring/Kunjungan/Tanggal/{tgl}/JnsPelayanan/{jns}`
  - `jns = 1`: Rawat Inap
  - `jns = 2`: Rawat Jalan
- Returns: `['sep' => [...], ...]`

### Other VClaim helper endpoints
- `/Peserta/nik/{nik}/tglSEP/{tgl}` — peserta by NIK
- `/Rujukan/{noRujukan}` — rujukan by number
- `/Rujukan/Peserta/{noKartu}` — rujukan by card
- `/SEP/{noSep}` — SEP detail
- `/referensi/diagnosa/{kode}` — diagnosis reference
- `/referensi/dokter/pelayanan/{jns}/tglPelayanan/{tgl}/Spesialis/{spesialis}` — doctor reference
- `/monitoring/HistoriPelayanan/NoKartu/{kartu}/tglMulai/{t1}/tglAkhir/{t2}` — visit history

## 4. Direct (Encrypted) Antrol API

Helper methods: `getRequestDirect($urlQL, $endpoint, $params)`, `postRequestDirect($urlQL, $endpoint, $data)`

- **Base URL**: `BPJS_BASE_URL` (default: `https://apijkn.bpjs-kesehatan.go.id/antreanrs`)
- **ConsId/Secret/UserKey**: Per branch: `BPJS_ANTROL_CONS_ID_QLJ`, `BPJS_ANTROL_SECRET_QLJ`, `BPJS_ANTROL_USERKEY_QLJ`
- **Response**: Encrypted, decrypted by `decryptResponse()`
- **Timeout**: 30s

## 5. Error Handling Pattern

```php
$response = BpjsHelper::getVclaimDataKunjungan($urlQL, $tanggal, 2);

if ($response === null || empty($response['sep'])) {
    // No data or API error — handle gracefully
    return response()->json([
        'metadata' => ['code' => 404, 'message' => 'Tidak ada data dari BPJS'],
        'response' => []
    ], 404);
}

$data = $response['sep']; // Array of records
```

- Always check for `null` return (API unreachable)
- Always check `empty($data['sep'])` (no data)
- Wrap in try-catch for `\Throwable`
- Log errors with `Log::error("[Context] Message: " . $e->getMessage())`

## 6. Branch Selection Pattern

```php
$availableQLs = \App\Helpers\BpjsHelper::getUrlQLOptions();
$urlQL = strtoupper($request->input('urlQL', ''));

if ($urlQL && !in_array($urlQL, $availableQLs)) {
    return response()->json([
        'metadata' => [
            'code' => 422,
            'message' => 'Parameter urlQL tidak valid. Nilai yang diizinkan: ' . implode(', ', $availableQLs),
        ],
    ], 422);
}

$targetQLs = ($urlQL) ? [$urlQL] : $availableQLs;

foreach ($targetQLs as $ql) {
    // Call BPJS API for each branch
    $data = BpjsHelper::getVclaimDataKunjungan($ql, $tanggal, 2);
}
```

## 7. Implementation Checklist (BPJS API)

- [ ] Call via `BpjsHelper`, never `Http::get()`/`Http::post()` directly for BPJS
- [ ] Use correct credential env vars (global for Antrol, per-branch for VClaim)
- [ ] Validate `urlQL` against `BpjsHelper::getUrlQLOptions()`
- [ ] Handle `null` response (API down/unreachable)
- [ ] Handle empty `sep` array (no data)
- [ ] Log all API calls and errors
- [ ] For large data, dispatch to queue instead of inline processing