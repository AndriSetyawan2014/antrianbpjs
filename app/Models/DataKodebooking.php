<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataKodebooking extends Model
{
    use HasFactory;

    protected $table;// = 'data_kodebooking';

    protected $fillable = [
        'idpendaftaran',
        'norm',
        'kodebooking',
        'carabayar',
        'noantrian',
        'idjeniskunjungan',
        'tanggalperiksa',
        'ispasienlama',
        'nojkn',
        'nik',
        'notelpon',
        'nomorreferensi',
        'quota_jkn',
        'quota_jkn_sisa',
        'quota_nonjkn',
        'quota_nonjkn_sisa',
        'estimasidilayani',
        'bpjs_kodedokter',
        'namadokter',
        'kodeunit',
        'namaunit',
        'jammulai',
        'jamakhir',
        'code',
        'message',
        'statuspemeriksaan',
        'request',
        'response',
        'reupload'
    ];

    public $timestamps = true;

    public function __construct(array $attributes = [], $urlQL = 'QLJ')
    {
        parent::__construct($attributes);

        // Menentukan tabel berdasarkan nilai $urlQL
        $this->table = $urlQL === 'QLJ' ? 'data_kodebooking' : 'qlkp_data_kodebooking';
    }

    // public static function boot()
    // {
    //     parent::boot();

    //     static::updating(function ($model) {
    //         $model->updated_at = now();
    //     });
    // }

    public function scopeTanggalPeriksaBetween($query, $startDate, $endDate)
    {
        if ($startDate && $endDate) {
            return $query->whereBetween('tanggalperiksa', [$startDate, $endDate]);
        }
        return $query;
    }
}
