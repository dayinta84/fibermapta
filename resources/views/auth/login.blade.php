<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FIBERMAPTA</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.22), transparent 35%),
                radial-gradient(circle at bottom right, rgba(16, 185, 129, 0.18), transparent 35%),
                linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
            color: #0f172a;
        }

        .login-page {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
        }

        .login-visual {
            position: relative;
            padding: 56px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }

        .login-visual::before {
            content: "";
            position: absolute;
            width: 520px;
            height: 520px;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.12);
            top: -180px;
            left: -160px;
        }

        .login-visual::after {
            content: "";
            position: absolute;
            width: 360px;
            height: 360px;
            border-radius: 999px;
            background: rgba(16, 185, 129, 0.14);
            bottom: -120px;
            right: -80px;
        }

        .brand-box {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-logo {
            width: 58px;
            height: 58px;
            border-radius: 20px;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            box-shadow: 0 18px 38px rgba(37, 99, 235, 0.22);
            border: 1px solid rgba(226, 232, 240, 0.95);
        }

        .brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .brand-text h1 {
            margin: 0;
            font-size: 24px;
            line-height: 1.1;
            font-weight: 900;
            color: #020617;
        }

        .brand-text p {
            margin: 4px 0 0;
            font-size: 13px;
            color: #64748b;
            font-weight: 700;
        }

        .hero-copy {
            position: relative;
            z-index: 1;
            max-width: 560px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 13px;
            border-radius: 999px;
            color: #1d4ed8;
            background: rgba(239, 246, 255, 0.9);
            border: 1px solid #bfdbfe;
            font-size: 13px;
            font-weight: 900;
            margin-bottom: 18px;
        }

        .hero-copy h2 {
            margin: 0;
            font-size: 46px;
            line-height: 1.05;
            letter-spacing: -1.4px;
            color: #020617;
            font-weight: 950;
        }

        .hero-copy p {
            margin: 18px 0 0;
            color: #475569;
            font-size: 16px;
            line-height: 1.7;
            max-width: 500px;
        }

        .feature-row {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            max-width: 650px;
        }

        .feature-card {
            padding: 14px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.78);
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
            backdrop-filter: blur(10px);
        }

        .feature-card strong {
            display: block;
            font-size: 14px;
            color: #020617;
            margin-bottom: 5px;
        }

        .feature-card span {
            display: block;
            font-size: 12px;
            line-height: 1.45;
            color: #64748b;
        }

        .login-panel-wrap {
            padding: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-panel {
            width: min(440px, 100%);
            padding: 34px;
            border-radius: 28px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(226, 232, 240, 0.95);
            box-shadow: 0 26px 70px rgba(15, 23, 42, 0.13);
            backdrop-filter: blur(12px);
        }

        .login-panel-header {
            margin-bottom: 26px;
        }

        .login-panel-header h2 {
            margin: 0;
            color: #020617;
            font-size: 28px;
            line-height: 1.15;
            font-weight: 950;
            letter-spacing: -0.6px;
        }

        .login-panel-header p {
            margin: 8px 0 0;
            color: #64748b;
            font-size: 14px;
            line-height: 1.5;
        }

        .alert {
            padding: 12px 13px;
            border-radius: 14px;
            margin-bottom: 16px;
            font-size: 13px;
            line-height: 1.45;
        }

        .alert-success {
            color: #166534;
            background: #dcfce7;
            border: 1px solid #bbf7d0;
        }

        .alert-error {
            color: #991b1b;
            background: #fee2e2;
            border: 1px solid #fecaca;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            margin-bottom: 7px;
            color: #334155;
            font-size: 13px;
            font-weight: 900;
        }

        .input-wrap {
            position: relative;
        }

        .form-input {
            width: 100%;
            height: 48px;
            border: 1px solid #dbe3ef;
            border-radius: 15px;
            padding: 0 14px;
            background: #ffffff;
            color: #0f172a;
            font-size: 14px;
            outline: none;
            transition: 0.16s ease;
        }

        .form-input.password-input {
            padding-right: 52px;
        }

        .form-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .form-input::placeholder {
            color: #94a3b8;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 8px;
            transform: translateY(-50%);
            width: 38px;
            height: 38px;
            border: 0;
            border-radius: 12px;
            background: transparent;
            color: #64748b;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: 0.16s ease;
        }

        .password-toggle:hover {
            background: #f1f5f9;
            color: #1d4ed8;
        }

        .password-toggle svg {
            width: 20px;
            height: 20px;
            stroke-width: 2;
        }

        .password-toggle .eye-off {
            display: none;
        }

        .password-toggle.active .eye {
            display: none;
        }

        .password-toggle.active .eye-off {
            display: block;
        }

        .error-text {
            margin-top: 6px;
            color: #dc2626;
            font-size: 12px;
            line-height: 1.4;
            font-weight: 700;
        }

        .form-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin: 6px 0 20px;
        }

        .remember-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #475569;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            user-select: none;
        }

        .remember-label input {
            width: 15px;
            height: 15px;
            accent-color: #2563eb;
        }

        .forgot-link {
            color: #2563eb;
            text-decoration: none;
            font-size: 13px;
            font-weight: 900;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .login-button {
            width: 100%;
            height: 49px;
            border: 0;
            border-radius: 16px;
            color: #ffffff;
            background: linear-gradient(135deg, #2563eb, #10b981);
            font-size: 15px;
            font-weight: 950;
            cursor: pointer;
            box-shadow: 0 16px 30px rgba(37, 99, 235, 0.24);
            transition: 0.16s ease;
        }

        .login-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 20px 38px rgba(37, 99, 235, 0.28);
        }

        .login-footer {
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #64748b;
            font-size: 12px;
            line-height: 1.5;
        }

        @media (max-width: 980px) {
            .login-page {
                grid-template-columns: 1fr;
            }

            .login-visual {
                padding: 34px 24px 10px;
                min-height: auto;
                gap: 34px;
            }

            .hero-copy h2 {
                font-size: 34px;
            }

            .feature-row {
                grid-template-columns: 1fr;
            }

            .login-panel-wrap {
                padding: 24px;
                align-items: flex-start;
            }
        }

        @media (max-width: 520px) {
            .login-visual {
                padding: 24px 18px 6px;
            }

            .login-panel-wrap {
                padding: 18px;
            }

            .login-panel {
                padding: 24px 20px;
                border-radius: 24px;
            }

            .brand-logo {
                width: 50px;
                height: 50px;
                border-radius: 17px;
                padding: 9px;
            }

            .brand-text h1 {
                font-size: 21px;
            }

            .hero-copy h2 {
                font-size: 30px;
            }

            .hero-copy p {
                font-size: 14px;
            }

            .form-row {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <main class="login-page">
        <section class="login-visual">
            <div class="brand-box">
                <div class="brand-logo">
                    <img src="{{ asset('assets/siport_icon.svg') }}" alt="Logo fibermapta">
                </div>

                <div class="brand-text">
                    <h1>FIBERMAPTA</h1>
                    <p>Sistem Informasi Manajemen Jaringan FTTH</p>
                </div>
            </div>

            <div class="hero-copy">
                <div class="hero-badge">
                    WebGIS FTTH Management
                </div>

                <h2>Kelola jaringan fiber dengan lebih rapi dan terpusat.</h2>

                <p>
                    Pantau titik OLT, ODC, ODP, port, pelanggan, dan simulasi Power Link Budget dalam satu sistem.
                </p>
            </div>

            <div class="feature-row">
                <div class="feature-card">
                    <strong>WebGIS</strong>
                    <span>Pemetaan titik jaringan berbasis lokasi.</span>
                </div>

                <div class="feature-card">
                    <strong>Port</strong>
                    <span>Manajemen port ODC dan ODP lebih tertata.</span>
                </div>

                <div class="feature-card">
                    <strong>PLB</strong>
                    <span>Simulasi redaman jaringan untuk analisis teknis.</span>
                </div>
            </div>
        </section>

        <section class="login-panel-wrap">
            <div class="login-panel">
                <div class="login-panel-header">
                    <h2>Masuk ke Akun</h2>
                    <p>Gunakan username dan password yang sudah terdaftar untuk mengakses dashboard.</p>
                </div>

                @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
                @endif

                @if ($errors->has('username') || $errors->has('password'))
                <div class="alert alert-error">
                    Username atau password belum sesuai. Periksa kembali data login kamu.
                </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>

                        <div class="input-wrap">
                            <input
                                type="text"
                                id="username"
                                name="username"
                                class="form-input"
                                value="{{ old('username') }}"
                                placeholder="Masukkan username"
                                required
                                autofocus
                                autocomplete="username">
                        </div>

                        @error('username')
                        <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>

                        <div class="input-wrap">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-input password-input"
                                placeholder="Masukkan password"
                                required
                                autocomplete="current-password">

                            <button
                                type="button"
                                class="password-toggle"
                                id="passwordToggle"
                                aria-label="Tampilkan password">
                                <svg class="eye" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" />
                                </svg>

                                <svg class="eye-off" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.58 10.58A2 2 0 0 0 12 14a2 2 0 0 0 1.42-.58" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.88 4.36A9.77 9.77 0 0 1 12 4.13c6 0 9.75 7.87 9.75 7.87a17.7 17.7 0 0 1-2.62 3.6" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.12 6.12C3.64 7.9 2.25 12 2.25 12s3.75 7.87 9.75 7.87a9.8 9.8 0 0 0 4.1-.9" />
                                </svg>
                            </button>
                        </div>

                        @error('password')
                        <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-row">
                        <label class="remember-label">
                            <input type="checkbox" name="remember">
                            <span>Ingat saya</span>
                        </label>

                        @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">
                            Lupa password?
                        </a>
                        @endif
                    </div>

                    <button type="submit" class="login-button">
                        Masuk
                    </button>
                </form>

                <div class="login-footer">
                    © {{ date('Y') }} FIBERMAPTA. Sistem Informasi Manajemen Jaringan FTTH.
                </div>
            </div>
        </section>
    </main>

    <script>
        const passwordInput = document.getElementById('password');
        const passwordToggle = document.getElementById('passwordToggle');

        passwordToggle.addEventListener('click', function() {
            const isPassword = passwordInput.type === 'password';

            passwordInput.type = isPassword ? 'text' : 'password';

            passwordToggle.classList.toggle('active', isPassword);
            passwordToggle.setAttribute(
                'aria-label',
                isPassword ? 'Sembunyikan password' : 'Tampilkan password'
            );
        });
    </script>
</body>

</html>