<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title', $settings['meta_title'] ?? 'Bengkel Rudi — Spesialis Cat & Body Repair')</title>
    <meta name="description" content="@yield('description', $settings['meta_description'] ?? 'Layanan pengecatan, perbaikan penyok, dan body repair mobil maupun motor.')">
    @yield('structured_data')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="https://{{ request()->getHost() }}/images/logo-bengkel-rudi.png" type="image/png">
    <link rel="stylesheet" href="https://bengkel-rudi.vercel.app/css/site.css">
</head>
<body>
    @include('layouts.partials.header')

    <main id="main-content">
        @yield('content')
    </main>

    @include('layouts.partials.footer')
</body>
</html>