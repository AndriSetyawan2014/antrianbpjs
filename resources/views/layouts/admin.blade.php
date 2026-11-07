<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Antrian BPJS Online — Queen Latifa</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('dist/img/favicon.ico') }}">

    <!-- Preconnect CDN -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://code.jquery.com">
    <link rel="preconnect" href="https://cdn.datatables.net">

    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <!-- App CSS/JS via Vite -->
    @vite(['resources/css/variables.css', 'resources/css/app.css', 'resources/css/index.css', 'resources/css/vclaim.css', 'resources/js/app.js'])

    <!-- Page-specific CSS -->
    @stack('styles')
</head>

<body>
    <!-- Skip to content -->
    <a href="#mainContent" class="skip-link">Lewati ke konten utama</a>

    <div class="wrapper">
        <!-- ═══ Sidebar ═══ -->
        @include('layouts.sidenav')

        <!-- ═══ Top Header ═══ -->
        <header class="top-header">
            <button type="button" id="btnSidebarToggle" class="btn-hamburger"
                aria-label="Buka/tutup menu navigasi" aria-expanded="false" aria-controls="sidebar">
                <i class="fas fa-bars"></i>
            </button>

            <div class="d-flex align-items-center gap-3">
                <h1 class="system-title">
                    <i class="fas fa-hospital me-2"></i>
                    <span>Antrian BPJS Online</span>
                    <span class="d-inline d-md-none">Queen Latifa</span>
                </h1>

                @hasSection('page_title')
                <div class="ms-3 ps-3 border-start border-2 border-primary d-flex align-items-center"
                    style="font-size: 0.95rem; font-weight: 600; color: var(--color-primary);">
                    @yield('page_title')
                </div>
                @endif
            </div>

            <div class="header-right">
                <span class="header-datetime" id="headerDateTime"></span>
                <div class="header-user">
                    <i class="fas fa-user"></i>
                    <span>Admin</span>
                </div>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
        </header>

        <!-- ═══ Content Wrapper ═══ -->
        <div class="content-wrapper">
            <section class="content" id="mainContent" tabindex="-1">
                <div class="container-fluid px-0">

                    {{-- Flash Messages --}}
                    @include('components.flash')

                    {{-- Page Header & Breadcrumb --}}
                    @if(isset($pageTitle) || $__env->hasSection('page_header'))
                        <div class="page-header">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <div>
                                    @yield('page_title')
                                </div>
                                <nav class="breadcrumb-nav" aria-label="breadcrumb">
                                    <span>Antrian BPJS Online</span>
                                    <i class="fas fa-chevron-right text-xs text-muted"></i>
                                    <span class="text-sm font-medium text-gray-600">
                                        @yield('page_subtitle', null)
                                    </span>
                                </nav>
                            </div>
                        </div>
                    @endif

                    <div class="page-body">
                        @yield('content')
                    </div>
                </div>
            </section>

            <footer class="app-footer">
                <span><i class="fas fa-hospital me-1"></i> Antrian BPJS Online — Queen Latifa</span>
                <span class="app-footer-right">{{ date('Y') }} · QLJ · QLKP · QLTMG</span>
            </footer>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        // Default DataTables: Indonesian, sticky header, no auto width
        $.extend(true, $.fn.dataTable.defaults, {
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            },
            autoWidth: false,
            responsive: true
        });
    </script>

    <!-- Live Clock -->
    <script>
        function updateClock() {
            const now = new Date();
            const opts = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
            const el = document.getElementById('headerDateTime');
            if (el) el.textContent = now.toLocaleString('id-ID', opts);
        }
        updateClock();
        setInterval(updateClock, 1000);
    </script>

    <!-- Global UI helpers -->
    <script>
        (function () {
            const btnToggle = document.getElementById('btnSidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const isMobileView = () => window.innerWidth <= 768;

            function setSidebar(open) {
                if (!sidebar) return;
                sidebar.classList.toggle('show', open);
                if (overlay) overlay.classList.toggle('show', open);
                if (btnToggle) btnToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                document.body.classList.toggle('sidebar-open', open);
            }

            function applyCollapsedTitles() {
                const collapsed = document.body.classList.contains('sidebar-collapsed');
                document.querySelectorAll('#sidebar .nav-link').forEach(function (a) {
                    if (collapsed) {
                        if (!a.hasAttribute('title')) {
                            const t = (a.textContent || '').trim().replace(/\s+/g, ' ');
                            if (t) { a.setAttribute('title', t); a.setAttribute('data-auto-title', '1'); }
                        }
                    } else if (a.getAttribute('data-auto-title') === '1') {
                        a.removeAttribute('title');
                        a.removeAttribute('data-auto-title');
                    }
                });
            }

            function setCollapsed(collapsed) {
                document.body.classList.toggle('sidebar-collapsed', collapsed);
                try { localStorage.setItem('ql_sidebar', collapsed ? 'collapsed' : 'expanded'); } catch (e) {}
                if (btnToggle) btnToggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                applyCollapsedTitles();
            }

            try {
                if (!isMobileView() && localStorage.getItem('ql_sidebar') === 'collapsed') {
                    document.body.classList.add('sidebar-collapsed');
                }
            } catch (e) {}
            applyCollapsedTitles();

            if (btnToggle) {
                btnToggle.addEventListener('click', function () {
                    if (isMobileView()) {
                        const isOpen = sidebar && sidebar.classList.contains('show');
                        setSidebar(!isOpen);
                    } else {
                        setCollapsed(!document.body.classList.contains('sidebar-collapsed'));
                    }
                });
            }
            if (overlay) overlay.addEventListener('click', function () { setSidebar(false); });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') setSidebar(false);
            });

            document.querySelectorAll('#sidebar .nav-link').forEach(function (a) {
                a.addEventListener('click', function () {
                    if (window.innerWidth <= 768) setSidebar(false);
                });
            });

            // Toast global
            window.showGlobalToast = function (msg, type = 'success') {
                const toast = document.getElementById('globalToast');
                if (!toast) { alert(msg); return; }
                const icon = document.getElementById('globalToastIcon');
                const msgEl = document.getElementById('globalToastMsg');
                const icons = {
                    success: 'fas fa-check-circle text-success fa-lg',
                    warning: 'fas fa-exclamation-triangle text-warning fa-lg',
                    error: 'fas fa-times-circle text-danger fa-lg',
                    info: 'fas fa-info-circle text-info fa-lg'
                };
                if (icon) icon.className = icons[type] || icons.success;
                if (msgEl) msgEl.textContent = msg;
                toast.classList.remove('show-success', 'show-error', 'show-warning', 'show-info');
                toast.classList.add('show-' + type);
                toast.style.display = 'block';
                clearTimeout(window._globalToastTimer);
                window._globalToastTimer = setTimeout(function () {
                    toast.style.display = 'none';
                }, 4000);
            };

            // Date presets
            function fmt(d) {
                const y = d.getFullYear();
                const m = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return `${y}-${m}-${day}`;
            }
            window.applyDatePreset = function (preset, startSel = '#start_date', endSel = '#end_date') {
                const s = document.querySelector(startSel);
                const e = document.querySelector(endSel);
                if (!s || !e) return;
                const today = new Date();
                if (preset === 'today') { s.value = fmt(today); e.value = fmt(today); }
                else if (preset === 'yesterday') { const y = new Date(today); y.setDate(y.getDate() - 1); s.value = fmt(y); e.value = fmt(y); }
                else if (preset === 'week') { const w = new Date(today); w.setDate(w.getDate() - 6); s.value = fmt(w); e.value = fmt(today); }
                else if (preset === 'month') { const m = new Date(today.getFullYear(), today.getMonth(), 1); s.value = fmt(m); e.value = fmt(today); }
                const form = s.form || s.closest('form');
                if (form) form.submit();
            };
            document.addEventListener('click', function (ev) {
                const b = ev.target.closest('[data-preset]');
                if (!b) return;
                ev.preventDefault();
                window.applyDatePreset(b.getAttribute('data-preset'));
            });

            // Sidebar search filter
            const sidebarSearch = document.getElementById('sidebarSearch');
            const sidebarEmpty = document.getElementById('sidebarSearchEmpty');
            if (sidebarSearch) {
                sidebarSearch.addEventListener('input', function () {
                    const q = this.value.trim().toLowerCase();
                    const links = document.querySelectorAll('#sidebar .nav-link');
                    let visible = 0;
                    links.forEach(function (a) {
                        const text = (a.textContent || '').toLowerCase();
                        const hit = !q || text.includes(q);
                        const li = a.closest('li');
                        const target = li || a;
                        target.style.display = hit ? '' : 'none';
                        if (hit) visible++;
                    });
                    document.querySelectorAll('#sidebar .sidebar-section-label').forEach(function (label) {
                        let el = label.nextElementSibling;
                        let hasVisible = false;
                        while (el && !el.classList.contains('sidebar-section-label')) {
                            if (el.querySelector) {
                                const items = el.querySelectorAll(':scope .nav-link, :scope > .nav-link');
                                items.forEach(function (a) {
                                    const li = a.closest('li');
                                    const t = li || a;
                                    if (t.style.display !== 'none') hasVisible = true;
                                });
                                if (el.classList.contains('nav-link') && el.style.display !== 'none') hasVisible = true;
                            }
                            el = el.nextElementSibling;
                            if (el && el.classList && el.classList.contains('collapse')) {
                                const inner = el.querySelectorAll('.nav-link');
                                inner.forEach(function (a) {
                                    const li = a.closest('li');
                                    const t = li || a;
                                    if (t.style.display !== 'none') hasVisible = true;
                                });
                                el = el.nextElementSibling;
                                continue;
                            }
                            if (!el || (el.tagName === 'A' && el.classList.contains('nav-link'))) {
                                if (el && el.style.display !== 'none') hasVisible = true;
                                break;
                            }
                        }
                        label.style.display = (!q || hasVisible) ? '' : 'none';
                    });
                    if (q) {
                        document.querySelectorAll('#sidebar .collapse').forEach(function (c) {
                            if (c.querySelector('.nav-link:not([style*="none"])')) c.classList.add('show');
                        });
                    }
                    if (sidebarEmpty) sidebarEmpty.style.display = visible ? 'none' : 'block';
                });
            }
        })();
    </script>

    <!-- Global Toast -->
    <div id="globalToast" class="global-toast" role="status" aria-live="polite" style="display:none;">
        <i id="globalToastIcon" class="fas fa-check-circle text-success fa-lg"></i>
        <span id="globalToastMsg"></span>
    </div>

    <!-- JSON Viewer Modal -->
    <div class="modal fade" id="jsonViewerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header py-3 px-4" style="background:var(--color-primary);">
                    <h6 class="modal-title text-white m-0" id="jsonModalTitle" style="font-size: 0.95rem;">
                        <i class="fas fa-list-alt me-2"></i>Detail Data
                    </h6>
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn btn-sm btn-outline-light py-1 px-2 me-3" id="btnCopyJson" title="Salin JSON" style="font-size: 0.8rem;">
                            <i class="fas fa-copy"></i> Salin
                        </button>
                        <button type="button" class="btn-close btn-close-white" style="font-size: 0.75rem;" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                </div>
                <div class="modal-body p-0" style="background-color: var(--color-surface) !important;">
                    <div id="jsonContentArea"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- JSON Viewer Script -->
    <script>
        let currentRawJson = '';
        function escapeHtml(unsafe) {
            if (typeof unsafe !== 'string') return unsafe;
            return unsafe.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }
        function renderJsonToHtml(obj) {
            if (obj === null) return '<span style="color:#9ca3af; font-style:italic; font-family:monospace;">null</span>';
            if (typeof obj !== 'object') {
                if (typeof obj === 'boolean') return `<span style="color:#be185d; font-weight:700; font-family:monospace;">${obj}</span>`;
                if (typeof obj === 'number') return `<span style="color:#2563eb; font-weight:700; font-family:monospace;">${obj}</span>`;
                return `<span style="color:#047857; font-weight:700; font-family:monospace;">"${escapeHtml(obj)}"</span>`;
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
            document.getElementById('jsonModalTitle').innerHTML = '<i class="fas fa-list-alt me-2"></i>' + escapeHtml(title);
            function decodeEntities(s) {
                return s.replace(/&quot;/g, '"').replace(/&#039;/g, "'").replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&amp;/g, '&');
            }
            const container = document.getElementById('jsonContentArea');
            try {
                const parsed = JSON.parse(decodeEntities(currentRawJson));
                const prettyJson = JSON.stringify(parsed, null, 2);
                container.innerHTML = `<div style="background-color: var(--color-surface) !important; font-size: 0.85rem; font-family: monospace; color: var(--color-text-primary); white-space: pre-wrap; word-break: break-word; line-height: 1.4; border: none; max-height: none !important; overflow: visible !important;">${escapeHtml(prettyJson)}</div>`;
            } catch (ex) {
                container.innerHTML = `<div style="background-color: var(--color-surface) !important; font-size: 0.85rem; font-family: monospace; color: var(--color-text-primary); white-space: pre-wrap; word-break: break-word; line-height: 1.4; border: none; max-height: none !important; overflow: visible !important;">${escapeHtml(decodeEntities(currentRawJson))}</div>`;
            }
            new bootstrap.Modal(document.getElementById('jsonViewerModal')).show();
        });
        function fallbackCopyTextToClipboard(text, onSuccess) {
            var textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.top = "0";
            textArea.style.left = "0";
            textArea.style.position = "fixed";
            textArea.style.opacity = "0";
            var modalEl = document.getElementById('jsonViewerModal') || document.body;
            modalEl.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                var successful = document.execCommand('copy');
                if (successful && onSuccess) onSuccess();
            } catch (err) {}
            modalEl.removeChild(textArea);
        }
        const btnCopy = document.getElementById('btnCopyJson');
        if (btnCopy) {
            btnCopy.addEventListener('click', function() {
                if (!currentRawJson) return;
                const btn = this;
                let textToCopy = currentRawJson.replace(/&quot;/g, '"').replace(/&#039;/g, "'").replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&amp;/g, '&');
                try {
                    textToCopy = JSON.stringify(JSON.parse(textToCopy), null, 2);
                } catch (e) {}
                const copySuccess = () => {
                    const originalHTML = btn.innerHTML;
                    btn.innerHTML = '<i class="fas fa-check"></i> Disalin';
                    setTimeout(() => { btn.innerHTML = originalHTML; }, 2000);
                };
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(textToCopy).then(copySuccess).catch(() => fallbackCopyTextToClipboard(textToCopy, copySuccess));
                } else {
                    fallbackCopyTextToClipboard(textToCopy, copySuccess);
                }
            });
        }
    </script>

    @stack('scripts')
</body>

</html>