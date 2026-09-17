@extends('layouts.admin')

@section('content')
<div class="content">
    <div class="container-fluid">
        <!-- Page Title -->
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="page-title text-uppercase font-weight-bold" style="color: var(--secondary-dark);">
                    <i class="fas fa-cogs me-2"></i> Pengaturan Sistem
                </h4>
            </div>
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
                        
                        <form action="{{ route('settings.clear-cache') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membersihkan semua cache sistem?');">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-lg shadow-sm w-100">
                                <i class="fas fa-trash-alt me-2"></i> Bersihkan Semua Cache
                            </button>
                        </form>
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
