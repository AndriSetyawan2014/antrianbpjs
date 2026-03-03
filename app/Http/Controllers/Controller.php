<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request; // Untuk menangani permintaan HTTP
use App\Models\AddAntrian; // Model untuk tabel addantrian
use App\Models\PengirimanTaskID; // Model untuk tabel pengirimantaskid
use App\Models\TambahAntrianOnline; // Model untuk tabel tambahantrianonline

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}