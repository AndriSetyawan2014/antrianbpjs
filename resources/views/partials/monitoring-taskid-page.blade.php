{{-- Partial: halaman Monitoring Task ID --}}
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
</div>

{{-- Tabel --}}
<div class="table-responsive">
    <table id="{{ $tableId }}" class="table table-hover table-striped table-bordered text-center">
        <thead>
            <tr>
                <th style="width:4%;">No</th>
                <th style="width:15%; text-align: left;">Informasi Antrian</th>
                <th style="width:8%;">T1<br><small>Daftar</small></th>
                <th style="width:8%;">T2<br><small>Poli</small></th>
                <th style="width:8%;">T3<br><small>Layan</small></th>
                <th style="width:8%;">T4<br><small>Selesai Layan</small></th>
                <th style="width:8%;">T5<br><small>Ambil Obat</small></th>
                <th style="width:8%;">T6<br><small>Selesai Obat</small></th>
                <th style="width:8%;">T7<br><small>Selesai</small></th>
                <th style="width:8%;">T99<br><small>Batal</small></th>
                <th style="width:5%;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @php
                $tasks = [1, 2, 3, 4, 5, 6, 7, 99];
            @endphp
            @foreach($kodebookings as $index => $kb)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="text-align: left;">
                        <strong>{{ $kb->kodebooking }}</strong><br>
                        <small>RM: {{ $kb->norm }}</small><br>
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
                                    // Indikator Warna
                                    if (str_contains($msg, 'success') || str_contains($msg, 'ok')) {
                                        $color = '#28a745'; // green
                                        $title = 'Success: ' . $taskData->message;
                                    } elseif (str_contains($msg, 'antrean') || $msg == '') {
                                        $color = '#fd7e14'; // orange (Queue/Pending)
                                        $title = 'Pending/Queue';
                                    } else {
                                        $color = '#dc3545'; // red (Failed)
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
                    <td>
                        <button class="btn btn-sm btn-info" onclick="showDetail('{{ $kb->kodebooking }}')">
                            <i class="fas fa-search"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Modal Detail Task ID --}}
<div class="modal fade" id="modalDetailTask" tabindex="-1" aria-labelledby="modalDetailTaskLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <h5 class="modal-title" id="modalDetailTaskLabel">Detail Task ID - <span id="detailKodebookingTitle"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
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
    
    // Tampilkan modal (asumsi menggunakan Bootstrap 5)
    var modal = new bootstrap.Modal(document.getElementById('modalDetailTask'));
    modal.show();

    // Fetch data
    fetch('{{ url("/monitoring_taskid") }}/' + kodebooking)
        .then(response => response.json())
        .then(data => {
            if(data.metadata.code === 200 && data.response.length > 0) {
                let html = '';
                data.response.forEach(function(task) {
                    let badgeClass = 'bg-danger';
                    if (task.message.toLowerCase().includes('success') || task.message.toLowerCase().includes('ok')) {
                        badgeClass = 'bg-success';
                    } else if (task.message.toLowerCase().includes('antrean') || task.message == '') {
                        badgeClass = 'bg-warning text-dark';
                    }
                    
                    html += `<tr>
                        <td class="text-center"><strong>${task.taskid}</strong></td>
                        <td>${task.waktu}</td>
                        <td><span class="badge ${badgeClass}">${task.message}</span></td>
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

$(document).ready(function() {
    $('#{{ $tableId }}').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "pageLength": 50,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
        }
    });

    // Event listener untuk preset tanggal
    $('.btn-preset').on('click', function() {
        const preset = $(this).data('preset');
        let startDate, endDate;
        const today = new Date();

        // Helper function for local YYYY-MM-DD
        const formatDate = (date) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };

        if (preset === 'today') {
            startDate = formatDate(today);
            endDate = formatDate(today);
        } else if (preset === 'yesterday') {
            const yesterday = new Date(today);
            yesterday.setDate(today.getDate() - 1);
            startDate = formatDate(yesterday);
            endDate = formatDate(yesterday);
        } else if (preset === 'week') {
            const lastWeek = new Date(today);
            lastWeek.setDate(today.getDate() - 6);
            startDate = formatDate(lastWeek);
            endDate = formatDate(today);
        } else if (preset === 'month') {
            startDate = formatDate(new Date(today.getFullYear(), today.getMonth(), 1));
            endDate = formatDate(today);
        }

        $('#start_date').val(startDate);
        $('#end_date').val(endDate);
        $(this).closest('form').submit();
    });
});
</script>
@endpush
