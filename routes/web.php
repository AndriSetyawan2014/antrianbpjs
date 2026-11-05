<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AntrianController;
use App\Exports\DataKodeBookingExport;
use App\Exports\TaskIdExport;
use App\Exports\QlkpDataKodebookingExport;
use App\Exports\QlkpTaskIdExport;
use App\Exports\QltmgDataKodebookingExport;
use App\Exports\QltmgTaskIdExport;
use App\Http\Controllers\WABlastController;
use App\Http\Controllers\Api\VclaimController;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\DataKodebooking;
use App\Models\data_taskid;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// dashboard
Route::get('/', [AntrianController::class, 'index'])->name('dashboard.index');
Route::get('/dashboard', [AntrianController::class, 'index'])->name('dashboard');

// Chart data
Route::get('/api/chart-data', function () {
    $since = now()->subDays(90)->toDateString();
    // Ambil data 90 hari terakhir menggunakan kolom tanggal terindeks
    $dataKodebooking = DataKodebooking::selectRaw('tanggalperiksa as date, COUNT(*) as total')
        ->where('tanggalperiksa', '>=', $since)
        ->groupBy('tanggalperiksa')
        ->orderBy('tanggalperiksa', 'ASC')
        ->get();

    $dataTaskids = data_taskid::selectRaw('tanggal as date, COUNT(*) as total')
        ->where('tanggal', '>=', $since)
        ->groupBy('tanggal')
        ->orderBy('tanggal', 'ASC')
        ->get();

    return response()->json([
        'data_kodebooking' => $dataKodebooking,
        'taskids' => $dataTaskids,
    ]);
});

// Routes untuk QL Yogyakarta
// Tampilan
Route::get('/data_kodebooking', [AntrianController::class, 'data_kodebooking'])->name('web.data_kodebooking');
Route::get('/rekap_kodebooking', [AntrianController::class, 'rekapKodebooking'])->name('rekap_kodebooking');
Route::get('/TaskID', [AntrianController::class, 'TaskID'])->name('TaskID');
Route::get('/monitoring_taskid', [AntrianController::class, 'monitoringTaskid'])->name('monitoring_taskid');
Route::get('/monitoring_taskid/{kodebooking}', [AntrianController::class, 'getMonitoringTaskidDetail']);
Route::get('/rekap_taskid', [AntrianController::class, 'rekapTaskId'])->name('rekap_taskid');
Route::get('/get-patient-data', [AntrianController::class, 'getPatientData']);
Route::get('/get-patient-data-kulonprogo', [AntrianController::class, 'getPatientDataKulonProgo']);
Route::get('/get-patient-data-temanggung', [AntrianController::class, 'getPatientDataTemanggung']);


// Route untuk filter dan reset
Route::get('/taskid/filter', [AntrianController::class, 'TaskID'])->name('taskid.filter');
Route::get('/taskid/reset', [AntrianController::class, 'TaskID'])->name('taskid.reset');

// Export Excel - QL Yogyakarta
Route::get('/export-kodebooking', function () {
    return Excel::download(new DataKodeBookingExport, 'QLJ_Data_Kodebooking.xlsx');
})->name('export_kodebooking');
Route::get('/export-taskid', function () {
    return Excel::download(new TaskIdExport, 'QLJ_Taskid.xlsx');
})->name('export_taskid');

// Routes untuk QL Kulon Progo
// Tampilan
Route::get('/qlkp_data_kodebooking', [AntrianController::class, 'qlkp_datakodebooking'])->name('qlkp_data_kodebooking');
Route::get('/qlkp_rekap_kodebooking', [AntrianController::class, 'qlkp_rekap_kodebooking'])->name('qlkp_rekap_kodebooking');
Route::get('/qlkp_TaskID', [AntrianController::class, 'qlkp_TaskID'])->name('qlkp_TaskID');
Route::get('/qlkp_taskid/filter', [AntrianController::class, 'qlkp_TaskID'])->name('qlkp_taskid.filter');
Route::get('/qlkp_taskid/reset', [AntrianController::class, 'qlkp_TaskID'])->name('qlkp_taskid.reset');
Route::get('/qlkp_rekap_taskid', [AntrianController::class, 'qlkp_rekap_taskid'])->name('qlkp_rekap_taskid');
Route::get('/qlkp_kesesuaian_jadwal', [AntrianController::class, 'qlkp_kesesuaian_jadwal'])->name('qlkp_kesesuaian_jadwal');
Route::get('/qlkp_sync_jadwal_bpjs', [AntrianController::class, 'qlkp_sync_jadwal_bpjs'])->name('qlkp_sync_jadwal_bpjs');

