<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

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

        $timestamp = strval(time());
        $consId    = env('BPJS_ANTROL_SIRS_CONS_ID');
        $secret    = env('BPJS_ANTROL_SIRS_SECRET');
        $signature = base64_encode(hash_hmac('sha256', $consId . '&' . $timestamp, $secret, true));

        $response = Http::withHeaders([
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
        $response = Http::post($url, $data);
        return $response->body();
    }

    public static function postRequestDirect($urlQL, $endpoint, array $data = [])
    {
        $consId    = env('BPJS_ANTROL_CONS_ID_'  . $urlQL);
        $secret    = env('BPJS_ANTROL_SECRET_'   . $urlQL);
        $userKey   = env('BPJS_ANTROL_USERKEY_'  . $urlQL);
        $timestamp = strval(time());
        $signature = base64_encode(hash_hmac('sha256', $consId . '&' . $timestamp, $secret, true));

        $url = env('BPJS_BASE_URL') . $endpoint;

        $response = Http::withHeaders([
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
            'X-cons-id'    => $consId,
            'X-timestamp'  => $timestamp,
            'X-signature'  => $signature,
            'user_key'     => $userKey,
        ])->post($url, $data);

        return $response->body();
    }

    public static function getRequestDirect($urlQL, $endpoint, array $params = [])
    {
        $consId    = env('BPJS_ANTROL_CONS_ID_'  . $urlQL);
        $secret    = env('BPJS_ANTROL_SECRET_'   . $urlQL);
        $userKey   = env('BPJS_ANTROL_USERKEY_'  . $urlQL);
        $timestamp = strval(time());
        $signature = base64_encode(hash_hmac('sha256', $consId . '&' . $timestamp, $secret, true));

        $url = env('BPJS_BASE_URL') . $endpoint;

        $response = Http::withHeaders([
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
            'X-cons-id'    => $consId,
            'X-timestamp'  => $timestamp,
            'X-signature'  => $signature,
            'user_key'     => $userKey,
        ])->get($url, $params);

        return $response->body();
    }

    public static function getVclaimDataKunjungan($urlQL, $tglKunjungan, $jnsPelayanan)
    {
        $consId    = env('BPJS_VCLAIM_CONS_ID_'  . $urlQL);
        $secret    = env('BPJS_VCLAIM_SECRET_'   . $urlQL);
        $userKey   = env('BPJS_VCLAIM_USERKEY_'  . $urlQL);
        $timestamp = strval(time());
        $signature = base64_encode(hash_hmac('sha256', $consId . '&' . $timestamp, $secret, true));

        $endpoint = "/Monitoring/Kunjungan/Tanggal/{$tglKunjungan}/JnsPelayanan/{$jnsPelayanan}";
        $url = env('BPJS_VCLAIM_BASE_URL') . $endpoint;

        $response = Http::withHeaders([
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
            'X-cons-id'    => $consId,
            'X-timestamp'  => $timestamp,
            'X-signature'  => $signature,
            'user_key'     => $userKey,
        ])->get($url);

        $result = $response->json();

        if ($response->successful() && isset($result['metaData']['code']) && $result['metaData']['code'] == 200) {
            return $result['response'];
        }

        return null;
    }
}
