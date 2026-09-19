<?php

namespace App\Jobs;

use App\Helpers\BpjsHelper;
use App\Models\AntreanPerTanggalLog;
use App\Models\AntreanPerTanggalSyncLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncAntreanPerTanggalJob implements ShouldQueue
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
        $log = AntreanPerTanggalSyncLog::find($this->syncLogId);
        if (!$log) {
            Log::warning("[SyncAntreanPerTanggalJob] Log ID {$this->syncLogId} tidak ditemukan. Job dibatalkan.");
            return;
        }

        $log->update(['status' => 'processing', 'started_at' => now()]);
        Log::info("[SyncAntreanPerTanggalJob] START — QL={$this->urlQL}, Tanggal={$this->tanggal}");

        try {
            $jsonString = BpjsHelper::getRequestDirect($this->urlQL, "/antrean/pendaftaran/tanggal/{$this->tanggal}");
            $data = json_decode($jsonString, true);

            if (empty($data['response']) || !is_array($data['response'])) {
                Log::info("[SyncAntreanPerTanggalJob] Tidak ada data — QL={$this->urlQL}");
                $log->update([
                    'status'      => 'success',
                    'total_data'  => 0,
                    'message'     => 'Tidak ada data antrean dari API BPJS.',
                    'finished_at' => now(),
                ]);
                return;
            }

            $syncedAt = Carbon::now();

            $rows = collect($data['response'])->map(fn ($item) => [
                'kode_ql'             => $this->urlQL,
                'kodebooking'         => $item['kodebooking'] ?? '',
                'tanggal'             => $this->tanggal,
                'norekammedis'        => $item['norekammedis'] ?? null,
                'nik'                 => $item['nik'] ?? null,
                'nokapst'             => $item['nokapst'] ?? null,
                'kodepoli'            => $item['kodepoli'] ?? null,
                'kodedokter'          => $item['kodedokter'] ?? null,
                'jampraktek'          => $item['jampraktek'] ?? null,
                'jeniskunjungan'      => isset($item['jeniskunjungan']) ? (int) $item['jeniskunjungan'] : null,
                'nomorreferensi'      => $item['nomorreferensi'] ?? null,
                'sumberdata'          => $item['sumberdata'] ?? null,
                'ispeserta'           => !empty($item['ispeserta']),
                'noantrean'           => $item['noantrean'] ?? null,
                'estimasidilayani'    => $item['estimasidilayani'] ?? null,
                'createdtime'         => $item['createdtime'] ?? null,
                'status'              => $item['status'] ?? null,
                'raw_response'        => json_encode($item),
                'synced_at'           => $syncedAt,
                'created_at'          => $syncedAt,
                'updated_at'          => $syncedAt,
            ])->toArray();

            AntreanPerTanggalLog::upsert(
                $rows,
                ['kode_ql', 'kodebooking'],   // unique key combination
                [                             // columns to update on duplicate
                    'tanggal', 'norekammedis', 'nik', 'nokapst', 'kodepoli', 'kodedokter', 'jampraktek',
                    'jeniskunjungan', 'nomorreferensi', 'sumberdata', 'ispeserta', 'noantrean',
                    'estimasidilayani', 'createdtime', 'status', 'raw_response', 'synced_at', 'updated_at',
                ]
            );

            $count = count($rows);
            Log::info("[SyncAntreanPerTanggalJob] DONE — QL={$this->urlQL}, {$count} records di-upsert.");

            $log->update([
                'status'      => 'success',
                'total_data'  => $count,
                'message'     => null,
                'finished_at' => now(),
            ]);

        } catch (\Throwable $e) {
            Log::error("[SyncAntreanPerTanggalJob] ERROR — QL={$this->urlQL}: " . $e->getMessage());

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
        Log::error("[SyncAntreanPerTanggalJob] JOB FAILED (no more retries) — QL={$this->urlQL}: " . $e->getMessage());

        AntreanPerTanggalSyncLog::where('id', $this->syncLogId)->update([
            'status'      => 'error',
            'message'     => 'Job gagal setelah semua percobaan: ' . $e->getMessage(),
            'finished_at' => now(),
        ]);
    }
}
