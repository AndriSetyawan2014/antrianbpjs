<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('qltmg_data_taskids', function (Blueprint $table) {
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
            $table->timestamps(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('qltmg_data_taskids');
    }
};
