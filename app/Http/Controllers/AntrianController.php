<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\data_taskid;
use Illuminate\Http\Request;
use App\Models\DataKodebooking;
use App\Helpers\BpjsHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Schema\Blueprint;

class AntrianController extends Controller
{
    public function index()
    {
        // Cache seluruh data dashboard selama 5 menit (300 detik) untuk mengatasi loading lama
        $dashboardData = Cache::remember('dashboard_data_v1', 300, function () {
        $recentStart = Carbon::now()->subDays(90)->toDateString();

        // Group data for QLJ (bounded to recent 90 days for graph)
        $data_kodebooking = DataKodebooking::select('tanggalperiksa', 'message', DB::raw('count(*) as total'))
            ->where('tanggalperiksa', '>=', $recentStart)
            ->groupBy('tanggalperiksa', 'message')
            ->get();

        $TaskID = data_taskid::select('tanggal', 'message', DB::raw('count(*) as total'))
            ->where('tanggal', '>=', $recentStart)
            ->groupBy('tanggal', 'message')
            ->get();

        // Summary data (single pass per table instead of 2 queries per table)
        $qljKbStat = DB::table('data_kodebooking')
            ->selectRaw("COUNT(*) as total, SUM(CASE WHEN message IS NULL OR message != 'ok' THEN 1 ELSE 0 END) as rekap")
            ->first();
        $totalKodebooking = (int) ($qljKbStat->total ?? 0);
        $rekapKodebooking = (int) ($qljKbStat->rekap ?? 0);

        $qljTaskStat = DB::table('data_taskids')
            ->selectRaw("COUNT(*) as total, SUM(CASE WHEN message IS NULL OR message != 'success' THEN 1 ELSE 0 END) as rekap")
            ->first();
        $totalTaskId = (int) ($qljTaskStat->total ?? 0);
        $rekapTaskId = (int) ($qljTaskStat->rekap ?? 0);

        // QLKP data (single pass per table)
        $qlkpKbStat = DB::table('qlkp_data_kodebooking')
            ->selectRaw("COUNT(*) as total, SUM(CASE WHEN message IS NULL OR message != 'ok' THEN 1 ELSE 0 END) as rekap")
            ->first();
        $qlkptotalKodebooking = (int) ($qlkpKbStat->total ?? 0);
        $qlkprekapKodebooking = (int) ($qlkpKbStat->rekap ?? 0);

        $qlkpTaskStat = DB::table('qlkp_data_taskids')
            ->selectRaw("COUNT(*) as total, SUM(CASE WHEN message IS NULL OR message != 'success' THEN 1 ELSE 0 END) as rekap")
            ->first();
        $qlkptotalTaskId = (int) ($qlkpTaskStat->total ?? 0);
        $qlkprekapTaskId = (int) ($qlkpTaskStat->rekap ?? 0);

        // QLTMG data
        $qltmgtotalKodebooking = 0;
        $qltmgrekapKodebooking = 0;
        if (Schema::hasTable('qltmg_data_kodebooking')) {
            $qltmgKbStat = DB::table('qltmg_data_kodebooking')
                ->selectRaw("COUNT(*) as total, SUM(CASE WHEN message IS NULL OR message != 'ok' THEN 1 ELSE 0 END) as rekap")
                ->first();
            $qltmgtotalKodebooking = (int) ($qltmgKbStat->total ?? 0);
            $qltmgrekapKodebooking = (int) ($qltmgKbStat->rekap ?? 0);
        }

        $qltmgtotalTaskId = 0;
        $qltmgrekapTaskId = 0;
        if (Schema::hasTable('qltmg_data_taskids')) {
            $qltmgTaskStat = DB::table('qltmg_data_taskids')
                ->selectRaw("COUNT(*) as total, SUM(CASE WHEN message IS NULL OR message != 'success' THEN 1 ELSE 0 END) as rekap")
                ->first();
            $qltmgtotalTaskId = (int) ($qltmgTaskStat->total ?? 0);
            $qltmgrekapTaskId = (int) ($qltmgTaskStat->rekap ?? 0);
        }

        $qlkp_data_kodebooking = DB::table('qlkp_data_kodebooking')->select('message', DB::raw('COUNT(*) as total'))
            ->where('tanggalperiksa', '>=', $recentStart)
            ->groupBy('message')
            ->get();

        $qlkp_TaskID = DB::table('qlkp_data_taskids')->select('message', DB::raw('COUNT(*) as total'))
            ->where('tanggal', '>=', $recentStart)
            ->groupBy('message')
            ->get();

        // Prepare data for graph
        $graphData = [];
        foreach ($data_kodebooking as $item) {
            $graphData[] = [
                'Date' => $item->tanggalperiksa,
                'TaskID' => $item->message,
                'Kodebooking' => $item->total,
                'Status' => $item->message === 'ok' ? 'Berhasil' : 'Gagal',
            ];
        }

        foreach ($TaskID as $item) {
            $graphData[] = [
                'Date' => $item->tanggal,
                'TaskID' => $item->message,
                'Kodebooking' => $item->total,
                'Status' => $item->message === 'success' ? 'Berhasil' : 'Gagal',
            ];
        }

            return [
                'data_kodebooking' => $data_kodebooking,
                'dataTaskId' => $TaskID,
                'totalKodebooking' => $totalKodebooking,
                'rekapKodebooking' => $rekapKodebooking,
                'totalTaskId' => $totalTaskId,
                'rekapTaskId' => $rekapTaskId,
                'qlkptotalKodebooking' => $qlkptotalKodebooking,
                'qlkprekapKodebooking' => $qlkprekapKodebooking,
                'qlkptotalTaskId' => $qlkptotalTaskId,
                'qlkprekapTaskId' => $qlkprekapTaskId,
                'qlkp_data_kodebooking' => $qlkp_data_kodebooking,
                'qlkp_TaskID' => $qlkp_TaskID,
                'graphData' => json_encode($graphData),
            ];
        });

        return view('dashboard.index', $dashboardData);
    }

    // public function showKodeBooking($kodebooking)
    // {
    //     // Ambil data berdasarkan kodebooking
    //     $data_kodebooking = DataKodebooking::where('kodebooking', $kodebooking)->get();

