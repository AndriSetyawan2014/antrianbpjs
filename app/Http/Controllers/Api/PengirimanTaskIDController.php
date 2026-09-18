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
    protected $listTaskCache = []; // Cache untuk menyimpan hasil listtask per kodebooking
    public function taskid_get()
    {
        set_time_limit(99999);
        $allowedQL = BpjsHelper::getUrlQLOptions();

        // --- Settingan Filter Tanggal (Edit di sini jika diperlukan) ---
        $dari    = Carbon::now()->subDays(7)->toDateString(); // 30 hari yang lalu
        $sampai  = Carbon::now()->toDateString();           // Hari ini
        // --------------------------------------------------------------

        foreach ($allowedQL as $ql) {
            $this->data_pending_taskID_get($ql, null, $dari, $sampai);
        }
    }

    public function taskid_get_by_ql(Request $request)
    {
        set_time_limit(99999);

        $allowedQL = BpjsHelper::getUrlQLOptions();
        $urlQL = strtoupper($request->query('urlQL', $request->input('urlQL', '')));
        $tanggal = $request->query('tanggal', $request->input('tanggal', ''));
        $dari = $request->query('dari', $request->input('dari', ''));
        $sampai = $request->query('sampai', $request->input('sampai', ''));

        Log::info('[taskid_get_by_ql] Incoming request', [
            'urlQL' => $urlQL,
            'tanggal' => $tanggal,
            'ip' => $request->ip()
        ]);

        if (!in_array($urlQL, $allowedQL)) {
            return response()->json([
                'metadata' => [
                    'code'    => 422,
                    'message' => 'Parameter urlQL tidak valid. Nilai yang diizinkan: ' . implode(', ', $allowedQL),
                ],
            ], 422);
        }

        return $this->data_pending_taskID_get($urlQL, $tanggal, $dari, $sampai);
    }

    public function run_taskid_ql(Request $request)
    {
        set_time_limit(99999);

        $allowedQL = BpjsHelper::getUrlQLOptions();
        $urlQL = strtoupper($request->query('urlQL', $request->input('urlQL', '')));
        $tanggal = $request->input('tanggal');

        if (!in_array($urlQL, $allowedQL)) {
            return response()->json([
                'metadata' => [
                    'code'    => 422,
                    'message' => 'Parameter urlQL tidak valid. Nilai yang diizinkan: ' . implode(', ', $allowedQL),
                ],
            ], 422);
        }

        return $this->taskID_otomatis($urlQL, $tanggal);
    }

    public function run_taskid_by_kodebooking(Request $request)
    {
        set_time_limit(99999);

        $allowedQL = BpjsHelper::getUrlQLOptions();
        $urlQL = strtoupper($request->query('urlQL', $request->input('urlQL', '')));
        $kodebooking = $request->input('kodebooking');

        if (!in_array($urlQL, $allowedQL)) {
            return response()->json([
                'metadata' => [
                    'code'    => 422,
                    'message' => 'Parameter urlQL tidak valid. Gunakan: ' . implode(', ', $allowedQL),
                ],
            ], 422);
        }

        if (empty($kodebooking)) {
            return response()->json([
                'metadata' => [
                    'code'    => 422,
                    'message' => 'Parameter kodebooking wajib diisi.',
                ],
            ], 422);
        }

        // FORCE RESET: Pastikan record untuk kodebooking ini ditandai untuk diproses ulang
        $data_taskID_model = (new data_taskid())->setTableByQL($urlQL);
        $data_taskID_model->where('kodebooking', $kodebooking)->update(['reupload' => 1]);
        Log::info("[$urlQL] Force re-run for kodebooking: $kodebooking. Reset reupload=1.");

        return $this->taskID_otomatis($urlQL, null, $kodebooking);
    }

    public function manualAddTaskid(Request $request)
    {
        $urlQL = strtoupper($request->input('urlQL', 'QLJ'));
        $allowedQL = \App\Helpers\BpjsHelper::getUrlQLOptions();

        if (!in_array($urlQL, $allowedQL)) {
            return response()->json([
                'metadata' => ['code' => 422, 'message' => 'Cabang tidak valid.']
            ], 422);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'kodebooking' => 'required|string',
            'taskid' => 'required|integer',
            'waktu' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'metadata' => ['code' => 422, 'message' => 'Parameter tidak lengkap.']
            ], 422);
        }

        try {
            $waktuInput = $request->input('waktu'); // expect string like 2026-09-17T16:45
            // Convert to milliseconds timestamp
            $timestamp = \Carbon\Carbon::parse($waktuInput, 'Asia/Jakarta')->getTimestamp();
            $waktuMillis = $timestamp * 1000;

            $model = (new \App\Models\data_taskid())->setTableByQL($urlQL);
            
            $model->create([
                'kodebooking' => $request->input('kodebooking'),
                'taskid' => $request->input('taskid'),
                'waktu' => $waktuMillis,
                'idpendaftaran' => $request->input('idpendaftaran'),
                'tanggal' => \Carbon\Carbon::parse($waktuInput)->toDateString(),
                'jam' => \Carbon\Carbon::parse($waktuInput)->toTimeString(),
                'code' => 0,
                'message' => 'Menunggu Sinkronisasi',
                'reupload' => 1,
            ]);

            return response()->json([
                'metadata' => [
                    'code' => 200,
                    'message' => 'Task ID berhasil ditambahkan dan siap disinkronisasi.'
                ]
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error manualAddTaskid: ' . $e->getMessage());
            return response()->json([
                'metadata' => ['code' => 500, 'message' => 'Terjadi kesalahan pada server.']
            ], 500);
        }
    }

    public function manualEditTaskid(Request $request)
    {
        $urlQL = strtoupper($request->input('urlQL', 'QLJ'));
        $allowedQL = \App\Helpers\BpjsHelper::getUrlQLOptions();

        if (!in_array($urlQL, $allowedQL)) {
            return response()->json([
                'metadata' => ['code' => 422, 'message' => 'Cabang tidak valid.']
            ], 422);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'id' => 'required|integer',
            'kodebooking' => 'required|string',
            'taskid' => 'required|integer',
            'waktu' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'metadata' => ['code' => 422, 'message' => 'Parameter tidak lengkap.']
            ], 422);
        }

        try {
            $waktuInput = $request->input('waktu'); // expect string like 2026-09-17T16:45
            // Convert to milliseconds timestamp
            $timestamp = \Carbon\Carbon::parse($waktuInput, 'Asia/Jakarta')->getTimestamp();
            $waktuMillis = $timestamp * 1000;

            $model = (new \App\Models\data_taskid())->setTableByQL($urlQL);
            
            $record = $model->find($request->input('id'));
            
            if (!$record) {
                return response()->json([
                    'metadata' => ['code' => 404, 'message' => 'Data tidak ditemukan.']
                ], 404);
            }

            $record->update([
                'taskid' => $request->input('taskid'),
                'waktu' => $waktuMillis,
                'idpendaftaran' => $request->input('idpendaftaran'),
                'tanggal' => \Carbon\Carbon::parse($waktuInput)->toDateString(),
                'jam' => \Carbon\Carbon::parse($waktuInput)->toTimeString(),
                'code' => 0,
                'message' => 'Menunggu Sinkronisasi',
                'reupload' => 1,
            ]);

            return response()->json([
                'metadata' => [
                    'code' => 200,
                    'message' => 'Task ID berhasil diubah dan siap disinkronisasi.'
                ]
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error manualEditTaskid: ' . $e->getMessage());
            return response()->json([
                'metadata' => ['code' => 500, 'message' => 'Terjadi kesalahan pada server.']
            ], 500);
        }
    }

    public function data_pending_taskID_get($urlQL = 'QLJ', $tanggal = null, $dari = null, $sampai = null)
    {
        set_time_limit(99999);
        $params = [];
        if ($tanggal) $params['tanggal'] = $tanggal;
        if ($dari) $params['dari'] = $dari;
        if ($sampai) $params['sampai'] = $sampai;
        $endpoint = '/data_pending_taskid';
        try {
            $response = BpjsHelper::getRequest($urlQL, $endpoint, $params);

            if ($response === null) {
                Log::error('[data_pending_taskID_get - ' . $urlQL . '] FAIL: BpjsHelper::getRequest returned NULL.');
                return response()->json([
                    'error' => 'Server SIRSTQL (' . $urlQL . ') tidak dapat dijangkau.',
                ], 503);
            }

            $decode_response = json_decode($response, true);
            Log::info('[data_pending_taskID_get - ' . $urlQL . '] Response received', [
                'code' => $decode_response['metadata']['code'] ?? 'N/A',
                'message' => $decode_response['metadata']['message'] ?? 'N/A',
                'data_count' => isset($decode_response['metadata']['response']) ? count($decode_response['metadata']['response']) : 0
            ]);

            if (isset($decode_response['metadata']['response']) && is_array($decode_response['metadata']['response'])) {
                $pendingData = $decode_response['metadata']['response'];
                $totalIncoming = count($pendingData);
                Log::info("=== [data_pending_taskID_get - $urlQL] === Processing $totalIncoming records...");

                $data_taskID_model = (new data_taskid())->setTableByQL($urlQL);
                
                $dataChunks = array_chunk($pendingData, 1000);
                $totalUpserted = 0;

                foreach ($dataChunks as $index => $chunk) {
                    // Collect identifiers for this chunk only
                    $chunkIdentifiers = collect($chunk)->map(function($item) {
                        return ($item['kodebooking'] ?? '') . '_' . ($item['taskid'] ?? '');
                    })->filter()->toArray();

                    // Fetch existing records for this chunk only
                    $existingSuccess = $data_taskID_model->whereIn(\DB::raw("CONCAT(kodebooking, '_', taskid)"), $chunkIdentifiers)
                        ->where('reupload', 0)
                        ->get()
                        ->keyBy(function($item) {
                            return $item->kodebooking . '_' . $item->taskid;
                        });

                    $upsertData = [];
                    foreach ($chunk as $dataEntry) {
                        $key = ($dataEntry['kodebooking'] ?? '') . '_' . ($dataEntry['taskid'] ?? '');
                        
                        // Skip if already marked as reupload=0 locally
                        if ($existingSuccess->has($key)) {
                            continue;
                        }

                        $reupload = 1;
                        if (in_array($dataEntry['code'], [200, 208]) || ($dataEntry['message'] ?? '') === 'TaskId terakhir 99') {
                            $reupload = 0;
                        }

                        $upsertData[] = [
                            'kodebooking'   => $dataEntry['kodebooking'] ?? null,
                            'waktu'         => $dataEntry['waktu'] ?? null,
                            'taskid'        => $dataEntry['taskid'] ?? null,
                            'idpendaftaran' => $dataEntry['idpendaftaran'] ?? null,
                            'tanggal'       => Carbon::createFromTimestamp($dataEntry['waktu'] / 1000)->format('Y-m-d'),
                            'jam'           => Carbon::createFromTimestamp($dataEntry['waktu'] / 1000)->format('H:i:s'),
                            'code'          => $dataEntry['code'] ?? null,
                            'message'       => $dataEntry['message'] ?? null,
                            'reupload'      => $reupload,
                            'updated_at'    => now(),
                        ];
                    }

                    if (!empty($upsertData)) {
                        $data_taskID_model->upsert($upsertData, ['kodebooking', 'taskid'], [
                            'waktu', 'idpendaftaran', 'tanggal', 'jam', 'code', 'message', 'reupload', 'updated_at'
                        ]);
                        $totalUpserted += count($upsertData);
                    }

                    // Log progress every 10 chunks (10.000 records)
                    if (($index + 1) % 10 == 0) {
                        Log::info("[data_pending_taskID_get - $urlQL] Progress: " . (($index + 1) * 1000) . " / $totalIncoming records processed...");
                    }
                }

                Log::info("[data_pending_taskID_get - $urlQL] UPSERT_FINISHED: Processed $totalUpserted / $totalIncoming records.");

                // Panggil pengiriman otomatis (Queue Dispatcher)
                $this->taskID_otomatis($urlQL, $tanggal);

                return response()->json([
                    'metadata' => [
                        'code' => 200,
                        'message' => 'OK. Processed ' . count($upsertData) . ' records and dispatched to queue.',
                    ],
                ], 200);
            }
            return response()->json($decode_response, 200); // Return data as response for DataTables
        } catch (\Exception $e) {
            Log::error('[data_pending_taskID_get - ' . $urlQL . '] Gagal memuat request. Error: ' . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function taskID_otomatis($urlQL = 'QLJ', $tanggal = null, $kodebooking = null)
    {
        set_time_limit(10000);
        
        $isEmptyTanggal = empty($tanggal);
        if ($isEmptyTanggal) {
            // Jika tanggal kosong maka gunakan tanggal hari ini
            $tanggal = now()->setTimezone('Asia/Jakarta')->toDateString();
        }
        Log::info('Nilai tanggal: ' . $tanggal);

        try {
            $round = 1;
            $roundLimit = 5;
            Log::info('=== [taskID_otomatis - ' . $urlQL . '] === start processing, please wait...');
            
            // Auto-generate missing Task ID 5 for past bookings (not today)
            $data_taskID_gen = (new data_taskid())->setTableByQL($urlQL);
            $missingTask5 = $data_taskID_gen->getTaskid4withNullTaskid5($urlQL)->toArray();
            if (!empty($missingTask5)) {
                Log::info('[' . $urlQL . '] Found ' . count($missingTask5) . ' bookings missing Task ID 5. Generating...');
                foreach ($missingTask5 as $dataTaskID4) {
                    $waktuTask4 = $dataTaskID4->waktu;
                    $randomSecond = rand(60, 240);
                    $newWaktursMilliseconds = $waktuTask4 + ($randomSecond * 1000);
                    
                    $timestamp = $newWaktursMilliseconds / 1000;
                    $datetime_jakarta = Carbon::createFromTimestamp($timestamp, 'Asia/Jakarta');
                    
                    $dataTaskID5 = [
                        'kodebooking'   => $dataTaskID4->kodebooking,
                        'waktu'         => $newWaktursMilliseconds,
                        'taskid'        => 5,
                        'idpendaftaran' => $dataTaskID4->idpendaftaran,
                        'tanggal'       => $datetime_jakarta->format('Y-m-d'),
                        'jam'           => $datetime_jakarta->format('H:i:s'),
                        'reupload'      => 1
                    ];

                    $data_taskID_gen->updateOrCreate(
                        ['kodebooking' => $dataTaskID5['kodebooking'], 'taskid' => 5],
                        $dataTaskID5
                    );
                    Log::info('[' . $urlQL . '] Generated Task ID 5 for ' . $dataTaskID5['kodebooking']);
                }
            }

            $this->listTaskCache = []; // Reset cache di awal proses

            // Tentukan tabel dan model di luar loop untuk efisiensi
            $tableTaskID = match ($urlQL) {
                'QLJ'   => 'data_taskids',
                'QLKP'  => 'qlkp_data_taskids',
                'QLTMG' => 'qltmg_data_taskids',
                default => 'data_taskids',
            };
            $tableKodeBooking = match ($urlQL) {
                'QLJ'   => 'data_kodebooking',
                'QLKP'  => 'qlkp_data_kodebooking',
                'QLTMG' => 'qltmg_data_kodebooking',
                default => 'data_kodebooking',
            };
            $data_taskID_model = (new data_taskid())->setTableByQL($urlQL);

            // PROAKTIF: Batalkan Task 99 sebelum masuk queue jika sudah ada Task 3-7
            $task99Pending = $data_taskID_model->where('taskid', 99)->where('reupload', 1)->get();
            if ($task99Pending->isNotEmpty()) {
                Log::info("[$urlQL] Melakukan pengecekan proaktif untuk " . $task99Pending->count() . " Task 99.");
                foreach ($task99Pending as $t99) {
                    $hasActiveTasks = $data_taskID_model->where('kodebooking', $t99->kodebooking)
                                                        ->whereIn('taskid', [3, 4, 5, 6, 7])
                                                        ->exists();
                    if ($hasActiveTasks) {
                        $data_taskID_model->where('id', $t99->id)->update([
                            'reupload' => 0,
                            'message' => 'Skipped: Sudah ada Task Aktif (3-7) di lokal'
                        ]);
                        Log::info("[$urlQL] Proaktif membatalkan Task 99 untuk {$t99->kodebooking} karena DB lokal memiliki task aktif (3-7)");
                    }
                }
            }

            $terminalTasks = $data_taskID_model->where('reupload', 1)
                ->where(function ($q) {
                    $q->where('message', 'like', '%TaskId terakhir 7%')
                      ->orWhere('message', 'like', '%TaskId terakhir 5%');
                })->get();
                
            if ($terminalTasks->isNotEmpty()) {
                Log::info("[$urlQL] Melakukan pembersihan proaktif untuk " . $terminalTasks->count() . " Task berstatus error permanen.");
                foreach ($terminalTasks as $tt) {
                    $data_taskID_model->where('id', $tt->id)->update([
                        'reupload' => 0,
                    ]);
                }
            }

            // Ambil semua data yang memenuhi syarat pengiriman (Reupload = 1)
            $dataArray = $data_taskID_model->from($tableTaskID . ' as taskID')
                ->leftJoin("$tableKodeBooking as dk", 'taskID.kodebooking', '=', 'dk.kodebooking')
                ->where("taskID.reupload", 1)
                ->where(function ($query) use ($tanggal, $isEmptyTanggal, $kodebooking) {
                    if (!empty($kodebooking)) {
                        $query->where('taskID.kodebooking', $kodebooking);
                    } else {
                        // Selalu sertakan TaskID 99 (Pembatalan) agar tidak tertunda filter tanggal pelayanan
                        $query->where('taskID.taskid', 99)
                            ->orWhere(function ($q) use ($tanggal, $isEmptyTanggal) {
                                    if ($isEmptyTanggal) {
                                        $startDate = Carbon::parse($tanggal)->subDays(7)->toDateString();
                                        $endDate = Carbon::parse($tanggal)->toDateString();
                                        
                                        $q->whereBetween('dk.tanggalperiksa', [$startDate, $endDate])
                                            ->orWhere(function($subQ) use ($startDate, $endDate) {
                                                // Fallback ke taskID.waktu jika dk tidak ada
                                                $subQ->whereNull('dk.kodebooking')
                                                    ->whereRaw("DATE(FROM_UNIXTIME(taskID.waktu/1000)) BETWEEN ? AND ?", [$startDate, $endDate]);
                                            });
                                    } else {
                                        $q->where('dk.tanggalperiksa', '=', $tanggal)
                                            ->orWhere(function($subQ) use ($tanggal) {
                                                // Fallback ke taskID.waktu jika dk tidak ada
                                                $subQ->whereNull('dk.kodebooking')
                                                  ->whereRaw("DATE(FROM_UNIXTIME(taskID.waktu/1000)) = ?", [$tanggal]);
                                            });
                                    }
                            });
                    }
                })
                ->orderBy("taskID.kodebooking")
                ->orderBy("taskID.taskid")
                ->groupBy("taskID.id")
                ->get(["taskID.*"])
                ->toArray();

            $totalRecords = count($dataArray);
            if (empty($dataArray)) {
                Log::info("[taskID_otomatis - $urlQL] No data found to process. Skipping dispatch.");
            } else {
                $batchSize = 30;
                $chunks = array_chunk($dataArray, $batchSize);
                $totalJobs = count($chunks);

                Log::info("[taskID_otomatis - $urlQL] Dispatching $totalRecords records into $totalJobs background jobs.");

                foreach ($chunks as $chunk) {
                    \App\Jobs\SendTaskIdBatchJob::dispatch($chunk, $urlQL);
                }
                Log::info("=== [taskID_otomatis - $urlQL] === ALL JOBS DISPATCHED. Process finished.");
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

    public function taskID_single_arr($urlQL, $data, $model = null)
    {
        // Gunakan model yang dikirimkan atau buat baru jika tidak ada
        if (!$model) {
            $model = (new data_taskid())->setTableByQL($urlQL);
        }

        // Fetch FRESH record from DB to avoid stale data from batch/job
        $freshData = $model->find($data['id']);
        if ($freshData && $freshData->reupload == 0) {
            Log::info("[taskID_single_arr - $urlQL] SKIPPED: {$data['kodebooking']} Task {$data['taskid']} already SUCCESS in DB.");
            return response()->json([
                'metadata' => ['code' => 200, 'message' => 'Already processed successfully']
            ]);
        }
        
        // Use fresh data for processing
        if ($freshData) {
            $data = $freshData->toArray();
        }

        Log::info("[taskID_single_arr - $urlQL] Processing: {$data['kodebooking']} (Task: {$data['taskid']})");

        // RULE: Task 6 can only be sent if Task 5 is SUCCESS (Code 200/208).
        // RULE: Task 7 can only be sent if Task 6 is SUCCESS (Code 200/208).
        if ($data['taskid'] == 6 || $data['taskid'] == 7) {
            $prevTaskID = ($data['taskid'] == 6) ? 5 : 6;
            $prevTask = $model->where('kodebooking', $data['kodebooking'])
                              ->where('taskid', $prevTaskID)
                              ->first();
            
            $isPrevOk = $prevTask && in_array((int)$prevTask->code, [200, 208]);
            
            if (!$isPrevOk) {
                Log::warning("[taskID_single_arr - $urlQL] PENDING: Task {$data['taskid']} for {$data['kodebooking']} is waiting for Task $prevTaskID to be SUCCESS (200/208).");
                return response()->json([
                    'metadata' => [
                        'code' => 202, 
                        'message' => "Pending: Menunggu Task $prevTaskID sukses (Code 200/208) sebelum mengirim Task {$data['taskid']}."
                    ]
                ], 202);
            }
        }

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
        $data_taskID = (new data_taskid())->setTableByQL($urlQL);

        $pattern = '/Tanggal pelayanan untuk Kode Booking tersebut adalah \((\d{4}-\d{2}-\d{2})\)/';
        if (preg_match($pattern, $message, $matches)) {
            if ($data['taskid'] == 99) {
                Log::info('=== [taskID_99] ===');
                $tanggalPelayanan = $matches[1];
                $zonaWaktu = 'Asia/Jakarta';
                
                // Jika tanggal pelayanan masih di masa depan, gunakan waktu sekarang agar tidak ditolak BPJS (future timestamp)
                if (Carbon::parse($tanggalPelayanan)->isFuture()) {
                    $timestampMilidetik = now()->timestamp * 1000;
                    Log::info("[taskID_single_arr - $urlQL] [AUTO_FIX] Task 99 Future Booking. Menggunakan waktu sekarang.");
                } else {
                    $jam_report = '23:59:00';
                    $tanggalWaktu = $tanggalPelayanan . ' ' . $jam_report;
                    $timestampMilidetik = Carbon::createFromFormat('Y-m-d H:i:s', $tanggalWaktu, $zonaWaktu)->timestamp * 1000;
                    Log::info("[taskID_single_arr - $urlQL] [AUTO_FIX] Task 99 Past Booking. Menggunakan waktu akhir hari pelayanan.");
                }
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
                $response_code = $listtask_response['metaData']['code'] ?? $listtask_response['metadata']['response']['metaData']['code'] ?? $listtask_response['metadata']['code'] ?? 500;
                $response_list = $listtask_response['response'] ?? $listtask_response['list'] ?? $listtask_response['metadata']['response']['response'] ?? [];

                if ($response_code == 200 && !empty($response_list) && is_array($response_list)) {
                    $task99 = $response_list;

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
                        $task = array_filter($response_list, function ($item) use ($data) {
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
                            // Log::info('[Beda tanggal - ' . $urlQL . '] Task dengan taskid = ' . ($data['taskid'] - 1) . ' tidak ditemukan.');
                        }
                    }

                }
            }
        }

        // Logika AUTO_FIX dipindahkan ke bawah setelah mendapat respons dari BPJS agar bisa retry instan.

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
        if ((int)$data['reupload'] === 1) {
            Log::info('Reupload = 1');

            // Proteksi Task 99: Jika sudah ada Task 3, 4, 5, atau 7 di DB lokal atau BPJS, batalkan Task 99
            if ($data['taskid'] == 99) {
                $hasActiveTasks = false;
                
                // Cek DB lokal terlebih dahulu
                $localActiveTasks = $model->where('kodebooking', $kodebooking)
                                          ->whereIn('taskid', [3, 4, 5, 6, 7])
                                          ->exists();
                
                if ($localActiveTasks) {
                    $hasActiveTasks = true;
                    Log::info("[taskID_single_arr - $urlQL] Task 99 dibatalkan karena DB lokal memiliki task aktif (3-7) untuk $kodebooking.");
                } else {
                    // Fallback cek ke BPJS
                    $listtask_response = $this->listtask($urlQL, $kodebooking);
                    $response_list = $listtask_response['response'] ?? $listtask_response['list'] ?? $listtask_response['metadata']['response']['response'] ?? [];
                    
                    foreach ($response_list as $t) {
                        if (in_array($t['taskid'], [3, 4, 5, 6, 7])) {
                            $hasActiveTasks = true;
                            Log::info("[taskID_single_arr - $urlQL] Task 99 dibatalkan karena server BPJS memiliki task aktif (3-7) untuk $kodebooking.");
                            break;
                        }
                    }
                }

                if ($hasActiveTasks) {
                    Log::info('[taskID_single_arr] SKIPPED: Task 99 dibatalkan untuk ' . $kodebooking . ' karena sudah ada Task Aktif (3-7).');
                    $taskIDData['reupload'] = 0;
                    $taskIDData['code'] = 200;
                    $taskIDData['message'] = 'Skipped: Sudah ada Task Aktif (3-7) di lokal atau BPJS';
                    
                    $taskID = $model->updateOrCreate(['id' => $data['id']], $taskIDData);
                    
                    return response()->json([
                        'metadata' => ['code' => 200, 'message' => 'Task 99 dibatalkan (Data sudah aktif)']
                    ], 200);
                }
            }

            $endpoint = '/antrean/updatewaktu';
            try {
                // INTERNAL RETRY LOGIC FOR TIMEOUTS
                $maxRetries = 3;
                $attempt = 0;
                $response = null;

                while ($attempt < $maxRetries) {
                    try {
                        $attempt++;
                        $response = BpjsHelper::postRequestDirect($urlQL, $endpoint, $taskIDData);
                        break; // Success, exit retry loop
                    } catch (\Exception $e) {
                        $errorMsg = $e->getMessage();
                        
                        // Check if it's a cURL timeout or connection issue
                        if (strpos($errorMsg, 'cURL error 28') !== false || strpos($errorMsg, 'timed out') !== false) {
                            if ($attempt < $maxRetries) {
                                Log::warning("[taskID_single_arr - $urlQL] cURL Timeout. Retrying attempt $attempt/$maxRetries for {$kodebooking}...");
                                sleep(2); // Wait 2 seconds before retry
                                continue;
                            }
                        }
                        
                        // If it's not a timeout or we ran out of retries, throw the exception to the outer catch
                        throw $e;
                    }
                }
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

                // Menyimpan code, request, dan response
                $taskIDData['request'] = json_encode($taskIDData);
                $taskIDData['response'] = json_encode($response_decode);
                // BPJS Antrol langsung pakai 'metaData', via SIRSTQL pakai 'metadata'
                $meta = $response_decode['metaData'] ?? $response_decode['metadata'] ?? [];
                $taskIDData['code']    = $meta['code'] ?? null;
                $taskIDData['message'] = $meta['message'] ?? null;
                Log::info("[taskID_single_arr - $urlQL] BPJS_RESPONSE: {$data['kodebooking']} Task {$data['taskid']} -> Code: " . ($taskIDData['code'] ?? 'null') . ", Message: " . ($taskIDData['message'] ?? 'null'));

                if (
                    in_array($meta['code'], [200, 208]) ||
                    ($meta['message'] ?? '') === 'TaskId terakhir 99'
                ) {
                    $taskIDData['reupload'] = 0;
                } else {
                    $taskIDData['reupload'] = 1;
                }
                $taskID = $model->updateOrCreate(
                    [
                         'id' => $data['id'] 
                        // 'kodebooking' => $taskIDData['kodebooking'],
                        // 'taskid' => $taskIDData['taskid']
                    ],
                    $taskIDData
                );

                if ($taskID && $taskID instanceof \Illuminate\Database\Eloquent\Model) {
                    Log::info("[taskID_single_arr - $urlQL] DB_SAVED: {$data['kodebooking']} (Task: {$data['taskid']}, Status: " . ($taskID->reupload == 0 ? 'SUCCESS' : 'PENDING') . ")");
                } else {
                    Log::error('Error: taskID_single_arr was not saved correctly.');
                }


                $message_response = $meta['message'] ?? '';

                // --- START IMPROVED AUTO_FIX & RETRY LOGIC ---
                $isTimeError = (strpos($message_response, 'tidak boleh kurang') !== false || 
                                strpos($message_response, 'Waktu tidak valid') !== false ||
                                strpos($message_response, 'Tanggal pelayanan') !== false ||
                                strpos($message_response, 'maksimal H+7') !== false ||
                                strpos($message_response, 'Expired: Lebih dari H+7') !== false);
                $isMissingTaskError = (strpos($message_response, 'belum terkirim') !== false || strpos($message_response, 'belum ada') !== false);

                if ($meta['code'] == 201 && ($isTimeError || $isMissingTaskError)) {
                    Log::info("[taskID_single_arr - $urlQL] [AUTO_FIX] Mendeteksi error 201: $message_response. Mencoba perbaikan instan...");
                    
                    $listtask_response = $this->listtask($urlQL, $kodebooking);
                    $response_list = $listtask_response['response'] ?? $listtask_response['list'] ?? $listtask_response['metadata']['response']['response'] ?? [];
                    $response_code = $listtask_response['metaData']['code'] ?? $listtask_response['metadata']['response']['metaData']['code'] ?? $listtask_response['metadata']['code'] ?? 500;

                    if ($response_code == 200 && !empty($response_list) && is_array($response_list)) {
                        // Urutkan untuk dapat task terakhir di BPJS
                        usort($response_list, function ($a, $b) { return $b['taskid'] - $a['taskid']; });
                        $lastBPJSTask = $response_list[0];
                        $lastTaskID = $lastBPJSTask['taskid'];

                        if ($isTimeError) {
                            if ($lastTaskID >= $taskIDData['taskid']) {
                                Log::info("[taskID_single_arr - $urlQL] [AUTO_FIX] Ternyata Task {$taskIDData['taskid']} sudah ada di BPJS. Sukses.");
                                $taskIDData['reupload'] = 0;
                                $taskIDData['code'] = 208;
                                $taskIDData['message'] = "TaskId={$taskIDData['taskid']} sudah ada (Auto Fixed)";
                            } else {
                                // Koreksi waktu dan RETRY
                                $cleanWakturs = str_replace(' WIB', '', $lastBPJSTask['wakturs']);
                                $waktursTimestamp = strtotime($cleanWakturs);
                                if ($waktursTimestamp) {
                                    $randomSecond = rand(60, 300);
                                    $taskIDData['waktu'] = ($waktursTimestamp + $randomSecond) * 1000;
                                    Log::info("[taskID_single_arr - $urlQL] [AUTO_FIX] Koreksi waktu -> Retry kirim Task {$taskIDData['taskid']}...");
                                    
                                    $retry_res = BpjsHelper::postRequestDirect($urlQL, $endpoint, $taskIDData);
                                    $retry_decode = json_decode($retry_res, true);
                                    $retry_meta = $retry_decode['metaData'] ?? $retry_decode['metadata'] ?? [];
                                    
                                    $taskIDData['response'] = $retry_res;
                                    $taskIDData['code'] = $retry_meta['code'] ?? 500;
                                    $taskIDData['message'] = $retry_meta['message'] ?? 'Retry Failed';
                                    $taskIDData['reupload'] = (in_array($taskIDData['code'], [200, 208])) ? 0 : 1;
                                    Log::info("[taskID_single_arr - $urlQL] [AUTO_FIX] Hasil Retry: {$taskIDData['code']} - {$taskIDData['message']}");
                                }
                            }
                        } else if ($isMissingTaskError) {
                            // Cari taskid berapa yang hilang (biasanya taskid - 1)
                            $missingTaskID = $taskIDData['taskid'] - 1;
                            Log::info("[taskID_single_arr - $urlQL] [AUTO_FIX] Task $missingTaskID hilang. Mencoba membuat task cadangan dari data getlisttask...");
                            
                            $cleanWakturs = str_replace(' WIB', '', $lastBPJSTask['wakturs']);
                            $waktursTimestamp = strtotime($cleanWakturs);
                            if ($waktursTimestamp) {
                                $randomSecond = rand(60, 180);
                                $newWaktu = ($waktursTimestamp + $randomSecond) * 1000;
                                
                                $data_additional = [
                                    'kodebooking' => $kodebooking,
                                    'taskid' => $missingTaskID,
                                    'waktu' => $newWaktu,
                                    'idpendaftaran' => $data['idpendaftaran'] ?? null,
                                    'tanggal' => date('Y-m-d', $newWaktu/1000),
                                    'jam' => date('H:i:s', $newWaktu/1000),
                                    'reupload' => 1,
                                    'message' => 'Auto Created (Missing Task Detection)'
                                ];
                                $created_task = $model->updateOrCreate(['kodebooking' => $kodebooking, 'taskid' => $missingTaskID], $data_additional);
                                Log::info("[taskID_single_arr - $urlQL] [AUTO_FIX] Task $missingTaskID berhasil dibuat di DB. Mengirimkan sekarang secara sekuensial...");

                                // Kirim missing task sekarang secara langsung
                                $missingTaskData = $created_task->toArray();
                                $this->taskID_single_arr($urlQL, $missingTaskData, $model);

                                // Cek apakah task yang hilang berhasil dikirim
                                $check_missing = $model->where('kodebooking', $kodebooking)->where('taskid', $missingTaskID)->first();
                                if ($check_missing && (int)$check_missing->reupload === 0) {
                                    Log::info("[taskID_single_arr - $urlQL] [AUTO_FIX] Task $missingTaskID berhasil terkirim. Mengulang kembali pengiriman Task {$data['taskid']}...");
                                    
                                    // Retry mengirim task yang sekarang
                                    $retry_res = BpjsHelper::postRequestDirect($urlQL, $endpoint, $taskIDData);
                                    $retry_decode = json_decode($retry_res, true);
                                    $retry_meta = $retry_decode['metaData'] ?? $retry_decode['metadata'] ?? [];
                                    
                                    $taskIDData['response'] = $retry_res;
                                    $taskIDData['code'] = $retry_meta['code'] ?? 500;
                                    $taskIDData['message'] = $retry_meta['message'] ?? 'Retry Failed';
                                    $taskIDData['reupload'] = (in_array($taskIDData['code'], [200, 208])) ? 0 : 1;
                                    Log::info("[taskID_single_arr - $urlQL] [AUTO_FIX] Hasil Retry Task {$data['taskid']}: {$taskIDData['code']} - {$taskIDData['message']}");
                                }
                            }
                        }
                    } else {
                        // Jika BPJS mengembalikan 204 No Content, atau list kosong, kita harus tebak waktu dari waktu request sekarang!
                        if ($isTimeError) {
                            $newWaktuMs = $taskIDData['waktu'];
                            
                            // Ekstrak tanggal pelayanan dari pesan error BPJS (misal: "Tanggal pelayanan untuk Kode Booking tersebut adalah (2026-04-22)")
                            if (preg_match('/Tanggal pelayanan untuk Kode Booking tersebut adalah \((.*?)\)/', $message_response, $matches)) {
                                $correctDateStr = $matches[1];
                                
                                // Coba cari waktu task sebelumnya di DB lokal agar sekuensial!
                                $prevTask = $model->where('kodebooking', $kodebooking)->where('taskid', $taskIDData['taskid'] - 1)->whereNotNull('waktu')->first();
                                if ($prevTask && $prevTask->waktu) {
                                    $newWaktuMs = $prevTask->waktu + (rand(60, 300) * 1000);
                                    Log::info("[taskID_single_arr - $urlQL] [AUTO_FIX] Mengoreksi waktu Task {$taskIDData['taskid']} berdasarkan Task sebelumnya menjadi: " . date('Y-m-d H:i:s', $newWaktuMs / 1000));
                                } else {
                                    // Fallback: gunakan tanggal yang benar dengan jam aman (misal 12:XX:00)
                                    $safeTimeStr = $correctDateStr . ' 12:' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT) . ':00';
                                    $newWaktuMs = strtotime($safeTimeStr) * 1000;
                                    Log::info("[taskID_single_arr - $urlQL] [AUTO_FIX] Mengoreksi waktu Task {$taskIDData['taskid']} secara paksa ke tanggal pelayanan $correctDateStr");
                                }
                            } else {
                                // Fallback: ekstrak tanggal pelayanan dari string kodebooking (YYYYMMDD)
                                $trueDateFromKB = substr($kodebooking, 0, 4) . '-' . substr($kodebooking, 4, 2) . '-' . substr($kodebooking, 6, 2);
                                
                                // Coba cari waktu task sebelumnya di DB lokal
                                $prevTask = $model->where('kodebooking', $kodebooking)->where('taskid', $taskIDData['taskid'] - 1)->whereNotNull('waktu')->first();
                                if ($prevTask && $prevTask->waktu) {
                                    $newWaktuMs = $prevTask->waktu + (rand(60, 300) * 1000);
                                    Log::info("[taskID_single_arr - $urlQL] [AUTO_FIX] Mengoreksi waktu Task {$taskIDData['taskid']} berdasarkan Task sebelumnya menjadi: " . date('Y-m-d H:i:s', $newWaktuMs / 1000));
                                } else {
                                    $safeTimeStr = $trueDateFromKB . ' 12:' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT) . ':00';
                                    $newWaktuMs = strtotime($safeTimeStr) * 1000;
                                    Log::info("[taskID_single_arr - $urlQL] [AUTO_FIX] Mengoreksi waktu Task {$taskIDData['taskid']} secara paksa ke tanggal pelayanan $trueDateFromKB berdasarkan kodebooking");
                                }
                            }
                            $taskIDData['waktu'] = $newWaktuMs;
                            Log::info("[taskID_single_arr - $urlQL] [AUTO_FIX] List BPJS Kosong & Error Waktu -> Koreksi waktu & Retry kirim Task {$taskIDData['taskid']}...");
                            
                            $retry_res = BpjsHelper::postRequestDirect($urlQL, $endpoint, $taskIDData);
                            $retry_decode = json_decode($retry_res, true);
                            $retry_meta = $retry_decode['metaData'] ?? $retry_decode['metadata'] ?? [];
                            
                            $taskIDData['response'] = $retry_res;
                            $taskIDData['code'] = $retry_meta['code'] ?? 500;
                            $taskIDData['message'] = $retry_meta['message'] ?? 'Retry Failed';
                            $taskIDData['reupload'] = (in_array($taskIDData['code'], [200, 208])) ? 0 : 1;
                            Log::info("[taskID_single_arr - $urlQL] [AUTO_FIX] Hasil Retry: {$taskIDData['code']} - {$taskIDData['message']}");
                        } else if ($isMissingTaskError) {
                            $missingTaskID = $taskIDData['taskid'] - 1;
                            Log::info("[taskID_single_arr - $urlQL] [AUTO_FIX] Task $missingTaskID hilang (BPJS List Kosong/204). Mencoba membuat task cadangan...");
                            
                            $trueDate = substr($kodebooking, 0, 4) . '-' . substr($kodebooking, 4, 2) . '-' . substr($kodebooking, 6, 2);
                            $prevTask = $model->where('kodebooking', $kodebooking)->where('taskid', $missingTaskID - 1)->whereNotNull('waktu')->first();
                            
                            if ($prevTask && $prevTask->waktu) {
                                $randomSecond = rand(60, 180);
                                $newWaktu = $prevTask->waktu + ($randomSecond * 1000);
                            } else {
                                // Fallback: Buat waktu aman di tanggal pelayanan
                                $safeTimeStr = $trueDate . ' 08:' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT) . ':' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
                                $newWaktu = strtotime($safeTimeStr) * 1000;
                            }
                            
                            $data_additional = [
                                'kodebooking' => $kodebooking,
                                'taskid' => $missingTaskID,
                                'waktu' => $newWaktu,
                                'idpendaftaran' => $data['idpendaftaran'] ?? null,
                                'tanggal' => date('Y-m-d', $newWaktu/1000),
                                'jam' => date('H:i:s', $newWaktu/1000),
                                'reupload' => 1,
                                'message' => 'Auto Created (Missing Task Detection - No Content)'
                            ];
                            $created_task = $model->updateOrCreate(['kodebooking' => $kodebooking, 'taskid' => $missingTaskID], $data_additional);
                                Log::info("[taskID_single_arr - $urlQL] [AUTO_FIX] Task $missingTaskID berhasil dibuat di DB dari fallback waktu. Mengirimkan sekarang...");

                                // Kirim missing task sekarang secara langsung
                                $missingTaskData = $created_task->toArray();
                                $this->taskID_single_arr($urlQL, $missingTaskData, $model);

                                // Cek apakah task yang hilang berhasil dikirim
                                $check_missing = $model->where('kodebooking', $kodebooking)->where('taskid', $missingTaskID)->first();
                                if ($check_missing && (int)$check_missing->reupload === 0) {
                                    Log::info("[taskID_single_arr - $urlQL] [AUTO_FIX] Task $missingTaskID berhasil terkirim. Mengulang kembali pengiriman Task {$data['taskid']}...");
                                    
                                    // Retry mengirim task yang sekarang
                                    $retry_res = BpjsHelper::postRequestDirect($urlQL, $endpoint, $taskIDData);
                                    $retry_decode = json_decode($retry_res, true);
                                    $retry_meta = $retry_decode['metaData'] ?? $retry_decode['metadata'] ?? [];
                                    
                                    $taskIDData['response'] = $retry_res;
                                    $taskIDData['code'] = $retry_meta['code'] ?? 500;
                                    $taskIDData['message'] = $retry_meta['message'] ?? 'Retry Failed';
                                    $taskIDData['reupload'] = (in_array($taskIDData['code'], [200, 208])) ? 0 : 1;
                                    Log::info("[taskID_single_arr - $urlQL] [AUTO_FIX] Hasil Retry Task {$data['taskid']}: {$taskIDData['code']} - {$taskIDData['message']}");
                                }
                            }
                        }
                    
                    // Update DB dengan hasil perbaikan terbaru
                    $model->updateOrCreate(['id' => $data['id']], $taskIDData);
                    
                    // Use retry response for the final return if it exists
                    if (isset($retry_decode)) {
                        $response_decode = $retry_decode;
                    }
                } else if ($meta['code'] == 201 && strpos($message_response, 'Kode Booking tidak ditemukan') !== false) {
                    Log::info("[taskID_single_arr - $urlQL] [AUTO_FIX] Kode Booking belum ditemukan di BPJS. Akan dicoba lagi nanti.");
                    $taskIDData['reupload'] = 1;
                    $taskIDData['message'] = "Pending: Kode Booking belum ditemukan di BPJS (Akan dicoba lagi)";
                    $model->updateOrCreate(['id' => $data['id']], $taskIDData);
                    
                    // Keep original response for return
                }

                // Logika lama "TaskId=X belum ada" tetap dipertahankan sebagai backup
                // --- PERBAIKAN SELESAI ---
                // Logika lama yang duplikat sudah dihapus dan disatukan di atas.
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

            $data_taskID = (new data_taskid())->setTableByQL($urlQL);
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

        $urlQL = strtoupper($request->query('urlQL', $request->input('urlQL', 'QLJ')));
        $endpoint = '/antrean/getlisttask';
        try {
            $response = BpjsHelper::postRequestDirect($urlQL, $endpoint, $request->all());
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

    public function listtask($urlQL, $kodebooking)
    {
        // Cek cache terlebih dahulu
        if (isset($this->listTaskCache[$kodebooking])) {
            Log::info("[listtask - $urlQL] CACHE_HIT: $kodebooking");
            return $this->listTaskCache[$kodebooking];
        }

        // Log::info("[listtask - $urlQL] FETCHING: $kodebooking");
        $data['kodebooking'] = $kodebooking;

        $endpoint = '/antrean/getlisttask';
        try {
            $response = BpjsHelper::postRequestDirect($urlQL, $endpoint, $data);
            $response_decode = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'metadata' => [
                        'code' => 500,
                        'message' => '[listtask - ' . $urlQL . '] Terjadi kesalahan pada decoding respons.',
                    ],
                ];
            }

            // Simpan ke cache jika sukses atau data ditemukan
            if (is_array($response_decode) && !empty($response_decode)) {
                $this->listTaskCache[$kodebooking] = $response_decode;
                // Log::info('[listtask - ' . $urlQL . '] listtask cached for ' . $kodebooking);
            }
            
            return $response_decode;
        } catch (\Exception $e) {
            Log::error('[listtask - ' . $urlQL . '] Failed to fetch for ' . $kodebooking . ':', ['error' => $e->getMessage()]);
            return [
                'metadata' => [
                    'code' => 500,
                    'message' => $urlQL . ' - Terjadi kesalahan pada server.',
                ],
                'error' => $e->getMessage(),
            ];
        }
    }

    public function taskID_single_request($urlQL = 'QLJ', Request $data)
    {
        Log::info($urlQL . 'taskID_single_request method reached');
        $data_taskID = [
            'kodebooking' => $data['kodebooking'],
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

        $endpoint = '/antrean/updatewaktu';
        try {
            $response = BpjsHelper::postRequestDirect($urlQL, $endpoint, $data->all());
            $response_decode = json_decode($response, true);

            $meta = $response_decode['metaData'] ?? $response_decode['metadata'] ?? [];

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'metadata' => [
                        'code' => 500,
                        'message' => 'Terjadi kesalahan pada decoding respons.',
                    ],
                ], 500);
            }

            $data_taskID['code']     = $meta['code'] ?? null;
            $data_taskID['message']  = $meta['message'] ?? null;
            $data_taskID['response'] = json_encode($response_decode);

            $modelTaskID = (new data_taskid())->setTableByQL($urlQL);
            $taskID = $modelTaskID->updateOrCreate(
                [
                    'kodebooking' => $data['kodebooking'],
                    'taskid'      => $data_taskID['taskid']
                ],
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

        $endpoint = '/antrean/updatewaktu';
        try {
            $response = BpjsHelper::postRequestDirect($urlQL, $endpoint, $request->all());
            $response_decode = json_decode($response, true);

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
            $meta = $response_decode['metaData'] ?? $response_decode['metadata'] ?? [];
            $taskIDData['code']    = $meta['code'] ?? null;
            $taskIDData['message'] = $meta['message'] ?? null;
            $taskIDData['request']  = json_encode($taskIDData);
            $taskIDData['response'] = json_encode($response_decode);

            if (
                in_array($taskIDData['code'], [200, 208]) ||
                ($taskIDData['message'] ?? '') === 'TaskId terakhir 99'
            ) {
                $taskIDData['reupload'] = 0;
            } else {
                $taskIDData['reupload'] = 1;
            }
            $data_taskID = (new data_taskid())->setTableByQL($urlQL);
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
