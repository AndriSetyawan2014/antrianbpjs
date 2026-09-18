{{-- Partial DRY: halaman Rekap Task ID (QLJ / QLKP / QLTMG)
  Params: $pageTitle, $filterAction, $resetUrl, $detailRoute, $items
--}}
{{-- Judul + breadcrumb seragam (pola dashboard/settings) --}}
<div class="page-header">
    <h2 class="page-title"><i class="fas fa-clipboard-list me-2"></i>{{ $pageTitle }}</h2>
    <nav class="breadcrumb-nav" aria-label="breadcrumb">
        <span>Sistem Pemantauan Bridging BPJS</span>
        <i class="fas fa-chevron-right"></i>
        <span>{{ $pageTitle }}</span>
    </nav>
</div>

<div class="filter-card">
    <i class="fas fa-filter" style="color:var(--color-primary);"></i>
    <form method="GET" action="{{ $filterAction }}" style="display:contents;">
        <label for="start_date">Dari:</label>
        <input type="date" id="start_date" name="start_date" value="{{ request('start_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}">
        <label for="end_date">Sampai:</label>
        <input type="date" id="end_date" name="end_date" value="{{ request('end_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}">
        <button type="submit" class="btn-filter btn-primary">
            <i class="fas fa-search"></i> Filter
        </button>
        <a href="{{ $resetUrl }}" class="btn-filter btn-secondary">
            <i class="fas fa-undo"></i> Reset
        </a>
    </form>
    <div class="date-presets" role="group" aria-label="Preset tanggal cepat">
        <button type="button" class="btn-preset" data-preset="today">Hari ini</button>
        <button type="button" class="btn-preset" data-preset="yesterday">Kemarin</button>
        <button type="button" class="btn-preset" data-preset="week">7 hari</button>
        <button type="button" class="btn-preset" data-preset="month">Bulan ini</button>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover table-striped table-bordered">
        <thead>
            <tr>
                <th style="width:6%;">No</th>
                <th>Pesan / Message</th>
                <th style="width:10%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $startDate = request('start_date', date('Y-m-d'));
                $endDate = request('end_date', date('Y-m-d'));
                $counter = 1;
                $grandTotal = 0;
            @endphp
            @foreach($items as $row)
            @php $grandTotal += (int) $row->total; @endphp
            <tr>
                <td>{{ $counter++ }}</td>
                <td>
                    <a href="{{ route($detailRoute, ['message' => $row->message, 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                       style="color:var(--color-info); font-weight:500;">
                        <i class="fas fa-search me-1" style="font-size:11px;"></i>{{ $row->message }}
                    </a>
                </td>
                <td class="text-center">
                    <span class="status-badge badge-muted">{{ number_format($row->total) }}</span>
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
</div>
