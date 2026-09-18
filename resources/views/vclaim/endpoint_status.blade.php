@extends('layouts.admin')

@section('page_title')
    <i class="fas fa-network-wired me-1" style="color:var(--color-primary);"></i>
    Monitoring Endpoints BPJS
@endsection

{{-- styles VClaim dimuat global via Vite (resources/css/vclaim.css) --}}

@section('content')
<div class="row g-3">
    <div class="col-12">
        {{-- ══ Control Bar ══ --}}
        <div class="vclaim-control-bar">
            <div class="filter-group">
                <div>
                    <label class="form-label">Cabang Queen Latifa</label>
                    <select id="selectQL" class="form-select" style="width:200px;">
                        <option value="">-- Pilih Cabang --</option>
                        @foreach($availableQLs as $ql)
                            @php
                                $label = $ql;
                                if ($ql === 'QLJ') $label = 'QL Yogyakarta';
                                elseif ($ql === 'QLKP') $label = 'QL Kulon Progo';
                                elseif ($ql === 'QLTMG') $label = 'QL Temanggung';
                            @endphp
                            <option value="{{ $ql }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="d-flex gap-2 align-items-end">
                <button type="button" id="btnStartAnalysis" class="btn btn-primary btn-sm" style="height:38px;padding:0 1.2rem;">
                    <i class="fas fa-play me-1"></i> Mulai Analisa
                </button>
                <button type="button" id="btnCancelAnalysis" class="btn btn-secondary btn-sm" style="height:38px;padding:0 1.2rem;display:none;">
                    <i class="fas fa-stop me-1"></i> Batal
                </button>
            </div>
        </div>

        {{-- ══ Toolbar hasil (search + filter status) ══ --}}
        <div class="vclaim-control-bar" style="margin-top:-8px;">
            <div class="filter-group">
                <div>
                    <label class="form-label" for="endpointSearch">Cari endpoint</label>
                    <input type="search" id="endpointSearch" class="form-control" placeholder="cth: SEP, Poli…" style="width:220px;" autocomplete="off">
                </div>
            </div>
            <div class="endpoint-toolbar" role="group" aria-label="Filter status endpoint">
                <button type="button" class="btn-preset" data-epfilter="ALL" aria-pressed="true">Semua</button>
                <button type="button" class="btn-preset" data-epfilter="OK" aria-pressed="false">OK</button>
                <button type="button" class="btn-preset" data-epfilter="ERR" aria-pressed="false">Error</button>
                <button type="button" class="btn-preset" data-epfilter="PENDING" aria-pressed="false">Pending</button>
            </div>
        </div>

        {{-- ══ Dashboard ══ --}}
        <div id="appMonitor">
            <div class="row mb-4 mt-2">
                <div class="col-md-4 col-sm-12 mb-3">
                    <div class="summary-card shadow-sm bg-white" style="border-radius:0.75rem; padding:20px; display:flex; align-items:center; gap:15px; border:1px solid #e5e7eb;">
                        <div class="summary-icon d-flex align-items-center justify-content-center" style="width:50px; height:50px; border-radius:12px; font-size:1.4rem; background:#e0f2fe; color:#0284c7;">
                            <i class="fas fa-network-wired"></i>
                        </div>
                        <div>
                            <div class="text-uppercase text-muted" style="font-size:0.7rem; font-weight:700; letter-spacing:0.05em;">Total Endpoint</div>
                            <div id="totalEndpoints" style="font-size:1.5rem; font-weight:700; color:#111827; line-height:1.2;">0</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="summary-card shadow-sm bg-white" style="border-radius:0.75rem; padding:20px; display:flex; align-items:center; gap:15px; border:1px solid #e5e7eb;">
                        <div class="summary-icon d-flex align-items-center justify-content-center" style="width:50px; height:50px; border-radius:12px; font-size:1.4rem; background:#dcfce7; color:#16a34a;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <div class="text-uppercase text-muted" style="font-size:0.7rem; font-weight:700; letter-spacing:0.05em;">Status OK</div>
                            <div id="countOk" style="font-size:1.5rem; font-weight:700; color:#111827; line-height:1.2;">0</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="summary-card shadow-sm bg-white" style="border-radius:0.75rem; padding:20px; display:flex; align-items:center; gap:15px; border:1px solid #e5e7eb;">
                        <div class="summary-icon d-flex align-items-center justify-content-center" style="width:50px; height:50px; border-radius:12px; font-size:1.4rem; background:#fee2e2; color:#dc2626;">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div>
                            <div class="text-uppercase text-muted" style="font-size:0.7rem; font-weight:700; letter-spacing:0.05em;">Status Error</div>
                            <div id="countErr" style="font-size:1.5rem; font-weight:700; color:#111827; line-height:1.2;">0</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dashboard-header">
                <div class="last-updated" id="lastUpdatedTime">Belum dilakukan analisa</div>
            </div>

            <div class="endpoint-grid" id="endpointGrid">
                <!-- Cards will be rendered here by JS -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Definisi endpoint BPJS yang umum digunakan
    const baseEndpoints = [
        { name: "Get Peserta", defaultHttp: 200, defaultMsg: 200 },
        { name: "Get Rujukan", defaultHttp: 200, defaultMsg: 201 },
        { name: "Get Rujukan by No Kartu", defaultHttp: 200, defaultMsg: 201 },
        { name: "Get Rujukan by No Kartu Multiple", defaultHttp: 200, defaultMsg: 201 },
        { name: "Get Diagnosa", defaultHttp: 200, defaultMsg: 200 },
        { name: "Get DPJP", defaultHttp: 200, defaultMsg: 200 },
        { name: "Get SEP", defaultHttp: 200, defaultMsg: 201 },
        { name: "Get Surat Kontrol", defaultHttp: 200, defaultMsg: 201 },
        { name: "Get Riwayat Pelayanan", defaultHttp: 200, defaultMsg: 201 },
        { name: "Get Referensi Poli Antrol", defaultHttp: 200, defaultMsg: 200 },
        { name: "Get Jadwal Dokter Antrol", defaultHttp: 200, defaultMsg: 200 },
        { name: "Post Antrian Antrol", defaultHttp: 200, defaultMsg: 201 },
        { name: "Update Jadwal Antrol", defaultHttp: 200, defaultMsg: 200 },
        { name: "Get Fingerprint", defaultHttp: 200, defaultMsg: 200 },
        { name: "Batal Antrian Antrol", defaultHttp: 200, defaultMsg: 200 },
        { name: "Get Task ID", defaultHttp: 200, defaultMsg: 200 },
        { name: "Antrean Per Tanggal", defaultHttp: 200, defaultMsg: 200 },
        { name: "Antrean Per Kode Booking", defaultHttp: 200, defaultMsg: 200 }
    ];

    let currentEndpoints = [];
    const grid = document.getElementById('endpointGrid');
    const btnStartAnalysis = document.getElementById('btnStartAnalysis');
    const selectQL = document.getElementById('selectQL');

    // Inisialisasi state awal (Pending)
    function initCards() {
        currentEndpoints = baseEndpoints.map(ep => ({
            ...ep,
            http: '-',
            message: '-',
            time: 0,
            status: 'PENDING' // PENDING, OK, ERR
        }));
        renderCards();
        updateStats();
    }

    let epQuery = '';
    let epFilter = 'ALL';
    function visibleEndpoints() {
        return currentEndpoints.filter(ep => {
            if (epFilter !== 'ALL' && ep.status !== epFilter) return false;
            if (epQuery && !ep.name.toLowerCase().includes(epQuery)) return false;
            return true;
        });
    }

    function renderCards() {
        grid.innerHTML = '';
        const list = visibleEndpoints();
        if (!list.length) {
            grid.innerHTML = '<div class="empty-state" style="grid-column:1/-1;"><i class="fas fa-search"></i><p>Tidak ada endpoint yang cocok dengan pencarian/filter.</p></div>';
            return;
        }
        list.forEach(ep => {
            const isOk = ep.status === 'OK';
            const isPending = ep.status === 'PENDING';
            const isNa = ep.status === 'N/A';
            
            let statusClass = 'pending';
            let iconClass = 'fa-clock';
            if (isOk) { statusClass = 'ok'; iconClass = 'fa-check'; }
            else if (isNa) { statusClass = 'pending'; iconClass = 'fa-minus'; }
            else if (!isPending) { statusClass = 'err'; iconClass = 'fa-times'; }

            // max scale 1000ms for green, larger for red
            const maxTime = 1000; 
            const progressWidth = (isPending || isNa) ? 0 : Math.min(100, (ep.time / maxTime) * 100);

            // Tentukan indikator kecepatan berdasarkan response time
            let speedClass = 'text-pending';
            let bgClass = 'bg-pending';
            if (!isPending && !isNa) {
                if (ep.time > 800) { speedClass = 'text-slow'; bgClass = 'bg-slow'; }
                else if (ep.time >= 300) { speedClass = 'text-medium'; bgClass = 'bg-medium'; }
                else { speedClass = 'text-fast'; bgClass = 'bg-fast'; }
            }

            const cardHTML = `
                <div class="endpoint-card ${statusClass} ${isPending ? 'pulse-animation' : ''}">
                    <div class="card-header">
                        <h3 class="card-title">${ep.name}</h3>
                        <div class="status-label ${statusClass}">
                            <i class="fas ${iconClass} me-1"></i> ${ep.status}
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="data-row">
                            <span class="data-label">HTTP</span>
                            <span class="data-value">${ep.http}</span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">MESSAGE</span>
                            <span class="data-value">${ep.message}</span>
                        </div>
                    </div>
                    <div class="response-time-section">
                        <div class="response-header">
                            <span>RESPONSE TIME</span>
                            <span class="response-value ${speedClass}">${(isPending || isNa) ? '-' : ep.time + ' ms'}</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-bar ${bgClass}" style="width: ${progressWidth}%;"></div>
                        </div>
                    </div>
                </div>
            `;
            grid.insertAdjacentHTML('beforeend', cardHTML);
        });
    }

    function updateStats() {
        document.getElementById('totalEndpoints').textContent = currentEndpoints.length;
        document.getElementById('countOk').textContent = currentEndpoints.filter(e => e.status === 'OK').length;
        document.getElementById('countErr').textContent = currentEndpoints.filter(e => e.status === 'ERR').length;
    }

    initCards();

    // Search + filter status (client-side)
    const epSearch = document.getElementById('endpointSearch');
    if (epSearch) {
        epSearch.addEventListener('input', function () {
            epQuery = this.value.trim().toLowerCase();
            renderCards();
        });
    }
    document.querySelectorAll('[data-epfilter]').forEach(btn => {
        btn.addEventListener('click', function () {
            epFilter = this.getAttribute('data-epfilter');
            document.querySelectorAll('[data-epfilter]').forEach(b => b.setAttribute('aria-pressed', b === this ? 'true' : 'false'));
            renderCards();
        });
    });

    // Analisa paralel batch-5 + tombol Batal (ganti ping sekuensial 1-per-1)
    let analysisCancelled = false;
    const btnCancelAnalysis = document.getElementById('btnCancelAnalysis');
    if (btnCancelAnalysis) {
        btnCancelAnalysis.addEventListener('click', function () {
            analysisCancelled = true;
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Membatalkan…';
        });
    }

    function pingUrlFor(name, ql) {
        const base = `{{ url('') }}`;
        const map = {
            "Get Peserta": '/vclaim/ping-peserta',
            "Get Rujukan": '/vclaim/ping-rujukan',
            "Get Rujukan by No Kartu": '/vclaim/ping-rujukan-kartu',
            "Get Rujukan by No Kartu Multiple": '/vclaim/ping-rujukan-list',
            "Get Diagnosa": '/vclaim/ping-referensi-diagnosa',
            "Get DPJP": '/vclaim/ping-referensi-dpjp',
            "Get SEP": '/vclaim/ping-sep',
            "Get Surat Kontrol": '/vclaim/ping-surat-kontrol',
            "Get Riwayat Pelayanan": '/vclaim/ping-riwayat-pelayanan',
            "Get Referensi Poli Antrol": '/vclaim/ping-antrol-referensi-poli',
            "Get Jadwal Dokter Antrol": '/vclaim/ping-antrol-jadwal-dokter',
            "Post Antrian Antrol": '/vclaim/ping-antrol-antrean-add',
            "Update Jadwal Antrol": '/vclaim/ping-antrol-update-jadwal',
            "Get Fingerprint": '/vclaim/ping-get-fingerprint',
            "Batal Antrian Antrol": '/vclaim/ping-antrol-batal-antrean',
            "Get Task ID": '/vclaim/ping-antrol-get-list-task',
            "Antrean Per Tanggal": '/vclaim/ping-antrol-antrean-per-tanggal',
            "Antrean Per Kode Booking": '/vclaim/ping-antrol-antrean-per-kode-booking'
        };
        return map[name] ? base + map[name] + `?urlQL=${ql}` : '';
    }

    function setRunning(running) {
        btnStartAnalysis.disabled = running;
        btnStartAnalysis.innerHTML = running
            ? '<span class="spinner-border spinner-border-sm me-1"></span> Menganalisa...'
            : '<i class="fas fa-play me-1"></i> Analisa Ulang';
        if (btnCancelAnalysis) {
            btnCancelAnalysis.style.display = running ? '' : 'none';
            btnCancelAnalysis.disabled = !running;
            btnCancelAnalysis.innerHTML = '<i class="fas fa-stop me-1"></i> Batal';
        }
    }

    function finishAnalysis(qlText, note) {
        setRunning(false);
        const now = new Date();
        const p2 = n => String(n).padStart(2, '0');
        document.getElementById('lastUpdatedTime').innerHTML =
            `${note || 'Analisa terakhir'}: ${p2(now.getDate())}/${p2(now.getMonth() + 1)}/${now.getFullYear()}, ` +
            `${p2(now.getHours())}.${p2(now.getMinutes())}.${p2(now.getSeconds())} WIB | Cabang: <strong>${qlText}</strong>`;
    }

    async function pingOne(ep, ql) {
        const pingUrl = pingUrlFor(ep.name, ql);
        if (!pingUrl) {
            ep.status = 'N/A'; ep.http = '-'; ep.message = '-'; ep.time = 0;
            return;
        }
        try {
            const response = await fetch(pingUrl);
            const data = await response.json();
            // HTTP 200 + message code 1/200/201/202/204/208 = BPJS UP
            const validCodes = [1, 200, 201, 202, 204, 208];
            ep.status = (data.success && data.http_code == 200 && validCodes.includes(parseInt(data.message_code))) ? 'OK' : 'ERR';
            ep.http = data.http_code || '-';
            ep.message = (data.message_code !== null && data.message_code !== undefined) ? data.message_code : '-';
            ep.time = data.response_time || 0;
        } catch (err) {
            ep.status = 'ERR'; ep.http = 500; ep.message = 'Fetch Error'; ep.time = 0;
        }
    }

    btnStartAnalysis.addEventListener('click', async function() {
        const ql = selectQL.value;
        const qlText = selectQL.options[selectQL.selectedIndex].text;
        if (!ql) {
            (window.showGlobalToast || alert)('Silakan pilih Cabang Queen Latifa terlebih dahulu.', 'warning');
            selectQL.focus();
            return;
        }

        analysisCancelled = false;
        setRunning(true);

        // Reset state
        initCards();
        const total = currentEndpoints.length;
        document.getElementById('lastUpdatedTime').innerHTML = `Menganalisa API BPJS untuk cabang <strong>${qlText}</strong>…`;

        // Paralel batch-5 agar ~5x lebih cepat tanpa membanjiri server
        const BATCH = 5;
        for (let i = 0; i < total; i += BATCH) {
            if (analysisCancelled) break;
            const slice = currentEndpoints.slice(i, i + BATCH);
            await Promise.allSettled(slice.map(ep => pingOne(ep, ql)));
            renderCards();
            updateStats();
            document.getElementById('lastUpdatedTime').innerHTML =
                `Menganalisa… ${Math.min(i + BATCH, total)}/${total} endpoint (cabang <strong>${qlText}</strong>)`;
        }

        if (analysisCancelled) {
            finishAnalysis(qlText, 'Analisa dibatalkan');
        } else {
            renderCards();
            updateStats();
            finishAnalysis(qlText, 'Analisa terakhir');
        }
    });


});
</script>
@endpush
