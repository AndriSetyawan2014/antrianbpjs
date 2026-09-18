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