{{--
  Partial DRY: halaman Rekap Kode Booking (QLJ / QLKP / QLTMG), mode ringkas + detail.
  Params: $pageTitle, $routeName, $summaryItems, $isDetail, $detailItems, $startDate, $endDate, $tableId, $urlQL
--}}

@section('page_title')
    <i class="fas fa-chart-bar me-2 text-primary"></i>{{ $pageTitle }}
@endsection

@section('page_subtitle')
    Ringkasan dan detail data kode booking
@endsection

<style>
/* ─── Metric Filter Cards ─── */
.filter-box {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 14px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.filter-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(15, 76, 92, 0.12);
}
.filter-box.active-filter {
    border-color: var(--color-primary, #0f4c5c) !important;
    box-shadow: 0 0 0 2px rgba(15, 76, 92, 0.25) !important;
}
.filter-info {
    display: flex;
    flex-direction: column;
}
.filter-title {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: 2px;
    letter-spacing: 0.03em;
}
.filter-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.1;
}
.filter-icon {
    font-size: 1.85rem;
    opacity: 0.85;
}
.filter-primary .filter-title { color: var(--color-primary, #0f4c5c); }
.filter-primary .filter-icon { color: var(--color-primary, #0f4c5c); }
.filter-success .filter-title { color: #10b981; }
.filter-success .filter-icon { color: #10b981; }
.filter-warning .filter-title { color: #f59e0b; }
.filter-warning .filter-icon { color: #f59e0b; }
.filter-danger .filter-title { color: #ef4444; }
.filter-danger .filter-icon { color: #ef4444; }

/* ─── Table & Action Styling ─── */
.rekap-table-wrap {
    background: var(--color-surface, #fff);
    border: 1px solid var(--color-border, #e3e6f0);
    border-radius: 10px;
    padding: 12px 14px;
    box-shadow: var(--shadow-sm);
    overflow-x: auto;
}
.rekap-table-wrap table.dataTable {
    margin-top: 8px !important;
    margin-bottom: 8px !important;
    font-size: 0.82rem !important;
}
.rekap-table-wrap table.dataTable th,
.rekap-table-wrap table.dataTable td {
    padding: 7px 10px !important;
    vertical-align: middle !important;
}
.rekap-table-wrap thead th {
    position: static !important;
    background: var(--color-primary, #0f4c5c) !important;
    color: #ffffff !important;
    font-size: 0.78rem !important;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    white-space: nowrap;
    text-align: center;
    border-color: rgba(255, 255, 255, 0.15) !important;
}
.rekap-table-wrap thead th small,
.rekap-table-wrap thead th .text-muted {
    color: rgba(255, 255, 255, 0.8) !important;
}

.row-highlight-flash > td {
    background-color: #d1fae5 !important;
    transition: background-color 1.2s ease;
}

.btn-json-compact {
    padding: 2px 7px !important;
    font-size: 0.72rem !important;
    line-height: 1.25 !important;
    font-weight: 500;
}
.btn-resend-soft {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; font-size: 0.74rem; font-weight: 600;
    color: var(--color-primary, #0f4c5c);
    background: color-mix(in srgb, var(--color-primary, #0f4c5c) 10%, transparent);
    border: 1px solid color-mix(in srgb, var(--color-primary, #0f4c5c) 25%, transparent);
    border-radius: 999px; cursor: pointer;
    transition: all .15s ease;
}
.btn-resend-soft:hover {
    color: #fff; background: var(--color-primary, #0f4c5c);
    border-color: var(--color-primary, #0f4c5c);
    box-shadow: 0 2px 6px color-mix(in srgb, var(--color-primary, #0f4c5c) 35%, transparent);
}

.resend-prev {
    display: flex; align-items: center; gap: 10px; margin-bottom: 10px;
    padding: 6px 12px; border-radius: 8px; font-size: .8rem; text-align: left;
    white-space: nowrap; overflow: hidden;
    background: color-mix(in srgb, #ef4444 8%, transparent);
    border: 1px solid color-mix(in srgb, #ef4444 25%, transparent);
}
.resend-prev.ok {
    background: color-mix(in srgb, #10b981 8%, transparent);
    border-color: color-mix(in srgb, #10b981 25%, transparent);
}
.resend-prev-label { font-weight: 600; color: #64748b; flex-shrink: 0; }
.resend-prev-code { font-weight: 700; padding: 0 8px; border-radius: 999px; color: #fff; background: #ef4444; flex-shrink: 0; }
.resend-prev.ok .resend-prev-code { background: #10b981; }
.resend-prev-msg { overflow: hidden; text-overflow: ellipsis; color: #334155; }
</style>

{{-- Filter Card --}}
<div class="filter-card" style="flex-wrap: wrap; gap: 8px; align-items: center;">
    <i class="fas fa-filter" style="color:var(--color-primary); flex-shrink:0;"></i>
    <form method="GET" action="{{ route($routeName) }}" style="display:contents;">
        <label for="start_date">Dari:</label>
        <input type="date" id="start_date" name="start_date" value="{{ request('start_date', $startDate ?? date('Y-m-d')) }}">
        <label for="end_date">Sampai:</label>
        <input type="date" id="end_date" name="end_date" value="{{ request('end_date', $endDate ?? date('Y-m-d')) }}">
        <button type="submit" class="btn-filter btn-primary">
            <i class="fas fa-search"></i> Filter
        </button>
        <a href="{{ route($routeName) }}" class="btn-filter btn-secondary">
            <i class="fas fa-undo"></i> Reset
        </a>
    </form>
    <div class="date-presets" role="group" aria-label="Preset tanggal cepat">
        <button type="button" class="btn-preset" data-preset="yesterday">Kemarin</button>
        <button type="button" class="btn-preset" data-preset="week">7 hari</button>
        <button type="button" class="btn-preset" data-preset="month">Bulan ini</button>
    </div>
    @if($isDetail ?? false)
    <a href="{{ route($routeName, ['start_date' => $startDate, 'end_date' => $endDate]) }}"
       class="btn-filter btn-secondary ms-auto">
        <i class="fas fa-arrow-left me-1"></i> Kembali ke Rekap
    </a>
    @endif
</div>

{{-- Mode Detail / Mode Summary --}}
@if($isDetail ?? false)
@php
    $count200 = 0;
    $count0 = 0;
    $countError = 0;
    foreach($detailItems as $item) {
        if ($item->code == 200 || $item->code == 208) $count200++;
        else if ($item->code == 0) $count0++;
        else $countError++;
    }
    $totalCount = count($detailItems);
    $activeTableId = $tableId ?? 'detailTable';
    $activeUrlQL = $urlQL ?? 'QLJ';
@endphp

<div class="row mb-3 mt-3">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="filter-box filter-primary active-filter" onclick="filterTableCode(this, '')">
            <div class="filter-info">
                <span class="filter-title">Total Data</span>
                <span class="filter-value">{{ $totalCount }}</span>
            </div>
            <div class="filter-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="filter-box filter-success" onclick="filterTableCode(this, '200')">
            <div class="filter-info">
                <span class="filter-title">200 / 208 (OK)</span>
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
                <i class="fas fa-hourglass-half"></i>
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
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
    </div>
</div>

{{-- Detail per message --}}
<div class="rekap-table-wrap">
    <table id="{{ $activeTableId }}" class="table table-hover table-striped table-bordered w-100" style="table-layout: auto;">
        <thead>
            <tr>
                <th style="width:3%;">No</th>
                <th style="width:17%;">Informasi Pasien &amp; Booking<br><small class="text-muted fw-normal">Kode Booking, RM &amp; ID Daftar</small></th>
                <th style="width:8%;">Tgl Periksa</th>
                <th style="width:18%;">Dokter &amp; Poli<br><small class="text-muted fw-normal">Unit Layanan &amp; Dokter</small></th>
                <th style="width:7%;">Antrean<br><small class="text-muted fw-normal">No Antrean</small></th>
                <th style="width:28%;">Status &amp; Message BPJS<br><small class="text-muted fw-normal">Code &amp; Pesan Respon Bridging</small></th>
                <th style="width:19%;" class="text-center">Aksi / JSON</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detailItems as $index => $item)
            @php
                $code = $item->code;
                $badgeClass = ($code == 200 || $code == 208) ? 'badge-success' : ($code == 0 ? 'badge-warning' : 'badge-danger');
                $badgeLabel = $code == 200 ? '200 OK' : ($code == 208 ? '208 OK' : ($code == 0 ? 'Pending' : 'Err '.$code));
                $rawResp = $item->response ?? '';
                $rawReq = $item->request ?? '';
                $msg = json_decode($rawResp)->metadata->message ?? ($item->message ?? '');
                $idPend = $item->idpendaftaran ?? '';
                $normVal = !empty($item->norm) ? $item->norm : '-';
                $tglPeriksaVal = !empty($item->tanggalperiksa) ? $item->tanggalperiksa : '-';
                $dokterVal = !empty($item->namadokter) ? $item->namadokter : '-';
                $poliVal = !empty($item->namaunit) ? $item->namaunit : '-';
                $noAntreanVal = !empty($item->noantrian) ? $item->noantrian : '-';
                $reupCount = $item->reupload ?? 1;
            @endphp
            <tr data-row-id="{{ $item->id ?? '' }}" data-kb="{{ $item->kodebooking }}">
                <td class="text-center"></td>
                <td class="text-nowrap">
                    <div class="d-flex align-items-center gap-1">
                        <span class="font-monospace fw-bold text-dark fs-6">{{ $item->kodebooking }}</span>
                    </div>
                    <div class="small text-muted mt-1">
                        <span>RM: <strong class="text-dark font-monospace">{{ $normVal }}</strong></span>
                        @if($idPend)
                            <span class="ms-1">| ID: <strong class="text-dark font-monospace">{{ $idPend }}</strong></span>
                        @endif
                    </div>
                </td>
                <td class="text-nowrap text-center">{{ $tglPeriksaVal }}</td>
                <td>
                    <div class="text-truncate fw-semibold text-dark" style="max-width: 220px;" title="{{ $dokterVal }}">
                        {{ $dokterVal }}
                    </div>
                    <div class="mt-1">
                        <span class="badge bg-secondary text-truncate d-inline-block" style="max-width: 160px; font-size:0.68rem;" title="{{ $poliVal }}">
                            {{ $poliVal }}
                        </span>
                    </div>
                </td>
                <td class="text-center">
                    <span class="badge bg-primary px-2 fs-6">{{ $noAntreanVal }}</span>
                </td>
                <td>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="status-badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                    </div>
                    <div class="text-break" style="font-size:0.78rem; line-height:1.35; color:#334155;" title="{{ $msg }}">
                        {{ $msg ?: '-' }}
                    </div>
                </td>
                <td class="text-center">
                    <div class="d-flex flex-column gap-1">
                        <div class="d-flex align-items-center justify-content-between gap-1">
                            <span class="badge bg-light text-secondary border" title="Nilai Reupload" style="font-size:0.66rem;">
                                Reup: {{ $reupCount }}
                            </span>
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-outline-secondary btn-json-view btn-json-compact"
                                        data-json="{{ htmlspecialchars($rawReq, ENT_QUOTES, 'UTF-8') }}"
                                        data-title="Request — {{ $item->kodebooking }}"
                                        title="Lihat Payload Request">
                                    <i class="fas fa-file-code me-1"></i>Req
                                </button>
                                <button class="btn btn-outline-info btn-json-view btn-json-compact"
                                        data-json="{{ htmlspecialchars($rawResp, ENT_QUOTES, 'UTF-8') }}"
                                        data-title="Response — {{ $item->kodebooking }}"
                                        title="Lihat Payload Response">
                                    <i class="fas fa-poll-h me-1"></i>Resp
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn-resend-soft btn-resend-kb w-100 justify-content-center mt-1"
                            data-kodebooking="{{ $item->kodebooking }}"
                            data-norm="{{ $item->norm }}"
                            data-response="{{ $rawResp }}"
                            title="Kirim Ulang Kode Booking {{ $item->kodebooking }} ke BPJS">
                            <i class="fas fa-paper-plane me-1"></i> Kirim Ulang
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@else
{{-- Rekap summary --}}
<div class="rekap-table-wrap">
    <table id="summaryTable" class="table table-hover table-striped table-bordered w-100">
        <thead>
            <tr>
                <th style="width:5%;">No</th>
                <th>Pesan / Message Bridging</th>
                <th style="width:15%;">Total Frekuensi</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($summaryItems as $index => $item)
            @php
                $grandTotal += (int) $item->total;
                $rawMsg = $item->message_all ?? '';
                $isEmptyMsg = ($rawMsg === '__EMPTY__' || $rawMsg === null || $rawMsg === '');
                $msgParam = $isEmptyMsg ? '__EMPTY__' : $rawMsg;
                $msgLabel = $isEmptyMsg ? '(Kosong / Tanpa Pesan)' : $rawMsg;
            @endphp
            <tr>
                <td class="text-center"></td>
                <td>
                    <a href="{{ route($routeName, ['start_date' => $startDate, 'end_date' => $endDate, 'message' => $msgParam]) }}"
                       class="text-decoration-none fw-semibold" style="color:var(--color-primary, #0f4c5c);">
                        <i class="fas fa-filter me-1 text-primary" style="font-size:11px;"></i>
                        <span class="{{ $isEmptyMsg ? 'fst-italic text-muted' : '' }}">{{ $msgLabel }}</span>
                    </a>
                </td>
                <td class="text-center">
                    <span class="badge bg-secondary px-3 py-2 fs-6">{{ number_format($item->total) }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="table-light">
                <th colspan="2" class="text-end fw-bold">Total Keseluruhan</th>
                <th class="text-center fw-bold text-primary fs-6">{{ number_format($grandTotal) }}</th>
            </tr>
        </tfoot>
    </table>
</div>
@endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function filterTableCode(element, type) {
    $('.filter-box').removeClass('active-filter');
    $(element).addClass('active-filter');

    var table = $('#{{ $tableId ?? "detailTable" }}').DataTable();

    if (type === '') {
        table.column(5).search('').draw();
    } else if (type === '200') {
        table.column(5).search('200 OK|208 OK', true, false).draw();
    } else if (type === '0') {
        table.column(5).search('Pending').draw();
    } else if (type === 'error') {
        table.column(5).search('Err').draw();
    }
}

$(document).ready(function () {
    @if($isDetail ?? false)
    var tableId = '{{ $tableId ?? "detailTable" }}';
    var urlQL = '{{ $urlQL ?? "QLJ" }}';
    var $table = $('#' + tableId);
    var dtDetail = $table.DataTable({
        paging: true,
        lengthChange: true,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        responsive: false,
        pageLength: 25,
        order: [[1, 'asc']],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
        columnDefs: [
            { orderable: false, targets: [0, 6] },
            { className: 'text-center', targets: [0, 2, 4, 6] }
        ]
    });

    // Nomor urut dinamis saat sorting, searching, paging
    dtDetail.on('order.dt search.dt page.dt draw.dt', function () {
        var info = dtDetail.page.info();
        dtDetail.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
            cell.innerHTML = (info.start + i + 1);
        });
    });

    function escapeAttr(s) {
        if (s == null) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function updateKodebookingRow(kb, code, msg, res) {
        var $targetNode = null;
        dtDetail.rows().every(function () {
            var node = this.node();
            if (node && String($(node).attr('data-kb')) === String(kb)) {
                $targetNode = node;
            }
        });

        if (!$targetNode) return;
        var row = dtDetail.row($targetNode);
        if (!row.length) return;

        var badgeClass = (code == 200 || code == 208) ? 'badge-success' : (code == 0 ? 'badge-warning' : 'badge-danger');
        var badgeLabel = (code == 200 ? '200 OK' : (code == 208 ? '208 OK' : (code == 0 ? 'Pending' : 'Err ' + code)));
        var safeMsg = escapeAttr(msg);

        var data = row.data();
        data[5] = '<div class="d-flex align-items-center gap-2 mb-1">' +
                      '<span class="status-badge ' + badgeClass + '">' + badgeLabel + '</span>' +
                  '</div>' +
                  '<div class="text-break" style="font-size:0.78rem; line-height:1.35; color:#334155;" title="' + safeMsg + '">' +
                      (safeMsg || '-') +
                  '</div>';

        var respStr = '';
        try {
            respStr = JSON.stringify((res && res.response) ? res.response : res);
        } catch (e) {
            respStr = String((res && res.response) ? res.response : res);
        }

        var $tr = $($targetNode);
        $tr.find('.btn-resend-kb').attr('data-response', respStr);
        $tr.find('button[data-title^="Response"]').attr('data-json', respStr);

        row.data(data).draw(false);

        var $updatedTr = $(row.node());
        $updatedTr.addClass('row-highlight-flash');
        setTimeout(function () {
            $updatedTr.removeClass('row-highlight-flash');
        }, 1600);
    }

    $table.on('click', '.btn-resend-kb', function() {
        var $btnRow = $(this);
        var kb = $(this).data('kodebooking');
        var norm = $(this).data('norm');

        Swal.fire({ title: 'Memuat request...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        $.getJSON('{{ url("api/preview-kodebooking") }}', { urlQL: urlQL, kodebooking: kb })
        .fail(function(xhr) {
            var m = (xhr.responseJSON && xhr.responseJSON.metadata) ? xhr.responseJSON.metadata.message : 'Gagal memuat data.';
            Swal.fire({ icon: 'error', title: 'Gagal', text: m });
        })
        .done(function(res) {
            var rowResp = String($btnRow.attr('data-response') || '').trim();
            var pc = '-', pMsg = 'Belum ada response';
            if (rowResp) {
                pMsg = rowResp;
                try {
                    var pj = JSON.parse(rowResp);
                    if (pj && pj.metadata && pj.metadata.code !== undefined) pc = pj.metadata.code;
                    pMsg = JSON.stringify(pj);
                } catch (e) {}
            }
            var pOk = (pc == 200 || pc == 208);
            pMsg = $('<div>').text(pMsg).html();
            var prevBar = '<div class="resend-prev ' + (pOk ? 'ok' : 'err') + ' text-center" title="' + pMsg.replace(/"/g, '&quot;') + '">' +
                '<div class="d-flex align-items-center justify-content-center gap-2 mb-1">' +
                '<span class="resend-prev-label">Status</span>' +
                '<span class="resend-prev-code">' + pc + '</span>' +
                '</div>' +
                '<div class="resend-prev-msg text-start" style="font-size:0.75rem; max-height:120px; overflow:auto;">' + pMsg.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</div></div>';

            var r = res.response || {};
            var jampraktek = r.jampraktek || '';

            var pasienbaru = r.pasienbaru || 0;
            var estimasiRaw = r.estimasidilayani != null ? r.estimasidilayani : '';
            var estimasiWIB = '';
            if (estimasiRaw && !isNaN(estimasiRaw)) {
                var estDate = new Date(parseInt(estimasiRaw));
                if (!isNaN(estDate.getTime())) {
                    var opt = { timeZone: 'Asia/Jakarta', day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
                    estimasiWIB = estDate.toLocaleString('id-ID', opt).replace(/\./g, ':').replace(/\//g, '-');
                } else {
                    estimasiWIB = estimasiRaw;
                }
            } else {
                estimasiWIB = estimasiRaw;
            }

            var formHtml = '' +
                '<div class="row g-2 mb-2">' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Kode Booking</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.kodebooking || kb) + '" readonly>' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Jenis Pasien</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" id="inputJenispasien" value="' + (r.jenispasien || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">No Kartu BPJS</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" id="inputNomorkartu" value="' + (r.nomorkartu || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">NIK</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" id="inputNik" value="' + (r.nik || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">No Telpon</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" id="inputNohp" value="' + (r.nohp || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Kode Poli</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" id="inputKodepoli" value="' + (r.kodepoli || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Nama Poli</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" id="inputNamapoli" value="' + (r.namapoli || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Pasien Baru</label>' +
                '      <select class="form-select form-select-sm flex-fill" id="inputPasienbaru">' +
                '        <option value="0" ' + (pasienbaru == 0 ? 'selected' : '') + '>0 - Pasien Lama</option>' +
                '        <option value="1" ' + (pasienbaru == 1 ? 'selected' : '') + '>1 - Pasien Baru</option>' +
                '      </select>' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">No RM</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" id="inputNorm" value="' + (r.norm || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Tgl Periksa</label>' +
                '      <input type="date" class="form-control form-control-sm flex-fill" id="inputTanggalperiksa" value="' + (r.tanggalperiksa || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Kode Dokter</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" id="inputKodedokter" value="' + (r.kodedokter || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Nama Dokter</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" id="inputNamadokter" value="' + (r.namadokter || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Jam Praktik</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" id="inputJampraktek" value="' + (r.jampraktek || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Jenis Kunjungan</label>' +
                '      <select class="form-select form-select-sm flex-fill" id="inputJeniskunjungan">' +
                '        <option value="1" ' + (r.jeniskunjungan == 1 ? 'selected' : '') + '>1 - Rujukan FKTP</option>' +
                '        <option value="2" ' + (r.jeniskunjungan == 2 ? 'selected' : '') + '>2 - Rujukan Internal</option>' +
                '        <option value="3" ' + (r.jeniskunjungan == 3 ? 'selected' : '') + '>3 - Kontrol</option>' +
                '        <option value="4" ' + (r.jeniskunjungan == 4 ? 'selected' : '') + '>4 - Rujukan Antar RS</option>' +
                '      </select>' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">No Referensi</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" id="inputNomorreferensi" value="' + (r.nomorreferensi || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">No Antrean</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" id="inputNomorantrean" value="' + (r.nomorantrean || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Angka Antrean</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" id="inputAngkaantrean" value="' + (r.angkaantrean || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Estimasi Dilayani</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" id="inputEstimasi" value="' + estimasiWIB + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Sisa Kuota JKN</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" id="inputSisakuotajkn" value="' + (r.sisakuotajkn != null ? r.sisakuotajkn : '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Kuota JKN</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" id="inputKuotajkn" value="' + (r.kuotajkn || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Sisa Kuota Non JKN</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" id="inputSisakuotanonjkn" value="' + (r.sisakuotanonjkn || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Kuota Non JKN</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" id="inputKuotanonjkn" value="' + (r.kuotanonjkn || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-12">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Keterangan</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" id="inputKeterangan" value="' + (r.keterangan || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '</div>' +
                '<div class="mb-2">' +
                '  <button type="button" class="btn btn-sm btn-outline-secondary" id="btnToggleJson" style="width:100%;font-size:11px;"><i class="fas fa-code me-1"></i> Edit JSON</button>' +
                '</div>' +
                '<div id="jsonRawBox" style="display:none;" class="mb-2 p-2 bg-dark text-light rounded border border-secondary">' +
                '  <label class="form-label fw-bold text-xs text-white mb-1">JSON Request</label>' +
                '  <textarea id="resendPayload" class="form-control form-control-sm bg-black text-white border-0" rows="10" spellcheck="false"></textarea>' +
                '</div>';

            Swal.fire({
                title: '',
                width: 1150,
                html: '<div class="text-start mb-3" style="font-size:0.85rem;">' +
                    '<div class="p-2 rounded mb-2 text-center" style="background:var(--color-primary, #0f4c5c); color:#fff; border:1px solid var(--color-primary-dark, #0a3a46);">' +
                    '<div class="fw-bold mb-1" style="font-size:0.95rem;"><i class="fas fa-paper-plane me-2"></i>Kirim Ulang Kode Booking</div>' +
                    '<div class="d-flex align-items-center justify-content-center flex-wrap gap-3" style="font-size:0.85rem;">' +
                    '<span><i class="fas fa-barcode me-1"></i> Kode Booking: <strong>' + kb + '</strong></span>' +
                    '<span><i class="fas fa-user me-1"></i> No RM: <strong>' + norm + '</strong></span>' +
                    '</div>' +
                    '</div>' +
                    prevBar +
                    '<div class="mt-3"><h6 class="fw-bold text-dark mb-2" style="font-size:0.9rem;"><i class="fas fa-edit me-1"></i>Form Pengisian Ulang</h6></div>' +
                    formHtml,
                showCancelButton: true,
                didOpen: () => {
                    var ta = document.getElementById('resendPayload');
                    if (ta && !ta.value.trim()) {
                        try { ta.value = JSON.stringify(r, null, 2); } catch (e) {}
                    }
                    var btn = document.getElementById('btnToggleJson');
                    var box = document.getElementById('jsonRawBox');
                    if (btn && box) {
                        btn.addEventListener('click', function () {
                            if (box.style.display === 'none' || !box.style.display) {
                                box.style.display = 'block';
                                btn.innerHTML = '<i class="fas fa-eye-slash me-1"></i> Sembunyikan JSON';
                            } else {
                                box.style.display = 'none';
                                btn.innerHTML = '<i class="fas fa-code me-1"></i> Edit JSON';
                            }
                        });
                    }
                },
                confirmButtonText: '<i class="fas fa-paper-plane me-1"></i> Kirim ke BPJS',
                cancelButtonText: 'Batal',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-primary px-4 py-2',
                    cancelButton: 'btn btn-outline-secondary px-4 py-2'
                },
                preConfirm: () => {
                    var payloadText = document.getElementById('resendPayload') ? document.getElementById('resendPayload').value : '';
                    var payload = {};
                    try {
                        if (payloadText.trim()) {
                            payload = JSON.parse(payloadText);
                        } else {
                            var getVal = function(id) { var el = document.getElementById(id); return el ? el.value : ''; };
                            payload.kodebooking = kb;
                            payload.jenispasien = getVal('inputJenispasien') || r.jenispasien || 'JKN';
                            payload.nomorkartu = getVal('inputNomorkartu') || r.nomorkartu || '';
                            payload.nik = getVal('inputNik') || r.nik || '';
                            payload.nohp = getVal('inputNohp') || r.nohp || '';
                            payload.kodepoli = getVal('inputKodepoli') || r.kodepoli || '';
                            payload.namapoli = getVal('inputNamapoli') || r.namapoli || '';
                            payload.pasienbaru = parseInt(getVal('inputPasienbaru') || r.pasienbaru || 0);
                            payload.norm = getVal('inputNorm') || r.norm || '';
                            payload.tanggalperiksa = getVal('inputTanggalperiksa') || r.tanggalperiksa || '';
                            payload.kodedokter = getVal('inputKodedokter') || r.kodedokter || '';
                            payload.namadokter = getVal('inputNamadokter') || r.namadokter || '';
                            payload.jampraktek = getVal('inputJampraktek') || r.jampraktek || '';
                            payload.jeniskunjungan = getVal('inputJeniskunjungan') || r.jeniskunjungan || 3;
                            payload.nomorreferensi = getVal('inputNomorreferensi') || r.nomorreferensi || '';
                            payload.nomorantrean = getVal('inputNomorantrean') || r.nomorantrean || '';
                            payload.angkaantrean = getVal('inputAngkaantrean') || r.angkaantrean || '';

                            var estInput = getVal('inputEstimasi');
                            if (estInput && estInput !== estimasiWIB) {
                                var estDate = new Date(estInput);
                                if (!isNaN(estDate.getTime())) {
                                    payload.estimasidilayani = estDate.getTime();
                                } else {
                                    payload.estimasidilayani = r.estimasidilayani || 0;
                                }
                            } else {
                                payload.estimasidilayani = r.estimasidilayani || 0;
                            }
                            payload.sisakuotajkn = getVal('inputSisakuotajkn') || r.sisakuotajkn || 0;
                            payload.kuotajkn = getVal('inputKuotajkn') || r.kuotajkn || 0;
                            payload.sisakuotanonjkn = getVal('inputSisakuotanonjkn') || r.sisakuotanonjkn || 0;
                            payload.kuotanonjkn = getVal('inputKuotanonjkn') || r.kuotanonjkn || 0;
                            payload.keterangan = getVal('inputKeterangan') || r.keterangan || '';
                        }
                    } catch (e) {
                        Swal.showValidationMessage('Format JSON tidak valid: ' + e.message);
                        return false;
                    }
                    return payload;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Memproses ke BPJS...',
                        html: 'Sedang menghubungi BPJS Antrol...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    $.ajax({
                        url: '{{ url("api/run-kodebooking-by-kodebooking") }}',
                        type: 'POST',
                        data: {
                            urlQL: urlQL,
                            kodebooking: kb,
                            payload: JSON.stringify(result.value)
                        },
                        success: function(res) {
                            var meta = res.metadata || {};
                            var code = meta.code || 200;
                            var msg = meta.message || 'Proses selesai.';

                            updateKodebookingRow(kb, code, msg, res);

                            if (code == 200 || code == 208) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil (Code ' + code + ')',
                                    text: msg,
                                    timer: 2500,
                                    timerProgressBar: true
                                });
                            } else {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Respon BPJS (Code ' + code + ')',
                                    text: msg
                                });
                            }
                        },
                        error: function(xhr) {
                            var errMeta = (xhr.responseJSON && xhr.responseJSON.metadata) ? xhr.responseJSON.metadata : {};
                            var errMsg = errMeta.message || (xhr.responseJSON ? JSON.stringify(xhr.responseJSON) : 'Gagal mengirim ulang data.');
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Mengirim',
                                text: errMsg
                            });
                        }
                    });
                }
            });
        });
    });

    @else
    // Init DataTable untuk rekap summary agar rapi & nomor urut dinamis
    var dtSummary = $('#summaryTable').DataTable({
        paging: true,
        pageLength: 25,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        responsive: false,
        order: [[2, 'desc']], // Urutkan default berdasar total frekuensi
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
        columnDefs: [
            { orderable: false, targets: [0] },
            { className: 'text-center', targets: [0, 2] }
        ]
    });

    dtSummary.on('order.dt search.dt page.dt draw.dt', function () {
        var info = dtSummary.page.info();
        dtSummary.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
            cell.innerHTML = (info.start + i + 1);
        });
    });
    @endif
});
</script>
@endpush
