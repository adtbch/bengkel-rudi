@extends('layouts.public')

@section('title', $service->name . ' — ' . ($settings['business_name'] ?? 'Bengkel Rudi'))
@section('description', trim(strip_tags($service->description)))

@push('styles')
    <link rel="stylesheet" href="/css/public/page.css?v={{ filemtime(public_path('css/public/page.css')) }}">
    <link rel="stylesheet" href="/css/public/services.css?v={{ filemtime(public_path('css/public/services.css')) }}">
@endpush

@section('breadcrumb_structured_data')
    @php($breadcrumbs = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => rtrim(config('app.url'), '/') . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Layanan', 'item' => rtrim(config('app.url'), '/') . '/layanan'],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $service->name, 'item' => rtrim(config('app.url'), '/') . '/layanan/' . $service->slug],
        ],
    ])

    <script type="application/ld+json">{!! json_encode($breadcrumbs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endsection

@section('content')
    @php($whatsapp = $settings['whatsapp_number'] ?? '628123456789')

    <div class="page-header page-header--compact">
        <a href="/layanan" class="page-backlink">&larr; Kembali ke Daftar Layanan</a>
        <h1 class="page-title page-title--page">{{ $service->name }}</h1>
    </div>

    <div class="grid page-single">
        <article class="card page-card">
            <div class="service-detail__eyebrow">Deskripsi Layanan &amp; Ketentuan</div>
            <p class="service-detail__description">{{ $service->description }}</p>

            @if($service->min_price || $service->max_price)
                <div class="service-detail__price">
                    <span class="service-detail__price-label">Estimasi Biaya Pengerjaan:</span>

                    <p class="service-detail__price-value">
                        @if($service->min_price)
                            Rp{{ number_format($service->min_price, 0, ',', '.') }}
                        @endif
                        @if($service->max_price)
                            – Rp{{ number_format($service->max_price, 0, ',', '.') }}
                        @endif
                    </p>

                    <small class="service-detail__price-note">
                        *Estimasi final disesuaikan dengan luas bidang panel, tingkat keparahan bodi, dan jenis warna cat kendaraan.
                    </small>
                </div>
            @endif

            @if(!empty($service->features) && count($service->features) > 0)
                <div class="service-detail__features">
                    <h2 class="service-detail__features-title">Keunggulan &amp; Cakupan Pekerjaan:</h2>

                    <ul class="service-detail__feature-list">
                        @foreach($service->features as $feature)
                            <li class="service-detail__feature-item">
                                <span class="service-detail__feature-check">&#10003;</span>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="service-detail__cta">
                <a
                    class="btn btn-wa"
                    href="https://wa.me/{{ $whatsapp }}?text={{ urlencode('Halo Bengkel Rudi, saya ingin konsultasi estimasi layanan ' . $service->name) }}"
                    target="_blank"
                    rel="noopener"
                >Konsultasi Layanan via WhatsApp</a>

                <a class="btn btn-white" href="/portfolio">Lihat Contoh Hasil di Galeri</a>
            </div>
        </article>
    </div>
@endsection