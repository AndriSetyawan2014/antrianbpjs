<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
// use App\Models\Antrian;

class BpjsAntrianController extends Controller
{
    protected $consId;
    protected $secretKey;
    protected $baseUrl;
    protected $serviceName;

    public function __construct()
    {
        $this->consId = config('bpjs.cons_id');
        $this->secretKey = config('bpjs.secret_key');
        $this->baseUrl = config('bpjs.base_url');
        $this->serviceName = config('bpjs.service_name');
    }

    // Generate signature for BPJS API
    private function generateSignature($timestamp)
    {
        return base64_encode(hash_hmac('sha256', $this->consId . '&' . $timestamp, $this->secretKey, true));
    }

    // Update waktu antrian ke BPJS
    public function updateWaktuAntrian(Request $request)
    {
        $timestamp = now()->format('YmdHis');
        $signature = $this->generateSignature($timestamp);

        $url = "{$this->baseUrl}/{$this->serviceName}/updatewaktu";
        $data = [
            'kodebooking' => $request->input('kodebooking'),
            'taskid' => $request->input('taskid'),
            'waktu' => $request->input('waktu'),
        ];

        $response = Http::withHeaders([
            'X-cons-id' => $this->consId,
            'X-timestamp' => $timestamp,
            'X-signature' => $signature,
            'Content-Type' => 'application/json',
        ])->post($url, $data);

        if ($response->successful()) {
            // Simpan data ke database
            // Antrian::create($data);
            return response()->json(['message' => 'Update waktu antrian berhasil', 'data' => $response->json()]);
        }

        return response()->json(['error' => 'Gagal mengupdate waktu antrian'], $response->status());
    }
}
