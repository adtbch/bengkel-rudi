<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Administrator — Bengkel Rudi</title>
    <link rel="icon" href="/images/logo-bengkel-rudi.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/admin/login.css?v={{ filemtime(public_path('css/admin/login.css')) }}">
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