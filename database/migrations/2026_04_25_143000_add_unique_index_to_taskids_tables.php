<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = ['data_taskids', 'qlkp_data_taskids', 'qltmg_data_taskids'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                // 1. Bersihkan duplikasi agar tidak error saat tambah unique index
                // Kita simpan record terakhir (ID terbesar) untuk setiap kodebooking + taskid
                DB::statement("
                    DELETE t1 FROM $table t1
                    INNER JOIN $table t2 
                    WHERE t1.id < t2.id 
                    AND t1.kodebooking = t2.kodebooking 
                    AND t1.taskid = t2.taskid
                ");

                // 2. Tambah Unique Index
                Schema::table($table, function (Blueprint $table) {
                    $table->unique(['kodebooking', 'taskid'], 'uq_kodebooking_taskid');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['data_taskids', 'qlkp_data_taskids', 'qltmg_data_taskids'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropUnique('uq_kodebooking_taskid');
                });
            }
        }
    }
};
