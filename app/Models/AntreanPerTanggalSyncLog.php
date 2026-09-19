<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AntreanPerTanggalSyncLog extends Model
{
    protected $table = 'antrean_per_tanggal_sync_logs';

    protected $fillable = [
        'kode_ql',
        'tanggal',
        'status',
        'total_data',
        'message',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'tanggal'     => 'date',
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function isDone(): bool
    {
        return in_array($this->status, ['success', 'error']);
    }
}
