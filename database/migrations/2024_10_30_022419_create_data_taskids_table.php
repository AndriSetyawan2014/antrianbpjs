<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_taskids', function (Blueprint $table) {
            $table->id();
            $table->string('kodebooking', 20);
            $table->bigInteger('waktu');
            $table->integer('taskid');
            $table->string('idpendaftaran', 20);
            $table->string('code', 10)->nullable();
            $table->text('message')->nullable();
            $table->date('tanggal')->nullable();
            $table->time('jam')->nullable();
            $table->text('request')->nullable();
            $table->text('response')->nullable();
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
        Schema::dropIfExists('data_taskids');
    }
};
