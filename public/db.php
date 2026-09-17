<?php
/**
 * Emergency DB Log Viewer
 * Akses: http://server-anda/public/db.php
 */
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "<html><head><title>DB Log Viewer</title><style>body { background: #1e1e1e; color: #00ff00; font-family: monospace; padding: 20px; } table { width: 100%; border-collapse: collapse; } th, td { border: 1px solid #444; padding: 8px; text-align: left; }</style></head><body>";
echo "<h2>🗄️ 20 Status Terakhir dari Tabel vclaim_sync_logs</h2>";

try {
    $logs = \Illuminate\Support\Facades\DB::table('vclaim_sync_logs')
        ->orderBy('id', 'desc')
        ->limit(20)
        ->get();

    if ($logs->isEmpty()) {
        echo "<p>Tabel kosong.</p>";
    } else {
        echo "<table>";
        echo "<tr><th>ID</th><th>QL</th><th>Tanggal</th><th>Status</th><th>Total Data</th><th>Message</th><th>Mulai</th><th>Selesai</th></tr>";
        foreach ($logs as $log) {
            echo "<tr>";
            echo "<td>{$log->id}</td>";
            echo "<td>{$log->kode_ql}</td>";
            echo "<td>{$log->tanggal}</td>";
            echo "<td>{$log->status}</td>";
            echo "<td>{$log->total_data}</td>";
            echo "<td>" . htmlspecialchars((string) $log->message) . "</td>";
            echo "<td>{$log->started_at}</td>";
            echo "<td>{$log->finished_at}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (\Exception $e) {
    echo "<p style='color:red;'>Error Database: " . $e->getMessage() . "</p>";
}

echo "<p style='margin-top: 30px; color: #aaa;'>Hapus file ini setelah selesai.</p>";
echo "</body></html>";
