<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title') - Admin</title>
    <style>
        :root { --radius-button: 9999px; font-family: system-ui, sans-serif; color: #172033; background: #f4f6f8; }
        * { box-sizing: border-box; }
        body { margin: 0; }
        button, input, select, textarea { font: inherit; }
        button { border-radius: var(--radius-button); }
        button, .admin-nav a { min-height: 44px; }
        :focus-visible { outline: 3px solid #f59e0b; outline-offset: 2px; }
        .admin-nav { position: sticky; top: 0; z-index: 20; display: flex; gap: .25rem; align-items: center; overflow-x: auto; padding: .65rem max(1rem, calc((100vw - 1180px) / 2)); background: #172033; }
        .admin-nav a, .admin-nav button { display: inline-flex; align-items: center; padding: .65rem .8rem; border: 0; border-radius: .5rem; color: #fff; background: transparent; text-decoration: none; white-space: nowrap; cursor: pointer; }
        .admin-nav a:hover, .admin-nav button:hover { background: rgba(255,255,255,.12); }
        .admin-nav form { margin-left: auto; }
        .admin-shell { width: min(1180px, 100%); margin-inline: auto; padding: 1rem; }
        .admin-page-head { margin-bottom: 1rem; }
        .admin-page-head h1 { margin: 0; font-size: clamp(1.5rem, 5vw, 2rem); }
        .admin-page-head p { margin: .35rem 0 0; color: #667085; }
        .admin-panel, .admin-card { border: 1px solid #dbe0e7; border-radius: .75rem; background: #fff; padding: 1rem; }
        .admin-panel { margin-bottom: 1rem; }
        .admin-card-grid { display: grid; gap: 1rem; }
        .admin-form-grid { display: grid; gap: .85rem; }
        .admin-field { display: grid; gap: .35rem; font-weight: 650; }
        .admin-field input, .admin-field select, .admin-field textarea { width: 100%; min-height: 44px; border: 1px solid #aeb7c5; border-radius: .5rem; padding: .65rem .75rem; background: #fff; color: #172033; }
        .admin-check { display: flex; gap: .55rem; align-items: center; min-height: 44px; }
        .admin-check input { width: 1.2rem; height: 1.2rem; }
        .admin-actions { display: flex; flex-wrap: wrap; gap: .6rem; margin-top: .85rem; }
        .admin-actions form { margin: 0; }
        .admin-button { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; border: 0; border-radius: var(--radius-button); padding: .65rem 1rem; color: #fff; background: #172033; font-weight: 700; cursor: pointer; }
        .admin-button--secondary { color: #172033; background: #e9edf2; }
        .admin-button--danger { background: #b42318; }
        .admin-status { display: inline-block; margin-bottom: .75rem; padding: .25rem .55rem; border-radius: 999px; background: #e8f7ee; color: #176b3a; font-size: .8rem; font-weight: 700; }
        .admin-status--draft { background: #f1f3f5; color: #535d6c; }
        .admin-alert { margin-bottom: 1rem; border: 1px solid #f2b8b5; border-radius: .5rem; padding: .75rem; color: #8f1d18; background: #fff1f0; }
        @media (min-width: 720px) {
            .admin-shell { padding: 1.5rem; }
            .admin-form-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .admin-card-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .admin-field--wide { grid-column: 1 / -1; }
        }
    </style>
</head>
<body>
<nav class="admin-nav" aria-label="Navigasi admin">
    <a href="/admin">Dashboard</a>
    <a href="/admin/portfolio">Portfolio</a>
    <a href="/admin/layanan">Layanan</a>
    @if(auth('admin')->user()?->role === 'SUPER_ADMIN')
        <a href="/admin/users">Users</a>
        <a href="/admin/settings">Settings</a>
    @endif
    <form method="post" action="/admin/logout">@csrf<button type="submit">Keluar</button></form>
</nav>
<main class="admin-shell">@yield('content')</main>
</body>
</html>
