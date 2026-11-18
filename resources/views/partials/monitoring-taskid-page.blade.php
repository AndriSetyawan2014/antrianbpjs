{{-- Partial: halaman Monitoring Task ID (Optimized Server-Side Pagination) --}}
{{-- Page header dihandle oleh layout via @section('page_title') --}}

@php
    $kelengkapanFilter = $kelengkapanFilter ?? request('kelengkapan', '');
    $searchQuery = $searchQuery ?? request('q', '');
    $perPage = $perPage ?? (int) request('per_page', 50);

    $formatDuration = function ($seconds) {
        if ($seconds === null) return '-';
        $sec = (int) round($seconds);
        $neg = $sec < 0;
        $sec = abs($sec);
        $hrs = intdiv($sec, 3600);
        $mins = intdiv($sec % 3600, 60);
        $remSec = $sec % 60;
        $prefix = $neg ? '-' : '';
        if ($hrs > 0) {
            return sprintf('%s%d jam %d mnt %d dtk', $prefix, $hrs, $mins, $remSec);
        }
        return sprintf('%s%d mnt %d dtk', $prefix, $mins, $remSec);
    };

    $countTotalAll = $evalSummary['countTotalAll'] ?? 0;
    $countLengkap = $evalSummary['countLengkap'] ?? 0;
    $countLengkap15 = $evalSummary['countLengkap15'] ?? 0;
    $countLengkap17 = $evalSummary['countLengkap17'] ?? 0;
    $countLengkap99 = $evalSummary['countLengkap99'] ?? 0;
    $countTidakLengkap = $evalSummary['countTidakLengkap'] ?? 0;

    $avg2to4 = $evalSummary['avg2to4'] ?? null;
    $avg3to4 = $evalSummary['avg3to4'] ?? null;
    $avgCombined = $evalSummary['avgCombined'] ?? null;
    $count2to4 = $evalSummary['count2to4'] ?? 0;
    $count3to4 = $evalSummary['count3to4'] ?? 0;
    $countBoth = $evalSummary['countBoth'] ?? 0;

    $isPaginated = $kodebookings instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;
    $firstItemNumber = $isPaginated ? ($kodebookings->firstItem() ?? 1) : 1;
@endphp

