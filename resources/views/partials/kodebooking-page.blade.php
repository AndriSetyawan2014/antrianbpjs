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

{{-- Tabel --}}
<div class="table-responsive">
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
</div>

@push('scripts')
<script>
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
