<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\data_taskid;
use App\Helpers\BpjsHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PengirimanTaskIDController extends Controller
{
    public function taskid_get()
    {
        set_time_limit(99999);
        // $this->data_pending_taskID_get('QLJ');
        // $this->data_pending_taskID_get('QLKP');
        $this->taskID_otomatis('QLJ');
    }

    public function data_pending_taskID_get($urlQL = 'QLJ')
    {
        set_time_limit(10000);
        $params = [];
        $endpoint = '/data_pending_taskid';
        try {
            // $response = BpjsHelper::getRequestOLD($urlQL, $endpoint, $params);
            $response = BpjsHelper::getRequest($urlQL, $endpoint, $params);
            $decode_response = json_decode($response, true);

            if (isset($decode_response['metadata']['response']) && is_array($decode_response['metadata']['response'])) {

                // Collect data for DataTables
                Log::info('=== [data_pending_taskID_get - ' . $urlQL . '] === start processing, please wait...');
                foreach ($decode_response['metadata']['response'] as $dataEntry) {
                    Log::info('=== [data_pending_taskID_get - ' . $urlQL . '] data_pending_taskid ===');
                    $data_pending_taskid = [
                        'kodebooking' => $dataEntry['kodebooking'] ?? null,
                        'waktu' => $dataEntry['waktu'] ?? null,
                        'taskid' => $dataEntry['taskid'] ?? null,
                        'idpendaftaran' => $dataEntry['idpendaftaran'] ?? null,
                        'tanggal' => Carbon::createFromTimestamp($dataEntry['waktu'] / 1000)->locale('id')->translatedFormat('Y-m-d') ?? null,
                        'jam' => Carbon::createFromTimestamp($dataEntry['waktu'] / 1000)->timezone('Asia/Jakarta')->format('H:i:s') ?? null,
                        'code' => $dataEntry['code'] ?? null,
                        'message' => $dataEntry['message'] ?? null,
                    ];

                    if (
                        in_array($dataEntry['code'], [200, 208]) ||
                        $dataEntry['message'] === 'TaskId terakhir 99'
                    ) {
                        $data_pending_taskid['reupload'] = 0;
                    } else {
                        $data_pending_taskid['reupload'] = 1;
                    }
                    $data_taskID = new data_taskid([], $urlQL);

                    $existingData = $data_taskID->where('kodebooking', $data_pending_taskid['kodebooking'])
                        ->where('taskid', $data_pending_taskid['taskid'])
                        ->first();
                    if (!$existingData || $existingData->reupload != 0) { // Cek jika 'reupload' bukan 0
                        $saveUpdateData = $data_taskID->updateOrCreate(
                            [
                                'kodebooking' => $data_pending_taskid['kodebooking'],
                                'taskid' => $data_pending_taskid['taskid']
                            ],
                            $data_pending_taskid
                        );

                        Log::info('[data_pending_taskID_get - ' . $urlQL . '] saveUpdateData');
                        if ($saveUpdateData && $saveUpdateData instanceof \Illuminate\Database\Eloquent\Model) {
                            Log::info('[data_pending_taskID_get - ' . $urlQL . '] saved successfully :', $saveUpdateData->toArray());
                        } else {
                            Log::error('[data_pending_taskID_get - ' . $urlQL . '] Error: not saved correctly.');
                            }
                    } else {
                        Log::info('[data_pending_taskID_get - ' . $urlQL . '] Kodebooking : ' . $existingData->kodebooking . ' Skipped: reupload is 0, no update or insert performed.');
                    }
                }
                Log::info('=== [data_pending_taskID_get - ' . $urlQL . '] === horray, process finished..');
            }
            $this->taskID_otomatis($urlQL);
            return response()->json($decode_response, 200); // Return data as response for DataTables
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function taskID_otomatis($urlQL = 'QLJ')
    {
        set_time_limit(10000);
        try {
            $round = 1;
            $roundLimit = 1;
            Log::info('=== [taskID_otomatis - ' . $urlQL . '] === start processing, please wait...');
            while ($round <= $roundLimit) {
                Log::info('=== [taskID_otomatis - ' . $urlQL . '] === processing round ' . $round . '...');

                $tableTaskID = $urlQL === 'QLJ' ? 'data_taskids' : 'qlkp_data_taskids';
                $tableKodeBooking = $urlQL === 'QLJ' ? 'data_kodebooking' : 'qlkp_data_kodebooking';
                $data_taskID = new data_taskid([], $urlQL);

                try {
                    $dataArray = $data_taskID->from($tableTaskID . ' as taskID')
                        ->join("$tableKodeBooking as dk", function ($join) {
                            $join->on('taskID.kodebooking', '=', 'dk.kodebooking');
                            //    ->where('dk.reupload', 0);
                        })
                        ->where("taskID.reupload", 1)
                        ->where(function ($query) {
                            $query->whereNull('taskID.message') // taskID.message IS NULL
                                ->orWhere(function ($query) {
                                    $query->where('taskID.message', 'not like', '%Kode Booking tidak ditemukan%')
                                        ->where('taskID.message', 'not like', '%Gagal. Tanggal kirim TaskId maksimal H+7 dari tanggal pelayanan.%')
                                        ->where('taskID.message', 'not like', '%TaskId terakhir 7%')
                                        ->where('taskID.message', 'not like', '%TaskId terakhir 5%');
                                });
                        })
                        ->where('taskID.kodebooking', 'like', '202502078513')
                        // ->where('dk.message', 'not like', '%sudah terbit SEP%')
                        ->whereBetween('taskID.tanggal', [
                            now()->setTimezone('Asia/Jakarta')->subDays(7)->toDateString(),
                            now()->setTimezone('Asia/Jakarta')->toDateString()
                        ])
                        ->orderBy("taskID.kodebooking")
                        ->orderBy("taskID.taskid")
                        ->get(["taskID.*"])
                        ->toArray();

                    // Jika $dataArray kosong, lewati proses
                    if (empty($dataArray)) {
                        Log::info('=== [taskID_otomatis - ' . $urlQL . '] === No data found in round ' . $round . '. Skipping...');
                        $round++;
                        continue;
                    }
                    Log::info('=== [taskID_otomatis - ' . $urlQL . '] starting process round ' . $round . ' with ' . count($dataArray) . ' records, please wait... ===');
                    foreach ($dataArray as $data) {
                        $this->taskID_single_arr($urlQL, $data);
                    }
                    Log::info('=== [taskID_otomatis - ' . $urlQL . '] horray, process round ' . $round . ' finished ===');
                }
                catch (\Exception $e) {
                    Log::error('=== [taskID_otomatis - ' . $urlQL . '] === Database query failed in round ' . $round . ': ' . $e->getMessage());
                    throw $e; // Re-throw exception untuk ditangani oleh blok catch utama
                }
                Log::info('=== [taskID_otomatis - ' . $urlQL . '] === Finish round ' . $round . '...');
                $round++;
            }
            Log::info('=== [taskID_otomatis - ' . $urlQL . '] === Horray.. process finished');

            return response()->json([
                'metadata' => [
                    'code' => 200,
                    'message' => 'Pengiriman taskID_otomatis sukses.',
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to add taskID_otomatis:', ['error' => $e->getMessage()]);
            return response()->json([
                'metadata' => [
                    'code' => 500,
                    'message' => 'Terjadi kesalahan pada decoding respons.',
                ],
            ], 500);
        }
    }

    public function taskID_single_arr($urlQL = 'QLJ', array $data)
    {
        Log::info('[taskID_single_arr - ' . $urlQL . '] <<<=====>>> ', ['id' => $data['id'], 'kodebooking' => $data['kodebooking']]);
        $taskIDData = [
            'kodebooking' => $data['kodebooking'],
            'taskid' => $data['taskid'],
            'waktu' => $data['waktu'],
        ];

        $message = $data['message'];
        $kodebooking = $data['kodebooking'];
        // $tanggal_report = '';
        $jam_report = '';
        $tanggalPelayanan = '';
        $pattern = '';
        $data_taskID = new data_taskid([], $urlQL);

        $pattern = '/Tanggal pelayanan untuk Kode Booking tersebut adalah \((\d{4}-\d{2}-\d{2})\)/';
        if (preg_match($pattern, $message, $matches)) {
            if ($data['taskid'] == 99) {
                Log::info('=== [taskID_99] ===');
                $tanggalPelayanan = $matches[1];
                $jam_report = '23:59:00';
                $zonaWaktu = 'Asia/Jakarta';
                $tanggalWaktu = $tanggalPelayanan . ' ' . $jam_report;
                $timestampMilidetik = Carbon::createFromFormat('Y-m-d H:i:s', $tanggalWaktu, $zonaWaktu)->timestamp * 1000;
                $taskIDData['waktu'] = $timestampMilidetik;
            } else if ($data['taskid'] == 3) {
                Log::info('=== [taskID_3] ===');
                $waktuTask4 = $data_taskID->getWaktuTaskID4($kodebooking);
                $randomSecond = rand(60, 240);
                $newWaktursMilliseconds = $waktuTask4 - ($randomSecond * 1000);

                Log::info('[Beda tanggal - ' . $urlQL . '] Waktu RS taskID ' . ($data['taskid'] + 1) . ' : ' . $waktuTask4);
                Log::info('[Beda tanggal - ' . $urlQL . '] Waktu RS taskID ' . ($data['taskid']) . ' setelah dikurangi ' . $randomSecond . ' second: ' . $newWaktursMilliseconds);
                $taskIDData['waktu'] = $newWaktursMilliseconds;
            } else {
                Log::info('=== [taskID_LAIN] ===');
                $listtask_response = $this->listtask($urlQL, $kodebooking);
                $response_code = $listtask_response['metadata']['response']['metaData']['code'];
                if ($response_code == 200) {
                    $task99 = $listtask_response['metadata']['response']['response'];

                    // Filter array untuk mencari taskid = 99
                    $filteredTask99 = array_filter($task99, function ($item) {
                        return $item['taskid'] === 99;
                    });

                    if (!empty($filteredTask99)) {
                        // Jika ditemukan taskid = 99
                        Log::info('[Task ID 99] Task dengan taskid = 99 ditemukan.');
                        $data['reupload'] = '0';
                    } else {
                        // Jika tidak ditemukan taskid = 99
                        Log::info('[Task ID 99] Task dengan taskid = 99 tidak ditemukan.');
                        $task = array_filter($listtask_response['metadata']['response']['response'], function ($item) use ($data) {
                            return $item['taskid'] === ($data['taskid'] - 1);
                        });

                        if (!empty($task)) {
                            $task = array_values($task)[0];
                            $wakturs = $task['wakturs'];
                            $waktursTimestamp = strtotime($wakturs);
                            $randomSecond = rand(60, 240);
                            $newWaktursTimestamp = $waktursTimestamp + $randomSecond;
                            $newWaktursMilliseconds = $newWaktursTimestamp * 1000;

                            $newWakturs = Carbon::createFromTimestamp($newWaktursTimestamp)
                                ->setTimezone('Asia/Jakarta')
                                ->format('d-m-Y H:i:s');

                            Log::info('[Beda tanggal - ' . $urlQL . '] Waktu RS taskID ' . ($data['taskid'] - 1) . ' : ' . $wakturs);
                            Log::info('[Beda tanggal - ' . $urlQL . '] Waktu RS setelah ditambahkan ' . $randomSecond . ' second: ' . $newWakturs);
                            Log::info('[Beda tanggal - ' . $urlQL . '] Waktu RS taskID ' . ($data['taskid']) . ' setelah ditambahkan (milidetik): ' . $newWaktursMilliseconds);
                            $taskIDData['waktu'] = $newWaktursMilliseconds;
                        } else {
                            Log::info('[Beda tanggal - ' . $urlQL . '] Task dengan taskid = ' . ($data['taskid'] - 1) . ' tidak ditemukan.');
                        }
                    }

                }
            }
        }

        $pattern = '/Waktu \((\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} WIB)\) tidak boleh kurang atau sama dengan waktu sebelumnya/';
        if (preg_match($pattern, $message, $matches)) {
            $listtask_response = $this->listtask($urlQL, $kodebooking);
            $response_code = $listtask_response['metadata']['response']['metaData']['code'];
            if ($response_code == 200) {
                Log::info('Task - ' . $urlQL . ' dengan response_code 200.');

                // Pencarian taskid = 5
                $task_5 = array_filter($listtask_response['metadata']['response']['response'], function ($item) {
                    return $item['taskid'] === 5;
                });
                $task_5 = array_values($task_5); // Reset indeks array

                // Pencarian taskid = 7
                $task_7 = array_filter($listtask_response['metadata']['response']['response'], function ($item) {
                    return $item['taskid'] === 7;
                });
                $task_7 = array_values($task_7); // Reset indeks array

                $istask_99 = $data['taskid'] == 99 ? true : false;

                Log::info('Task - istask_99 = ' . $istask_99);
                if ((!empty($task_5) || !empty($task_7)) && $istask_99) {
                    // Jika taskid = 5 atau taskid = 7 ada dan $istask_99 bernilai true
                    $data['reupload'] = '0';
                    Log::info('Task - ' . $urlQL . ' dengan taskid = ' . ($data['taskid']) . ' tidak dijalankan karena taskID 5 atau 7 sudah ada.');
                } else {
                    $task = array_filter($listtask_response['metadata']['response']['response'], function ($item) use ($data) {
                        return $item['taskid'] === ($data['taskid'] - 1);
                    });

                    $task = array_values($task);
                    if (!empty($task)) {

                        usort($task, function ($a, $b) {
                            return strtotime($b['wakturs']) - strtotime($a['wakturs']);
                        });

                        // $task = array_values($task)[0];
                        $task = $task[0];
                        $wakturs = $task['wakturs'];
                        $waktursTimestamp = strtotime($wakturs);
                        $randomSecond = rand(60, 240);
                        $newWaktursTimestamp = $waktursTimestamp + $randomSecond;
                        $newWaktursMilliseconds = $newWaktursTimestamp * 1000;

                        $newWakturs = Carbon::createFromTimestamp($newWaktursTimestamp)
                            ->setTimezone('Asia/Jakarta')
                            ->format('d-m-Y H:i:s');

                        Log::info('Waktu RS - ' . $urlQL . ' taskID ' . ($data['taskid'] - 1) . ' : ' . $wakturs);
                        Log::info('Waktu RS - ' . $urlQL . ' setelah ditambahkan ' . $randomSecond . ' second: ' . $newWakturs);
                        Log::info('Waktu RS - ' . $urlQL . ' taskID ' . ($data['taskid']) . ' setelah ditambahkan (milidetik): ' . $newWaktursMilliseconds);
                        $taskIDData['waktu'] = $newWaktursMilliseconds;
                    } else {
                        Log::info('Task - ' . $urlQL . ' dengan taskid = ' . ($data['taskid'] - 1) . ' tidak ditemukan.');
                    }
                }
            }
        }

        // Validasi data yang diterima
        $validator = Validator::make($taskIDData, [
            'kodebooking' => 'required|string',
            'taskid' => 'required|integer',
            'waktu' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            Log::error('Validation errors:', $validator->errors()->toArray());
            return response()->json([
                'metadata' => [
                    'message' => 'Parameter tidak valid',
                    'code' => 422,
                ],
                'errors' => $validator->errors(),
            ], 422);
        };
        if ($data['reupload'] === 1) {
            Log::info('Reupload = 1');
            $bpjsHelper = new BpjsHelper();
            $endpoint = '/pengiriman_taskID';
            try {
                // $response = BpjsHelper::postRequest($urlQL, $endpoint, $taskIDData);
                $response = $bpjsHelper->postRequestSelf($urlQL, $endpoint, $taskIDData);
                $response_decode = json_decode((string) $response, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('JSON decoding error:', ['response' => $response]);
                    return response()->json([
                        'metadata' => [
                            'code' => 500,
                            'message' => 'Terjadi kesalahan pada decoding respons.',
                        ],
                    ], 500);
                }

                // Menyimpan code, request, dan response
                $taskIDData['request'] = json_encode($taskIDData);
                $taskIDData['response'] = json_encode($response_decode);
                $taskIDData['code'] = $response_decode['metadata']['code'] ?? null;
                $taskIDData['message'] = $response_decode['metadata']['message'] ?? null;

                if (
                    in_array($response_decode['metadata']['code'], [200, 208]) ||
                    $response_decode['metadata']['message'] === 'TaskId terakhir 99'
                ) {
                    $taskIDData['reupload'] = 0;
                } else {
                    $taskIDData['reupload'] = 1;
                }
                $data_taskID = new data_taskid([], $urlQL);
                $taskID = $data_taskID->updateOrCreate(
                    [
                        'kodebooking' => $taskIDData['kodebooking'],
                        'taskid' => $taskIDData['taskid']
                    ],
                    $taskIDData
                );

                if ($taskID && $taskID instanceof \Illuminate\Database\Eloquent\Model) {
                    Log::info('taskID_single_arr saved successfully :', $taskID->toArray());
                } else {
                    Log::error('Error: taskID_single_arr was not saved correctly.');
                }


                $message_response = $response_decode['metadata']['message'];

                $pattern = '/TaskId=(\d+) belum ada/';
                if (preg_match($pattern, $message_response, $matches)) {
                    $taskId_value = $matches[1];
                    $listtask_response = $this->listtask($urlQL, $kodebooking);
                    $response_code = $listtask_response['metadata']['response']['metaData']['code'];
                    if ($response_code == 200) {
                        $task = array_filter($listtask_response['metadata']['response']['response'], function ($item) use ($taskId_value) {
                            return $item['taskid'] === ($taskId_value - 1);
                        });
                        if (!empty($task)) {
                            $task = array_values($task)[0];
                            $wakturs = $task['wakturs'];
                            $waktursTimestamp = strtotime($wakturs);
                            $randomSecond = rand(60, 240);
                            $newWaktursTimestamp = $waktursTimestamp + $randomSecond;
                            $newWaktursMilliseconds = $newWaktursTimestamp * 1000;

                            $newWakturs = Carbon::createFromTimestamp($newWaktursTimestamp)
                                ->setTimezone('Asia/Jakarta')
                                ->format('d-m-Y H:i:s');

                            Log::info('Waktu RS update after response');
                            Log::info('Waktu RS taskID ' . ($taskId_value - 1) . ' : ' . $wakturs);
                            Log::info('Waktu RS setelah ditambahkan ' . $randomSecond . ' second: ' . $newWakturs);
                            Log::info('Waktu RS taskID ' . ($taskId_value) . ' setelah ditambahkan (milidetik): ' . $newWaktursMilliseconds);

                            //simpan database untuk taskID tambahan yang hilang
                            $data_taskID_additional = [
                                'kodebooking' => $data['kodebooking'] ?? null,
                                'waktu' => $newWaktursMilliseconds ?? null,
                                'taskid' => $taskId_value ?? null,
                                'idpendaftaran' => $data['idpendaftaran'] ?? null,
                                'tanggal' => Carbon::createFromTimestamp($newWaktursMilliseconds / 1000)->locale('id')->translatedFormat('Y-m-d') ?? null,
                                'jam' => Carbon::createFromTimestamp($newWaktursMilliseconds / 1000)->timezone('Asia/Jakarta')->format('H:i:s') ?? null,
                                'code' => null,
                                'message' => null,
                            ];
                            $data_taskID = new data_taskid([], $urlQL);
                            $saveUpdateData = $data_taskID->updateOrCreate(
                                [
                                    'kodebooking' => $data_taskID_additional['kodebooking'],
                                    'taskid' => $data_taskID_additional['taskid']
                                ],
                                $data_taskID_additional
                            );

                            if ($saveUpdateData && $saveUpdateData instanceof \Illuminate\Database\Eloquent\Model) {
                                Log::info('[taskID_single_arr] taskID_additional saved successfully :', $saveUpdateData->toArray());
                            } else {
                                Log::error('[taskID_single_arr] Error: taskID_additional not saved correctly.');
                            }
                        } else {
                            Log::info('[taskID_single_arr] Data taskid = ' . ($taskId_value - 1) . ' tidak ditemukan.');
                        }
                    }
                }

                $pattern = '/TaskId=(\d+) tidak valid \/ TaskId sebelumnya belum terkirim/';
                if (preg_match($pattern, $message_response, $matches)) {
                    $taskId_value = $matches[1];
                    if ($taskId_value != 7) {
                        $waktursMilliseconds  = $data['waktu'];
                        $waktursTimestamp = $waktursMilliseconds / 1000;
                        $randomSecond = rand(130, 300);
                        $newWaktursTimestamp = $waktursTimestamp - $randomSecond;
                        $newWaktursMilliseconds = $newWaktursTimestamp * 1000;

                        $newWakturs = Carbon::createFromTimestamp($newWaktursTimestamp)
                            ->setTimezone('Asia/Jakarta')
                            ->format('d-m-Y H:i:s');

                        Log::info('Waktu RS create new TaskID ' . ($taskId_value - 1));
                        Log::info('Waktu RS taskID ' . ($taskId_value - 1) . ' setelah dikurangi ' . $randomSecond . ' (detik) dari TaskID setelahnya : ' . $newWaktursMilliseconds);

                        //simpan database untuk taskID baru
                        $data_taskID_new = [
                            'kodebooking' => $data['kodebooking'] ?? null,
                            'waktu' => $newWaktursMilliseconds ?? null,
                            'taskid' => ($taskId_value - 1) ?? null,
                            'idpendaftaran' => $data['idpendaftaran'] ?? null,
                            'tanggal' => Carbon::createFromTimestamp($newWaktursMilliseconds / 1000)->locale('id')->translatedFormat('Y-m-d') ?? null,
                            'jam' => Carbon::createFromTimestamp($newWaktursMilliseconds / 1000)->timezone('Asia/Jakarta')->format('H:i:s') ?? null,
                            'code' => null,
                            'message' => null,
                        ];
                        $data_taskID = new data_taskid([], $urlQL);
                        $saveUpdateData = $data_taskID->updateOrCreate(
                            [
                                'kodebooking' => $data_taskID_new['kodebooking'],
                                'taskid' => $data_taskID_new['taskid']
                            ],
                            $data_taskID_new
                        );

                        if ($saveUpdateData && $saveUpdateData instanceof \Illuminate\Database\Eloquent\Model) {
                            Log::info('[taskID_single_arr] new taskID ' . $data_taskID_new['taskid'] . ' saved successfully :', $saveUpdateData->toArray());
                        } else {
                            Log::error('[taskID_single_arr] Error: new taskID ' . $data_taskID_new['taskid'] . ' not saved correctly.');
                        }
                    }
                }

                $data_taskid4withNulltaskid5 = $data_taskID->getTaskid4withNullTaskid5($urlQL)->toArray();
                Log::info('[' . $urlQL . '] Waktu RS data_taskid4withNulltaskid5 : ' . json_encode($data_taskid4withNulltaskid5));
                if (!empty($data_taskid4withNulltaskid5)) {
                    foreach ($data_taskid4withNulltaskid5 as $dataTaskID4) {
                        $waktuTask4 = $dataTaskID4->waktu;
                        $randomSecond = rand(60, 240);
                        $newWaktursMilliseconds = $waktuTask4 + ($randomSecond * 1000);
                        Log::info('[' . $urlQL . '] Waktu RS taskID 4 : ' . $waktuTask4);
                        Log::info('[' . $urlQL . '] NEW Create Waktu RS taskID 5 setelah ditambah ' . $randomSecond . ' second: ' . $newWaktursMilliseconds);
                        $timestamp = $newWaktursMilliseconds / 1000;
                        $datetime_jakarta = Carbon::createFromTimestamp($timestamp, 'Asia/Jakarta');
                        $tanggalTaskId5 = $datetime_jakarta->format('Y-m-d');
                        $jamTaskId5 = $datetime_jakarta->format('H:i:s');

                        $dataTaskID5 = [
                            'kodebooking' => $dataTaskID4->kodebooking,
                            'waktu' => $newWaktursMilliseconds,
                            'taskid' => 5,
                            'idpendaftaran' => $dataTaskID4->idpendaftaran,
                            'tanggal' => $tanggalTaskId5,
                            'jam' => $jamTaskId5,
                            'reupload' => 1
                        ];

                        Log::info('[' . $urlQL . '] Waktu dataTaskID5 : ' . json_encode($dataTaskID5));
                        $taskID = $data_taskID->updateOrCreate(
                            [
                                'kodebooking' => $dataTaskID5['kodebooking'],
                                'taskid' => $dataTaskID5['taskid']
                            ],
                            $dataTaskID5
                        );
                    }
                }

                return response()->json($response_decode, 200, [], JSON_PRETTY_PRINT);
            } catch (\Exception $e) {
                Log::error('Failed to add taskID_single_arr:', ['error' => $e->getMessage()]);
                return response()->json([
                    'metadata' => [
                        'code' => 500,
                        'message' => 'Terjadi kesalahan pada server.',
                    ],
                    'error' => $e->getMessage(),
                ], 500);
            }
        } else {
            Log::info('Reupload = 1, update database');
            $taskIDData['reupload'] = 0;

            $data_taskID = new data_taskid([], $urlQL);
            $taskID = $data_taskID->updateOrCreate(
                [
                    'kodebooking' => $taskIDData['kodebooking'],
                    'taskid' => $taskIDData['taskid']
                ],
                $taskIDData
            );
        }
    }


    public function listtask_post(Request $request)
    {
        Log::info('[listtask_post] method reached');
        $validator = Validator::make($request->all(), [
            'kodebooking' => 'required|string',
        ]);

        // Cek validasi gagal
        if ($validator->fails()) {
            Log::error('[listtask_post] Validation errors:', $validator->errors()->toArray());
            return response()->json([
                'metadata' => [
                    'message' => 'Parameter tidak valid',
                    'code' => 422,
                ],
                'errors' => $validator->errors(),
            ], 422);
        }

        $endpoint = '/listtask';
        try {
            $response = BpjsHelper::postRequest($endpoint, $request->all());
            $response_decode = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'metadata' => [
                        'code' => 500,
                        'message' => '[listtask_post]  Terjadi kesalahan pada decoding respons.',
                    ],
                ], 500);
            };

            if (is_array($response_decode) && !empty($response_decode)) {
                Log::info('[listtask_post] Antrian added successfully :', $response_decode);
            } else {
                Log::error('[listtask_post] Error: Antrian was not saved correctly.');
            }
            return response()->json($response_decode, 200, [], JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            Log::error('[listtask_post] Failed to add :', ['error' => $e->getMessage()]);
            return response()->json([
                'metadata' => [
                    'code' => 500,
                    'message' => 'Terjadi kesalahan pada server.',
                ],
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function listtask($urlQL = 'QLJ', $kodebooking)
    {
        Log::info('[listtask - ' . $urlQL . '] method reached');
        // $data = array_merge($request->all(),
        $data['kodebooking'] = $kodebooking;

        $endpoint = '/listtask';
        try {
            $response = BpjsHelper::postRequest($urlQL, $endpoint, $data);
            $response_decode = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'metadata' => [
                        'code' => 500,
                        'message' => '[listtask - ' . $urlQL . ']  Terjadi kesalahan pada decoding respons.',
                    ],
                ], 500);
            };

            if (is_array($response_decode) && !empty($response_decode)) {
                Log::info('[listtask - ' . $urlQL . '] listtask added successfully :', $response_decode);
            } else {
                Log::error('[listtask - ' . $urlQL . '] Error: listtask was not saved correctly.');
            }
            return $response_decode;
        } catch (\Exception $e) {
            Log::error('[listtask - ' . $urlQL . '] Failed to add :', ['error' => $e->getMessage()]);
            return response()->json([
                'metadata' => [
                    'code' => 500,
                    'message' => $urlQL . ' - Terjadi kesalahan pada server.',
                ],
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function taskID_single_request($urlQL = 'QLJ', Request $data)
    {
        Log::info($urlQL . 'taskID_single_request method reached');
        $data_taskID = [
            'norm' => $data['norm'],
            'taskid' => $data['taskid'],
            'waktu' => $data['waktu'],
            'request' => json_encode($data->all()),
            'created_at' => now(),
            'updated_at' => now(),
        ];



        $message = $data['message'];
        $kodebooking = $data['kodebooking'];
        $tanggal_report = '';
        $jam_report = '';
        $tanggalPelayanan = '';
        $pattern = '';

        $pattern = '/Tanggal pelayanan untuk Kode Booking tersebut adalah \((\d{4}-\d{2}-\d{2})\)/';
        if (preg_match($pattern, $message, $matches)) {
            if ($data['taskid'] == 99) {
                $tanggalPelayanan = $matches[1];
                $jam_report = '23:59:00';
                $zonaWaktu = 'Asia/Jakarta';
                $tanggalWaktu = $tanggalPelayanan . ' ' . $jam_report;
                $timestampMilidetik = Carbon::createFromFormat('Y-m-d H:i:s', $tanggalWaktu, $zonaWaktu)->timestamp * 1000;
                $taskIDData['waktu'] = $timestampMilidetik;
            } else {
                $listtask_response = $this->listtask($urlQL, $kodebooking);
                $response_code = $listtask_response['metadata']['response']['metaData']['code'];
                if ($response_code == 200) {
                    $task = array_filter($listtask_response['metadata']['response']['response'], function ($item) use ($data) {
                        return $item['taskid'] === ($data['taskid'] - 1);
                    });

                    if (!empty($task)) {
                        $task = array_values($task)[0];
                        $wakturs = $task['wakturs'];
                        $waktursTimestamp = strtotime($wakturs);
                        $randomSecond = rand(60, 240);
                        $newWaktursTimestamp = $waktursTimestamp + $randomSecond;
                        $newWaktursMilliseconds = $newWaktursTimestamp * 1000;

                        $newWakturs = Carbon::createFromTimestamp($newWaktursTimestamp)
                            ->setTimezone('Asia/Jakarta')
                            ->format('d-m-Y H:i:s');

                        Log::info('[Beda tanggal] Waktu RS taskID ' . ($data['taskid'] - 1) . ' : ' . $wakturs);
                        Log::info('[Beda tanggal] Waktu RS setelah ditambahkan ' . $randomSecond . ' second: ' . $newWakturs);
                        Log::info('[Beda tanggal] Waktu RS taskID ' . ($data['taskid']) . ' setelah ditambahkan (milidetik): ' . $newWaktursMilliseconds);
                        $taskIDData['waktu'] = $newWaktursMilliseconds;
                    } else {
                        Log::info('[Beda tanggal] Task dengan taskid = ' . ($data['taskid'] - 1) . ' tidak ditemukan.');
                    }
                }
            }
        }

        $pattern = '/Waktu \((\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} WIB)\) tidak boleh kurang atau sama dengan waktu sebelumnya/';
        if (preg_match($pattern, $message, $matches)) {
            $listtask_response = $this->listtask($urlQL, $kodebooking);
            $response_code = $listtask_response['metadata']['response']['metaData']['code'];
            if ($response_code == 200) {
                $task = array_filter($listtask_response['metadata']['response']['response'], function ($item) use ($data) {
                    return $item['taskid'] === ($data['taskid'] - 1);
                });

                $task = array_values($task);
                if (!empty($task)) {

                    usort($task, function ($a, $b) {
                        return strtotime($b['wakturs']) - strtotime($a['wakturs']);
                    });

                    // $task = array_values($task)[0];
                    $task = $task[0];
                    $wakturs = $task['wakturs'];
                    $waktursTimestamp = strtotime($wakturs);
                    $randomSecond = rand(60, 240);
                    $newWaktursTimestamp = $waktursTimestamp + $randomSecond;
                    $newWaktursMilliseconds = $newWaktursTimestamp * 1000;

                    $newWakturs = Carbon::createFromTimestamp($newWaktursTimestamp)
                        ->setTimezone('Asia/Jakarta')
                        ->format('d-m-Y H:i:s');

                    Log::info('Waktu RS taskID ' . ($data['taskid'] - 1) . ' : ' . $wakturs);
                    Log::info('Waktu RS setelah ditambahkan ' . $randomSecond . ' second: ' . $newWakturs);
                    Log::info('Waktu RS taskID ' . ($data['taskid']) . ' setelah ditambahkan (milidetik): ' . $newWaktursMilliseconds);
                    $taskIDData['waktu'] = $newWaktursMilliseconds;
                } else {
                    Log::info('Task dengan taskid = ' . ($data['taskid'] - 1) . ' tidak ditemukan.');
                }
            }
        }

        $validator = Validator::make($data_taskID, [
            'norm' => 'required|string',
            'taskid' => 'required|string',
            'waktu' => 'required|string',
            'request' => 'required|string',
            'created_at' => 'required|string',
            'updated_at' => 'required|string',
        ]);

        if ($validator->fails()) {
            Log::error('Validation errors:', $validator->errors()->toArray());
            return response()->json([
                'metadata' => [
                    'message' => 'Parameter tidak valid',
                    'code' => 422,
                ],
                'errors' => $validator->errors(),
            ], 422);
        }

        $endpoint = '/pengiriman_taskID';

        try {
            $response = BpjsHelper::postRequest($endpoint, $data_taskID);
            $response_decode = json_decode($response);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'metadata' => [
                        'code' => 500,
                        'message' => 'Terjadi kesalahan pada decoding respons.',
                    ],
                ], 500);
            }

            $data_taskID['code'] = $response_decode->metadata->code;
            $data_taskID['response'] = json_encode($response_decode);

            $data_taskID = new data_taskid([], $urlQL);
            $taskID = $data_taskID->updateOrCreate(
                ['taskid' => $data_taskID['taskid']],
                $data_taskID
            );

            if ($taskID instanceof \Illuminate\Database\Eloquent\Model) {
                Log::info('Task ID added:', $taskID->toArray());
            } else {
                Log::error('Error: Task ID was not saved correctly.');
            }
            return response()->json($response_decode, 200, [], JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            Log::error('Failed to add taskID_single_request:', ['error' => $e->getMessage()]);
            return response()->json([
                'metadata' => [
                    'code' => 500,
                    'message' => 'Terjadi kesalahan pada server.',
                ],
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function pengiriman_taskID_post($urlQL = 'QLJ', Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kodebooking' => 'required|string',
            'taskid' => 'required|integer',
            'waktu' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            Log::error('[pengiriman_taskID_post] Validation errors:', $validator->errors()->toArray());
            return response()->json([
                'metadata' => [
                    'message' => 'Parameter tidak valid',
                    'code' => 422,
                ],
                'errors' => $validator->errors(),
            ], 422);
        }

        $endpoint = '/pengiriman_taskID';
        try {
            $response = BpjsHelper::postRequest($endpoint, $request->all());
            $response_decode = json_decode($response);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('[pengiriman_taskID_post] JSON decoding error:', ['response' => $response]);
                return response()->json([
                    'metadata' => [
                        'code' => 500,
                        'message' => 'Terjadi kesalahan pada decoding respons.',
                    ],
                ], 500);
            };

            $taskIDData = $request->only([
                'kodebooking',
                'taskid',
                'waktu'
            ]);

            // Menyimpan code, request, dan response
            $taskIDData['code'] = $response_decode->metadata->code ?? null;
            $taskIDData['request'] = json_encode($taskIDData);
            $taskIDData['response'] = json_encode($response_decode);

            $data_taskID = new data_taskid([], $urlQL);
            $taskID = $data_taskID->updateOrCreate(
                [
                    'kodebooking' => $taskIDData['kodebooking'],
                    'taskid' => $taskIDData['taskid']
                ],
                $taskIDData
            );

            if ($taskID && $taskID instanceof \Illuminate\Database\Eloquent\Model) {
                Log::info('[pengiriman_taskID_post] saved successfully :', $taskID->toArray());
            } else {
                Log::error('[pengiriman_taskID_post] Error: not saved correctly.');
            }

            return response()->json($response_decode, 200, [], JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            Log::error('[pengiriman_taskID_post] Failed to add : ', ['error' => $e->getMessage()]);
            return response()->json([
                'metadata' => [
                    'code' => 500,
                    'message' => '[pengiriman_taskID_post] Terjadi kesalahan pada server.',
                ],
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