{{-- Compact Filter & Toolbar Bar --}}
<div class="filter-card py-2 px-3 mb-2" style="gap: 6px; align-items: center;">
    <div class="btn-group btn-group-sm me-1" role="group" aria-label="Pilih Cabang QL">
        <a href="{{ url('/monitoring_taskid') }}?start_date={{ $startDate }}&end_date={{ $endDate }}&kelengkapan={{ $kelengkapanFilter }}&per_page={{ $perPage }}"
           class="btn {{ ($urlQL ?? 'QLJ') === 'QLJ' ? 'btn-primary' : 'btn-outline-primary' }}" style="font-size: 0.74rem; padding: 3px 9px;">
            QLJ
        </a>
        <a href="{{ url('/qlkp_monitoring_taskid') }}?start_date={{ $startDate }}&end_date={{ $endDate }}&kelengkapan={{ $kelengkapanFilter }}&per_page={{ $perPage }}"
           class="btn {{ ($urlQL ?? 'QLJ') === 'QLKP' ? 'btn-primary' : 'btn-outline-primary' }}" style="font-size: 0.74rem; padding: 3px 9px;">
            QLKP
        </a>
        <a href="{{ url('/qltmg_monitoring_taskid') }}?start_date={{ $startDate }}&end_date={{ $endDate }}&kelengkapan={{ $kelengkapanFilter }}&per_page={{ $perPage }}"
           class="btn {{ ($urlQL ?? 'QLJ') === 'QLTMG' ? 'btn-primary' : 'btn-outline-primary' }}" style="font-size: 0.74rem; padding: 3px 9px;">
            QLTMG
        </a>
    </div>

    <form method="GET" action="{{ $filterAction }}" style="display:contents;">
        <input type="date" id="start_date" name="start_date" value="{{ $startDate }}" style="width: 132px; height: 32px; font-size: 0.76rem; padding: 4px 8px;" title="Dari tanggal">
        <span class="text-muted small">–</span>
        <input type="date" id="end_date" name="end_date" value="{{ $endDate }}" style="width: 132px; height: 32px; font-size: 0.76rem; padding: 4px 8px;" title="Sampai tanggal">

        <select id="kelengkapan" name="kelengkapan" style="width: 195px; height: 32px; font-size: 0.76rem; padding: 4px 8px;" onchange="this.form.submit()">
            <option value="" {{ $kelengkapanFilter === '' ? 'selected' : '' }}>Semua ({{ number_format($countTotalAll) }})</option>
            <option value="lengkap" {{ $kelengkapanFilter === 'lengkap' ? 'selected' : '' }}>Lengkap ({{ number_format($countLengkap) }})</option>
            <option value="tidak_lengkap" {{ $kelengkapanFilter === 'tidak_lengkap' ? 'selected' : '' }}>Tidak Lengkap ({{ number_format($countTidakLengkap) }})</option>
            <option value="t1_5" {{ $kelengkapanFilter === 't1_5' ? 'selected' : '' }}>↳ T1–5 ({{ number_format($countLengkap15) }})</option>
            <option value="t1_7" {{ $kelengkapanFilter === 't1_7' ? 'selected' : '' }}>↳ T1–7 ({{ number_format($countLengkap17) }})</option>
            <option value="batal_99" {{ $kelengkapanFilter === 'batal_99' ? 'selected' : '' }}>↳ Batal 99 ({{ number_format($countLengkap99) }})</option>
        </select>

        <input type="text" name="q" value="{{ $searchQuery }}" placeholder="Cari Booking / RM / Dokter..." style="width: 185px; height: 32px; font-size: 0.76rem; padding: 4px 10px;">

        <select name="per_page" style="width: 90px; height: 32px; font-size: 0.76rem; padding: 4px 6px;" onchange="this.form.submit()" title="Baris per halaman">
            @foreach([25, 50, 100, 250] as $pp)
                <option value="{{ $pp }}" {{ (int)$perPage === $pp ? 'selected' : '' }}>{{ $pp }}/hal</option>
            @endforeach
        </select>

        <button type="submit" class="btn-filter btn-primary" style="height: 32px; padding: 0 12px; font-size: 0.76rem;">
            <i class="fas fa-search"></i> Filter
        </button>
        <a href="{{ $filterAction }}" class="btn-filter btn-secondary" style="height: 32px; padding: 0 10px; font-size: 0.76rem;" title="Reset">
            <i class="fas fa-undo"></i>
        </a>
    </form>

    <div class="date-presets ms-auto" role="group" aria-label="Preset tanggal cepat" style="gap: 4px;">
        <button type="button" class="btn-preset" data-preset="yesterday" style="height: 28px; padding: 0 10px; font-size: 0.72rem;">Kemarin</button>
        <button type="button" class="btn-preset" data-preset="week" style="height: 28px; padding: 0 10px; font-size: 0.72rem;">7 Hari</button>
        <button type="button" class="btn-preset" data-preset="month" style="height: 28px; padding: 0 10px; font-size: 0.72rem;">Bulan Ini</button>
    </div>
</div>

