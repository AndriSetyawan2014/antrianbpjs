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
        // URL API yang diberikan
        $urls = [
            'http://localhost:80/api/data-pending-kodebooking',
            'http://localhost:80/api/add-antrians-otomatis',
            'http://localhost:80/api/data-pending-taskID',
            'http://localhost:80/api/task-id-otomatis',
        ];

        foreach ($urls as $url) {
            // Kirim HTTP GET request menggunakan Guzzle
            $response = Http::get($url);

            // Cek jika request sukses
            if ($response->successful()) {
                $this->info("Successfully fetched data from: {$url}");
            } else {
                $this->error("Failed to fetch data from: {$url}");
            }
        }

        return 0;
    }
}

