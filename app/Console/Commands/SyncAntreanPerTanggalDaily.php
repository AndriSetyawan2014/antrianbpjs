<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AntreanPerTanggalSyncLog;
use App\Jobs\SyncAntreanPerTanggalJob;
use App\Helpers\BpjsHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SyncAntreanPerTanggalDaily extends Command
{
    protected $signature = 'vclaim:sync-antrean-daily';
    protected $description = 'Menjalankan sinkronisasi harian Antrean Per Tanggal dari BPJS VClaim';

    public function handle()
    {
        $this->info('Memulai sinkronisasi antrean per tanggal untuk hari ini (H-0)...');
        $tanggal = Carbon::today()->toDateString();
        $availableQLs = BpjsHelper::getUrlQLOptions();

        foreach ($availableQLs as $urlQL) {
            $log = AntreanPerTanggalSyncLog::create([
                'kode_ql' => $urlQL,
                'tanggal' => $tanggal,
                'status'  => 'pending',
            ]);

            SyncAntreanPerTanggalJob::dispatch($urlQL, $tanggal, $log->id);
            $this->info("Job dispatched untuk cabang {$urlQL} pada tanggal {$tanggal}.");
        }

        $this->info('Semua job sinkronisasi berhasil dikirim ke antrean.');
        Log::info('[SyncAntreanPerTanggalDaily] Daily sync dispatched.');
        return 0;
    }
}
