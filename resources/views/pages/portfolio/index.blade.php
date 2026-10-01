@extends('layouts.public')

@section('title', 'Portfolio — ' . ($settings['business_name'] ?? 'Bengkel Rudi'))
@section('description', 'Koleksi dokumentasi hasil pengerjaan body repair dan pengecatan kendaraan oleh Bengkel Rudi.')

@push('styles')
    <link rel="stylesheet" href="/css/public/page.css?v={{ filemtime(public_path('css/public/page.css')) }}">
    <link rel="stylesheet" href="/css/public/portfolio.css?v={{ filemtime(public_path('css/public/portfolio.css')) }}">
@endpush

@section('content')
    <div class="page-header">
        <div class="pill-badge badge-stage-after page-header__badge">Hasil Pekerjaan Nyata</div>
        <h1 class="page-title page-title--index">Portfolio &amp; Galeri Pengerjaan</h1>
        <p class="page-lead">
            Dokumentasi tahapan perbaikan bodi, pengecatan, dan pemolesan mobil maupun motor di bengkel kami.
        </p>
    </div>

    @if($portfolios->isEmpty())
        <div class="page-empty">
            <p class="page-empty__text">Belum ada portfolio tersedia.</p>
        </div>
    @else
        <div class="grid">
            @foreach($portfolios as $portfolio)
                @php($cover = $portfolio->images->first())

                <article class="card portfolio-card">
                    <div class="portfolio-card__media">
                        @if($cover)
                            @if($cover->media_type === 'video')
                                <video
                                    src="{{ $cover->image_url }}"
                                    controls
                                    preload="metadata"
                                    playsinline
                                    aria-label="Video {{ $portfolio->title }}"
                                ></video>
                            @else
                                <img src="{{ $cover->image_url }}" alt="{{ $portfolio->title }}" loading="lazy">
                            @endif
                        @else
                            <div class="portfolio-card__media-empty">Belum ada foto</div>
                        @endif

                        <div class="portfolio-card__badges">
                            <span class="pill-badge badge-vehicle">{{ $portfolio->vehicle_label }}</span>
                            @if($cover && !empty($cover->stage))
                                <span class="pill-badge badge-stage-{{ strtolower($cover->stage) }}">{{ $cover->stage }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="portfolio-card__body">
                        <div class="portfolio-card__service">{{ $portfolio->service->name }}</div>

                        <h2 class="portfolio-card__title">
                            <a href="/portfolio/{{ $portfolio->slug }}">{{ $portfolio->title }}</a>
                        </h2>

                        <p class="portfolio-card__meta">
                            {{ $portfolio->vehicle_label }} · {{ $portfolio->service->name }}
                        </p>

                        <div class="portfolio-card__actions">
                            <a href="/portfolio/{{ $portfolio->slug }}" class="btn btn-white portfolio-card__cta">
                                Lihat Foto Lengkap (Before/After) &rarr;
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
@endsection