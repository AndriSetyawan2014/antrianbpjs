<?php
/**
 * Emergency Log Viewer
 * Akses: http://server-anda/public/log.php
 */
$logFile = __DIR__ . '/../storage/logs/laravel.log';

echo "<html><head><title>Laravel Log Viewer</title><style>body { background: #1e1e1e; color: #00ff00; font-family: monospace; padding: 20px; } pre { white-space: pre-wrap; word-wrap: break-word; }</style></head><body>";
echo "<h2>📜 100 Baris Terakhir dari storage/logs/laravel.log</h2>";

if (!file_exists($logFile)) {
    echo "<p style='color:red;'>File log tidak ditemukan di $logFile</p>";
} else {
    // Membaca 100 baris terakhir secara efisien
    $file = file($logFile);
    $lastLines = array_slice($file, -100);
    echo "<pre>";
    foreach ($lastLines as $line) {
        echo htmlspecialchars($line);
    }
    echo "</pre>";
}
echo "</body></html>";
