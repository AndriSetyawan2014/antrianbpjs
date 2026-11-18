{{--
  Partial DRY: halaman Data Task ID (QLJ / QLKP / QLTMG)
  Params: $pageTitle, $filterAction, $resetUrl, $exportUrl, $exportMode ('server'|'client'),
          $items, $startDate, $endDate, $tableId, $urlQL
  Catatan: Partial ini HANYA berisi konten (tanpa @section wrapper).
           View induk bertanggung jawab membungkus dengan @section('content').
--}}

<style>
.taskid-table-wrap {
    background: var(--color-surface, #fff);
    border: 1px solid var(--color-border, #e3e6f0);
    border-radius: 10px;
    padding: 12px 14px;
    box-shadow: var(--shadow-sm);
    overflow-x: auto;
}
.taskid-table-wrap table.dataTable {
    margin-top: 8px !important;
    margin-bottom: 8px !important;
}
.taskid-table-wrap thead th {
    position: static !important;
}
.row-highlight-flash > td {
    background-color: #d1fae5 !important;
    transition: background-color 1.2s ease;
}
</style>

@php
    $activeMessageFilter = $messageFilter ?? request('message', '');
    $messageList = $messageOptions ?? [];
    if ($activeMessageFilter !== '' && $activeMessageFilter !== '__EMPTY__' && !in_array($activeMessageFilter, $messageList, true)) {
        array_unshift($messageList, $activeMessageFilter);
    }
    $rekapRoutesMap = [
        'QLJ' => 'rekap_taskid',
        'QLKP' => 'qlkp_rekap_taskid',
        'QLTMG' => 'qltmg_rekap_taskid',
    ];
    $rekapRouteName = $rekapRoutesMap[$urlQL ?? 'QLJ'] ?? 'rekap_taskid';
@endphp

{{-- Filter Card --}}
<div class="filter-card" style="flex-wrap: wrap; gap: 8px; align-items: center;">
    <i class="fas fa-filter" style="color:var(--color-primary); flex-shrink:0;"></i>
    <form method="GET" action="{{ $filterAction }}" style="display:contents;">
        <label for="start_date">Dari:</label>
        <input type="date" id="start_date" name="start_date" value="{{ $startDate }}">
        <label for="end_date">Sampai:</label>
        <input type="date" id="end_date" name="end_date" value="{{ $endDate }}">
        <label for="message_filter">Pesan:</label>
        <select id="message_filter" name="message" style="min-width: 190px; max-width: 300px; height: 34px; font-size: 0.82rem; padding: 4px 8px; border-radius: 6px; border: 1px solid #d1d5db;" onchange="this.form.submit()">
            <option value="" {{ $activeMessageFilter === '' ? 'selected' : '' }}>-- Semua Pesan --</option>
            <option value="__EMPTY__" {{ $activeMessageFilter === '__EMPTY__' ? 'selected' : '' }}>(Kosong / Tanpa Pesan)</option>
            @foreach($messageList as $msgOpt)
                <option value="{{ $msgOpt }}" {{ (string) $activeMessageFilter === (string) $msgOpt ? 'selected' : '' }}>
                    {{ \Illuminate\Support\Str::limit($msgOpt, 60) }}
                </option>
            @endforeach
        </select>
        @if(request('from_rekap'))
            <input type="hidden" name="from_rekap" value="1">
        @endif
        <button type="submit" class="btn-filter btn-primary">
            <i class="fas fa-search"></i> Filter
        </button>
        <a href="{{ $resetUrl }}" class="btn-filter btn-secondary">
            <i class="fas fa-undo"></i> Reset
        </a>
    </form>

    <div class="date-presets" role="group" aria-label="Preset tanggal cepat">
        <button type="button" class="btn-preset" data-preset="yesterday">Kemarin</button>
        <button type="button" class="btn-preset" data-preset="week">7 hari</button>
        <button type="button" class="btn-preset" data-preset="month">Bulan ini</button>
    </div>

    @if(($exportMode ?? 'server') === 'server')
    <a href="{{ $exportUrl }}" class="btn-filter btn-success ms-2">
        <i class="fas fa-file-excel"></i> Download
    </a>
    @else
    <button id="download_excel_{{ $tableId }}" class="btn-filter btn-success ms-2">
        <i class="fas fa-file-excel"></i> Download
    </button>
    @endif

    @if($activeMessageFilter !== '' || request('from_rekap'))
    <a href="{{ route($rekapRouteName, ['start_date' => $startDate, 'end_date' => $endDate]) }}"
       class="btn-filter btn-secondary ms-auto">
        <i class="fas fa-arrow-left"></i> Kembali ke Rekap
    </a>
    @endif
</div>

{{-- Tabel --}}
<div class="taskid-table-wrap">
    <table id="{{ $tableId }}" class="table table-hover table-striped table-bordered w-100">
        <thead>
            <tr>
                <th style="width:3%;">No</th>
                <th style="width:9%;">Kode Booking</th>
                <th style="width:8%;">Waktu</th>
                <th style="width:5%;">Task ID</th>
                <th style="width:7%;">ID Pendaftaran</th>
                <th style="width:10%;">Dokter</th>
                <th style="width:7%;">Poli</th>
                <th style="width:7%;">Jadwal RS</th>
                <th style="width:6%;">Code</th>
                <th style="width:10%;">Message</th>
                <th style="width:7%;">Tanggal</th>
                <th style="width:5%;">Jam</th>
                <th style="width:6%;" class="text-center">Request</th>
                <th style="width:6%;" class="text-center">Response</th>
                <th style="width:4%;">Reupload</th>
                <th style="width:8%;" class="text-center">Created / Updated By</th>
                <th style="width:6%;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            @php
                $code = $item->code;
                $badgeClass = $code == 200 ? 'badge-success' : ($code == 0 ? 'badge-warning' : 'badge-danger');
                $badgeLabel = $code == 200 ? '✓ 200 OK' : ($code == 0 ? '⏳ Pending' : '✗ Err '.$code);
                $rawResp = $item->response ?? '';
                $rawReq = $item->request ?? '';
                $idPend = $item->idpendaftaran ?? '';
                $msg = ($item->message !== null && $item->message !== '')
                    ? $item->message
                    : (json_decode($rawResp)->metadata->message ?? '');
                $createdBy = !empty($item->created_by) ? $item->created_by : 'Sync SIMRS';
                $updatedBy = $item->updated_by ?? null;
                $isAutoGen = stripos($createdBy, 'Auto') !== false;
                $isManual = stripos($createdBy, 'Manual') !== false;
            @endphp
            <tr data-row-id="{{ $item->id }}" data-kb="{{ $item->kodebooking }}" data-taskid="{{ $item->taskid }}">
                <td></td>
                <td class="text-nowrap">{{ $item->kodebooking }}</td>
                <td class="text-nowrap">{{ $item->waktu }}</td>
                <td class="text-center"><strong>{{ $item->taskid }}</strong></td>
                <td class="text-nowrap">{{ $idPend }}</td>
                <td><small class="font-weight-bold">{{ $item->namadokter ?? '-' }}</small></td>
                <td><span class="badge bg-secondary">{{ $item->namapoli ?? '-' }}</span></td>
                <td class="text-nowrap">
                    <small class="text-nowrap font-weight-bold">
                        {{ (isset($item->jammulai) && $item->jammulai) ? substr($item->jammulai, 0, 5) . '-' . substr($item->jamakhir, 0, 5) : '-' }}
                    </small>
                </td>
                <td class="text-center text-nowrap">
                    <span class="status-badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                </td>
                <td>{{ $msg }}</td>
                <td class="text-nowrap">{{ $item->tanggal }}</td>
                <td class="text-nowrap">{{ $item->jam }}</td>
                <td class="text-center text-nowrap">
                    <button class="btn-json-view"
                            data-json="{{ htmlspecialchars($rawReq, ENT_QUOTES, 'UTF-8') }}"
                            data-title="Request — {{ $item->taskid }}">
                        <i class="fas fa-eye"></i> Lihat
                    </button>
                </td>
                <td class="text-center text-nowrap">
                    <button class="btn-json-view"
                            data-json="{{ htmlspecialchars($rawResp, ENT_QUOTES, 'UTF-8') }}"
                            data-title="Response — {{ $item->taskid }}">
                        <i class="fas fa-eye"></i> Lihat
                    </button>
                </td>
                <td class="text-center">{{ $item->reupload }}</td>
                <td class="text-center text-nowrap">
                    @if($isAutoGen)
                        <span class="badge" style="background-color: #7c3aed; color: #fff;" title="Dibuat otomatis oleh sistem (Auto Generate)">
                            <i class="fas fa-magic me-1"></i>{{ $createdBy }}
                        </span>
                    @elseif($isManual)
                        <span class="badge bg-warning text-dark" title="Dibuat manual oleh operator">
                            <i class="fas fa-user-edit me-1"></i>{{ $createdBy }}
                        </span>
                    @else
                        <span class="badge bg-info text-dark" title="Hasil sinkronisasi dari SIMRS">
                            <i class="fas fa-sync-alt me-1"></i>{{ $createdBy }}
                        </span>
                    @endif
                    @if(!empty($updatedBy) && $updatedBy !== $createdBy)
                        <div class="text-muted mt-1" style="font-size: 0.68rem;">
                            Upd: <strong>{{ $updatedBy }}</strong>
                        </div>
                    @endif
                </td>
                <td class="text-center text-nowrap">
                    <button class="btn btn-sm btn-info btn-duplicate"
                        data-kodebooking="{{ $item->kodebooking }}"
                        data-taskid="{{ $item->taskid }}"
                        data-waktu="{{ $item->waktu }}"
                        data-idpendaftaran="{{ $idPend }}"
                        title="Duplikat Task ID ini">
                        <i class="fas fa-copy"></i>
                    </button>
                    <button class="btn btn-sm btn-warning btn-edit ms-1"
                        data-id="{{ $item->id }}"
                        data-kodebooking="{{ $item->kodebooking }}"
                        data-taskid="{{ $item->taskid }}"
                        data-waktu="{{ $item->waktu }}"
                        data-idpendaftaran="{{ $idPend }}"
                        title="Edit Task ID ini">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-success btn-sync-kb ms-1"
                        data-kodebooking="{{ $item->kodebooking }}"
                        title="Sinkronisasi Ulang Semua Task ID untuk Kode Booking Ini">
                        <i class="fas fa-sync-alt"></i>
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
    <i class="fas fa-tasks text-slate-300" style="font-size: 3rem;"></i>
    <p class="mt-3 text-slate-500">Tidak ada data Task ID untuk periode yang dipilih.</p>
    <button class="btn btn-primary mt-3" onclick="location.reload()">
        <i class="fas fa-sync-alt me-1"></i> Coba Lagi
    </button>
</div>
@endif

<!-- Modal Duplicate Task ID -->
<div class="modal fade" id="modalDuplicate_{{ $tableId }}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formDuplicate_{{ $tableId }}">
      <div class="modal-header" style="background: var(--color-primary); color: white;">
        <h5 class="modal-title">Duplikat Task ID</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="urlQL" value="{{ $urlQL }}">
        <div class="mb-3">
            <label class="form-label">Kode Booking</label>
            <input type="text" class="form-control dup_kodebooking" name="kodebooking" required readonly>
        </div>
        <div class="mb-3">
            <label class="form-label">ID Pendaftaran</label>
            <input type="text" class="form-control dup_idpendaftaran" name="idpendaftaran">
        </div>
        <div class="mb-3">
            <label class="form-label">Task ID</label>
            <input type="number" class="form-control dup_taskid" name="taskid" min="1" max="99" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Waktu (Waktu RS)</label>
            <input type="datetime-local" step="1" class="form-control dup_waktu" name="waktu" required>
            <small class="text-muted">Ubah waktu ini sesuai kebutuhan sebelum disimpan.</small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Task ID Baru</button>
      </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Edit Task ID -->
<div class="modal fade" id="modalEdit_{{ $tableId }}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formEdit_{{ $tableId }}">
      <div class="modal-header" style="background: var(--color-primary); color: white;">
        <h5 class="modal-title">Edit Task ID</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="urlQL" value="{{ $urlQL }}">
        <input type="hidden" class="edit_id" name="id">
        <div class="mb-3">
            <label class="form-label">Kode Booking</label>
            <input type="text" class="form-control edit_kodebooking" name="kodebooking" required readonly>
        </div>
        <div class="mb-3">
            <label class="form-label">ID Pendaftaran</label>
            <input type="text" class="form-control edit_idpendaftaran" name="idpendaftaran">
        </div>
        <div class="mb-3">
            <label class="form-label">Task ID</label>
            <input type="number" class="form-control edit_taskid" name="taskid" min="1" max="99" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Waktu (Waktu RS)</label>
            <input type="datetime-local" step="1" class="form-control edit_waktu" name="waktu" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
      </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.1/xlsx.full.min.js"></script>
<script>
$(document).ready(function () {
    var tableId = '{{ $tableId }}';
    var urlQL = '{{ $urlQL }}';
    var $table = $('#' + tableId);
    var table = $table.DataTable({
        paging: true,
        lengthChange: true,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        responsive: false,
        pageLength: 25,
        order: [[1, 'asc'], [3, 'asc']],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
        columnDefs: [
            { orderable: false, targets: [0, 12, 13, 16] },
            { render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1, targets: 0 }
        ]
    });

    function escapeAttr(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function buildSourceBadge(r) {
        var createdBy = r.created_by || 'Sync SIMRS';
        var updatedBy = r.updated_by || '';
        var lower = String(createdBy).toLowerCase();
        var badgeHtml = '';
        if (lower.indexOf('auto') !== -1) {
            badgeHtml = '<span class="badge" style="background-color: #7c3aed; color: #fff;"><i class="fas fa-magic me-1"></i>' + escapeAttr(createdBy) + '</span>';
        } else if (lower.indexOf('manual') !== -1) {
            badgeHtml = '<span class="badge bg-warning text-dark"><i class="fas fa-user-edit me-1"></i>' + escapeAttr(createdBy) + '</span>';
        } else {
            badgeHtml = '<span class="badge bg-info text-dark"><i class="fas fa-sync-alt me-1"></i>' + escapeAttr(createdBy) + '</span>';
        }
        if (updatedBy && updatedBy !== createdBy) {
            badgeHtml += '<div class="text-muted mt-1" style="font-size: 0.68rem;">Upd: <strong>' + escapeAttr(updatedBy) + '</strong></div>';
        }
        return badgeHtml;
    }

    function buildRowCells(r) {
        var code = parseInt(r.code, 10) || 0;
        var badgeClass = code === 200 ? 'badge-success' : (code === 0 ? 'badge-warning' : 'badge-danger');
        var badgeLabel = code === 200 ? '✓ 200 OK' : (code === 0 ? '⏳ Pending' : '✗ Err ' + r.code);
        var msg = r.message || '';
        if (r.response) {
            try {
                var parsed = JSON.parse(r.response);
                if (parsed && parsed.metadata && parsed.metadata.message) {
                    msg = parsed.metadata.message;
                }
            } catch (e) {}
        }
        var jadwal = (r.jammulai && r.jamakhir) ? (String(r.jammulai).slice(0, 5) + '-' + String(r.jamakhir).slice(0, 5)) : '-';
        var idPend = r.idpendaftaran || '';
        var rawReq = escapeAttr(r.request || '');
        var rawResp = escapeAttr(r.response || '');
        var kbSafe = escapeAttr(r.kodebooking);

        return [
            '',
            kbSafe,
            escapeAttr(r.waktu),
            '<strong>' + escapeAttr(r.taskid) + '</strong>',
            escapeAttr(idPend),
            '<small class="font-weight-bold">' + escapeAttr(r.namadokter || '-') + '</small>',
            '<span class="badge bg-secondary">' + escapeAttr(r.namapoli || '-') + '</span>',
            '<small class="text-nowrap font-weight-bold">' + escapeAttr(jadwal) + '</small>',
            '<span class="status-badge ' + badgeClass + '">' + badgeLabel + '</span>',
            escapeAttr(msg),
            escapeAttr(r.tanggal || ''),
            escapeAttr(r.jam || ''),
            '<button class="btn-json-view" data-json="' + rawReq + '" data-title="Request — ' + escapeAttr(r.taskid) + '"><i class="fas fa-eye"></i> Lihat</button>',
            '<button class="btn-json-view" data-json="' + rawResp + '" data-title="Response — ' + escapeAttr(r.taskid) + '"><i class="fas fa-eye"></i> Lihat</button>',
            escapeAttr(r.reupload != null ? r.reupload : 1),
            buildSourceBadge(r),
            '<button class="btn btn-sm btn-info btn-duplicate" data-kodebooking="' + kbSafe + '" data-taskid="' + escapeAttr(r.taskid) + '" data-waktu="' + escapeAttr(r.waktu) + '" data-idpendaftaran="' + escapeAttr(idPend) + '" title="Duplikat Task ID ini"><i class="fas fa-copy"></i></button>' +
            '<button class="btn btn-sm btn-warning btn-edit ms-1" data-id="' + escapeAttr(r.id) + '" data-kodebooking="' + kbSafe + '" data-taskid="' + escapeAttr(r.taskid) + '" data-waktu="' + escapeAttr(r.waktu) + '" data-idpendaftaran="' + escapeAttr(idPend) + '" title="Edit Task ID ini"><i class="fas fa-edit"></i></button>' +
            '<button class="btn btn-sm btn-success btn-sync-kb ms-1" data-kodebooking="' + kbSafe + '" title="Sinkronisasi Ulang Semua Task ID untuk Kode Booking Ini"><i class="fas fa-sync-alt"></i></button>'
        ];
    }

    function applyRowAttributes($tr, r) {
        $tr.attr('data-row-id', r.id)
           .attr('data-kb', r.kodebooking)
           .attr('data-taskid', r.taskid);
        $tr.find('td').eq(1).addClass('text-nowrap');
        $tr.find('td').eq(2).addClass('text-nowrap');
        $tr.find('td').eq(3).addClass('text-center');
        $tr.find('td').eq(4).addClass('text-nowrap');
        $tr.find('td').eq(7).addClass('text-nowrap');
        $tr.find('td').eq(8).addClass('text-center text-nowrap');
        $tr.find('td').eq(10).addClass('text-nowrap');
        $tr.find('td').eq(11).addClass('text-nowrap');
        $tr.find('td').eq(12).addClass('text-center text-nowrap');
        $tr.find('td').eq(13).addClass('text-center text-nowrap');
        $tr.find('td').eq(14).addClass('text-center');
        $tr.find('td').eq(15).addClass('text-center text-nowrap');
        $tr.find('td').eq(16).addClass('text-center text-nowrap');
    }

    function upsertRowInDataTable(r) {
        if (!r || !r.id) return;
        var cells = buildRowCells(r);
        var existingNode = null;

        table.rows().every(function () {
            var node = this.node();
            if (node && (String($(node).attr('data-row-id')) === String(r.id) ||
                ($(node).attr('data-kb') === String(r.kodebooking) && String($(node).attr('data-taskid')) === String(r.taskid)))) {
                existingNode = node;
            }
        });

        var $targetTr;
        if (existingNode) {
            table.row(existingNode).data(cells).draw(false);
            $targetTr = $(existingNode);
        } else {
            var dtRow = table.row.add(cells).draw(false);
            $targetTr = $(dtRow.node());
        }

        applyRowAttributes($targetTr, r);
        $targetTr.addClass('row-highlight-flash');
        setTimeout(function () {
            $targetTr.removeClass('row-highlight-flash');
        }, 1600);
    }

    function msToLocalInput(ms) {
        var n = parseInt(ms, 10);
        if (isNaN(n)) return '';
        var d = new Date(n);
        var off = d.getTimezoneOffset() * 60000;
        return (new Date(d - off)).toISOString().slice(0, 19);
    }

    $table.on('click', '.btn-duplicate', function() {
        var btn = $(this);
        var modalEl = document.getElementById('modalDuplicate_' + tableId);
        var $modal = $(modalEl);
        $modal.find('.dup_kodebooking').val(btn.attr('data-kodebooking'));
        $modal.find('.dup_idpendaftaran').val(btn.attr('data-idpendaftaran'));
        $modal.find('.dup_taskid').val(btn.attr('data-taskid'));
        $modal.find('.dup_waktu').val(msToLocalInput(btn.attr('data-waktu')));
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    });

    $('#formDuplicate_' + tableId).on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = form.find('button[type="submit"]');
        var original = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...').prop('disabled', true);
        $.ajax({
            url: '{{ url("api/manual-add-taskid") }}',
            type: 'POST',
            data: form.serialize(),
            success: function(res) {
                btn.html(original).prop('disabled', false);
                var modalEl = document.getElementById('modalDuplicate_' + tableId);
                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                if (res && res.response) {
                    upsertRowInDataTable(res.response);
                }
                (window.showGlobalToast || alert)('Berhasil: ' + (res.metadata && res.metadata.message ? res.metadata.message : 'Task ID disimpan'), 'success');
            },
            error: function(err) {
                (window.showGlobalToast || alert)('Gagal menyimpan: ' + (err.responseJSON && err.responseJSON.metadata ? err.responseJSON.metadata.message : 'Error server'), 'error');
                btn.html(original).prop('disabled', false);
            }
        });
    });

    $table.on('click', '.btn-edit', function() {
        var btn = $(this);
        var modalEl = document.getElementById('modalEdit_' + tableId);
        var $modal = $(modalEl);
        $modal.find('.edit_id').val(btn.attr('data-id'));
        $modal.find('.edit_kodebooking').val(btn.attr('data-kodebooking'));
        $modal.find('.edit_idpendaftaran').val(btn.attr('data-idpendaftaran'));
        $modal.find('.edit_taskid').val(btn.attr('data-taskid'));
        $modal.find('.edit_waktu').val(msToLocalInput(btn.attr('data-waktu')));
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    });

    $('#formEdit_' + tableId).on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = form.find('button[type="submit"]');
        var original = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...').prop('disabled', true);
        $.ajax({
            url: '{{ url("api/manual-edit-taskid") }}',
            type: 'POST',
            data: form.serialize(),
            success: function(res) {
                btn.html(original).prop('disabled', false);
                var modalEl = document.getElementById('modalEdit_' + tableId);
                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                if (res && res.response) {
                    upsertRowInDataTable(res.response);
                }
                (window.showGlobalToast || alert)('Berhasil: ' + (res.metadata && res.metadata.message ? res.metadata.message : 'Task ID diperbarui'), 'success');
            },
            error: function(err) {
                (window.showGlobalToast || alert)('Gagal menyimpan: ' + (err.responseJSON && err.responseJSON.metadata ? err.responseJSON.metadata.message : 'Error server'), 'error');
                btn.html(original).prop('disabled', false);
            }
        });
    });

    $table.on('click', '.btn-sync-kb', function() {
        var kb = $(this).attr('data-kodebooking');
        Swal.fire({
            title: 'Sinkronisasi Ulang?',
            text: "Kirim ulang seluruh Task ID milik kode booking " + kb + "?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Kirim!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if(result.isConfirmed) {
                Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                $.ajax({
                    url: '{{ url("api/run-taskid-by-kodebooking") }}?urlQL=' + urlQL + '&kodebooking=' + encodeURIComponent(kb),
                    type: 'GET',
                    success: function(res) {
                        if (res && Array.isArray(res.updated_rows)) {
                            res.updated_rows.forEach(function (rowObj) {
                                upsertRowInDataTable(rowObj);
                            });
                        }
                        Swal.fire({
                            icon: 'success',
                            title: 'Selesai!',
                            text: (res.metadata && res.metadata.message) ? res.metadata.message : 'Berhasil disinkronisasi',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function() {
                        Swal.fire('Error', 'Gagal menyinkronkan data.', 'error');
                    }
                });
            }
        });
    });

    $('#download_excel_' + tableId).on('click', function () {
        var skip = [12, 13, 16];
        var headers = [];
        $table.find('thead th').each(function (i) { if (!skip.includes(i)) headers.push($(this).text().trim()); });
        var rows = [headers];
        table.rows({ search: 'applied' }).every(function () {
            var $tr = $(this.node());
            var line = [];
            $tr.find('td').each(function (i) { if (!skip.includes(i)) line.push($(this).text().trim()); });
            rows.push(line);
        });
        var wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, XLSX.utils.aoa_to_sheet(rows), 'Data TaskID');
        XLSX.writeFile(wb, 'TaskID_' + urlQL + '.xlsx');
    });
});
</script>
@endpush
