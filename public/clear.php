<?php
/**
 * Script untuk menjalankan artisan command tanpa SSH
 * Letakkan file ini di dalam folder "public"
 * Akses melalui browser: http://server-anda/public/clear.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// Inisialisasi kernel console
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$output = "";
$message = "";

if (isset($_POST['clear_cache'])) {
    try {
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        $output = \Illuminate\Support\Facades\Artisan::output();
        $message = "<div style='color: green; margin-bottom: 20px;'><strong>Berhasil!</strong> Cache Laravel telah dibersihkan.</div>";
    } catch (\Exception $e) {
        $message = "<div style='color: red; margin-bottom: 20px;'><strong>Gagal:</strong> " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Clear Cache Server</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; background: #f4f7f6; }
        .container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 600px; margin: 0 auto; }
        h2 { margin-top: 0; color: #333; }
        .btn { background: #e74c3c; color: white; border: none; padding: 12px 24px; font-size: 16px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn:hover { background: #c0392b; }
        pre { background: #2d3436; color: #dfe6e9; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 14px; }
        .warning { font-size: 13px; color: #7f8c8d; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🛠️ Manajemen Cache Server</h2>
        <p>Gunakan alat ini untuk membersihkan cache sistem (Views, Config, Routes, Cache) jika perubahan kode Anda belum tampil di server live.</p>
        
        <?= $message ?>

        <form method="POST">
            <button type="submit" name="clear_cache" class="btn">🗑️ Bersihkan Semua Cache Sekarang</button>
        </form>

        <?php if ($output): ?>
            <h4 style="margin-top: 30px;">Log Output:</h4>
            <pre><?= htmlspecialchars($output) ?></pre>
        <?php endif; ?>

        <p class="warning">Disarankan untuk menghapus file <code>clear.php</code> ini dari folder public jika tidak lagi digunakan demi alasan keamanan.</p>
    </div>
</body>
</html>
