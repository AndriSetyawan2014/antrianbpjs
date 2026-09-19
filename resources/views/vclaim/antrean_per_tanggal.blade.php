@extends('layouts.admin')

@section('page_title')
    <i class="fas fa-list-ol me-1" style="color:var(--color-primary);"></i>
    Antrean Per Tanggal
@endsection

@section('content')
<div class="row g-3">
    <div class="col-12">

        {{-- ══ Control Bar ════════════════════════════════════════════ --}}
        <div class="card card-primary card-outline mb-3">
            <div class="card-body p-3">
                <form id="filterForm" class="d-flex align-items-end gap-3 flex-wrap">
                    <div>
                        <label class="form-label mb-1">Tanggal Antrean</label>
                        <input type="date" id="inputTanggal" name="tanggal" class="form-control"
                               value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" style="width:155px;" required>
                    </div>
                    <div>
                        <label class="form-label mb-1">Cabang QL</label>
                        <select id="inputUrlQL" name="urlQL" class="form-select" style="width:170px;" required>
                            <option value="">Pilih Cabang</option>
                            @foreach($urlQLOptions as $ql)
                                <option value="{{ $ql }}">{{ $ql }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary" id="btnCari">
                            <i class="fas fa-search me-1"></i> Cari Antrean
                        </button>
                        <button type="button" id="btnSync" class="btn btn-success ms-2">
                            <i class="fas fa-sync-alt me-1"></i> Sync BPJS
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ══ Sync Progress Panel ════════════════════════════════════ --}}
        <div id="syncProgressPanel" style="display:none; margin-bottom:1rem; padding:15px; border-radius:8px; border-left:4px solid var(--color-info); background:var(--color-surface); box-shadow:0 2px 8px rgba(0,0,0,0.05);">
            <div class="sync-title" style="font-weight:600; margin-bottom:10px;">
                <span class="spinner-border spinner-border-sm text-info me-2" role="status"></span>
                <span>Menyinkronisasi data dari API BPJS...</span>
            </div>
            <div id="syncProgressItems"></div>
            <div id="syncProgressSummary" style="font-size:.78rem; color:var(--color-text-muted); margin-top:8px;"></div>
        </div>

        {{-- ── Toast Notifikasi ── --}}
        <div id="syncToast" class="sync-toast" style="display:none; position:fixed; top:20px; right:20px; z-index:9999; background:white; padding:15px; border-radius:5px; box-shadow:0 4px 12px rgba(0,0,0,0.15); width: max-content; max-width: 400px; height: fit-content;">
            <div class="d-flex align-items-center">
                <i id="syncToastIcon" class="fas fa-check-circle text-success fa-lg me-2"></i>
                <span id="syncToastMsg"></span>
            </div>
        </div>

        {{-- ══ Resume Status Box ══════════════════════════════════════ --}}
        <div id="resumeStatusContainer" style="display: none;">
            <div class="row">
                <!-- Jenis Pasien -->
                <div class="col-md-4">
                    <div class="card card-primary card-outline">
                        <div class="card-header border-bottom-0 pb-0">
                            <h3 class="card-title fw-bold">Jenis Pasien</h3>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="p-2 bg-primary text-white rounded">
                                        <h3 id="resPesertaBPJS" class="mb-0 fs-3">0</h3>
                                        <p class="mb-1">BPJS</p>
                                        <small style="font-size: 0.75rem;">MJKN: <span id="resMJKN">0</span> <br> Bridging: <span id="resBridging">0</span></small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 bg-success text-white rounded h-100 d-flex flex-column justify-content-center">
                                        <h3 id="resNonPesertaBPJS" class="mb-0 fs-3">0</h3>
                                        <p class="mb-0">UMUM</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Pemeriksaan -->
                <div class="col-md-8">
                    <div class="card card-success card-outline">
                        <div class="card-header border-bottom-0 pb-0">
                            <h3 class="card-title fw-bold">Status Pemeriksaan</h3>
                        </div>
                        <div class="card-body">
                            <div class="row text-center g-2">
                                <div class="col-3">
                                    <div class="p-2 bg-success text-white rounded h-100">
                                        <h3 id="resSelesai" class="mb-0 fs-4">0</h3>
                                        <p class="mb-1" style="font-size:12px;">Selesai</p>
                                        <small style="font-size: 0.7rem;">BPJS: <span id="resSelesaiBPJS">0</span> <br> UMUM: <span id="resSelesaiUmum">0</span></small>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="p-2 bg-danger text-white rounded h-100">
                                        <h3 id="resBatal" class="mb-0 fs-4">0</h3>
                                        <p class="mb-1" style="font-size:12px;">Batal</p>
                                        <small style="font-size: 0.7rem;">BPJS: <span id="resBatalBPJS">0</span> <br> UMUM: <span id="resBatalUmum">0</span></small>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="p-2 bg-warning text-dark rounded h-100 d-flex flex-column justify-content-center">
                                        <h3 id="resSedang" class="mb-0 fs-4">0</h3>
                                        <p class="mb-0" style="font-size:12px;">Sedang Dilayani</p>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="p-2 bg-info text-white rounded h-100 d-flex flex-column justify-content-center">
                                        <h3 id="resBelum" class="mb-0 fs-4">0</h3>
                                        <p class="mb-0" style="font-size:12px;">Belum Dilayani</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══ Tabel Antrean ════════════════════════════════════════ --}}
        <div class="card card-outline card-info">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="tableAntrean" style="width: 100%; font-size: 0.85rem;">
                        <thead class="bg-light">
                            <tr>
                                <th>No</th>
                                <th>Identitas</th>
                                <th>Dokter</th>
                                <th>No. HP</th>
                                <th>Jns Kunjungan</th>
                                <th>No. Referensi</th>
                                <th>Sumber Data</th>
                                <th>Peserta?</th>
                                <th>Antrean</th>
                                <th>Dibuat</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="bodyAntrean">
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">Pilih cabang dan tanggal lalu klik Cari Antrean.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let table = null;

    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        
        const tanggal = $('#inputTanggal').val();
        const urlQL = $('#inputUrlQL').val();
        const btnCari = $('#btnCari');

        if(!urlQL) {
            alert('Silakan pilih Cabang QL terlebih dahulu.');
            return;
        }

        btnCari.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Mencari...');
        
        // Show loading in table
        if(table) {
            table.destroy();
            table = null;
        }
        $('#bodyAntrean').html('<tr><td colspan="11" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');
        $('#resumeStatusContainer').hide();

        $.ajax({
            url: `{{ url('/api/vclaim/antrol-antrean-per-tanggal-data') }}`,
            type: 'GET',
            data: {
                tanggal: tanggal,
                urlQL: urlQL
            },
            dataType: 'json',
            success: function(res) {
                btnCari.prop('disabled', false).html('<i class="fas fa-search me-1"></i> Cari Antrean');
                
                if(res.metadata && res.metadata.code == 200) {
                    const data = res.response;
                    if(data && data.length > 0) {
                        renderTable(data);
                        renderResume(data);
                    } else {
                        $('#bodyAntrean').html('<tr><td colspan="11" class="text-center text-muted py-4">Data antrean tidak ditemukan.</td></tr>');
                    }
                } else {
                    const msg = (res.metadata && res.metadata.message) ? res.metadata.message : 'Terjadi kesalahan saat mengambil data.';
                    $('#bodyAntrean').html(`<tr><td colspan="11" class="text-center text-danger py-4">${res.metadata.code} - ${msg}</td></tr>`);
                }
            },
            error: function(err) {
                btnCari.prop('disabled', false).html('<i class="fas fa-search me-1"></i> Cari Antrean');
                $('#bodyAntrean').html('<tr><td colspan="11" class="text-center text-danger py-4">Gagal menghubungi server.</td></tr>');
            }
        });
    });

    function renderTable(data) {
        let rows = '';
        data.forEach((item, index) => {
            
            const no = index + 1;
            const estimasi = item.estimasidilayani ? new Date(item.estimasidilayani).toLocaleString('id-ID') : '-';
            const created = item.createdtime ? new Date(item.createdtime).toLocaleString('id-ID') : '-';
            
            let statusBadge = '';
            if (item.status === 'Selesai dilayani') statusBadge = '<span class="badge bg-success">Selesai</span>';
            else if (item.status === 'Sedang dilayani') statusBadge = '<span class="badge bg-warning text-dark">Sedang dilayani</span>';
            else if (item.status === 'Batal') statusBadge = '<span class="badge bg-danger">Batal</span>';
            else statusBadge = '<span class="badge bg-info">Belum dilayani</span>';

            rows += `
                <tr>
                    <td class="text-center">${no}</td>
                    <td>
                        <div class="mb-1"><span class="badge bg-secondary">Booking: ${item.kodebooking}</span></div>
                        <div style="font-size:0.8rem;">
                            <strong>NIK:</strong> ${item.nik || '-'}<br>
                            <strong>JKN:</strong> ${item.nokapst || '-'}<br>
                            <strong>RM:</strong> ${item.norekammedis || '-'}
                        </div>
                    </td>
                    <td>
                        <div style="font-size:0.8rem;">
                            <strong>Poli:</strong> ${item.kodepoli || '-'}<br>
                            <strong>Dokter:</strong> ${item.kodedokter || '-'}<br>
                            <strong>Tgl:</strong> ${item.tanggal || '-'}<br>
                            <strong>Jam:</strong> ${item.jampraktek || '-'}
                        </div>
                    </td>
                    <td>${item.nohp || '-'}</td>
                    <td>${item.jeniskunjungan || '-'}</td>
                    <td>${item.nomorreferensi || '-'}</td>
                    <td>${item.sumberdata || '-'}</td>
                    <td class="text-center">${item.ispeserta ? '<span class="text-success"><i class="fas fa-check"></i> Ya</span>' : '<span class="text-muted"><i class="fas fa-times"></i> Tidak</span>'}</td>
                    <td>
                        <div class="text-center mb-1"><span class="badge bg-primary" style="font-size:1rem;">${item.noantrean || '-'}</span></div>
                        <div style="font-size:0.75rem;" class="text-muted text-center">Estimasi:<br>${estimasi}</div>
                    </td>
                    <td style="font-size:0.75rem;">${created}</td>
                    <td class="text-center">
                        ${statusBadge}
                    </td>
                </tr>
            `;
        });

        $('#bodyAntrean').html(rows);

        table = $('#tableAntrean').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
            },
            "pageLength": 25,
            "responsive": true,
            "autoWidth": false
        });
    }

    function renderResume(data) {
        let pesertaBPJS = 0, nonPesertaBPJS = 0;
        let BPJS_by_MJKN = 0, BPJS_by_Bridging = 0;
        let sSelesai = 0, sSedang = 0, sBelum = 0, sBatal = 0;
        let sSelesai_BPJS = 0, sSelesai_NonBPJS = 0;
        let sBatal_BPJS = 0, sBatal_NonBPJS = 0;

        data.forEach(item => {
            // Status
            if (item.status === 'Selesai dilayani') {
                sSelesai++;
                item.ispeserta ? sSelesai_BPJS++ : sSelesai_NonBPJS++;
            } else if (item.status === 'Sedang dilayani') {
                sSedang++;
            } else if (item.status === 'Belum dilayani') {
                sBelum++;
            } else if (item.status === 'Batal') {
                sBatal++;
                item.ispeserta ? sBatal_BPJS++ : sBatal_NonBPJS++;
            }

            // Peserta
            if (item.ispeserta) {
                pesertaBPJS++;
                if (item.sumberdata === 'Mobile JKN') {
                    BPJS_by_MJKN++;
                } else if (item.sumberdata === 'Bridging Antrean' || item.sumberdata === 'Bridging') {
                    BPJS_by_Bridging++;
                }
            } else {
                nonPesertaBPJS++;
            }
        });

        $('#resPesertaBPJS').text(pesertaBPJS);
        $('#resMJKN').text(BPJS_by_MJKN);
        $('#resBridging').text(BPJS_by_Bridging);
        $('#resNonPesertaBPJS').text(nonPesertaBPJS);
        
        $('#resSelesai').text(sSelesai);
        $('#resSelesaiBPJS').text(sSelesai_BPJS);
        $('#resSelesaiUmum').text(sSelesai_NonBPJS);

        $('#resBatal').text(sBatal);
        $('#resBatalBPJS').text(sBatal_BPJS);
        $('#resBatalUmum').text(sBatal_NonBPJS);

        $('#resSedang').text(sSedang);
        $('#resBelum').text(sBelum);

        $('#resumeStatusContainer').fadeIn();
    }

    // ── SYNC BPJS LOGIC ──
    const btnSync = document.getElementById('btnSync');
    const progressPanel = document.getElementById('syncProgressPanel');

    function triggerSync() {
        const tanggal = document.getElementById('inputTanggal').value;
        const urlQL   = document.getElementById('inputUrlQL').value;
        
        if(!urlQL) {
            alert('Silakan pilih Cabang QL terlebih dahulu.');
            return;
        }

        setButtonLoading(true);
        progressPanel.style.display = 'block';
        progressPanel.style.borderLeftColor = 'var(--color-info)';
        progressPanel.querySelector('.sync-title .spinner-border').style.display = 'inline-block';
        document.getElementById('syncProgressItems').innerHTML = '';
        document.getElementById('syncProgressSummary').textContent = 'Menjadwalkan job ke queue...';

        const params = new URLSearchParams({ tanggal, urlQL });

        fetch(`{{ url('/api/vclaim/sync-antrol-antrean') }}?${params}`, { method: 'GET' })
            .then(r => r.json())
            .then(data => {
                const syncIds = data.sync_ids || [];
                if (!syncIds.length) {
                    showToast('Tidak ada job yang dijadwalkan.', 'warning');
                    setButtonLoading(false);
                    return;
                }
                document.getElementById('syncProgressSummary').textContent =
                    `${syncIds.length} job dispatched ke queue. Memantau progres...`;
                startPolling(syncIds);
            })
            .catch(() => {
                showToast('Gagal menghubungi server. Coba lagi.', 'error');
                setButtonLoading(false);
                progressPanel.style.display = 'none';
            });
    }

    if (btnSync) btnSync.addEventListener('click', triggerSync);

    let pollInterval = null;

    function startPolling(syncIds) {
        if (pollInterval) clearInterval(pollInterval);
        let attempts = 0;
        const maxAttempts = 150; // ~5 menit

        pollInterval = setInterval(async () => {
            attempts++;
            try {
                const qs  = syncIds.map(id => `ids[]=${id}`).join('&');
                const res = await fetch(`{{ url('/api/vclaim/sync-antrol-status') }}?${qs}`);
                const data = await res.json();

                updateProgressUI(data);

                if (data.is_done) {
                    clearInterval(pollInterval);
                    pollInterval = null;
                    setButtonLoading(false);

                    const msg = data.has_error
                        ? `Sync selesai (ada error).`
                        : `Sync berhasil! ${data.total_sync} data antrean tersimpan.`;
                    showToast(msg, data.has_error ? 'warning' : 'success');

                    // Refresh table otomatis
                    $('#filterForm').submit();
                } else if (attempts >= maxAttempts) {
                    clearInterval(pollInterval);
                    pollInterval = null;
                    setButtonLoading(false);
                    showToast('Sync belum selesai setelah 5 menit. Pastikan queue worker berjalan.', 'warning');
                }
            } catch (e) {
                console.error('Polling error:', e);
            }
        }, 2000);
    }

    function updateProgressUI(data) {
        const container = document.getElementById('syncProgressItems');
        container.innerHTML = '';

        const statusLabel = {
            pending:    { text: 'Menunggu...',   cls: 'bg-secondary', width: '30%'  },
            processing: { text: 'Memproses...',  cls: 'bg-info',      width: '65%'  },
            success:    { text: 'Selesai ✓',   cls: 'bg-success',   width: '100%' },
            error:      { text: 'Error ✗',     cls: 'bg-danger',    width: '100%' },
        };

        (data.logs || []).forEach(log => {
            const s = statusLabel[log.status] || statusLabel.pending;
            const extra = log.status === 'success'
                ? ` — ${log.total_data} data`
                : (log.status === 'error' ? ` — ${log.message || ''}` : '');

            container.insertAdjacentHTML('beforeend', `
                <div class="mb-2" style="font-size:0.85rem;">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="fw-bold">${log.kode_ql}</span>
                        <span>${s.text}${extra}</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar ${s.cls} ${log.status !== 'success' && log.status !== 'error' ? 'progress-bar-animated progress-bar-striped' : ''}"
                             style="width:${s.width}; transition: width .4s ease;"></div>
                    </div>
                </div>
            `);
        });

        if (data.is_done) {
            document.getElementById('syncProgressSummary').textContent =
                `Selesai. Total ${data.total_sync} data berhasil di-sync.`;
            progressPanel.style.borderLeftColor = data.has_error ? 'var(--color-danger)' : 'var(--color-success)';
            progressPanel.querySelector('.sync-title .spinner-border').style.display = 'none';
        }
    }

    function setButtonLoading(isLoading) {
        if (btnSync) {
            btnSync.disabled = isLoading;
            btnSync.innerHTML = isLoading
                ? '<span class="spinner-border spinner-border-sm me-1"></span> Syncing...'
                : '<i class="fas fa-sync-alt me-1"></i> Sync BPJS';
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
        icon.className  = `fas fa-lg me-2 ${icons[type] || icons.success}`;
        msgEl.textContent = msg;
        toast.style.display = 'block';
        setTimeout(() => { toast.style.display = 'none'; }, 5000);
    }
});
</script>
@endpush
