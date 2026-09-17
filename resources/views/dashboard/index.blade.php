@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endpush

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

{{-- ══════════════════════════════════════════
     YOGYAKARTA SECTION
═════════════════════════════════════════════ --}}
<div id="yogyakarta" class="region-content">

    {{-- Stat Cards --}}
    <div class="row mb-3">
        <div class="col-sm-6 col-lg-3">
            <a href="{{ route('data_kodebooking') }}" class="stat-card card-kode-booking">
                <div class="stat-card-label"><i class="fas fa-calendar-check me-1"></i>Kode Booking</div>
                <div class="stat-card-value">{{ $totalKodebooking }}</div>
                <div class="stat-card-sub"><i class="fas fa-arrow-right"></i> Lihat Detail</div>
                <div class="stat-card-icon-bg"><i class="fas fa-calendar-check"></i></div>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3">
            <a href="{{ route('rekap_kodebooking') }}" class="stat-card card-rekap-booking">
                <div class="stat-card-label"><i class="fas fa-chart-bar me-1"></i>Rekap Kode Booking</div>
                <div class="stat-card-value">{{ $rekapKodebooking }}</div>
                <div class="stat-card-sub"><i class="fas fa-arrow-right"></i> Lihat Detail</div>
                <div class="stat-card-icon-bg"><i class="fas fa-chart-bar"></i></div>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3">
            <a href="{{ route('TaskID') }}" class="stat-card card-task-id">
                <div class="stat-card-label"><i class="fas fa-tasks me-1"></i>Task ID</div>
                <div class="stat-card-value">{{ $totalTaskId }}</div>
                <div class="stat-card-sub"><i class="fas fa-arrow-right"></i> Lihat Detail</div>
                <div class="stat-card-icon-bg"><i class="fas fa-tasks"></i></div>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3">
            <a href="{{ route('rekap_taskid') }}" class="stat-card card-rekap-taskid">
                <div class="stat-card-label"><i class="fas fa-clipboard-list me-1"></i>Rekap Task ID</div>
                <div class="stat-card-value">{{ $rekapTaskId }}</div>
                <div class="stat-card-sub"><i class="fas fa-arrow-right"></i> Lihat Detail</div>
                <div class="stat-card-icon-bg"><i class="fas fa-clipboard-list"></i></div>
            </a>
        </div>
    </div>

    {{-- Charts --}}
    <div class="row">
        <div class="col-md-7">
            <div class="chart-card">
                <div class="chart-card-header">
                    <i class="fas fa-chart-bar"></i> Jumlah Pasien per Poli
                </div>
                <div class="chart-card-body">
                    <div class="chart-container">
                        <canvas id="patientChartOverall"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="chart-card">
                <div class="chart-card-header">
                    <i class="fas fa-chart-pie"></i> Distribusi Data
                </div>
                <div class="chart-card-body">
                    <div class="chart-container">
                        <canvas id="patientPieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════
     KULON PROGO SECTION
═════════════════════════════════════════════ --}}
<div id="kulonprogo" class="region-content hidden">

    {{-- Stat Cards --}}
    <div class="row mb-3">
        <div class="col-sm-6 col-lg-3">
            <a href="{{ route('qlkp_data_kodebooking') }}" class="stat-card card-kode-booking">
                <div class="stat-card-label"><i class="fas fa-calendar-check me-1"></i>Kode Booking</div>
                <div class="stat-card-value">{{ $qlkptotalKodebooking ?? '—' }}</div>
                <div class="stat-card-sub"><i class="fas fa-arrow-right"></i> Lihat Detail</div>
                <div class="stat-card-icon-bg"><i class="fas fa-calendar-check"></i></div>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3">
            <a href="{{ route('qlkp_rekap_kodebooking') }}" class="stat-card card-rekap-booking">
                <div class="stat-card-label"><i class="fas fa-chart-bar me-1"></i>Rekap Kode Booking</div>
                <div class="stat-card-value">{{ $qlkprekapKodebooking ?? '—' }}</div>
                <div class="stat-card-sub"><i class="fas fa-arrow-right"></i> Lihat Detail</div>
                <div class="stat-card-icon-bg"><i class="fas fa-chart-bar"></i></div>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3">
            <a href="{{ route('qlkp_TaskID') }}" class="stat-card card-task-id">
                <div class="stat-card-label"><i class="fas fa-tasks me-1"></i>Task ID</div>
                <div class="stat-card-value">{{ $qlkptotalTaskId ?? '—' }}</div>
                <div class="stat-card-sub"><i class="fas fa-arrow-right"></i> Lihat Detail</div>
                <div class="stat-card-icon-bg"><i class="fas fa-tasks"></i></div>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3">
            <a href="{{ route('qlkp_rekap_taskid') }}" class="stat-card card-rekap-taskid">
                <div class="stat-card-label"><i class="fas fa-clipboard-list me-1"></i>Rekap Task ID</div>
                <div class="stat-card-value">{{ $qlkprekapTaskId ?? '—' }}</div>
                <div class="stat-card-sub"><i class="fas fa-arrow-right"></i> Lihat Detail</div>
                <div class="stat-card-icon-bg"><i class="fas fa-clipboard-list"></i></div>
            </a>
        </div>
    </div>


    {{-- Charts --}}
    <div class="row">
        <div class="col-md-7">
            <div class="chart-card">
                <div class="chart-card-header">
                    <i class="fas fa-chart-bar"></i> Jumlah Pasien per Poli — Kulon Progo
                </div>
                <div class="chart-card-body">
                    <div class="chart-container">
                        <canvas id="patientChartKulonProgoOverall"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="chart-card">
                <div class="chart-card-header">
                    <i class="fas fa-chart-pie"></i> Distribusi Data
                </div>
                <div class="chart-card-body">
                    <div class="chart-container">
                        <canvas id="patientPieChartKulonProgo"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════
     TEMANGGUNG SECTION (placeholder)
