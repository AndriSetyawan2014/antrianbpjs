@extends('layouts.admin')

@section('content')

@section('page_title')
    <i class="fas fa-tasks me-2"></i>Data Task ID
@endsection

{{-- Filter Card --}}
<div class="filter-card">
    <i class="fas fa-filter" style="color:var(--color-primary);"></i>
    <form id="filterForm" action="{{ route('taskid.filter') }}" method="GET"
          style="display:contents;">
        <label for="start_date">Dari:</label>
        <input type="date" id="start_date" name="start_date" value="{{ $startDate }}">
        <label for="end_date">Sampai:</label>
        <input type="date" id="end_date" name="end_date" value="{{ $endDate }}">
        <button type="submit" class="btn-filter btn-primary">
            <i class="fas fa-search"></i> Filter
        </button>
        <a href="{{ route('taskid.reset') }}" class="btn-filter btn-secondary">
            <i class="fas fa-undo"></i> Reset
        </a>
    </form>


    <button id="download_excel" class="btn-filter btn-success ms-2">
        <i class="fas fa-file-excel"></i> Download Excel
    </button>
</div>

{{-- Tabel --}}
<div class="table-responsive">
    <table id="data_taskid" class="table table-hover table-striped table-bordered">
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
                <th style="width:8%;">Request</th>
                <th style="width:8%;">Response</th>
                <th style="width:5%;">Reupload</th>
                <th style="width:5%;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($TaskID as $item)
            @php
                $code = $item->code;
                $badgeClass = $code == 200 ? 'badge-success' : ($code == 0 ? 'badge-warning' : 'badge-danger');
                $badgeLabel = $code == 200 ? '✓ 200' : ($code == 0 ? '⏳' : '✗ '.$code);
                $msg = json_decode($item->response)->metadata->message ?? '';
            @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
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
                            data-json="{{ htmlspecialchars($item->request) }}"
                            data-title="Request — {{ $item->taskid }}">
                        <i class="fas fa-eye"></i> Lihat
                    </button>
                </td>
                <td class="text-center">
                    <button class="btn-json-view"
                            data-json="{{ htmlspecialchars($item->response) }}"
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
<div class="modal fade" id="modalDuplicate" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formDuplicate">
      <div class="modal-header">
        <h5 class="modal-title">Duplikat Task ID</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="dup_urlQL" name="urlQL" value="QLJ">
        <div class="mb-3">
            <label class="form-label">Kode Booking</label>
            <input type="text" class="form-control" id="dup_kodebooking" name="kodebooking" required readonly>
        </div>
        <div class="mb-3">
            <label class="form-label">ID Pendaftaran</label>
            <input type="text" class="form-control" id="dup_idpendaftaran" name="idpendaftaran">
        </div>
        <div class="mb-3">
            <label class="form-label">Task ID</label>
            <input type="number" class="form-control" id="dup_taskid" name="taskid" min="1" max="99" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Waktu (Waktu RS)</label>
            <input type="datetime-local" step="1" class="form-control" id="dup_waktu" name="waktu" required>
            <small class="text-muted">Ubah waktu ini sesuai kebutuhan sebelum disimpan.</small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary" id="btnSaveDuplicate"><i class="fas fa-save me-1"></i> Simpan Task ID Baru</button>
      </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.1/xlsx.full.min.js"></script>
<script>
$(document).ready(function () {
    var table = $('#data_taskid').DataTable({
        paging: true,
        lengthChange: true,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        pageLength: 25,
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
        columnDefs: [{ orderable: false, targets: [9, 10, 12] }]
    });

    // Custom filter tanggal
    function formatDateToISO(date) {
        const d = new Date(date);
        return isNaN(d.getTime()) ? null : d.toISOString().split('T')[0];
    }

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        var startDate = $('#start_date').val();
        var endDate   = $('#end_date').val();
        var tableDate = data[7] ? formatDateToISO(data[7].trim()) : null;
        if (!startDate && !endDate) return true;
        if (startDate && !endDate) return tableDate >= startDate;
        if (!startDate && endDate) return tableDate <= endDate;
        return tableDate >= startDate && tableDate <= endDate;
    });



    // Logic for duplicate modal
    $('.btn-duplicate').on('click', function() {
        var btn = $(this);
        $('#dup_kodebooking').val(btn.data('kodebooking'));
        $('#dup_idpendaftaran').val(btn.data('idpendaftaran'));
        $('#dup_taskid').val(btn.data('taskid'));
        
        // Convert timestamp (ms) to YYYY-MM-DDThh:mm:ss
        var ms = parseInt(btn.data('waktu'));
        if(!isNaN(ms)) {
            var d = new Date(ms);
            var tzOffset = d.getTimezoneOffset() * 60000;
            var localISOTime = (new Date(d - tzOffset)).toISOString().slice(0, 19);
            $('#dup_waktu').val(localISOTime);
        } else {
            $('#dup_waktu').val('');
        }
        
        $('#modalDuplicate').modal('show');
    });

    $('#formDuplicate').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#btnSaveDuplicate');
        var originalText = btn.html();
        
        btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...').prop('disabled', true);
        
        $.ajax({
            url: '{{ url("api/manual-add-taskid") }}',
            type: 'POST',
            data: form.serialize(),
            success: function(res) {
                alert('Berhasil: ' + (res.metadata.message || ''));
                $('#modalDuplicate').modal('hide');
                location.reload();
            },
            error: function(err) {
                alert('Gagal menyimpan: ' + (err.responseJSON ? err.responseJSON.metadata.message : 'Error server'));
                btn.html(originalText).prop('disabled', false);
            }
        });
    });

    // Logic for sync by kodebooking
    $('.btn-sync-kb').on('click', function() {
        var kb = $(this).data('kodebooking');
        var urlQL = 'QLJ';

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
                    error: function(err) {
                        Swal.fire('Error', 'Gagal menyinkronkan data.', 'error');
                    }
                });
            }
        });
    });

    $('#download_excel').on('click', function () {
        var wb = XLSX.utils.table_to_book(document.getElementById('data_taskid'), { sheet: "Data TaskID BPJS" });
        XLSX.writeFile(wb, 'TaskID_BPJS.xlsx');
    });


});
</script>
@endpush