<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Antrian BPJS Online</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', system-ui, sans-serif;
            background: radial-gradient(1200px 600px at 10% 10%, #1e6a7a 0%, transparent 60%),
                        radial-gradient(1000px 700px at 90% 90%, #0a3a46 0%, transparent 60%),
                        linear-gradient(135deg, #0a3a46 0%, #0f4c5c 50%, #1e6a7a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #111827;
            overflow: hidden;
        }
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.35;
            animation: float 8s ease-in-out infinite;
        }
        .orb.one { width: 320px; height: 320px; background: #f59e0b; top: -80px; left: -60px; }
        .orb.two { width: 400px; height: 400px; background: #10b981; bottom: -120px; right: -80px; animation-delay: 2s; }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .login-card {
            position: relative;
            width: 100%;
            max-width: 420px;
            padding: 40px 36px;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(18px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(255,255,255,0.4);
            z-index: 2;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 28px;
        }
        .brand-icon {
            width: 48px; height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, #0f4c5c, #1e6a7a);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 20px;
            box-shadow: 0 8px 20px rgba(15, 76, 92, 0.35);
        }
        .brand-text h1 { margin: 0; font-size: 1.25rem; font-weight: 700; color: #111827; }
        .brand-text p { margin: 0; font-size: 0.8rem; color: #6b7280; }
        .field { margin-bottom: 18px; }
        .field label { display: block; font-size: 0.75rem; font-weight: 600; color: #4b5563; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em; }
        .input-wrap { position: relative; }
        .input-wrap i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 0.9rem; }
        .input-wrap input {
            width: 100%;
            padding: 12px 14px 12px 40px;
            border: 1.5px solid #d1d5db;
            border-radius: 12px;
            font-size: 0.95rem;
            background: #f9fafb;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
            outline: none;
        }
        .input-wrap input:focus {
            border-color: #0f4c5c;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(15, 76, 92, 0.12);
        }
        .btn-login {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #0f4c5c, #1e6a7a);
            color: #fff;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 8px 20px rgba(15, 76, 92, 0.3);
            margin-top: 6px;
        }
        .btn-login:hover { transform: translateY(-1px); box-shadow: 0 12px 26px rgba(15, 76, 92, 0.4); }
        .btn-login:active { transform: translateY(0); }
        .error {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 0.8rem;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .footer {
            text-align: center;
            margin-top: 22px;
            font-size: 0.75rem;
            color: #9ca3af;
        }
        .footer strong { color: #6b7280; }
    </style>
</head>
<body>
    <div class="orb one"></div>
    <div class="orb two"></div>

    <div class="login-card">
        <div class="brand" style="flex-direction:column; text-align:center; gap:6px;">
            <img src="{{ asset('dist/img/logoqlheader.png') }}" alt="Logo Queen Latifa" style="height:64px; width:auto; object-fit:contain; margin-bottom:6px;">
            <div class="brand-text">
                <h1>Antrian BPJS Online</h1>
                <p>Queen Latifa Healthcare System</p>
            </div>
        </div>

        <div class="info-box" style="background:#eef2f5; border:1px solid #d1d5db; border-radius:10px; padding:10px 12px; font-size:0.78rem; color:#4b5563; margin-bottom:16px;">
            <div style="font-weight:600; color:#111827; margin-bottom:4px;"><i class="fas fa-info-circle me-1"></i> Info Login</div>
            <div>Username: <strong>Queen Latifa</strong></div>
            <div>Password: <strong>Hospital@2026</strong></div>
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
                    <select id="branch" name="branch" required style="width:100%; padding:12px 14px 12px 40px; border:1.5px solid #d1d5db; border-radius:12px; font-size:0.95rem; background:#f9fafb; outline:none; appearance:none;">
                        <option value="">-- Pilih Cabang --</option>
                        <option value="QLJ" {{ old('branch') === 'QLJ' ? 'selected' : '' }}>QLH Yogyakarta</option>
                        <option value="QLKP" {{ old('branch') === 'QLKP' ? 'selected' : '' }}>QLH Kulon Progo</option>
                        <option value="QLTMG" {{ old('branch') === 'QLTMG' ? 'selected' : '' }}>QLH Temanggung</option>
                        <option value="PUSAT" {{ old('branch') === 'PUSAT' ? 'selected' : '' }}>PUSAT</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt me-2"></i> Masuk
            </button>
        </form>

        <div class="footer">
            &copy; {{ date('Y') }} <strong>Queen Latifa</strong> · Sistem Antrian BPJS
        </div>
    </div>
</body>
</html>
