<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class data_taskid extends Model
{
    use HasFactory;

    protected $table = 'data_taskids'; // Default table

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
        'norm',
        'reupload',
    ];

    public $timestamps = true;

    /**
     * Set table name based on urlQL
     */
    public function setTableByQL($urlQL = 'QLJ')
    {
        $map = [
            'QLJ'   => 'data_taskids',
            'QLKP'  => 'qlkp_data_taskids',
            'QLTMG' => 'qltmg_data_taskids',
        ];
        $this->table = $map[strtoupper($urlQL)] ?? 'data_taskids';
        return $this;
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

    /**
     * Ambil daftar antrean yang memiliki Task ID 3 tetapi belum memiliki Task ID 2
     */
    public function getTaskid3withNullTaskid2($urlQL = 'QLJ', $kodebooking = null)
    {
        $query = DB::table("{$this->table} as dt3")
            ->select('dt3.kodebooking', 'dt3.waktu', 'dt3.idpendaftaran')
            ->leftJoin("{$this->table} as dt2", function ($join) {
                $join->on('dt3.kodebooking', '=', 'dt2.kodebooking')
                    ->where('dt2.taskid', '=', 2);
            })
            ->leftJoin("{$this->table} as dt99", function ($join) {
                $join->on('dt3.kodebooking', '=', 'dt99.kodebooking')
                    ->where('dt99.taskid', '=', 99);
            })
            ->where('dt3.taskid', '=', 3)
            ->where('dt3.waktu', '>', 0)
            ->where(function ($q) {
                $q->whereNull('dt3.message')
                  ->orWhere('dt3.message', 'not like', '%TaskId terakhir 99%');
            })
            ->whereNull('dt2.id')
            ->whereNull('dt99.id');

        if (!empty($kodebooking)) {
            $query->where('dt3.kodebooking', '=', $kodebooking);
        } else {
            $query->whereBetween('dt3.tanggal', [
                now()->setTimezone('Asia/Jakarta')->subDays(31)->toDateString(),
                now()->setTimezone('Asia/Jakarta')->toDateString()
            ]);
        }

        return $query->get();
    }

    /**
     * Ambil daftar antrean yang memiliki Task ID 2 tetapi belum memiliki Task ID 1
     */
    public function getTaskid2withNullTaskid1($urlQL = 'QLJ', $kodebooking = null)
    {
        $query = DB::table("{$this->table} as dt2")
            ->select('dt2.kodebooking', 'dt2.waktu', 'dt2.idpendaftaran')
            ->leftJoin("{$this->table} as dt1", function ($join) {
                $join->on('dt2.kodebooking', '=', 'dt1.kodebooking')
                    ->where('dt1.taskid', '=', 1);
            })
            ->leftJoin("{$this->table} as dt99", function ($join) {
                $join->on('dt2.kodebooking', '=', 'dt99.kodebooking')
                    ->where('dt99.taskid', '=', 99);
            })
            ->where('dt2.taskid', '=', 2)
            ->where('dt2.waktu', '>', 0)
            ->where(function ($q) {
                $q->whereNull('dt2.message')
                  ->orWhere('dt2.message', 'not like', '%TaskId terakhir 99%');
            })
            ->whereNull('dt1.id')
            ->whereNull('dt99.id');

        if (!empty($kodebooking)) {
            $query->where('dt2.kodebooking', '=', $kodebooking);
        } else {
            $query->whereBetween('dt2.tanggal', [
                now()->setTimezone('Asia/Jakarta')->subDays(31)->toDateString(),
                now()->setTimezone('Asia/Jakarta')->toDateString()
            ]);
        }

        return $query->get();
    }

    public function getTaskid4withNullTaskid5($urlQL = 'QLJ', $kodebooking = null)
    {
        $kbMap = [
            'QLJ'   => 'data_kodebooking',
            'QLKP'  => 'qlkp_data_kodebooking',
            'QLTMG' => 'qltmg_data_kodebooking',
        ];
        $kodebookingTable = $kbMap[strtoupper($urlQL)] ?? 'data_kodebooking';
        Log::info('Nama tabel kodebookingTable: ' . $kodebookingTable);
        Log::info('Nama tabel this->table: ' . $this->table);

        $query = DB::table("$kodebookingTable as dk")
            ->select('dt4.kodebooking', 'dt4.waktu', 'dt4.idpendaftaran')
            ->join("{$this->table} as dt4", function ($join) use ($kodebooking) {
                $join->on('dk.kodebooking', '=', 'dt4.kodebooking')
                    ->where('dt4.taskid', '=', 4)
                    ->where(function ($q) {
                        $q->whereNull('dt4.message')
                          ->orWhere('dt4.message', 'not like', '%TaskId terakhir 99%');
                    });
                if (empty($kodebooking)) {
                    $join->where('dt4.reupload', '=', 0);
                }
            })
            ->leftJoin("{$this->table} as dt5", function ($join) {
                $join->on('dk.kodebooking', '=', 'dt5.kodebooking')
                    ->where('dt5.taskid', '=', 5);
            })
            ->whereNull('dt5.kodebooking');

        if (!empty($kodebooking)) {
            $query->where('dk.kodebooking', '=', $kodebooking);
        } else {
            $query->whereBetween('dk.tanggalperiksa', [
                now()->setTimezone('Asia/Jakarta')->subDays(31)->toDateString(),
                now()->setTimezone('Asia/Jakarta')->subDays(1)->toDateString()
            ]);
        }

        return $query->get();
    }
}
