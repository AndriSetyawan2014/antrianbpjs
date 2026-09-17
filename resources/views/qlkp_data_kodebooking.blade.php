@extends('layouts.admin')

@section('content')

@section('page_title')
    <i class="fas fa-calendar-check me-2"></i>Data Kode Booking — Kulon Progo
@endsection

<div class="filter-card">
    <i class="fas fa-filter" style="color:var(--color-primary);"></i>
    <form id="filterForm" action="{{ route('qlkp_data_kodebooking') }}" method="GET" style="display:contents;">
        <label>Dari:</label>
        <input type="date" id="start_date" name="start_date" value="{{ $startDate }}">
        <label>Sampai:</label>
        <input type="date" id="end_date" name="end_date" value="{{ $endDate }}">
        <button type="submit" class="btn-filter btn-primary"><i class="fas fa-search"></i> Filter</button>
        <a href="{{ route('qlkp_data_kodebooking') }}" class="btn-filter btn-secondary"><i class="fas fa-undo"></i> Reset</a>
    </form>
    <a href="{{ route('export_qlkp_kodebooking') }}" class="btn-filter btn-success ms-auto">
        <i class="fas fa-file-excel"></i> Download Excel
    </a>
</div>

<div class="table-responsive">
    <table id="qlkp_data_kodebooking" class="table table-hover table-striped table-bordered">
        <thead>
            <tr>
                <th style="width:4%;">No</th>
                <th style="width:8%;">No RM</th>
                <th style="width:10%;">Tgl Periksa</th>
                <th style="width:7%;">Code</th>
                <th style="width:16%;">Message</th>
                <th style="width:27%;">Request</th>
                <th style="width:28%;">Response</th>
            </tr>
        </thead>
        <tbody>
            @foreach($qlkp_data_kodebooking as $item)
            @php
                $code = $item->code;
                $badgeClass = $code == 200 ? 'badge-success' : ($code == 0 ? 'badge-warning' : 'badge-danger');
                $badgeLabel = $code == 200 ? '✓ 200' : ($code == 0 ? '⏳' : '✗ '.$code);
                $msg = json_decode($item->response)->metadata->message ?? '';
            @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->norm }}</td>
                <td>{{ $item->tanggalperiksa }}</td>
                <td class="text-center">
                    <span class="status-badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                </td>
                <td>{{ $msg }}</td>
                <td class="text-center">
                    <button class="btn-json-view" data-json="{{ htmlspecialchars($item->request) }}" data-title="Request — {{ $item->norm }}">
                        <i class="fas fa-eye"></i> Lihat
                    </button>
                </td>
                <td class="text-center">
                    <button class="btn-json-view" data-json="{{ htmlspecialchars($item->response) }}" data-title="Response — {{ $item->norm }}">
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
    $('#qlkp_data_kodebooking').DataTable({
        responsive: true, pageLength: 25,
        columnDefs: [{ orderable: false, targets: [5, 6] }],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' }
    });
});
</script>
@endpush