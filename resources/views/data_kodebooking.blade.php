@extends('layouts.admin')

@section('content')

@section('page_title')
    <i class="fas fa-calendar-check me-2"></i>Data Kode Booking
@endsection

{{-- Filter Card --}}
<div class="filter-card">
    <i class="fas fa-filter" style="color:var(--color-primary);"></i>
    <label for="start_date">Dari:</label>
    <input type="date" id="start_date" value="{{ $start_date }}">
    <label for="end_date">Sampai:</label>
    <input type="date" id="end_date" value="{{ $end_date }}">
    <button id="filter" class="btn-filter btn-primary">
        <i class="fas fa-search"></i> Filter
    </button>
    <button id="reset" class="btn-filter btn-secondary">
        <i class="fas fa-undo"></i> Reset
    </button>
    <a href="{{ url('export-kodebooking') }}" class="btn-filter btn-success ms-auto">
        <i class="fas fa-file-excel"></i> Download Excel
    </a>
</div>

{{-- Tabel --}}
<div class="table-responsive">
    <table id="data_kodebooking" class="table table-hover table-striped table-bordered">
        <thead>
            <tr>
                <th style="width:4%;">No</th>
                <th style="width:8%;">No RM</th>
                <th style="width:10%;">Tgl Periksa</th>
                <th style="width:6%;">Code</th>
                <th style="width:16%;">Message</th>
                <th style="width:28%;">Request</th>
                <th style="width:28%;">Response</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data_kodebooking as $item)
            @php
                $code = $item->code;
                $badgeClass = $code == 200 ? 'badge-success' : ($code == 0 ? 'badge-warning' : 'badge-danger');
                $badgeLabel = $code == 200 ? '✓ 200 OK' : ($code == 0 ? '⏳ Pending' : '✗ Err '.$code);
                $msg = json_decode($item->response)->metadata->message ?? '';
            @endphp
            <tr>
                <td></td>
                <td>{{ $item->norm }}</td>
                <td>{{ $item->tanggalperiksa }}</td>
                <td class="text-center">
                    <span class="status-badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                </td>
                <td>{{ $msg }}</td>
                <td class="text-center">
                    <button class="btn-json-view" data-json="{{ htmlspecialchars($item->request) }}" data-title="Request — No RM {{ $item->norm }}">
                        <i class="fas fa-eye"></i> Lihat
                    </button>
                </td>
                <td class="text-center">
                    <button class="btn-json-view" data-json="{{ htmlspecialchars($item->response) }}" data-title="Response — No RM {{ $item->norm }}">
                        <i class="fas fa-eye"></i> Lihat
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {
    var table = $('#data_kodebooking').DataTable({
        responsive: true,
        processing: true,
        serverSide: false,
        pageLength: 25,
        columnDefs: [{ orderable: false, targets: [5, 6] }],
        columns: [
            { data: null, render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1 },
            { data: 'norm' },
            { data: 'tanggalperiksa' },
            { data: 'code' },
            { data: 'message' },
            { data: 'request', orderable: false },
            { data: 'response', orderable: false }
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' }
    });

    $('#filter').click(function () {
        window.location.href = `?start_date=${$('#start_date').val()}&end_date=${$('#end_date').val()}`;
    });

    $('#reset').click(function () {
        var today = new Date().toISOString().split('T')[0];
        $('#start_date').val(today);
        $('#end_date').val(today);
        $('#filter').click();
    });
});
</script>
@endpush