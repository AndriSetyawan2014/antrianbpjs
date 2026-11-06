@extends('layouts.admin')

@section('page_title')
    <h1 class="page-title"><i class="fas fa-calendar-check me-2 text-primary"></i>Data Kode Booking</h1>
@endsection

@section('page_subtitle')
    Kelola data kode booking pasien
@endsection

@section('content')
@include('partials.kodebooking-page', [
    'pageTitle' => 'Data Kode Booking',
    'filterAction' => url('/data_kodebooking_jogja'),
    'exportUrl' => url('export-kodebooking'),
    'items' => $data_kodebooking,
    'startDate' => $start_date ?? request('start_date', date('Y-m-d')),
    'endDate' => $end_date ?? request('end_date', date('Y-m-d')),
    'tableId' => 'data_kodebooking',
    'urlQL' => 'QLJ',
])
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    var tableId = '{{ $tableId }}';
    var urlQL = '{{ $urlQL }}';
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
            pMsg = $('<div>').text(pMsg).html();
            var prevBar = '<div class="resend-prev ' + (pOk ? 'ok' : 'err') + '" title="' + pMsg.replace(/"/g, '&quot;') + '">' +
                '<span class="resend-prev-label">Status</span>' +
                '<span class="resend-prev-code">' + pc + '</span>' +
                '<span class="resend-prev-msg">' + pMsg.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</span></div>';

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
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.kodebooking || kb) + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Jenis Pasien</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.jenispasien || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">No Kartu BPJS</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.nomorkartu || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">NIK</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.nik || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">No Telpon</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.nohp || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Kode Poli</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.kodepoli || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Nama Poli</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.namapoli || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Pasien Baru</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + pasienbaruLabel + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">No RM</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.norm || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Tgl Periksa</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.tanggalperiksa || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Kode Dokter</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.kodedokter || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Nama Dokter</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.namadokter || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Jam Praktik</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.jampraktek || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Jenis Kunj.</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + jeniskunjunganLabel + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">No Referensi</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.nomorreferensi || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">No Antrean</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.nomorantrean || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Angka Antrean</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.angkaantrean || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Estimasi Dilayani</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.estimasidilayani != null ? r.estimasidilayani : '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Sisa Kuota JKN</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.sisakuotajkn != null ? r.sisakuotajkn : '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Kuota JKN</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.kuotajkn || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Sisa Kuota Non JKN</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.sisakuotanonjkn || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Kuota Non JKN</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.kuotanonjkn || '') + '" >' +
                '    </div>' +
                '  </div>' +
                '  <div class="col-md-6">' +
                '    <div class="d-flex align-items-center gap-2">' +
                '      <label class="form-label fw-bold text-xs mb-0" style="width: 150px; text-align: right;">Keterangan</label>' +
                '      <input type="text" class="form-control form-control-sm flex-fill" value="' + (r.keterangan || '') + '" >' +
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
                    '</div>' + prevBar +
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