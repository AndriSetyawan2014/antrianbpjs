<?php

namespace App\Jobs;

use App\Helpers\BpjsHelper;
use App\Models\VclaimKunjungan;
use App\Models\VclaimSyncLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncVclaimKunjunganJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;
    public int $tries   = 2;

    public function __construct(
        public string $urlQL,
        public string $tanggal,
        public int    $syncLogId
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $log = VclaimSyncLog::find($this->syncLogId);
        if (!$log) {
            Log::warning("[SyncVclaimKunjunganJob] Log ID {$this->syncLogId} tidak ditemukan. Job dibatalkan.");
            return;
        }

        $log->update(['status' => 'processing', 'started_at' => now()]);
        Log::info("[SyncVclaimKunjunganJob] START — QL={$this->urlQL}, Tanggal={$this->tanggal}");

        try {
            $data = BpjsHelper::getVclaimDataKunjungan($this->urlQL, $this->tanggal, 2);

            if (empty($data['sep'])) {
                Log::info("[SyncVclaimKunjunganJob] Tidak ada data — QL={$this->urlQL}");
                $log->update([
                    'status'      => 'success',
                    'total_data'  => 0,
                    'message'     => 'Tidak ada data kunjungan rawat jalan dari API BPJS.',
                    'finished_at' => now(),
                ]);
                return;
            }

            $kodePPK  = env('BPJS_VCLAIM_KODEPPK_' . $this->urlQL);
            $syncedAt = Carbon::now();

            // ── BULK UPSERT: 1 query untuk semua records (vs N×2 query sebelumnya) ──
            $rows = collect($data['sep'])->map(fn ($sep) => [
                'kode_ql'             => $this->urlQL,
                'kode_ppk'            => $kodePPK,
                'tgl_kunjungan'       => $this->tanggal,
                'jns_pelayanan'       => 2,
                'no_sep'              => $sep['noSep']       ?? null,
                'no_kartu'            => $sep['noKartu']     ?? null,
                'no_rujukan'          => $sep['noRujukan']   ?? null,
                'nama'                => $sep['nama']        ?? null,
                'diagnosa'            => $sep['diagnosa']    ?? null,
                'poli'                => $sep['poli']        ?? null,
                'kelas_rawat'         => $sep['kelasRawat']  ?? null,
                'jns_pelayanan_label' => $sep['jnsPelayanan'] ?? 'R.Jalan',
                'tgl_sep'             => $sep['tglSep']      ?? null,
                'tgl_plg_sep'         => $sep['tglPlgSep']   ?? null,
                'raw_response'        => json_encode($sep),
                'sync_status'         => 'success',
                'sync_message'        => null,
                'synced_at'           => $syncedAt,
                'created_at'          => $syncedAt,
                'updated_at'          => $syncedAt,
            ])->toArray();

            VclaimKunjungan::upsert(
                $rows,
                ['kode_ql', 'no_sep'],   // unique key combination
                [                         // columns to update on duplicate
                    'kode_ppk', 'tgl_kunjungan', 'jns_pelayanan',
                    'no_kartu', 'no_rujukan', 'nama', 'diagnosa',
                    'poli', 'kelas_rawat', 'jns_pelayanan_label',
                    'tgl_sep', 'tgl_plg_sep', 'raw_response',
                    'sync_status', 'sync_message', 'synced_at', 'updated_at',
                ]
            );

            $count = count($rows);
            Log::info("[SyncVclaimKunjunganJob] DONE — QL={$this->urlQL}, {$count} records di-upsert.");

            $log->update([
                'status'      => 'success',
                'total_data'  => $count,
                'message'     => null,
                'finished_at' => now(),
            ]);

        } catch (\Throwable $e) {
            Log::error("[SyncVclaimKunjunganJob] ERROR — QL={$this->urlQL}: " . $e->getMessage());

            $log->update([
                'status'      => 'error',
                'message'     => $e->getMessage(),
                'finished_at' => now(),
            ]);

            throw $e; // Re-throw so queue marks it as failed and retries
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error("[SyncVclaimKunjunganJob] JOB FAILED (no more retries) — QL={$this->urlQL}: " . $e->getMessage());

        VclaimSyncLog::where('id', $this->syncLogId)->update([
            'status'      => 'error',
            'message'     => 'Job gagal setelah semua percobaan: ' . $e->getMessage(),
            'finished_at' => now(),
        ]);
    }
}
