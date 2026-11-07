<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Antrian BPJS Online</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('dist/img/favicon.ico') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --ink: #0f2930;
            --ink-soft: #486972;
            --brand-teal: #128298;
            --brand-teal-dark: #0d697b;
            --brand-cyan: #229ab0;
            --teal-soft: #e6f6f9;
            --line: #cde5ea;
            --amber: #f59e0b;
            --amber-soft: #fef5e3;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', system-ui, sans-serif;
            background-color: #e8f6f8;
            background-image:
                radial-gradient(#bfe4ec 1.2px, transparent 1.2px),
                radial-gradient(#d5eef3 1.2px, #e8f6f8 1.2px);
            background-size: 32px 32px;
            background-position: 0 0, 16px 16px;
            color: var(--ink);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 20px;
            overflow-x: hidden;
        }

        /* Dekorasi latar cerah & bersih */
        .bg-shape {
            position: fixed;
            pointer-events: none;
            z-index: 0;
        }
        .bg-shape.circle-a {
            width: 560px; height: 560px;
            border-radius: 50%;
            background: #cdeef4;
            top: -160px; left: -120px;
        }
        .bg-shape.circle-b {
            width: 500px; height: 500px;
            border-radius: 50%;
            background: #fdeac8;
            bottom: -170px; right: -100px;
        }
        .bg-shape.circle-c {
            width: 260px; height: 260px;
            border-radius: 50%;
            background: #d8f3ec;
            top: 12%; right: 14%;
        }
        .bg-shape.ring {
            width: 320px; height: 320px;
            border-radius: 50%;
            border: 2px solid #9fd8e4;
            top: 8%; right: 10%;
        }
        .bg-shape.ring-left {
            width: 240px; height: 240px;
            border-radius: 50%;
            border: 2px dashed #eab308;
            opacity: 0.35;
            bottom: 10%; left: 10%;
        }

        /* Bar informasi atas di luar kartu agar ruang halaman terisi rapi */
        .top-status-bar {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 960px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
            padding: 10px 18px;
            background: rgba(255, 255, 255, 0.88);
            border: 1px solid #cde6ec;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(13, 98, 115, 0.06);
            font-size: 0.76rem;
            color: var(--ink-soft);
        }
        .top-status-left {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            color: var(--ink);
        }
        .top-status-left i {
            color: var(--brand-teal);
        }
        .top-status-pills {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .status-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            background: #eff9fb;
            border: 1px solid #cce7ed;
            font-size: 0.71rem;
            font-weight: 600;
            color: var(--brand-teal-dark);
        }
        .status-chip .pulse {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #10b981;
        }

        .auth-shell {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 460px 500px;
            width: 100%;
            max-width: 960px;
            background: #ffffff;
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid #cde5ea;
            box-shadow: 0 22px 55px rgba(13, 98, 115, 0.14), 0 3px 10px rgba(13, 98, 115, 0.06);
        }

        /* Panel kiri — biru-toska cerah mengikuti warna logo QL */
        .brand-panel {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            padding: 28px 30px 115px;
            background-color: var(--brand-teal);
            background-image: url('{{ asset('dist/img/login-bg.svg') }}?v=2');
            background-repeat: no-repeat;
            background-position: bottom center;
            background-size: 100% auto;
            color: #ffffff;
        }
        .brand-top {
            position: relative;
            z-index: 1;
        }
        .brand-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .brand-logo {
            height: 52px;
            width: auto;
            max-width: 72%;
            object-fit: contain;
            filter: drop-shadow(0 2px 6px rgba(8, 52, 62, 0.22));
        }
        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.34);
            font-size: 0.68rem;
            font-weight: 600;
            letter-spacing: 0.03em;
            color: #ffffff;
            white-space: nowrap;
        }
        .brand-badge .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #6ee7b7;
        }
        .brand-panel h1 {
            margin: 14px 0 3px;
            font-size: 1.24rem;
            font-weight: 700;
            letter-spacing: 0.01em;
        }
        .brand-panel .brand-sub {
            margin: 0;
            font-size: 0.72rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.86);
        }
        .brand-divider {
            width: 48px;
            height: 3.5px;
            margin: 14px 0;
            background: #fbbf24;
            border-radius: 2px;
        }
        .brand-points {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 9px;
        }
        .brand-points li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 0.79rem;
            line-height: 1.42;
            color: rgba(255, 255, 255, 0.95);
        }
        .brand-points li i {
            font-size: 0.72rem;
            color: #fde68a;
            background: rgba(255, 255, 255, 0.18);
            width: 20px;
            height: 20px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-top: 1px;
        }

        /* Pengisi ruang tengah panel kiri: Jaringan Unit Layanan */
        .branch-showcase {
            position: relative;
            z-index: 1;
            margin-top: 14px;
            padding: 12px 13px;
            border-radius: 12px;
            background: rgba(9, 71, 84, 0.55);
            border: 1px solid rgba(255, 255, 255, 0.25);
        }
        .branch-showcase-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.68rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #fde68a;
            margin-bottom: 8px;
        }
        .branch-showcase-title span {
            font-weight: 400;
            text-transform: none;
            letter-spacing: 0;
            color: rgba(255, 255, 255, 0.84);
            font-size: 0.66rem;
        }
        .branch-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 7px;
        }
        .branch-chip {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 9px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #ffffff;
            font-size: 0.73rem;
            cursor: pointer;
            transition: background 0.15s, border-color 0.15s;
            text-align: left;
            font-family: inherit;
        }
        .branch-chip:hover {
            background: rgba(255, 255, 255, 0.24);
            border-color: #fde68a;
        }
        .branch-chip.active {
            background: #ffffff;
            color: var(--ink);
            border-color: #ffffff;
            font-weight: 600;
        }
        .branch-chip .code {
            font-size: 0.64rem;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 5px;
            background: rgba(251, 191, 36, 0.28);
            color: #fde68a;
            letter-spacing: 0.03em;
        }
        .branch-chip.active .code {
            background: var(--brand-teal);
            color: #ffffff;
        }

        /* Ringkasan alur Task ID di panel kiri bawah agar tidak ada ruang kosong */
        .workflow-card {
            position: relative;
            z-index: 1;
            margin-top: 12px;
            padding: 10px 12px;
            border-radius: 10px;
            background: rgba(9, 71, 84, 0.52);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .workflow-title {
            font-size: 0.66rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #b8f2ff;
            margin-bottom: 6px;
        }
        .workflow-steps {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 4px;
            font-size: 0.67rem;
            color: #ffffff;
        }
        .workflow-step {
            display: flex;
            align-items: center;
            gap: 4px;
            background: rgba(255, 255, 255, 0.14);
            padding: 3px 7px;
            border-radius: 6px;
            white-space: nowrap;
        }
        .workflow-step strong {
            color: #fde68a;
        }

        /* Panel form */
        .form-panel {
            padding: 32px 38px 24px;
            display: flex;
            flex-direction: column;
            background: #ffffff;
        }
        .form-top-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            align-self: flex-start;
            padding: 4px 10px;
            border-radius: 999px;
            background: var(--teal-soft);
            color: var(--brand-teal);
            font-size: 0.69rem;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .form-panel h2 {
            margin: 0;
            font-size: 1.32rem;
            font-weight: 700;
            color: var(--ink);
        }
        .form-panel .form-sub {
            margin: 4px 0 16px;
            font-size: 0.8rem;
            color: var(--ink-soft);
        }

        .info-box {
            background: var(--amber-soft);
            border: 1px solid #f7d99c;
            border-radius: 11px;
            padding: 11px 13px;
            font-size: 0.76rem;
            color: #6b4e16;
            margin-bottom: 16px;
        }
        .info-box-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .info-box .info-title {
            font-weight: 700;
            color: #85580a;
            font-size: 0.75rem;
        }
        .btn-autofill {
            border: 1px solid #eab308;
            background: #ffffff;
            color: #85580a;
            font-family: inherit;
            font-size: 0.68rem;
            font-weight: 600;
            padding: 2px 9px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.15s;
        }
        .btn-autofill:hover {
            background: #fef0c7;
        }
        .info-box .info-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 1px 0;
        }
        .info-box strong { color: #4a3205; }

        .error {
            background: #fdeceb;
            color: #a4302a;
            border: 1px solid #f3cfcc;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 0.78rem;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .field { margin-bottom: 13px; }
        .field label {
            display: block;
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--ink-soft);
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .input-wrap { position: relative; }
        .input-wrap > i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b95a0;
            font-size: 0.85rem;
        }
        .input-wrap input,
        .input-wrap select {
            width: 100%;
            padding: 10px 14px 10px 40px;
            border: 1.5px solid var(--line);
            border-radius: 9px;
            font-size: 0.89rem;
            font-family: inherit;
            color: var(--ink);
            background: #f5fbfc;
            transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
            outline: none;
        }
        .input-wrap input:focus,
        .input-wrap select:focus {
            border-color: var(--brand-teal);
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(18, 130, 152, 0.13);
        }
        .input-wrap select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1.5 6 6.5l5-5' fill='none' stroke='%236b95a0' stroke-width='1.6' stroke-linecap='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 36px;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 9px;
            background: var(--brand-teal);
            color: #ffffff;
            font-family: inherit;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s, transform 0.1s, box-shadow 0.15s;
            margin-top: 4px;
            box-shadow: 0 6px 16px rgba(18, 130, 152, 0.24);
        }
        .btn-login:hover { background: var(--brand-teal-dark); }
        .btn-login:active { transform: translateY(1px); }

        /* Pengisi ruang bawah form: modul ringkas */
        .module-strip {
            margin-top: 16px;
            padding-top: 13px;
            border-top: 1px dashed #d7eaee;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 7px;
        }
        .module-pill {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            font-size: 0.69rem;
            font-weight: 500;
            color: var(--ink-soft);
            background: #f2fafc;
            border: 1px solid #d5ebf0;
            padding: 6px 8px;
            border-radius: 8px;
            text-align: center;
        }
        .module-pill i {
            color: var(--brand-teal);
            font-size: 0.68rem;
        }

        .form-footer {
            margin-top: auto;
            padding-top: 14px;
            font-size: 0.72rem;
            color: #7997a0;
            text-align: center;
        }
        .form-footer strong { color: var(--ink-soft); }

        @media (max-width: 920px) {
            body { padding: 18px 14px; }
            .top-status-bar { max-width: 440px; flex-direction: column; align-items: flex-start; }
            .auth-shell {
                grid-template-columns: 1fr;
                max-width: 440px;
            }
            .brand-panel {
                padding: 22px 24px 18px;
                background-image: none;
            }
            .brand-logo { height: 44px; max-width: 65%; }
            .brand-panel h1 { margin-top: 12px; font-size: 1.05rem; }
            .brand-divider { margin: 12px 0; }
            .brand-points, .workflow-card { display: none; }
            .form-panel { padding: 26px 24px 22px; }
            .bg-shape.ring, .bg-shape.ring-left, .bg-shape.circle-c { display: none; }
        }
    </style>