{{-- Compact Evaluasi & Kelengkapan Bar --}}
<div class="row g-2 mb-2 align-items-stretch">
    <div class="col-lg-3 col-md-6">
        <div class="card h-100 border-0 shadow-sm" style="border-left: 3px solid #0f766e !important;">
            <div class="card-body py-2 px-3 d-flex flex-column justify-content-center">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-uppercase fw-bold text-muted" style="font-size: 0.66rem;">1. Rata-rata T2 ➔ T4</span>
                    <span class="badge bg-light text-dark border" style="font-size: 0.65rem;">{{ number_format($count2to4) }} data</span>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-1">
                    <span class="fw-bold" style="font-size: 1.05rem; color: #0f766e;">
                        {{ $avg2to4 !== null ? number_format($avg2to4 / 60, 1) . ' mnt' : '-' }}
                    </span>
                    <small class="text-muted" style="font-size: 0.72rem;">({{ $formatDuration($avg2to4) }})</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card h-100 border-0 shadow-sm" style="border-left: 3px solid #0284c7 !important;">
            <div class="card-body py-2 px-3 d-flex flex-column justify-content-center">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-uppercase fw-bold text-muted" style="font-size: 0.66rem;">2. Rata-rata T3 ➔ T4</span>
                    <span class="badge bg-light text-dark border" style="font-size: 0.65rem;">{{ number_format($count3to4) }} data</span>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-1">
                    <span class="fw-bold" style="font-size: 1.05rem; color: #0284c7;">
                        {{ $avg3to4 !== null ? number_format($avg3to4 / 60, 1) . ' mnt' : '-' }}
                    </span>
                    <small class="text-muted" style="font-size: 0.72rem;">({{ $formatDuration($avg3to4) }})</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card h-100 border-0 shadow-sm" style="border-left: 3px solid #d97706 !important;">
            <div class="card-body py-2 px-3 d-flex flex-column justify-content-center">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-uppercase fw-bold text-muted" style="font-size: 0.66rem;">3. Gabungan T2➔T4 &amp; T3➔T4</span>
                    <span class="badge bg-light text-dark border" style="font-size: 0.65rem;">{{ number_format($countBoth) }} data</span>
                </div>
                <div class="d-flex align-items-baseline gap-2 mt-1">
                    <span class="fw-bold" style="font-size: 1.05rem; color: #d97706;">
                        {{ $avgCombined !== null ? number_format($avgCombined / 60, 1) . ' mnt' : '-' }}
                    </span>
                    <small class="text-muted" style="font-size: 0.7rem;">
                        T2➔T3: {{ ($avg2to4 !== null && $avg3to4 !== null && $avg2to4 >= $avg3to4) ? number_format(($avg2to4 - $avg3to4) / 60, 1) . 'm' : '-' }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card h-100 border-0 shadow-sm" style="border-left: 3px solid #16a34a !important;">
            <div class="card-body py-2 px-3 d-flex flex-column justify-content-center">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-uppercase fw-bold text-muted" style="font-size: 0.66rem;">Kelengkapan Antrian</span>
                    <span class="text-muted" style="font-size: 0.66rem;">Total: <strong>{{ number_format($countTotalAll) }}</strong></span>
                </div>
                <div class="d-flex gap-1 flex-wrap">
                    <a href="{{ $filterAction }}?start_date={{ $startDate }}&end_date={{ $endDate }}&kelengkapan=lengkap&q={{ urlencode($searchQuery) }}&per_page={{ $perPage }}"
                       class="badge text-decoration-none {{ $kelengkapanFilter === 'lengkap' ? 'bg-success text-white' : 'bg-success-subtle text-success border border-success' }}"
                       style="font-size: 0.68rem;"
                       title="T1-5: {{ $countLengkap15 }} | T1-7: {{ $countLengkap17 }} | Batal 99: {{ $countLengkap99 }}">
                        ✓ Lengkap: {{ number_format($countLengkap) }}
                    </a>
                    <a href="{{ $filterAction }}?start_date={{ $startDate }}&end_date={{ $endDate }}&kelengkapan=tidak_lengkap&q={{ urlencode($searchQuery) }}&per_page={{ $perPage }}"
                       class="badge text-decoration-none {{ $kelengkapanFilter === 'tidak_lengkap' ? 'bg-danger text-white' : 'bg-danger-subtle text-danger border border-danger' }}"
                       style="font-size: 0.68rem;">
                        ✗ Tidak: {{ number_format($countTidakLengkap) }}
                    </a>
                    @if($kelengkapanFilter !== '')
                        <a href="{{ $filterAction }}?start_date={{ $startDate }}&end_date={{ $endDate }}&q={{ urlencode($searchQuery) }}&per_page={{ $perPage }}"
                           class="badge bg-secondary text-white text-decoration-none" style="font-size: 0.65rem;">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tabel (Hanya merender baris per halaman agar ringan & bebas crash browser) --}}
