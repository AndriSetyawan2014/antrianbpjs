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
        <div class="modal-dialog modal-dialog-scrollable" style="max-width: 850px; height: 95vh; margin: 2.5vh auto;">
            <div class="modal-content border-0 shadow" style="height: 100%; max-height: 100%; background-color: #ffffff !important;">
                <div class="modal-header py-2 px-3" style="background:var(--color-primary);">
                    <h6 class="modal-title text-white m-0" id="jsonModalTitle" style="font-size: 0.95rem;">
                        <i class="fas fa-list-alt me-2"></i>Detail Data
                    </h6>
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn btn-sm btn-outline-light py-1 px-2 me-3" id="btnCopyJson" title="Salin format JSON" style="font-size: 0.8rem;">
                            <i class="fas fa-copy"></i> Salin
                        </button>
                        <button type="button" class="btn-close btn-close-white" style="font-size: 0.75rem;" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-0" style="background-color: #ffffff !important;">
                    <div id="jsonContentArea"></div>
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
                return `<span style="color:#059669; font-weight:700; font-family:monospace;">"${escapeHtml(obj)}"</span>`;
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
            
            let html = '<table style="width:100%; border-collapse:collapse; margin:0; font-size:13px;"><tbody>';
            for (let key in obj) {
                html += `<tr style="border-bottom:1px solid #f3f4f6;">
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
            
            const container = document.getElementById('jsonContentArea');
            try {
                // Blade htmlspecialchars() sering menghasilkan double-escaping (menjadi &quot;).
                let jsonToParse = currentRawJson.replace(/&quot;/g, '"');
                const parsed = JSON.parse(jsonToParse); 
                const prettyJson = JSON.stringify(parsed, null, 2);
                container.innerHTML = `<div style="background-color: #ffffff !important; font-size: 0.85rem; font-family: monospace; color: #1f2937; white-space: pre-wrap; word-break: break-word; line-height: 1.4; border: none; max-height: none !important; overflow: visible !important;">${escapeHtml(prettyJson)}</div>`;
            } catch(ex) {
                container.innerHTML = `<div style="background-color: #ffffff !important; font-size: 0.85rem; font-family: monospace; color: #1f2937; white-space: pre-wrap; word-break: break-word; line-height: 1.4; border: none; max-height: none !important; overflow: visible !important;">${currentRawJson}</div>`;
            }
            
            new bootstrap.Modal(document.getElementById('jsonViewerModal')).show();
        });

        function fallbackCopyTextToClipboard(text, onSuccess) {
            var textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.top = "0";
            textArea.style.left = "0";
            textArea.style.position = "fixed";
            textArea.style.opacity = "0"; // Supaya tidak terlihat
            
            // Bootstrap Modal memiliki fitur "Enforce Focus". Jika kita menempelkan textarea ke document.body, 
            // modal akan merebut kembali focus-nya sebelum execCommand('copy') sempat berjalan.
            // Solusinya adalah menempelkan textarea ke dalam modal itu sendiri.
            var modalEl = document.getElementById('jsonViewerModal') || document.body;
            modalEl.appendChild(textArea);
            
            textArea.focus();
            textArea.select();
            try {
                var successful = document.execCommand('copy');
                if (successful && onSuccess) {
                    onSuccess();
                }
            } catch (err) {}
            
            modalEl.removeChild(textArea);
        }

        const btnCopy = document.getElementById('btnCopyJson');
        if(btnCopy) {
            btnCopy.addEventListener('click', function() {
                if(!currentRawJson) return;
                
                const btn = this;
                let textToCopy = currentRawJson.replace(/&quot;/g, '"');
                try {
                    textToCopy = JSON.stringify(JSON.parse(textToCopy), null, 2);
                } catch(e) {}
                
                const copySuccess = () => {
                    const originalHTML = btn.innerHTML;
                    btn.innerHTML = '<i class="fas fa-check"></i> Disalin';
                    setTimeout(() => { btn.innerHTML = originalHTML; }, 2000);
                };

                // Jika HTTPS maka coba pakai clipboard API, jika gagal/HTTP fallback ke execCommand
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(textToCopy)
                        .then(copySuccess)
                        .catch(() => fallbackCopyTextToClipboard(textToCopy, copySuccess));
                } else {
                    fallbackCopyTextToClipboard(textToCopy, copySuccess);
                }
            });
        }
    </script>

    @stack('scripts')
</body>

</html>