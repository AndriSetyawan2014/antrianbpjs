<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return !empty($indexes);
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Optimasi tabel *_data_kodebooking
        $kbTables = ['data_kodebooking', 'qlkp_data_kodebooking', 'qltmg_data_kodebooking'];
        foreach ($kbTables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            // Bersihkan duplikasi kodebooking (simpan baris terbaik: prioritas code 200/208 atau message berisi, lalu ID terbesar)
            DB::statement("
                DELETE t1 FROM `{$table}` t1
                INNER JOIN `{$table}` t2
                ON t1.kodebooking = t2.kodebooking
                WHERE t1.id < t2.id
                AND (
                    (t2.code IN ('200', '208') OR (t2.message IS NOT NULL AND t2.message != ''))
                    OR (t1.code NOT IN ('200', '208') AND (t1.message IS NULL OR t1.message = ''))
                )
            ");
            // Pastikan sisa duplikasi dengan prioritas setara tetap menyisakan ID terbesar
            DB::statement("
                DELETE t1 FROM `{$table}` t1
                INNER JOIN `{$table}` t2
                ON t1.kodebooking = t2.kodebooking
                WHERE t1.id < t2.id
            ");

            if (!$this->indexExists($table, 'uq_kodebooking')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->unique('kodebooking', 'uq_kodebooking');
                });
            }
            if (!$this->indexExists($table, 'idx_tanggalperiksa_code')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->index(['tanggalperiksa', 'code'], 'idx_tanggalperiksa_code');
                });
            }
            if (!$this->indexExists($table, 'idx_reupload_tanggalperiksa')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->index(['reupload', 'tanggalperiksa'], 'idx_reupload_tanggalperiksa');
                });
            }
            if (!$this->indexExists($table, 'idx_norm_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->index(['norm', 'id'], 'idx_norm_id');
                });
            }
            if (!$this->indexExists($table, 'idx_created_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->index('created_at', 'idx_created_at');
                });
            }
        }

        // 2. Optimasi tabel *_data_taskids
        $taskTables = ['data_taskids', 'qlkp_data_taskids', 'qltmg_data_taskids'];
        foreach ($taskTables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            if (!$this->indexExists($table, 'idx_tanggal')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->index('tanggal', 'idx_tanggal');
                });
            }
            if (!$this->indexExists($table, 'idx_reupload_taskid')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->index(['reupload', 'taskid'], 'idx_reupload_taskid');
                });
            }
            if (!$this->indexExists($table, 'idx_taskid_reupload_kb')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->index(['taskid', 'reupload', 'kodebooking'], 'idx_taskid_reupload_kb');
                });
            }
            if (!$this->indexExists($table, 'idx_created_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->index('created_at', 'idx_created_at');
                });
            }
        }

        // 3. Optimasi tabel vclaim_kunjungan
        if (Schema::hasTable('vclaim_kunjungan') && !$this->indexExists('vclaim_kunjungan', 'idx_jns_tgl_ql')) {
            Schema::table('vclaim_kunjungan', function (Blueprint $t) {
                $t->index(['jns_pelayanan', 'tgl_kunjungan', 'kode_ql'], 'idx_jns_tgl_ql');
            });
        }

        // 4. Optimasi tabel antrean_per_tanggal_logs & antrean_per_tanggal_sync_logs
        if (Schema::hasTable('antrean_per_tanggal_logs') && !$this->indexExists('antrean_per_tanggal_logs', 'idx_kode_ql_tanggal')) {
            Schema::table('antrean_per_tanggal_logs', function (Blueprint $t) {
                $t->index(['kode_ql', 'tanggal'], 'idx_kode_ql_tanggal');
            });
        }
        if (Schema::hasTable('antrean_per_tanggal_sync_logs') && !$this->indexExists('antrean_per_tanggal_sync_logs', 'idx_ql_tanggal_status')) {
            Schema::table('antrean_per_tanggal_sync_logs', function (Blueprint $t) {
                $t->index(['kode_ql', 'tanggal', 'status'], 'idx_ql_tanggal_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $kbTables = ['data_kodebooking', 'qlkp_data_kodebooking', 'qltmg_data_kodebooking'];
        foreach ($kbTables as $table) {
            if (!Schema::hasTable($table)) continue;
            foreach (['uq_kodebooking', 'idx_tanggalperiksa_code', 'idx_reupload_tanggalperiksa', 'idx_norm_id', 'idx_created_at'] as $idx) {
                if ($this->indexExists($table, $idx)) {
                    Schema::table($table, function (Blueprint $t) use ($idx) {
                        $t->dropIndex($idx);
                    });
                }
            }
        }

        $taskTables = ['data_taskids', 'qlkp_data_taskids', 'qltmg_data_taskids'];
        foreach ($taskTables as $table) {
            if (!Schema::hasTable($table)) continue;
            foreach (['idx_tanggal', 'idx_reupload_taskid', 'idx_taskid_reupload_kb', 'idx_created_at'] as $idx) {
                if ($this->indexExists($table, $idx)) {
                    Schema::table($table, function (Blueprint $t) use ($idx) {
                        $t->dropIndex($idx);
                    });
                }
            }
        }

        if (Schema::hasTable('vclaim_kunjungan') && $this->indexExists('vclaim_kunjungan', 'idx_jns_tgl_ql')) {
            Schema::table('vclaim_kunjungan', function (Blueprint $t) {
                $t->dropIndex('idx_jns_tgl_ql');
            });
        }

        if (Schema::hasTable('antrean_per_tanggal_logs') && $this->indexExists('antrean_per_tanggal_logs', 'idx_kode_ql_tanggal')) {
            Schema::table('antrean_per_tanggal_logs', function (Blueprint $t) {
                $t->dropIndex('idx_kode_ql_tanggal');
            });
        }

        if (Schema::hasTable('antrean_per_tanggal_sync_logs') && $this->indexExists('antrean_per_tanggal_sync_logs', 'idx_ql_tanggal_status')) {
            Schema::table('antrean_per_tanggal_sync_logs', function (Blueprint $t) {
                $t->dropIndex('idx_ql_tanggal_status');
            });
        }
    }
};