</head>
<body>
    <div class="bg-shape circle-a"></div>
    <div class="bg-shape circle-b"></div>
    <div class="bg-shape circle-c"></div>
    <div class="bg-shape ring"></div>
    <div class="bg-shape ring-left"></div>

    <div class="top-status-bar">
        <div class="top-status-left">
            <i class="fas fa-network-wired"></i>
            <span>Integrasi SIMRS &amp; BPJS Kesehatan — Queen Latifa Healthcare</span>
        </div>
        <div class="top-status-pills">
            <span class="status-chip"><span class="pulse"></span> Antrol WS Aktif</span>
            <span class="status-chip"><span class="pulse"></span> VClaim V2 Encrypted</span>
            <span class="status-chip"><i class="fas fa-code-branch"></i> 4 Unit Cabang</span>
        </div>
    </div>

    <div class="auth-shell">
        <aside class="brand-panel">
            <div class="brand-top">
                <div class="brand-header">
                    <img src="{{ asset('dist/img/logoqlheader.png') }}" alt="Logo Queen Latifa" class="brand-logo">
                    <span class="brand-badge"><span class="dot"></span> BPJS Ready</span>
                </div>
                <h1>Antrian BPJS Online</h1>
                <p class="brand-sub">Queen Latifa Healthcare System</p>

                <div class="brand-divider"></div>

                <ul class="brand-points">
                    <li><i class="fas fa-check"></i><span>Antrean online terhubung langsung dengan BPJS Kesehatan.</span></li>
                    <li><i class="fas fa-check"></i><span>Kirim &amp; pantau Kode Booking serta Task ID 1–7 per cabang.</span></li>
                    <li><i class="fas fa-check"></i><span>Verifikasi VClaim dan rekap kunjungan dalam satu tempat.</span></li>
                </ul>

                <div class="branch-showcase">
                    <div class="branch-showcase-title">
                        <strong><i class="fas fa-hospital-alt"></i> Unit Layanan</strong>
                        <span>Klik untuk pilih cabang</span>
                    </div>
                    <div class="branch-grid">
                        <button type="button" class="branch-chip" data-branch="QLJ">
                            <span class="code">QLJ</span>
                            <span>Yogyakarta</span>
                        </button>
                        <button type="button" class="branch-chip" data-branch="QLKP">
                            <span class="code">QLKP</span>
                            <span>Kulon Progo</span>
                        </button>
                        <button type="button" class="branch-chip" data-branch="QLTMG">
                            <span class="code">QLTMG</span>
                            <span>Temanggung</span>
                        </button>
                        <button type="button" class="branch-chip" data-branch="PUSAT">
                            <span class="code">PUSAT</span>
                            <span>Manajemen</span>
                        </button>
                    </div>
                </div>

                <div class="workflow-card">
                    <div class="workflow-title"><i class="fas fa-stream"></i> Alur Pemantauan Antrean</div>
                    <div class="workflow-steps">
                        <span class="workflow-step"><strong>1</strong> Booking</span>
                        <span class="workflow-step"><strong>2</strong> Admisi</span>
                        <span class="workflow-step"><strong>3</strong> Poli</span>
                        <span class="workflow-step"><strong>4</strong> Farmasi</span>
                    </div>
                </div>
            </div>
        </aside>

        <main class="form-panel">
            <span class="form-top-tag"><i class="fas fa-shield-alt"></i> Portal Operator SIMRS</span>
            <h2>Masuk ke Sistem</h2>
            <p class="form-sub">Gunakan akun operator dan pilih cabang tugas Anda.</p>

            <div class="info-box">
                <div class="info-box-header">
                    <div class="info-title"><i class="fas fa-key"></i> Kredensial Operator</div>
                    <button type="button" class="btn-autofill" id="btnAutofill">Isi Otomatis</button>
                </div>
                <div class="info-row"><span>Username</span><strong>Queen Latifa</strong></div>
                <div class="info-row"><span>Password</span><strong>Hospital@2026</strong></div>
            </div>

            @if ($errors->any())
                <div class="error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}">
                @csrf
                <div class="field">
                    <label for="username">Username</label>
                    <div class="input-wrap">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" value="{{ old('username') }}" placeholder="Masukkan username" autocomplete="username" required>
                    </div>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Masukkan password" autocomplete="current-password" required>
                    </div>
                </div>
                <div class="field">
                    <label for="branch">Cabang</label>
                    <div class="input-wrap">
                        <i class="fas fa-building"></i>
                        <select id="branch" name="branch" required>
                            <option value="">-- Pilih Cabang --</option>
                            <option value="QLJ" {{ old('branch') === 'QLJ' ? 'selected' : '' }}>QLH Yogyakarta</option>
                            <option value="QLKP" {{ old('branch') === 'QLKP' ? 'selected' : '' }}>QLH Kulon Progo</option>
                            <option value="QLTMG" {{ old('branch') === 'QLTMG' ? 'selected' : '' }}>QLH Temanggung</option>
                            <option value="PUSAT" {{ old('branch') === 'PUSAT' ? 'selected' : '' }}>PUSAT</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Masuk ke Dashboard
                </button>
            </form>

            <div class="module-strip">
                <span class="module-pill"><i class="fas fa-ticket-alt"></i> Kode Booking</span>
                <span class="module-pill"><i class="fas fa-tasks"></i> Task ID 1–7</span>
                <span class="module-pill"><i class="fas fa-stethoscope"></i> VClaim Rawat Jalan</span>
            </div>

            <div class="form-footer">
                &copy; {{ date('Y') }} <strong>Queen Latifa</strong> · Sistem Antrian BPJS
            </div>
        </main>
    </div>

    <script>
        (function () {
            const branchSelect = document.getElementById('branch');
            const chips = document.querySelectorAll('.branch-chip');
            const btnAutofill = document.getElementById('btnAutofill');

            function syncChips() {
                chips.forEach(function (chip) {
                    chip.classList.toggle('active', chip.dataset.branch === branchSelect.value);
                });
            }

            chips.forEach(function (chip) {
                chip.addEventListener('click', function () {
                    branchSelect.value = chip.dataset.branch;
                    syncChips();
                });
            });

            branchSelect.addEventListener('change', syncChips);
            syncChips();

            if (btnAutofill) {
                btnAutofill.addEventListener('click', function () {
                    document.getElementById('username').value = 'Queen Latifa';
                    document.getElementById('password').value = 'Hospital@2026';
                    if (!branchSelect.value) {
                        branchSelect.focus();
                    }
                });
            }
        })();
    </script>
</body>
</html>