    //     // Jika data tidak ditemukan, alihkan kembali ke halaman rekap
    //     if ($data_kodebooking->isEmpty()) {
    //         return redirect()->route('rekap_kodebooking')->with('error', 'Data tidak ditemukan untuk kodebooking: ' . $kodebooking);
    //     }

    //     // Tampilkan data ke view 'data_kodebooking'
    //     return view('data_kodebooking', compact('data_kodebooking'));
    // }

    public function monitoringTaskid(Request $request)
    {
        $today = date('Y-m-d');
        $start_date = $request->input('start_date', $today);
        $end_date = $request->input('end_date', $today);

        // Ambil data kode booking berdasarkan rentang tanggal — hanya kolom yang diperlukan
        $kodebookings = DataKodebooking::select(
            'id', 'kodebooking', 'norm', 'nojkn', 'namadokter', 'namaunit', 'tanggalperiksa'
        )
            ->whereBetween('tanggalperiksa', [$start_date, $end_date])
            ->get();

        // Ambil semua kodebooking sebagai array
        $kbs = $kodebookings->pluck('kodebooking')->toArray();
        $nojkns = $kodebookings->pluck('nojkn')->filter()->unique()->values()->toArray();
        
        // Ambil data_taskid untuk kodebooking terkait — hanya kolom yang diperlukan (hindari request/response TEXT besar)
        $taskids = !empty($kbs) ? data_taskid::select(
            'id', 'kodebooking', 'taskid', 'waktu', 'jam', 'code', 'message', 'tanggal', 'reupload'
        )
            ->whereIn('kodebooking', $kbs)
            ->get() : collect();

        // Ambil data SEP dari VclaimKunjungan
        $vclaimMap = [];
        if (count($nojkns) > 0) {
            $vclaims = \App\Models\VclaimKunjungan::select('no_kartu', 'tgl_kunjungan', 'no_sep')
                ->whereIn('no_kartu', $nojkns)
                ->whereBetween('tgl_kunjungan', [$start_date, $end_date])
                ->get();

            foreach ($vclaims as $v) {
                // Tgl Kunjungan cast to date, so format it back
                $tgl = $v->tgl_kunjungan ? $v->tgl_kunjungan->format('Y-m-d') : '';
                $vclaimMap[$v->no_kartu][$tgl] = $v->no_sep;
            }
        }

        // Grouping berdasarkan kodebooking
        $groupedTasks = [];
        foreach ($taskids as $t) {
            $groupedTasks[$t->kodebooking][$t->taskid] = $t;
        }

        return view('monitoring_taskid', compact('kodebookings', 'groupedTasks', 'start_date', 'end_date', 'vclaimMap'));
    }

    public function getMonitoringTaskidDetail($kodebooking)
    {
        $taskids = data_taskid::where('kodebooking', $kodebooking)
            ->orderBy('taskid', 'asc')
            ->get();

        return response()->json([
            'metadata' => [
                'code' => 200,
                'message' => 'Sukses'
            ],
            'response' => $taskids
        ]);
    }

