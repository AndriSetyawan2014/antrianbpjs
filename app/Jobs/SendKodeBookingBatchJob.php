<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\TambahAntrianOnlineController;

class SendKodeBookingBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $urlQL;
    protected $records;
    
    public $timeout = 600; // 10 minutes

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($urlQL, array $records)
    {
        $this->urlQL = $urlQL;
        $this->records = $records;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("[SendKodeBookingBatchJob - {$this->urlQL}] START: Processing batch of " . count($this->records) . " records.");
        
        $controller = new TambahAntrianOnlineController();
        
        foreach ($this->records as $record) {
            try {
                // Konversi array dari DB ke format yang dibutuhkan addAntrians_single_arr
                // Jika data dari DB sudah lengkap, bisa langsung dikirim
                $controller->addAntrians_single_arr($this->urlQL, (array)$record);
            } catch (\Exception $e) {
                Log::error("[SendKodeBookingBatchJob - {$this->urlQL}] Error processing record: " . ($record['kodebooking'] ?? 'unknown') . " -> " . $e->getMessage());
            }
        }
        
        Log::info("[SendKodeBookingBatchJob - {$this->urlQL}] FINISHED: Processed " . count($this->records) . " records.");
    }
}
