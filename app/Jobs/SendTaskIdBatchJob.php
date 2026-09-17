<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\PengirimanTaskIDController;
use App\Models\data_taskid;

class SendTaskIdBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $batchData;
    protected $urlQL;

    public $timeout = 600; // 10 minutes
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @param array $batchData Array of task ID records to process
     * @param string $urlQL The branch identifier (QLJ, etc)
     */
    public function __construct(array $batchData, $urlQL = 'QLJ')
    {
        $this->batchData = $batchData;
        $this->urlQL = $urlQL;
        $this->onQueue('sync_lokal');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $count = count($this->batchData);
        Log::info("[SendTaskIdBatchJob - {$this->urlQL}] START: Processing batch of $count records.");

        $controller = new PengirimanTaskIDController();
        $model = (new data_taskid())->setTableByQL($this->urlQL);

        foreach ($this->batchData as $data) {
            try {
                // Gunakan fungsi pengiriman tunggal yang sudah ada
                $controller->taskID_single_arr($this->urlQL, $data, $model);
            } catch (\Exception $e) {
                Log::error("[SendTaskIdBatchJob - {$this->urlQL}] Error processing {$data['kodebooking']}: " . $e->getMessage());
            }
        }

        Log::info("[SendTaskIdBatchJob - {$this->urlQL}] FINISHED: Processed $count records.");
    }
}