    public function data_kodebooking_jogja(Request $request)
    {
    // Mendapatkan tanggal hari ini
    $today = date('Y-m-d');

    // Mendapatkan parameter tanggal dari request atau gunakan tanggal hari ini sebagai default
    $start_date = $request->input('start_date', $today);
    $end_date = $request->input('end_date', $today);
    $codeFilter = $request->input('code');

    // Query untuk mengambil data kode booking berdasarkan rentang tanggal
    $query = DataKodebooking::whereBetween('tanggalperiksa', [$start_date, $end_date]);

    if ($codeFilter !== null && $codeFilter !== '') {
        if ($codeFilter === 'error') {
            $query->whereNotIn('code', [200, 0])
                  ->whereNotNull('code'); // ensure it doesn't count nulls as errors if any
        } else {
            $query->where('code', $codeFilter);
        }
    }

    $data_kodebooking = $query->get();

    // Mengirim data ke view
    return view('data_kodebooking', compact('data_kodebooking', 'start_date', 'end_date'));
    }

public function rekapKodeBooking(Request $request)
{
    // Ambil nilai tanggal, start_date, end_date, dan message dari parameter
    $tanggal = $request->input('filter_date');
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');
    $messageFilter = $request->input('message');

    // Validasi format tanggal jika diberikan
    if ($tanggal && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
        return redirect()->back()->with('error', 'Format tanggal tidak valid.');
    }
    if ($startDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
        return redirect()->back()->with('error', 'Format start date tidak valid.');
    }
    if ($endDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
        return redirect()->back()->with('error', 'Format end date tidak valid.');
    }

    // Jika start_date atau end_date tidak diberikan, default ke hari ini
    if (!$startDate) {
        $startDate = Carbon::now()->toDateString();
    }
    if (!$endDate) {
        $endDate = Carbon::now()->toDateString();
    }

    // Jika ada filter message, ambil detail data sesuai message dan rentang tanggal
    if ($messageFilter) {
        $detailData = DB::table('data_kodebooking')
            ->where(DB::raw("CASE
                WHEN message LIKE '%sudah terbit SEP%' THEN 'sudah terbit SEP'
                WHEN message LIKE '%Rujukan untuk tanggal%' AND message LIKE '%tidak valid / masa berlaku habis%' THEN 'masa berlaku habis'
                ELSE message
            END"), '=', $messageFilter)
            ->whereBetween('tanggalperiksa', [$startDate, $endDate]) // Filter rentang tanggal
            ->get();

        return view('rekap_kodebooking', compact('detailData', 'messageFilter', 'startDate', 'endDate'));
    }

    // Query untuk rekap data berdasarkan rentang tanggal
    $data = DB::table('data_kodebooking')
        ->select(
            DB::raw("CASE
                WHEN message LIKE '%sudah terbit SEP%' THEN 'sudah terbit SEP'
                WHEN message LIKE '%Rujukan untuk tanggal%' AND message LIKE '%tidak valid / masa berlaku habis%' THEN 'masa berlaku habis'
                ELSE message
            END AS message_all"),
            DB::raw('COUNT(*) as total')
        )
        ->whereBetween('tanggalperiksa', [$startDate, $endDate]) // Filter rentang tanggal
        ->groupBy('message_all')
        ->orderByDesc('total')
        ->get();

    // Kirim data rekap ke view
    return view('rekap_kodebooking', compact('data', 'startDate', 'endDate'));
}

    // Method untuk menampilkan detail data kodebooking berdasarkan kodebooking
    public function showKodeBooking($kodebooking)
    {
        // Ambil data berdasarkan kodebooking (gunakan first() jika hanya ingin satu data)
        $data_kodebooking = DataKodebooking::where('kodebooking', $kodebooking)->get();

        // Jika tidak ada data untuk kodebooking yang diberikan, tampilkan pesan error
        if ($data_kodebooking->isEmpty()) {
            return redirect()->route('rekap_kodebooking')->with('error', 'Data tidak ditemukan untuk kodebooking: ' . $kodebooking);
        }

        // Tampilkan data ke view data_kodebooking
        return view('data_kodebooking', compact('data_kodebooking'));
    }

    // Method untuk TaskID tetap ada
    public function TaskID(Request $request)
{
    $messageFilter = $request->input('message');
    $startDate = $request->input('start_date', date('Y-m-d')); // Default to today
    $endDate = $request->input('end_date', date('Y-m-d')); // Default to today

    $TaskID = DB::table('data_taskids as dt')
        ->leftJoin('data_kodebooking as dk', 'dt.kodebooking', '=', 'dk.kodebooking')
        ->select(
            'dt.id', 'dt.kodebooking', 'dt.taskid', 'dt.waktu', 'dt.jam', 'dt.code', 'dt.message', 'dt.tanggal', 'dt.reupload',
            'dk.namadokter', 'dk.namaunit as namapoli', 'dk.jammulai', 'dk.jamakhir'
        );

    if ($messageFilter) {
        $TaskID->where('dt.message', 'like', '%' . $messageFilter . '%');
    }

    if ($startDate && $endDate) {
        $TaskID->whereBetween('dt.tanggal', [$startDate, $endDate]);
    } elseif ($startDate) {
        $TaskID->where('dt.tanggal', '>=', $startDate);
    } elseif ($endDate) {
        $TaskID->where('dt.tanggal', '<=', $endDate);
    } else {
        // Default to show data for today if no date is selected
        $TaskID->where('dt.tanggal', '=', date('Y-m-d'));
    }

    $TaskID->orderBy('dt.kodebooking', 'asc')->orderBy('dt.taskid', 'asc');
    $TaskID = $TaskID->get();

    return view('TaskID', compact('TaskID', 'startDate', 'endDate'));
}

public function rekapTaskId(Request $request)
{
    // Ambil nilai start_date dan end_date dari request
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');

    // Validasi format tanggal jika diberikan
    if ($startDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
        return redirect()->back()->with('error', 'Format start date tidak valid.');
    }
    if ($endDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
        return redirect()->back()->with('error', 'Format end date tidak valid.');
    }

    // Jika start_date atau end_date tidak diberikan, default ke hari ini
    if (!$startDate) {
        $startDate = Carbon::now()->toDateString();
    }
    if (!$endDate) {
        $endDate = Carbon::now()->toDateString();
    }

    // Query untuk rekap data berdasarkan rentang tanggal
    $TaskID = data_taskid::select(DB::raw('tanggal, message, COUNT(*) as total'))
        ->whereBetween('tanggal', [$startDate, $endDate]) // Filter rentang tanggal
        ->groupBy('tanggal', 'message')
        ->get();

    return view('rekap_taskid', compact('TaskID', 'startDate', 'endDate'));
}

public function qlkp_datakodebooking(Request $request)
{
    // Gunakan Carbon untuk parsing tanggal dengan format yang sesuai
    $startDate = Carbon::parse($request->input('start_date', date('Y-m-d')))->format('Y-m-d');
    $endDate = Carbon::parse($request->input('end_date', date('Y-m-d')))->format('Y-m-d');
    $messageFilter = $request->input('message');
    $codeFilter = $request->input('code');

    // Ambil data dari database dengan filter tanggal yang tepat
    $query = DB::table('qlkp_data_kodebooking')
        ->when($messageFilter, function ($query, $messageFilter) {
            $query->where('message', 'like', "%{$messageFilter}%");
        })
        ->whereBetween('tanggalperiksa', [$startDate, $endDate]);

    if ($codeFilter !== null && $codeFilter !== '') {
        if ($codeFilter === 'error') {
            $query->whereNotIn('code', [200, 0])
                  ->whereNotNull('code');
        } else {
            $query->where('code', $codeFilter);
        }
    }

    $qlkp_data_kodebooking = $query->get();

    // Kembalikan data ke view
    return view('qlkp_data_kodebooking', compact('qlkp_data_kodebooking', 'startDate', 'endDate'));
}

    public function qlkp_rekap_kodebooking(Request $request)
    {
        // Ambil nilai start_date dan end_date dari request
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $messageFilter = $request->input('message');
    
        // Validasi format tanggal jika diberikan
        if ($startDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            return redirect()->back()->with('error', 'Format start date tidak valid.');
        }
        if ($endDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            return redirect()->back()->with('error', 'Format end date tidak valid.');
        }

        // Jika start_date atau end_date tidak diberikan, default ke hari ini
        if (!$startDate) {
            $startDate = Carbon::now()->toDateString();
        }
        if (!$endDate) {
            $endDate = Carbon::now()->toDateString();
        }

        // Jika ada filter message, ambil detail data sesuai message dan rentang tanggal
        if ($messageFilter) {
            $detailData = DB::table('qlkp_data_kodebooking')
                ->where(DB::raw("CASE
                    WHEN message LIKE '%sudah terbit SEP%' THEN 'sudah terbit SEP'
                    WHEN message LIKE '%Rujukan untuk tanggal%' AND message LIKE '%tidak valid / masa berlaku habis%' THEN 'masa berlaku habis'
                    ELSE message
                END"), '=', $messageFilter)
                ->whereBetween('tanggalperiksa', [$startDate, $endDate]) // Filter rentang tanggal
                ->get();

            return view('qlkp_rekap_kodebooking', compact('detailData', 'messageFilter', 'startDate', 'endDate'));
        }

        // Jika tidak ada filter message, ambil data ringkasan berdasarkan rentang tanggal
        $data = DB::table('qlkp_data_kodebooking')
            ->select(DB::raw("CASE
                WHEN message LIKE '%sudah terbit SEP%' THEN 'sudah terbit SEP'
                WHEN message LIKE '%Rujukan untuk tanggal%' AND message LIKE '%tidak valid / masa berlaku habis%' THEN 'masa berlaku habis'
                ELSE message
            END as message_all"), DB::raw('count(*) as total'))
            ->whereBetween('tanggalperiksa', [$startDate, $endDate]) // Filter rentang tanggal
            ->groupBy('message_all')
            ->get();

        return view('qlkp_rekap_kodebooking', compact('data', 'startDate', 'endDate'));
    }

    public function qlkp_TaskID(Request $request)
    {
    // Pastikan tabel qlkp_data_taskids ada
    if (!Schema::hasTable('qlkp_data_taskids')) {
        return redirect()->back()->with('error', 'The table qlkp_data_taskid does not exist.');
    }

    // Ambil data filter message dan tanggal dari query string
    $messageFilter = $request->input('message');
    $filterDate = $request->input('filter_date'); // Ambil filter_date jika ada
    $startDate = $request->input('start_date', date('Y-m-d')); // Default ke tanggal hari ini jika tidak ada
    $endDate = $request->input('end_date', date('Y-m-d')); // Default ke tanggal hari ini jika tidak ada

    // Query untuk mengambil data taskid berdasarkan filter message dan tanggal, join dengan kodebooking untuk dokter & poli & jadwal
    // Hanya ambil kolom yang dibutuhkan (hindari request/response TEXT besar)
    $qlkp_TaskID = DB::table('qlkp_data_taskids as dt')
        ->leftJoin('qlkp_data_kodebooking as dk', 'dt.kodebooking', '=', 'dk.kodebooking')
        ->select(
            'dt.id', 'dt.kodebooking', 'dt.taskid', 'dt.waktu', 'dt.jam', 'dt.code', 'dt.message', 'dt.tanggal', 'dt.reupload',
            'dk.namadokter', 'dk.namaunit as namapoli', 'dk.jammulai', 'dk.jamakhir'
        );

    // Filter berdasarkan message yang sesuai
    if ($messageFilter) {
        $qlkp_TaskID->where('dt.message', 'like', "%{$messageFilter}%");
    }

    // Filter berdasarkan filter_date yang dipilih di halaman Rekap Task ID
    if ($filterDate) {
        $qlkp_TaskID->where('dt.tanggal', '=', $filterDate); // Sesuaikan dengan kolom tanggal
    } else {
    // Jika tidak ada filter tanggal, filter berdasarkan rentang tanggal
    if ($startDate && $endDate) {
        $qlkp_TaskID->whereBetween('dt.tanggal', [$startDate, $endDate]);
    } elseif ($startDate) {
        $qlkp_TaskID->where('dt.tanggal', '>=', $startDate);
    } elseif ($endDate) {
        $qlkp_TaskID->where('dt.tanggal', '<=', $endDate);
    }
    }
    // Urutkan berdasarkan kodebooking lalu taskid
    $qlkp_TaskID->orderBy('dt.kodebooking', 'asc')->orderBy('dt.taskid', 'asc');

    // Ambil data sesuai filter
    $qlkp_TaskID = $qlkp_TaskID->get();
    // Kirimkan data ke view qlkp_TaskID
    return view('qlkp_TaskID', compact
    ('qlkp_TaskID', 'startDate', 'endDate', 'filterDate'));
    }

    private function ensureBpjsJadwalTableExists()
    {
        if (!Schema::hasTable('bpjs_jadwal_dokter')) {
            Schema::create('bpjs_jadwal_dokter', function (Blueprint $table) {
                $table->id();
                $table->string('kode_ql', 10)->index();
                $table->date('tanggal')->index();
                $table->string('kodepoli', 20)->nullable();
                $table->string('namapoli', 100)->nullable();
                $table->string('kodedokter', 50)->nullable();
                $table->string('namadokter', 150)->nullable();
                $table->string('jadwal', 50)->nullable();
                $table->string('buka', 10)->nullable();
                $table->string('tutup', 10)->nullable();
                $table->integer('kapasitaspasien')->nullable();
                $table->tinyInteger('libur')->default(0);
                $table->timestamps();
                $table->unique(['kode_ql', 'tanggal', 'kodedokter', 'kodepoli', 'jadwal'], 'bpjs_jadwal_unique');
            });
        }
    }

    public function qlkp_sync_jadwal_bpjs(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());
        $urlQL = 'QLKP';

        $this->ensureBpjsJadwalTableExists();

        $poliList = DB::table('qlkp_data_kodebooking')
            ->whereBetween('tanggalperiksa', [$startDate, $endDate])
            ->whereNotNull('kodeunit')
            ->where('kodeunit', '!=', '')
            ->distinct()
            ->pluck('kodeunit')
            ->toArray();

        if (empty($poliList)) {
            $poliList = ['BED', 'OBG', 'ANA', 'INT', 'MAT', 'JIW', 'SAR', 'GIG', 'THT', 'KUL', 'PAR'];
        }

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $totalSynced = 0;
        $upsertRows = [];
        $nowTs = now();

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $tglStr = $date->toDateString();
            foreach ($poliList as $kodepoli) {
                $schedules = BpjsHelper::getAntrolJadwalDokterData($urlQL, $kodepoli, $tglStr);
                if (!empty($schedules) && is_array($schedules)) {
                    foreach ($schedules as $sch) {
                        $jadwal = $sch['jadwal'] ?? '';
                        $buka = null;
                        $tutup = null;
                        if (!empty($jadwal) && strpos($jadwal, '-') !== false) {
                            $parts = explode('-', $jadwal);
                            $buka = trim($parts[0] ?? '');
                            $tutup = trim($parts[1] ?? '');
                        }

                        $upsertRows[] = [
                            'kode_ql'         => $urlQL,
                            'tanggal'         => $tglStr,
                            'kodedokter'      => (string)($sch['kodedokter'] ?? ''),
                            'kodepoli'        => (string)($sch['kodepoli'] ?? $kodepoli),
                            'jadwal'          => $jadwal,
                            'namadokter'      => $sch['namadokter'] ?? null,
                            'namapoli'        => $sch['namapoli'] ?? null,
                            'buka'            => $buka,
                            'tutup'           => $tutup,
                            'kapasitaspasien' => $sch['kapasitaspasien'] ?? null,
                            'libur'           => $sch['libur'] ?? 0,
                            'updated_at'      => $nowTs,
                            'created_at'      => $nowTs,
                        ];
                        $totalSynced++;
                    }
                }
            }
        }

        foreach (array_chunk($upsertRows, 500) as $chunk) {
            DB::table('bpjs_jadwal_dokter')->upsert(
                $chunk,
                ['kode_ql', 'tanggal', 'kodedokter', 'kodepoli', 'jadwal'],
                ['namadokter', 'namapoli', 'buka', 'tutup', 'kapasitaspasien', 'libur', 'updated_at']
            );
        }

        return response()->json([
            'metadata' => [
                'code' => 200,
                'message' => "Berhasil menyinkronkan {$totalSynced} data jadwal dokter dari BPJS Bridging."
            ],
            'total_synced' => $totalSynced
        ]);
    }

    public function qlkp_kesesuaian_jadwal(Request $request)
    {
        $today = Carbon::now();
        $startDate = $request->input('start_date', $today->copy()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', $today->toDateString());
        $statusFilter = $request->input('status');
        $dokterFilter = $request->input('namadokter');
        $poliFilter = $request->input('nmpoli');

        if (!Schema::hasTable('qlkp_data_kodebooking') || !Schema::hasTable('qlkp_data_taskids')) {
            return redirect()->back()->with('error', 'Tabel qlkp_data_kodebooking atau qlkp_data_taskids tidak ditemukan.');
        }

        $this->ensureBpjsJadwalTableExists();

        // Ambil jadwal resmi HFIS BPJS yang sudah disinkronkan
        $bpjsJadwalRecords = DB::table('bpjs_jadwal_dokter')
            ->where('kode_ql', 'QLKP')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();

        $bpjsJadwalMap = [];
        foreach ($bpjsJadwalRecords as $bj) {
            $keyDokter = $bj->tanggal . '|' . trim($bj->kodedokter);
            $keyNama = $bj->tanggal . '|' . strtoupper(trim($bj->namadokter));
            $bpjsJadwalMap[$keyDokter] = $bj;
            $bpjsJadwalMap[$keyNama] = $bj;
        }

        // Ambil data jampraktek dari antrean_per_tanggal_logs (data antrean BPJS)
        $antrolLogMap = [];
        if (Schema::hasTable('antrean_per_tanggal_logs')) {
            $antrolLogs = DB::table('antrean_per_tanggal_logs')
                ->where('kode_ql', 'QLKP')
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->whereNotNull('jampraktek')
                ->where('jampraktek', '!=', '')
                ->select('tanggal', 'kodedokter', 'kodepoli', 'jampraktek')
                ->distinct()
                ->get();

            foreach ($antrolLogs as $al) {
                $key = $al->tanggal . '|' . trim($al->kodedokter);
                if (!isset($antrolLogMap[$key])) {
                    $antrolLogMap[$key] = $al->jampraktek;
                }
            }
        }

        $listDokter = Cache::remember('qlkp_list_dokter_v1', 600, function () {
            return DB::table('qlkp_data_kodebooking')
                ->whereNotNull('namadokter')
                ->where('namadokter', '!=', '')
                ->distinct()
                ->orderBy('namadokter')
                ->pluck('namadokter');
        });

        $listPoli = Cache::remember('qlkp_list_poli_v1', 600, function () {
            return DB::table('qlkp_data_kodebooking')
                ->whereNotNull('namaunit')
                ->where('namaunit', '!=', '')
                ->distinct()
                ->orderBy('namaunit')
                ->pluck('namaunit');
        });

        $query = DB::table('qlkp_data_kodebooking')
            ->select(
                'tanggalperiksa',
                'bpjs_kodedokter',
                'kodeunit as kodepoli',
                'namadokter',
                'namaunit as nmpoli',
                'jammulai',
                'jamakhir'
            )
            ->whereBetween('tanggalperiksa', [$startDate, $endDate])
            ->whereNotNull('namadokter')
            ->where('namadokter', '!=', '')
            ->groupBy('tanggalperiksa', 'bpjs_kodedokter', 'kodeunit', 'namadokter', 'namaunit', 'jammulai', 'jamakhir');

        if ($dokterFilter) {
            $query->where('namadokter', $dokterFilter);
        }
        if ($poliFilter) {
            $query->where('namaunit', $poliFilter);
        }

        $schedules = $query->orderBy('tanggalperiksa', 'asc')
            ->orderBy('namadokter', 'asc')
            ->get();

        $task4Records = DB::table('qlkp_data_taskids as t')
            ->join('qlkp_data_kodebooking as kb', 't.kodebooking', '=', 'kb.kodebooking')
            ->where('t.taskid', 4)
            ->whereBetween('kb.tanggalperiksa', [$startDate, $endDate])
            ->select(
                'kb.kodebooking',
                'kb.nojkn',
                'kb.norm',
                'kb.tanggalperiksa',
                'kb.namadokter',
                'kb.namaunit',
                'kb.jammulai',
                'kb.jamakhir',
                't.tanggal',
                't.jam',
                't.waktu'
            )
            ->orderBy('t.waktu', 'asc')
            ->get();

        $firstTask4Map = [];
        foreach ($task4Records as $t4) {
            $jm = $t4->jammulai ? substr($t4->jammulai, 0, 5) : '';
            $ja = $t4->jamakhir ? substr($t4->jamakhir, 0, 5) : '';
            // Kunci spesifik per tanggal + dokter + poli + jadwal yang dipilih pasien
            $key = $t4->tanggalperiksa . '|' . trim($t4->namadokter) . '|' . trim($t4->namaunit) . '|' . $jm . '|' . $ja;
            if (!isset($firstTask4Map[$key])) {
                $firstTask4Map[$key] = $t4;
            }
        }

        $firstBookingRecords = DB::table('qlkp_data_kodebooking')
            ->whereBetween('tanggalperiksa', [$startDate, $endDate])
            ->whereNotNull('namadokter')
            ->where('namadokter', '!=', '')
            ->select(
                'kodebooking',
                'nojkn',
                'norm',
                'tanggalperiksa',
                'namadokter',
                'namaunit',
                'jammulai',
                'jamakhir'
            )
            ->orderBy('noantrian', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $firstBookingMap = [];
        foreach ($firstBookingRecords as $fb) {
            $jm = $fb->jammulai ? substr($fb->jammulai, 0, 5) : '';
            $ja = $fb->jamakhir ? substr($fb->jamakhir, 0, 5) : '';
            $key = $fb->tanggalperiksa . '|' . trim($fb->namadokter) . '|' . trim($fb->namaunit) . '|' . $jm . '|' . $ja;
            if (!isset($firstBookingMap[$key])) {
                $firstBookingMap[$key] = $fb;
            }
        }

        $allNojkn = $task4Records->pluck('nojkn')
            ->merge($firstBookingRecords->pluck('nojkn'))
            ->filter()
            ->unique()
            ->toArray();

        $vclaimNames = [];
        if (!empty($allNojkn) && Schema::hasTable('vclaim_kunjungan')) {
            $vclaimNames = DB::table('vclaim_kunjungan')
                ->whereIn('no_kartu', $allNojkn)
                ->whereNotNull('nama')
                ->where('nama', '!=', '')
                ->pluck('nama', 'no_kartu')
                ->toArray();
        }

        $daysMap = [
            'Sunday' => 'MINGGU',
            'Monday' => 'SENIN',
            'Tuesday' => 'SELASA',
            'Wednesday' => 'RABU',
            'Thursday' => 'KAMIS',
            'Friday' => 'JUMAT',
            'Saturday' => 'SABTU',
        ];

        $results = [];
        $totalSesuai = 0;
        $totalTidakSesuai = 0;

        foreach ($schedules as $sch) {
            $schJm = $sch->jammulai ? substr($sch->jammulai, 0, 5) : '';
            $schJa = $sch->jamakhir ? substr($sch->jamakhir, 0, 5) : '';
            $key = $sch->tanggalperiksa . '|' . trim($sch->namadokter) . '|' . trim($sch->nmpoli) . '|' . $schJm . '|' . $schJa;
            $t4 = $firstTask4Map[$key] ?? null;

            // Fallback: jika tidak ada match slot jam persis, cari yang tanggal + dokter + poli sama
            if (!$t4) {
                $fallbackKey = $sch->tanggalperiksa . '|' . trim($sch->namadokter) . '|' . trim($sch->nmpoli);
                foreach ($firstTask4Map as $k => $val) {
                    if (str_starts_with($k, $fallbackKey)) {
                        $t4 = $val;
                        break;
                    }
                }
            }

            // Cari data pasien (utamakan dari task 4 pertama, jika tidak ada fallback ke booking pertama)
            $kodebooking = '-';
            $noBpjs = '-';
            $namaPasien = '-';

            $targetPatient = $t4 ?? ($firstBookingMap[$key] ?? null);
            if ($targetPatient) {
                $kodebooking = $targetPatient->kodebooking ?? '-';
                $noBpjs = $targetPatient->nojkn ?? '-';
                $namaPasien = $vclaimNames[$targetPatient->nojkn] ?? ($targetPatient->norm ? 'No RM: ' . $targetPatient->norm : '-');
            }

            $dt = Carbon::parse($sch->tanggalperiksa);
            $bulanUpd = (int)$dt->format('m');
            $namaHari = $daysMap[$dt->englishDayOfWeek] ?? strtoupper($dt->isoFormat('dddd'));
            $monthPeriod = $dt->format('M-y');
            $dayMonthYear = $dt->format('d-M-y');

            // Menentukan jam BUKA dan TUTUP sesuai Bridging Jadwal BPJS
            $buka = null;
            $tutup = null;
            $sumberJadwal = 'BPJS HFIS';

            $keyDokter = $sch->tanggalperiksa . '|' . trim($sch->bpjs_kodedokter ?? '');
            $keyNama = $sch->tanggalperiksa . '|' . strtoupper(trim($sch->namadokter));
            $bj = $bpjsJadwalMap[$keyDokter] ?? ($bpjsJadwalMap[$keyNama] ?? null);

            if ($bj && !empty($bj->buka) && !empty($bj->tutup)) {
                $buka = $bj->buka;
                $tutup = $bj->tutup;
                $sumberJadwal = 'BPJS HFIS';
            } elseif (isset($antrolLogMap[$keyDokter])) {
                $jp = $antrolLogMap[$keyDokter];
                if (strpos($jp, '-') !== false) {
                    $parts = explode('-', $jp);
                    $buka = trim($parts[0] ?? '');
                    $tutup = trim($parts[1] ?? '');
                    $sumberJadwal = 'Antrol BPJS';
                }
            }

            // Fallback jika belum tersinkronisasi dari BPJS
            if (!$buka || !$tutup) {
                $buka = $sch->jammulai ? substr($sch->jammulai, 0, 5) : '-';
                $tutup = $sch->jamakhir ? substr($sch->jamakhir, 0, 5) : '-';
                $sumberJadwal = 'Bridging RS';
            }

            $wakturs = 'Tidak Ada';
            $kesesuaian = 'TIDAK SESUAI';
            $keterangan = 'Tidak Ada Pelayanan Task 4';

            if ($t4 && $t4->jam) {
                $jamT4 = substr($t4->jam, 0, 5);
                $waktursDate = Carbon::parse($t4->tanggal ?? $sch->tanggalperiksa);
                $wakturs = $waktursDate->format('n/j/y') . ' ' . ltrim($jamT4, '0');
                if (empty(ltrim($jamT4, '0'))) {
                    $wakturs = $waktursDate->format('n/j/y') . ' 0:00';
                }

                if ($buka !== '-' && $tutup !== '-') {
                    try {
                        $bukaTime = Carbon::createFromTimeString($buka);
                        $bukaToleransi = (clone $bukaTime)->subMinutes(60);
                        $tutupTime = Carbon::createFromTimeString($tutup);
                        $pelayananTime = Carbon::createFromTimeString($t4->jam);

                        if ($pelayananTime->betweenIncluded($bukaToleransi, $tutupTime)) {
                            $kesesuaian = 'SESUAI';
                            $keterangan = 'Sesuai Jadwal BPJS';
                        } elseif ($pelayananTime->lt($bukaToleransi)) {
                            $kesesuaian = 'TIDAK SESUAI';
                            $keterangan = 'Mulai >60 menit sebelum buka BPJS (' . $jamT4 . ' vs ' . $buka . ')';
                        } else {
                            $kesesuaian = 'TIDAK SESUAI';
                            $keterangan = 'Mulai lewat jam tutup BPJS (' . $jamT4 . ' vs ' . $tutup . ')';
                        }
                    } catch (\Exception $e) {
                        $kesesuaian = 'SESUAI';
                        $keterangan = 'Format jam tidak standar';
                    }
                }
            }

            if ($kesesuaian === 'SESUAI') {
                $totalSesuai++;
            } else {
                $totalTidakSesuai++;
            }

            if ($statusFilter && $kesesuaian !== $statusFilter) {
                continue;
            }

            $results[] = (object)[
                'bulan_upd' => $bulanUpd,
                'nmkc' => 'SLEMAN',
                'nmppk' => 'RSU QUEEN LATIFA KULON PROGO',
                'namadokter' => $sch->namadokter,
                'nmpoli' => $sch->nmpoli,
                'month_periode' => $monthPeriod,
                'day_month_year' => $dayMonthYear,
                'tanggalperiksa' => $sch->tanggalperiksa,
                'nama_hari' => $namaHari,
                'buka' => $buka,
                'tutup' => $tutup,
                'sumber_jadwal' => $sumberJadwal,
                'kodebooking' => $kodebooking,
                'no_bpjs' => $noBpjs,
                'nama_pasien' => $namaPasien,
                'wakturs' => $wakturs,
                'kesesuaian' => $kesesuaian,
                'keterangan' => $keterangan,
            ];
        }

        $totalJadwal = count($schedules);
        $persenSesuai = $totalJadwal > 0 ? round(($totalSesuai / $totalJadwal) * 100, 1) : 0;

        return view('qlkp_kesesuaian_jadwal', compact(
            'results',
            'startDate',
            'endDate',
            'statusFilter',
            'dokterFilter',
            'poliFilter',
            'listDokter',
            'listPoli',
            'totalJadwal',
            'totalSesuai',
            'totalTidakSesuai',
            'persenSesuai'
        ));
    }

    public function qlkp_rekap_taskid(Request $request)
{
    // Ambil nilai start_date dan end_date dari request
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');

    // Validasi format tanggal jika diberikan
    if ($startDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
        return redirect()->back()->with('error', 'Format start date tidak valid.');
    }
    if ($endDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
        return redirect()->back()->with('error', 'Format end date tidak valid.');
    }

    // Jika start_date atau end_date tidak diberikan, default ke hari ini
    if (!$startDate) {
        $startDate = Carbon::now()->toDateString();
    }
    if (!$endDate) {
        $endDate = Carbon::now()->toDateString();
    }

    // Query untuk rekap data berdasarkan rentang tanggal (menggunakan tabel qlkp_data_taskids)
    $qlkp_TaskID = DB::table('qlkp_data_taskids')
        ->select(DB::raw('tanggal, message, COUNT(*) as total'))
        ->whereBetween('tanggal', [$startDate, $endDate]) // Filter rentang tanggal
        ->groupBy('tanggal', 'message')
        ->get();

    // Mengirimkan data ke view
    return view('qlkp_rekap_taskid', compact('qlkp_TaskID', 'startDate', 'endDate'));
}

    public function showJson(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-d'));
        $endDate = $request->input('end_date', $startDate);
        $data_kodebooking = DataKodebooking::whereBetween('tanggalperiksa', [$startDate, $endDate])->get();
        return response()->json($data_kodebooking);
    }

    public function qlkp_showJson(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-d'));
        $endDate = $request->input('end_date', $startDate);
        $qlkp_data_kodebooking = DB::table('qlkp_data_kodebooking')
            ->whereBetween('tanggalperiksa', [$startDate, $endDate])
            ->get();
        return response()->json($qlkp_data_kodebooking);
    }

    // ==================== TEMANGGUNG (QLTMG) ====================

    public function qltmg_datakodebooking(Request $request)
    {
        $startDate = Carbon::parse($request->input('start_date', date('Y-m-d')))->format('Y-m-d');
        $endDate = Carbon::parse($request->input('end_date', date('Y-m-d')))->format('Y-m-d');
        $messageFilter = $request->input('message');
        $codeFilter = $request->input('code');

        $query = DB::table('qltmg_data_kodebooking')
            ->when($messageFilter, function ($query, $messageFilter) {
                $query->where('message', 'like', "%{$messageFilter}%");
            })
            ->whereBetween('tanggalperiksa', [$startDate, $endDate]);

        if ($codeFilter !== null && $codeFilter !== '') {
            if ($codeFilter === 'error') {
                $query->whereNotIn('code', [200, 0])
                      ->whereNotNull('code');
            } else {
                $query->where('code', $codeFilter);
            }
        }

        $qltmg_data_kodebooking = $query->get();

        return view('qltmg_data_kodebooking', compact('qltmg_data_kodebooking', 'startDate', 'endDate'));
    }

    public function qltmg_rekap_kodebooking(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $messageFilter = $request->input('message');

        if ($startDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            return redirect()->back()->with('error', 'Format start date tidak valid.');
        }
        if ($endDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            return redirect()->back()->with('error', 'Format end date tidak valid.');
        }

        if (!$startDate) {
            $startDate = Carbon::now()->toDateString();
        }
        if (!$endDate) {
            $endDate = Carbon::now()->toDateString();
        }

        if ($messageFilter) {
            $detailData = DB::table('qltmg_data_kodebooking')
                ->where(DB::raw("CASE
                    WHEN message LIKE '%sudah terbit SEP%' THEN 'sudah terbit SEP'
                    WHEN message LIKE '%Rujukan untuk tanggal%' AND message LIKE '%tidak valid / masa berlaku habis%' THEN 'masa berlaku habis'
                    ELSE message
                END"), '=', $messageFilter)
                ->whereBetween('tanggalperiksa', [$startDate, $endDate])
                ->get();

            return view('qltmg_rekap_kodebooking', compact('detailData', 'messageFilter', 'startDate', 'endDate'));
        }

        $data = DB::table('qltmg_data_kodebooking')
            ->select(DB::raw("CASE
                WHEN message LIKE '%sudah terbit SEP%' THEN 'sudah terbit SEP'
                WHEN message LIKE '%Rujukan untuk tanggal%' AND message LIKE '%tidak valid / masa berlaku habis%' THEN 'masa berlaku habis'
                ELSE message
            END as message_all"), DB::raw('count(*) as total'))
            ->whereBetween('tanggalperiksa', [$startDate, $endDate])
            ->groupBy('message_all')
            ->get();

        return view('qltmg_rekap_kodebooking', compact('data', 'startDate', 'endDate'));
    }

    public function qltmg_TaskID(Request $request)
    {
        if (!Schema::hasTable('qltmg_data_taskids')) {
            return redirect()->back()->with('error', 'The table qltmg_data_taskids does not exist.');
        }

        $messageFilter = $request->input('message');
        $filterDate = $request->input('filter_date');
        $startDate = $request->input('start_date', date('Y-m-d'));
        $endDate = $request->input('end_date', date('Y-m-d'));

        $qltmg_TaskID = DB::table('qltmg_data_taskids as dt')
            ->leftJoin('qltmg_data_kodebooking as dk', 'dt.kodebooking', '=', 'dk.kodebooking')
            ->select(
                'dt.id', 'dt.kodebooking', 'dt.taskid', 'dt.waktu', 'dt.jam', 'dt.code', 'dt.message', 'dt.tanggal', 'dt.reupload',
                'dk.namadokter', 'dk.namaunit as namapoli', 'dk.jammulai', 'dk.jamakhir'
            );

        if ($messageFilter) {
            $qltmg_TaskID->where('dt.message', 'like', "%{$messageFilter}%");
        }

        if ($filterDate) {
            $qltmg_TaskID->where('dt.tanggal', '=', $filterDate);
        } else {
            if ($startDate && $endDate) {
                $qltmg_TaskID->whereBetween('dt.tanggal', [$startDate, $endDate]);
            } elseif ($startDate) {
                $qltmg_TaskID->where('dt.tanggal', '>=', $startDate);
            } elseif ($endDate) {
                $qltmg_TaskID->where('dt.tanggal', '<=', $endDate);
            }
        }

        $qltmg_TaskID->orderBy('dt.kodebooking', 'asc')->orderBy('dt.taskid', 'asc');
        $qltmg_TaskID = $qltmg_TaskID->get();
        return view('qltmg_TaskID', compact('qltmg_TaskID', 'startDate', 'endDate', 'filterDate'));
    }

    public function qltmg_rekap_taskid(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            return redirect()->back()->with('error', 'Format start date tidak valid.');
        }
        if ($endDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            return redirect()->back()->with('error', 'Format end date tidak valid.');
        }

        if (!$startDate) {
            $startDate = Carbon::now()->toDateString();
        }
        if (!$endDate) {
            $endDate = Carbon::now()->toDateString();
        }

        $qltmg_TaskID = DB::table('qltmg_data_taskids')
            ->select(DB::raw('message, COUNT(*) as total'))
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->groupBy('message')
            ->get();

        return view('qltmg_rekap_taskid', compact('qltmg_TaskID', 'startDate', 'endDate'));
    }

    public function getPatientData(Request $request)
    {
        $date = $request->query('date'); // Ambil tanggal dari query parameter

        // Cek apakah parameter tanggal ada
        if (!$date) {
            return response()->json([
                'error' => 'Tanggal tidak ditemukan'
            ]);
        }

        // Ambil data berdasarkan tanggal untuk Kodebooking
        $data_kodebooking = DataKodebooking::select(DB::raw('message, count(*) as total'))
            ->where('tanggalperiksa', $date)
            ->groupBy('message')
            ->get();

        // Ambil data berdasarkan tanggal untuk TaskID
        $task_id_data = data_taskid::select(DB::raw('message, count(*) as total'))
            ->where('tanggal', $date)
            ->groupBy('message')
            ->get();

        // Siapkan data untuk grafik
        $labels = ['Kodebooking', 'TaskID'];
        $kodebooking_values = $data_kodebooking->pluck('total')->toArray();
        $taskid_values = $task_id_data->pluck('total')->toArray();

        // Gabungkan data Kodebooking dan TaskID
        $values = array_merge($kodebooking_values, $taskid_values);

        // Kembalikan data dalam format JSON untuk digunakan di grafik
        return response()->json([
            'labels' => $labels,
            'values' => $values,
        ]);
    }

    public function getPatientDataKulonProgo(Request $request)
    {
        $date = $request->query('date');

        if (!$date) {
            return response()->json(['error' => 'Tanggal tidak ditemukan']);
        }

        $data_kodebooking = DB::table('qlkp_data_kodebooking')
            ->select(DB::raw('message, count(*) as total'))
            ->where('tanggalperiksa', $date)
            ->groupBy('message')
            ->get();

        $task_id_data = DB::table('qlkp_data_taskids')
            ->select(DB::raw('message, count(*) as total'))
            ->where('tanggal', $date)
            ->groupBy('message')
            ->get();

        $labels = ['Kodebooking', 'TaskID'];
        $kodebooking_values = $data_kodebooking->pluck('total')->toArray();
        $taskid_values = $task_id_data->pluck('total')->toArray();
        $values = array_merge($kodebooking_values, $taskid_values);

        return response()->json([
            'labels' => $labels,
            'values' => $values,
        ]);
    }

    public function getPatientDataTemanggung(Request $request)
    {
        $date = $request->query('date');

        if (!$date) {
            return response()->json(['error' => 'Tanggal tidak ditemukan']);
        }

        $data_kodebooking = DB::table('qltmg_data_kodebooking')
            ->select(DB::raw('message, count(*) as total'))
            ->where('tanggalperiksa', $date)
            ->groupBy('message')
            ->get();

        $task_id_data = DB::table('qltmg_data_taskids')
            ->select(DB::raw('message, count(*) as total'))
            ->where('tanggal', $date)
            ->groupBy('message')
            ->get();

        $labels = ['Kodebooking', 'TaskID'];
        $kodebooking_values = $data_kodebooking->pluck('total')->toArray();
        $taskid_values = $task_id_data->pluck('total')->toArray();
        $values = array_merge($kodebooking_values, $taskid_values);

        return response()->json([
            'labels' => $labels,
            'values' => $values,
        ]);
    }

}
