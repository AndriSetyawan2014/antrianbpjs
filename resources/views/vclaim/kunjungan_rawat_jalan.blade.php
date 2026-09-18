@extends('layouts.admin')

@section('page_title')
    <i class="fas fa-hospital-user me-1" style="color:var(--color-primary);"></i>
    Monitoring Kunjungan Rawat Jalan
@endsection

{{-- styles VClaim dimuat global via Vite (resources/css/vclaim.css) --}}

@section('content')
<div class="row g-3">
    <div class="col-12">

        {{-- ══ Control Bar ════════════════════════════════════════════ --}}
        <form id="filterForm" method="GET" action="{{ route('vclaim.kunjungan.jalan') }}" class="vclaim-control-bar">
            <div class="filter-group">
                <div>
                    <label class="form-label">Tanggal Kunjungan</label>
                    <input type="date" id="inputTanggal" name="tanggal" class="form-control"
                           value="{{ $tanggal }}" max="{{ date('Y-m-d') }}" style="width:155px;">
                </div>
                <div>
                    <label class="form-label">Cabang QL</label>
                    <select name="urlQL" class="form-select" style="width:170px;">
                        <option value="">Semua Cabang</option>
                        @foreach($availableQLs as $ql)
                            <option value="{{ $ql }}" {{ strtoupper($urlQL) === $ql ? 'selected' : '' }}>{{ $ql }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-sm d-block" style="height:38px;padding:0 1rem;">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                </div>
            </div>
            <div class="d-flex gap-2 align-items-end">
                <button type="button" id="btnSync" class="btn btn-success btn-sm" style="height:38px;padding:0 1.1rem;"
                        data-tanggal="{{ $tanggal }}" data-urlql="{{ $urlQL }}">
                    <i class="fas fa-sync-alt me-1"></i> Sync BPJS
                </button>
                <a href="{{ route('vclaim.kunjungan.jalan') }}" class="btn btn-secondary btn-sm d-flex align-items-center" style="height:38px;padding:0 .85rem;">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>

        {{-- ══ Sync Progress Panel ════════════════════════════════════ --}}
        <div id="syncProgressPanel">
            <div class="sync-title">
                <span class="spinner-border spinner-border-sm text-info" role="status"></span>
                <span>Menyinkronisasi data dari API BPJS VClaim…</span>
            </div>
            <div id="syncProgressItems"></div>
            <div id="syncProgressSummary" style="font-size:.78rem; color:var(--color-text-muted); margin-top:8px;"></div>
        </div>

        {{-- ══ Stat Cards ═════════════════════════════════════════════ --}}
        <div class="stat-cards">
            @forelse($availableQLs as $ql)
                @php $st = $statistik[$ql] ?? null; @endphp
                <div class="stat-card" onclick="filterByQL('{{ $ql }}')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();filterByQL('{{ $ql }}');}" role="button" tabindex="0" style="cursor:pointer;" title="Filter data {{ $ql }}" aria-label="Filter data cabang {{ $ql }}">
                    <div class="ql-label">{{ $ql }}</div>
                    <div class="ql-total">
                        {{ $st ? number_format($st->total) : 0 }}
                        <span>pasien</span>
                    </div>
                    <div class="ql-sync">
                        @if($st && $st->last_sync)
                            <i class="fas fa-check-circle text-success"></i>
                            Sync: {{ \Carbon\Carbon::parse($st->last_sync)->format('H:i') }}
                        @else
                            <i class="fas fa-clock text-warning"></i> Belum di-sync
                        @endif
                    </div>
                </div>
            @empty
                <div class="stat-card"><div class="ql-label">-</div><div class="ql-total">0 <span>pasien</span></div></div>
            @endforelse
            <div class="stat-card stat-card-total" onclick="filterByQL('')" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();filterByQL('');}" role="button" tabindex="0" style="cursor:pointer;" title="Tampilkan Semua Cabang" aria-label="Tampilkan semua cabang">
                <div class="ql-label">Total Semua</div>
                <div class="ql-total">
                    {{ number_format($statistik->sum('total')) }}
                    <span>pasien</span>
                </div>
                <div class="ql-sync">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}</div>
            </div>
        </div>

        {{-- ══ Tabel Kunjungan ════════════════════════════════════════ --}}
        <div class="vclaim-table-wrap">
            @if($kunjungan->count())
            <div class="table-responsive" style="max-height:calc(100vh - 320px);">
                <table class="table" id="tableKunjungan">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th>Cabang</th>
                            <th>No SEP</th>
                            <th>No Kartu</th>
                            <th>Nama Peserta</th>
                            <th>Poli</th>
                            <th>Diagnosa</th>
                            <th>Kelas</th>
                            <th>Tgl SEP</th>
                            <th>Waktu Sync</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kunjungan as $i => $row)
                        <tr class="kunjungan-row" style="cursor: pointer;" data-row='@json($row)' title="Klik untuk melihat detail" tabindex="0" aria-label="Lihat detail kunjungan {{ $row->no_sep ?? '' }}">
                            <td style="color:var(--color-text-muted);font-size:.75rem;">
                                {{ ($kunjungan->currentPage() - 1) * $kunjungan->perPage() + $loop->iteration }}
                            </td>
                            <td>
                                @php $qlLower = strtolower($row->kode_ql); @endphp
                                <span class="badge-ql badge-{{ $qlLower }}">{{ $row->kode_ql }}</span>
                            </td>
                            <td class="cell-sep">{{ $row->no_sep ?? '-' }}</td>
                            <td class="cell-kartu">{{ $row->no_kartu ?? '-' }}</td>
                            <td class="cell-nama">{{ $row->nama ?? '-' }}</td>
                            <td style="color:var(--color-text-secondary);">
                                {{ $row->poli ? \Str::limit($row->poli, 28) : '-' }}
                            </td>
                            <td>
                                @if($row->diagnosa)
                                    <span class="badge-diagnosa">{{ $row->diagnosa }}</span>
                                @else
                                    <span style="color:var(--color-text-muted);">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @php $kelas = $row->kelas_rawat; @endphp
                                @if($kelas && $kelas !== '-')
                                    <span class="kelas-{{ $kelas }}">{{ $kelas }}</span>
                                @else
                                    <span style="color:var(--color-text-muted);">-</span>
                                @endif
                            </td>
                            <td class="cell-date">
                                {{ $row->tgl_sep ? \Carbon\Carbon::parse($row->tgl_sep)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="cell-time">
                                <i class="fas fa-clock me-1" style="font-size:.65rem;"></i>
                                {{ $row->synced_at ? \Carbon\Carbon::parse($row->synced_at)->format('H:i:s') : '-' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-between align-items-center px-3 py-2 pagination-bar">
                <div class="pagination-info">
                    Menampilkan {{ $kunjungan->firstItem() }}–{{ $kunjungan->lastItem() }}
                    dari <strong>{{ number_format($kunjungan->total()) }}</strong> data
                </div>
                <div>{{ $kunjungan->links() }}</div>
            </div>

            @else
            <div class="empty-state">
                <i class="fas fa-hospital-user"></i>
                <p>Belum ada data kunjungan rawat jalan untuk tanggal <strong>{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}</strong>.</p>
                <p class="mt-1" style="font-size:.8rem;">Klik tombol <strong>Sync BPJS</strong> untuk mengambil data dari API VClaim.</p>
                <button type="button" class="btn-sync-trigger" id="btnSyncEmpty">
                    <i class="fas fa-sync-alt"></i> Sync BPJS Sekarang
                </button>
            </div>
            @endif
        </div>

    </div>
</div>

{{-- ══ Modal Detail Kunjungan ════════════════════════════════ --}}
<div class="modal fade" id="modalDetailKunjungan" tabindex="-1" aria-labelledby="modalDetailKunjunganLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background: var(--color-primary); color: white;">
                <h5 class="modal-title" id="modalDetailKunjunganLabel"><i class="fas fa-file-medical me-2"></i>Detail Kunjungan Rawat Jalan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted mb-1" style="font-size: .8rem; text-transform: uppercase; letter-spacing: .05em;">No SEP</label>
                        <div id="detailNoSep" class="fw-bold" style="font-family: monospace; font-size: 1.1rem; color: var(--color-primary);">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted mb-1" style="font-size: .8rem; text-transform: uppercase; letter-spacing: .05em;">No Kartu BPJS</label>
                        <div id="detailNoKartu" class="fw-bold" style="font-family: monospace; font-size: 1.1rem;">-</div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label text-muted mb-1" style="font-size: .8rem; text-transform: uppercase; letter-spacing: .05em;">Nama Peserta</label>
                        <div id="detailNama" class="fw-bold fs-5">-</div>
                    </div>
                    
                    <hr class="my-3 text-muted">
                    
                    <div class="col-md-6">
                        <label class="form-label text-muted mb-1" style="font-size: .8rem; text-transform: uppercase; letter-spacing: .05em;">Poliklinik</label>
                        <div id="detailPoli" class="fw-semibold">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted mb-1" style="font-size: .8rem; text-transform: uppercase; letter-spacing: .05em;">Kelas Rawat</label>
                        <div id="detailKelas" class="fw-semibold">-</div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label text-muted mb-1" style="font-size: .8rem; text-transform: uppercase; letter-spacing: .05em;">Diagnosa</label>
                        <div id="detailDiagnosaContainer"><span id="detailDiagnosa" class="badge-diagnosa fs-6 px-2 py-1">-</span></div>
                    </div>

                    <hr class="my-3 text-muted">

                    <div class="col-md-4">
                        <label class="form-label text-muted mb-1" style="font-size: .8rem; text-transform: uppercase; letter-spacing: .05em;">Tanggal SEP</label>
                        <div id="detailTglSep" class="fw-semibold">-</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted mb-1" style="font-size: .8rem; text-transform: uppercase; letter-spacing: .05em;">Cabang QL</label>
                        <div id="detailCabang"></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted mb-1" style="font-size: .8rem; text-transform: uppercase; letter-spacing: .05em;">Waktu Sinkronisasi</label>
                        <div id="detailWaktuSync" class="text-muted" style="font-size: .9rem;">-</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background: var(--color-surface-alt);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
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

    const btnSync      = document.getElementById('btnSync');
    const btnSyncEmpty = document.getElementById('btnSyncEmpty');
    const progressPanel = document.getElementById('syncProgressPanel');

    // ── Sync trigger (both buttons call the same function) ──
    function triggerSync() {
        const tanggal = document.getElementById('inputTanggal').value;
        const urlQL   = document.querySelector('[name="urlQL"]').value;

        setButtonLoading(true);
        progressPanel.style.display = 'block';
        document.getElementById('syncProgressItems').innerHTML = '';
        document.getElementById('syncProgressSummary').textContent = 'Menjadwalkan job ke queue…';

        const params = new URLSearchParams({ tanggal });
        if (urlQL) params.append('urlQL', urlQL);

        fetch(`{{ url('/api/vclaim/sync-kunjungan-jalan') }}?${params}`, { method: 'GET' })
            .then(r => r.json())
            .then(data => {
                const syncIds = data.sync_ids || [];
                if (!syncIds.length) {
                    showToast('Tidak ada job yang dijadwalkan.', 'warning');
                    setButtonLoading(false);
                    return;
                }
                document.getElementById('syncProgressSummary').textContent =
                    `${syncIds.length} job dispatched ke queue. Memantau progres…`;
                startPolling(syncIds);
            })
            .catch(() => {
                showToast('Gagal menghubungi server. Coba lagi.', 'error');
                setButtonLoading(false);
                progressPanel.style.display = 'none';
            });
    }

    if (btnSync)      btnSync.addEventListener('click', triggerSync);
    if (btnSyncEmpty) btnSyncEmpty.addEventListener('click', triggerSync);

    // ── Polling: cek status setiap 2 detik ──
    let pollInterval = null;

    function startPolling(syncIds) {
        if (pollInterval) clearInterval(pollInterval);
        let attempts = 0;
        const maxAttempts = 150; // ~5 menit, cegah polling tak berujung bila worker mati

        pollInterval = setInterval(async () => {
            attempts++;
            try {
                const qs  = syncIds.map(id => `ids[]=${id}`).join('&');
                const res = await fetch(`{{ url('/api/vclaim/sync-status') }}?${qs}`);
                const data = await res.json();

                updateProgressUI(data);

                if (data.is_done) {
                    clearInterval(pollInterval);
                    pollInterval = null;
                    setButtonLoading(false);

                    const msg = data.has_error
                        ? `Sync selesai (ada error). ${data.total_sync} data berhasil.`
                        : `Sync berhasil! ${data.total_sync} data kunjungan tersimpan.`;
                    showToast(msg, data.has_error ? 'warning' : 'success');

                    // Refresh halaman setelah 1.5 detik
                    setTimeout(() => window.location.reload(), 1500);
                } else if (attempts >= maxAttempts) {
                    clearInterval(pollInterval);
                    pollInterval = null;
                    setButtonLoading(false);
                    showToast('Sync belum selesai setelah 5 menit. Pastikan queue worker berjalan, lalu refresh halaman.', 'warning');
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
            pending:    { text: 'Menunggu…',   cls: 'bg-secondary', width: '30%'  },
            processing: { text: 'Memproses…',  cls: 'bg-info',      width: '65%'  },
            success:    { text: 'Selesai ✓',   cls: 'bg-success',   width: '100%' },
            error:      { text: 'Error ✗',     cls: 'bg-danger',    width: '100%' },
        };

        (data.logs || []).forEach(log => {
            const s = statusLabel[log.status] || statusLabel.pending;
            const extra = log.status === 'success'
                ? ` — ${log.total_data} data`
                : (log.status === 'error' ? ` — ${log.message || ''}` : '');

            container.insertAdjacentHTML('beforeend', `
                <div class="ql-progress-item">
                    <div class="ql-progress-label">
                        <span class="ql-name">${log.kode_ql}</span>
                        <span class="ql-state">${s.text}${extra}</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar ${s.cls} ${log.status !== 'success' && log.status !== 'error' ? 'progress-bar-animated' : ''}"
                             style="width:${s.width}; transition: width .4s ease;"></div>
                    </div>
                </div>
            `);
        });

        if (data.is_done) {
            document.getElementById('syncProgressSummary').textContent =
                `Selesai. Total ${data.total_sync} data berhasil di-sync.`;

            // Change panel color to success/error
            progressPanel.style.borderLeftColor = data.has_error
                ? 'var(--color-danger)' : 'var(--color-success)';

            // Hide spinner in title
            progressPanel.querySelector('.sync-title .spinner-border').style.display = 'none';
        }
    }

    // ── UI Helpers ──
    function setButtonLoading(isLoading) {
        if (btnSync) {
            btnSync.disabled = isLoading;
            btnSync.innerHTML = isLoading
                ? '<span class="spinner-border spinner-border-sm me-1"></span> Syncing…'
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
        icon.className  = `fas fa-lg ${icons[type] || icons.success}`;
        msgEl.textContent = msg;
        toast.style.display = 'block';
        setTimeout(() => { toast.style.display = 'none'; }, 5000);
    }

    // ── Row Click to Show Modal ──
    const kunjunganRows = document.querySelectorAll('.kunjungan-row');
    const modalElement = document.getElementById('modalDetailKunjungan');
    let modalInstance = null;
    if (modalElement && typeof bootstrap !== 'undefined') {
        modalInstance = new bootstrap.Modal(modalElement);
    }

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c]));
    }
    function openDetail(dataStr) {
            if (!dataStr) return;

            try {
                const data = JSON.parse(dataStr);
                
                document.getElementById('detailNoSep').textContent = data.no_sep || '-';
                document.getElementById('detailNoKartu').textContent = data.no_kartu || '-';
                document.getElementById('detailNama').textContent = data.nama || '-';
                document.getElementById('detailPoli').textContent = data.poli || '-';
                document.getElementById('detailKelas').textContent = data.kelas_rawat || '-';
                
                const diagContainer = document.getElementById('detailDiagnosaContainer');
                if (data.diagnosa) {
                    diagContainer.innerHTML = `<span class="badge-diagnosa fs-6 px-2 py-1">${escapeHtml(data.diagnosa)}</span>`;
                } else {
                    diagContainer.innerHTML = '<span style="color:var(--color-text-muted);">-</span>';
                }
                
                if (data.tgl_sep) {
                    const d = new Date(data.tgl_sep);
                    document.getElementById('detailTglSep').textContent = isNaN(d) ? data.tgl_sep : d.toLocaleDateString('id-ID');
                } else {
                    document.getElementById('detailTglSep').textContent = '-';
                }
                
                const qlLower = String(data.kode_ql || '').toLowerCase().replace(/[^a-z0-9-]/g, '');
                document.getElementById('detailCabang').innerHTML = `<span class="badge-ql badge-${escapeHtml(qlLower)}">${escapeHtml(data.kode_ql || '-')}</span>`;
                
                if (data.synced_at) {
                    let formattedSync = data.synced_at;
                    const matchSync = data.synced_at.match(/^(\d{4})-(\d{2})-(\d{2})T(\d{2}:\d{2}:\d{2})/);
                    if (matchSync) {
                        formattedSync = `${matchSync[3]}/${matchSync[2]}/${matchSync[1]} ${matchSync[4]}`;
                    }
                    document.getElementById('detailWaktuSync').textContent = formattedSync;
                } else {
                    document.getElementById('detailWaktuSync').textContent = '-';
                }
                
                if (modalInstance) {
                    modalInstance.show();
                } else {
                    // Fallback using jQuery if bootstrap 5 JS object is not available
                    if (typeof $ !== 'undefined') {
                        $('#modalDetailKunjungan').modal('show');
                    }
                }
            } catch (e) {
                console.error('Error parsing row data:', e);
            }
    }
    kunjunganRows.forEach(row => {
        row.addEventListener('click', function() { openDetail(this.getAttribute('data-row')); });
        row.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openDetail(this.getAttribute('data-row')); }
        });
    });

    window.filterByQL = function(ql) {
        const select = document.querySelector('select[name="urlQL"]');
        if (select) {
            select.value = ql;
            document.getElementById('filterForm').submit();
        }
    };

    // Initialize DataTables
    if ($('#tableKunjungan').length > 0) {
        $('#tableKunjungan').DataTable({
            "paging": false, // Disable paging as we use Laravel pagination or we just want to search the current page
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": false,
            "autoWidth": false,
            "responsive": true,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
            }
        });
    }
});
</script>
@endpush
