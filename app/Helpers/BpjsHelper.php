<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BpjsHelper
{
    /**
     * Dapatkan daftar URL QL yang diizinkan (QLJ, QLKP, QLTMG dsb) dari .env
     */
    public static function getUrlQLOptions()
    {
        $envValue = env('BPJS_AVAILABLE_URLQL', 'QLJ,QLKP,QLTMG');
        return explode(',', str_replace(' ', '', $envValue));
    }

    public static function getRequest($urlQL, $endpoint, array $params = [])
    {
        $url = env('BPJS_ANTROL_SIRS_URL_' . $urlQL) . $endpoint;

        Log::info('[BpjsHelper::getRequest] Target URL', [
            'url' => $url,
            'params' => $params
        ]);

        $timestamp = strval(time());
        $consId    = env('BPJS_ANTROL_SIRS_CONS_ID');
        $secret    = env('BPJS_ANTROL_SIRS_SECRET');
        $signature = base64_encode(hash_hmac('sha256', $consId . '&' . $timestamp, $secret, true));

        $response = Http::timeout(300)->withHeaders([
            'X-Cons-Id'      => $consId,
            'X-Timestamp'    => $timestamp,
            'X-Signature'    => $signature,
            'X-Jeniskoneksi' => env('BPJS_ANTROL_SIRS_JENIS_KONEKSI'),
        ])->get($url, $params);

        if (!$response->successful()) {
            return null;
        }
        return $response->body();
    }

    public static function postRequest($urlQL, $endpoint, array $data = [])
    {
        $url = env('BPJS_ANTROL_SIRS_URL_' . $urlQL) . $endpoint;

        $timestamp = strval(time());
        $consId    = env('BPJS_ANTROL_SIRS_CONS_ID');
        $secret    = env('BPJS_ANTROL_SIRS_SECRET');
        $signature = base64_encode(hash_hmac('sha256', $consId . '&' . $timestamp, $secret, true));

        Log::info('[BpjsHelper::postRequest] Sending POST to: ' . $url);

        $response = Http::timeout(120)
            ->connectTimeout(15)
            ->withHeaders([
                'X-Cons-Id'      => $consId,
                'X-Timestamp'    => $timestamp,
                'X-Signature'    => $signature,
                'X-Jeniskoneksi' => env('BPJS_ANTROL_SIRS_JENIS_KONEKSI'),
            ])
            ->post($url, $data);

        if (!$response->successful()) {
            Log::warning('[BpjsHelper::postRequest] Non-2xx response from ' . $url . ' — Status: ' . $response->status());
        }

        return $response->body();
    }

    public static function postRequestDirect($urlQL, $endpoint, array $data = [])
    {
        $consId    = env('BPJS_ANTROL_CONS_ID_'  . $urlQL);
        $secret    = env('BPJS_ANTROL_SECRET_'   . $urlQL);
        $userKey   = env('BPJS_ANTROL_USERKEY_'  . $urlQL);
        $timestamp = strval(time());
        
        $signature = base64_encode(hash_hmac('sha256', $consId . '&' . $timestamp, $secret, true));

        Log::info("DEBUG_SIGNATURE [BpjsHelper::postRequestDirect] QL: {$urlQL}, ConsId: {$consId}, Timestamp: {$timestamp}, SecretPrefix: " . substr($secret, 0, 3));

        $url = env('BPJS_BASE_URL', 'https://apijkn.bpjs-kesehatan.go.id/antreanrs') . $endpoint;

        Log::info('[BpjsHelper::postRequestDirect] Sending DIRECT POST to: ' . $url, [
            'payload' => $data
        ]);

        $response = Http::timeout(30)
            ->connectTimeout(30)
            ->withHeaders([
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
                'X-cons-id'    => $consId,
                'X-timestamp'  => $timestamp,
                'X-signature'  => $signature,
                'user_key'     => $userKey,
            ])->post($url, $data);

        $body = $response->body();
        $decryptedBody = self::decryptResponse($consId, $secret, $timestamp, $body);
        
        Log::info('DEBUG_BPJS [BpjsHelper::postRequestDirect] Response (Decrypted): ' . $decryptedBody);

        return $decryptedBody;
    }

    public static function getRequestDirect($urlQL, $endpoint, array $params = [])
    {
        $consId    = env('BPJS_ANTROL_CONS_ID_'  . $urlQL);
        $secret    = env('BPJS_ANTROL_SECRET_'   . $urlQL);
        $userKey   = env('BPJS_ANTROL_USERKEY_'  . $urlQL);
        $timestamp = strval(time());
        $signature = base64_encode(hash_hmac('sha256', $consId . '&' . $timestamp, $secret, true));

        $url = env('BPJS_BASE_URL', 'https://apijkn.bpjs-kesehatan.go.id/antreanrs') . $endpoint;

        $response = Http::timeout(30)
            ->connectTimeout(30)
            ->withHeaders([
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
            'X-cons-id'    => $consId,
            'X-timestamp'  => $timestamp,
            'X-signature'  => $signature,
            'user_key'     => $userKey,
        ])->get($url, $params);

        $body = $response->body();
        return self::decryptResponse($consId, $secret, $timestamp, $body);
    }

    /**
     * Decrypt generic BPJS response body (metaData + encrypted response).
     */
    private static function decryptResponse($consId, $secret, $timestamp, $body)
    {
        $result = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $body;
        }

        // Jika 'response' berupa string (terenkripsi), lakukan dekripsi
        Log::info("DEBUG_BPJS [BpjsHelper::decryptResponse] Body received. Metadata code: " . ($result['metaData']['code'] ?? $result['metadata']['code'] ?? 'N/A'));

        if (isset($result['response']) && is_string($result['response']) && !empty($result['response'])) {
            $key = $consId . $secret . $timestamp;
            Log::info("DEBUG_BPJS [BpjsHelper::decryptResponse] Decrypting response field. Key prefix: " . substr($key, 0, 10));

            $decrypted = self::vclaimDecrypt($key, $result['response']);
            if ($decrypted) {
                Log::info("DEBUG_BPJS [BpjsHelper::decryptResponse] Decrypt success.");
                $decompressed = self::vclaimDecompress($decrypted);
                if ($decompressed) {
                    Log::info("DEBUG_BPJS [BpjsHelper::decryptResponse] Decompress success.");
                    $finalData = $decompressed;
                } else {
                    Log::info("DEBUG_BPJS [BpjsHelper::decryptResponse] Decompress failed, using raw decrypted.");
                    $finalData = $decrypted;
                }

                $decodedData = json_decode($finalData, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $result['response'] = $decodedData;
                } else {
                    $result['response'] = $finalData;
                }
                
                $finalJson = json_encode($result);
                Log::info("DEBUG_BPJS [BpjsHelper::decryptResponse] Final JSON length: " . strlen($finalJson));
                return $finalJson;
            } else {
                Log::warning("DEBUG_BPJS [BpjsHelper::decryptResponse] Decrypt FAILED.");
            }
        }

        return $body;
    }

    public static function getVclaimDataKunjungan($urlQL, $tanggal, $jnsPelayanan = 2)
    {
        $consId    = env('BPJS_VCLAIM_CONS_ID_'  . $urlQL);
        $secret    = env('BPJS_VCLAIM_SECRET_'   . $urlQL);
        $userKey   = env('BPJS_VCLAIM_USERKEY_'  . $urlQL);
        $timestamp = strval(time());
        $signature = base64_encode(hash_hmac('sha256', $consId . '&' . $timestamp, $secret, true));

        $endpoint = "/Monitoring/Kunjungan/Tanggal/{$tanggal}/JnsPelayanan/{$jnsPelayanan}";
        $url = env('BPJS_VCLAIM_BASE_URL', 'https://apijkn.bpjs-kesehatan.go.id/vclaim-rest') . $endpoint;

        Log::info("[BpjsHelper::getVclaimDataKunjungan] GET {$urlQL} → {$url}");

        $response = Http::timeout(120)
            ->connectTimeout(15)
            ->withHeaders([
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json; charset=utf-8',
                'X-cons-id'    => $consId,
                'X-timestamp'  => $timestamp,
                'X-signature'  => $signature,
                'user_key'     => $userKey,
            ])->get($url);

        $body = $response->body();
        $decryptedBody = self::decryptResponse($consId, $secret, $timestamp, $body);

        Log::info("DEBUG_BPJS [BpjsHelper::getVclaimDataKunjungan] Response {$urlQL} (Decrypted): " . $decryptedBody);

        $decoded = json_decode($decryptedBody, true);
        if (isset($decoded['response']) && is_array($decoded['response'])) {
            Log::info("DEBUG_BPJS [BpjsHelper::getVclaimDataKunjungan] Found response array with keys: " . implode(', ', array_keys($decoded['response'])));
            return $decoded['response'];
        }

        Log::warning("DEBUG_BPJS [BpjsHelper::getVclaimDataKunjungan] Response field is NOT an array or missing.");
        return null;
    }

    public static function getVclaimPesertaNik($urlQL, $nik, $tanggal)
    {
        $consId    = env('BPJS_VCLAIM_CONS_ID_'  . $urlQL);
        $secret    = env('BPJS_VCLAIM_SECRET_'   . $urlQL);
        $userKey   = env('BPJS_VCLAIM_USERKEY_'  . $urlQL);
        $timestamp = strval(time());
        $signature = base64_encode(hash_hmac('sha256', $consId . '&' . $timestamp, $secret, true));

        $endpoint = "/Peserta/nik/{$nik}/tglSEP/{$tanggal}";
        $url = env('BPJS_VCLAIM_BASE_URL', 'https://apijkn.bpjs-kesehatan.go.id/vclaim-rest') . $endpoint;

        $startTime = microtime(true);

        try {
            $response = Http::timeout(30)
                ->connectTimeout(15)
                ->withHeaders([
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json; charset=utf-8',
                    'X-cons-id'    => $consId,
                    'X-timestamp'  => $timestamp,
                    'X-signature'  => $signature,
                    'user_key'     => $userKey,
                ])->get($url);

            $endTime = microtime(true);
            $responseTimeMs = round(($endTime - $startTime) * 1000);

            $body = $response->body();
            $decryptedBody = self::decryptResponse($consId, $secret, $timestamp, $body);
            $decoded = json_decode($decryptedBody, true);
            
            $metaCode = $decoded['metaData']['code'] ?? ($decoded['metadata']['code'] ?? $response->status());

            return [
                'success'       => true,
                'http_code'     => $response->status(),
                'message_code'  => $metaCode,
                'response_time' => $responseTimeMs,
            ];
        } catch (\Exception $e) {
            $endTime = microtime(true);
            $responseTimeMs = round(($endTime - $startTime) * 1000);
            
            return [
                'success'       => false,
                'http_code'     => 500,
                'message_code'  => 500,
                'response_time' => $responseTimeMs,
                'error'         => $e->getMessage()
            ];
        }
    }

    public static function getVclaimRujukan($urlQL, $noRujukan)
    {
        $consId    = env('BPJS_VCLAIM_CONS_ID_'  . $urlQL);
        $secret    = env('BPJS_VCLAIM_SECRET_'   . $urlQL);
        $userKey   = env('BPJS_VCLAIM_USERKEY_'  . $urlQL);
        $timestamp = strval(time());
        $signature = base64_encode(hash_hmac('sha256', $consId . '&' . $timestamp, $secret, true));

        $endpoint = "/Rujukan/{$noRujukan}";
        $url = env('BPJS_VCLAIM_BASE_URL', 'https://apijkn.bpjs-kesehatan.go.id/vclaim-rest') . $endpoint;

        $startTime = microtime(true);

        try {
            $response = Http::timeout(30)
                ->connectTimeout(15)
                ->withHeaders([
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json; charset=utf-8',
                    'X-cons-id'    => $consId,
                    'X-timestamp'  => $timestamp,
                    'X-signature'  => $signature,
                    'user_key'     => $userKey,
                ])->get($url);

            $endTime = microtime(true);
            $responseTimeMs = round(($endTime - $startTime) * 1000);

            $body = $response->body();
            $decryptedBody = self::decryptResponse($consId, $secret, $timestamp, $body);
            $decoded = json_decode($decryptedBody, true);
            
            $metaCode = $decoded['metaData']['code'] ?? ($decoded['metadata']['code'] ?? $response->status());

            return [
                'success'       => true,
                'http_code'     => $response->status(),
                'message_code'  => $metaCode,
                'response_time' => $responseTimeMs,
            ];
        } catch (\Exception $e) {
            $endTime = microtime(true);
            $responseTimeMs = round(($endTime - $startTime) * 1000);
            
            return [
                'success'       => false,
                'http_code'     => 500,
                'message_code'  => 500,
                'response_time' => $responseTimeMs,
                'error'         => $e->getMessage()
            ];
        }
    }

    public static function getVclaimRujukanByNoKartu($urlQL, $noKartu)
    {
        $consId    = env('BPJS_VCLAIM_CONS_ID_'  . $urlQL);
        $secret    = env('BPJS_VCLAIM_SECRET_'   . $urlQL);
        $userKey   = env('BPJS_VCLAIM_USERKEY_'  . $urlQL);
        $timestamp = strval(time());
        $signature = base64_encode(hash_hmac('sha256', $consId . '&' . $timestamp, $secret, true));

        $endpoint = "/Rujukan/Peserta/{$noKartu}";
        $url = env('BPJS_VCLAIM_BASE_URL', 'https://apijkn.bpjs-kesehatan.go.id/vclaim-rest') . $endpoint;

        $startTime = microtime(true);

        try {
            $response = Http::timeout(30)
                ->connectTimeout(15)
                ->withHeaders([
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json; charset=utf-8',
                    'X-cons-id'    => $consId,
                    'X-timestamp'  => $timestamp,
                    'X-signature'  => $signature,
                    'user_key'     => $userKey,
                ])->get($url);

            $endTime = microtime(true);
            $responseTimeMs = round(($endTime - $startTime) * 1000);

            $body = $response->body();
            $decryptedBody = self::decryptResponse($consId, $secret, $timestamp, $body);
            $decoded = json_decode($decryptedBody, true);
            
            $metaCode = $decoded['metaData']['code'] ?? ($decoded['metadata']['code'] ?? $response->status());

            return [
                'success'       => true,
                'http_code'     => $response->status(),
                'message_code'  => $metaCode,
                'response_time' => $responseTimeMs,
            ];
        } catch (\Exception $e) {
            $endTime = microtime(true);
            $responseTimeMs = round(($endTime - $startTime) * 1000);
            
            return [
                'success'       => false,
                'http_code'     => 500,
                'message_code'  => 500,
                'response_time' => $responseTimeMs,
                'error'         => $e->getMessage()
            ];
        }
    }

    /**
     * Helper umum untuk keperluan PING endpoint VClaim menggunakan method GET.
     */
    public static function pingVclaimGetEndpoint($urlQL, $endpoint)
    {
        $consId    = env('BPJS_VCLAIM_CONS_ID_'  . $urlQL);
        $secret    = env('BPJS_VCLAIM_SECRET_'   . $urlQL);
        $userKey   = env('BPJS_VCLAIM_USERKEY_'  . $urlQL);
        $timestamp = strval(time());
        $signature = base64_encode(hash_hmac('sha256', $consId . '&' . $timestamp, $secret, true));

        $url = env('BPJS_VCLAIM_BASE_URL', 'https://apijkn.bpjs-kesehatan.go.id/vclaim-rest') . $endpoint;

        $startTime = microtime(true);

        try {
            $response = Http::timeout(30)
                ->connectTimeout(15)
                ->withHeaders([
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json; charset=utf-8',
                    'X-cons-id'    => $consId,
                    'X-timestamp'  => $timestamp,
                    'X-signature'  => $signature,
                    'user_key'     => $userKey,
                ])->get($url);

            $endTime = microtime(true);
            $responseTimeMs = round(($endTime - $startTime) * 1000);

            $body = $response->body();
            $decryptedBody = self::decryptResponse($consId, $secret, $timestamp, $body);
            $decoded = json_decode($decryptedBody, true);
            
            $metaCode = $decoded['metaData']['code'] ?? ($decoded['metadata']['code'] ?? $response->status());

            return [
                'success'       => true,
                'http_code'     => $response->status(),
                'message_code'  => $metaCode,
                'response_time' => $responseTimeMs,
            ];
        } catch (\Exception $e) {
            $endTime = microtime(true);
            $responseTimeMs = round(($endTime - $startTime) * 1000);
            
            return [
                'success'       => false,
                'http_code'     => 500,
                'message_code'  => 500,
                'response_time' => $responseTimeMs,
                'error'         => $e->getMessage()
            ];
        }
    }

    public static function getVclaimRujukanListPeserta($urlQL, $noKartu)
    {
        return self::pingVclaimGetEndpoint($urlQL, "/Rujukan/List/Peserta/{$noKartu}");
    }

    public static function getVclaimReferensiDiagnosa($urlQL, $diagnosa)
    {
        return self::pingVclaimGetEndpoint($urlQL, "/referensi/diagnosa/{$diagnosa}");
    }

    public static function getVclaimReferensiDpjp($urlQL, $jnsPelayanan, $tglPelayanan, $spesialis)
    {
        return self::pingVclaimGetEndpoint($urlQL, "/referensi/dokter/pelayanan/{$jnsPelayanan}/tglPelayanan/{$tglPelayanan}/Spesialis/{$spesialis}");
    }

    public static function getVclaimSep($urlQL, $noSep)
    {
        return self::pingVclaimGetEndpoint($urlQL, "/SEP/{$noSep}");
    }

    public static function getVclaimSuratKontrol($urlQL, $noSuratKontrol)
    {
        return self::pingVclaimGetEndpoint($urlQL, "/RencanaKontrol/noSuratKontrol/{$noSuratKontrol}");
    }

    public static function getVclaimRiwayatPelayanan($urlQL, $noKartu, $tglMulai, $tglAkhir)
    {
        return self::pingVclaimGetEndpoint($urlQL, "/monitoring/HistoriPelayanan/NoKartu/{$noKartu}/tglMulai/{$tglMulai}/tglAkhir/{$tglAkhir}");
    }

    /**
     * Helper umum untuk keperluan PING endpoint Antrol (Antrean RS) menggunakan method GET.
     */
    public static function pingAntrolGetEndpoint($urlQL, $endpoint)
    {
        $consId    = env('BPJS_ANTROL_CONS_ID_'  . $urlQL);
        $secret    = env('BPJS_ANTROL_SECRET_'   . $urlQL);
        $userKey   = env('BPJS_ANTROL_USERKEY_'  . $urlQL);
        $timestamp = strval(time());
        $signature = base64_encode(hash_hmac('sha256', $consId . '&' . $timestamp, $secret, true));

        $url = env('BPJS_BASE_URL', 'https://apijkn.bpjs-kesehatan.go.id/antreanrs') . $endpoint;

        $startTime = microtime(true);

        try {
            $response = Http::timeout(30)
                ->connectTimeout(15)
                ->withHeaders([
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                    'X-cons-id'    => $consId,
                    'X-timestamp'  => $timestamp,
                    'X-signature'  => $signature,
                    'user_key'     => $userKey,
                ])->get($url);

            $endTime = microtime(true);
            $responseTimeMs = round(($endTime - $startTime) * 1000);

            $body = $response->body();
            $decryptedBody = self::decryptResponse($consId, $secret, $timestamp, $body);
            $decoded = json_decode($decryptedBody, true);
            
            $metaCode = $decoded['metaData']['code'] ?? ($decoded['metadata']['code'] ?? $response->status());

            return [
                'success'       => true,
                'http_code'     => $response->status(),
                'message_code'  => $metaCode,
                'response_time' => $responseTimeMs,
            ];
        } catch (\Exception $e) {
            $endTime = microtime(true);
            $responseTimeMs = round(($endTime - $startTime) * 1000);
            
            return [
                'success'       => false,
                'http_code'     => 500,
                'message_code'  => 500,
                'response_time' => $responseTimeMs,
                'error'         => $e->getMessage()
            ];
        }
    }

    /**
     * Helper umum untuk keperluan PING endpoint Antrol (Antrean RS) menggunakan method POST.
     */
    public static function pingAntrolPostEndpoint($urlQL, $endpoint, $data)
    {
        $consId    = env('BPJS_ANTROL_CONS_ID_'  . $urlQL);
        $secret    = env('BPJS_ANTROL_SECRET_'   . $urlQL);
        $userKey   = env('BPJS_ANTROL_USERKEY_'  . $urlQL);
        $timestamp = strval(time());
        $signature = base64_encode(hash_hmac('sha256', $consId . '&' . $timestamp, $secret, true));

        $url = env('BPJS_BASE_URL', 'https://apijkn.bpjs-kesehatan.go.id/antreanrs') . $endpoint;

        $startTime = microtime(true);

        try {
            $response = Http::timeout(30)
                ->connectTimeout(15)
                ->withHeaders([
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                    'X-cons-id'    => $consId,
                    'X-timestamp'  => $timestamp,
                    'X-signature'  => $signature,
                    'user_key'     => $userKey,
                ])->post($url, $data);

            $endTime = microtime(true);
            $responseTimeMs = round(($endTime - $startTime) * 1000);

            $body = $response->body();
            $decryptedBody = self::decryptResponse($consId, $secret, $timestamp, $body);
            $decoded = json_decode($decryptedBody, true);
            
            $metaCode = $decoded['metaData']['code'] ?? ($decoded['metadata']['code'] ?? $response->status());

            return [
                'success'       => true,
                'http_code'     => $response->status(),
                'message_code'  => $metaCode,
                'response_time' => $responseTimeMs,
            ];
        } catch (\Exception $e) {
            $endTime = microtime(true);
            $responseTimeMs = round(($endTime - $startTime) * 1000);
            
            return [
                'success'       => false,
                'http_code'     => 500,
                'message_code'  => 500,
                'response_time' => $responseTimeMs,
                'error'         => $e->getMessage()
            ];
        }
    }

    public static function getAntrolReferensiPoli($urlQL)
    {
        return self::pingAntrolGetEndpoint($urlQL, "/ref/poli");
    }

    public static function getAntrolJadwalDokter($urlQL, $kodePoli, $tanggal)
    {
        return self::pingAntrolGetEndpoint($urlQL, "/jadwaldokter/kodepoli/{$kodePoli}/tanggal/{$tanggal}");
    }

    public static function postAntrolAntreanAdd($urlQL, $data)
    {
        return self::pingAntrolPostEndpoint($urlQL, "/antrean/add", $data);
    }

    public static function postAntrolUpdateJadwal($urlQL, $data)
    {
        return self::pingAntrolPostEndpoint($urlQL, "/jadwaldokter/updatejadwaldokter", $data);
    }

    public static function getVclaimFingerprint($urlQL, $noKartu, $tglPelayanan)
    {
        return self::pingVclaimGetEndpoint($urlQL, "/SEP/FingerPrint/Peserta/{$noKartu}/TglPelayanan/{$tglPelayanan}");
    }

    public static function postAntrolBatalAntrean($urlQL, $data)
    {
        return self::pingAntrolPostEndpoint($urlQL, "/antrean/batal", $data);
    }

    public static function postAntrolGetListTask($urlQL, $data)
    {
        return self::pingAntrolPostEndpoint($urlQL, "/antrean/getlisttask", $data);
    }

    public static function getAntrolAntreanPerTanggal($urlQL, $tanggal)
    {
        return self::pingAntrolGetEndpoint($urlQL, "/antrean/pendaftaran/tanggal/{$tanggal}");
    }

    public static function getAntrolAntreanPerKodeBooking($urlQL, $kodeBooking)
    {
        return self::pingAntrolGetEndpoint($urlQL, "/antrean/pendaftaran/kodebooking/{$kodeBooking}");
    }

    /**
     * Decrypt response BPJS VClaim V2.
     * Key  = hex2bin(sha256(consId + secret + timestamp))
     * IV   = 16 byte pertama dari key_hash
     */
    private static function vclaimDecrypt(string $key, string $encryptedBase64): string|false
    {
        $keyHash = hex2bin(hash('sha256', $key));
        $iv      = substr($keyHash, 0, 16);
        return openssl_decrypt(base64_decode($encryptedBase64), 'AES-256-CBC', $keyHash, OPENSSL_RAW_DATA, $iv);
    }

    /**
     * Dekompresi LZString (format encodedURIComponent) hasil decrypt VClaim V2.
     * Membutuhkan package nullpunkt/lz-string atau implementasi manual.
     */
    private static function vclaimDecompress(string $compressed): string|false
    {
        // Cek apakah ada library LZString via composer
        if (!class_exists('\LZCompressor\LZString')) {
            // Gunakan Standalone Loader anti-gagal yang sudah digabung menjadi 1 file
            $standalonePath = __DIR__ . '/LZStringStandalone.php';
            if (file_exists($standalonePath)) {
                require_once $standalonePath;
            }
        }

        if (class_exists('\LZCompressor\LZString')) {
            return \LZCompressor\LZString::decompressFromEncodedURIComponent($compressed);
        }

        // Fallback: jika string sudah JSON valid, tidak perlu decompress
        $test = json_decode($compressed, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $compressed;
        }

        // Fallback: URL decode dan coba
        $urlDecoded = urldecode($compressed);
        $test2 = json_decode($urlDecoded, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $urlDecoded;
        }

        return false;
    }
}
