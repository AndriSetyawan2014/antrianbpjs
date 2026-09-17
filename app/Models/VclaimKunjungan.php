<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class VclaimKunjungan extends Model
{
    protected $table = 'vclaim_kunjungan';

    protected $fillable = [
        'kode_ql',
        'kode_ppk',
        'tgl_kunjungan',
        'jns_pelayanan',
        'no_sep',
        'no_kartu',
        'no_rujukan',
        'nama',
        'diagnosa',
        'poli',
        'kelas_rawat',
        'jns_pelayanan_label',
        'tgl_sep',
        'tgl_plg_sep',
        'raw_response',
        'sync_status',
        'sync_message',
        'synced_at',
    ];

    protected $casts = [
        'raw_response'  => 'array',
        'tgl_kunjungan' => 'date',
        'tgl_sep'       => 'date',
        'tgl_plg_sep'   => 'date',
        'synced_at'     => 'datetime',
    ];

    // ── Scopes ──────────────────────────────────────────────────

    public function scopeByQL(Builder $query, string $kodeQL): Builder
    {
        return $query->where('kode_ql', strtoupper($kodeQL));
    }

    public function scopeRawatJalan(Builder $query): Builder
    {
        return $query->where('jns_pelayanan', 2);
    }

    public function scopeRawatInap(Builder $query): Builder
    {
        return $query->where('jns_pelayanan', 1);
    }

    public function scopeByTanggal(Builder $query, string $tanggal): Builder
    {
        return $query->where('tgl_kunjungan', $tanggal);
    }

    // ── Accessors ────────────────────────────────────────────────

    public function getJnsPelayananTextAttribute(): string
    {
        return $this->jns_pelayanan == 1 ? 'Rawat Inap' : 'Rawat Jalan';
    }

    public function getKelasRawatTextAttribute(): string
    {
        return match ($this->kelas_rawat) {
            '1' => 'Kelas I',
            '2' => 'Kelas II',
            '3' => 'Kelas III',
            default => $this->kelas_rawat ?? '-',
        };
    }
}
