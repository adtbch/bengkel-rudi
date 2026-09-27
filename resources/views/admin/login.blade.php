<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Administrator — Bengkel Rudi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-body: #0b0f17;
            --bg-card: #111827;
            --bg-input: #1f2937;
            --border: #374151;
            --border-focus: #ef4444;
            --brand: #dc2626;
            --brand-hover: #b91c1c;
            --brand-glow: rgba(220, 38, 38, 0.35);
            --text-main: #f9fafb;
            --text-muted: #9ca3af;
            --radius-btn: 9999px;
            --radius-box: 16px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-body);
            background-image:
                radial-gradient(at 0% 0%, rgba(220, 38, 38, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(30, 58, 138, 0.12) 0px, transparent 50%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
            overflow-x: hidden;
        }

        .login-wrapper {
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 10;
        }

        .login-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-box);
            padding: 2.5rem 2rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
        }

        .brand-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .brand-logo {
            width: 72px;
            height: 72px;
            margin: 0 auto 1rem;
            display: block;
            object-fit: contain;
            filter: drop-shadow(0 4px 12px rgba(220, 38, 38, 0.3));
        }

        .brand-title {
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #ffffff;
        }

        .brand-subtitle {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 0.35rem;
        }

        .form-group { margin-bottom: 1.25rem; }

        .form-label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: #e5e7eb;
            margin-bottom: 0.4rem;
        }

        .form-control {
            width: 100%;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 0.8rem 1rem;
            font-size: 0.92rem;
            color: #ffffff;
            font-family: inherit;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px var(--brand-glow);
            background: #111827;
        }

        .form-control::placeholder { color: #6b7280; }

        .btn-submit {
            width: 100%;
            padding: 0.85rem 1.5rem;
            background: var(--brand);
            color: #ffffff;
            border: none;
            border-radius: var(--radius-btn);
            font-size: 0.95rem;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px var(--brand-glow);
            margin-top: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-submit:hover {
            background: var(--brand-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(220, 38, 38, 0.45);
        }

        .btn-submit:active { transform: translateY(0); }

        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.35);
            color: #fca5a5;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-muted);
            font-size: 0.82rem;
            text-decoration: none;
            transition: color 0.2s;
        }

        .back-link:hover { color: #ffffff; }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <main class="login-card">
            <header class="brand-header">
                <img class="brand-logo" src="https://res.cloudinary.com/dkv2rn5ax/image/upload/logo-bengkel-rudi.png_calja6.png" alt="Logo Bengkel Rudi" width="72" height="72">
                <h1 class="brand-title">Bengkel Rudi</h1>
                <p class="brand-subtitle">Panel Kontrol Administrator</p>
            </header>

            @if($errors->any())
                <div class="alert-error" role="alert">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="post" action="/admin/login">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="email">Alamat Email</label>
                    <input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="admin@bengkelrudi.com" required autocomplete="username" autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Kata Sandi</label>
                    <input class="form-control" id="password" name="password" type="password" placeholder="••••••••" required autocomplete="current-password">
                </div>
                <button class="btn-submit" type="submit">
                    <span>Masuk ke Dashboard</span>
                    <span aria-hidden="true">→</span>
                </button>
            </form>

            <a href="/" class="back-link">← Kembali ke Halaman Utama</a>
        </main>
    </div>
</body>
</html>