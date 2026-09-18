{{-- Partial DRY: halaman Rekap Kode Booking (QLJ / QLKP / QLTMG), mode ringkas + detail.
  Params: $pageTitle, $routeName, $summaryItems,
          $isDetail, $detailItems, $startDate, $endDate
--}}
{{-- Judul + breadcrumb seragam (pola dashboard/settings) --}}
<div class="page-header">
    <h2 class="page-title"><i class="fas fa-chart-bar me-2"></i>{{ $pageTitle }}</h2>
    <nav class="breadcrumb-nav" aria-label="breadcrumb">
        <span>Sistem Pemantauan Bridging BPJS</span>
        <i class="fas fa-chevron-right"></i>
        <span>{{ $pageTitle }}</span>
    </nav>
</div>

{{-- Filter Card --}}
<div class="filter-card">
    <i class="fas fa-filter" style="color:var(--color-primary);"></i>
    <form method="GET" action="{{ route($routeName) }}" style="display:contents;">
        <label for="start_date">Dari:</label>
        <input type="date" id="start_date" name="start_date" value="{{ request('start_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}">
        <label for="end_date">Sampai:</label>
        <input type="date" id="end_date" name="end_date" value="{{ request('end_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}">
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
<div class="table-responsive">
    @if($isDetail ?? false)
    {{-- Detail per message --}}
    <table class="table table-hover table-striped table-bordered">
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
            @foreach($detailItems as $index => $item)
            @php
                $code = $item->code;
                $badgeClass = $code == 200 ? 'badge-success' : ($code == 0 ? 'badge-warning' : 'badge-danger');
                $badgeLabel = $code == 200 ? '✓ 200 OK' : ($code == 0 ? '⏳ Pending' : '✗ Err '.$code);
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
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
</div>
