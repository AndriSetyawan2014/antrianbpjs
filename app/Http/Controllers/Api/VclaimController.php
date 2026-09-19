<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\BpjsHelper;
use App\Jobs\SyncVclaimKunjunganJob;
use App\Models\VclaimKunjungan;
use App\Models\VclaimSyncLog;
use App\Models\AntreanPerTanggalLog;
use App\Models\AntreanPerTanggalSyncLog;
use App\Jobs\SyncAntreanPerTanggalJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VclaimController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
    }
    // ─────────────────────────────────────────────────────────────────────────
    //  1. SYNC — Fetch dari BPJS VClaim, simpan ke DB, untuk semua / satu QL
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Sync data kunjungan rawat jalan dari BPJS VClaim ke database lokal.
     * Mendukung single QL (via ?urlQL=QLJ) atau semua QL sekaligus.
     *
     * GET/POST /api/vclaim/sync-kunjungan-jalan?tanggal=2026-04-21&urlQL=QLJ
     */
    public function syncKunjunganJalan(Request $request)
    {
        $tanggal      = $request->input('tanggal', Carbon::today()->toDateString());
        $urlQLParam   = strtoupper($request->input('urlQL', ''));
        $availableQLs = BpjsHelper::getUrlQLOptions();

        $targetQLs = ($urlQLParam && in_array($urlQLParam, $availableQLs))
            ? [$urlQLParam]
            : $availableQLs;

        // PAKSA eksekusi sinkron tanpa bergantung pada parameter frontend
        // Ini untuk memastikan jika browser masih ter-cache, API tetap menggunakan logika inline terbaru
        $syncNow = true;
        
        $syncIds = [];
        $totalData = 0;
        $hasError = false;

        foreach ($targetQLs as $urlQL) {
            // Buat log record dulu, kemudian dispatch job
            $log = VclaimSyncLog::create([
                'kode_ql' => $urlQL,
                'tanggal' => $tanggal,
                'status'  => 'pending',
            ]);

            if ($syncNow) {
                try {
                    $log->update(['status' => 'processing', 'started_at' => now()]);
                    $data = \App\Helpers\BpjsHelper::getVclaimDataKunjungan($urlQL, $tanggal, 2);

                    if (empty($data['sep'])) {
                        $log->update([
                            'status'      => 'success',
                            'total_data'  => 0,
                            'message'     => 'Tidak ada data kunjungan rawat jalan.',
                            'finished_at' => now(),
                        ]);
                    } else {
                        $kodePPK = env('BPJS_VCLAIM_KODEPPK_' . $urlQL);
                        
                        $upsertData = [];
                        foreach ($data['sep'] as $sep) {
                            $upsertData[] = [
                                'kode_ql'             => $urlQL,
                                'no_sep'              => $sep['noSep'] ?? null,
                                'kode_ppk'            => $kodePPK,
                                'tgl_kunjungan'       => $tanggal,
                                'jns_pelayanan'       => 2,
                                'no_kartu'            => $sep['noKartu'] ?? null,
                                'no_rujukan'          => $sep['noRujukan'] ?? null,
                                'nama'                => $sep['nama'] ?? null,
                                'diagnosa'            => $sep['diagnosa'] ?? null,
                                'poli'                => $sep['poli'] ?? null,
                                'kelas_rawat'         => $sep['kelasRawat'] ?? null,
                                'jns_pelayanan_label' => $sep['jnsPelayanan'] ?? 'R.Jalan',
                                'tgl_sep'             => $sep['tglSep'] ?? null,
                                'tgl_plg_sep'         => $sep['tglPlgSep'] ?? null,
                                'raw_response'        => is_array($sep) ? json_encode($sep) : $sep,
                                'sync_status'         => 'success',
                                'sync_message'        => null,
                                'synced_at'           => now()->format('Y-m-d H:i:s'),
                            ];
                        }

                        // Lakukan Mass-Upsert (Ribuan query dijadikan HANYA 1 query super cepat)
                        if (!empty($upsertData)) {
                            \App\Models\VclaimKunjungan::upsert(
                                $upsertData,
                                ['kode_ql', 'no_sep'], // Kolom unique index (uq_ql_no_sep)
                                [
                                    'kode_ppk', 'tgl_kunjungan', 'jns_pelayanan', 'no_kartu', 'no_rujukan', 
                                    'nama', 'diagnosa', 'poli', 'kelas_rawat', 'jns_pelayanan_label', 
                                    'tgl_sep', 'tgl_plg_sep', 'raw_response', 'sync_status', 'sync_message', 'synced_at'
                                ] // Kolom yang diupdate jika data sudah ada
                            );
                        }
                        
                        $count = count($upsertData);

                        $log->update([
                            'status'      => 'success',
                            'total_data'  => $count,
                            'finished_at' => now(),
                        ]);
                        $totalData += $count;
                        Log::info("[VclaimController] Inline Sync Sukses — QL={$urlQL}, Tanggal={$tanggal}, Data={$count}");
                    }
                } catch (\Throwable $e) {
                    $hasError = true;
                    $log->update([
                        'status'      => 'error',
                        'message'     => $e->getMessage(),
                        'finished_at' => now(),
                    ]);
                    Log::error("[VclaimController] Error Inline Sync: " . $e->getMessage());
                }
            } else {
                SyncVclaimKunjunganJob::dispatch($urlQL, $tanggal, $log->id);
                Log::info("[VclaimController] Job dijadwalkan ke antrean — QL={$urlQL}, Tanggal={$tanggal}, LogID={$log->id}");
            }

            $syncIds[] = $log->id;
        }

        // ✅ Return SEGERA — tidak menunggu proses selesai (kecuali sync_now=true, proses sudah selesai)
        return response()->json([
            'metadata' => [
                'code'     => $hasError ? 500 : ($syncNow ? 200 : 202),
                'message'  => count($syncIds) . ($syncNow ? ' job sync selesai dieksekusi.' : ' job sync dijadwalkan ke background queue.'),
                'tanggal'  => $tanggal,
            ],
            'sync_ids' => $syncIds,
            'total_data' => $totalData,
            'has_error' => $hasError,
        ], $hasError ? 500 : ($syncNow ? 200 : 202));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  1c. STATUS POLLING — Cek progress sync job dari frontend
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Cek status progress sync berdasarkan array sync_ids.
     * GET /api/vclaim/sync-status?ids[]=1&ids[]=2
     */
    public function syncStatus(Request $request)
    {
        $ids  = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json(['metadata' => ['code' => 422, 'message' => 'Parameter ids wajib diisi.']], 422);
        }

        $logs = VclaimSyncLog::whereIn('id', $ids)
            ->get(['id', 'kode_ql', 'tanggal', 'status', 'total_data', 'message', 'started_at', 'finished_at']);

        $isDone   = $logs->every(fn ($l) => in_array($l->status, ['success', 'error']));
        $hasError = $logs->some(fn ($l) => $l->status === 'error');
        $totalSync = $logs->sum('total_data');

        return response()->json([
            'metadata'   => ['code' => 200, 'message' => 'OK'],
            'logs'       => $logs,
            'is_done'    => $isDone,
            'has_error'  => $hasError,
            'total_sync' => $totalSync,
        ]);
    }


    // ─────────────────────────────────────────────────────────────────────────
    //  1b. SYNC RANGE — Sync data kunjungan dari rentang tanggal
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Sync data kunjungan rawat jalan untuk rentang tanggal (date range).
     *
     * GET/POST /api/vclaim/sync-kunjungan-jalan-range
     *   ?tgl_mulai=2026-04-01
     *   &tgl_akhir=2026-04-21     (default: hari ini)
     *   &urlQL=QLJ                (optional, default: semua QL)
     *   &limit_hari=31            (optional, max hari, default 31)
     */
    public function syncKunjunganJalanRange(Request $request)
    {
        $request->validate([
            'tgl_mulai'  => 'required|date_format:Y-m-d',
            'tgl_akhir'  => 'nullable|date_format:Y-m-d',
            'urlQL'      => 'nullable|string',
            'limit_hari' => 'nullable|integer|min:1|max:365',
        ]);

        $tglMulai   = Carbon::parse($request->input('tgl_mulai'));
        $tglAkhir   = Carbon::parse($request->input('tgl_akhir', Carbon::today()->toDateString()));
        $urlQLParam = strtoupper($request->input('urlQL', ''));
        $limitHari  = (int) $request->input('limit_hari', 31);

        // Safety: tgl_akhir tidak boleh melebihi hari ini
        if ($tglAkhir->isAfter(Carbon::today())) {
            $tglAkhir = Carbon::today();
        }

        // Safety: jangan lebih dari limit_hari
        $diffHari = $tglMulai->diffInDays($tglAkhir);
        if ($diffHari > $limitHari) {
            return response()->json([
                'metadata' => [
                    'code'    => 422,
                    'message' => "Rentang tanggal melebihi batas {$limitHari} hari. Gunakan parameter limit_hari untuk menaikkan batas (max 365).",
                ],
            ], 422);
        }

        $availableQLs = BpjsHelper::getUrlQLOptions();
        $targetQLs    = ($urlQLParam && in_array($urlQLParam, $availableQLs))
            ? [$urlQLParam]
            : $availableQLs;

        $syncIds = [];

        $current = $tglMulai->copy();
        while ($current->lte($tglAkhir)) {
            $tanggal = $current->toDateString();

            foreach ($targetQLs as $urlQL) {
                // Buat log pending
                $log = VclaimSyncLog::create([
                    'kode_ql' => $urlQL,
                    'tanggal' => $tanggal,
                    'status'  => 'pending',
                ]);

                // Dispatch job ke queue
                SyncVclaimKunjunganJob::dispatch($urlQL, $tanggal, $log->id);

                Log::info("[VclaimController] Range Job dispatched — QL={$urlQL}, Tanggal={$tanggal}, LogID={$log->id}");
                $syncIds[] = $log->id;
            }

            $current->addDay();
        }

        return response()->json([
            'metadata' => [
                'code'    => 202,
                'message' => count($syncIds) . ' job sync dijadwalkan ke background queue.',
                'tgl_mulai' => $tglMulai->toDateString(),
                'tgl_akhir' => $tglAkhir->toDateString(),
            ],
            'sync_ids' => $syncIds,
        ], 202);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  2. READ — Ambil data dari DB lokal (sudah di-sync)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Ambil data kunjungan rawat jalan dari DB lokal.
     * GET /api/vclaim/kunjungan-jalan?tanggal=2026-04-21&urlQL=QLJ
     */
    public function getKunjunganJalan(Request $request)
    {
        $tanggal    = $request->input('tanggal', Carbon::today()->toDateString());
        $urlQL      = strtoupper($request->input('urlQL', ''));
        $perPage    = (int) $request->input('per_page', 50);

        $query = VclaimKunjungan::rawatJalan()
            ->byTanggal($tanggal)
            ->orderBy('nama');

        if ($urlQL) {
            $query->byQL($urlQL);
        }

        $data = $query->paginate($perPage);

        return response()->json([
            'metadata' => [
                'code'    => 200,
                'message' => 'OK',
                'tanggal' => $tanggal,
                'urlQL'   => $urlQL ?: 'ALL',
            ],
            'response' => $data,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  3. DIRECT — Fetch langsung dari BPJS VClaim tanpa disimpan ke DB
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Fetch data kunjungan rawat jalan langsung dari BPJS VClaim (direct, tanpa DB).
     * GET /api/vclaim/kunjungan-jalan-direct?tanggal=2026-04-21&urlQL=QLJ
     */
    public function getKunjunganJalanDirect(Request $request)
    {
        $request->validate([
            'urlQL'   => 'required|string|in:' . implode(',', BpjsHelper::getUrlQLOptions()),
            'tanggal' => 'required|date_format:Y-m-d',
        ]);

        $urlQL   = strtoupper($request->input('urlQL'));
        $tanggal = $request->input('tanggal');

        $data = BpjsHelper::getVclaimDataKunjungan($urlQL, $tanggal, 2);

        if (!empty($data['sep'])) {
            return response()->json([
                'metadata' => ['code' => 200, 'message' => 'OK'],
                'response' => $data,
            ]);
        }

        return response()->json([
            'metadata' => [
                'code'    => 404,
                'message' => 'Tidak ada data kunjungan rawat jalan untuk parameter tersebut',
            ],
        ], 404);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  4. VIEW — Halaman web dashboard kunjungan rawat jalan
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Tampilkan halaman monitoring kunjungan rawat jalan.
     * GET /vclaim/kunjungan-rawat-jalan
     */
    public function pageKunjunganJalan(Request $request)
    {
        $tanggal    = $request->input('tanggal', Carbon::today()->toDateString());
        $urlQL      = strtoupper($request->input('urlQL', ''));
        $availableQLs = BpjsHelper::getUrlQLOptions();

        $query = VclaimKunjungan::rawatJalan()
            ->byTanggal($tanggal)
            ->orderBy('kode_ql')
            ->orderBy('nama');

        if ($urlQL && in_array($urlQL, $availableQLs)) {
            $query->byQL($urlQL);
        }

        $kunjungan = $query->paginate(50)->withQueryString();

        // Statistik ringkasan per cabang
        $statistik = VclaimKunjungan::rawatJalan()
            ->byTanggal($tanggal)
            ->when($urlQL && in_array($urlQL, $availableQLs), fn($q) => $q->byQL($urlQL))
            ->selectRaw('kode_ql, COUNT(*) as total, MAX(synced_at) as last_sync')
            ->groupBy('kode_ql')
            ->get()
            ->keyBy('kode_ql');

        return view('vclaim.kunjungan_rawat_jalan', compact(
            'kunjungan',
            'statistik',
            'tanggal',
            'urlQL',
            'availableQLs'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  5. VIEW — Halaman web rekap kunjungan rawat jalan
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Tampilkan halaman rekap kunjungan rawat jalan per bulan.
     * GET /vclaim/rekap-kunjungan-rawat-jalan
     */
    public function pageRekapKunjunganJalan(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));
        
        $availableQLs = BpjsHelper::getUrlQLOptions();

        $data = VclaimKunjungan::rawatJalan()
            ->whereMonth('tgl_kunjungan', $bulan)
            ->whereYear('tgl_kunjungan', $tahun)
            ->selectRaw('tgl_kunjungan, kode_ql, COUNT(*) as total')
            ->groupBy('tgl_kunjungan', 'kode_ql')
            ->get();

        $grouped = [];
        foreach ($data as $item) {
            $dateStr = $item->tgl_kunjungan->format('Y-m-d');
            $ql = strtoupper($item->kode_ql);
            $grouped[$dateStr][$ql] = $item->total;
        }

        $daysInMonth = Carbon::createFromDate($tahun, $bulan, 1)->daysInMonth;
        
        $rekap = [];
        $totals = array_fill_keys($availableQLs, 0);
        $grandTotal = 0;

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dateStr = sprintf('%04d-%02d-%02d', $tahun, $bulan, $i);
            $rekap[$dateStr] = [];
            $rowTotal = 0;

            foreach ($availableQLs as $ql) {
                $count = $grouped[$dateStr][$ql] ?? 0;
                $rekap[$dateStr][$ql] = $count;
                $rowTotal += $count;
                $totals[$ql] += $count;
            }
            $rekap[$dateStr]['total'] = $rowTotal;
            $grandTotal += $rowTotal;
        }

        // Pagination untuk $rekap
        $perPage = 10;
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $offset = ($page - 1) * $perPage;
        $pagedData = array_slice($rekap, $offset, $perPage, true);
        
        $rekapPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $pagedData,
            count($rekap),
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );

        return view('vclaim.rekap_kunjungan_rawat_jalan', compact(
            'bulan',
            'tahun',
            'availableQLs',
            'rekapPaginator',
            'totals',
            'grandTotal'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  LEGACY — Endpoint lama (tetap dipertahankan untuk kompatibilitas)
    // ─────────────────────────────────────────────────────────────────────────

    public function dataKunjungan(Request $request)
    {
        $allowedQL = implode(',', BpjsHelper::getUrlQLOptions());
        $request->validate([
            'urlQL'        => 'required|string|in:' . $allowedQL,
            'tglKunjungan' => 'required|date_format:Y-m-d',
            'jnsPelayanan' => 'required|in:1,2',
        ]);

        $urlQL        = $request->input('urlQL');
        $tglKunjungan = $request->input('tglKunjungan');
        $jnsPelayanan = $request->input('jnsPelayanan');

        $result = BpjsHelper::getVclaimDataKunjungan($urlQL, $tglKunjungan, $jnsPelayanan);

        if ($result) {
            return response()->json([
                'metadata' => ['code' => 200, 'message' => 'OK'],
                'response' => $result,
            ]);
        }

        return response()->json([
            'metadata' => [
                'code'    => 404,
                'message' => 'Data tidak ditemukan atau terjadi kesalahan dari API VClaim',
            ],
        ], 404);
    }

    public function endpointStatus(Request $request)
    {
        $urlQL = strtoupper($request->input('urlQL', ''));
        $availableQLs = \App\Helpers\BpjsHelper::getUrlQLOptions();
        return view('vclaim.endpoint_status', compact('urlQL', 'availableQLs'));
    }

    public function pingPeserta(Request $request)
    {
        $urlQL = strtoupper($request->input('urlQL', ''));
        // Menggunakan NIK dummy/default untuk sekadar tes koneksi ke BPJS
        $nik = '3301010101010001'; 
        $tanggal = date('Y-m-d'); 
        
        $result = \App\Helpers\BpjsHelper::getVclaimPesertaNik($urlQL, $nik, $tanggal);
        
        return response()->json($result);
    }

    public function pingRujukan(Request $request)
    {
        $urlQL = strtoupper($request->input('urlQL', ''));
        // Menggunakan nomor rujukan dummy/default (19 digit standar PCare/RS)
        $noRujukan = '0000000000000000000'; 
        
        $result = \App\Helpers\BpjsHelper::getVclaimRujukan($urlQL, $noRujukan);
        
        return response()->json($result);
    }

    public function pingRujukanByNoKartu(Request $request)
    {
        $urlQL = strtoupper($request->input('urlQL', ''));
        // Menggunakan NIK/No Kartu dummy/default (13 digit format kartu BPJS)
        $noKartu = '0000000000000'; 
        
        $result = \App\Helpers\BpjsHelper::getVclaimRujukanByNoKartu($urlQL, $noKartu);
        
        return response()->json($result);
    }

    public function pingRujukanListPeserta(Request $request)
    {
        $urlQL = strtoupper($request->input('urlQL', ''));
        $noKartu = '0000000000000'; 
        $result = \App\Helpers\BpjsHelper::getVclaimRujukanListPeserta($urlQL, $noKartu);
        return response()->json($result);
    }

    public function pingReferensiDiagnosa(Request $request)
    {
        $urlQL = strtoupper($request->input('urlQL', ''));
        $diagnosa = 'A00'; // Dummy ICD-10
        $result = \App\Helpers\BpjsHelper::getVclaimReferensiDiagnosa($urlQL, $diagnosa);
        return response()->json($result);
    }

    public function pingReferensiDpjp(Request $request)
    {
        $urlQL = strtoupper($request->input('urlQL', ''));
        $jnsPelayanan = '1'; // 1. Rawat Inap, 2. Rawat Jalan
        $tglPelayanan = date('Y-m-d');
        $spesialis = 'INT'; // Dummy kode spesialis Penyakit Dalam
        $result = \App\Helpers\BpjsHelper::getVclaimReferensiDpjp($urlQL, $jnsPelayanan, $tglPelayanan, $spesialis);
        return response()->json($result);
    }

    public function pingSep(Request $request)
    {
        $urlQL = strtoupper($request->input('urlQL', ''));
        $noSep = '0000000000000000000'; // Dummy No SEP (19 digit)
        $result = \App\Helpers\BpjsHelper::getVclaimSep($urlQL, $noSep);
        return response()->json($result);
    }

    public function pingSuratKontrol(Request $request)
    {
        $urlQL = strtoupper($request->input('urlQL', ''));
        $noSuratKontrol = '0000000000000000000'; // Dummy No Surat Kontrol (19 digit)
        $result = \App\Helpers\BpjsHelper::getVclaimSuratKontrol($urlQL, $noSuratKontrol);
        return response()->json($result);
    }

    public function pingRiwayatPelayanan(Request $request)
    {
        $urlQL = strtoupper($request->input('urlQL', ''));
        $noKartu = '0000000000000'; // Dummy 13 digit
        $tglAkhir = date('Y-m-d');
        $tglMulai = date('Y-m-d', strtotime('-30 days')); // Ambil riwayat 30 hari terakhir
        $result = \App\Helpers\BpjsHelper::getVclaimRiwayatPelayanan($urlQL, $noKartu, $tglMulai, $tglAkhir);
        return response()->json($result);
    }

    public function pingAntrolReferensiPoli(Request $request)
    {
        $urlQL = strtoupper($request->input('urlQL', ''));
        $result = \App\Helpers\BpjsHelper::getAntrolReferensiPoli($urlQL);
        return response()->json($result);
    }

    public function pingAntrolJadwalDokter(Request $request)
    {
        $urlQL = strtoupper($request->input('urlQL', ''));
        $kodePoli = 'ANA'; // Dari request user
        $tanggal = '2021-08-07'; // Dari request user
        $result = \App\Helpers\BpjsHelper::getAntrolJadwalDokter($urlQL, $kodePoli, $tanggal);
        return response()->json($result);
    }

    public function pingAntrolAntreanAdd(Request $request)
    {
        $urlQL = strtoupper($request->input('urlQL', ''));
        $data = [
            "kodebooking" => "16032021A001",
            "jenispasien" => "JKN",
            "nomorkartu" => "00012345678",
            "nik" => "3212345678987654",
            "nohp" => "085635228888",
            "kodepoli" => "ANA",
            "namapoli" => "Anak",
            "pasienbaru" => 0,
            "norm" => "123345",
            "tanggalperiksa" => "2021-01-28",
            "kodedokter" => 12345,
            "namadokter" => "Dr. Hendra",
            "jampraktek" => "08:00-16:00",
            "jeniskunjungan" => 1,
            "nomorreferensi" => "0001R0040116A000001",
            "nomorantrean" => "A-12",
            "angkaantrean" => 12,
            "estimasidilayani" => 1615869169000,
            "sisakuotajkn" => 5,
            "kuotajkn" => 30,
            "sisakuotanonjkn" => 5,
            "kuotanonjkn" => 30,
            "keterangan" => "Peserta harap 30 menit lebih awal guna pencatatan administrasi."
        ];
        $result = \App\Helpers\BpjsHelper::postAntrolAntreanAdd($urlQL, $data);
        return response()->json($result);
    }

    public function pingAntrolUpdateJadwal(Request $request)
    {
        $urlQL = strtoupper($request->input('urlQL', ''));
        $data = [
            "kodepoli" => "ANA",
            "kodesubspesialis" => "ANA",
            "kodedokter" => 12346,
            "jadwal" => [
                [
                    "hari" => "1",
                    "buka" => "08:00",
                    "tutup" => "10:00"
                ],
                [
                    "hari" => "2",
                    "buka" => "15:00",
                    "tutup" => "17:00"
                ]
            ]
        ];
        $result = \App\Helpers\BpjsHelper::postAntrolUpdateJadwal($urlQL, $data);
        return response()->json($result);
    }

    public function pingVclaimFingerprint(Request $request)
    {
        $urlQL = strtoupper($request->input('urlQL', ''));
        $noKartu = '3301010101010001'; // Dummy Kartu
        $tglPelayanan = date('Y-m-d');
        $result = \App\Helpers\BpjsHelper::getVclaimFingerprint($urlQL, $noKartu, $tglPelayanan);
        return response()->json($result);
    }

    public function pingAntrolBatalAntrean(Request $request)
    {
        $urlQL = strtoupper($request->input('urlQL', ''));
        $data = [
            "kodebooking" => "16032021A001",
            "keterangan" => "Terjadi perubahan jadwal dokter, silahkan daftar kembali"
        ];
        $result = \App\Helpers\BpjsHelper::postAntrolBatalAntrean($urlQL, $data);
        return response()->json($result);
    }

    public function pingAntrolGetListTask(Request $request)
    {
        $urlQL = strtoupper($request->input('urlQL', ''));
        $data = [
            "kodebooking" => "Y03-20#1617068533"
        ];
        $result = \App\Helpers\BpjsHelper::postAntrolGetListTask($urlQL, $data);
        return response()->json($result);
    }

    public function pingAntrolAntreanPerTanggal(Request $request)
    {
        $urlQL = strtoupper($request->input('urlQL', ''));
        $tanggal = date('Y-m-d');
        $result = \App\Helpers\BpjsHelper::getAntrolAntreanPerTanggal($urlQL, $tanggal);
        return response()->json($result);
    }

    public function pingAntrolAntreanPerKodeBooking(Request $request)
    {
        $urlQL = strtoupper($request->input('urlQL', ''));
        $kodeBooking = '16032021A001';
        $result = \App\Helpers\BpjsHelper::getAntrolAntreanPerKodeBooking($urlQL, $kodeBooking);
        return response()->json($result);
    }

    public function pageAntrolAntreanPerTanggal()
    {
        $urlQLOptions = BpjsHelper::getUrlQLOptions();
        return view('vclaim.antrean_per_tanggal', compact('urlQLOptions'));
    }

    public function getAntrolAntreanPerTanggalData(Request $request)
    {
        $urlQL = strtoupper($request->input('urlQL', ''));
        $tanggal = $request->input('tanggal', date('Y-m-d'));
        
        if (empty($urlQL)) {
            return response()->json([
                'metadata' => ['code' => 400, 'message' => 'Cabang (urlQL) harus dipilih.']
            ]);
        }

        $logs = AntreanPerTanggalLog::where('tanggal', $tanggal)
                    ->where('kode_ql', $urlQL)
                    ->get();
        
        // Format response so it looks exactly like BPJS raw format
        $responseArray = $logs->map(function($log) {
            $raw = $log->raw_response;
            // Sometimes json casts might return array directly
            if (is_string($raw)) {
                $raw = json_decode($raw, true);
            }
            return $raw;
        })->toArray();

        return response()->json([
            'metadata' => ['code' => 200, 'message' => 'OK (Local DB)'],
            'response' => $responseArray
        ]);
    }

    public function syncAntreanPerTanggal(Request $request)
    {
        $tanggal      = $request->input('tanggal', Carbon::today()->toDateString());
        $urlQLParam   = strtoupper($request->input('urlQL', ''));
        $availableQLs = BpjsHelper::getUrlQLOptions();

        $targetQLs = ($urlQLParam && in_array($urlQLParam, $availableQLs))
            ? [$urlQLParam]
            : $availableQLs;

        $syncIds = [];

        foreach ($targetQLs as $urlQL) {
            $log = AntreanPerTanggalSyncLog::create([
                'kode_ql' => $urlQL,
                'tanggal' => $tanggal,
                'status'  => 'pending',
            ]);

            SyncAntreanPerTanggalJob::dispatch($urlQL, $tanggal, $log->id);
            $syncIds[] = $log->id;
        }

        return response()->json([
            'status'   => 'success',
            'message'  => 'Sync job dispatched',
            'sync_ids' => $syncIds,
        ]);
    }

    public function syncAntreanStatus(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['error' => 'No IDs provided'], 400);
        }

        $logs = AntreanPerTanggalSyncLog::whereIn('id', $ids)->get();

        $allDone = true;
        $hasError = false;
        $totalSync = 0;

        foreach ($logs as $log) {
            if (!$log->isDone()) {
                $allDone = false;
            }
            if ($log->status === 'error') {
                $hasError = true;
            }
            if ($log->status === 'success') {
                $totalSync += $log->total_data;
            }
        }

        return response()->json([
            'is_done'    => $allDone,
            'has_error'  => $hasError,
            'total_sync' => $totalSync,
            'logs'       => $logs
        ]);
    }
}