// Export Excel - QL Kulon Progo
Route::get('/export-qlkp-kodebooking', function () {
    return Excel::download(new QlkpDataKodebookingExport, 'QLKP_Data_Kodebooking.xlsx');
})->name('export_qlkp_kodebooking');
Route::get('/export-qlkp-taskid', function () {
    return Excel::download(new QlkpTaskIdExport, 'QLKP_Task_ID.xlsx');
})->name('export_qlkp_taskid');

// Menambahkan route untuk Task ID QLKP
Route::get('/qlkp-taskid', [AntrianController::class, 'qlkp_TaskID'])->name('qlkp_taskid');

// Routes untuk QL Temanggung
// Tampilan
Route::get('/qltmg_data_kodebooking', [AntrianController::class, 'qltmg_datakodebooking'])->name('qltmg_data_kodebooking');
Route::get('/qltmg_rekap_kodebooking', [AntrianController::class, 'qltmg_rekap_kodebooking'])->name('qltmg_rekap_kodebooking');
Route::get('/qltmg_TaskID', [AntrianController::class, 'qltmg_TaskID'])->name('qltmg_TaskID');
Route::get('/qltmg_taskid/filter', [AntrianController::class, 'qltmg_TaskID'])->name('qltmg_taskid.filter');
Route::get('/qltmg_taskid/reset', [AntrianController::class, 'qltmg_TaskID'])->name('qltmg_taskid.reset');
Route::get('/qltmg_rekap_taskid', [AntrianController::class, 'qltmg_rekap_taskid'])->name('qltmg_rekap_taskid');

// Export Excel - QL Temanggung
Route::get('/export-qltmg-kodebooking', function () {
    return Excel::download(new QltmgDataKodebookingExport, 'QLTMG_Data_Kodebooking.xlsx');
})->name('export_qltmg_kodebooking');
Route::get('/export-qltmg-taskid', function () {
    return Excel::download(new QltmgTaskIdExport, 'QLTMG_Task_ID.xlsx');
})->name('export_qltmg_taskid');

// Menambahkan route untuk Task ID QLTMG
Route::get('/qltmg-taskid', [AntrianController::class, 'qltmg_TaskID'])->name('qltmg_taskid');

// Route::get('/wablast', [WABlastController::class, 'index'])->name('wablast.index');
// Route::post('/wablast/send', [WABlastController::class, 'send'])->name('wablast.send');

// ── VClaim — Monitoring Kunjungan ──────────────────────────────────────────
Route::get('/vclaim/kunjungan-rawat-jalan', [VclaimController::class, 'pageKunjunganJalan'])
    ->name('vclaim.kunjungan.jalan');

Route::get('/vclaim/rekap-kunjungan-rawat-jalan', [VclaimController::class, 'pageRekapKunjunganJalan'])
    ->name('vclaim.rekap.kunjungan.jalan');

Route::get('/vclaim/endpoint-status', [VclaimController::class, 'endpointStatus'])
    ->name('vclaim.endpoint.status');

Route::get('/vclaim/ping-peserta', [VclaimController::class, 'pingPeserta'])
    ->name('vclaim.ping.peserta');

Route::get('/vclaim/ping-rujukan', [VclaimController::class, 'pingRujukan'])
    ->name('vclaim.ping.rujukan');

Route::get('/vclaim/ping-rujukan-kartu', [VclaimController::class, 'pingRujukanByNoKartu'])
    ->name('vclaim.ping.rujukan.kartu');

Route::get('/vclaim/ping-rujukan-list', [VclaimController::class, 'pingRujukanListPeserta'])
    ->name('vclaim.ping.rujukan.list');

Route::get('/vclaim/ping-referensi-diagnosa', [VclaimController::class, 'pingReferensiDiagnosa'])
    ->name('vclaim.ping.referensi.diagnosa');

Route::get('/vclaim/ping-referensi-dpjp', [VclaimController::class, 'pingReferensiDpjp'])
    ->name('vclaim.ping.referensi.dpjp');

Route::get('/vclaim/ping-sep', [VclaimController::class, 'pingSep'])
    ->name('vclaim.ping.sep');

Route::get('/vclaim/ping-surat-kontrol', [VclaimController::class, 'pingSuratKontrol'])
    ->name('vclaim.ping.surat.kontrol');

Route::get('/vclaim/ping-riwayat-pelayanan', [VclaimController::class, 'pingRiwayatPelayanan'])
    ->name('vclaim.ping.riwayat.pelayanan');

Route::get('/vclaim/ping-antrol-referensi-poli', [VclaimController::class, 'pingAntrolReferensiPoli'])
    ->name('vclaim.ping.antrol.referensi.poli');

