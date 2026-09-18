@extends('layouts.admin')

{{-- index.css sudah dimuat global via @vite di layouts/admin --}}
@section('content')

{{-- ── Page Header ── --}}
<div class="page-header">
    <h2 class="page-title"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h2>
    <nav class="breadcrumb-nav">
        <span>Sistem Pemantauan Bridging BPJS</span>
        <i class="fas fa-chevron-right"></i>
        <span>Dashboard</span>
    </nav>
</div>

{{-- ── Control Bar ── --}}
<div class="control-bar">
    <div class="control-bar-left">
        <div class="control-item">
            <label for="regionSelector"><i class="fas fa-map-marker-alt"></i> Wilayah:</label>
            <select id="regionSelector">
                <option value="yogyakarta">Queen Latifa Yogyakarta</option>
                <option value="kulonprogo">Queen Latifa Kulon Progo</option>
                <option value="temanggung">Queen Latifa Temanggung</option>
            </select>
        </div>
        {{-- Yogyakarta Filter --}}
        <div id="filter-wrap-yogyakarta" class="control-item region-filter">
            <label for="filterDateYogyakarta"><i class="fas fa-calendar-alt"></i> Tanggal:</label>
            <input type="date" id="filterDateYogyakarta">
        </div>
        {{-- Kulon Progo Filter --}}
        <div id="filter-wrap-kulonprogo" class="control-item region-filter hidden">
            <label for="filterDateKulonProgo"><i class="fas fa-calendar-alt"></i> Tanggal:</label>
            <input type="date" id="filterDateKulonProgo">
        </div>
        {{-- Temanggung Filter --}}
        <div id="filter-wrap-temanggung" class="control-item region-filter hidden">
            <label for="filterDateTemanggung"><i class="fas fa-calendar-alt"></i> Tanggal:</label>
            <input type="date" id="filterDateTemanggung">
        </div>
    </div>
    <div class="control-bar-right">
        <button id="sendButton" class="btn-filter btn-primary">
            <i class="fas fa-paper-plane"></i> Kirim Pesan
        </button>
        <button onclick="location.reload()" class="btn-filter btn-secondary">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
    </div>
</div>

{{-- ── Status operasional (queue worker) ── --}}
<div class="control-bar" id="queueStatusBar" style="margin-top:-8px;">
    <div class="control-item">
        <i class="fas fa-server" style="color:var(--color-primary);"></i>
        <span id="queueStatusText" style="font-size:13px;color:var(--color-text-secondary);">Memeriksa status queue…</span>
    </div>
    <div class="control-bar-right">
        <a href="{{ route('settings.index') }}" class="btn-filter btn-secondary" style="text-decoration:none;">
            <i class="fas fa-cogs"></i> Kelola Queue
        </a>
    </div>
</div>

@include('partials.dashboard-region', [
    'regionId' => 'yogyakarta',
    'hidden' => false,
    'chartSuffix' => '',
    'routeKodebooking' => route('data_kodebooking'),
    'routeRekapKodebooking' => route('rekap_kodebooking'),
    'routeTaskid' => route('TaskID'),
    'routeRekapTaskid' => route('rekap_taskid'),
    'countKodebooking' => $totalKodebooking,
    'countRekapKodebooking' => $rekapKodebooking,
    'countTaskid' => $totalTaskId,
    'countRekapTaskid' => $rekapTaskId,
    'barId' => 'patientChartOverall',
    'pieId' => 'patientPieChart',
])

@include('partials.dashboard-region', [
    'regionId' => 'kulonprogo',
    'hidden' => true,
    'chartSuffix' => ' — Kulon Progo',
    'routeKodebooking' => route('qlkp_data_kodebooking'),
    'routeRekapKodebooking' => route('qlkp_rekap_kodebooking'),
    'routeTaskid' => route('qlkp_TaskID'),
    'routeRekapTaskid' => route('qlkp_rekap_taskid'),
    'countKodebooking' => $qlkptotalKodebooking ?? '—',
    'countRekapKodebooking' => $qlkprekapKodebooking ?? '—',
    'countTaskid' => $qlkptotalTaskId ?? '—',
    'countRekapTaskid' => $qlkprekapTaskId ?? '—',
    'barId' => 'patientChartKulonProgoOverall',
    'pieId' => 'patientPieChartKulonProgo',
])

