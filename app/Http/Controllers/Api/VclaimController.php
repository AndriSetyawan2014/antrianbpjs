<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\BpjsHelper;

class VclaimController extends Controller
{
    public function dataKunjungan(Request $request)
    {
        // Validasi input payload
        $allowedQL = implode(',', BpjsHelper::getUrlQLOptions());
        $request->validate([
            'urlQL' => 'required|string|in:' . $allowedQL,
            'tglKunjungan' => 'required|date_format:Y-m-d',
            'jnsPelayanan' => 'required|in:1,2' // 1: Rawat Inap, 2: Rawat Jalan
        ]);

        $urlQL = $request->input('urlQL');
        $tglKunjungan = $request->input('tglKunjungan');
        $jnsPelayanan = $request->input('jnsPelayanan');

        // Memanggil fungsi dari helper
        $result = BpjsHelper::getVclaimDataKunjungan($urlQL, $tglKunjungan, $jnsPelayanan);

        if ($result) {
            return response()->json([
                'metadata' => [
                    'code' => 200,
                    'message' => 'OK'
                ],
                'response' => $result
            ]);
        }

        return response()->json([
            'metadata' => [
                'code' => 404,
                'message' => 'Data Kunjungan tidak ditemukan atau terjadi kesalahan dari API VClaim'
            ]
        ], 404);
    }
}
