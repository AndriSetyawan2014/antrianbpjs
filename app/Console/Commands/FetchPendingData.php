<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchPendingData extends Command
{
    // Nama dan deskripsi dari command
    protected $signature = 'fetch:pendingdata';
    protected $description = 'Fetch pending data and automatically add antrians and task IDs';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $branches = ['QLJ', 'QLKP', 'QLTMG'];
        $this->info("Starting TaskID Sync for all branches...");

        $controller = new \App\Http\Controllers\Api\PengirimanTaskIDController();

        foreach ($branches as $branch) {
            $this->info("Processing branch: {$branch}...");
            
            try {
                // Call taskID_otomatis directly
                // Passing null as second argument will process last 7 days
                $controller->taskID_otomatis($branch);
                $this->info("Branch {$branch} processed successfully.");
            } catch (\Exception $e) {
                $this->error("Error processing branch {$branch}: " . $e->getMessage());
            }
        }

        $this->info("All branches processed.");
        return 0;
    }
}