@include('partials.dashboard-region', [
    'regionId' => 'temanggung',
    'hidden' => true,
    'chartSuffix' => ' — Temanggung',
    'routeKodebooking' => route('qltmg_data_kodebooking'),
    'routeRekapKodebooking' => route('qltmg_rekap_kodebooking'),
    'routeTaskid' => route('qltmg_TaskID'),
    'routeRekapTaskid' => route('qltmg_rekap_taskid'),
    'countKodebooking' => $qltmgtotalKodebooking ?? '—',
    'countRekapKodebooking' => $qltmgrekapKodebooking ?? '—',
    'countTaskid' => $qltmgtotalTaskId ?? '—',
    'countRekapTaskid' => $qltmgrekapTaskId ?? '—',
    'barId' => 'patientChartTemanggungOverall',
    'pieId' => 'patientPieChartTemanggung',
])
{{-- ── Tren 7 hari terakhir (mengikuti wilayah & tanggal aktif) ── --}}
<div class="row">
    <div class="col-12">
        <div class="chart-card">
            <div class="chart-card-header">
                <i class="fas fa-chart-line"></i>&nbsp;<span id="trendTitle">Tren 7 Hari Terakhir</span>
                <span id="trendLoading" class="ms-auto" style="font-weight:400;font-size:11px;opacity:.85;display:none;">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memuat…
                </span>
            </div>
            <div class="chart-card-body">
                <div class="chart-container" id="trendWrap" style="height:220px;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ── Palet warna tetap (tidak lagi random) ──
const CHART_PALETTE = [
    '#0C356A','#FFC436','#1E8449','#9B59B6',
    '#C0392B','#F39C12','#1A6FA0','#1ABC9C',
    '#E67E22','#2ECC71','#8E44AD','#3498DB'
];

// ── Helper tanggal lokal (hindari geser hari karena UTC) ──
function localToday() {
    const d = new Date();
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}
function fetchJson(url, onOk) {
    return fetch(url)
        .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(onOk)
        .catch(err => {
            console.error('Gagal memuat data chart:', err);
            if (window.showGlobalToast) window.showGlobalToast('Gagal memuat data chart. Coba refresh.', 'error');
        });
}

// ── Chart generik: 1 fungsi untuk 3 wilayah (ganti 3x duplikat) ──
const barCharts = {};

function makeBarChart(canvasId, labels, values) {
    if (barCharts[canvasId]) barCharts[canvasId].destroy();
    const ctx = document.getElementById(canvasId).getContext('2d');
    barCharts[canvasId] = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Jumlah Pasien',
                data: values,
                backgroundColor: values.map((_, i) => CHART_PALETTE[i % CHART_PALETTE.length]),
                borderRadius: 5,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1A202C',
                    bodyFont: { family: 'Poppins' },
                    callbacks: { label: c => `Jumlah: ${c.raw}` }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: {
                        font: { family: 'Poppins', size: 11 },
                        stepSize: 1,
                        callback: value => Number.isInteger(value) ? value : null
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'Poppins', size: 11 } }
                }
            }
        }
    });
}

function initPie(canvasId, valBooking, valTask) {
    new Chart(document.getElementById(canvasId).getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Kode Booking', 'Task ID'],
            datasets: [{
                data: [valBooking, valTask],
                backgroundColor: ['#0C356A', '#FFC436'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true, position: 'bottom',
                    labels: { font: { family: 'Poppins', size: 12 }, padding: 16 }
                },
                tooltip: { backgroundColor: '#1A202C', bodyFont: { family: 'Poppins' } }
            },
            cutout: '55%'
        }
    });
}

// Konfigurasi per wilayah: [pieId, barId, filterInputId, urlFetch, valBooking, valTask]
const REGIONS = [
    { pie: 'patientPieChart', bar: 'patientChartOverall', input: 'filterDateYogyakarta',
      url: `{{ url('/get-patient-data') }}`, booking: {{ $totalKodebooking }}, task: {{ $totalTaskId }} },
    { pie: 'patientPieChartKulonProgo', bar: 'patientChartKulonProgoOverall', input: 'filterDateKulonProgo',
      url: `{{ url('/get-patient-data-kulonprogo') }}`, booking: {{ $qlkptotalKodebooking ?? 0 }}, task: {{ $qlkptotalTaskId ?? 0 }} },
    { pie: 'patientPieChartTemanggung', bar: 'patientChartTemanggungOverall', input: 'filterDateTemanggung',
      url: `{{ url('/get-patient-data-temanggung') }}`, booking: {{ $qltmgtotalKodebooking ?? 0 }}, task: {{ $qltmgtotalTaskId ?? 0 }} },
];

