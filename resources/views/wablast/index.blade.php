@extends('layouts.admin')

@section('page_title')
    <h1 class="page-title"><i class="fab fa-whatsapp me-2 text-primary"></i>WhatsApp Blast</h1>
@endsection

@section('page_subtitle')
    Kirim notifikasi WhatsApp ke pasien
@endsection

@section('content')

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h5 class="card-title font-weight-bold"><i class="fas fa-comment-dots me-2 text-primary"></i> Form Kirim Pesan WA</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('wablast.send') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="phone" class="form-label fw-medium">Nomor Telepon</label>
                        <input type="text" class="form-control" id="phone" name="phone"
                               placeholder="Contoh: 08123456789" required>
                        <div class="form-text">Masukkan nomor telepon tanpa tanda +62 di depan.</div>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label fw-medium">Nama Penerima</label>
                        <input type="text" class="form-control" id="name" name="name"
                               placeholder="Nama lengkap pasien" required>
                    </div>

                    <div class="mb-3">
                        <label for="appointment_date" class="form-label fw-medium">Tanggal Janji Temui</label>
                        <input type="datetime-local" class="form-control" id="appointment_date" name="appointment_date" required>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i> Kirim Pesan
                        </button>
                        <a href="{{ url('/dashboard') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Kembali ke Dashboard
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection