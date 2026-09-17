<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "--- STARTING DATABASE OPTIMIZATION ---\n";

// 1. Kill ALL processes related to data_taskids to clear locks
$processes = DB::select('SHOW PROCESSLIST');
foreach ($processes as $p) {
    if ($p->Info && (strpos($p->Info, 'data_taskids') !== false || strpos($p->Info, 'qlkp_data_taskids') !== false || strpos($p->Info, 'qltmg_data_taskids') !== false)) {
        echo "Killing process ID: {$p->Id} -> " . substr($p->Info, 0, 50) . "...\n";
        try {
            DB::statement("KILL {$p->Id}");
        } catch (\Exception $e) {}
    }
}

// 2. Add temporary indexes to speed up cleanup
$tables = ['data_taskids', 'qlkp_data_taskids', 'qltmg_data_taskids'];

foreach ($tables as $table) {
    echo "Processing table: $table...\n";
    
    // Add index if not exists (to speed up DELETE)
    try {
        echo "Adding helper index to $table...\n";
        Schema::table($table, function (Blueprint $table) {
            $table->index(['kodebooking', 'taskid'], 'tmp_idx_cleanup');
        });
    } catch (\Exception $e) {
        echo "Index might already exist or table is locked: " . $e->getMessage() . "\n";
    }

    // Delete duplicates (now with index it should be fast)
    echo "Removing duplicates from $table...\n";
    $deleted = DB::delete("DELETE a FROM $table a JOIN $table b ON a.kodebooking = b.kodebooking AND a.taskid = b.taskid WHERE a.id > b.id");
    echo "Deleted $deleted duplicates from $table.\n";

    // Add unique index (final)
    try {
        echo "Adding UNIQUE constraint to $table...\n";
        Schema::table($table, function (Blueprint $table) {
            $table->dropIndex('tmp_idx_cleanup');
            $table->unique(['kodebooking', 'taskid']);
        });
        echo "SUCCESS: $table is now optimized and unique.\n";
    } catch (\Exception $e) {
        echo "Failed to add unique index (maybe still has duplicates?): " . $e->getMessage() . "\n";
    }
}

echo "--- OPTIMIZATION FINISHED ---\n";
