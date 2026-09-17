<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pemantauan Data Bridging BPJS</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('dist/img/favicon.ico') }}">

    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <!-- App CSS (global styles + design tokens) -->
    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <!-- Page-specific CSS -->
    @stack('styles')
</head>

<body>
    <div class="wrapper">
        <!-- ═══ Sidebar ═══ -->
        @include('layouts.sidenav')

        <!-- ═══ Top Header ═══ -->
        <header class="top-header">
            <h1 class="system-title d-flex align-items-center mb-0">
                <i class="fas fa-hospital-symbol me-2"></i>
                <span class="d-none d-md-inline">Sistem Pemantauan Bridging BPJS</span>
                <span class="d-inline d-md-none">Bridging BPJS</span>

                @hasSection('page_title')
                <div class="ms-3 ps-3 border-start border-2 border-secondary d-flex align-items-center" style="font-size: 0.95rem; font-weight: 500; color: var(--color-primary-light);">
                    @yield('page_title')
                </div>
                @endif
            </h1>
            <div class="header-right">
                <span class="header-datetime" id="headerDateTime"></span>
                <div class="header-user">
                    <i class="fas fa-user-circle"></i>
                    <span>Admin</span>
                </div>
            </div>
        </header>

        <!-- ═══ Content Wrapper ═══ -->
        <div class="content-wrapper">
            <section class="content">
                <div class="container-fluid px-0">
                    @yield('content')
                </div>
            </section>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- Live Clock -->
    <script>
        function updateClock() {
            const now = new Date();
            const opts = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric',
                           hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
            const el = document.getElementById('headerDateTime');
            if (el) el.textContent = now.toLocaleString('id-ID', opts);
        }
        updateClock();
        setInterval(updateClock, 1000);
    </script>

    <!-- JSON Viewer Modal (shared) -->
    <div class="modal fade" id="jsonViewerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header" style="background:var(--color-primary);">
                    <h5 class="modal-title text-white" id="jsonModalTitle">
                        <i class="fas fa-list-alt me-2"></i>Detail Data
                    </h5>
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn btn-sm btn-outline-light me-3" id="btnCopyJson" title="Salin format JSON">
                            <i class="fas fa-copy"></i> Salin JSON
                        </button>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-4" style="background:var(--color-bg);">
                    <div id="jsonViewerContent"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- JSON Viewer Script (shared) -->
    <script>
        let currentRawJson = '';

        function escapeHtml(unsafe) {
            if(typeof unsafe !== 'string') return unsafe;
            return unsafe.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }

        function renderJsonToHtml(obj) {
            if (obj === null) return '<span style="color:#9ca3af; font-style:italic; font-family:monospace;">null</span>';
            if (typeof obj !== 'object') {
                if (typeof obj === 'boolean') {
                    return `<span style="color:#db2777; font-weight:700; font-family:monospace;">${obj}</span>`;
                }
                if (typeof obj === 'number') {
                    return `<span style="color:#2563eb; font-weight:700; font-family:monospace;">${obj}</span>`;
                }
                return `<span style="color:#16a34a; font-family:monospace; font-weight:500; word-break:break-word;">"${escapeHtml(String(obj))}"</span>`;
            }
            if (Array.isArray(obj)) {
                if (obj.length === 0) return '<span style="color:#adb5bd; font-style:italic; font-family:monospace;">[]</span>';
                let html = '<div class="d-flex flex-column gap-3">';
                obj.forEach((item, idx) => {
                    html += `<div class="border rounded p-3 shadow-sm" style="background:var(--color-surface); border-color:var(--color-border) !important;">
                                <div class="mb-3 border-bottom pb-2" style="font-size:0.85rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:var(--color-text-muted);">
                                    <i class="fas fa-layer-group me-1"></i> Item #${idx + 1}
                                </div>
                                ${renderJsonToHtml(item)}
                             </div>`;
                });
                html += '</div>';
                return html;
            }
            
            let html = '<table class="table table-bordered mb-0 shadow-sm" style="font-size:0.9rem; background:#ffffff; border-color:#e5e7eb; border-radius: 6px; overflow: hidden;"><tbody>';
            for (let key in obj) {
                html += `<tr>
                            <td style="width:30%; background:#f8f9fa; font-weight:700; color:#4b5563; vertical-align:middle; padding: 12px 16px; font-family:monospace; border-color:#e5e7eb;">${escapeHtml(key)}</td>
                            <td style="word-break:break-word; vertical-align:middle; padding: 12px 16px; color:#1f2937; border-color:#e5e7eb; background:#ffffff;">${renderJsonToHtml(obj[key])}</td>
                         </tr>`;
            }
            html += '</tbody></table>';
            return html;
        }

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-json-view');
            if (!btn) return;
            
            currentRawJson = btn.getAttribute('data-json') || '';
            const title = btn.getAttribute('data-title') || 'Detail Data';
            
            document.getElementById('jsonModalTitle').innerHTML = '<i class="fas fa-list-alt me-2"></i>' + title;
            
            const container = document.getElementById('jsonViewerContent');
            try { 
                const parsed = JSON.parse(currentRawJson); 
                container.innerHTML = renderJsonToHtml(parsed);
            } catch(ex) {
                container.innerHTML = `<pre class="bg-white p-3 border rounded shadow-sm" style="font-size: 0.85rem;">${currentRawJson}</pre>`;
            }
            
            new bootstrap.Modal(document.getElementById('jsonViewerModal')).show();
        });

        const btnCopy = document.getElementById('btnCopyJson');
        if(btnCopy) {
            btnCopy.addEventListener('click', function() {
                if(!currentRawJson) return;
                let textToCopy = currentRawJson;
                try {
                    textToCopy = JSON.stringify(JSON.parse(currentRawJson), null, 2);
                } catch(e) {}
                navigator.clipboard.writeText(textToCopy).then(() => {
                    const originalHTML = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-check"></i> Disalin';
                    setTimeout(() => { this.innerHTML = originalHTML; }, 2000);
                });
            });
        }
    </script>

    @stack('scripts')
</body>

</html>