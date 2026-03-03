<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PengirimanTaskIDController;
use App\Http\Controllers\Api\WABlastControllerapi;
use App\Http\Controllers\Api\TambahAntrianOnlineController;
use App\Http\Controllers\WABlastControllerapi as ControllersWABlastControllerapi;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Middleware untuk autentikasi
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Addantrian
// Route untuk Menambahkan Antrian
Route::post('/addAntrians_single_request', [TambahAntrianOnlineController::class, 'addAntrians_single_request']);
Route::post('/data_kodebooking', [TambahAntrianOnlineController::class, 'data_kodebooking']);
Route::post('/nomor_rekon', [TambahAntrianOnlineController::class, 'nomor_rekon_post']);
Route::post('/listtask', [PengirimanTaskIDController::class, 'listtask_post']);
Route::post('/addAntrians', [TambahAntrianOnlineController::class, 'addAntrians']);

// Route untuk Mendapatkan Data Kode Booking
Route::get('/data_kodebooking/paginate', [TambahAntrianOnlineController::class, 'getDataKodeBooking']);
Route::get('/data_kodebooking', [TambahAntrianOnlineController::class, 'getDataKodeBooking']);

// Route untuk Mendapatkan Antrian Berdasarkan ID
Route::get('/data_kodebooking/{id}', [TambahAntrianOnlineController::class, 'getAntrianById']);

// Route untuk Mendapatkan Antrian Berdasarkan NORM
Route::get('/data_kodebooking-by-norm/{norm}', [TambahAntrianOnlineController::class, 'getAntrianByNorm']);

// Route untuk Menghapus Antrian (menggunakan DELETE)
Route::delete('/addantrians', [TambahAntrianOnlineController::class, 'batalAntrian']);

// Batal Antrian
Route::post('/batal-antrian', [TambahAntrianOnlineController::class, 'batalAntrian']);

// Akses endpoint data pending kode booking
Route::get('/data-pending-kodebooking', [TambahAntrianOnlineController::class, 'data_pending_kodebooking_get']);

// Menambahkan antrian otomatis dari data pending kode booking
Route::get('/add-antrians-otomatis', [TambahAntrianOnlineController::class, 'addAntrians_otomatis']);

// Menampilkan rekap kode booking
Route::get('/kodebooking/messages', [TambahAntrianOnlineController::class, 'showMessages'])->name('kodebooking.messages');

// Menampilkan message rekap kode booking di data kode booking
Route::get('/data_kodebooking', [TambahAntrianOnlineController::class, 'data_kodebooking'])->name('data_kodebooking');

// TASK ID
// Route untuk Menambahkan task ID
Route::post('/taskID_single_request', [PengirimanTaskIDController::class, 'taskID_single_request']);
// Menambahkan antrian otomatis dari data pending task ID
Route::get('/task-id-otomatis', [PengirimanTaskIDController::class, 'taskID_otomatis']);
// Route untuk Task ID
Route::match(['get', 'post'], '/addantrians/task-id', [PengirimanTaskIDController::class, 'taskId']);
// Pengiriman task ID
Route::post('/pengiriman-task-id', [PengirimanTaskIDController::class, 'pengiriman_taskID_post']);
// get taSK ID
Route::post('/taskid', [PengirimanTaskIDController::class, 'getTaskIDs']);
Route::get('/task-id', [PengirimanTaskIDController::class, 'getTaskIDs']);
Route::get('/addantrians/paginate', [TambahAntrianOnlineController::class, 'getAddAntrians']);
// Akses endpoint data pending task ID
Route::get('/data-pending-taskID', [PengirimanTaskIDController::class, 'data_pending_taskID_get']);

// route untuk menjalankan kodebooking dan task id
Route::get('/kodebooking-all-ql', [TambahAntrianOnlineController::class, 'kodebooking_get']);
Route::get('/taskid-all-ql', [PengirimanTaskIDController::class, 'taskid_get']);

// Route::get('/wablast/send', [WABlastController::class, 'index'])->name('wablast.send');
// Route::post('/wablast/send', [WABlastController::class, 'index'])->name('wablast.send');

// Route::get('/wablast', [WABlastControllerapi::class, 'index'])->name('wablast.index');
// Route::post('/wablast/send', [WABlastControllerapi::class, 'send'])->name('wablast.send');
Route::get('/kirimpesan', [WABlastControllerapi::class, 'send_all'])->name('wablast.send-all');
