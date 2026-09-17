@extends('layouts.admin')

@section('content')

@section('page_title')
    <i class="fas fa-tasks me-2"></i>Data Task ID
@endsection

{{-- Filter Card --}}
<div class="filter-card">
    <i class="fas fa-filter" style="color:var(--color-primary);"></i>
    <form id="filterForm" action="{{ route('taskid.filter') }}" method="GET"
          style="display:contents;">
        <label for="start_date">Dari:</label>
        <input type="date" id="start_date" name="start_date" value="{{ $startDate }}">
        <label for="end_date">Sampai:</label>
        <input type="date" id="end_date" name="end_date" value="{{ $endDate }}">
        <button type="submit" class="btn-filter btn-primary">
            <i class="fas fa-search"></i> Filter
        </button>
        <a href="{{ route('taskid.reset') }}" class="btn-filter btn-secondary">
            <i class="fas fa-undo"></i> Reset
        </a>
    </form>


    <button id="download_excel" class="btn-filter btn-success ms-2">
        <i class="fas fa-file-excel"></i> Download Excel
    </button>
</div>

{{-- Tabel --}}
<div class="table-responsive">
    <table id="data_taskid" class="table table-hover table-striped table-bordered">
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
        <tbody>
            @foreach($TaskID as $item)
            @php
                $code = $item->code;
                $badgeClass = $code == 200 ? 'badge-success' : ($code == 0 ? 'badge-warning' : 'badge-danger');
                $badgeLabel = $code == 200 ? '✓ 200' : ($code == 0 ? '⏳' : '✗ '.$code);
                $msg = json_decode($item->response)->metadata->message ?? '';
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
                <td>{{ $msg }}</td>
                <td>{{ $item->tanggal }}</td>
                <td>{{ $item->jam }}</td>
                <td class="text-center">
                    <button class="btn-json-view"
                            data-json="{{ htmlspecialchars($item->request) }}"
                            data-title="Request — {{ $item->taskid }}">
                        <i class="fas fa-eye"></i> Lihat
                    </button>
                </td>
                <td class="text-center">
                    <button class="btn-json-view"
                            data-json="{{ htmlspecialchars($item->response) }}"
                            data-title="Response — {{ $item->taskid }}">
                        <i class="fas fa-eye"></i> Lihat
                    </button>
                </td>
                <td class="text-center">{{ $item->reupload }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.1/xlsx.full.min.js"></script>
<script>
$(document).ready(function () {
    var table = $('#data_taskid').DataTable({
        paging: true,
        lengthChange: true,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        pageLength: 25,
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
        columnDefs: [{ orderable: false, targets: [9, 10] }]
    });

    // Custom filter tanggal
    function formatDateToISO(date) {
        const d = new Date(date);
        return isNaN(d.getTime()) ? null : d.toISOString().split('T')[0];
    }

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        var startDate = $('#start_date').val();
        var endDate   = $('#end_date').val();
        var tableDate = data[7] ? formatDateToISO(data[7].trim()) : null;
        if (!startDate && !endDate) return true;
        if (startDate && !endDate) return tableDate >= startDate;
        if (!startDate && endDate) return tableDate <= endDate;
        return tableDate >= startDate && tableDate <= endDate;
    });



    $('#download_excel').on('click', function () {
        var wb = XLSX.utils.table_to_book(document.getElementById('data_taskid'), { sheet: "Data TaskID BPJS" });
        XLSX.writeFile(wb, 'TaskID_BPJS.xlsx');
    });


});
</script>
@endpush