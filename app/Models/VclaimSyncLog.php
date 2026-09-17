<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VclaimSyncLog extends Model
{
    protected $table = 'vclaim_sync_logs';

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

    // ── Scopes ──────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDone($query)
    {
        return $query->whereIn('status', ['success', 'error']);
    }

    // ── Helpers ─────────────────────────────────────────────────

    public function isDone(): bool
    {
        return in_array($this->status, ['success', 'error']);
    }

    public function getDurationAttribute(): ?string
    {
        if (!$this->started_at || !$this->finished_at) return null;
        return $this->started_at->diffForHumans($this->finished_at, true);
    }
}
