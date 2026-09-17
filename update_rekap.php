<?php
$file = 'resources/views/vclaim/rekap_kunjungan_rawat_jalan.blade.php';
$content = file_get_contents($file);

// 1. Add CSS
$css = <<<CSS
    .table-vclaim tfoot th:first-child {
        text-align: right;
    }

    /* ── Sync Progress Panel ────────────────────── */
    #syncProgressPanel {
        display: none; background: var(--color-surface); border: 1px solid var(--color-border);
        border-left: 4px solid var(--color-info); border-radius: var(--radius-md);
        padding: 16px 20px; margin-bottom: 20px; box-shadow: var(--shadow-sm);
    }
    #syncProgressPanel .sync-title {
        font-size: .85rem; font-weight: 600; color: var(--color-text-primary);
        margin-bottom: 12px; display: flex; align-items: center; gap: 8px;
    }
    .ql-progress-item { margin-bottom: 10px; }
    .ql-progress-label { display: flex; justify-content: space-between; font-size: .78rem; margin-bottom: 4px; }
    .ql-progress-label .ql-name { font-weight: 600; color: var(--color-text-primary); }
    .ql-progress-label .ql-state { color: var(--color-text-muted); }
    .progress { height: 8px; border-radius: 6px; background: var(--color-border); }
    .progress-bar-animated { animation: progress-pulse 1.2s ease-in-out infinite alternate; }
    @keyframes progress-pulse { from { opacity: .7; } to { opacity: 1; } }

    /* ── Toast ──────────────────────────────────── */
    .sync-toast {
        display: none; position: fixed; bottom: 20px; right: 20px; z-index: 9999;
        background: #fff; border-left: 4px solid var(--color-success); border-radius: var(--radius-md);
        box-shadow: 0 10px 25px rgba(0,0,0,.15); padding: 16px 20px; min-width: 250px;
        animation: slideInUp .4s cubic-bezier(.175, .885, .32, 1.275);
    }
    @keyframes slideInUp { from { transform: translateY(100%); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .sync-toast .toast-body { display: flex; align-items: center; gap: 12px; font-size: .85rem; font-weight: 600; color: var(--color-text-primary); }
CSS;
$content = str_replace("    .table-vclaim tfoot th:first-child {\r\n        text-align: right;\r\n    }", $css, $content);
$content = str_replace("    .table-vclaim tfoot th:first-child {\n        text-align: right;\n    }", $css, $content);

// 2. Add Button
$btn = <<<BTN
                <button type="submit" class="btn btn-primary" style="padding: 8px 16px; font-size:.85rem; border-radius: var(--radius-sm);">
                    <i class="fas fa-filter me-1"></i> Terapkan Filter
                </button>
                <button type="button" class="btn btn-success ms-2" id="btnSyncBulan" style="padding: 8px 16px; font-size:.85rem; border-radius: var(--radius-sm);">
                    <i class="fas fa-sync-alt me-1"></i> Sync 1 Bulan
                </button>
BTN;
$searchBtn1 = <<<BTN
                <button type="submit" class="btn btn-primary" style="padding: 8px 16px; font-size:.85rem; border-radius: var(--radius-sm);">
                    <i class="fas fa-filter me-1"></i> Terapkan Filter
                </button>
BTN;
$content = str_replace($searchBtn1, $btn, $content);

// 3. Add Progress HTML
$progressHtml = <<<HTML
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
HTML;
$content = str_replace("    {{-- Rekap Table --}}", $progressHtml, $content);

// 4. Add Toast HTML and JS
$js = <<<JS
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

    function triggerSyncRange() {
        // Ambil dari selector
        const bulan = document.querySelector('[name="bulan"]').value;
        const tahun = document.querySelector('[name="tahun"]').value;
        
        // Buat format tgl_mulai dan tgl_akhir
        const tglMulai = `\${tahun}-\${bulan}-01`;
        // Tgl akhir adalah akhir bulan (bisa diserahkan ke backend jika backend punya default)
        // Kita hitung hari terakhir bulan tersebut (bisa pake Date JS)
        const lastDay = new Date(tahun, bulan, 0).getDate();
        const tglAkhir = `\${tahun}-\${bulan}-\${lastDay}`;

        setButtonLoading(true);
        progressPanel.style.display = 'block';
        document.getElementById('syncProgressItems').innerHTML = '';
        document.getElementById('syncProgressSummary').textContent = 'Menjadwalkan antrean sinkronisasi (bisa memakan waktu lama)...';

        const params = new URLSearchParams({ tgl_mulai: tglMulai, tgl_akhir: tglAkhir });

        fetch(`{{ url('/api/vclaim/sync-kunjungan-jalan-range') }}?\${params}`, { method: 'GET' })
            .then(r => r.json())
            .then(data => {
                const syncIds = data.sync_ids || [];
                if (!syncIds.length) {
                    showToast('Tidak ada job yang dijadwalkan.', 'warning');
                    setButtonLoading(false);
                    return;
                }
                document.getElementById('syncProgressSummary').textContent =
                    `\${syncIds.length} antrean diproses. Sedang memantau progress...`;
                startPolling(syncIds);
            })
            .catch(() => {
                showToast('Gagal menghubungi server. Coba lagi.', 'error');
                setButtonLoading(false);
                progressPanel.style.display = 'none';
            });
    }

    if (btnSyncBulan) btnSyncBulan.addEventListener('click', triggerSyncRange);

    let pollInterval = null;
    function startPolling(syncIds) {
        if (pollInterval) clearInterval(pollInterval);

        pollInterval = setInterval(async () => {
            try {
                // Jangan kirim semua id via GET parameter jika kepanjangan, 
                // untungnya 30 hari x 4 QL = 120 ids, string length sekitar 1500 chars (masih aman untuk GET).
                const qs  = syncIds.map(id => `ids[]=\${id}`).join('&');
                const res = await fetch(`{{ url('/api/vclaim/sync-status') }}?\${qs}`);
                const data = await res.json();

                updateProgressUI(data);

                if (data.is_done) {
                    clearInterval(pollInterval);
                    pollInterval = null;
                    setButtonLoading(false);

                    const msg = data.has_error
                        ? `Sync bulan ini selesai (ada error). \${data.total_sync} data berhasil.`
                        : `Sync berhasil! \${data.total_sync} kunjungan tersimpan.`;
                    showToast(msg, data.has_error ? 'warning' : 'success');

                    // Refresh halaman setelah 2 detik
                    setTimeout(() => window.location.reload(), 2000);
                }
            } catch (e) {
                console.error('Polling error:', e);
            }
        }, 2500);
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

        // Buat rekapitulasi progress dari data.logs
        // Karena ada >100 logs, kita tidak tampilkan per item log, melainkan totalnya.
        const summaryCount = { pending: 0, processing: 0, success: 0, error: 0 };
        (data.logs || []).forEach(log => {
            if (summaryCount[log.status] !== undefined) summaryCount[log.status]++;
        });

        const totalLogs = data.logs ? data.logs.length : 1;
        const progressPercent = Math.round(((summaryCount.success + summaryCount.error) / totalLogs) * 100);

        container.insertAdjacentHTML('beforeend', `
            <div class="ql-progress-item">
                <div class="ql-progress-label">
                    <span class="ql-name">Progress Keseluruhan (\${progressPercent}%)</span>
                    <span class="ql-state">Sukses: \${summaryCount.success} | Gagal: \${summaryCount.error} | Menunggu: \${summaryCount.pending + summaryCount.processing}</span>
                </div>
                <div class="progress" style="height:12px;">
                    <div class="progress-bar bg-primary"
                         style="width:\${progressPercent}%; transition: width .4s ease;"></div>
                </div>
            </div>
        `);

        if (data.is_done) {
            document.getElementById('syncProgressSummary').textContent =
                `Selesai. Total \${data.total_sync} data kunjungan berhasil ditarik.`;

            progressPanel.style.borderLeftColor = data.has_error
                ? 'var(--color-danger)' : 'var(--color-success)';

            const spinner = progressPanel.querySelector('.sync-title .spinner-border');
            if(spinner) spinner.style.display = 'none';
        }
    }

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
        icon.className  = `fas fa-lg \${icons[type] || icons.success}`;
        msgEl.textContent = msg;
        toast.style.display = 'block';
        setTimeout(() => { toast.style.display = 'none'; }, 5000);
    }
});
</script>
JS;

$searchJs = <<<JS
</div>
@endsection

@push('scripts')
<script>
    // Tidak ada script khusus yang diperlukan untuk fitur rekap sederhana.
</script>
JS;

$content = str_replace($searchJs, $js, $content);

file_put_contents($file, $content);
echo "File updated successfully.";
