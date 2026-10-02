<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="google-site-verification" content="IAqufHi5uz3_fhq-n-WVj7hU_p5fmlxYyEK1D9w3w4g">
    @php($canonicalBase = rtrim(config('app.url'), '/'))
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
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="apple-touch-icon" href="/images/apple-touch-icon.png">
    <link rel="stylesheet" href="/css/public/base.css?v={{ filemtime(public_path('css/public/base.css')) }}">
    <link rel="stylesheet" href="/css/public/components.css?v={{ filemtime(public_path('css/public/components.css')) }}">
    <link rel="stylesheet" href="/css/public/header.css?v={{ filemtime(public_path('css/public/header.css')) }}">
    <link rel="stylesheet" href="/css/public/footer.css?v={{ filemtime(public_path('css/public/footer.css')) }}">
    @stack('styles')
</head>
<body>
    @include('layouts.partials.header')

    <main id="main-content">
        @yield('content')
    </main>

    @include('layouts.partials.footer')
</body>
</html>