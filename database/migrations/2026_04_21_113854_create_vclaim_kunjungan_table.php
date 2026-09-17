<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel vclaim_kunjungan menyimpan data kunjungan rawat jalan & rawat inap
     * dari semua cabang QL (QLJ, QLKP, QLTMG, dsb).
     *
     * Desain multi-branch:
     * - kode_ql       : identifier cabang (QLJ, QLKP, QLTMG, QLBR, dst)
     * - kode_ppk      : kode PPK/faskes dari env BPJS_VCLAIM_KODEPPK_{kode_ql}
     * - jns_pelayanan : 1=Rawat Inap, 2=Rawat Jalan
     * - tgl_kunjungan : tanggal parameter pencarian dari API
     */
    public function up(): void
    {
        Schema::create('vclaim_kunjungan', function (Blueprint $table) {
            $table->id();

            // ── Identitas Cabang ──────────────────────────────
            $table->string('kode_ql', 10)->comment('Kode cabang QL: QLJ, QLKP, QLTMG, dst');
            $table->string('kode_ppk', 20)->nullable()->comment('Kode PPK/faskes cabang');

            // ── Parameter Request ─────────────────────────────
            $table->date('tgl_kunjungan')->comment('Tanggal kunjungan parameter request API');
            $table->tinyInteger('jns_pelayanan')->default(2)->comment('1=Rawat Inap, 2=Rawat Jalan');

            // ── Data SEP dari BPJS VClaim ─────────────────────
            $table->string('no_sep', 30)->nullable()->comment('Nomor SEP');
            $table->string('no_kartu', 20)->nullable()->comment('Nomor kartu JKN peserta');
            $table->string('no_rujukan', 30)->nullable()->nullable();
            $table->string('nama', 100)->nullable()->comment('Nama peserta');
            $table->string('diagnosa', 20)->nullable()->comment('Kode ICD-10 diagnosa');
            $table->string('poli', 50)->nullable()->comment('Nama poli tujuan');
            $table->string('kelas_rawat', 5)->nullable()->comment('Kelas rawat: 1, 2, 3');
            $table->string('jns_pelayanan_label', 20)->nullable()->comment('Label: R.Jalan / R.Inap');
            $table->date('tgl_sep')->nullable()->comment('Tanggal SEP diterbitkan');
            $table->date('tgl_plg_sep')->nullable()->comment('Tanggal pulang SEP (rawat inap)');

            // ── Raw Response ──────────────────────────────────
            $table->json('raw_response')->nullable()->comment('Raw JSON satu record SEP dari BPJS');

            // ── Status Sync ───────────────────────────────────
            $table->string('sync_status', 20)->default('success')->comment('success / error / partial');
            $table->text('sync_message')->nullable();
            $table->timestamp('synced_at')->nullable()->comment('Waktu terakhir data di-sync dari API BPJS');

            $table->timestamps(); // created_at, updated_at

            // ── Index untuk query performa ────────────────────
            $table->index(['kode_ql', 'tgl_kunjungan', 'jns_pelayanan'], 'idx_ql_tgl_jns');
            $table->index(['no_sep'], 'idx_no_sep');
            $table->index(['no_kartu'], 'idx_no_kartu');
            $table->unique(['kode_ql', 'no_sep'], 'uq_ql_no_sep');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vclaim_kunjungan');
    }
};
