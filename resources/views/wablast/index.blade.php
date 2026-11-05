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

                {{-- Flash message akan di-include otomatis dari layout --}}

                <form action="{{ route('wablast.send') }}" method="POST" id="formWablast">
                    @csrf

                    <x-form-group
                        label="Nomor Telepon"
                        name="phone"
                        type="text"
                        value="{{ old('phone') }}"
                        placeholder="Contoh: 08123456789"
                        required
                        icon="fas fa-phone"
                        help="Masukkan nomor telepon tanpa tanda +62 di depan."
                        error="{{ $errors->first('phone') }}"
                    />

                    <x-form-group
                        label="Nama Penerima"
                        name="name"
                        type="text"
                        value="{{ old('name') }}"
                        placeholder="Nama lengkap pasien"
                        required
                        icon="fas fa-user"
                        error="{{ $errors->first('name') }}"
                    />

                    <x-form-group
                        label="Tanggal Janji Temui"
                        name="appointment_date"
                        type="datetime-local"
                        value="{{ old('appointment_date') }}"
                        required
                        icon="fas fa-calendar"
                        error="{{ $errors->first('appointment_date') }}"
                    />

                    <div class="d-grid gap-2 mt-4">
                        <x-form-button
                            type="submit"
                            variant="primary"
                            size="lg"
                            icon="fas fa-paper-plane"
                            title="Kirim Pesan"
                        />
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

@push('scripts')
<script>
$(document).ready(function() {
    $('#formWablast').on('submit', function(e) {
        var btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true);
        btn.html('<span class="spinner-border spinner-border-sm me-2"></span> Mengirim…');
    });
});
</script>
@endpush