<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    @php($canonicalBase = 'https://bengkel-rudi.vercel.app')
    @php($canonicalUrl = $canonicalBase . (request()->path() === '/' ? '/' : '/' . request()->path()))
    @php($pageTitle = html_entity_decode(trim($__env->yieldContent('title', $settings['meta_title'] ?? 'Bengkel Rudi — Cat & Body Repair Batang')), ENT_QUOTES, 'UTF-8'))
    @php($pageDescription = trim($__env->yieldContent('description', $settings['meta_description'] ?? 'Layanan cat dan body repair mobil dan motor di Wonokerto, Bandar, Kabupaten Batang.')))
    @php($socialImage = $canonicalBase . '/images/og-bengkel-rudi.png')
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="id_ID">
    <meta property="og:site_name" content="Bengkel Rudi">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $socialImage }}">
    <meta property="og:image:width" content="1731">
    <meta property="og:image:height" content="909">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $socialImage }}">
    @yield('structured_data')
    @yield('breadcrumb_structured_data')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="/images/logo-bengkel-rudi.png" type="image/png">
    <link rel="stylesheet" href="/css/site.css?v={{ filemtime(public_path('css/site.css')) }}">
</head>
<body>
    @include('layouts.partials.header')

    <main id="main-content">
        @yield('content')
    </main>

    @include('layouts.partials.footer')
</body>
</html>