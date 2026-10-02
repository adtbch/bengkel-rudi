<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Admin</title>
    <link rel="icon" href="/images/logo-bengkel-rudi.png" type="image/png">
    <link rel="stylesheet" href="/css/admin/base.css?v={{ filemtime(public_path('css/admin/base.css')) }}">
    <link rel="stylesheet" href="/css/admin/layout.css?v={{ filemtime(public_path('css/admin/layout.css')) }}">
    <link rel="stylesheet" href="/css/admin/nav.css?v={{ filemtime(public_path('css/admin/nav.css')) }}">
    <link rel="stylesheet" href="/css/admin/components.css?v={{ filemtime(public_path('css/admin/components.css')) }}">
    <link rel="stylesheet" href="/css/admin/editor.css?v={{ filemtime(public_path('css/admin/editor.css')) }}">
    @stack('styles')
    <script src="/js/admin-form-feedback.js?v={{ filemtime(public_path('js/admin-form-feedback.js')) }}" defer></script>
</head>
<body>
    <nav class="admin-nav" aria-label="Navigasi admin">
        <a class="admin-brand" href="/admin" aria-label="Bengkel Rudi, dashboard admin">
            <img class="admin-brand__logo" src="https://res.cloudinary.com/dkv2rn5ax/image/upload/logo-bengkel-rudi.png_calja6.png" alt="Logo Bengkel Rudi" width="44" height="44">
            <span>Bengkel Rudi</span>
        </a>

        <div class="admin-nav__links">
            <a href="/admin" @if(request()->is('admin')) aria-current="page" @endif>Dashboard</a>
            <a href="/admin/portfolio" @if(request()->is('admin/portfolio*')) aria-current="page" @endif>Portfolio</a>
            <a href="/admin/layanan" @if(request()->is('admin/layanan*')) aria-current="page" @endif>Layanan</a>
            @if(auth('admin')->user()?->role === 'SUPER_ADMIN')
                <a href="/admin/users" @if(request()->is('admin/users*')) aria-current="page" @endif>Users</a>
                <a href="/admin/settings" @if(request()->is('admin/settings*')) aria-current="page" @endif>Settings</a>
            @endif
        </div>

        <form class="admin-nav__logout" method="post" action="/admin/logout">@csrf<button type="submit">Keluar</button></form>

        <details class="admin-mobile-menu">
            <summary aria-label="Buka menu admin"><span></span><span></span><span></span></summary>
            <div class="admin-mobile-menu__list">
                <a href="/admin" @if(request()->is('admin')) aria-current="page" @endif>Dashboard</a>
                <a href="/admin/portfolio" @if(request()->is('admin/portfolio*')) aria-current="page" @endif>Portfolio</a>
                <a href="/admin/layanan" @if(request()->is('admin/layanan*')) aria-current="page" @endif>Layanan</a>
                @if(auth('admin')->user()?->role === 'SUPER_ADMIN')
                    <a href="/admin/users" @if(request()->is('admin/users*')) aria-current="page" @endif>Users</a>
                    <a href="/admin/settings" @if(request()->is('admin/settings*')) aria-current="page" @endif>Settings</a>
                @endif
                <form method="post" action="/admin/logout">@csrf<button type="submit">Keluar</button></form>
            </div>
        </details>
    </nav>

    <main class="admin-shell">@yield('content')</main>

    @if(session('status'))
        <div class="admin-toast admin-toast--success" role="status">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="admin-toast admin-toast--error" role="alert">{{ $errors->first() }}</div>
    @endif
</body>
</html>