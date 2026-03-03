<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\DataKodebooking;
use Illuminate\Http\Request; // Import class Request
use App\Helpers\BpjsHelper; // Import class BpjsHelper
use Illuminate\Support\Facades\Log; // Import class Log
use App\Http\Controllers\Controller; // Import class Controller
use Illuminate\Support\Facades\Validator; // Import class Validator
use Illuminate\Support\Facades\DB;

class TambahAntrianOnlineController extends Controller
{

    public function kodebooking_get()
    {
        set_time_limit(99999);
        $this->data_pending_kodebooking_get('QLJ');
        $this->data_pending_kodebooking_get('QLKP');
        $this->data_pending_kodebooking_get('QLTMG');
        // $this->addAntrians_otomatis('QLJ');
    }

    public function data_pending_kodebooking_get($urlQL = 'QLJ')
    {
        Log::info('===[data_pending_kodebooking_get - ' . $urlQL . '] ===');

        set_time_limit(10000);
        $params = [];
        $endpoint = '/data_pending_kodebooking';
        try {
            $response = BpjsHelper::getRequest($urlQL, $endpoint, $params);
            $decode_response = json_decode($response, true);

            if (isset($decode_response['metadata']['response']) && is_array($decode_response['metadata']['response'])) {
                Log::info('===[data_pending_kodebooking_get - ' . $urlQL . '] start.... please wait.... ===');
                foreach ($decode_response['metadata']['response'] as $data) {

                    $data_pending_kodebooking = [
                        'idpendaftaran' => $data['idpendaftaran'] ?? null,
                        'norm' => $data['norm'] ?? null,
                        'kodebooking' => $data['kodebooking'] ?? null,
                        'carabayar' => $data['carabayar'] ?? null,
                        'noantrian' => $data['noantrian'] ?? null,
                        'idjeniskunjungan' => $data['idjeniskunjungan'] ?? null,
                        'tanggalperiksa' => $data['tanggalperiksa'] ?? null,
                        'ispasienlama' => $data['ispasienlama'] ?? null,
                        'nojkn' => $data['nojkn'] ?? null,
                        'nik' => $data['nik'] ?? null,
                        'notelpon' => $data['notelpon'] ?? null,
                        'nomorreferensi' => $data['nomorreferensi'] ?? null,
                        'quota_jkn' => $data['quota_jkn'] ?? null,
                        'quota_jkn_sisa' => $data['quota_jkn_sisa'] ?? null,
                        'quota_nonjkn' => $data['quota_nonjkn'] ?? null,
                        'quota_nonjkn_sisa' => $data['quota_nonjkn_sisa'] ?? null,
                        'estimasidilayani' => $data['estimasidilayani'] ?? null,
                        'bpjs_kodedokter' => $data['bpjs_kodedokter'] ?? null,
                        'namadokter' => $data['namadokter'] ?? null,
                        'kodeunit' => $data['kodeunit'] ?? null,
                        'namaunit' => $data['namaunit'] ?? null,
                        'jammulai' => $data['jammulai'] ?? null,
                        'jamakhir' => $data['jamakhir'] ?? null,
                        'code' => $data['code'] ?? null,
                        'message' => $data['message'] ?? null,
                        'statuspemeriksaan' => $data['status'] ?? null,
                    ];

                    if (
                        in_array($data['code'], [200, 208]) ||
                        preg_match('/ sudah terbit SEP/', $data_pending_kodebooking['message'])
                    ) {
                        $data_pending_kodebooking['reupload'] = 0;
                    }
                    $data_Kodebooking = new DataKodebooking([], $urlQL);
                    $existingData = $data_Kodebooking->where('kodebooking', $data_pending_kodebooking['kodebooking'])->first();

                    if (!$existingData || $existingData->reupload !== 0) {
                        $saveUpdateData = $data_Kodebooking->updateOrCreate(
                            [
                                'kodebooking' => $data_pending_kodebooking['kodebooking'],
                            ],
                            $data_pending_kodebooking
                        );
                        Log::info('[data_pending_kodebooking_get - ' . $urlQL . '] saveUpdateData - '.$data_pending_kodebooking['kodebooking']);
                    } else {
                        $existingData->update([
                            'statuspemeriksaan' => $data_pending_kodebooking['statuspemeriksaan']
                        ]);
                        $saveUpdateData = $existingData;
                        Log::info('[data_pending_kodebooking_get - ' . $urlQL . '] update statuspemeriksaan - '.$data_pending_kodebooking['kodebooking']);
                    }
                    // $saveUpdateData = DataKodebooking::updateOrCreate(
                    //     [
                    //         'idpendaftaran' => $data_pending_kodebooking['idpendaftaran'],
                    //     ],
                    //     $data_pending_kodebooking
                    // );

                    if ($saveUpdateData && $saveUpdateData instanceof \Illuminate\Database\Eloquent\Model) {
                        Log::info('[data_pending_kodebooking_get - ' . $urlQL . '] saved successfully :', $saveUpdateData->toArray());
                    } else {
                        Log::error('[data_pending_kodebooking_get - ' . $urlQL . '] Error: not saved correctly.');
                    }
                }
                $this->addAntrians_otomatis($urlQL);
                Log::info('===[data_pending_kodebooking_get - ' . $urlQL . '] horray, process finished ===');
            }
            return response()->json($decode_response, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function nomor_rekon_post(Request $request)
    {
        $urlQL = 'QLJ';
        Log::info('[nomor_rekon_get] method reached');
        $validator = Validator::make($request->all(), [
            'bulan' => 'required|numeric',
            'tahun' => 'required|numeric',
            'nokartu' => 'required|string',
            'filter' => 'required|numeric' // 1: tanggal entri, 2: tanggal rencana kontrol
        ]);

        // Cek validasi gagal
        if ($validator->fails()) {
            Log::error('[nomor_rekon_post] Validation errors:', $validator->errors()->toArray());
            return response()->json([
                'metadata' => [
                    'message' => 'Parameter tidak valid',
                    'code' => 422,
                ],
                'errors' => $validator->errors(),
            ], 422);
        }

        $endpoint = '/nomor_rekon';
        try {
            $response = BpjsHelper::postRequest($urlQL, $endpoint, $request->all());
            $response_decode = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'metadata' => [
                        'code' => 500,
                        'message' => '[nomor_rekon_post]  Terjadi kesalahan pada decoding respons.',
                    ],
                ], 500);
            };

            if (is_array($response_decode) && !empty($response_decode)) {
                Log::info('[nomor_rekon_post] Antrian added successfully :', $response_decode);
            } else {
                Log::error('[nomor_rekon_post] Error: Antrian was not saved correctly.');
            }
            return response()->json($response_decode, 200, [], JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            // Log::error('[nomor_rekon_post] Failed to add :', ['error' => $e->getMessage()]);
            return response()->json([
                'metadata' => [
                    'code' => 500,
                    'message' => 'Terjadi kesalahan pada server.',
                ],
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function addAntrians_otomatis($urlQL = 'QLJ')
    {
        set_time_limit(10000);
        try {
            $data_Kodebooking = new DataKodebooking([], $urlQL);
            $today = Carbon::now('Asia/Jakarta')->toDateString();
            $yesterday = Carbon::yesterday('Asia/Jakarta')->toDateString();
            // $today = '2025-01-16';
            Log::info('=== [addAntrians_otomatis - ' . $urlQL . '] tanggal hari ini = '.$today.' ===');
            // $dataArray = $data_Kodebooking->where('kodebooking', '20241108539')->get()->toArray(); // spesific kodebooking
            $dataArray = $data_Kodebooking
                ->where('reupload', 1)
                ->where('tanggalperiksa', $today)
                // ->where('tanggalperiksa', '=','2025-11-26')
                //->whereBetween('tanggalperiksa', [$yesterday, $today])
                // ->where('statuspemeriksaan', '!=', 'batal')
                ->get()
                ->toArray();

            if (isset($dataArray) && is_array($dataArray)) {
                Log::info('=== [addAntrians_otomatis - ' . $urlQL . '] isset && is_array with total '.count($dataArray).' data ===');
                foreach ($dataArray as $data) {
                    $this->addAntrians_single_arr($urlQL, $data);
                }
                Log::info('=== [addAntrians_otomatis - ' . $urlQL . '] horray... processes finished ===');
            }

            return response()->json([
                'metadata' => [
                    'code' => 200,
                    'message' => 'Pengiriman addAntrians_otomatis sukses.',
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('[addAntrians_otomatis - ' . $urlQL . '] Failed to add addAntrians_otomatis:', ['error' => $e->getMessage()]);
            return response()->json([
                'metadata' => [
                    'code' => 500,
                    'message' => 'Terjadi kesalahan pada decoding respons.',
                ],
            ], 500);
        }
    }

    public function addAntrians_single_arr($urlQL = 'QLJ', array $arr)
    {
        Log::info('[addAntrians_single_arr - ' . $urlQL . '] arr  ', $arr);

        $notelpon = $arr['notelpon'] ?? null;
        // ===== [START] memperbaiki penulisan notelpon =====
         // Hilangkan semua karakter non-digit
        $notelpon = preg_replace('/[^\d]/', '', $notelpon);
        // Jika notelpon dimulai dengan "62" (kode negara Indonesia), ubah menjadi "0"
        if (substr($notelpon, 0, 2) === '62') {
            $notelpon = '0' . substr($notelpon, 2);
        }
        // Jika notelpon dimulai dengan "8" langsung, tambahkan "0" di depan
        if (substr($notelpon, 0, 1) === '8') {
            $notelpon = '0' . $notelpon;
        }
        // jika lebih dari 13 digit
        if (strlen($notelpon) > 13) {
            // Jika lebih dari 13 digit, ambil 13 digit pertama
            $notelpon = substr($notelpon, 0, 13);
        }
        // ===== [END] memperbaiki penulisan notelpon =====

        $data_addAntrians = [
            "kodebooking" => $arr['kodebooking'],
            "jenispasien" => (($arr['carabayar'] == "jknpbi" or $arr['carabayar'] == "jknnonpbi") ? "JKN" : "NON JKN"),
            "nomorkartu" => $arr['nojkn'],
            "nik" => $arr['nik'],
            "nohp" => $notelpon,
            "kodepoli" => $arr['kodeunit'],  //(($arr['kodeunit'] == 'IGD') ? 'UMU' : $arr['kodeunit']),
            "namapoli" => $arr['namaunit'],
            "pasienbaru" => (($arr['ispasienlama'] == 0) ? 1 : 0),
            "norm" => $arr['norm'],
            "tanggalperiksa" => $arr['tanggalperiksa'],
            "kodedokter" => $arr['bpjs_kodedokter'],
            "namadokter" => $arr['namadokter'],
            "jampraktek" => date('H:i', strtotime($arr['jammulai'])) . '-' . date('H:i', strtotime($arr['jamakhir'])),
            "jeniskunjungan" => ($arr['idjeniskunjungan'] == 5) ? 3 : $arr['idjeniskunjungan'], // --> 1 (Rujukan FKTP), 2 (Rujukan Internal), 3 (Kontrol), 4 (Rujukan Antar RS)
            "nomorreferensi" => $arr['nomorreferensi'],
            "nomorantrean" => $arr['noantrian'],
            "angkaantrean" => $arr['noantrian'],
            "estimasidilayani" => $arr['estimasidilayani'],
            "sisakuotajkn" => $arr['quota_jkn_sisa'],
            "kuotajkn" => $arr['quota_jkn'],
            "sisakuotanonjkn" => $arr['quota_nonjkn_sisa'],
            "kuotanonjkn" => $arr['quota_nonjkn'],
            "keterangan" => "Peserta harap 30 menit lebih awal guna pencatatan administrasi.",
        ];

        // Validasi data yang diterima
        $validator = Validator::make($data_addAntrians, [
            'kodebooking' => 'required|string',
            'jenispasien' => 'required|string',
            'nomorkartu' => 'nullable|string',
            'nik' => 'required|string',
            'nohp' => 'required|string',
            'kodepoli' => 'required|string',
            'namapoli' => 'required|string',
            'pasienbaru' => 'required|integer',
            'norm' => 'required|string',
            'tanggalperiksa' => 'required|date',
            'kodedokter' => 'required|numeric',
            'namadokter' => 'required|string',
            'jampraktek' => 'required|string',
            'jeniskunjungan' => 'required|integer',
            'nomorreferensi' => 'nullable|string',
            'nomorantrean' => 'required|numeric',
            'angkaantrean' => 'required|integer',
            'estimasidilayani' => 'required|integer',
            'sisakuotajkn' => 'required|integer',
            'kuotajkn' => 'required|integer',
            'sisakuotanonjkn' => 'required|integer',
            'kuotanonjkn' => 'required|integer',
            'keterangan' => 'required|string',
        ]);

        if ($validator->fails()) {
            Log::error('[addAntrians_single_arr - ' . $urlQL . '] Validation errors:', $validator->errors()->toArray());
            $conditions = [
                'kodebooking' => $data_addAntrians['kodebooking'],
                'norm' => $arr['norm']
            ];

            $data_addAntrians['code'] = '442';
            $data_addAntrians['message'] = $validator->errors()->toArray();

            $data_Kodebooking = new DataKodebooking([], $urlQL);
            $addAntrians = $data_Kodebooking->updateOrCreate(
                $conditions,
                $data_addAntrians
            );

            return response()->json([
                'metadata' => [
                    'message' => 'Parameter tidak valid',
                    'code' => 422,
                ],
                'errors' => $validator->errors(),
            ], 422);
        };

        $endpoint = '/tambah_antrianonline';
        try {
            $response = BpjsHelper::postRequest($urlQL, $endpoint, $data_addAntrians);
            $response_decode = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON decoding error:', ['response' => $response]);
                return response()->json([
                    'metadata' => [
                        'code' => 500,
                        'message' => 'Terjadi kesalahan pada decoding respons.',
                    ],
                ], 500);
            }
            Log::info('addAntrians_single_arr response_decode ', $response_decode);

            $data_kodebooking['request'] = json_encode($data_addAntrians);
            $data_kodebooking['response'] = json_encode($response_decode);
            $data_kodebooking['code'] = $response_decode['metadata']['code'] ?? null;
            $data_kodebooking['message'] = $response_decode['metadata']['message'] ?? null; // Menyimpan message

            // // cek kondisi
            // // jika data nomorreferensi  belum sesuai.
            if (
                $arr['idjeniskunjungan'] == 3 &&
                (preg_match('/^(Rujukan tidak valid|data nomorreferensi  belum sesuai\.)$/', $data_kodebooking['message']) ||
                    preg_match('/^(data nomorreferensi  belum sesuai.\.)$/', $data_kodebooking['message']))
            ) {
                Log::info('[addAntrians_single_arr - ' . $urlQL . '] - perbaikan jenis kunjungan = 3 dan data nomorreferensi  belum sesuai. noRM => '.$arr['norm']);

                $date = Carbon::parse($arr['tanggalperiksa']);
                $bulan = $date->format('m');
                $tahun = $date->format('Y');
                $nomorreferensi_response = $this->nomor_rekon_arr($urlQL, $bulan, $tahun, $arr['nojkn'], 2);
                if (isset($nomorreferensi_response['metadata']['response']['metaData']['code'])) {
                    $nomorreferensi_response_code = $nomorreferensi_response['metadata']['response']['metaData']['code'];
                    if ($nomorreferensi_response_code == 200) {
                        if (isset($nomorreferensi_response['metadata']['response']['response']['list'][0]['noSuratKontrol'])) {
                            $responseList = $nomorreferensi_response['metadata']['response']['response']['list'];
                            $tanggalPeriksa = $arr['tanggalperiksa'];
                            $kodeUnit = $arr['kodeunit'];

                            $filteredResult = array_filter($responseList, function ($item) use ($tanggalPeriksa, $kodeUnit) {
                                return isset($item['tglRencanaKontrol']) &&
                                    $item['tglRencanaKontrol'] === $tanggalPeriksa &&
                                    isset($item['poliTujuan']) &&
                                    $item['poliTujuan'] === $kodeUnit; // Tambahkan syarat untuk poliTujuan
                            });

                            if (!empty($filteredResult)) {
                                $noSuratKontrol = array_values($filteredResult)[0]['noSuratKontrol'];
                                $data_addAntrians['nomorreferensi'] = $noSuratKontrol;
                                Log::info($urlQL . ' Nomor Surat Kontrol yang diperoleh: ' . $noSuratKontrol);

                                //Kirim Ulang
                                $response = BpjsHelper::postRequest($urlQL, $endpoint, $data_addAntrians);
                                $response_decode = json_decode($response, true);

                                $data_kodebooking['request'] = json_encode($data_addAntrians);
                                $data_kodebooking['response'] = json_encode($response_decode);
                                $data_kodebooking['code'] = $response_decode['metadata']['code'] ?? null;
                                $data_kodebooking['message'] = $response_decode['metadata']['message'] ?? null; // Menyimpan message

                            } else {
                                Log::info($urlQL . " Data Surat Kontrol dengan tglRencanaKontrol {$tanggalPeriksa} tidak ditemukan.");
                            }
                        } else {
                            Log::error('Data noSuratKontrol tidak ditemukan dalam response.');
                        }
                    }
                }
            }

            if (
                $arr['idjeniskunjungan'] == 1 &&
                preg_match('/^Rujukan untuk tanggal .* tidak valid \/ masa berlaku habis$/', $data_kodebooking['message'])
            ) {
                $data_addAntrians['jeniskunjungan'] = 3;
                Log::info('[addAntrians_single_arr - ' . $urlQL . '] - perbaikan data kunjugan = 1 dan Rujukan untuk tanggal * tidak valid, jeniskunjungan ==> '. $data_addAntrians['jeniskunjungan']);
                //Kirim Ulang
                $response = BpjsHelper::postRequest($urlQL, $endpoint, $data_addAntrians);
                $response_decode = json_decode($response, true);

                $data_kodebooking['request'] = json_encode($data_addAntrians);
                $data_kodebooking['response'] = json_encode($response_decode);
                $data_kodebooking['code'] = $response_decode['metadata']['code'] ?? null;
                $data_kodebooking['message'] = $response_decode['metadata']['message'] ?? null; // Menyimpan message
            }

            if (
                $arr['idjeniskunjungan'] == 3 &&
                preg_match('/^Rujukan tidak valid$/', $data_kodebooking['message'])
            ) {
                $data_addAntrians['jeniskunjungan'] = 1;
                Log::info('[addAntrians_single_arr - ' . $urlQL . '] - perbaikan data kunjungan = 3 dan Rujukan tidak valid , jeniskunjungan ==> '. $data_addAntrians['jeniskunjungan']);
                //Kirim Ulang
                $response = BpjsHelper::postRequest($urlQL, $endpoint, $data_addAntrians);
                $response_decode = json_decode($response, true);

                $data_kodebooking['request'] = json_encode($data_addAntrians);
                $data_kodebooking['response'] = json_encode($response_decode);
                $data_kodebooking['code'] = $response_decode['metadata']['code'] ?? null;
                $data_kodebooking['message'] = $response_decode['metadata']['message'] ?? null; // Menyimpan message
            }

            if (
                in_array($response_decode['metadata']['code'], [200, 208]) ||
                preg_match('/ sudah terbit SEP/', $data_kodebooking['message'])
            ) {
                $data_kodebooking['reupload'] = 0;
            }

            $conditions = [
                'kodebooking' => $data_addAntrians['kodebooking'],
                'norm' => $arr['norm']
            ];

            if ($response_decode['metadata']['code'] == '208') {
                $conditions['idpendaftaran'] = $arr['idpendaftaran'];
            }
            $data_Kodebooking = new DataKodebooking([], $urlQL);
            $addAntrians = $data_Kodebooking->updateOrCreate(
                $conditions,
                $data_kodebooking
            );

            if ($addAntrians && $addAntrians instanceof \Illuminate\Database\Eloquent\Model) {
                Log::info('addAntrians_single_arr saved successfully :', $addAntrians->toArray());
            } else {
                Log::error('Error: addAntrians_single_arr was not saved correctly.');
            }

            return response()->json($response_decode, 200, [], JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            Log::error('Failed to add addAntrians_single_arr:', ['error' => $e->getMessage()]);
            return response()->json([
                'metadata' => [
                    'code' => 500,
                    'message' => 'Terjadi kesalahan pada server.',
                ],
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function addAntrians_single_request(Request $arr)
    {
        Log::info('addAntrians_single_request method reached');
        $data_addAntrians = [
            "kodebooking" => $arr['kodebooking'],
            "jenispasien" => (($arr['carabayar'] == "jknpbi" or $arr['carabayar'] == "jknnonpbi") ? "JKN" : "NON JKN"),
            "nomorkartu" => $arr['nojkn'],
            "nik" => $arr['nik'],
            "nohp" => $arr['notelpon'],
            "kodepoli" => $arr['kodeunit'],
            "namapoli" => $arr['namaunit'],
            "pasienbaru" => (($arr['ispasienlama'] == 0) ? 1 : 0),
            "norm" => $arr['norm'],
            "tanggalperiksa" => $arr['tanggalperiksa'],
            "kodedokter" => $arr['bpjs_kodedokter'],
            "namadokter" => $arr['namadokter'],
            "jampraktek" => date('H:i', strtotime($arr['jammulai'])) . '-' . date('H:i', strtotime($arr['jamakhir'])),
            "jeniskunjungan" => $arr['idjeniskunjungan'], // --> 1 (Rujukan FKTP), 2 (Rujukan Internal), 3 (Kontrol), 4 (Rujukan Antar RS)
            "nomorreferensi" => $arr['nomorreferensi'],
            "nomorantrean" => $arr['noantrian'],
            "angkaantrean" => $arr['noantrian'],
            "estimasidilayani" => $arr['estimasidilayani'],
            "sisakuotajkn" => $arr['quota_jkn_sisa'],
            "kuotajkn" => $arr['quota_jkn'],
            "sisakuotanonjkn" => $arr['quota_nonjkn_sisa'],
            "kuotanonjkn" => $arr['quota_nonjkn'],
            "keterangan" => "Peserta harap 30 menit lebih awal guna pencatatan administrasi.",
        ];
        $validator = Validator::make($data_addAntrians, [
            'kodebooking' => 'required|string',
            'jenispasien' => 'required|string',
            'nomorkartu' => 'required|string',
            'nik' => 'required|string',
            'nohp' => 'required|string',
            'kodepoli' => 'required|string',
            'namapoli' => 'required|string',
            'pasienbaru' => 'required|integer',
            'norm' => 'required|string',
            'tanggalperiksa' => 'required|date',
            'kodedokter' => 'required|numeric',
            'namadokter' => 'required|string',
            'jampraktek' => 'required|string',
            'jeniskunjungan' => 'required|integer',
            'nomorreferensi' => 'nullable|string',
            'nomorantrean' => 'required|string',
            'angkaantrean' => 'required|integer',
            'estimasidilayani' => 'required|integer',
            'sisakuotajkn' => 'required|integer',
            'kuotajkn' => 'required|integer',
            'sisakuotanonjkn' => 'required|integer',
            'kuotanonjkn' => 'required|integer',
            'keterangan' => 'required|string',
        ]);

        // Jika validasi gagal, kembalikan respons kesalahan
        if ($validator->fails()) {
            Log::error('[addAntrians_single_request] Validation errors:', $validator->errors()->toArray());
            return response()->json([
                'metadata' => [
                    'message' => 'Parameter tidak valid',
                    'code' => 422,
                ],
                'errors' => $validator->errors(),
            ], 422);
        }

        $endpoint = '/tambah_antrianonline';

        try {
            $response = BpjsHelper::postRequest('QLJ', $endpoint, $data_addAntrians);
            $response_decode = json_decode($response);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'metadata' => [
                        'code' => 500,
                        'message' => 'Terjadi kesalahan pada decoding respons.',
                    ],
                ], 500);
            };

            // Menyimpan status, request, dan response
            $data_kodebooking['request'] = json_encode($data_addAntrians);
            $data_kodebooking['response'] = json_encode($response_decode);
            $data_kodebooking['code'] = $response_decode['metadata']['code'] ?? null;
            $data_kodebooking['message'] = $response_decode['metadata']['message'] ?? null; // Menyimpan message

            if (preg_match('/ sudah terbit SEP/', $data_kodebooking['message'])) {
                $data_kodebooking['reupload'] = 0;
            }

            $addAntrians = DataKodebooking::updateOrCreate(
                ['kodebooking' => $data_addAntrians['kodebooking']],
                $data_kodebooking
            );

            if ($addAntrians && $addAntrians instanceof \Illuminate\Database\Eloquent\Model) {
                Log::info('Antrian added:', $addAntrians->toArray());
            } else {
                Log::error('Error: Antrian was not saved correctly.');
            }
            return response()->json($response_decode, 200, [], JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            Log::error('Failed to add addAntrians_single_request:', ['error' => $e->getMessage()]);
            return response()->json([
                'metadata' => [
                    'code' => 500,
                    'message' => 'Terjadi kesalahan pada server.',
                ],
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function addAntrians(Request $request)
    {
        set_time_limit(1000);
        $urlQL = 'QLJ';
        Log::info('[addAntrians] method reached');
        $validator = Validator::make($request->all(), [
            'kodebooking' => 'required|string',
            'jenispasien' => 'required|string',
            'nomorkartu' => 'required|string',
            'nik' => 'required|string',
            'nohp' => 'required|string',
            'kodepoli' => 'required|string',
            'namapoli' => 'required|string',
            'pasienbaru' => 'required|integer',
            'norm' => 'required|string',
            'tanggalperiksa' => 'required|date',
            'kodedokter' => 'required|numeric',
            'namadokter' => 'required|string',
            'jampraktek' => 'required|string',
            'jeniskunjungan' => 'required|integer',
            'nomorreferensi' => 'nullable|string',
            'nomorantrean' => 'required|numeric',
            'angkaantrean' => 'required|integer',
            'estimasidilayani' => 'required|integer',
            'sisakuotajkn' => 'required|integer',
            'kuotajkn' => 'required|integer',
            'sisakuotanonjkn' => 'required|integer',
            'kuotanonjkn' => 'required|integer',
            'keterangan' => 'required|string',
        ]);

        // Cek validasi gagal
        if ($validator->fails()) {
            Log::error('[addAntrians] Validation errors:', $validator->errors()->toArray());
            return response()->json([
                'metadata' => [
                    'message' => 'Parameter tidak valid',
                    'code' => 422,
                ],
                'errors' => $validator->errors(),
            ], 422);
        }

        // Cek kontisi untuk "data nomorreferensi  belum sesuai."
        if ($request->jeniskunjungan == 3 && $request->nomorreferensi == 0) {
            // perbaikan data nomor referensi
            $date = Carbon::parse($request->tanggalperiksa);
            $bulan = $date->format('m');
            $tahun = $date->format('Y');
            $nomorreferensi_response = $this->nomor_rekon($urlQL, $request, $bulan, $tahun, $request->nomorkartu, 2);

            if (isset($nomorreferensi_response['metadata']['response']['response']['list'][0]['noSuratKontrol'])) {
                $responseList = $nomorreferensi_response['metadata']['response']['response']['list'];
                $tanggalPeriksa = $request->tanggalperiksa;
                $filteredResult = array_filter($responseList, function ($item) use ($tanggalPeriksa) {
                    return isset($item['tglRencanaKontrol']) && $item['tglRencanaKontrol'] === $tanggalPeriksa;
                });
                if (!empty($filteredResult)) {
                    $noSuratKontrol = array_values($filteredResult)[0]['noSuratKontrol'];
                    $request->merge(['nomorreferensi' => $noSuratKontrol]);
                    Log::info('Nomor Surat Kontrol yang diperoleh: ' . $noSuratKontrol);
                } else {
                    Log::info("Data Surat Kontrol dengan tglRencanaKontrol {$tanggalPeriksa} tidak ditemukan.");
                }
            } else {
                Log::error('Data noSuratKontrol tidak ditemukan dalam response.');
            }
        }

        $endpoint = '/tambah_antrianonline';
        try {
            $response = BpjsHelper::postRequest($urlQL, $endpoint, $request->all());
            $response_decode = json_decode($response);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'metadata' => [
                        'code' => 500,
                        'message' => '[addAntrians] Terjadi kesalahan pada decoding respons.',
                    ],
                ], 500);
            };

            // Siapkan data untuk disimpan di database
            $data_addAntrians = $request->only([
                'kodebooking',
                'jenispasien',
                'nomorkartu',
                'nik',
                'nohp',
                'kodepoli',
                'namapoli',
                'pasienbaru',
                'norm',
                'tanggalperiksa',
                'kodedokter',
                'namadokter',
                'jampraktek',
                'jeniskunjungan',
                'nomorreferensi',
                'nomorantrean',
                'angkaantrean',
                'estimasidilayani',
                'sisakuotajkn',
                'kuotajkn',
                'sisakuotanonjkn',
                'kuotanonjkn',
                'keterangan'
            ]);

            $data_addAntrians['request'] = json_encode($data_addAntrians);
            $data_addAntrians['response'] = json_encode($response_decode);
            $data_addAntrians['code'] = $response_decode->metadata->code ?? null;
            $data_addAntrians['message'] = $response_decode->metadata->message ?? null;

            if (
                in_array($response_decode->metadata->code, [200, 208]) ||
                preg_match('/ sudah terbit SEP/', $data_addAntrians['message'])
            ) {
                $data_kodebooking['reupload'] = 0;
            }
            $data_Kodebooking = new DataKodebooking([], $urlQL);
            $addAntrians = $data_Kodebooking->updateOrCreate(
                ['kodebooking' => $data_addAntrians['kodebooking']],
                $data_addAntrians
            );

            if ($addAntrians instanceof \Illuminate\Database\Eloquent\Model) {
                Log::info('[addAntrians] Antrian added successfully :', $addAntrians->toArray());
            } else {
                Log::error('[addAntrians] Error: Antrian was not saved correctly.');
            }
            return response()->json($response_decode, 200, [], JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            Log::error('[addAntrians] Failed to add :', ['error' => $e->getMessage()]);
            return response()->json([
                'metadata' => [
                    'code' => 500,
                    'message' => 'Terjadi kesalahan pada server.',
                ],
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function nomor_rekon($urlQL, Request $request, $bulan, $tahun, $nokartu, $filter)
    {
        Log::info('[nomor_rekon] method reached');
        $data = array_merge($request->all(), [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'nokartu' => $nokartu,
            'filter' => $filter,
        ]);
        // Log::info('[nomor_rekon] data = '.json_encode($data));
        $endpoint = '/nomor_rekon';
        try {
            $response = BpjsHelper::postRequest($urlQL, $endpoint, $data);
            $response_decode = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'metadata' => [
                        'code' => 500,
                        'message' => '[nomor_rekon_post]  Terjadi kesalahan pada decoding respons.',
                    ],
                ], 500);
            };

            if (is_array($response_decode) && !empty($response_decode)) {
                Log::info('[nomor_rekon_post] Antrian added successfully :', $response_decode);
            } else {
                Log::error('[nomor_rekon_post] Error: Antrian was not saved correctly.');
            }
            return $response_decode;
        } catch (\Exception $e) {
            // Log::error('[nomor_rekon_post] Failed to add :', ['error' => $e->getMessage()]);
            return response()->json([
                'metadata' => [
                    'code' => 500,
                    'message' => 'Terjadi kesalahan pada server.',
                ],
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function nomor_rekon_arr($urlQL = 'QLJ', $bulan, $tahun, $nokartu, $filter)
    {
        Log::info('[nomor_rekon_arr - ' . $urlQL . '] method reached');
        $data = [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'nokartu' => $nokartu,
            'filter' => $filter,
        ];

        $endpoint = '/nomor_rekon';
        try {
            $response = BpjsHelper::postRequest($urlQL, $endpoint, $data);
            $response_decode = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'metadata' => [
                        'code' => 500,
                        'message' => '[nomor_rekon_arr - ' . $urlQL . ']  Terjadi kesalahan pada decoding respons.',
                    ],
                ], 500);
            };

            if (is_array($response_decode) && !empty($response_decode)) {
                Log::info('[nomor_rekon_arr - ' . $urlQL . '] Antrian added successfully :', $response_decode);
            } else {
                Log::error('[nomor_rekon_arr - ' . $urlQL . '] Error: Antrian was not saved correctly.');
            }
            return $response_decode;
        } catch (\Exception $e) {
            Log::error('[nomor_rekon_arr - ' . $urlQL . '] Failed to add :', ['error' => $e->getMessage()]);
            return response()->json([
                'metadata' => [
                    'code' => 500,
                    'message' => 'Terjadi kesalahan pada server.',
                ],
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Rekap data kode booking

    public function showMessages()
    {
        $tanggal = '2024-11-08'; // Tanggal yang ingin Anda filter
        $data = DataKodebooking::select('message', DB::raw('count(*) as total'))
            ->whereDate('tanggalperiksa', $tanggal)
            ->groupBy('message')
            ->get();

        return view('kodebooking.messages', compact('data'));
    }

    // Get data kode booking
    public function getDataKodeBooking(Request $request)
    {
        Log::info('Incoming request for getDataKodeBooking:', $request->all());

        // Mencari data antrian
        $data_kodebookingQuery = DataKodebooking::query();

        // Menambahkan filter jika ada
        if ($request->has('status')) {
            $data_kodebookingQuery->where('status', $request->input('status'));
        }

        // Pagination
        $perPage = $request->input('per_page', 25); // Default 10 per halaman
        $data_kodebookingQuery = DataKodebooking::query(); // Siapkan query
        $data_kodebookingData = $data_kodebookingQuery->paginate($perPage);

        // Log hasil paginasi
        // Log::info('AddAntrians data:', $data_kodebookingData->toArray());

        return response()->json([
            'metadata' => [
                'message' => 'Data antrian berhasil diambil',
                'code' => 200,
            ],
            'data' => $data_kodebookingData,

        ]);
    }
}