═════════════════════════════════════════════ --}}
<div id="temanggung" class="region-content hidden">
    <div class="row mb-3">
        <div class="col-sm-6 col-lg-3">
            <a href="{{ route('qltmg_data_kodebooking') }}" class="stat-card card-kode-booking">
                <div class="stat-card-label"><i class="fas fa-calendar-check me-1"></i>Kode Booking</div>
                <div class="stat-card-value">{{ $qltmgtotalKodebooking ?? '—' }}</div>
                <div class="stat-card-sub"><i class="fas fa-arrow-right"></i> Lihat Detail</div>
                <div class="stat-card-icon-bg"><i class="fas fa-calendar-check"></i></div>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3">
            <a href="{{ route('qltmg_rekap_kodebooking') }}" class="stat-card card-rekap-booking">
                <div class="stat-card-label"><i class="fas fa-chart-bar me-1"></i>Rekap Kode Booking</div>
                <div class="stat-card-value">{{ $qltmgrekapKodebooking ?? '—' }}</div>
                <div class="stat-card-sub"><i class="fas fa-arrow-right"></i> Lihat Detail</div>
                <div class="stat-card-icon-bg"><i class="fas fa-chart-bar"></i></div>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3">
            <a href="{{ route('qltmg_TaskID') }}" class="stat-card card-task-id">
                <div class="stat-card-label"><i class="fas fa-tasks me-1"></i>Task ID</div>
                <div class="stat-card-value">{{ $qltmgtotalTaskId ?? '—' }}</div>
                <div class="stat-card-sub"><i class="fas fa-arrow-right"></i> Lihat Detail</div>
                <div class="stat-card-icon-bg"><i class="fas fa-tasks"></i></div>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3">
            <a href="{{ route('qltmg_rekap_taskid') }}" class="stat-card card-rekap-taskid">
                <div class="stat-card-label"><i class="fas fa-clipboard-list me-1"></i>Rekap Task ID</div>
                <div class="stat-card-value">{{ $qltmgrekapTaskId ?? '—' }}</div>
                <div class="stat-card-sub"><i class="fas fa-arrow-right"></i> Lihat Detail</div>
                <div class="stat-card-icon-bg"><i class="fas fa-clipboard-list"></i></div>
            </a>
        </div>
    </div>


    {{-- Charts --}}
    <div class="row">
        <div class="col-md-7">
            <div class="chart-card">
                <div class="chart-card-header">
                    <i class="fas fa-chart-bar"></i> Jumlah Pasien per Poli — Temanggung
                </div>
                <div class="chart-card-body">
                    <div class="chart-container">
                        <canvas id="patientChartTemanggungOverall"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="chart-card">
                <div class="chart-card-header">
                    <i class="fas fa-chart-pie"></i> Distribusi Data
                </div>
                <div class="chart-card-body">
                    <div class="chart-container">
                        <canvas id="patientPieChartTemanggung"></canvas>
                    </div>
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

