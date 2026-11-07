@extends('layouts.admin')

@section('page_title')
    <h1 class="page-title"><i class="fas fa-user-clock me-2 text-primary"></i>Kesesuaian Jadwal Dokter — Kulon Progo</h1>
@endsection

@section('page_subtitle')
    Perbandingan jadwal dokter HFIS BPJS vs waktu pelayanan RS
@endsection

@section('content')

{{-- KPI Summary Cards --}}
<div class="row mb-3">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm border-start border-primary border-4 h-100 py-2">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Jadwal Praktik</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800">{{ number_format($totalJadwal) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-alt fa-2x text-gray-300" style="opacity:0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm border-start border-success border-4 h-100 py-2">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Sesuai Jadwal BPJS</div>
                        <div class="h4 mb-0 font-weight-bold text-success">{{ number_format($totalSesuai) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-success" style="opacity:0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm border-start border-danger border-4 h-100 py-2">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Tidak Sesuai</div>
                        <div class="h4 mb-0 font-weight-bold text-danger">{{ number_format($totalTidakSesuai) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-times-circle fa-2x text-danger" style="opacity:0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-0 shadow-sm border-start border-info border-4 h-100 py-2">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Tingkat Kesesuaian</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $persenSesuai }}%</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-pie fa-2x text-info" style="opacity:0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filter Card --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('qlkp_kesesuaian_jadwal') }}" class="row g-2 align-items-center" id="formFilterKesesuaian">
            <div class="col-auto">
                <label for="start_date" class="col-form-label"><i class="fas fa-calendar-day me-1 text-primary"></i> Dari:</label>
            </div>
            <div class="col-auto">
                <input type="date" id="start_date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
            </div>
            <div class="col-auto">
                <label for="end_date" class="col-form-label">Sampai:</label>
            </div>
            <div class="col-auto">
                <input type="date" id="end_date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
            </div>
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Semua Status --</option>
                    <option value="SESUAI" {{ ($statusFilter ?? '') === 'SESUAI' ? 'selected' : '' }}>SESUAI</option>
                    <option value="TIDAK SESUAI" {{ ($statusFilter ?? '') === 'TIDAK SESUAI' ? 'selected' : '' }}>TIDAK SESUAI</option>
                </select>
            </div>
            <div class="col-auto">
                <select name="namadokter" class="form-select form-select-sm" style="max-width: 200px;">
                    <option value="">-- Semua Dokter --</option>
                    @foreach($listDokter as $dok)
                        <option value="{{ $dok }}" {{ ($dokterFilter ?? '') === $dok ? 'selected' : '' }}>{{ $dok }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="nmpoli" class="form-select form-select-sm" style="max-width: 170px;">
                    <option value="">-- Semua Poli --</option>
                    @foreach($listPoli as $p)
                        <option value="{{ $p }}" {{ ($poliFilter ?? '') === $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
                <a href="{{ route('qlkp_kesesuaian_jadwal') }}" class="btn btn-sm btn-secondary ms-1">
                    <i class="fas fa-undo me-1"></i> Reset
                </a>
            </div>
            <div class="col-auto ms-auto d-flex gap-1 align-items-center flex-nowrap">
                <button type="button" class="btn btn-sm btn-outline-secondary btn-preset" data-preset="month">Bulan ini</button>
                <button type="button" id="btnSyncJadwalBpjs" class="btn btn-sm btn-info text-white ms-1" title="Tarik jadwal dokter resmi HFIS dari server BPJS Antrol">
                    <i class="fas fa-cloud-download-alt me-1"></i> Tarik Jadwal BPJS
                </button>
                <button type="button" id="btnDownloadExcel" class="btn btn-sm btn-success ms-1">
                    <i class="fas fa-file-excel me-1"></i> Download Excel
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Data Table --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-table me-2"></i>Laporan Kesesuaian Jadwal Dokter BPJS (HFIS) vs Waktu Pelayanan RS</h6>
            <small class="text-muted">Waktu Buka dan Tutup bersumber dari Bridging Jadwal BPJS (HFIS)</small>
        </div>
        <span class="badge bg-light text-muted border">Kaidah BPJS: Toleransi mulai max 60 menit sebelum jam buka s/d jam tutup</span>
    </div>
    <div class="card-body p-2">
        <div class="table-responsive">
            <table id="tableKesesuaian" class="table table-hover table-striped table-bordered align-middle" style="width:100%; font-size:0.875rem;">
                <thead class="table-light text-nowrap">
                    <tr>
                        <th style="width: 3%;">No</th>
                        <th>Bulan Upd</th>
                        <th>Nmkc</th>
                        <th>Nmppk</th>
                        <th>Nama Dokter</th>
                        <th>Nmpoli</th>
                        <th>Month of Periode</th>
                        <th>Day, Month, Year</th>
                        <th>Nama Hari</th>
                        <th class="text-center" title="Jam Buka Jadwal Dokter dari Bridging BPJS (HFIS)">Buka</th>
                        <th class="text-center" title="Jam Tutup Jadwal Dokter dari Bridging BPJS (HFIS)">Tutup</th>
                        <th>Kode Booking</th>
                        <th>No BPJS</th>
                        <th>Nama Pasien</th>
                        <th class="text-center" title="Waktu riil pelayanan pertama Task ID 4 di RS">wakturs</th>
                        <th class="text-center">KESESUAIAN</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($results as $index => $row)
                    @php
                        $isSesuai = ($row->kesesuaian === 'SESUAI');
                        $badgeClass = $isSesuai ? 'bg-success' : 'bg-danger';
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ $row->bulan_upd }}</td>
                        <td>{{ $row->nmkc }}</td>
                        <td>{{ $row->nmppk }}</td>
                        <td class="font-weight-bold">{{ $row->namadokter }}</td>
                        <td><span class="badge bg-secondary">{{ $row->nmpoli }}</span></td>
                        <td class="text-center">{{ $row->month_periode }}</td>
                        <td class="text-center">{{ $row->day_month_year }}</td>
                        <td class="text-center">{{ $row->nama_hari }}</td>
                        <td class="text-center font-weight-bold">
                            {{ $row->buka }}
                            @if(isset($row->sumber_jadwal) && $row->sumber_jadwal === 'BPJS HFIS')
                                <span class="badge bg-primary text-white" style="font-size:0.6rem; vertical-align:middle;" title="Jadwal resmi dari HFIS BPJS">BPJS</span>
                            @endif
                        </td>
                        <td class="text-center font-weight-bold">{{ $row->tutup }}</td>
                        <td>
                            @if($row->kodebooking !== '-')
                                <span class="badge bg-light text-dark border font-monospace">{{ $row->kodebooking }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($row->no_bpjs !== '-')
                                <code class="text-dark">{{ $row->no_bpjs }}</code>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($row->nama_pasien !== '-')
                                <span class="font-weight-bold">{{ $row->nama_pasien }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center {{ $row->wakturs === 'Tidak Ada' ? 'text-muted fst-italic' : 'text-primary font-weight-bold' }}">
                            {{ $row->wakturs }}
                        </td>
                        <td class="text-center text-nowrap">
                            <span class="badge {{ $badgeClass }} px-2 py-1" title="{{ $row->keterangan }}">
                                <i class="fas {{ $isSesuai ? 'fa-check' : 'fa-times' }} me-1"></i>{{ $row->kesesuaian }}
                            </span>
                            @if(!$isSesuai && $row->wakturs !== 'Tidak Ada')
                                <div class="text-muted" style="font-size:0.75rem;">{{ $row->keterangan }}</div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.1/xlsx.full.min.js"></script>
<script>
$(document).ready(function () {
    var table = $('#tableKesesuaian').DataTable({
        paging: true,
        lengthChange: true,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        pageLength: 25,
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
        columnDefs: [
            { orderable: false, targets: 0 }
        ]
    });

    // Preset tanggal
    $('.btn-preset').on('click', function() {
        var preset = $(this).data('preset');
        var today = new Date();
        var formatDate = function(d) {
            var month = '' + (d.getMonth() + 1),
                day = '' + d.getDate(),
                year = d.getFullYear();
            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;
            return [year, month, day].join('-');
        };

        if (preset === 'today') {
            var t = formatDate(today);
            $('#start_date').val(t);
            $('#end_date').val(t);
        } else if (preset === 'month') {
            var firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            $('#start_date').val(formatDate(firstDay));
            $('#end_date').val(formatDate(today));
        }
        $(this).closest('form').submit();
    });

    // Tarik Jadwal resmi dari BPJS (HFIS)
    $('#btnSyncJadwalBpjs').on('click', function() {
        var start = $('#start_date').val();
        var end = $('#end_date').val();

        Swal.fire({
            title: 'Tarik Jadwal dari BPJS Antrol?',
            html: 'Sistem akan mengambil jadwal buka & tutup resmi dokter (HFIS) langsung dari server BPJS untuk periode:<br><b>' + start + ' s/d ' + end + '</b>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-cloud-download-alt me-1"></i> Ya, Tarik Jadwal',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menarik Jadwal dari BPJS...',
                    text: 'Sedang menghubungi endpoint /jadwaldokter BPJS Antrol...',
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{{ route("qlkp_sync_jadwal_bpjs") }}',
                    type: 'GET',
                    data: { start_date: start, end_date: end },
                    success: function(res) {
                        Swal.fire({
                            title: 'Sinkronisasi Berhasil!',
                            text: res.metadata.message || 'Jadwal dokter BPJS berhasil diperbarui.',
                            icon: 'success'
                        }).then(function() {
                            location.reload();
                        });
                    },
                    error: function(err) {
                        Swal.fire({
                            title: 'Gagal Menarik Jadwal',
                            text: (err.responseJSON && err.responseJSON.metadata) ? err.responseJSON.metadata.message : 'Terjadi kendala saat menghubungi server BPJS.',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    });

    // Export Excel
    $('#btnDownloadExcel').on('click', function () {
        var headers = [
            'Bulan Upd',
            'Nmkc',
            'Nmppk',
            'Nama Dokter',
            'Nmpoli',
            'Month of Periode Jadwal Dokter',
            'Day, Month, Year of Periode Jadwal Dokter',
            'Nama Hari',
            'Buka',
            'Tutup',
            'Kode Booking',
            'No BPJS',
            'Nama Pasien',
            'wakturs',
            'KESESUAIAN'
        ];

        var rows = [headers];
        table.rows({ search: 'applied' }).every(function () {
            var $tr = $(this.node());
            var $tds = $tr.find('td');
            rows.push([
                $tds.eq(1).text().trim(),
                $tds.eq(2).text().trim(),
                $tds.eq(3).text().trim(),
                $tds.eq(4).text().trim(),
                $tds.eq(5).text().trim(),
                $tds.eq(6).text().trim(),
                $tds.eq(7).text().trim(),
                $tds.eq(8).text().trim(),
                $tds.eq(9).text().replace('BPJS', '').trim(),
                $tds.eq(10).text().trim(),
                $tds.eq(11).text().trim(),
                $tds.eq(12).text().trim(),
                $tds.eq(13).text().trim(),
                $tds.eq(14).text().trim(),
                $tds.eq(15).find('.badge').text().trim()
            ]);
        });

        var wb = XLSX.utils.book_new();
        var ws = XLSX.utils.aoa_to_sheet(rows);
        XLSX.utils.book_append_sheet(wb, ws, 'Kesesuaian Jadwal');
        XLSX.writeFile(wb, 'Laporan_Kesesuaian_Jadwal_Dokter_QLKP.xlsx');
    });
});
</script>
@endpush
