{{-- Partial DRY: halaman Data Kode Booking (QLJ / QLKP / QLTMG)
  Params: $pageTitle, $filterAction, $exportUrl, $items, $startDate, $endDate, $tableId
--}}
{{-- Judul + breadcrumb seragam (pola dashboard/settings) --}}
<div class="page-header">
    <h2 class="page-title"><i class="fas fa-calendar-check me-2"></i>{{ $pageTitle }}</h2>
    <nav class="breadcrumb-nav" aria-label="breadcrumb">
        <span>Sistem Pemantauan Bridging BPJS</span>
        <i class="fas fa-chevron-right"></i>
        <span>{{ $pageTitle }}</span>
    </nav>
</div>

<style>
.filter-box {
    transition: transform 0.2s, box-shadow 0.2s;
    border-radius: 8px;
    cursor: pointer;
}
.filter-box:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15) !important;
}
.filter-box.active-filter {
    border: 3px solid #343a40;
    transform: scale(1.02);
}
.filter-box .inner h3 {
    font-size: 2.2rem;
    font-weight: 700;
}
.filter-box .icon i {
    font-size: 3.5rem;
    opacity: 0.7;
}
</style>

{{-- Filter Card (form GET agar tetap jalan tanpa JS) --}}
<div class="filter-card">
    <i class="fas fa-filter" style="color:var(--color-primary);"></i>
    <form method="GET" action="{{ $filterAction }}" style="display:contents;">
        <label for="start_date">Dari:</label>
        <input type="date" id="start_date" name="start_date" value="{{ $startDate }}" max="{{ date('Y-m-d') }}">
        <label for="end_date">Sampai:</label>
        <input type="date" id="end_date" name="end_date" value="{{ $endDate }}" max="{{ date('Y-m-d') }}">

        <button type="submit" class="btn-filter btn-primary">
            <i class="fas fa-search"></i> Filter
        </button>
        <a href="{{ $filterAction }}" class="btn-filter btn-secondary">
            <i class="fas fa-undo"></i> Reset
        </a>
    </form>
    <div class="date-presets" role="group" aria-label="Preset tanggal cepat">
        <button type="button" class="btn-preset" data-preset="today">Hari ini</button>
        <button type="button" class="btn-preset" data-preset="yesterday">Kemarin</button>
        <button type="button" class="btn-preset" data-preset="week">7 hari</button>
        <button type="button" class="btn-preset" data-preset="month">Bulan ini</button>
    </div>
    <a href="{{ $exportUrl }}" class="btn-filter btn-success ms-auto">
        <i class="fas fa-file-excel"></i> Download Excel
    </a>
</div>

@php
    $count200 = 0;
    $count0 = 0;
    $countError = 0;
    foreach($items as $item) {
        if ($item->code == 200) $count200++;
        else if ($item->code == 0) $count0++;
        else $countError++;
    }
    $totalCount = count($items);
@endphp

<div class="row mb-3 mt-4">
    <div class="col-lg-3 col-6 mb-3">
        <div class="small-box bg-info shadow-sm filter-box active-filter" onclick="filterTableCode(this, '')">
            <div class="inner">
                <h3>{{ $totalCount }}</h3>
                <p>Total Data</p>
            </div>
            <div class="icon">
                <i class="fas fa-list-alt"></i>
            </div>
            <div class="small-box-footer">
                Tampilkan Semua <i class="fas fa-arrow-circle-right"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6 mb-3">
        <div class="small-box bg-success shadow-sm filter-box" onclick="filterTableCode(this, '200')">
            <div class="inner">
                <h3>{{ $count200 }}</h3>
                <p>200 (OK)</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="small-box-footer">
                Filter 200 OK <i class="fas fa-filter"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6 mb-3">
        <div class="small-box bg-warning shadow-sm filter-box" onclick="filterTableCode(this, '0')">
            <div class="inner">
                <h3 class="text-dark">{{ $count0 }}</h3>
                <p class="text-dark">Pending (0)</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock text-dark"></i>
            </div>
            <div class="small-box-footer" style="color: #333 !important;">
                Filter Pending <i class="fas fa-filter"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6 mb-3">
        <div class="small-box bg-danger shadow-sm filter-box" onclick="filterTableCode(this, 'error')">
            <div class="inner">
                <h3>{{ $countError }}</h3>
                <p>Error</p>
            </div>
            <div class="icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="small-box-footer">
                Filter Error <i class="fas fa-filter"></i>
            </div>
        </div>
    </div>
</div>

{{-- Tabel --}}
    <table id="{{ $tableId }}" class="table table-hover table-striped table-bordered">
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
            @foreach($items as $item)
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
                    <button class="btn-json-view" data-json="{{ htmlspecialchars($item->request, ENT_QUOTES, 'UTF-8') }}" data-title="Request — No RM {{ $item->norm }}">
                        <i class="fas fa-eye"></i> Lihat
                    </button>
                </td>
                <td class="text-center">
                    <button class="btn-json-view" data-json="{{ htmlspecialchars($item->response, ENT_QUOTES, 'UTF-8') }}" data-title="Response — No RM {{ $item->norm }}">
                        <i class="fas fa-eye"></i> Lihat
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

@push('scripts')
<script>
function filterTableCode(element, type) {
    // Styling active box
    $('.filter-box').removeClass('active-filter');
    $(element).addClass('active-filter');

    // DataTables filter on column index 3 (Code column)
    var table = $('#{{ $tableId }}').DataTable();
    
    // Custom filter using DataTables search
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
    $('#{{ $tableId }}').DataTable({
        responsive: true,
        pageLength: 25,
        columnDefs: [
            { orderable: false, targets: [0, 5, 6] },
            { render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1, targets: 0 }
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' }
    });
});
</script>
@endpush