// ── PIE CHART: Yogyakarta ──
const ctxPie = document.getElementById('patientPieChart').getContext('2d');
new Chart(ctxPie, {
    type: 'doughnut',
    data: {
        labels: ['Kode Booking', 'Task ID'],
        datasets: [{
            data: [{{ $totalKodebooking }}, {{ $totalTaskId }}],
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

// ── BAR CHART: Yogyakarta ──
let patientChartOverall;

function fetchDataForDate(selectedDate) {
    fetch(`{{ url('/get-patient-data') }}?date=${selectedDate}`)
        .then(r => r.json())
        .then(data => updateChart(data.labels, data.values));
}

function updateChart(labels, values) {
    if (patientChartOverall) patientChartOverall.destroy();
    const ctx = document.getElementById('patientChartOverall').getContext('2d');
    patientChartOverall = new Chart(ctx, {
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
                tooltip: { backgroundColor: '#1A202C', bodyFont: { family: 'Poppins' } }
            },
        scales: {
            y: { 
                beginAtZero: true, 
                grid: { color: 'rgba(0,0,0,0.05)' },
                ticks: { 
                    font: { family: 'Poppins', size: 11 },
                    stepSize: 1, // Ensure integer ticks for patient counts
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

document.getElementById('filterDateYogyakarta').addEventListener('change', e => {
    if (e.target.value) fetchDataForDate(e.target.value);
});
const today = new Date().toISOString().split('T')[0];
document.getElementById('filterDateYogyakarta').value = today;
fetchDataForDate(today);

// ── PIE CHART: Kulon Progo ──
const ctxPieKP = document.getElementById('patientPieChartKulonProgo').getContext('2d');
new Chart(ctxPieKP, {
    type: 'doughnut',
    data: {
        labels: ['Kode Booking', 'Task ID'],
        datasets: [{
            data: [{{ $qlkptotalKodebooking ?? 0 }}, {{ $qlkptotalTaskId ?? 0 }}],
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
            }
        },
        cutout: '55%'
    }
});

// ── BAR CHART: Kulon Progo ──
let patientChartKulonProgoOverall;

function fetchDataForDateKulonProgo(selectedDate) {
    fetch(`{{ url('/get-patient-data-kulonprogo') }}?date=${selectedDate}`)
        .then(r => r.json())
        .then(data => updateChartKulonProgo(data.labels, data.values));
}

function updateChartKulonProgo(labels, values) {
    if (patientChartKulonProgoOverall) patientChartKulonProgoOverall.destroy();
    const ctx = document.getElementById('patientChartKulonProgoOverall').getContext('2d');
    patientChartKulonProgoOverall = new Chart(ctx, {
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
                    callbacks: { label: ctx => `Jumlah: ${ctx.raw}` }
                }
            },
        scales: {
            y: { 
                beginAtZero: true, 
                grid: { color: 'rgba(0,0,0,0.05)' },
                ticks: { 
                    font: { family: 'Poppins', size: 11 },
                    stepSize: 1, // Ensure integer ticks for patient counts
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

document.getElementById('filterDateKulonProgo').addEventListener('change', e => {
    if (e.target.value) fetchDataForDateKulonProgo(e.target.value);
});
const todayKP = new Date().toISOString().split('T')[0];
document.getElementById('filterDateKulonProgo').value = todayKP;
fetchDataForDateKulonProgo(todayKP);

// ── PIE CHART: Temanggung ──
const ctxPieTMG = document.getElementById('patientPieChartTemanggung').getContext('2d');
new Chart(ctxPieTMG, {
    type: 'doughnut',
    data: {
        labels: ['Kode Booking', 'Task ID'],
        datasets: [{
            data: [{{ $qltmgtotalKodebooking ?? 0 }}, {{ $qltmgtotalTaskId ?? 0 }}],
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
            }
        },
        cutout: '55%'
    }
});

// ── BAR CHART: Temanggung ──
let patientChartTemanggungOverall;

function fetchDataForDateTemanggung(selectedDate) {
    fetch(`{{ url('/get-patient-data-temanggung') }}?date=${selectedDate}`)
        .then(r => r.json())
        .then(data => updateChartTemanggung(data.labels, data.values));
}

function updateChartTemanggung(labels, values) {
    if (patientChartTemanggungOverall) patientChartTemanggungOverall.destroy();
    const ctx = document.getElementById('patientChartTemanggungOverall').getContext('2d');
    patientChartTemanggungOverall = new Chart(ctx, {
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
                tooltip: { backgroundColor: '#1A202C', bodyFont: { family: 'Poppins' } }
            },
        scales: {
            y: { 
                beginAtZero: true, 
                grid: { color: 'rgba(0,0,0,0.05)' },
                ticks: { 
                    font: { family: 'Poppins', size: 11 },
                    stepSize: 1, // Ensure integer ticks for patient counts
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

document.getElementById('filterDateTemanggung').addEventListener('change', e => {
    if (e.target.value) fetchDataForDateTemanggung(e.target.value);
});
const todayTMG = new Date().toISOString().split('T')[0];
document.getElementById('filterDateTemanggung').value = todayTMG;
fetchDataForDateTemanggung(todayTMG);

// ── Region Switcher ──
document.getElementById('regionSelector').addEventListener('change', function() {
    const selectedRegion = this.value;

    // Show/Hide Content
    document.querySelectorAll('.region-content').forEach(el => {
        el.classList.add('hidden');
    });
    document.getElementById(selectedRegion)?.classList.remove('hidden');

    // Show/Hide Filter
    document.querySelectorAll('.region-filter').forEach(el => {
        el.classList.add('hidden');
    });
    document.getElementById(`filter-wrap-${selectedRegion}`)?.classList.remove('hidden');
});
</script>
@endpush