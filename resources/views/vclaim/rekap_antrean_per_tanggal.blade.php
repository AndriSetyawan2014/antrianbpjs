@extends('layouts.admin')

@section('page_title')
    <i class="fas fa-chart-bar me-1" style="color:var(--color-primary);"></i>
    Rekap Antrean Per Tanggal
@endsection

{{-- styles VClaim dimuat global via Vite (resources/css/vclaim.css) --}}

@section('content')
<div class="container-fluid pt-0 pb-0 px-3">

    {{-- Control Bar --}}
    <form method="GET" action="{{ route('vclaim.rekap.antrean.per.tanggal') }}" class="vclaim-control-bar">
        <div class="filter-group">
            <div>
                <label class="form-label"><i class="fas fa-calendar-alt me-1"></i> Bulan</label>
                <select name="bulan" class="form-select" style="min-width: 140px;">
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ sprintf('%02d', $i) }}" {{ $bulan == sprintf('%02d', $i) ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $i, 10)) }}
                        </option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="form-label"><i class="fas fa-calendar me-1"></i> Tahun</label>
                <select name="tahun" class="form-select" style="min-width: 100px;">
                    @for($y = date('Y') - 2; $y <= date('Y'); $y++)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary" style="padding: 8px 16px; font-size:.85rem; border-radius: var(--radius-sm);">
                    <i class="fas fa-filter me-1"></i> Terapkan Filter
                </button>
                <button type="button" class="btn btn-success ms-2" id="btnSyncBulan" style="padding: 8px 16px; font-size:.85rem; border-radius: var(--radius-sm);">
                    <i class="fas fa-sync-alt me-1"></i> Sync 1 Bulan
                </button>
            </div>
        </div>
    </form>

    {{-- Progress Panel (Hidden by default) --}}
    <div id="syncProgressPanel" class="sync-progress-panel" style="display: none; margin-bottom: 20px;">
        <div class="sync-title">
            <span><i class="fas fa-cloud-download-alt me-2"></i> Proses Sync BPJS VClaim (Bulanan)</span>
            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
        </div>
        <div class="sync-summary" id="syncProgressSummary">Memulai...</div>
        <div class="sync-items" id="syncProgressItems"></div>
    </div>

    {{-- Rekap Table --}}
    <div class="table-container">
        <table class="table table-hover table-vclaim mb-0">
            <thead>
                <tr>
                    <th style="width: 150px;">Tanggal</th>
                    @foreach($availableQLs as $ql)
                        <th>{{ strtoupper($ql) }}</th>
                    @endforeach
                    <th style="background: var(--color-primary) !important; color: #ffffff !important;">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekapPaginator as $dateStr => $cols)
                    <tr>
                        <td style="cursor: pointer;" onclick="window.location='{{ route('vclaim.antrol.antrean.per.tanggal', ['tanggal' => $dateStr]) }}'" title="Lihat semua antrean {{ \Carbon\Carbon::parse($dateStr)->format('d M Y') }}">
                            <strong class="text-primary">{{ \Carbon\Carbon::parse($dateStr)->format('d M Y') }}</strong>
                        </td>
                        @foreach($availableQLs as $ql)
                            <td style="cursor: pointer;" onclick="window.location='{{ route('vclaim.antrol.antrean.per.tanggal', ['tanggal' => $dateStr, 'urlQL' => $ql]) }}'" title="Lihat detail antrean {{ strtoupper($ql) }}">
                                @if($cols[$ql] > 0)
                                    <span class="badge bg-success" style="font-size: .8rem; padding: 5px 8px;">{{ number_format($cols[$ql], 0, ',', '.') }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        @endforeach
                        <td style="font-weight: 700; color: var(--color-primary); background: rgba(12,53,106,.1) !important;">
                            {{ number_format($cols['total'], 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($availableQLs) + 2 }}" class="text-center py-4">
                            <div class="text-muted" style="font-size:.9rem;">
                                <i class="fas fa-folder-open mb-2" style="font-size:1.5rem; opacity:.5;"></i><br>
                                Tidak ada data rekap antrean untuk periode ini
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <th>TOTAL KESELURUHAN</th>
                    @foreach($availableQLs as $ql)
                        <th>{{ number_format($totals[$ql], 0, ',', '.') }}</th>
                    @endforeach
                    <th>{{ number_format($grandTotal, 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Pagination Links --}}
    <div class="mt-3 d-flex justify-content-end">
        {{ $rekapPaginator->appends(request()->query())->links('pagination::bootstrap-4') }}
    </div>

</div>

{{-- ── Toast Notifikasi ── --}}
<div id="syncToast" class="sync-toast">
    <div class="toast-body">
        <i id="syncToastIcon" class="fas fa-check-circle text-success fa-lg"></i>
        <span id="syncToastMsg"></span>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnSyncBulan = document.getElementById('btnSyncBulan');
    const progressPanel = document.getElementById('syncProgressPanel');

    async function triggerSyncRange() {
        // Ambil dari selector
        const bulan = document.querySelector('[name="bulan"]').value;
        const tahun = document.querySelector('[name="tahun"]').value;
        
        // Kita hitung hari terakhir bulan tersebut
        const lastDay = new Date(tahun, bulan, 0).getDate();

        setButtonLoading(true);
        progressPanel.style.display = 'block';
        const container = document.getElementById('syncProgressItems');
        container.innerHTML = `
            <div class="ql-progress-item">
                <div class="ql-progress-label">
                    <span class="ql-name" id="syncStatusText">Progress Keseluruhan (0%)</span>
                    <span class="ql-state" id="syncStatusCount">Hari ke-0 dari ${lastDay}</span>
                </div>
                <div class="progress" style="height:12px;">
                    <div id="syncProgressBar" class="progress-bar bg-primary progress-bar-animated"
                         style="width:0%; transition: width .4s ease;"></div>
                </div>
            </div>
        `;
        document.getElementById('syncProgressSummary').textContent = 'Memulai proses sinkronisasi hari per hari...';

        let totalSuccess = 0;
        let totalError = 0;
        let totalRecords = 0;

        // BATCH PROCESSING: Tarik 5 tanggal sekaligus secara bersamaan
        const BATCH_SIZE = 5;
        for (let day = 1; day <= lastDay; day += BATCH_SIZE) {
            const batchPromises = [];
            const endDay = Math.min(day + BATCH_SIZE - 1, lastDay);
            
            document.getElementById('syncProgressSummary').textContent = `Menyinkronkan data tanggal ${day} sampai ${endDay} dari ${lastDay}...`;

            for (let d = day; d <= endDay; d++) {
                const dayStr = d.toString().padStart(2, '0');
                const tgl = `${tahun}-${bulan}-${dayStr}`;
                const params = new URLSearchParams({ tanggal: tgl, sync_now: 1 });
                
                // Masukkan request ke dalam array promise
                const req = fetch(`{{ route('api.vclaim.sync.antrol.antrean') }}?${params}`, { method: 'GET' })
                    .then(res => {
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        return res.json();
                    })
                    .then(data => {
                        // Tidak ada total records yang kembali langsung dari job dispatch, 
                        // tapi kita asumsikan dispatch berhasil
                        totalSuccess++;
                    })
                    .catch(e => {
                        console.error('Error on day ' + tgl, e);
                        totalError++;
                    });
                
                batchPromises.push(req);
            }

            // Tunggu 5 request selesai bersamaan
            await Promise.allSettled(batchPromises);

            // Update progress bar setelah 1 batch selesai
            const percent = Math.round((endDay / lastDay) * 100);
            document.getElementById('syncStatusText').textContent = `Progress Keseluruhan (${percent}%)`;
            document.getElementById('syncStatusCount').textContent = `Selesai: ${endDay}/${lastDay} | Sukses: ${totalSuccess} | Gagal: ${totalError} | Data Ditarik: ${totalRecords}`;
            document.getElementById('syncProgressBar').style.width = `${percent}%`;
        }

        document.getElementById('syncProgressSummary').textContent = `Selesai. Seluruh data bulan ${bulan}-${tahun} berhasil diproses.`;
        
        // Hapus animasi progress bar
        document.getElementById('syncProgressBar').classList.remove('progress-bar-animated');
        document.getElementById('syncProgressBar').classList.replace('bg-primary', 'bg-success');
        
        const spinner = progressPanel.querySelector('.sync-title .spinner-border');
        if(spinner) spinner.style.display = 'none';

        setButtonLoading(false);
        showToast('Sinkronisasi bulan ini telah selesai!', 'success');
        
        // Refresh halaman ke parameter bulan dan tahun yang di-sync setelah 2 detik
        const baseUrl = '{{ route("vclaim.rekap.antrean.per.tanggal") }}';
        setTimeout(() => window.location.href = `${baseUrl}?bulan=${bulan}&tahun=${tahun}`, 2000);
    }

    if (btnSyncBulan) btnSyncBulan.addEventListener('click', triggerSyncRange);

    function setButtonLoading(isLoading) {
        if (btnSyncBulan) {
            btnSyncBulan.disabled = isLoading;
            btnSyncBulan.innerHTML = isLoading
                ? '<span class="spinner-border spinner-border-sm me-1"></span> Proses...'
                : '<i class="fas fa-sync-alt me-1"></i> Sync 1 Bulan';
        }
    }

    function showToast(msg, type = 'success') {
        const toast = document.getElementById('syncToast');
        const icon  = document.getElementById('syncToastIcon');
        const msgEl = document.getElementById('syncToastMsg');
        const icons = {
            success: 'fa-check-circle text-success',
            warning: 'fa-exclamation-triangle text-warning',
            error:   'fa-times-circle text-danger',
        };
        icon.className  = `fas fa-lg ${icons[type] || icons.success}`;
        msgEl.textContent = msg;
        toast.style.display = 'block';
        setTimeout(() => { toast.style.display = 'none'; }, 5000);
    }
});
</script>
@endpush
