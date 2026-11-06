{@--
  Partial DRY: halaman Rekap Kode Booking (QLJ / QLKP / QLTMG), mode ringkas + detail.
  Params: $pageTitle, $routeName, $summaryItems, $isDetail, $detailItems, $startDate, $endDate
--@}

@section('page_title')
    <h1 class="page-title"><i class="fas fa-chart-bar me-2 text-primary"></i>{{ $pageTitle }}</h1>
@endsection

@section('page_subtitle')
    Ringkasan dan detail data kode booking
@endsection

<style>
.filter-box {
    background: #fff;
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 15px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
}
.filter-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.filter-box.active-filter {
    border-color: var(--color-primary, #4e73df);
    box-shadow: 0 0 0 2px rgba(78, 115, 223, 0.25);
}
.filter-info {
    display: flex;
    flex-direction: column;
}
.filter-title {
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    color: #858796;
    margin-bottom: 4px;
}
.filter-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #5a5c69;
    line-height: 1;
}
.filter-icon {
    font-size: 2rem;
    opacity: 0.8;
}
.filter-primary .filter-title { color: #4e73df; }
.filter-primary .filter-icon { color: #4e73df; }
.filter-success .filter-title { color: #1cc88a; }
.filter-success .filter-icon { color: #1cc88a; }
.filter-warning .filter-title { color: #f6c23e; }
.filter-warning .filter-icon { color: #f6c23e; }
.filter-danger .filter-title { color: #e74a3b; }
.filter-danger .filter-icon { color: #e74a3b; }
</style>

{{-- Filter Card --}}
<div class="filter-card">
    <i class="fas fa-filter" style="color:var(--color-primary);"></i>
    <form method="GET" action="{{ route($routeName) }}" style="display:contents;">
        <label for="start_date">Dari:</label>
        <input type="date" id="start_date" name="start_date" value="{{ request('start_date', date('Y-m-d')) }}">
        <label for="end_date">Sampai:</label>
        <input type="date" id="end_date" name="end_date" value="{{ request('end_date', date('Y-m-d')) }}">
        <button type="submit" class="btn-filter btn-primary">
            <i class="fas fa-search"></i> Filter
        </button>
        <a href="{{ route($routeName) }}" class="btn-filter btn-secondary">
            <i class="fas fa-undo"></i> Reset
        </a>
    </form>
    <div class="date-presets" role="group" aria-label="Preset tanggal cepat">
        <button type="button" class="btn-preset" data-preset="today">Hari ini</button>
        <button type="button" class="btn-preset" data-preset="yesterday">Kemarin</button>
        <button type="button" class="btn-preset" data-preset="week">7 hari</button>
        <button type="button" class="btn-preset" data-preset="month">Bulan ini</button>
    </div>
    @if($isDetail ?? false)
    <a href="{{ route($routeName, ['start_date' => $startDate, 'end_date' => $endDate]) }}"
       class="btn-filter btn-secondary ms-auto">
        <i class="fas fa-arrow-left"></i> Kembali ke Rekap
    </a>
    @endif
</div>

{{-- Tabel --}}
@if($isDetail ?? false)
@php
    $count200 = 0;
    $count0 = 0;
    $countError = 0;
    foreach($detailItems as $item) {
        if ($item->code == 200) $count200++;
        else if ($item->code == 0) $count0++;
        else $countError++;
    }
    $totalCount = count($detailItems);
@endphp

<div class="row mb-3 mt-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="filter-box filter-primary active-filter" onclick="filterTableCode(this, '')">
            <div class="filter-info">
                <span class="filter-title">Total Data</span>
                <span class="filter-value">{{ $totalCount }}</span>
            </div>
            <div class="filter-icon">
                <i class="fas fa-list-alt"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="filter-box filter-success" onclick="filterTableCode(this, '200')">
            <div class="filter-info">
                <span class="filter-title">200 (OK)</span>
                <span class="filter-value">{{ $count200 }}</span>
            </div>
            <div class="filter-icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="filter-box filter-warning" onclick="filterTableCode(this, '0')">
            <div class="filter-info">
                <span class="filter-title">Pending</span>
                <span class="filter-value">{{ $count0 }}</span>
            </div>
            <div class="filter-icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="filter-box filter-danger" onclick="filterTableCode(this, 'error')">
            <div class="filter-info">
                <span class="filter-title">Error</span>
                <span class="filter-value">{{ $countError }}</span>
            </div>
            <div class="filter-icon">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
    </div>
</div>

{{-- Detail per message --}}
<table id="detailTable" class="table table-hover table-striped table-bordered">
    <thead>
        <tr>
            <th style="width:4%;">No</th>
            <th style="width:8%;">No RM</th>
            <th style="width:10%;">Tgl Periksa</th>
            <th style="width:7%;">Code</th>
            <th style="width:16%;">Message</th>
            <th style="width:27%;" class="text-center">Request</th>
            <th style="width:28%;" class="text-center">Response</th>
        </tr>
    </thead>
    <tbody>
        @foreach($detailItems as $index => $item)
        @php
            $code = $item->code;
            $badgeClass = $code == 200 ? 'badge-success' : ($code == 0 ? 'badge-warning' : 'badge-danger');
            $badgeLabel = $code == 200 ? '✓ 200 OK' : ($code == 0 ? '⏳ Pending' : '✗ Err '.$code);
        @endphp
        <tr>
            <td></td>
            <td>{{ $item->norm }}</td>
            <td>{{ $item->tanggalperiksa }}</td>
            <td class="text-center">
                <span class="status-badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
            </td>
            <td>{{ json_decode($item->response)->metadata->message ?? '' }}</td>
            <td class="text-center">
                <button class="btn-json-view"
                        data-json="{{ htmlspecialchars($item->request, ENT_QUOTES, 'UTF-8') }}"
                        data-title="Request — No RM {{ $item->norm }}">
                    <i class="fas fa-eye"></i> Lihat
                </button>
            </td>
            <td class="text-center">
                <button class="btn-json-view"
                        data-json="{{ htmlspecialchars($item->response, ENT_QUOTES, 'UTF-8') }}"
                        data-title="Response — No RM {{ $item->norm }}">
                    <i class="fas fa-eye"></i> Lihat
                </button>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@else
{{-- Rekap summary --}}
<table class="table table-hover table-striped table-bordered">
    <thead>
        <tr>
            <th style="width:6%;">No</th>
            <th>Pesan / Message</th>
            <th style="width:10%;">Total</th>
        </tr>
    </thead>
    <tbody>
        @php $grandTotal = 0; @endphp
        @foreach($summaryItems as $index => $item)
        @php $grandTotal += (int) $item->total; @endphp
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>
                <a href="{{ route($routeName, ['start_date' => $startDate, 'end_date' => $endDate, 'message' => $item->message_all]) }}"
                   style="color:var(--color-info); font-weight:500;">
                    <i class="fas fa-search me-1" style="font-size:11px;"></i>{{ $item->message_all }}
                </a>
            </td>
            <td class="text-center">
                <span class="status-badge badge-muted">{{ number_format($item->total) }}</span>
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th colspan="2" class="text-end">Total Keseluruhan</th>
            <th class="text-center">{{ number_format($grandTotal) }}</th>
        </tr>
    </tfoot>
</table>
@endif

@push('scripts')
<script>
function filterTableCode(element, type) {
    $('.filter-box').removeClass('active-filter');
    $(element).addClass('active-filter');

    var table = $('#detailTable').DataTable();

    if (type === '') {
        table.column(3).search('').draw();
    } else if (type === '200') {
        table.column(3).search('200 OK').draw();
    } else if (type === '0') {
        table.column(3).search('Pending').draw();
    } else if (type === 'error') {
        table.column(3).search('Err').draw();
    }
}

$(document).ready(function () {
    @if($isDetail ?? false)
    $('#detailTable').DataTable({
        responsive: true,
        pageLength: 25,
        columnDefs: [
            { orderable: false, targets: [0, 5, 6] },
            { render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1, targets: 0 }
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' }
    });
    @else
    // Init DataTable untuk rekap summary agar rapi
    $('.table').DataTable({
        responsive: true,
        paging: false,
        searching: true,
        info: false,
        order: [[2, 'desc']], // urutkan berdasar total
        columnDefs: [
            { orderable: false, targets: [0] }
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' }
    });
    @endif
});
</script>
@endpush