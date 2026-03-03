<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\BpjsHelper;

class AntrianOnlineController extends Controller
{
    public function token_get(Request $request)
    {

        $params = [];

        // Endpoint untuk referensi poli
        $endpoint = '/token';
        try {
            $response = BpjsHelper::getRequest($endpoint, $params);

            return response()->json(json_decode($response), 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function profil_post(Request $request)
    {
        $params = [];

        if ($request->has('norm')) {
            $params['norm'] = $request->input('norm');
        } else {
            $statusCode = 500;
            return response()->json([
                'metadata' => [
                    'message' => $statusCode == 200 ? 'Ok' : 'No.RM belum diisi',
                    'code' => $statusCode
                ]
            ], $statusCode);
        }

        // Endpoint untuk referensi poli
        $endpoint = '/profil';
        try {
            $response = BpjsHelper::postRequest($endpoint, $params);
            return response()->json(json_decode($response), 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function ref_poli_get()
    {

        $params = [];
        $endpoint = '/ref_poli';
        try {
            $response = BpjsHelper::getRequest($endpoint, $params);

            return response()->json(json_decode($response), 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function ref_dokter_get()
    {

        $params = [];
        $endpoint = '/ref_dokter';
        try {
            $response = BpjsHelper::getRequest($endpoint, $params);

            return response()->json(json_decode($response), 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