REGIONS.forEach(r => {
    initPie(r.pie, r.booking, r.task);
    const load = (date) => fetchJson(`${r.url}?date=${date}`, d => makeBarChart(r.bar, d.labels, d.values));
    const input = document.getElementById(r.input);
    input.addEventListener('change', e => { if (e.target.value) load(e.target.value); });
    const t = localToday();
    input.value = t;
    input.max = t;
    load(t);
});


// ── Region Switcher (diingat via localStorage) ──
const regionSelector = document.getElementById('regionSelector');
function showRegion(selectedRegion) {
    document.querySelectorAll('.region-content').forEach(el => el.classList.add('hidden'));
    document.getElementById(selectedRegion)?.classList.remove('hidden');
    document.querySelectorAll('.region-filter').forEach(el => el.classList.add('hidden'));
    document.getElementById(`filter-wrap-${selectedRegion}`)?.classList.remove('hidden');
}
regionSelector.addEventListener('change', function() {
    showRegion(this.value);
    try { localStorage.setItem('ql_region', this.value); } catch (e) {}
});
try {
    const saved = localStorage.getItem('ql_region');
    if (saved && document.getElementById(saved)) {
        regionSelector.value = saved;
        showRegion(saved);
    }
} catch (e) {}

// ── Tren 7 hari: total pasien per hari untuk wilayah & tanggal aktif ──
let trendChart = null;
const REGION_INDEX = { yogyakarta: 0, kulonprogo: 1, temanggung: 2 };
const REGION_NAMES = { yogyakarta: 'Yogyakarta', kulonprogo: 'Kulon Progo', temanggung: 'Temanggung' };
function shiftISO(dateStr, delta) {
    const d = new Date(dateStr + 'T00:00:00');
    d.setDate(d.getDate() + delta);
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}
async function loadTrend() {
    const key = regionSelector.value || 'yogyakarta';
    const cfg = REGIONS[REGION_INDEX[key] ?? 0];
    const endInput = document.getElementById(cfg.input);
    const end = (endInput && endInput.value) || localToday();
    const days = Array.from({ length: 7 }, (_, i) => shiftISO(end, i - 6));
    const wrap = document.getElementById('trendWrap');
    const loading = document.getElementById('trendLoading');
    document.getElementById('trendTitle').textContent =
        `Tren 7 Hari — ${REGION_NAMES[key] ?? key} (s/d ${end})`;
    if (wrap) wrap.classList.add('is-loading');
    if (loading) loading.style.display = '';
    try {
        const totals = await Promise.all(days.map(dt =>
            fetch(`${cfg.url}?date=${dt}`)
                .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
                .then(d => (d.values || []).reduce((a, b) => a + (Number(b) || 0), 0))
                .catch(() => null)
        ));
        const primary = getComputedStyle(document.documentElement).getPropertyValue('--color-primary').trim() || '#0C356A';
        if (trendChart) trendChart.destroy();
        trendChart = new Chart(document.getElementById('trendChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: days.map(d => new Date(d + 'T00:00:00').toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })),
                datasets: [{
                    label: 'Total pasien',
                    data: totals,
                    borderColor: primary,
                    backgroundColor: primary + '22',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4,
                    pointBackgroundColor: primary,
                    spanGaps: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { backgroundColor: '#1A202C', bodyFont: { family: 'Poppins' } }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: { font: { family: 'Poppins', size: 11 }, precision: 0 }
                    },
                    x: { grid: { display: false }, ticks: { font: { family: 'Poppins', size: 11 } } }
                }
            }
        });
    } finally {
        if (wrap) wrap.classList.remove('is-loading');
        if (loading) loading.style.display = 'none';
    }
}
regionSelector.addEventListener('change', () => loadTrend());
['filterDateYogyakarta', 'filterDateKulonProgo', 'filterDateTemanggung'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('change', () => loadTrend());
});
loadTrend();

// ── Status queue (ringkas, tanpa timeout dashboard) ──
(function loadQueueStatus() {
    const el = document.getElementById('queueStatusText');
    if (!el) return;
    fetch(`{{ url('/api/queue-status') }}`)
        .then(r => r.json())
        .then(data => {
            const pending = data?.response?.pending ?? data?.pending ?? null;
            if (pending === null) {
                el.textContent = data?.metadata?.message || 'Status queue tidak tersedia.';
                return;
            }
            const n = Number(pending);
            el.innerHTML = n > 0
                ? `<strong style="color:var(--color-warning);">${n} job menunggu</strong> di queue — pastikan worker berjalan.`
                : `<span style="color:var(--color-success);font-weight:600;">Queue kosong</span> — worker siap.`;
        })
        .catch(() => { el.textContent = 'Gagal memuat status queue.'; });
})();
</script>
@endpush