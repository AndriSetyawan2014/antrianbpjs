<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('antrean_per_tanggal_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('kode_ql', 10)->index();
            $table->date('tanggal')->index();
            $table->string('status', 20)->default('pending');
            $table->integer('total_data')->default(0);
            $table->text('message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('antrean_per_tanggal_sync_logs');
    }
};
