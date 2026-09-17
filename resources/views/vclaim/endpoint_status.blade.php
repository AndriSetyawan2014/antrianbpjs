@extends('layouts.admin')

@section('page_title')
    <i class="fas fa-network-wired me-1" style="color:var(--color-primary);"></i>
    Monitoring Endpoints BPJS
@endsection

@push('styles')
<style>
    /* ── Filter / Control Bar (from standard template) ── */
    .vclaim-control-bar {
        background: var(--color-surface, #fff);
        border: 1px solid var(--color-border, #e5e7eb);
        border-radius: var(--radius-md, 0.5rem);
        padding: 14px 20px;
        margin-bottom: 20px;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: flex-end;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        justify-content: space-between;
    }
    .vclaim-control-bar .filter-group {
        display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;
    }
    .vclaim-control-bar .form-label {
        font-size: .75rem; color: var(--color-text-secondary, #6b7280);
        margin-bottom: .2rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em;
    }

    /* ── Dashboard Header ── */
    .dashboard-header {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 20px; padding: 0 5px;
    }
    .last-updated { font-size: 0.85rem; color: var(--color-text-muted, #9ca3af); font-style: italic; }

    /* ── Endpoint Grid ── */
    .endpoint-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 20px;
    }

    .endpoint-card {
        background-color: var(--color-surface, #fff);
        border: 1px solid var(--color-border, #e5e7eb);
        border-radius: 0.75rem;
        padding: 20px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        position: relative;
        overflow: hidden;
    }

    .endpoint-card::before {
        content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%;
    }
    .endpoint-card.ok::before { background-color: #10b981; }
    .endpoint-card.err::before { background-color: #ef4444; }
    .endpoint-card.pending::before { background-color: #d1d5db; }

    .endpoint-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    @keyframes pulsePending {
        0% { opacity: 1; }
        50% { opacity: 0.4; }
        100% { opacity: 1; }
    }
    .pulse-animation {
        animation: pulsePending 1.5s infinite ease-in-out;
    }

    .card-header {
        display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;
    }
    .card-title {
        font-size: 0.95rem; font-weight: 700; color: var(--color-text-primary, #111827); margin: 0;
    }

    .status-label {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 10px; border-radius: 20px; font-size: 0.7rem;
        font-weight: 700; text-transform: uppercase; letter-spacing: .02em;
    }
    .status-label.ok { background-color: #d1fae5; color: #065f46; }
    .status-label.err { background-color: #fee2e2; color: #991b1b; }
    .status-label.pending { background-color: #f3f4f6; color: #374151; }

    .card-body { display: flex; flex-direction: column; gap: 10px; }
    .data-row { display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; font-family: monospace; }
    .data-label { color: var(--color-text-secondary, #6b7280); font-weight: 600; }
    .data-value { color: var(--color-text-primary, #111827); font-weight: 700; background: #f9fafb; padding: 2px 6px; border-radius: 4px; }

    .response-time-section {
        margin-top: 15px; padding-top: 15px; border-top: 1px dashed var(--color-border, #e5e7eb);
    }
    .response-header {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 8px; font-size: 0.75rem; font-weight: 600; color: var(--color-text-secondary, #6b7280);
    }
    .response-value { font-weight: 700; font-size: 0.85rem; }

    /* Kecepatan warna teks */
    .text-fast { color: #10b981; }
    .text-medium { color: #f59e0b; }
    .text-slow { color: #ef4444; }
    .text-pending { color: #9ca3af; }

    .progress-track {
        width: 100%; height: 6px; background-color: #f3f4f6;
        border-radius: 3px; overflow: hidden;
    }
    .progress-bar {
        height: 100%; border-radius: 3px; transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.3s;
    }
    /* Kecepatan warna bar */
    .bg-fast { background-color: #10b981; }
    .bg-medium { background-color: #f59e0b; }
    .bg-slow { background-color: #ef4444; }
</style>
@endpush

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

    function renderCards() {
        grid.innerHTML = '';
        currentEndpoints.forEach(ep => {
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

    // Simulasi Analisa (karena endpoint cek spesifik per URL belum tersedia di backend)
    btnStartAnalysis.addEventListener('click', function() {
        const ql = selectQL.value;
        const qlText = selectQL.options[selectQL.selectedIndex].text;
        if (!ql) {
            alert('Silakan pilih Cabang Queen Latifa terlebih dahulu.');
            selectQL.focus();
            return;
        }

        btnStartAnalysis.disabled = true;
        btnStartAnalysis.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menganalisa...';
        
        // Reset state
        initCards();
        document.getElementById('lastUpdatedTime').innerHTML = `Menganalisa API BPJS untuk cabang <strong>${qlText}</strong>...`;

        // Simulasi request satu per satu (seolah-olah sedang ping)
        let index = 0;
        const total = currentEndpoints.length;
        
        function pingNext() {
            if (index >= total) {
                // Selesai
                btnStartAnalysis.disabled = false;
                btnStartAnalysis.innerHTML = '<i class="fas fa-play me-1"></i> Analisa Ulang';
                
                const now = new Date();
                const strDate = now.getDate().toString().padStart(2, '0') + '/' + 
                                (now.getMonth() + 1).toString().padStart(2, '0') + '/' + 
                                now.getFullYear();
                const strTime = now.getHours().toString().padStart(2, '0') + '.' + 
                                now.getMinutes().toString().padStart(2, '0') + '.' + 
                                now.getSeconds().toString().padStart(2, '0');
                
                document.getElementById('lastUpdatedTime').innerHTML = `Analisa terakhir: ${strDate}, ${strTime} WIB | Cabang: <strong>${qlText}</strong>`;
                return;
            }

            const ep = currentEndpoints[index];

            const realEndpoints = [
                "Get Peserta", 
                "Get Rujukan", 
                "Get Rujukan by No Kartu", 
                "Get Rujukan by No Kartu Multiple",
                "Get Diagnosa",
                "Get DPJP",
                "Get SEP",
                "Get Surat Kontrol",
                "Get Riwayat Pelayanan",
                "Get Referensi Poli Antrol",
                "Get Jadwal Dokter Antrol",
                "Post Antrian Antrol",
                "Update Jadwal Antrol",
                "Get Fingerprint",
                "Batal Antrian Antrol",
                "Get Task ID",
                "Antrean Per Tanggal",
                "Antrean Per Kode Booking"
            ];

            // Jika endpoint memiliki AJAX backend nyata
            if (realEndpoints.includes(ep.name)) {
                let pingUrl = '';
                if (ep.name === "Get Peserta") pingUrl = `{{ url('/vclaim/ping-peserta') }}?urlQL=${ql}`;
                else if (ep.name === "Get Rujukan") pingUrl = `{{ url('/vclaim/ping-rujukan') }}?urlQL=${ql}`;
                else if (ep.name === "Get Rujukan by No Kartu") pingUrl = `{{ url('/vclaim/ping-rujukan-kartu') }}?urlQL=${ql}`;
                else if (ep.name === "Get Rujukan by No Kartu Multiple") pingUrl = `{{ url('/vclaim/ping-rujukan-list') }}?urlQL=${ql}`;
                else if (ep.name === "Get Diagnosa") pingUrl = `{{ url('/vclaim/ping-referensi-diagnosa') }}?urlQL=${ql}`;
                else if (ep.name === "Get DPJP") pingUrl = `{{ url('/vclaim/ping-referensi-dpjp') }}?urlQL=${ql}`;
                else if (ep.name === "Get SEP") pingUrl = `{{ url('/vclaim/ping-sep') }}?urlQL=${ql}`;
                else if (ep.name === "Get Surat Kontrol") pingUrl = `{{ url('/vclaim/ping-surat-kontrol') }}?urlQL=${ql}`;
                else if (ep.name === "Get Riwayat Pelayanan") pingUrl = `{{ url('/vclaim/ping-riwayat-pelayanan') }}?urlQL=${ql}`;
                else if (ep.name === "Get Referensi Poli Antrol") pingUrl = `{{ url('/vclaim/ping-antrol-referensi-poli') }}?urlQL=${ql}`;
                else if (ep.name === "Get Jadwal Dokter Antrol") pingUrl = `{{ url('/vclaim/ping-antrol-jadwal-dokter') }}?urlQL=${ql}`;
                else if (ep.name === "Post Antrian Antrol") pingUrl = `{{ url('/vclaim/ping-antrol-antrean-add') }}?urlQL=${ql}`;
                else if (ep.name === "Update Jadwal Antrol") pingUrl = `{{ url('/vclaim/ping-antrol-update-jadwal') }}?urlQL=${ql}`;
                else if (ep.name === "Get Fingerprint") pingUrl = `{{ url('/vclaim/ping-get-fingerprint') }}?urlQL=${ql}`;
                else if (ep.name === "Batal Antrian Antrol") pingUrl = `{{ url('/vclaim/ping-antrol-batal-antrean') }}?urlQL=${ql}`;
                else if (ep.name === "Get Task ID") pingUrl = `{{ url('/vclaim/ping-antrol-get-list-task') }}?urlQL=${ql}`;
                else if (ep.name === "Antrean Per Tanggal") pingUrl = `{{ url('/vclaim/ping-antrol-antrean-per-tanggal') }}?urlQL=${ql}`;
                else if (ep.name === "Antrean Per Kode Booking") pingUrl = `{{ url('/vclaim/ping-antrol-antrean-per-kode-booking') }}?urlQL=${ql}`;

                fetch(pingUrl)
                    .then(response => response.json())
                    .then(data => {
                        // Untuk monitoring koneksi, HTTP 200 dengan message code:
                        // 200, 201, 202 (standar VClaim/beberapa Antrol) 
                        // atau 1 (standar API Referensi Antrol/HFIS)
                        // menandakan server BPJS UP dan menerima request kita.
                        const validCodes = [1, 200, 201, 202, 204, 208]; // 208 biasanya untuk "Sudah Ada", 204 "No Content"
                        if (data.success && data.http_code == 200 && validCodes.includes(parseInt(data.message_code))) {
                            ep.status = 'OK';
                        } else {
                            ep.status = 'ERR';
                        }
                        ep.http = data.http_code || '-';
                        ep.message = (data.message_code !== null && data.message_code !== undefined) ? data.message_code : '-';
                        ep.time = data.response_time || 0;
                        
                        renderCards();
                        updateStats();
                        index++;
                        pingNext();
                    })
                    .catch(err => {
                        ep.status = 'ERR';
                        ep.http = 500;
                        ep.message = 'Fetch Error';
                        ep.time = 0;
                        
                        renderCards();
                        updateStats();
                        index++;
                        pingNext();
                    });
            } else {
                // Endpoint belum terhubung ke BPJS (dummy), biarkan kosong untuk membedakan
                setTimeout(() => {
                    ep.status = 'N/A';
                    ep.http = '-';
                    ep.message = '-';
                    ep.time = 0;

                    renderCards();
                    updateStats();
                    
                    index++;
                    pingNext();
                    
                }, 50); // delay sangat singkat agar cepat terlewat
            }
        }

        // Mulai ping
        pingNext();
    });

});
</script>
@endpush
