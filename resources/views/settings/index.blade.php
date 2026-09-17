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