Route::get('/vclaim/ping-antrol-jadwal-dokter', [VclaimController::class, 'pingAntrolJadwalDokter'])
    ->name('vclaim.ping.antrol.jadwal.dokter');

Route::get('/vclaim/ping-antrol-antrean-add', [VclaimController::class, 'pingAntrolAntreanAdd'])
    ->name('vclaim.ping.antrol.antrean.add');

Route::get('/vclaim/ping-antrol-update-jadwal', [VclaimController::class, 'pingAntrolUpdateJadwal'])
    ->name('vclaim.ping.antrol.update.jadwal');

Route::get('/vclaim/ping-get-fingerprint', [VclaimController::class, 'pingVclaimFingerprint'])
    ->name('vclaim.ping.get.fingerprint');

Route::get('/vclaim/ping-antrol-batal-antrean', [VclaimController::class, 'pingAntrolBatalAntrean'])
    ->name('vclaim.ping.antrol.batal.antrean');

Route::get('/vclaim/ping-antrol-get-list-task', [VclaimController::class, 'pingAntrolGetListTask'])
    ->name('vclaim.ping.antrol.get.list.task');

Route::get('/vclaim/ping-antrol-antrean-per-tanggal', [VclaimController::class, 'pingAntrolAntreanPerTanggal'])
    ->name('vclaim.ping.antrol.antrean.per.tanggal');

Route::get('/vclaim/antrol-antrean-per-tanggal', [VclaimController::class, 'pageAntrolAntreanPerTanggal'])
    ->name('vclaim.antrol.antrean.per.tanggal');

Route::get('/api/vclaim/antrol-antrean-per-tanggal-data', [VclaimController::class, 'getAntrolAntreanPerTanggalData'])
    ->name('api.vclaim.antrol.antrean.per.tanggal.data');

Route::get('/vclaim/rekap-antrean-per-tanggal', [VclaimController::class, 'pageRekapAntreanPerTanggal'])
    ->name('vclaim.rekap.antrean.per.tanggal');

Route::get('/api/vclaim/sync-antrol-antrean', [VclaimController::class, 'syncAntreanPerTanggal'])
    ->name('api.vclaim.sync.antrol.antrean');

Route::get('/api/vclaim/sync-antrol-antrean-range', [VclaimController::class, 'syncAntreanPerTanggalRange'])
    ->name('api.vclaim.sync.antrol.antrean.range');

Route::get('/api/vclaim/sync-antrol-status', [VclaimController::class, 'syncAntreanStatus'])
    ->name('api.vclaim.sync.antrol.status');

Route::get('/vclaim/ping-antrol-antrean-per-kode-booking', [VclaimController::class, 'pingAntrolAntreanPerKodeBooking'])
    ->name('vclaim.ping.antrol.antrean.per.kode.booking');

// Settings & Tools
Route::get('/settings', function () {
    return view('settings.index');
})->name('settings.index');

Route::post('/settings/clear-cache', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        $output = \Illuminate\Support\Facades\Artisan::output();
        return back()->with('success', 'Cache berhasil dibersihkan! Log: ' . $output);
    } catch (\Exception $e) {
        return back()->with('error', 'Gagal membersihkan cache: ' . $e->getMessage());
    }
})->name('settings.clear-cache');

Route::post('/settings/migrate', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $output = \Illuminate\Support\Facades\Artisan::output();
        return back()->with('success', 'Migrasi database berhasil dijalankan! Log: ' . $output);
    } catch (\Exception $e) {
        return back()->with('error', 'Gagal menjalankan migrasi: ' . $e->getMessage());
    }
})->name('settings.migrate');

// ============================================
// TEMPORARY: Clear View Cache via Browser
// HAPUS SETELAH DIGUNAKAN — KEAMANAN
// ============================================
Route::get('/clear-view-cache', function () {
    try {
        $viewsDir = storage_path('framework/views');
        $files = glob($viewsDir . '/*');
        $deleted = 0;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
                $deleted++;
            }
        }
        
        // Hapus view cache file di bootstrap/cache (tanpa bootstrap_path)
        $viewCacheFile = base_path('bootstrap/cache/views.php');
        if (file_exists($viewCacheFile)) {
            unlink($viewCacheFile);
        }
        $viewCacheFile2 = base_path('bootstrap/cache/views.php');
        if (file_exists($viewCacheFile2)) {
            unlink($viewCacheFile2);
        }
        
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        
        return response()->json([
            'success' => true,
            'message' => "View cache berhasil dibersihkan. {$deleted} file dihapus.",
            'files_deleted' => $deleted
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal: ' . $e->getMessage()
        ], 500);
    }
})->name('temp.clear-view-cache');