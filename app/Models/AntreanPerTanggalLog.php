<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AntreanPerTanggalLog extends Model
{
    protected $table = 'antrean_per_tanggal_logs';

    protected $fillable = [
        'kode_ql',
        'kodebooking',
        'tanggal',
        'norekammedis',
        'nik',
        'nokapst',
        'kodepoli',
        'kodedokter',
        'jampraktek',
        'jeniskunjungan',
        'nomorreferensi',
        'sumberdata',
        'ispeserta',
        'noantrean',
        'estimasidilayani',
        'createdtime',
        'status',
        'raw_response',
        'synced_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'ispeserta' => 'boolean',
        'raw_response' => 'array',
        'synced_at' => 'datetime',
    ];
}
