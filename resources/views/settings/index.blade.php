@extends('layouts.admin')

@section('content')
<div class="content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="page-header">
            <h2 class="page-title"><i class="fas fa-cogs me-2"></i>Pengaturan Sistem</h2>
            <nav class="breadcrumb-nav">
                <span>Sistem Pemantauan Bridging BPJS</span>
                <i class="fas fa-chevron-right"></i>
                <span>Pengaturan</span>
            </nav>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {!! session('success') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <h5 class="card-title font-weight-bold"><i class="fas fa-sync-alt me-2 text-primary"></i> Sinkronisasi BPJS</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Gunakan fitur ini untuk mengirim ulang (re-upload) seluruh antrean dan Task ID BPJS (7 hari terakhir) yang berstatus error / tertunda.</p>
                        
                        <div class="d-grid gap-2" style="display: grid; gap: 0.5rem;">
                            <button type="button" class="btn btn-outline-primary btn-sync-taskid" data-urlql="QLJ">
                                <i class="fas fa-paper-plane me-2"></i> Kirim Ulang Task ID (Pusat / QLJ)
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sync-taskid" data-urlql="QLKP">
                                <i class="fas fa-paper-plane me-2"></i> Kirim Ulang Task ID (Kulon Progo)
                            </button>
                            <button type="button" class="btn btn-outline-info btn-sync-taskid" data-urlql="QLTMG">
                                <i class="fas fa-paper-plane me-2"></i> Kirim Ulang Task ID (Temanggung)
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <h5 class="card-title font-weight-bold"><i class="fas fa-broom me-2 text-warning"></i> Manajemen Cache</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Gunakan fitur ini jika Anda baru saja melakukan perubahan kode (terutama tampilan/View) namun tidak muncul di halaman aplikasi (tersangkut di memori cache server).</p>

                        <form id="formClearCache" action="{{ route('settings.clear-cache') }}" method="POST" class="mb-3">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-lg shadow-sm w-100">
                                <i class="fas fa-trash-alt me-2"></i> Bersihkan Semua Cache
                            </button>
                        </form>

                        <form id="formMigrate" action="{{ route('settings.migrate') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-lg shadow-sm w-100 text-dark">
                                <i class="fas fa-database me-2"></i> Jalankan Migrasi Database
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <h5 class="card-title font-weight-bold"><i class="fas fa-server me-2 text-primary"></i> Background Queue</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Kelola worker pengiriman Task ID / Kode Booking ke BPJS tanpa timeout dashboard.</p>
                        <div class="alert alert-info py-2" id="queueStatusBox" role="status">
                            <i class="fas fa-spinner fa-spin me-1"></i> Memeriksa status queue…
                        </div>
                        <div class="d-grid gap-2" style="display:grid;gap:.5rem;">
                            <button type="button" class="btn btn-outline-primary" id="btnQueueStart">
                                <i class="fas fa-play me-2"></i> Start Worker
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="btnQueueStop">
                                <i class="fas fa-stop me-2"></i> Stop Worker
                            </button>
                            <button type="button" class="btn btn-outline-danger" id="btnQueueClear">
                                <i class="fas fa-trash me-2"></i> Clear Queue
                            </button>
                            <button type="button" class="btn btn-outline-dark" id="btnQueueRefresh">
                                <i class="fas fa-sync-alt me-2"></i> Refresh Status
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <h5 class="card-title font-weight-bold"><i class="fas fa-hospital-user me-2 text-success"></i> VClaim Harian</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Sinkronisasi kunjungan Rawat Jalan hari ini untuk semua cabang, atau buka halaman monitoring.</p>
                        <div class="d-grid gap-2" style="display:grid;gap:.5rem;">
                            <a href="{{ route('vclaim.kunjungan.jalan') }}" class="btn btn-outline-success">
                                <i class="fas fa-list me-2"></i> Buka Monitoring Kunjungan
                            </a>
                            <a href="{{ route('vclaim.rekap.kunjungan.jalan') }}" class="btn btn-outline-primary">
                                <i class="fas fa-chart-bar me-2"></i> Buka Rekap Bulanan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Konfirmasi clear-cache via SweetAlert (konsisten, bukan confirm bawaan)
    $('#formClearCache').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: 'Bersihkan cache?',
            text: 'Seluruh cache sistem akan dibersihkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, bersihkan',
            cancelButtonText: 'Batal'
        }).then((r) => { if (r.isConfirmed) form.submit(); });
    // Konfirmasi migrate via SweetAlert
    $('#formMigrate').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: 'Jalankan Migrasi Database?',
            text: 'Ini akan membuat tabel baru atau mengubah struktur database (aman untuk tabel yang sudah ada).',
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Ya, jalankan',
            cancelButtonText: 'Batal'
        }).then((r) => { if (r.isConfirmed) {
            Swal.fire({ title: 'Memproses Migrasi…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            form.submit();
        }});
    });

    // ── Queue management (tanpa curl manual) ──
    function queueToast(msg, type) {
        if (window.showGlobalToast) window.showGlobalToast(msg, type);
    }
    function refreshQueueStatus() {
        const box = $('#queueStatusBox');
        box.html('<i class="fas fa-spinner fa-spin me-1"></i> Memeriksa status queue…');
        $.get('{{ url("api/queue-status") }}')
            .done(function(res) {
                const pending = res?.response?.pending ?? res?.pending ?? '?';
                box.removeClass('alert-info alert-success alert-warning')
                   .addClass(Number(pending) > 0 ? 'alert-warning' : 'alert-success')
                   .html(`<i class="fas fa-server me-1"></i> <strong>${pending}</strong> job menunggu di queue.`);
            })
            .fail(function() {
                box.removeClass('alert-info').addClass('alert-warning')
                   .html('<i class="fas fa-exclamation-triangle me-1"></i> Gagal memuat status queue.');
            });
    }
    function hitQueue(url, okMsg) {
        Swal.fire({ title: 'Memproses…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        $.get(url)
            .done(function(res) {
                const msg = res?.metadata?.message || okMsg;
                Swal.fire({ icon: 'success', title: 'Berhasil', text: msg }).then(refreshQueueStatus);
                queueToast(msg, 'success');
            })
            .fail(function() {
                Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal menghubungi endpoint queue.' });
                queueToast('Gagal menghubungi endpoint queue.', 'error');
            });
    }
    $('#btnQueueRefresh').on('click', refreshQueueStatus);
    $('#btnQueueStart').on('click', () => hitQueue('{{ url("api/queue-work-start") }}', 'Worker dijalankan.'));
    $('#btnQueueStop').on('click', () => hitQueue('{{ url("api/queue-work-stop") }}', 'Worker dihentikan.'));
    $('#btnQueueClear').on('click', function() {
        Swal.fire({
            title: 'Clear queue?',
            text: 'Seluruh job tertunda akan dihapus dan tidak dikirim ke BPJS.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then((r) => { if (r.isConfirmed) hitQueue('{{ url("api/queue-clear") }}', 'Queue dibersihkan.'); });
    });
    refreshQueueStatus();

    $('.btn-sync-taskid').on('click', function() {
        var urlQL = $(this).data('urlql');
        
        Swal.fire({
            title: 'Konfirmasi Sinkronisasi',
            text: "Anda yakin ingin memproses ulang seluruh data Task ID yang tertunda (H-7 hingga hari ini) untuk cabang ini?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Proses Sekarang!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                
                // Tampilkan Loading
                Swal.fire({
                    title: 'Memproses Data...',
                    html: 'Sistem sedang mensinkronisasi data dengan server BPJS.<br><br><b>Mohon jangan tutup halaman ini.</b>',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Jalankan AJAX
                $.ajax({
                    url: '{{ url("api/run-taskid-ql") }}?urlQL=' + urlQL,
                    type: 'GET',
                    success: function(res) {
                        let msg = res.metadata ? res.metadata.message : 'Berhasil diproses.';
                        Swal.fire({
                            icon: 'success',
                            title: 'Proses Selesai!',
                            text: msg,
                            confirmButtonText: 'OK'
                        });
                    },
                    error: function(err) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi Kesalahan',
                            text: 'Gagal menghubungi server atau terjadi error saat memproses data.',
                            confirmButtonText: 'Tutup'
                        });
                    }
                });
            }
        });
    });
});
</script>
@endpush
