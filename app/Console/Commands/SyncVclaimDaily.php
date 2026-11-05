<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncVclaimDaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vclaim:sync-kunjungan-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Melakukan sinkronisasi data kunjungan rawat jalan VClaim harian ke DB lokal untuk seluruh cabang';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("Memulai proses sinkronisasi kunjungan rawat jalan VClaim harian...");
        
        $tanggal = \Carbon\Carbon::today()->toDateString();
        $availableQLs = \App\Helpers\BpjsHelper::getUrlQLOptions();

        $this->info("Tanggal: $tanggal");
        
        foreach ($availableQLs as $ql) {
            $this->info("Mendaftarkan job sinkronisasi untuk cabang: $ql");
            $log = \App\Models\VclaimSyncLog::create([
                'kode_ql' => $ql,
                'tanggal' => $tanggal,
                'status'  => 'pending',
            ]);
            \App\Jobs\SyncVclaimKunjunganJob::dispatch($ql, $tanggal, $log->id);
        }

        $this->info("Seluruh job sinkronisasi telah didaftarkan/dijalankan.");

        return Command::SUCCESS;
    }
}
