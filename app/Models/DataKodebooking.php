<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataKodebooking extends Model
{
    use HasFactory;

    protected $table = 'data_kodebooking'; // Default table

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

    /**
     * Set table name based on urlQL
     */
    public function setTableByQL($urlQL = 'QLJ')
    {
        $this->table = match (strtoupper($urlQL)) {
            'QLJ'   => 'data_kodebooking',
            'QLKP'  => 'qlkp_data_kodebooking',
            'QLTMG' => 'qltmg_data_kodebooking',
            default => 'data_kodebooking',
        };
        return $this;
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
