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
     * Ambil daftar antrean yang memiliki $sourceTaskId tetapi belum memiliki $missingTaskId (di bawahnya)
     */
    public function getTaskWithNullPrevTask(int $sourceTaskId, int $missingTaskId, $urlQL = 'QLJ', $kodebooking = null)
    {
        $prevLowerTaskId = $missingTaskId - 1;

        $query = DB::table("{$this->table} as src")
            ->select(
                'src.kodebooking',
                'src.waktu',
                'src.idpendaftaran',
                DB::raw($prevLowerTaskId >= 1 ? 'low.waktu as waktu_lower' : 'NULL as waktu_lower')
            )
            ->leftJoin("{$this->table} as miss", function ($join) use ($missingTaskId) {
                $join->on('src.kodebooking', '=', 'miss.kodebooking')
                    ->where('miss.taskid', '=', $missingTaskId);
            })
            ->leftJoin("{$this->table} as dt99", function ($join) {
                $join->on('src.kodebooking', '=', 'dt99.kodebooking')
                    ->where('dt99.taskid', '=', 99);
            });

        if ($prevLowerTaskId >= 1) {
            $query->leftJoin("{$this->table} as low", function ($join) use ($prevLowerTaskId) {
                $join->on('src.kodebooking', '=', 'low.kodebooking')
                    ->where('low.taskid', '=', $prevLowerTaskId);
            });
        }

        $query->where('src.taskid', '=', $sourceTaskId)
            ->where('src.waktu', '>', 0)
            ->where(function ($q) {
                $q->whereNull('src.message')
                  ->orWhere('src.message', 'not like', '%TaskId terakhir 99%');
            })
            ->whereNull('miss.id')
            ->whereNull('dt99.id');

        if (!empty($kodebooking)) {
            $query->where('src.kodebooking', '=', $kodebooking);
        } else {
            $query->whereBetween('src.tanggal', [
                now()->setTimezone('Asia/Jakarta')->subDays(31)->toDateString(),
                now()->setTimezone('Asia/Jakarta')->toDateString()
            ]);
        }

        return $query->get();
    }

    public function getTaskid3withNullTaskid2($urlQL = 'QLJ', $kodebooking = null)
    {
        return $this->getTaskWithNullPrevTask(3, 2, $urlQL, $kodebooking);
    }

    public function getTaskid2withNullTaskid1($urlQL = 'QLJ', $kodebooking = null)
    {
        return $this->getTaskWithNullPrevTask(2, 1, $urlQL, $kodebooking);
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
