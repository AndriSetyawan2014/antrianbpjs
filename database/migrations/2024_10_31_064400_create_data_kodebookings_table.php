<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_kodebooking', function (Blueprint $table) {
            $table->id();
            $table->string('idpendaftaran', 20);
            $table->string('norm', 20);
            $table->string('kodebooking', 20);
            $table->string('carabayar', 50)->nullable();
            $table->integer('noantrian')->nullable();
            $table->integer('idjeniskunjungan')->nullable();
            $table->date('tanggalperiksa')->nullable();
            $table->boolean('ispasienlama')->nullable();
            $table->string('nojkn', 20)->nullable();
            $table->string('nik', 20)->nullable();
            $table->string('notelpon', 50)->nullable();
            $table->string('nomorreferensi', 30)->nullable();
            $table->integer('quota_jkn')->nullable();
            $table->integer('quota_jkn_sisa')->nullable();
            $table->integer('quota_nonjkn')->nullable();
            $table->integer('quota_nonjkn_sisa')->nullable();
            $table->bigInteger('estimasidilayani')->nullable();
            $table->string('bpjs_kodedokter', 10)->nullable();
            $table->string('namadokter', 100)->nullable();
            $table->string('kodeunit', 10)->nullable();
            $table->string('namaunit', 100)->nullable();
            $table->time('jammulai')->nullable();
            $table->time('jamakhir')->nullable();
            $table->string('code', 10)->nullable();
            $table->text('message')->nullable();
            $table->string('statuspemeriksaan', 100)->nullable();
            $table->text('request')->nullable(); // Kolom request
            $table->text('response')->nullable(); // Kolom response
            $table->tinyInteger('reupload')->default(1);
            $table->timestamps(0); // Menambahkan kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data_kodebooking');
    }
};
