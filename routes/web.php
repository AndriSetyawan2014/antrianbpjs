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
    // Ambil data dari tabel
    $dataKodebooking = DataKodebooking::selectRaw('DATE(created_at) as date, COUNT(*) as total')
        ->groupBy('date')
        ->orderBy('date', 'ASC')
        ->get();

    $dataTaskids = data_taskid::selectRaw('DATE(created_at) as date, COUNT(*) as total')
        ->groupBy('date')
        ->orderBy('date', 'ASC')
        ->get();

    return response()->json([
        'data_kodebooking' => $dataKodebooking,
        'taskids' => $dataTaskids,
    ]);
});

// Routes untuk QL Yogyakarta
// Tampilan
Route::get('/data_kodebooking', [AntrianController::class, 'data_kodebooking'])->name('data_kodebooking');
Route::get('/rekap_kodebooking', [AntrianController::class, 'rekapKodebooking'])->name('rekap_kodebooking');
Route::get('/TaskID', [AntrianController::class, 'TaskID'])->name('TaskID');
Route::get('/rekap_taskid', [AntrianController::class, 'rekapTaskId'])->name('rekap_taskid');
Route::get('/get-patient-data', [AntrianController::class, 'getPatientData']);
Route::get('/get-patient-data-kulonprogo', [AntrianController::class, 'getPatientDataKulonProgo']);


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
Route::get('/qlkp_rekap_taskid', [AntrianController::class, 'qlkp_rekap_taskid'])->name('qlkp_rekap_taskid');

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
