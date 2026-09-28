<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title') - Admin</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>
<nav class="admin-nav" aria-label="Navigasi admin">
    <a class="admin-brand" href="/admin" aria-label="Bengkel Rudi, dashboard admin">
        <img class="admin-brand__logo" src="https://res.cloudinary.com/dkv2rn5ax/image/upload/logo-bengkel-rudi.png_calja6.png" alt="Logo Bengkel Rudi" width="44" height="44">
        <span>Bengkel Rudi</span>
    </a>
    <a href="/admin" @if(request()->is('admin')) aria-current="page" @endif>Dashboard</a>
    <a href="/admin/portfolio" @if(request()->is('admin/portfolio*')) aria-current="page" @endif>Portfolio</a>
    <a href="/admin/layanan" @if(request()->is('admin/layanan*')) aria-current="page" @endif>Layanan</a>
    @if(auth('admin')->user()?->role === 'SUPER_ADMIN')
        <a href="/admin/users" @if(request()->is('admin/users*')) aria-current="page" @endif>Users</a>
        <a href="/admin/settings" @if(request()->is('admin/settings*')) aria-current="page" @endif>Settings</a>
    @endif
    <form method="post" action="/admin/logout">@csrf<button type="submit">Keluar</button></form>
</nav>
<main class="admin-shell">@yield('content')</main>
</body>
</html>
