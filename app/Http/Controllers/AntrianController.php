<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\data_taskid;
use Illuminate\Http\Request;
use App\Models\DataKodebooking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AntrianController extends Controller
{
    public function index()
    {
        // Group data for QLJ
        $data_kodebooking = DataKodebooking::select('tanggalperiksa', 'message', DB::raw('count(*) as total'))
            ->groupBy('tanggalperiksa', 'message')
            ->get();

        $TaskID = data_taskid::select('tanggal', 'message', DB::raw('count(*) as total'))
            ->groupBy('tanggal', 'message')
            ->get();

        // Summary data
        $totalKodebooking = DataKodebooking::count();
        $rekapKodebooking = DataKodebooking::where('message', '!=', 'ok')->count();
        $totalTaskId = data_taskid::count();
        $rekapTaskId = data_taskid::where('message', '!=', 'success')->count();

        // QLKP data
        $qlkptotalKodebooking = DB::table('qlkp_data_kodebooking')->count();
        $qlkprekapKodebooking = DB::table('qlkp_data_kodebooking')->where('message', '!=', 'ok')->count();
        $qlkptotalTaskId = DB::table('qlkp_data_taskids')->count();
        $qlkprekapTaskId = DB::table('qlkp_data_taskids')->where('message', '!=', 'success')->count();

        // QLTMG data
        $qltmgtotalKodebooking = Schema::hasTable('qltmg_data_kodebooking') ? DB::table('qltmg_data_kodebooking')->count() : 0;
        $qltmgrekapKodebooking = Schema::hasTable('qltmg_data_kodebooking') ? DB::table('qltmg_data_kodebooking')->where('message', '!=', 'ok')->count() : 0;
        $qltmgtotalTaskId = Schema::hasTable('qltmg_data_taskids') ? DB::table('qltmg_data_taskids')->count() : 0;
        $qltmgrekapTaskId = Schema::hasTable('qltmg_data_taskids') ? DB::table('qltmg_data_taskids')->where('message', '!=', 'success')->count() : 0;

        $qlkp_data_kodebooking = DB::table('qlkp_data_kodebooking')->select('message', DB::raw('COUNT(*) as total'))
            ->groupBy('message')
            ->get();

        $qlkp_TaskID = DB::table('qlkp_data_taskids')->select('message', DB::raw('COUNT(*) as total'))
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

        return view('dashboard.index', [
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
        ]);
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

    public function data_kodebooking(Request $request)
    {
    // Mendapatkan tanggal hari ini
    $today = date('Y-m-d');

    // Mendapatkan parameter tanggal dari request atau gunakan tanggal hari ini sebagai default
    $start_date = $request->input('start_date', $today);
    $end_date = $request->input('end_date', $today);

    // Query untuk mengambil data kode booking berdasarkan rentang tanggal
    $data_kodebooking = DataKodebooking::whereDate('tanggalperiksa', '>=', $start_date)
        ->whereDate('tanggalperiksa', '<=', $end_date)
        ->get();

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

    $TaskID = data_taskid::query();

    if ($messageFilter) {
        $TaskID->where('message', 'like', '%' . $messageFilter . '%');
    }

    if ($startDate && $endDate) {
        $TaskID->whereBetween('tanggal', [$startDate, $endDate]);
    } elseif ($startDate) {
        $TaskID->where('tanggal', '>=', $startDate);
    } elseif ($endDate) {
        $TaskID->where('tanggal', '<=', $endDate);
    } else {
        // Default to show data for today if no date is selected
        $TaskID->whereDate('tanggal', '=', date('Y-m-d'));
    }

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

    // Ambil data dari database dengan filter tanggal yang tepat
    $qlkp_data_kodebooking = DB::table('qlkp_data_kodebooking')
        ->when($messageFilter, function ($query, $messageFilter) {
            $query->where('message', 'like', "%{$messageFilter}%");
        })
        ->whereDate('tanggalperiksa', '>=', $startDate) // Gunakan tanggalperiksa untuk filter
        ->whereDate('tanggalperiksa', '<=', $endDate)
        ->get();

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

    // Query untuk mengambil data taskid berdasarkan filter message dan tanggal
    $qlkp_TaskID = DB::table('qlkp_data_taskids');

    // Filter berdasarkan message yang sesuai
    if ($messageFilter) {
        $qlkp_TaskID->where('message', 'like', "%{$messageFilter}%");
    }

    // Filter berdasarkan filter_date yang dipilih di halaman Rekap Task ID
    if ($filterDate) {
        $qlkp_TaskID->whereDate('tanggal', '=', $filterDate); // Sesuaikan dengan kolom tanggal
    } else {
    // Jika tidak ada filter tanggal, filter berdasarkan rentang tanggal
    if ($startDate && $endDate) {
        $qlkp_TaskID->whereBetween('tanggal', [$startDate, $endDate]);
    } elseif ($startDate) {
        $qlkp_TaskID->where('tanggal', '>=', $startDate);
    } elseif ($endDate) {
        $qlkp_TaskID->where('tanggal', '<=', $endDate);
    }
    }
    // Ambil data sesuai filter
    $qlkp_TaskID = $qlkp_TaskID->get();
    // Kirimkan data ke view qlkp_TaskID
    return view('qlkp_TaskID', compact
    ('qlkp_TaskID', 'startDate', 'endDate', 'filterDate'));
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

    // Query untuk rekap data berdasarkan rentang tanggal
    $qlkp_TaskID = data_taskid::select(DB::raw('tanggal, message, COUNT(*) as total'))
        ->whereBetween('tanggal', [$startDate, $endDate]) // Filter rentang tanggal
        ->groupBy('tanggal', 'message')
        ->get();

    // Mengirimkan data ke view
    return view('qlkp_rekap_taskid', compact('qlkp_TaskID', 'startDate', 'endDate'));
}

    public function showJson()
    {
        $data_kodebooking = DataKodebooking::all();
        return response()->json($data_kodebooking);
    }

    public function qlkp_showJson()
    {
        $qlkp_data_kodebooking = DB::table('qlkp_data_kodebooking')->get();
        return response()->json($qlkp_data_kodebooking);
    }

    // ==================== TEMANGGUNG (QLTMG) ====================

    public function qltmg_datakodebooking(Request $request)
    {
        $startDate = Carbon::parse($request->input('start_date', date('Y-m-d')))->format('Y-m-d');
        $endDate = Carbon::parse($request->input('end_date', date('Y-m-d')))->format('Y-m-d');
        $messageFilter = $request->input('message');

        $qltmg_data_kodebooking = DB::table('qltmg_data_kodebooking')
            ->when($messageFilter, function ($query, $messageFilter) {
                $query->where('message', 'like', "%{$messageFilter}%");
            })
            ->whereDate('tanggalperiksa', '>=', $startDate)
            ->whereDate('tanggalperiksa', '<=', $endDate)
            ->get();

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

        $qltmg_TaskID = DB::table('qltmg_data_taskids');

        if ($messageFilter) {
            $qltmg_TaskID->where('message', 'like', "%{$messageFilter}%");
        }

        if ($filterDate) {
            $qltmg_TaskID->whereDate('tanggal', '=', $filterDate);
        } else {
            if ($startDate && $endDate) {
                $qltmg_TaskID->whereBetween('tanggal', [$startDate, $endDate]);
            } elseif ($startDate) {
                $qltmg_TaskID->where('tanggal', '>=', $startDate);
            } elseif ($endDate) {
                $qltmg_TaskID->where('tanggal', '<=', $endDate);
            }
        }

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
            ->whereDate('tanggalperiksa', $date)
            ->groupBy('message')
            ->get();

        // Ambil data berdasarkan tanggal untuk TaskID
        $task_id_data = data_taskid::select(DB::raw('message, count(*) as total'))
            ->whereDate('tanggal', $date)
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
            ->whereDate('tanggalperiksa', $date)
            ->groupBy('message')
            ->get();

        $task_id_data = DB::table('qlkp_data_taskids')
            ->select(DB::raw('message, count(*) as total'))
            ->whereDate('tanggal', $date)
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
