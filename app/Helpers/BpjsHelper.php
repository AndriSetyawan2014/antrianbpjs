<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class BpjsHelper
{
    public static function post($endpoint, $data)
    {
        $baseUrl = 'https://bpjs-url.com'; // Sesuaikan URL dasar API BPJS
        $response = Http::post($baseUrl . $endpoint, $data);

        return $response->json();
    }
    public static function generateSignature()
    {
        $consId = env('ANTROL_CONS_ID');
        $secretKey = ENV('ANTROL_SECRET_KEY');
        $timestamp = strval(now()->timestamp - Carbon::create('1970-01-01 00:00:00')->timestamp);
        $data = $consId . "&" . $timestamp;

        $signature = hash_hmac('sha256', $data, $secretKey, true);
        return base64_encode($signature);
    }

    public static function getTimestamp()
    {
        return strval(now()->timestamp - Carbon::create('1970-01-01 00:00:00')->timestamp);
    }

    public static function getRequest($urlQL, $endpoint, array $params = [])
    {
        // $url = env('ANTROL_BASE_URL') . $endpoint;
        $baseUrl = match ($urlQL) {
            'QLJ'   => env('ANTROL_BASE_URL'),
            'QLKP'  => env('ANTROL_BASE_URL_QLKP'),
            'QLTMG' => env('ANTROL_BASE_URL_QLTMG'),
            default => env('ANTROL_BASE_URL'),
        };
        $url = $baseUrl . $endpoint;
        Log::info('=== [getRequest - ' . $urlQL . '] getRequest url = ' . $url);
        // Log::info('=== [getRequest - ' . $urlQL . '] getRequest params = ' . $params);
        // $data = [];
        // foreach ($params as $param) {
        //     if ($param !== null) {
        //         $data = array_merge($data, $param);
        //     }
        // }

        $response = Http::withHeaders([
            'X-Cons-Id' => env('ANTROL_CONS_ID'),
            'X-Timestamp' => self::getTimestamp(),
            'X-Signature' => self::generateSignature(),
            'X-Jeniskoneksi' => env('ANTROL_JENIS_KONEKSI')
        ])->get($url, $params);

        if (!$response->successful()) {
            Log::error('Failed request to ' . $url, [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return null;
        }
        return $response->body();
    }

    public static function postRequest($urlQL, $endpoint, array $data = [])
    {
        // $url = env('ANTROL_BASE_URL') . $endpoint;
        //$url = 'http://172.100.10.11/sirstql/api/WSAntrianOnline/pengiriman_taskID';
        $baseUrl = match ($urlQL) {
            'QLJ'   => env('ANTROL_BASE_URL'),
            'QLKP'  => env('ANTROL_BASE_URL_QLKP'),
            'QLTMG' => env('ANTROL_BASE_URL_QLTMG'),
            default => env('ANTROL_BASE_URL'),
        };
        $url = $baseUrl . $endpoint;
        $response = Http::withHeaders([
            'X-Cons-Id' => env('ANTROL_CONS_ID'),
            'X-Timestamp' => self::getTimestamp(),
            'X-Signature' => self::generateSignature(),
            'X-Jeniskoneksi' => env('ANTROL_JENIS_KONEKSI')
        ])->post($url, $data);

        return $response->body();
    }

    // public static function postRequest($endpoint, ...$params)
    // {
    //     $url = env('ANTROL_BASE_URL') . $endpoint;
    //     //'http://172.100.10.11/sirstql/api/WSAntrianOnline/pengiriman_taskID';
    //     $data = [];
    //     foreach ($params as $param) {
    //         if ($param !== null) {
    //             $data = array_merge($data, $param);
    //         }
    //     }

    //     $response = Http::withHeaders([
    //         'X-Cons-Id' => env('ANTROL_CONS_ID'),
    //         'X-Timestamp' => self::getTimestamp(),
    //         'X-Signature' => self::generateSignature(),
    //         'X-Jeniskoneksi' => env('ANTROL_JENIS_KONEKSI')
    //     ])->post($url, $data);

    //     return $response->body();
    // }



    // public static function decryptResponse($response)
    // {
    //     $key = env('BPJS_SECRET_KEY');
    //     $key = hex2bin(hash('sha256', $key));

    //     $ciphertext = base64_decode($response);
    //     $ivLength = openssl_cipher_iv_length('AES-256-CBC');
    //     $iv = substr($ciphertext, 0, $ivLength);
    //     $encryptedData = substr($ciphertext, $ivLength);

    //     $decrypted = openssl_decrypt($encryptedData, 'AES-256-CBC', $key, 0, $iv);

    //     return $decrypted;
    // }
}
