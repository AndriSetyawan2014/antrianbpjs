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
use App\Jobs\SendKodeBookingBatchJob;

class TambahAntrianOnlineController extends Controller
{

    public function kodebooking_get()
    {
        set_time_limit(99999);
        $allowedQL = BpjsHelper::getUrlQLOptions();
        
        // --- Settingan Filter Tanggal (Edit di sini jika diperlukan) ---
        $dari    = Carbon::now()->toDateString();    // Hari ini
        $sampai  = Carbon::now()->addDay()->toDateString(); // Besok
        // --------------------------------------------------------------

        foreach ($allowedQL as $ql) {
            $this->data_pending_kodebooking_get($ql, null, $dari, $sampai);
        }
    }

    public function kodebooking_get_by_ql(Request $request)
    {
        set_time_limit(99999);

        $allowedQL = BpjsHelper::getUrlQLOptions();
        $urlQL = strtoupper($request->query('urlQL', $request->input('urlQL', '')));
        $tanggal = $request->query('tanggal', $request->input('tanggal', ''));
        $dari = $request->query('dari', $request->input('dari', ''));
        $sampai = $request->query('sampai', $request->input('sampai', ''));

        if (!in_array($urlQL, $allowedQL)) {
            return response()->json([
                'metadata' => [
                    'code'    => 422,
                    'message' => 'Parameter urlQL tidak valid. Nilai yang diizinkan: ' . implode(', ', $allowedQL),
                ],
            ], 422);
        }

        return $this->data_pending_kodebooking_get($urlQL, $tanggal, $dari, $sampai);
    }

