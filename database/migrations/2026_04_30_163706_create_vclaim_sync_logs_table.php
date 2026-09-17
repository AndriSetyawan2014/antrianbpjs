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
        Schema::create('vclaim_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('kode_ql', 10)->nullable();
            $table->date('tanggal');
            $table->enum('status', ['pending', 'processing', 'success', 'error'])->default('pending');
            $table->unsignedInteger('total_data')->default(0);
            $table->text('message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['kode_ql', 'tanggal', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vclaim_sync_logs');
    }
};
