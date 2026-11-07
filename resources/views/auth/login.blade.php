<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Antrian BPJS Online</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --ink: #0f2930;
            --ink-soft: #486972;
            --brand-teal: #117a8e;
            --brand-teal-dark: #0c6273;
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
            background-color: #eaf6f8;
            color: var(--ink);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 20px;
            overflow-x: hidden;
        }

        /* Dekorasi latar cerah & bersih */
        .bg-shape {
            position: fixed;
            pointer-events: none;
            z-index: 0;
        }
        .bg-shape.circle-a {
            width: 540px; height: 540px;
            border-radius: 50%;
            background: #d2eef3;
            top: -170px; left: -130px;
        }
        .bg-shape.circle-b {
            width: 460px; height: 460px;
            border-radius: 50%;
            background: #fcebd0;
            bottom: -180px; right: -110px;
        }
        .bg-shape.ring {
            width: 310px; height: 310px;
            border-radius: 50%;
            border: 2px solid #b5e1ea;
            top: 10%; right: 8%;
        }
        .bg-shape.dots {
            width: 136px; height: 136px;
            bottom: 10%; left: 7%;
            background-image: radial-gradient(#9fd6e2 1.8px, transparent 1.8px);
            background-size: 14px 14px;
        }

        .auth-shell {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 420px 460px;
            width: 100%;
            max-width: 880px;
            min-height: 590px;
            background: #ffffff;
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid #d7ecf0;
            box-shadow: 0 22px 55px rgba(13, 98, 115, 0.14), 0 3px 10px rgba(13, 98, 115, 0.06);
        }

        /* Panel kiri — lebih cerah mengikuti warna biru-toska & emas logo QL */
        .brand-panel {
            position: relative;
            display: flex;
            flex-direction: column;
            padding: 30px 30px 140px;
            background-color: var(--brand-teal);
            background-image: url('{{ asset('dist/img/login-bg.svg') }}');
            background-repeat: no-repeat;
            background-position: bottom center;
            background-size: 100% auto;
            color: #ffffff;
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
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.28);
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
            margin: 16px 0 3px;
            font-size: 1.2rem;
            font-weight: 700;
            letter-spacing: 0.01em;
        }
        .brand-panel .brand-sub {
            margin: 0;
            font-size: 0.72rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.82);
        }
        .brand-divider {
            width: 46px;
            height: 3.5px;
            margin: 16px 0;
            background: #fbbf24;
            border-radius: 2px;
        }
        .brand-points {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 10px;
            position: relative;
            z-index: 1;
        }
        .brand-points li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 0.79rem;
            line-height: 1.45;
            color: rgba(255, 255, 255, 0.94);
        }
        .brand-points li i {
            font-size: 0.72rem;
            color: #fde68a;
            background: rgba(255, 255, 255, 0.16);
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
            margin-top: 18px;
            padding: 13px 14px;
            border-radius: 12px;
            background: rgba(10, 74, 88, 0.48);
            border: 1px solid rgba(255, 255, 255, 0.22);
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
            margin-bottom: 9px;
        }
        .branch-showcase-title span {
            font-weight: 400;
            text-transform: none;
            letter-spacing: 0;
            color: rgba(255, 255, 255, 0.78);
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
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.16);
            color: #ffffff;
            font-size: 0.73rem;
            cursor: pointer;
            transition: background 0.15s, border-color 0.15s, transform 0.1s;
            text-align: left;
            font-family: inherit;
        }
        .branch-chip:hover {
            background: rgba(255, 255, 255, 0.22);
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
            background: rgba(251, 191, 36, 0.25);
            color: #fde68a;
            letter-spacing: 0.03em;
        }
        .branch-chip.active .code {
            background: var(--brand-teal);
            color: #ffffff;
        }

        .brand-note {
            position: absolute;
            left: 30px;
            right: 30px;
            bottom: 14px;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.68rem;
            color: rgba(255, 255, 255, 0.88);
            letter-spacing: 0.02em;
            text-shadow: 0 1px 2px rgba(7, 45, 54, 0.5);
        }

        /* Panel form */
        .form-panel {
            padding: 34px 38px 26px;
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
            margin-bottom: 10px;
        }
        .form-panel h2 {
            margin: 0;
            font-size: 1.32rem;
            font-weight: 700;
            color: var(--ink);
        }
        .form-panel .form-sub {
            margin: 5px 0 18px;
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
            margin-bottom: 18px;
        }
        .info-box-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 6px;
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
            padding: 2px 8px;
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
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .field { margin-bottom: 14px; }
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
            padding: 11px 14px 11px 40px;
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
            box-shadow: 0 0 0 3px rgba(17, 122, 142, 0.13);
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
            box-shadow: 0 6px 16px rgba(17, 122, 142, 0.24);
        }
        .btn-login:hover { background: var(--brand-teal-dark); }
        .btn-login:active { transform: translateY(1px); }

        /* Pengisi ruang bawah form: modul ringkas */
        .module-strip {
            margin-top: 18px;
            padding-top: 14px;
            border-top: 1px dashed #d7eaee;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
            flex-wrap: wrap;
        }
        .module-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.7rem;
            color: var(--ink-soft);
            background: #f3fafb;
            border: 1px solid #d9edf1;
            padding: 4px 9px;
            border-radius: 7px;
        }
        .module-pill i {
            color: var(--brand-teal);
            font-size: 0.68rem;
        }

        .form-footer {
            margin-top: auto;
            padding-top: 14px;
            font-size: 0.72rem;
            color: #829ea6;
            text-align: center;
        }
        .form-footer strong { color: var(--ink-soft); }

        @media (max-width: 860px) {
            body { padding: 20px 14px; }
            .auth-shell {
                grid-template-columns: 1fr;
                max-width: 430px;
                min-height: 0;
            }
            .brand-panel {
                padding: 22px 24px 20px;
                background-image: none;
            }
            .brand-logo { height: 44px; max-width: 65%; }
            .brand-panel h1 { margin-top: 12px; font-size: 1.05rem; }
            .brand-divider { margin: 12px 0; }
            .brand-points, .branch-showcase, .brand-note { display: none; }
            .form-panel { padding: 26px 24px 22px; }
            .bg-shape.ring, .bg-shape.dots { display: none; }
        }
    </style>
</head>
<body>
    <div class="bg-shape circle-a"></div>
    <div class="bg-shape circle-b"></div>
    <div class="bg-shape ring"></div>
    <div class="bg-shape dots"></div>

    <div class="auth-shell">
        <aside class="brand-panel">
            <div class="brand-header">
                <img src="{{ asset('dist/img/logoqlheader.png') }}" alt="Logo Queen Latifa" class="brand-logo">
                <span class="brand-badge"><span class="dot"></span> BPJS Ready</span>
            </div>
            <h1>Antrian BPJS Online</h1>
            <p class="brand-sub">Queen Latifa Healthcare System</p>

            <div class="brand-divider"></div>

            <ul class="brand-points">
                <li><i class="fas fa-check"></i><span>Antrean online terhubung langsung dengan BPJS Kesehatan.</span></li>
                <li><i class="fas fa-check"></i><span>Kirim & pantau Kode Booking serta Task ID 1–7 per cabang.</span></li>
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

            <div class="brand-note">
                <span>Sistem Internal · QL Group</span>
                <span>Antrol &amp; VClaim v2</span>
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
