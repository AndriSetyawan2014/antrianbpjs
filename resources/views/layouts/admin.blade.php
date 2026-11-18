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
        <div class="modal-dialog modal-dialog-scrollable modal-xl" style="max-width: 95vw; width: 1200px;">
            <div class="modal-content border-0 shadow">
                <div class="modal-header py-2 px-3" style="background:var(--color-primary);">
                    <h6 class="modal-title text-white m-0" id="jsonModalTitle" style="font-size: 0.95rem;">
                        <i class="fas fa-list-alt me-2"></i>Detail Data
                    </h6>
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn btn-sm btn-outline-light py-1 px-2 me-3" id="btnCopyJson" title="Salin JSON" style="font-size: 0.8rem;">
                            <i class="fas fa-copy"></i> Salin JSON
                        </button>
                        <button type="button" class="btn-close btn-close-white" style="font-size: 0.75rem;" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                </div>
                <div class="modal-body p-0" style="background-color: #f8fafc;">
                    <div id="jsonContentArea"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- JSON Viewer Script -->
    <script>
        let currentRawJson = '';
        function escapeHtml(unsafe) {
            if (unsafe == null) return '';
            if (typeof unsafe !== 'string') unsafe = String(unsafe);
            return unsafe.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }

        const taskFieldLabels = {
            'kodebooking': 'Kode Booking',
            'taskid': 'Task ID',
            'waktu': 'Waktu (Epoch / Timestamp)',
            'idpendaftaran': 'ID Pendaftaran',
            'tanggal': 'Tanggal',
            'jam': 'Jam',
            'code': 'Status Code',
            'message': 'Pesan / Respon',
            'metadata': 'Metadata Respon',
            'response': 'Data Respon',
            'norm': 'No. RM',
            'nojkn': 'No. Kartu JKN',
            'nik': 'NIK Pasien',
            'namadokter': 'Nama Dokter',
            'namaunit': 'Nama Poli / Unit',
            'kodepoli': 'Kode Poli',
            'kodedokter': 'Kode Dokter',
            'jammulai': 'Jam Mulai',
            'jamakhir': 'Jam Akhir',
            'reupload': 'Status Reupload'
        };

        function formatEpochToWib(val) {
            let num = parseInt(val, 10);
            if (!isNaN(num) && String(val).length >= 12) {
                let d = new Date(num > 100000000000 ? num : num * 1000);
                if (!isNaN(d.getTime())) {
                    let day = String(d.getDate()).padStart(2, '0');
                    let mo = String(d.getMonth() + 1).padStart(2, '0');
                    let yr = d.getFullYear();
                    let hr = String(d.getHours()).padStart(2, '0');
                    let min = String(d.getMinutes()).padStart(2, '0');
                    let sec = String(d.getSeconds()).padStart(2, '0');
                    return `${day}-${mo}-${yr} ${hr}:${min}:${sec} WIB`;
                }
            }
            return null;
        }

        function renderJsonFormView(obj) {
            if (obj === null || obj === undefined) return '<div class="text-muted fst-italic p-3">Data kosong (null)</div>';
            if (typeof obj !== 'object') {
                return `<div class="p-3 fs-6">${escapeHtml(obj)}</div>`;
            }

            if (Array.isArray(obj)) {
                if (obj.length === 0) return '<div class="text-muted fst-italic p-3">Daftar kosong []</div>';
                let html = '<div class="d-flex flex-column gap-3 p-3">';
                obj.forEach((item, idx) => {
                    html += `<div class="card border shadow-sm">
                                <div class="card-header bg-light py-2 px-3 fw-bold text-secondary" style="font-size:0.8rem;">
                                    <i class="fas fa-layer-group me-1"></i> Data #${idx + 1}
                                </div>
                                <div class="card-body p-0">${renderJsonFormView(item)}</div>
                             </div>`;
                });
                html += '</div>';
                return html;
            }

            let html = '<div class="table-responsive m-0"><table class="table table-bordered table-striped m-0" style="font-size:0.84rem;"><tbody>';
            for (let key in obj) {
                let label = taskFieldLabels[key.toLowerCase()] || key;
                let val = obj[key];

                let formattedVal = '';
                if (val === null || val === undefined) {
                    formattedVal = '<span class="badge bg-secondary">null</span>';
                } else if (typeof val === 'boolean') {
                    formattedVal = val ? '<span class="badge bg-success">True</span>' : '<span class="badge bg-danger">False</span>';
                } else if (key.toLowerCase() === 'code') {
                    let cNum = parseInt(val, 10);
                    let cBadge = (cNum === 200 || cNum === 208) ? 'bg-success' : (cNum === 0 ? 'bg-warning text-dark' : 'bg-danger');
                    formattedVal = `<span class="badge ${cBadge} fs-6 px-2 py-1">${escapeHtml(val)}</span>`;
                } else if (key.toLowerCase() === 'taskid') {
                    formattedVal = `<span class="badge bg-primary fs-6 px-2 py-1">Task ${escapeHtml(val)}</span>`;
                } else if (key.toLowerCase() === 'waktu') {
                    let wib = formatEpochToWib(val);
                    formattedVal = `<div class="fw-bold text-primary font-monospace">${escapeHtml(val)}</div>`;
                    if (wib) {
                        formattedVal += `<small class="text-success fw-bold"><i class="fas fa-clock me-1"></i>${wib}</small>`;
                    }
                } else if (typeof val === 'object') {
                    formattedVal = `<div class="border rounded bg-white mt-1">${renderJsonFormView(val)}</div>`;
                } else {
                    let strVal = String(val);
                    if (key.toLowerCase() === 'message') {
                        let isSucc = strVal.toLowerCase().includes('success') || strVal.toLowerCase().includes('ok') || strVal.toLowerCase().includes('sudah ada');
                        let isWarn = strVal.toLowerCase().includes('menunggu') || strVal.toLowerCase().includes('antrean');
                        let alertClass = isSucc ? 'alert-success text-success' : (isWarn ? 'alert-warning text-warning' : 'alert-danger text-danger');
                        formattedVal = `<div class="alert ${alertClass} py-1 px-2 m-0 fw-bold border-0" style="font-size:0.84rem;"><i class="fas fa-info-circle me-1"></i>${escapeHtml(strVal)}</div>`;
                    } else {
                        formattedVal = `<span class="fw-semibold text-dark">${escapeHtml(strVal)}</span>`;
                    }
                }

                html += `<tr>
                            <td style="width:32%; background:#f1f5f9; color:#475569; font-weight:600; vertical-align:top; padding:8px 12px;">
                                <div>${escapeHtml(label)}</div>
                                ${label !== key ? `<small class="text-muted font-monospace" style="font-size:0.7rem;">(${escapeHtml(key)})</small>` : ''}
                            </td>
                            <td style="vertical-align:middle; padding:8px 12px; background:#fff; word-break:break-word;">
                                ${formattedVal}
                            </td>
                         </tr>`;
            }
            html += '</tbody></table></div>';
            return html;
        }

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-json-view');
            if (!btn) return;
            currentRawJson = btn.getAttribute('data-json') || '';
            const title = btn.getAttribute('data-title') || 'Detail Data';
            document.getElementById('jsonModalTitle').innerHTML = '<i class="fas fa-file-code me-2"></i>' + escapeHtml(title);
            function decodeEntities(s) {
                return s.replace(/&quot;/g, '"').replace(/&#039;/g, "'").replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&amp;/g, '&');
            }
            const container = document.getElementById('jsonContentArea');
            let decoded = decodeEntities(currentRawJson).trim();
            let parsed = null;
            let parseSuccess = false;

            try {
                if (decoded !== '') {
                    parsed = JSON.parse(decoded);
                    if (typeof parsed === 'string') {
                        try { parsed = JSON.parse(parsed); } catch (e2) {}
                    }
                    parseSuccess = true;
                }
            } catch (ex) {
                parseSuccess = false;
            }

            let prettyJson = parseSuccess ? JSON.stringify(parsed, null, 2) : decoded;
            let leftFormHtml = parseSuccess ? renderJsonFormView(parsed) : `<div class="p-4 text-muted fst-italic">Data tidak berformat JSON valid:<br><pre class="mt-2 font-monospace">${escapeHtml(decoded)}</pre></div>`;

            container.innerHTML = `
                <div class="row g-0" style="min-height: 480px;">
                    <div class="col-lg-6 border-end d-flex flex-column" style="background:#fff;">
                        <div class="p-2 px-3 bg-light border-bottom d-flex align-items-center justify-content-between">
                            <span class="fw-bold text-dark" style="font-size:0.82rem;">
                                <i class="fas fa-table-list text-primary me-1"></i> Tampilan Form Rapi
                            </span>
                            <span class="badge bg-primary-subtle text-primary border border-primary" style="font-size:0.7rem;">Informatif</span>
                        </div>
                        <div class="p-3" style="max-height: 72vh; overflow-y: auto;">
                            ${leftFormHtml}
                        </div>
                    </div>
                    <div class="col-lg-6 d-flex flex-column" style="background:#1e293b;">
                        <div class="p-2 px-3 border-bottom d-flex align-items-center justify-content-between" style="background:#0f172a; border-color:#334155 !important;">
                            <span class="fw-bold text-light" style="font-size:0.82rem;">
                                <i class="fas fa-code text-warning me-1"></i> Format JSON Asli (Raw)
                            </span>
                            <span class="badge bg-secondary" style="font-size:0.7rem;">JSON Source</span>
                        </div>
                        <div class="p-3" style="max-height: 72vh; overflow-y: auto;">
                            <pre class="m-0 font-monospace" style="font-size:0.8rem; line-height:1.45; color:#38bdf8; white-space:pre-wrap; word-break:break-all;">${escapeHtml(prettyJson)}</pre>
                        </div>
                    </div>
                </div>
            `;

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