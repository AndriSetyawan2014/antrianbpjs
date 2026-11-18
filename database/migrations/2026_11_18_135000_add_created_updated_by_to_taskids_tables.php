<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['data_taskids', 'qlkp_data_taskids', 'qltmg_data_taskids'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'created_by')) {
                        $table->string('created_by', 60)->nullable()->default('Sync SIMRS')->after('reupload');
                    }
                    if (!Schema::hasColumn($tableName, 'updated_by')) {
                        $table->string('updated_by', 60)->nullable()->after('created_by');
                    }
                });
            }
        }
    }

    public function down(): void
    {
        $tables = ['data_taskids', 'qlkp_data_taskids', 'qltmg_data_taskids'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($tableName, 'updated_by')) {
                        $table->dropColumn('updated_by');
                    }
                    if (Schema::hasColumn($tableName, 'created_by')) {
                        $table->dropColumn('created_by');
                    }
                });
            }
        }
    }
};
