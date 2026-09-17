@extends('layouts.admin')

@section('content')

@section('page_title')
    <i class="fas fa-tasks me-2"></i>Data Task ID — Kulon Progo
@endsection

<div class="filter-card">
    <i class="fas fa-filter" style="color:var(--color-primary);"></i>
    <form id="filterForm" action="{{ route('qlkp_taskid.filter') }}" method="GET" style="display:inline-flex; align-items:center; gap:8px; margin:0;">
        <label>Dari:</label>
        <input type="date" name="start_date" id="start_date" value="{{ $startDate }}">
        <label>Sampai:</label>
        <input type="date" name="end_date" id="end_date" value="{{ $endDate }}">
        <button type="submit" id="filter" class="btn-filter btn-primary"><i class="fas fa-search"></i> Filter</button>
        <a href="{{ route('qlkp_taskid.reset') }}" id="reset" class="btn-filter btn-secondary" style="text-decoration:none; display:inline-flex; align-items:center;"><i class="fas fa-undo"></i> Reset</a>
    </form>


    <a href="{{ route('export_qlkp_taskid') }}" class="btn-filter btn-success ms-2">
        <i class="fas fa-file-excel"></i> Download Excel
    </a>
</div>

<div class="table-responsive">
    <table id="qlkp_table" class="table table-hover table-striped table-bordered">
        <thead>
            <tr>
                <th style="width:4%;">No</th>
                <th style="width:10%;">Kode Booking</th>
                <th style="width:10%;">Waktu</th>
                <th style="width:8%;">Task ID</th>
                <th style="width:8%;">ID Pendaftaran</th>
                <th style="width:7%;">Code</th>
                <th style="width:13%;">Message</th>
                <th style="width:8%;">Tanggal</th>
                <th style="width:6%;">Jam</th>
                <th style="width:8%;">Request</th>
                <th style="width:8%;">Response</th>
                <th style="width:6%;">Reupload</th>
            </tr>
        </thead>
        <tbody id="taskid-body">
            @foreach($qlkp_TaskID as $item)
            @php
                $code = $item->code;
                $badgeClass = $code == 200 ? 'badge-success' : ($code == 0 ? 'badge-warning' : 'badge-danger');
                $badgeLabel = $code == 200 ? '✓ 200' : ($code == 0 ? '⏳' : '✗ '.$code);
            @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->kodebooking }}</td>
                <td>{{ $item->waktu }}</td>
                <td><strong>{{ $item->taskid }}</strong></td>
                <td>{{ $item->idpendaftaran }}</td>
                <td class="text-center">
                    <span class="status-badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                </td>
                <td>{{ json_decode($item->response)->metadata->message ?? '' }}</td>
                <td>{{ $item->tanggal }}</td>
                <td>{{ $item->jam }}</td>
                <td class="text-center">
                    <button class="btn-json-view" data-json="{{ htmlspecialchars($item->request) }}" data-title="Request — {{ $item->taskid }}">
                        <i class="fas fa-eye"></i> Lihat
                    </button>
                </td>
                <td class="text-center">
                    <button class="btn-json-view" data-json="{{ htmlspecialchars($item->response) }}" data-title="Response — {{ $item->taskid }}">
                        <i class="fas fa-eye"></i> Lihat
                    </button>
                </td>
                <td>{{ $item->reupload }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {
    var table = $('#qlkp_table').DataTable({
        paging: true, searching: true, ordering: true, autoWidth: false, pageLength: 25,
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
        columnDefs: [{ orderable: false, targets: [9, 10] }]
    });

    function formatDateToISO(d) {
        const o = new Date(d); return isNaN(o.getTime()) ? null : o.toISOString().split('T')[0];
    }

    $.fn.dataTable.ext.search.push(function(settings, data) {
        var s = $('#start_date').val(), e = $('#end_date').val();
        var td = data[7] ? formatDateToISO(data[7].trim()) : null;
        if (!s && !e) return true;
        if (s && !e) return td >= s;
        if (!s && e) return td <= e;
        return td >= s && td <= e;
    });

    // Custom filter tanggal untuk JS Datatable tidak diperlukan lagi (menggunakan server-side filter)
    // $('#filter').on('click', () => table.draw());
    // $('#reset').on('click', () => { $('#start_date').val(''); $('#end_date').val(''); table.column(3).search('').draw(); });


});
</script>
@endpush
