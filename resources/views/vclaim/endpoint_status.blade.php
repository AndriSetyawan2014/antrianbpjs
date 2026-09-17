@extends('layouts.admin')

@section('page_title')
    <i class="fas fa-network-wired me-1" style="color:var(--color-primary);"></i>
    Monitoring Endpoints BPJS
@endsection

@push('styles')
<style>
    /* ── Filter / Control Bar (from standard template) ── */
    .vclaim-control-bar {
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        padding: 14px 20px;
        margin-bottom: 20px;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: flex-end;
        box-shadow: var(--shadow-sm);
        justify-content: space-between;
    }
    .vclaim-control-bar .filter-group {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: flex-end;
    }
    .vclaim-control-bar .form-label {
        font-size: .75rem;
        color: var(--color-text-secondary);
        margin-bottom: .2rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .05em;
    }
    .vclaim-control-bar .form-control,
    .vclaim-control-bar .form-select {
        background: var(--color-bg);
        border: 1.5px solid var(--color-border);
        color: var(--color-text-primary);
        border-radius: var(--radius-sm);
        font-size: .85rem;
        transition: border-color .2s, box-shadow .2s;
    }
    .vclaim-control-bar .form-control:focus,
    .vclaim-control-bar .form-select:focus {
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(12,53,106,.12);
    }

    /* ── Dashboard Header ── */
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        padding: 0 5px;
    }
    
    .header-left {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .last-updated {
        font-size: 0.75rem;
        color: var(--color-text-muted);
        font-family: monospace;
    }

    .endpoint-count {
        font-size: 1rem;
        font-weight: 700;
        color: var(--color-text-primary);
    }

    .status-summary {
        display: flex;
        gap: 10px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        background-color: var(--color-surface);
        border: 1px solid var(--color-border);
        box-shadow: var(--shadow-sm);
    }

    .status-badge.success { color: var(--color-success); border-color: #a7f3d0; background-color: #ecfdf5;}
    .status-badge.warning { color: var(--color-warning); border-color: #fde68a; background-color: #fffbeb;}
    .status-badge.danger  { color: var(--color-danger); border-color: #fca5a5; background-color: #fef2f2;}

    /* ── Endpoint Grid ── */
    .endpoint-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 15px;
    }

    .endpoint-card {
        background-color: var(--color-surface);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        padding: 16px;
        box-shadow: var(--shadow-sm);
        transition: var(--transition-base);
        position: relative;
    }

    .endpoint-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
        border-color: var(--color-primary);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
    }

    .card-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--color-text-primary);
        margin: 0;
    }

    .status-label {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    
    .status-label.ok {
        background-color: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .status-label.err {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    
    .status-label.pending {
        background-color: #f3f4f6;
        color: #374151;
        border: 1px solid #e5e7eb;
    }

    .card-body {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .data-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.75rem;
        font-family: monospace;
    }

    .data-label {
        color: var(--color-text-secondary);
        font-weight: 600;
    }

    .data-value {
        color: var(--color-text-primary);
        font-weight: 700;
    }

    .response-time-section {
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px dashed var(--color-border);
    }

    .response-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 6px;
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--color-text-secondary);
    }

    .response-value {
        font-weight: 700;
        font-size: 0.8rem;
    }
    
    .response-value.ok { color: var(--color-success); }
    .response-value.err { color: var(--color-danger); }
    .response-value.pending { color: var(--color-text-muted); }

    .progress-track {
        width: 100%;
        height: 6px;
        background-color: var(--color-bg);
        border: 1px solid var(--color-border);
        border-radius: 3px;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        border-radius: 2px;
        transition: width 0.5s ease-out;
    }

    .progress-bar.ok { background-color: var(--color-success); }
    .progress-bar.err { background-color: var(--color-danger); }
    .progress-bar.pending { background-color: var(--color-text-muted); width: 0%; }
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
            <div class="dashboard-header">
                <div class="header-left">
                    <div class="endpoint-count">Menampilkan <span id="totalEndpoints">0</span> endpoint</div>
                    <div class="last-updated" id="lastUpdatedTime">Belum dilakukan analisa</div>
                </div>
                <div class="status-summary">
                    <span class="status-badge success"><i class="fas fa-check-circle me-1"></i> <span id="countOk">0</span></span>
                    <span class="status-badge danger"><i class="fas fa-times-circle me-1"></i> <span id="countErr">0</span></span>
                </div>
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
            const maxTime = isOk ? 1000 : (ep.time > 1000 ? ep.time : 1000); 
            const progressWidth = (isPending || isNa) ? 0 : Math.min(100, (ep.time / maxTime) * 100);

            const cardHTML = `
                <div class="endpoint-card" style="border-left: 3px solid ${isOk ? 'var(--color-success)' : ((isPending || isNa) ? 'transparent' : 'var(--color-danger)')}">
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
                            <span class="response-value ${statusClass}">${(isPending || isNa) ? '-' : ep.time + ' ms'}</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-bar ${statusClass}" style="width: ${progressWidth}%;"></div>
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
