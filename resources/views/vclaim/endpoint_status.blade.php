@extends('layouts.admin')

@section('page_title')
    <i class="fas fa-network-wired me-1" style="color:var(--color-primary);"></i>
    Monitoring Endpoints BPJS
@endsection

{{-- styles VClaim dimuat global via Vite (resources/css/vclaim.css) --}}

@section('content')
<div class="row g-3">
    <div class="col-12">
        <div id="branchAlert" class="alert alert-warning py-2 px-3 mb-2" style="display:none; font-size:0.85rem;">
            <i class="fas fa-exclamation-triangle me-2"></i> Silakan pilih Cabang Queen Latifa terlebih dahulu.
        </div>
        {{-- ══ Control Bar (2 kolom) ══ --}}
        <div class="row g-2 mb-2">
            {{-- Kiri: cabang, analisa, batal --}}
            <div class="col-md-6">
                <div class="vclaim-control-bar h-100" style="padding:10px 14px; gap:8px; margin-bottom:0; align-items:flex-end;">
                    <div class="filter-group" style="gap:8px;">
                        <div>
                            <label class="form-label mb-0" style="font-size:0.7rem;">Cabang</label>
                            <select id="selectQL" class="form-select form-select-sm" style="width:150px;">
                                <option value="">-- Pilih --</option>
                                @foreach($availableQLs as $ql)
                                    @php
                                        $label = $ql;
                                        if ($ql === 'QLJ') $label = 'Yogyakarta';
                                        elseif ($ql === 'QLKP') $label = 'Kulon Progo';
                                        elseif ($ql === 'QLTMG') $label = 'Temanggung';
                                    @endphp
                                    <option value="{{ $ql }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="d-flex gap-2 align-items-end">
                            <button type="button" id="btnStartAnalysis" class="btn btn-primary btn-sm" style="height:32px;padding:0 0.9rem;font-size:0.8rem;">
                                <i class="fas fa-play me-1"></i> Analisa
                            </button>
                            <button type="button" id="btnCancelAnalysis" class="btn btn-secondary btn-sm" style="height:32px;padding:0 0.9rem;font-size:0.8rem;display:none;">
                                <i class="fas fa-stop me-1"></i> Batal
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Kanan: cari + total/OK/Error --}}
            <div class="col-md-6">
                <div class="vclaim-control-bar h-100" style="padding:10px 14px; gap:8px; margin-bottom:0; align-items:flex-end; flex-wrap:wrap;">
                    <div class="filter-group" style="gap:8px; flex:1;">
                        <div>
                            <label class="form-label mb-0" style="font-size:0.7rem;" for="endpointSearch">Cari</label>
                            <input type="search" id="endpointSearch" class="form-control form-control-sm" placeholder="SEP, Poli…" style="width:140px;" autocomplete="off">
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap" style="flex:1;">
                        <div class="summary-card shadow-sm bg-white" style="border-radius:0.5rem; padding:10px 14px; display:flex; align-items:center; gap:10px; border:1px solid #e5e7eb; flex:1; min-width:110px;">
                            <div class="summary-icon d-flex align-items-center justify-content-center" style="width:34px; height:34px; border-radius:8px; font-size:1rem; background:#e0f2fe; color:#0284c7;">
                                <i class="fas fa-network-wired"></i>
                            </div>
                            <div>
                                <div class="text-uppercase text-muted" style="font-size:0.6rem; font-weight:700; letter-spacing:0.05em;">Total</div>
                                <div id="totalEndpoints" style="font-size:1.1rem; font-weight:700; color:#111827; line-height:1.1;">0</div>
                            </div>
                        </div>
                        <div class="summary-card shadow-sm bg-white" style="border-radius:0.5rem; padding:10px 14px; display:flex; align-items:center; gap:10px; border:1px solid #e5e7eb; flex:1; min-width:110px;">
                            <div class="summary-icon d-flex align-items-center justify-content-center" style="width:34px; height:34px; border-radius:8px; font-size:1rem; background:#dcfce7; color:#16a34a;">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <div class="text-uppercase text-muted" style="font-size:0.6rem; font-weight:700; letter-spacing:0.05em;">OK</div>
                                <div id="countOk" style="font-size:1.1rem; font-weight:700; color:#111827; line-height:1.1;">0</div>
                            </div>
                        </div>
                        <div class="summary-card shadow-sm bg-white" style="border-radius:0.5rem; padding:10px 14px; display:flex; align-items:center; gap:10px; border:1px solid #e5e7eb; flex:1; min-width:110px;">
                            <div class="summary-icon d-flex align-items-center justify-content-center" style="width:34px; height:34px; border-radius:8px; font-size:1rem; background:#fee2e2; color:#dc2626;">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div>
                                <div class="text-uppercase text-muted" style="font-size:0.6rem; font-weight:700; letter-spacing:0.05em;">Error</div>
                                <div id="countErr" style="font-size:1.1rem; font-weight:700; color:#111827; line-height:1.1;">0</div>
                            </div>
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

    {{-- Modal Request/Response --}}
    <div class="modal fade" id="epDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="epDetailTitle">Detail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <pre id="epDetailContent" style="white-space: pre-wrap; word-break: break-word; font-size: 0.8rem;"></pre>
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
                            <span class="data-label">Ping Endpoint</span>
                            <span class="data-value">${Number(ep.http) === 200 ? 'Berhasil' : 'Gagal (' + (ep.http || '-') + ')'}</span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">Response BPJS</span>
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
                    <div class="card-actions mt-2 d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="showEpDetail('request','${ep.name}')">Request</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="showEpDetail('response','${ep.name}')">Response</button>
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

    window.showEpDetail = function (type, name) {
        const d = (window.epData && window.epData[name]) || {};
        const raw = type === 'request' ? d.request : d.response;
        let text = '';
        if (raw === null || raw === undefined) {
            text = '(tidak ada data)';
        } else if (typeof raw === 'string') {
            try { text = JSON.stringify(JSON.parse(raw), null, 2); }
            catch (e) { text = raw; }
        } else {
            text = JSON.stringify(raw, null, 2);
        }
        document.getElementById('epDetailTitle').textContent = (type === 'request' ? 'Request' : 'Response') + ' — ' + name;
        document.getElementById('epDetailContent').textContent = text;
        const modal = new bootstrap.Modal(document.getElementById('epDetailModal'));
        modal.show();
    };

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
            // BPJS OK hanya jika HTTP 200 dan BPJS metaData code 200
            const isUp = data.success && Number(data.http_code) === 200 && Number(data.message_code) === 200;
            ep.status = isUp ? 'OK' : 'ERR';
            ep.http = data.http_code || '-';
            ep.message = (data.message_code !== null && data.message_code !== undefined) ? data.message_code : '-';
            ep.time = data.response_time || 0;
            ep.request = data.request || null;
            ep.response = data.response || null;
            window.epData = window.epData || {};
            window.epData[ep.name] = { request: ep.request, response: ep.response };
        } catch (err) {
            ep.status = 'ERR'; ep.http = 500; ep.message = 'Fetch Error'; ep.time = 0;
        }
    }

    btnStartAnalysis.addEventListener('click', async function() {
        const ql = selectQL.value;
        const qlText = selectQL.options[selectQL.selectedIndex].text;
        if (!ql) {
            const alertEl = document.getElementById('branchAlert');
            if (alertEl) {
                alertEl.style.display = 'block';
                clearTimeout(window._branchAlertTimer);
                window._branchAlertTimer = setTimeout(function () { alertEl.style.display = 'none'; }, 4000);
            }
            selectQL.classList.add('is-invalid');
            selectQL.focus();
            return;
        }
        selectQL.classList.remove('is-invalid');

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
