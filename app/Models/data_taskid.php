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
        $this->table = match (strtoupper($urlQL)) {
            'QLJ'   => 'data_taskids',
            'QLKP'  => 'qlkp_data_taskids',
            'QLTMG' => 'qltmg_data_taskids',
            default => 'data_taskids',
        };
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

    public function getTaskid4withNullTaskid5($urlQL = 'QLJ')
    {
        $kodebookingTable = match ($urlQL) {
            'QLJ'   => 'data_kodebooking',
            'QLKP'  => 'qlkp_data_kodebooking',
            'QLTMG' => 'qltmg_data_kodebooking',
            default => 'data_kodebooking',
        };
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
            ->whereBetween('dk.tanggalperiksa', [
                now()->setTimezone('Asia/Jakarta')->subDays(31)->toDateString(),
                now()->setTimezone('Asia/Jakarta')->subDays(1)->toDateString()
            ])
            ->whereNotNull('dt4.kodebooking')
            ->whereNull('dt5.kodebooking')
            ->get();
    }
}
