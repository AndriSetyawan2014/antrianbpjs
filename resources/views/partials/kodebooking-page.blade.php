{{--
  Partial DRY: halaman Data Kode Booking (QLJ / QLKP / QLTMG)
  Params: $pageTitle, $filterAction, $exportUrl, $items, $startDate, $endDate, $tableId, $urlQL
  Catatan: Partial ini HANYA mendefinisikan page_title, page_subtitle, dan konten.
           View induk bertanggung jawab membungkus @include ini dalam @section('content').
--}}

@section('page_title')
    <h1 class="page-title"><i class="fas fa-calendar-check me-2 text-primary"></i>{{ $pageTitle }}</h1>
@endsection

@section('page_subtitle')
    Kelola data kode booking pasien
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
.btn-resend-soft {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 4px 12px; font-size: 0.78rem; font-weight: 600;
    color: var(--color-primary, #4e73df);
    background: color-mix(in srgb, var(--color-primary, #4e73df) 10%, transparent);
    border: 1px solid color-mix(in srgb, var(--color-primary, #4e73df) 25%, transparent);
    border-radius: 999px; cursor: pointer;
    transition: all .15s ease;
}
.btn-resend-soft:hover {
    color: #fff; background: var(--color-primary, #4e73df);
    border-color: var(--color-primary, #4e73df);
    box-shadow: 0 2px 6px color-mix(in srgb, var(--color-primary, #4e73df) 35%, transparent);
}
.resend-editor {
    width: 100%; min-height: 320px; font-family: monospace; font-size: 0.8rem;
    padding: 10px; border: 1px solid #d1d3e2; border-radius: 8px; resize: vertical;
}
.resend-editor:focus { outline: none; border-color: var(--color-primary, #4e73df); }
.kb-table-wrap { border: 1px solid var(--color-border, #e3e6f0); border-radius: 8px; overflow: hidden; }
.kb-table-wrap .dataTables_wrapper { padding: 8px 10px; }
.kb-table-wrap .dataTables_scrollHead { overflow: hidden; }
.kb-table-wrap .dataTables_scrollHead table { margin-bottom: 0 !important; }
.kb-table-wrap .dataTables_scrollHead thead th {
    position: static !important; box-shadow: none !important;
}
.kb-table-wrap .dataTables_scrollBody thead th,
.kb-table-wrap .dataTables_scrollBody thead td {
    position: static !important; box-shadow: none !important;
    padding-top: 0 !important; padding-bottom: 0 !important; border-top: 0 !important;
    height: 0 !important; line-height: 0 !important; background: transparent !important; color: transparent !important;
}
.kb-table-wrap .dataTables_scrollBody thead th::before,
.kb-table-wrap .dataTables_scrollBody thead th::after { display: none !important; }
.resend-prev {
    display: flex; align-items: center; gap: 10px; margin-bottom: 10px;
    padding: 6px 12px; border-radius: 8px; font-size: .8rem; text-align: left;
    white-space: nowrap; overflow: hidden;
    background: color-mix(in srgb, #e74a3b 8%, transparent);
    border: 1px solid color-mix(in srgb, #e74a3b 25%, transparent);
}
.resend-prev.ok {
    background: color-mix(in srgb, #1cc88a 8%, transparent);
    border-color: color-mix(in srgb, #1cc88a 25%, transparent);
}
.resend-prev-label { font-weight: 600; color: #858796; flex-shrink: 0; }
.resend-prev-code { font-weight: 700; padding: 0 8px; border-radius: 999px; color: #fff; background: #e74a3b; flex-shrink: 0; }
.resend-prev.ok .resend-prev-code { background: #1cc88a; }
.resend-prev-msg { overflow: hidden; text-overflow: ellipsis; color: #5a5c69; }
</style>

{{-- Filter Card (form GET agar tetap jalan tanpa JS) --}}
<div class="filter-card">
    <i class="fas fa-filter" style="color:var(--color-primary);"></i>
    <form method="GET" action="{{ $filterAction }}" style="display:contents;">
        <label for="start_date">Dari:</label>
        <input type="date" id="start_date" name="start_date" value="{{ $startDate }}">
        <label for="end_date">Sampai:</label>
        <input type="date" id="end_date" name="end_date" value="{{ $endDate }}">

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
    <button type="button" class="btn-filter btn-secondary ms-auto" id="btnHideDup" title="Sembunyikan baris berpesan 'Terdapat duplikasi Kode Booking' (dianggap sudah berhasil)">
        <i class="fas fa-eye-slash me-1"></i> <span>Sembunyikan Duplikasi</span>
    </button>
    <button type="button" class="btn-filter btn-warning ms-2" id="btnResendBatch" title="Kirim Ulang Semua Kode Booking Pending/Error pada rentang tanggal ini">
        <i class="fas fa-sync-alt me-1"></i> Kirim Ulang Pending/Error
    </button>
    <a href="{{ $exportUrl }}" class="btn-filter btn-success ms-2">
        <i class="fas fa-file-excel"></i> Download Excel
    </a>
</div>

@php
    $items = collect($items)
        ->sortByDesc(function ($r) {
            $hasResp = !empty($r->response);
            return ($r->code == 200 ? 4 : ($hasResp ? 2 : 0)) * 1000000000 + (int) ($r->id ?? 0);
        })
        ->unique(fn ($r) => !empty($r->kodebooking) ? $r->kodebooking : spl_object_id($r))
        ->sortBy('id')
        ->values();
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

{{-- Tabel --}}
<div class="kb-table-wrap">
    <table id="{{ $tableId }}" class="table table-hover table-striped table-bordered">
        <thead>
            <tr>
                <th style="width:3%;">No</th>
                <th style="width:13%;">Kode Booking</th>
                <th style="width:8%;">No RM</th>
                <th style="width:9%;">Tgl Periksa</th>
                <th style="width:8%;">Code</th>
                <th style="width:18%;">Message</th>
                <th style="width:9%;" class="text-center">Request</th>
                <th style="width:9%;" class="text-center">Response</th>
                <th style="width:10%;" class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            @php
                $code = $item->code;
                $badgeClass = $code == 200 ? 'badge-success' : ($code == 0 ? 'badge-warning' : 'badge-danger');
                $badgeLabel = $code == 200 ? '✓ 200 OK' : ($code == 0 ? '⏳ Pending' : '✗ Err '.$code);
                $msg = json_decode($item->response)->metadata->message ?? ($item->message ?? '');
            @endphp
            <tr>
                <td></td>
                <td><strong>{{ $item->kodebooking }}</strong></td>
                <td>{{ $item->norm }}</td>
                <td>{{ $item->tanggalperiksa }}</td>
                <td class="text-center">
                    <span class="status-badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                </td>
                <td>{{ $msg }}</td>
                <td class="text-center">
                    <button class="btn-json-view" data-json="{{ htmlspecialchars($item->request, ENT_QUOTES, 'UTF-8') }}" data-title="Request — {{ $item->kodebooking }}">
                        <i class="fas fa-eye"></i> Lihat
                    </button>
                </td>
                <td class="text-center">
                    <button class="btn-json-view" data-json="{{ htmlspecialchars($item->response, ENT_QUOTES, 'UTF-8') }}" data-title="Response — {{ $item->kodebooking }}">
                        <i class="fas fa-eye"></i> Lihat
                    </button>
                </td>
                <td class="text-center text-nowrap">
                    <button type="button" class="btn-resend-soft btn-resend-kb"
                        data-kodebooking="{{ $item->kodebooking }}"
                        data-norm="{{ $item->norm }}"
                        data-response="{{ $item->response }}"
                        title="Kirim Ulang Kode Booking {{ $item->kodebooking }} ke BPJS">
                        <i class="fas fa-paper-plane"></i> Kirim Ulang
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Empty State --}}
@if(empty($items))
<div class="empty-state mt-5">
    <i class="fas fa-calendar-check text-slate-300" style="font-size: 3rem;"></i>
    <p class="mt-3 text-slate-500">Tidak ada data Kode Booking untuk periode yang dipilih.</p>
    <button class="btn btn-primary mt-3" onclick="location.reload()">
        <i class="fas fa-sync-alt me-1"></i> Coba Lagi
    </button>
</div>
@endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function filterTableCode(element, type) {
    $('.filter-box').removeClass('active-filter');
    $(element).addClass('active-filter');

    var table = $('#{{ $tableId }}').DataTable();
    if (type === '') {
        table.column(4).search('').draw();
    } else if (type === '200') {
        table.column(4).search('200 OK').draw();
    } else if (type === '0') {
        table.column(4).search('Pending').draw();
    } else if (type === 'error') {
        table.column(4).search('Err').draw();
    }
}

$(document).ready(function () {
    var tableId = '{{ $tableId }}';
    var urlQL = '{{ $urlQL ?? (str_contains($tableId, "qlkp") ? "QLKP" : (str_contains($tableId, "qltmg") ? "QLTMG" : "QLJ")) }}';
    var $table = $('#' + tableId);

    var dtMain = $table.DataTable({
        responsive: false,
        scrollY: '55vh',
        scrollCollapse: true,
        autoWidth: false,
        pageLength: 25,
        columnDefs: [
            { orderable: false, targets: [0, 6, 7, 8] },
            { render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1, targets: 0 }
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' }
    });

    var hideKey = 'hideDupKb_' + tableId;
    var hideDup = localStorage.getItem(hideKey) === '1';
    $.fn.dataTable.ext.search.push(function (settings, data) {
        if (settings.nTable.id !== tableId || !hideDup) return true;
        return !/duplikasi/i.test(data[5] || '');
    });
    function refreshHideBtn() {
        var $b = $('#btnHideDup');
        $b.toggleClass('btn-primary', hideDup).toggleClass('btn-secondary', !hideDup);
        $b.find('i').attr('class', 'fas me-1 ' + (hideDup ? 'fa-eye' : 'fa-eye-slash'));
        $b.find('span').text(hideDup ? 'Tampilkan Duplikasi' : 'Sembunyikan Duplikasi');
    }
    $('#btnHideDup').on('click', function () {
        hideDup = !hideDup;
        localStorage.setItem(hideKey, hideDup ? '1' : '0');
        refreshHideBtn();
        dtMain.draw();
    });
    refreshHideBtn();
    dtMain.draw();

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
            var pMsgStr = String(pMsg);
            var prevBar = '<div class="resend-prev ' + (pOk ? 'ok' : 'err') + '" title="' + pMsgStr.replace(/"/g, '&quot;') + '">' +
                '<span class="resend-prev-label">Response Sebelumnya</span>' +
                '<span class="resend-prev-code">' + pc + '</span>' +
                '<span class="resend-prev-msg">' + pMsgStr + '</span></div>';
            var prevBox = '<div class="mb-3 p-3 rounded bg-' + (pOk ? 'success' : 'danger') + '-light border border-' + (pOk ? 'success' : 'danger') + '">' +
                '<div class="d-flex justify-content-between align-items-start mb-2">' +
                '<small class="fw-bold text-' + (pOk ? 'success' : 'danger') + '"><i class="fas fa-' + (pOk ? 'check-circle' : 'exclamation-circle') + ' me-1"></i> Status Sebelumnya</small>' +
                '<span class="badge bg-' + (pOk ? 'success' : 'danger') + ' text-white fw-normal">' + pc + '</span>' +
                '</div>' +
                '<div><small class="text-' + (pOk ? 'success' : 'danger') + '">' + pMsgStr.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</small></div>' +
                '</div>';

            // Bangun form input dari response BPJS — label rata kanan, input lebih kecil
            var r = res.response || {};
            var jampraktek = r.jampraktek || '';
            var jamMulai = '', jamAkhir = '';
            if (jampraktek && jampraktek.includes('-')) {
                var parts = jampraktek.split('-');
                jamMulai = parts[0] || '';
                jamAkhir = parts[1] || '';
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
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">No RM</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.norm || '') + '" readonly>' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">No Antrian</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.nomorantrean || '') + '" readonly>' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Tgl Periksa</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.tanggalperiksa || '') + '" readonly>' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Dokter</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.namadokter || '') + '" readonly>' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Poli</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.namapoli || '') + '" readonly>' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Jam Mulai</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + jamMulai + '" readonly>' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Jam Akhir</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + jamAkhir + '" readonly>' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Jenis Kunj.</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.jeniskunjungan || '') + '" readonly>' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Cara Bayar</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.jenispasien || '') + '" readonly>' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">No Kartu BPJS</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.nomorkartu || '') + '" readonly>' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">NIK</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.nik || '') + '" readonly>' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">No Telpon</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.nohp || '') + '" readonly>' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Nomor Referensi</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.nomorreferensi || '') + '" readonly>' +
                '    </div>' +
                '  </div>' +
                '</div>' +
                '<div class="mb-2">' +
                '  <button type="button" class="btn btn-sm btn-outline-secondary" id="btnToggleJson" style="width:100%;font-size:11px;"><i class="fas fa-code me-1"></i> Edit JSON</button>' +
                '</div>' +
                '<div id="jsonRawBox" style="display:none;" class="mb-2 p-2 bg-dark text-light rounded border border-secondary">' +
                '  <label class="form-label fw-bold text-xs text-white mb-1">JSON Request</label>' +
                '  <textarea id="resendPayload" class="form-control form-control-sm bg-black text-white border-0" rows="4" spellcheck="false"></textarea>' +
                '</div>';

            Swal.fire({
                title: '<i class="fas fa-paper-plane me-2"></i> Kirim Ulang Kode Booking',
                width: 1150,
                html: '<div class="text-start mb-3">' +
                    '<div class="d-flex align-items-center mb-1">' +
                    '<span class="badge bg-primary fs-6 me-2">Kode Booking</span>' +
                    '<span class="fw-bold text-dark">' + kb + '</span>' +
                    '</div>' +
                    '<small class="text-muted">No RM: <strong>' + norm + '</strong></small>' +
                    '</div>' + prevBox +
                    '<div class="mt-3"><h6 class="fw-bold text-dark mb-2"><i class="fas fa-edit me-1"></i>Form Pengisian Ulang</h6></div>' +
                    formHtml,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-paper-plane me-1"></i> Kirim ke BPJS',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#0f4c5c',
                cancelButtonColor: '#6c757d',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-primary btn-sm px-4',
                    cancelButton: 'btn btn-outline-secondary btn-sm px-4'
                },
                preConfirm: () => {
                    var payloadText = document.getElementById('resendPayload') ? document.getElementById('resendPayload').value : '';
                    var payload = {};
                    try {
                        if (payloadText.trim()) {
                            payload = JSON.parse(payloadText);
                        } else {
                            payload = r;
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

                            if (code == 200 || code == 208) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil (Code ' + code + ')',
                                    text: msg,
                                    timer: 3000,
                                    timerProgressBar: true
                                }).then(() => location.reload());
                            } else {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Respon BPJS (Code ' + code + ')',
                                    text: msg
                                }).then(() => location.reload());
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

            // Toggle JSON box
            document.getElementById('resendPayload').value = JSON.stringify(r, null, 2);
            document.getElementById('btnToggleJson') && document.getElementById('btnToggleJson').addEventListener('click', function() {
                var box = document.getElementById('jsonRawBox');
                if (box.style.display === 'none') {
                    box.style.display = 'block';
                    this.innerHTML = '<i class="fas fa-eye-slash me-1"></i> Sembunyikan JSON';
                } else {
                    box.style.display = 'none';
                    this.innerHTML = '<i class="fas fa-code me-1"></i> Tampilkan / Edit JSON Raw';
                }
            });
        });
    });

    $('#btnResendBatch').on('click', function() {
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();

        Swal.fire({
            title: 'Kirim Ulang Batch?',
            html: "Kirim ulang semua Kode Booking yang <b>Pending atau Error</b> pada tanggal:<br><strong>" + startDate + "</strong> s/d <strong>" + endDate + "</strong>?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-sync-alt me-1"></i> Ya, Proses Antrean!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#f6c23e',
            cancelButtonColor: '#858796',
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menyiapkan antrean...',
                    html: 'Memasukkan data ke background queue...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: '{{ url("api/run-kodebooking-batch") }}',
                    type: 'GET',
                    data: {
                        urlQL: urlQL,
                        start_date: startDate,
                        end_date: endDate
                    },
                    success: function(res) {
                        var meta = res.metadata || {};
                        Swal.fire({
                            icon: 'success',
                            title: 'Antrean Dibuat',
                            text: meta.message || 'Data berhasil dimasukkan ke antrean pengiriman.'
                        }).then(() => location.reload());
                    },
                    error: function(xhr) {
                        var errMeta = (xhr.responseJSON && xhr.responseJSON.metadata) ? xhr.responseJSON.metadata : {};
                        var errMsg = errMeta.message || 'Gagal memproses antrean.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errMsg
                        });
                    }
                });
            }
        });
    });
});
</script>
@endpush

