<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('antrean_per_tanggal_logs', function (Blueprint $table) {
            $table->id();
            $table->string('kode_ql', 10)->index();
            $table->string('kodebooking', 50);
            $table->date('tanggal')->index();
            $table->string('norekammedis', 20)->nullable();
            $table->string('nik', 20)->nullable();
            $table->string('nokapst', 20)->nullable();
            $table->string('kodepoli', 10)->nullable();
            $table->string('kodedokter', 20)->nullable();
            $table->string('jampraktek', 20)->nullable();
            $table->integer('jeniskunjungan')->nullable();
            $table->string('nomorreferensi', 50)->nullable();
            $table->string('sumberdata', 50)->nullable();
            $table->boolean('ispeserta')->default(false);
            $table->string('noantrean', 20)->nullable();
            $table->string('estimasidilayani', 50)->nullable();
            $table->string('createdtime', 50)->nullable();
            $table->string('status', 50)->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            // Composite unique index
            $table->unique(['kode_ql', 'kodebooking']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('antrean_per_tanggal_logs');
    }
};
