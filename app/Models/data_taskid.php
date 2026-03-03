<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class data_taskid extends Model
{
    use HasFactory;

    protected $table; //= 'data_taskid'; // Pastikan ini sesuai dengan nama tabel Anda

    protected $fillable = [
        'kodebooking',
        'waktu',
        'taskid',
        'idpendaftaran',
        'tanggal',
        'jam',
        'code',
        'message',
        'request',
        'response',
        'reupload',
    ];

    public $timestamps = true;

    public function __construct(array $attributes = [], $urlQL = 'QLJ')
    {
        parent::__construct($attributes);

        // Menentukan tabel berdasarkan nilai $urlQL
        $this->table = $urlQL === 'QLJ' ? 'data_taskids' : 'qlkp_data_taskids';
    }

    public function getWaktuTaskID4($kodebooking)
    {
        return $this->where('taskid', 4)
            ->where('kodebooking', $kodebooking)
            ->value('waktu');
    }

    public function getidpendaftaranTaskID4($kodebooking)
    {
        return $this->where('taskid', 4)
            ->where('kodebooking', $kodebooking)
            ->value('idpendaftaran');
    }

    public function getTaskid4withNullTaskid5($urlQL = 'QLJ')
    {
        $kodebookingTable = $urlQL === 'QLJ' ? 'data_kodebooking' : 'qlkp_data_kodebooking';
        Log::info('Nama tabel kodebookingTable: ' . $kodebookingTable);
        Log::info('Nama tabel this->table: ' . $this->table);
        return DB::table("$kodebookingTable as dk")
            ->select('dt4.*')
            ->leftJoin("{$this->table} as dt4", function ($join) {
                $join->on('dk.kodebooking', '=', 'dt4.kodebooking')
                    ->where('dt4.taskid', '=', 4)
                    ->where('dt4.reupload', '=', 0)
                    ->where('dt4.message', 'not like', '%TaskId terakhir 99%');
            })
            ->leftJoin("{$this->table} as dt5", function ($join) {
                $join->on('dk.kodebooking', '=', 'dt5.kodebooking')
                    ->where('dt5.taskid', '=', 5);
            })
            ->where('dk.tanggalperiksa', '<', now()->setTimezone('Asia/Jakarta')->toDateString()) // DATE(NOW()) equivalent
            ->where('dk.reupload', '=', 0)
            ->where('dk.statuspemeriksaan', '!=', 'batal')
            ->whereNotNull('dt4.kodebooking')
            ->whereNull('dt5.kodebooking')
            ->get();
    }
}