    public function data_pending_kodebooking_get($urlQL = 'QLJ', $tanggal = null, $dari = null, $sampai = null)
    {
        Log::info('===[data_pending_kodebooking_get - ' . $urlQL . '] ===');

        set_time_limit(10000);
        $params = [];
        if ($tanggal) $params['tanggal'] = $tanggal;
        if ($dari) $params['dari'] = $dari;
        if ($sampai) $params['sampai'] = $sampai;
        $endpoint = '/data_pending_kodebooking';
        try {
            $response = BpjsHelper::getRequest($urlQL, $endpoint, $params);
            
            if ($response === null) {
                Log::error('[data_pending_kodebooking_get - ' . $urlQL . '] FAIL: BpjsHelper::getRequest returned NULL.');
                return response()->json([
                    'error' => 'Server SIRSTQL (' . $urlQL . ') tidak dapat dijangkau.',
                ], 503);
            }

            $decode_response = json_decode($response, true);
            
            Log::info('[data_pending_kodebooking_get - ' . $urlQL . '] Response received', [
                'data_count' => isset($decode_response['metadata']['response']) ? count($decode_response['metadata']['response']) : 0
            ]);

            if (isset($decode_response['metadata']['response']) && is_array($decode_response['metadata']['response'])) {
                $pendingData = $decode_response['metadata']['response'];
                $totalIncoming = count($pendingData);
                Log::info("=== [data_pending_kodebooking_get - $urlQL] === Processing $totalIncoming records...");

                $data_Kodebooking_model = (new DataKodebooking())->setTableByQL($urlQL);
                
                // Pra-penarikan (Pre-fetch) data yang sudah ada (berdasarkan kodebooking dan idpendaftaran)
                $existingData = $data_Kodebooking_model
                    ->whereIn('kodebooking', collect($pendingData)->pluck('kodebooking')->unique())
                    ->get()
                    ->keyBy(function($item) {
                        return $item->kodebooking . '_' . $item->idpendaftaran;
                    });

                $upsertData = [];
                foreach ($pendingData as $data) {
                    $key = ($data['kodebooking'] ?? '') . '_' . ($data['idpendaftaran'] ?? '');
                    
                    // Skip jika kodebooking dan idpendaftaran sudah sama (mencegah overwrite status lokal)
                    if ($existingData->has($key)) {
                        continue;
                    }

                    $item = [
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
                        'updated_at' => now(),
                    ];

                    $reupload = 1;
                    if (
                        in_array($data['code'], [200, 208]) ||
                        (isset($data['message']) && preg_match('/ sudah terbit SEP/', $data['message']))
                    ) {
                        $reupload = 0;
                    }
                    $item['reupload'] = $reupload;
                    $upsertData[] = $item;
                }

                if (!empty($upsertData)) {
                    $chunks = array_chunk($upsertData, 500);
                    foreach ($chunks as $chunk) {
                        $data_Kodebooking_model->upsert($chunk, ['kodebooking'], [
                            'idpendaftaran', 'norm', 'carabayar', 'noantrian', 'idjeniskunjungan', 'tanggalperiksa',
                            'ispasienlama', 'nojkn', 'nik', 'notelpon', 'nomorreferensi', 'quota_jkn', 'quota_jkn_sisa',
                            'quota_nonjkn', 'quota_nonjkn_sisa', 'estimasidilayani', 'bpjs_kodedokter', 'namadokter',
                            'kodeunit', 'namaunit', 'jammulai', 'jamakhir', 'code', 'message', 'statuspemeriksaan', 'reupload', 'updated_at'
                        ]);
                    }
                    Log::info("[data_pending_kodebooking_get - $urlQL] UPSERT_SUCCESS: Processed " . count($upsertData) . " / $totalIncoming records.");
                    
                    // Panggil otomatisasi antrean hanya jika ada data yang diproses
                    $this->addAntrians_otomatis($urlQL);
                } else {
                    Log::info("[data_pending_kodebooking_get - $urlQL] No new or eligible records to update.");
                }

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
        $urlQL = strtoupper($request->query('urlQL', $request->input('urlQL', 'QLJ')));
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
            $data_Kodebooking = (new DataKodebooking())->setTableByQL($urlQL);
            $today = Carbon::now('Asia/Jakarta')->toDateString();
            
            Log::info('=== [addAntrians_otomatis - ' . $urlQL . '] tanggal hari ini = '.$today.' ===');
            
            $dataArray = $data_Kodebooking
                ->where('reupload', 1)
                ->whereBetween('tanggalperiksa', [
                    Carbon::today()->subDays(1)->toDateString(),
                    Carbon::today()->toDateString() 
                ])
                ->get()
                ->toArray();

            if (!empty($dataArray)) {
                $totalRecords = count($dataArray);
                $chunks = array_chunk($dataArray, 30);
                $totalJobs = count($chunks);

                Log::info("[addAntrians_otomatis - $urlQL] Dispatching $totalRecords records into $totalJobs background jobs.");

                foreach ($chunks as $chunk) {
                    SendKodeBookingBatchJob::dispatch($urlQL, $chunk);
                }

                Log::info("=== [addAntrians_otomatis - $urlQL] ALL JOBS DISPATCHED. Process finished. ===");
            }

            return response()->json([
                'metadata' => [
                    'code' => 200,
                    'message' => 'OK. ' . count($dataArray) . ' records dispatched to background queue.',
                ],
            ], 200);
        } catch (\Exception $e) {
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
            'nik' => 'nullable|string',
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

            $data_Kodebooking = (new DataKodebooking())->setTableByQL($urlQL);
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

        $endpoint = '/antrean/add';
        try {
            $response = BpjsHelper::postRequestDirect($urlQL, $endpoint, $data_addAntrians);
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
                                $response = BpjsHelper::postRequestDirect($urlQL, $endpoint, $data_addAntrians);
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
                $response = BpjsHelper::postRequestDirect($urlQL, $endpoint, $data_addAntrians);
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
                $response = BpjsHelper::postRequestDirect($urlQL, $endpoint, $data_addAntrians);
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
            $data_Kodebooking = (new DataKodebooking())->setTableByQL($urlQL);
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

        $urlQL = strtoupper($arr->query('urlQL', $arr->input('urlQL', 'QLJ')));
        $endpoint = '/antrean/add';

        try {
            $response = BpjsHelper::postRequestDirect($urlQL, $endpoint, $data_addAntrians);
            $response_decode = json_decode($response, true);

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
        $urlQL = strtoupper($request->query('urlQL', $request->input('urlQL', 'QLJ')));
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

        $endpoint = '/antrean/add';
        try {
            $response = BpjsHelper::postRequestDirect($urlQL, $endpoint, $request->all());
            $response_decode = json_decode($response, true);

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
            $data_addAntrians['code'] = $response_decode['metadata']['code'] ?? null;
            $data_addAntrians['message'] = $response_decode['metadata']['message'] ?? null;

            if (
                in_array($response_decode['metadata']['code'], [200, 208]) ||
                preg_match('/ sudah terbit SEP/', $data_addAntrians['message'])
            ) {
                $data_kodebooking['reupload'] = 0;
            }
            $data_Kodebooking = (new DataKodebooking())->setTableByQL($urlQL);
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

    public function queue_status()
    {
        try {
            $total = \DB::table('jobs')->count();
            $failed = \DB::table('failed_jobs')->count();
            $detail = \DB::table('jobs')
                ->select('queue', \DB::raw('count(*) as total'))
                ->groupBy('queue')
                ->get();

            // Menghitung jumlah data aktual yang akan diproses dari payload tiap Job
            $totalDataDiproses = 0;
            $jobs = \DB::table('jobs')->get(['payload']);
            foreach ($jobs as $job) {
                $payload = json_decode($job->payload, true);
                if (isset($payload['data']['command'])) {
                    try {
                        $commandStr = $payload['data']['command'];
                        $command = unserialize($commandStr);
                        
                        if ($command instanceof \App\Jobs\SendKodeBookingBatchJob) {
                            $reflection = new \ReflectionClass($command);
                            $property = $reflection->getProperty('records');
                            $property->setAccessible(true);
                            $records = $property->getValue($command);
                            $totalDataDiproses += is_array($records) ? count($records) : 0;
                        } elseif ($command instanceof \App\Jobs\SendTaskIdBatchJob) {
                            $reflection = new \ReflectionClass($command);
                            $property = $reflection->getProperty('batchData');
                            $property->setAccessible(true);
                            $records = $property->getValue($command);
                            $totalDataDiproses += is_array($records) ? count($records) : 0;
                        }
                    } catch (\Exception $ex) {
                        // Skip if unserialize fails
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_antrean' => $total,
                    'total_gagal' => $failed,
                    'total_data_diproses' => $totalDataDiproses,
                    'detail' => $detail,
                    'server_time' => date('Y-m-d H:i:s')
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function queue_work_start()
    {
        try {
            $baseCommand = 'php artisan queue:work --queue=sync_lokal,default --tries=3 --timeout=600';
            
            if (PHP_OS_FAMILY === 'Windows') {
                // Perintah untuk Windows
                $command = "start /B php artisan queue:work --queue=sync_lokal,default --tries=3 --timeout=600 > nul 2>&1";
                pclose(popen($command, "r"));
            } else {
                // Perintah untuk Linux (Ubuntu)
                $phpPath = defined('PHP_BINARY') && PHP_BINARY ? PHP_BINARY : 'php';
                $artisan = base_path('artisan');
                $projectPath = base_path();
                
                // Gunakan perintah yang lebih eksplisit
                $command = "cd $projectPath && nohup $phpPath $artisan queue:work --queue=sync_lokal,default --tries=3 --timeout=600 > /dev/null 2>&1 &";
                shell_exec($command);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Queue worker started in background (' . PHP_OS_FAMILY . ').',
                'command' => $command
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function queue_work_stop()
    {
        try {
            // Perintah restart akan memberitahu semua worker yang sedang berjalan untuk berhenti setelah job selesai
            \Artisan::call('queue:restart');

            return response()->json([
                'status' => 'success',
                'message' => 'Signal sent to stop all queue workers (queue:restart).',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function queue_clear()
    {
        try {
            // Membersihkan semua antrean di database
            \Artisan::call('queue:clear', ['--force' => true]);

            return response()->json([
                'status' => 'success',
                'message' => 'All queues have been cleared successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kirim Ulang satu Kode Booking ke BPJS Antrol secara langsung
     */
    public function run_kodebooking_by_kodebooking(Request $request)
    {
        set_time_limit(99999);

        $allowedQL = BpjsHelper::getUrlQLOptions();
        $urlQL = strtoupper($request->query('urlQL', $request->input('urlQL', 'QLJ')));
        $kodebooking = $request->input('kodebooking', $request->query('kodebooking'));

        if (!in_array($urlQL, $allowedQL)) {
            return response()->json([
                'metadata' => [
                    'code'    => 422,
                    'message' => 'Parameter urlQL tidak valid. Gunakan: ' . implode(', ', $allowedQL),
                ],
                'response' => null,
            ], 422);
        }

        if (empty($kodebooking)) {
            return response()->json([
                'metadata' => [
                    'code'    => 422,
                    'message' => 'Parameter kodebooking wajib diisi.',
                ],
                'response' => null,
            ], 422);
        }

        $model = (new DataKodebooking())->setTableByQL($urlQL);
        $record = $model->where('kodebooking', $kodebooking)->first();

        if (!$record) {
            return response()->json([
                'metadata' => [
                    'code'    => 404,
                    'message' => "Data Kode Booking {$kodebooking} tidak ditemukan pada cabang {$urlQL}.",
                ],
                'response' => null,
            ], 404);
        }

        $arr = $this->prepareKodebookingArr($record->toArray(), $urlQL);

        // Payload hasil edit user (opsional)
        $payload = $request->input('payload');
        if (is_string($payload)) $payload = json_decode($payload, true);
        if (is_array($payload)) {
            $arr = $this->applyKodebookingPayload($arr, $payload);
        }

        // Panggil pengiriman single
        $resp = $this->addAntrians_single_arr($urlQL, $arr);
        $respData = $resp->getData(true);

        $code = $respData['metadata']['code'] ?? ($respData['code'] ?? $resp->getStatusCode());
        $message = $respData['metadata']['message'] ?? ($respData['message'] ?? 'Proses pengiriman selesai.');
        if (!empty($respData['errors']) && is_array($respData['errors'])) {
            $detail = [];
            foreach ($respData['errors'] as $field => $msgs) {
                $detail[] = $field . ': ' . implode(', ', (array) $msgs);
            }
            $message .= ' (' . implode('; ', $detail) . ')';
        }

        return response()->json([
            'metadata' => [
                'code'    => (int) $code,
                'message' => "Kode Booking {$kodebooking}: " . $message,
            ],
            'response' => $respData['response'] ?? $respData,
        ], 200);
    }

    /**
     * Lengkapi field record dengan fallback dari request sebelumnya & default.
     */
    private function prepareKodebookingArr(array $arr, string $urlQL = 'QLJ'): array
    {
        // NIK / No JKN kosong: ambil dari data lain milik No RM yang sama
        if (!empty($arr['norm']) && (empty($arr['nik']) || empty($arr['nojkn']))) {
            $other = (new DataKodebooking())->setTableByQL($urlQL)
                ->where('norm', $arr['norm'])
                ->where(function ($q) {
                    $q->whereNotNull('nik')->where('nik', '!=', '');
                })
                ->orderByDesc('id')->first();
            if ($other) {
                if (empty($arr['nik'])) $arr['nik'] = $other->nik;
                if (empty($arr['nojkn']) && !empty($other->nojkn)) $arr['nojkn'] = $other->nojkn;
            }
        }

        // Fallback jika ada data dari request payload sebelumnya
        if (!empty($arr['request'])) {
            $prevReq = json_decode($arr['request'], true);
            if (is_array($prevReq)) {
                if (empty($arr['bpjs_kodedokter']) && !empty($prevReq['kodedokter'])) {
                    $arr['bpjs_kodedokter'] = $prevReq['kodedokter'];
                }
                if (empty($arr['kodeunit']) && !empty($prevReq['kodepoli'])) {
                    $arr['kodeunit'] = $prevReq['kodepoli'];
                }
                if (empty($arr['namaunit']) && !empty($prevReq['namapoli'])) {
                    $arr['namaunit'] = $prevReq['namapoli'];
                }
                if (empty($arr['namadokter']) && !empty($prevReq['namadokter'])) {
                    $arr['namadokter'] = $prevReq['namadokter'];
                }
                if (empty($arr['nojkn']) && !empty($prevReq['nomorkartu'])) {
                    $arr['nojkn'] = $prevReq['nomorkartu'];
                }
                if (empty($arr['nik']) && !empty($prevReq['nik'])) {
                    $arr['nik'] = $prevReq['nik'];
                }
                if (empty($arr['notelpon']) && !empty($prevReq['nohp'])) {
                    $arr['notelpon'] = $prevReq['nohp'];
                }
                if (empty($arr['noantrian']) && !empty($prevReq['nomorantrean'])) {
                    $arr['noantrian'] = $prevReq['nomorantrean'];
                }
                if (empty($arr['estimasidilayani']) && !empty($prevReq['estimasidilayani'])) {
                    $arr['estimasidilayani'] = $prevReq['estimasidilayani'];
                }
                if (empty($arr['nomorreferensi']) && !empty($prevReq['nomorreferensi'])) {
                    $arr['nomorreferensi'] = $prevReq['nomorreferensi'];
                }
                if (!isset($arr['quota_jkn']) && isset($prevReq['kuotajkn'])) {
                    $arr['quota_jkn'] = $prevReq['kuotajkn'];
                }
                if (!isset($arr['quota_jkn_sisa']) && isset($prevReq['sisakuotajkn'])) {
                    $arr['quota_jkn_sisa'] = $prevReq['sisakuotajkn'];
                }
                if (!isset($arr['quota_nonjkn']) && isset($prevReq['kuotanonjkn'])) {
                    $arr['quota_nonjkn'] = $prevReq['kuotanonjkn'];
                }
                if (!isset($arr['quota_nonjkn_sisa']) && isset($prevReq['sisakuotanonjkn'])) {
                    $arr['quota_nonjkn_sisa'] = $prevReq['sisakuotanonjkn'];
                }
                if (empty($arr['idjeniskunjungan']) && isset($prevReq['jeniskunjungan'])) {
                    $arr['idjeniskunjungan'] = $prevReq['jeniskunjungan'];
                }
                if (empty($arr['carabayar']) && !empty($prevReq['jenispasien'])) {
                    $arr['carabayar'] = (strtoupper($prevReq['jenispasien']) === 'JKN') ? 'jknpbi' : 'umum';
                }
                if (!isset($arr['ispasienlama']) && isset($prevReq['pasienbaru'])) {
                    $arr['ispasienlama'] = ($prevReq['pasienbaru'] == 1) ? 0 : 1;
                }
                if ((empty($arr['jammulai']) || empty($arr['jamakhir'])) && !empty($prevReq['jampraktek'])) {
                    $parts = explode('-', $prevReq['jampraktek']);
                    $arr['jammulai'] = $arr['jammulai'] ?: ($parts[0] ?? '08:00');
                    $arr['jamakhir'] = $arr['jamakhir'] ?: ($parts[1] ?? '12:00');
                }
            }
        }

        // Default fallbacks untuk field mandatory
        if (empty($arr['estimasidilayani'])) {
            $tgl = $arr['tanggalperiksa'] ?? date('Y-m-d');
            $jam = !empty($arr['jammulai']) ? $arr['jammulai'] : '08:00:00';
            $arr['estimasidilayani'] = (int) (strtotime($tgl . ' ' . $jam) * 1000);
        }
        if (!isset($arr['quota_jkn'])) $arr['quota_jkn'] = 30;
        if (!isset($arr['quota_jkn_sisa'])) $arr['quota_jkn_sisa'] = 10;
        if (!isset($arr['quota_nonjkn'])) $arr['quota_nonjkn'] = 30;
        if (!isset($arr['quota_nonjkn_sisa'])) $arr['quota_nonjkn_sisa'] = 10;
        if (!isset($arr['ispasienlama'])) $arr['ispasienlama'] = 1;
        if (empty($arr['noantrian'])) $arr['noantrian'] = 1;
        if (empty($arr['jammulai'])) $arr['jammulai'] = '08:00';
        if (empty($arr['jamakhir'])) $arr['jamakhir'] = '12:00';

        return $arr;
    }

    /**
     * Susun payload BPJS /antrean/add dari array record (untuk preview & edit user).
     */
    private function buildKodebookingPayload(array $arr): array
    {
        $notelpon = preg_replace('/[^\d]/', '', $arr['notelpon'] ?? '');
        if (substr($notelpon, 0, 2) === '62') $notelpon = '0' . substr($notelpon, 2);
        if (substr($notelpon, 0, 1) === '8') $notelpon = '0' . $notelpon;
        $notelpon = substr($notelpon, 0, 13);

        return [
            'kodebooking'      => $arr['kodebooking'] ?? null,
            'jenispasien'      => (in_array($arr['carabayar'] ?? '', ['jknpbi', 'jknnonpbi'])) ? 'JKN' : 'NON JKN',
            'nomorkartu'       => $arr['nojkn'] ?? null,
            'nik'              => $arr['nik'] ?? null,
            'nohp'             => $notelpon,
            'kodepoli'         => $arr['kodeunit'] ?? null,
            'namapoli'         => $arr['namaunit'] ?? null,
            'pasienbaru'       => (($arr['ispasienlama'] ?? 1) == 0) ? 1 : 0,
            'norm'             => $arr['norm'] ?? null,
            'tanggalperiksa'   => $arr['tanggalperiksa'] ?? null,
            'kodedokter'       => $arr['bpjs_kodedokter'] ?? null,
            'namadokter'       => $arr['namadokter'] ?? null,
            'jampraktek'       => date('H:i', strtotime($arr['jammulai'])) . '-' . date('H:i', strtotime($arr['jamakhir'])),
            'jeniskunjungan'   => (($arr['idjeniskunjungan'] ?? 0) == 5) ? 3 : ($arr['idjeniskunjungan'] ?? null),
            'nomorreferensi'   => $arr['nomorreferensi'] ?? null,
            'nomorantrean'     => $arr['noantrian'] ?? null,
            'angkaantrean'     => $arr['noantrian'] ?? null,
            'estimasidilayani' => $arr['estimasidilayani'] ?? null,
            'sisakuotajkn'     => $arr['quota_jkn_sisa'] ?? null,
            'kuotajkn'         => $arr['quota_jkn'] ?? null,
            'sisakuotanonjkn'  => $arr['quota_nonjkn_sisa'] ?? null,
            'kuotanonjkn'      => $arr['quota_nonjkn'] ?? null,
            'keterangan'       => 'Peserta harap 30 menit lebih awal guna pencatatan administrasi.',
        ];
    }

    /**
     * Terapkan payload hasil edit user kembali ke array record.
     */
    private function applyKodebookingPayload(array $arr, array $p): array
    {
        $map = [
            'bpjs_kodedokter' => 'kodedokter', 'namadokter' => 'namadokter',
            'kodeunit' => 'kodepoli', 'namaunit' => 'namapoli',
            'nojkn' => 'nomorkartu', 'nik' => 'nik', 'notelpon' => 'nohp',
            'norm' => 'norm', 'tanggalperiksa' => 'tanggalperiksa',
            'idjeniskunjungan' => 'jeniskunjungan', 'nomorreferensi' => 'nomorreferensi',
            'noantrian' => 'nomorantrean', 'estimasidilayani' => 'estimasidilayani',
            'quota_jkn_sisa' => 'sisakuotajkn', 'quota_jkn' => 'kuotajkn',
            'quota_nonjkn_sisa' => 'sisakuotanonjkn', 'quota_nonjkn' => 'kuotanonjkn',
        ];
        foreach ($map as $field => $key) {
            if (array_key_exists($key, $p)) $arr[$field] = $p[$key];
        }
        if (isset($p['jenispasien'])) {
            $arr['carabayar'] = (strtoupper($p['jenispasien']) === 'JKN') ? 'jknpbi' : 'umum';
        }
        if (isset($p['pasienbaru'])) {
            $arr['ispasienlama'] = ($p['pasienbaru'] == 1) ? 0 : 1;
        }
        if (!empty($p['jampraktek']) && str_contains($p['jampraktek'], '-')) {
            [$arr['jammulai'], $arr['jamakhir']] = array_map('trim', explode('-', $p['jampraktek'], 2));
        }
        return $arr;
    }

    /**
     * Preview payload Kode Booking sebelum dikirim ulang.
     */
    public function preview_kodebooking(Request $request)
    {
        $allowedQL = BpjsHelper::getUrlQLOptions();
        $urlQL = strtoupper($request->input('urlQL', 'QLJ'));
        $kodebooking = $request->input('kodebooking');

        if (!in_array($urlQL, $allowedQL) || empty($kodebooking)) {
            return response()->json(['metadata' => ['code' => 422, 'message' => 'Parameter urlQL / kodebooking tidak valid.'], 'response' => null], 422);
        }
        $record = (new DataKodebooking())->setTableByQL($urlQL)->where('kodebooking', $kodebooking)->first();
        if (!$record) {
            return response()->json(['metadata' => ['code' => 404, 'message' => 'Data tidak ditemukan.'], 'response' => null], 404);
        }
        $arr = $this->prepareKodebookingArr($record->toArray(), $urlQL);

        $prev = json_decode($record->response ?? '', true);
        return response()->json([
            'metadata' => ['code' => 200, 'message' => 'OK'],
            'response' => $this->buildKodebookingPayload($arr),
            'previous' => [
                'code'    => $prev['metadata']['code'] ?? $record->code,
                'message' => $prev['metadata']['message'] ?? $record->message,
            ],
        ]);
    }

    /**
     * Kirim Ulang Massal (Batch) Kode Booking yang Pending atau Error ke Background Queue
     */
    public function run_kodebooking_batch(Request $request)
    {
        set_time_limit(99999);

        $allowedQL = BpjsHelper::getUrlQLOptions();
        $urlQL = strtoupper($request->query('urlQL', $request->input('urlQL', 'QLJ')));
        $startDate = $request->input('start_date', $request->query('start_date')) ?: date('Y-m-d');
        $endDate = $request->input('end_date', $request->query('end_date', $startDate)) ?: $startDate;

        if (!in_array($urlQL, $allowedQL)) {
            return response()->json([
                'metadata' => [
                    'code'    => 422,
                    'message' => 'Parameter urlQL tidak valid. Gunakan: ' . implode(', ', $allowedQL),
                ],
                'response' => null,
            ], 422);
        }

        $query = (new DataKodebooking())->setTableByQL($urlQL)->newQuery();

        $query->whereDate('tanggalperiksa', '>=', $startDate)
              ->whereDate('tanggalperiksa', '<=', $endDate);

        // Ambil data yang pending (code == 0) atau error (code != 200 & 208) dan bukan duplikasi / sudah terbit SEP
        $records = $query->where(function ($q) {
            $q->whereNotIn('code', [200, 208])
              ->orWhereNull('code')
              ->orWhere('reupload', 1);
        })->where(function ($q) {
            $q->whereNull('message')
              ->orWhere(function ($sub) {
                  $sub->where('message', 'not like', '%duplikasi%')
                      ->where('message', 'not like', '%sudah terbit SEP%');
              });
        })->orderByDesc('id')->get();

        // Deduplikasi berdasarkan kodebooking (ambil baris terbaik yang sudah diurutkan)
        $records = $records->unique('kodebooking')->values();

        $count = $records->count();

        if ($count === 0) {
            return response()->json([
                'metadata' => [
                    'code'    => 200,
                    'message' => 'Tidak ada data Kode Booking pending/error yang perlu dikirim ulang pada rentang tanggal tersebut.',
                ],
                'response' => ['count' => 0],
            ], 200);
        }

        $recordsArray = array_map(function ($rec) use ($urlQL) {
            return $this->prepareKodebookingArr($rec, $urlQL);
        }, $records->toArray());

        $kodebookings = collect($recordsArray)->pluck('kodebooking')->filter()->unique()->values();

        foreach ($kodebookings->chunk(500) as $kbChunk) {
            (new DataKodebooking())->setTableByQL($urlQL)->newQuery()
                ->whereIn('kodebooking', $kbChunk)
                ->update(['reupload' => 1]);
        }

        $chunks = array_chunk($recordsArray, 30);
        foreach ($chunks as $chunk) {
            SendKodeBookingBatchJob::dispatch($urlQL, $chunk);
        }

        return response()->json([
            'metadata' => [
                'code'    => 200,
                'message' => "Sebanyak {$count} data Kode Booking berhasil dimasukkan ke antrean background untuk dikirim ulang.",
            ],
            'response' => [
                'count' => $count,
                'jobs'  => count($chunks),
            ],
        ], 200);
    }
}
