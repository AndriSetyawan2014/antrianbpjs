{{-- Partial DRY: halaman Data Task ID (QLJ / QLKP / QLTMG)
  Params: $pageTitle, $filterAction, $resetUrl, $exportUrl, $exportMode ('server'|'client'),
          $items, $startDate, $endDate, $tableId, $urlQL
--}}
{{-- Judul + breadcrumb seragam (pola dashboard/settings) --}}
<div class="page-header">
    <h2 class="page-title"><i class="fas fa-tasks me-2"></i>{{ $pageTitle }}</h2>
    <nav class="breadcrumb-nav" aria-label="breadcrumb">
        <span>Sistem Pemantauan Bridging BPJS</span>
        <i class="fas fa-chevron-right"></i>
        <span>{{ $pageTitle }}</span>
    </nav>
</div>

{{-- Filter Card --}}
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

    @if(($exportMode ?? 'server') === 'server')
    <a href="{{ $exportUrl }}" class="btn-filter btn-success ms-2">
        <i class="fas fa-file-excel"></i> Download Excel
    </a>
    @else
    <button id="download_excel_{{ $tableId }}" class="btn-filter btn-success ms-2">
        <i class="fas fa-file-excel"></i> Download Excel
    </button>
    @endif
</div>

{{-- Tabel --}}
<div class="table-responsive">
    <table id="{{ $tableId }}" class="table table-hover table-striped table-bordered">
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
                <th style="width:8%;" class="text-center">Request</th>
                <th style="width:8%;" class="text-center">Response</th>
                <th style="width:5%;">Reupload</th>
                <th style="width:5%;">Aksi</th>
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
                            data-json="{{ htmlspecialchars($item->request, ENT_QUOTES, 'UTF-8') }}"
                            data-title="Request — {{ $item->taskid }}">
                        <i class="fas fa-eye"></i> Lihat
                    </button>
                </td>
                <td class="text-center">
                    <button class="btn-json-view"
                            data-json="{{ htmlspecialchars($item->response, ENT_QUOTES, 'UTF-8') }}"
                            data-title="Response — {{ $item->taskid }}">
                        <i class="fas fa-eye"></i> Lihat
                    </button>
                </td>
                <td class="text-center">{{ $item->reupload }}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-info btn-duplicate"
                        data-kodebooking="{{ $item->kodebooking }}"
                        data-taskid="{{ $item->taskid }}"
                        data-waktu="{{ $item->waktu }}"
                        data-idpendaftaran="{{ $item->idpendaftaran }}"
                        title="Duplikat Task ID ini">
                        <i class="fas fa-copy"></i>
                    </button>
                    <button class="btn btn-sm btn-warning btn-edit ms-1"
                        data-id="{{ $item->id }}"
                        data-kodebooking="{{ $item->kodebooking }}"
                        data-taskid="{{ $item->taskid }}"
                        data-waktu="{{ $item->waktu }}"
                        data-idpendaftaran="{{ $item->idpendaftaran }}"
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

<!-- Modal Duplicate Task ID -->
<div class="modal fade" id="modalDuplicate_{{ $tableId }}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formDuplicate_{{ $tableId }}">
      <div class="modal-header">
        <h5 class="modal-title">Duplikat Task ID</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
      <div class="modal-header">
        <h5 class="modal-title">Edit Task ID</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
        pageLength: 25,
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
        columnDefs: [
            { orderable: false, targets: [0, 9, 10, 12] },
            { render: (d, t, r, m) => m.row + m.settings._iDisplayStart + 1, targets: 0 }
        ]
    });

    function msToLocalInput(ms) {
        var n = parseInt(ms, 10);
        if (isNaN(n)) return '';
        var d = new Date(n);
        var off = d.getTimezoneOffset() * 60000;
        return (new Date(d - off)).toISOString().slice(0, 19);
    }

    // Duplicate
    $table.on('click', '.btn-duplicate', function() {
        var btn = $(this);
        var modal = $('#modalDuplicate_' + tableId);
        modal.find('.dup_kodebooking').val(btn.data('kodebooking'));
        modal.find('.dup_idpendaftaran').val(btn.data('idpendaftaran'));
        modal.find('.dup_taskid').val(btn.data('taskid'));
        modal.find('.dup_waktu').val(msToLocalInput(btn.data('waktu')));
        modal.modal('show');
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
                (window.showGlobalToast || alert)('Berhasil: ' + (res.metadata.message || ''), 'success');
                $('#modalDuplicate_' + tableId).modal('hide');
                location.reload();
            },
            error: function(err) {
                (window.showGlobalToast || alert)('Gagal menyimpan: ' + (err.responseJSON ? err.responseJSON.metadata.message : 'Error server'), 'error');
                btn.html(original).prop('disabled', false);
            }
        });
    });

    // Edit
    $table.on('click', '.btn-edit', function() {
        var btn = $(this);
        var modal = $('#modalEdit_' + tableId);
        modal.find('.edit_id').val(btn.data('id'));
        modal.find('.edit_kodebooking').val(btn.data('kodebooking'));
        modal.find('.edit_idpendaftaran').val(btn.data('idpendaftaran'));
        modal.find('.edit_taskid').val(btn.data('taskid'));
        modal.find('.edit_waktu').val(msToLocalInput(btn.data('waktu')));
        modal.modal('show');
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
                (window.showGlobalToast || alert)('Berhasil: ' + (res.metadata.message || ''), 'success');
                $('#modalEdit_' + tableId).modal('hide');
                location.reload();
            },
            error: function(err) {
                (window.showGlobalToast || alert)('Gagal menyimpan: ' + (err.responseJSON ? err.responseJSON.metadata.message : 'Error server'), 'error');
                btn.html(original).prop('disabled', false);
            }
        });
    });

    // Sync by kodebooking
    $table.on('click', '.btn-sync-kb', function() {
        var kb = $(this).data('kodebooking');
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
                    url: '{{ url("api/run-taskid-by-kodebooking") }}?urlQL=' + urlQL + '&kodebooking=' + kb,
                    type: 'GET',
                    success: function(res) {
                        Swal.fire('Selesai!', res.metadata.message || 'Berhasil disinkronisasi', 'success').then(() => location.reload());
                    },
                    error: function() {
                        Swal.fire('Error', 'Gagal menyinkronkan data.', 'error');
                    }
                });
            }
        });
    });

    // Export bersih: teks saja, tanpa tombol HTML (kolom Request/Response/Aksi dikecualikan)
    $('#download_excel_' + tableId).on('click', function () {
        var skip = [9, 10, 12];
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