<div class="table-responsive">
    <table id="{{ $tableId }}" class="table table-hover table-striped table-bordered text-center mb-0">
        <thead>
            <tr>
                <th style="width:4%;">No</th>
                <th style="width:16%; text-align: left;">Informasi Antrian</th>
                <th style="width:7%;">T1<br><small>Daftar</small></th>
                <th style="width:7%;">T2<br><small>Poli</small></th>
                <th style="width:7%;">T3<br><small>Layan</small></th>
                <th style="width:7%;">T4<br><small>Selesai Layan</small></th>
                <th style="width:7%;">T5<br><small>Ambil Obat</small></th>
                <th style="width:7%;">T6<br><small>Selesai Obat</small></th>
                <th style="width:7%;">T7<br><small>Selesai</small></th>
                <th style="width:7%;">T99<br><small>Batal</small></th>
                <th style="width:12%;">Evaluasi Waktu<br><small>T2➔T4 &amp; T3➔T4</small></th>
                <th style="width:5%;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @php
                $tasks = [1, 2, 3, 4, 5, 6, 7, 99];
            @endphp
            @forelse($kodebookings as $index => $kb)
                @php
                    $tgl = date('Y-m-d', strtotime($kb->tanggalperiksa));
                    $noSep = null;
                    if (!empty($kb->nojkn) && isset($vclaimMap[$kb->nojkn])) {
                        $noSep = $vclaimMap[$kb->nojkn][$tgl] ?? null;
                        if (!$noSep) {
                            $noSep = reset($vclaimMap[$kb->nojkn]);
                        }
                    }
                    $evalRow = $rowMetaMap[$kb->kodebooking] ?? [
                        'is_lengkap' => false,
                        'status_label' => 'Antrian Tidak Lengkap',
                        'd24' => null,
                        'd34' => null,
                    ];
                @endphp
                <tr>
                    <td>{{ $firstItemNumber + $index }}</td>
                    <td style="text-align: left;">
                        <strong>{{ $kb->kodebooking }}</strong>
                        <span class="text-muted small ms-1">({{ $tgl }})</span>
                        <div class="mt-1 mb-1">
                            @if($evalRow['is_lengkap'])
                                <span class="badge bg-success" style="font-size: 0.68rem;">
                                    <i class="fas fa-check-circle me-1"></i>{{ $evalRow['status_label'] }}
                                </span>
                            @else
                                <span class="badge bg-danger" style="font-size: 0.68rem;">
                                    <i class="fas fa-exclamation-circle me-1"></i>{{ $evalRow['status_label'] }}
                                </span>
                            @endif
                        </div>
                        <small>RM: {{ $kb->norm }} | JKN: {{ $kb->nojkn ?? '-' }}</small>
                        @if($noSep)
                            <br><small class="text-primary"><i class="fas fa-file-medical"></i> SEP: {{ $noSep }}</small>
                        @endif
                        <br>
                        <small>{{ $kb->namadokter }}</small><br>
                        <small class="text-muted">{{ $kb->namaunit }}</small>
                    </td>
                    @foreach($tasks as $t)
                        @php
                            $taskData = $groupedTasks[$kb->kodebooking][$t] ?? null;
                        @endphp
                        <td>
                            @if($taskData)
                                @php
                                    $msg = strtolower($taskData->message ?? '');
                                    if (str_contains($msg, 'success') || str_contains($msg, 'ok')) {
                                        $color = '#28a745';
                                        $title = 'Success: ' . $taskData->message;
                                    } elseif (str_contains($msg, 'antrean') || $msg == '') {
                                        $color = '#fd7e14';
                                        $title = 'Pending/Queue';
                                    } else {
                                        $color = '#dc3545';
                                        $title = 'Error: ' . $taskData->message;
                                    }
                                @endphp
                                <span class="badge" style="background-color: {{ $color }};" title="{{ $title }}">
                                    {{ $taskData->jam ?? substr($taskData->waktu, 11, 5) }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    @endforeach
                    <td style="text-align: left; font-size: 0.75rem; white-space: nowrap;">
                        <div>
                            <span class="text-muted">T2➔T4:</span>
                            <strong>{{ $evalRow['d24'] !== null ? $formatDuration($evalRow['d24']) : '-' }}</strong>
                        </div>
                        <div>
                            <span class="text-muted">T3➔T4:</span>
                            <strong>{{ $evalRow['d34'] !== null ? $formatDuration($evalRow['d34']) : '-' }}</strong>
                        </div>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="showDetail('{{ $kb->kodebooking }}')">
                            <i class="fas fa-search"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center py-4 text-muted">
                        Tidak ada data antrean untuk filter dan periode yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($isPaginated && $kodebookings->hasPages())
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
        <div class="text-muted small">
            Halaman <strong>{{ $kodebookings->currentPage() }}</strong> dari <strong>{{ $kodebookings->lastPage() }}</strong>
            (Total {{ number_format($kodebookings->total()) }} data)
        </div>
        <div>
            {{ $kodebookings->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endif

{{-- Modal Detail Task ID --}}
<div class="modal fade" id="modalDetailTask" tabindex="-1" aria-labelledby="modalDetailTaskLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <h5 class="modal-title" id="modalDetailTaskLabel">Detail Task ID - <span id="detailKodebookingTitle"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
            <table class="table table-bordered table-striped" id="tableDetailTask">
                <thead>
                    <tr>
                        <th>Task ID</th>
                        <th>Waktu Eksekusi</th>
                        <th>Status / Message</th>
                        <th>Request (Raw)</th>
                        <th>Response (Raw)</th>
                    </tr>
                </thead>
                <tbody id="bodyDetailTask">
                    <!-- Data will be populated via AJAX -->
                </tbody>
            </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
function showDetail(kodebooking) {
    $('#detailKodebookingTitle').text(kodebooking);
    $('#bodyDetailTask').html('<tr><td colspan="5" class="text-center">Memuat data...</td></tr>');
    
    var modal = new bootstrap.Modal(document.getElementById('modalDetailTask'));
    modal.show();

    fetch('{{ $detailBaseUrl ?? $filterAction }}/' + encodeURIComponent(kodebooking))
        .then(response => response.json())
        .then(data => {
            if(data.metadata.code === 200 && data.response.length > 0) {
                let html = '';
                
                const formatWaktuWIB = (waktuMs) => {
                    if (!waktuMs) return '-';
                    if (String(waktuMs).length === 13 && !isNaN(waktuMs)) {
                        let d = new Date(parseInt(waktuMs));
                        let day = String(d.getDate()).padStart(2, '0');
                        let month = String(d.getMonth() + 1).padStart(2, '0');
                        let year = d.getFullYear();
                        let hr = String(d.getHours()).padStart(2, '0');
                        let min = String(d.getMinutes()).padStart(2, '0');
                        let sec = String(d.getSeconds()).padStart(2, '0');
                        return `${day}-${month}-${year} ${hr}:${min}:${sec} WIB <br><small class="text-muted">(${waktuMs})</small>`;
                    }
                    return waktuMs;
                };

                data.response.forEach(function(task) {
                    let msgText = String(task.message || '');
                    let badgeClass = 'bg-danger';
                    if (msgText.toLowerCase().includes('success') || msgText.toLowerCase().includes('ok')) {
                        badgeClass = 'bg-success';
                    } else if (msgText.toLowerCase().includes('antrean') || msgText === '') {
                        badgeClass = 'bg-warning text-dark';
                    }
                    
                    let waktuWIB = formatWaktuWIB(task.waktu);

                    html += `<tr>
                        <td class="text-center"><strong>${task.taskid}</strong></td>
                        <td>${waktuWIB}</td>
                        <td><span class="badge ${badgeClass}">${msgText || '-'}</span></td>
                        <td style="max-width: 200px; overflow-wrap: break-word;"><small>${task.request || '-'}</small></td>
                        <td style="max-width: 200px; overflow-wrap: break-word;"><small>${task.response || '-'}</small></td>
                    </tr>`;
                });
                $('#bodyDetailTask').html(html);
            } else {
                $('#bodyDetailTask').html('<tr><td colspan="5" class="text-center">Tidak ada riwayat Task ID.</td></tr>');
            }
        })
        .catch(error => {
            console.error('Error fetching detail:', error);
            $('#bodyDetailTask').html('<tr><td colspan="5" class="text-center text-danger">Terjadi kesalahan saat memuat data.</td></tr>');
        });
}
</script>
@endpush
