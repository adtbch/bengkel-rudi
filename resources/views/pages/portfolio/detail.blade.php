@extends('layouts.public')

@section('title', $portfolio->title . ' — ' . ($settings['business_name'] ?? 'Bengkel Rudi'))
@section('description', 'Dokumentasi BEFORE, PROCESS, dan AFTER pengerjaan ' . $portfolio->title . ' di Bengkel Rudi.')

@push('styles')
    <link rel="stylesheet" href="/css/public/page.css?v={{ filemtime(public_path('css/public/page.css')) }}">
    <link rel="stylesheet" href="/css/public/portfolio.css?v={{ filemtime(public_path('css/public/portfolio.css')) }}">
@endpush

@section('breadcrumb_structured_data')
    @php($breadcrumbs = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => rtrim(config('app.url'), '/') . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Portfolio', 'item' => rtrim(config('app.url'), '/') . '/portfolio'],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $portfolio->title, 'item' => rtrim(config('app.url'), '/') . '/portfolio/' . $portfolio->slug],
        ],
    ])

    <script type="application/ld+json">{!! json_encode($breadcrumbs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endsection

@section('content')
    @php($whatsapp = $settings['whatsapp_number'] ?? '628123456789')

    <div class="page-header page-header--compact">
        <a href="/portfolio" class="page-backlink">&larr; Kembali ke Galeri Portfolio</a>

        <div class="portfolio-detail-meta">
            <span class="pill-badge badge-vehicle">{{ $portfolio->vehicle_label }}</span>
            <span class="portfolio-detail-service">{{ $portfolio->service->name }}</span>
        </div>

        <h1 class="page-title page-title--page">{{ $portfolio->title }}</h1>

        <p class="portfolio-detail-subtitle">
            {{ $portfolio->vehicle_label }} · {{ $portfolio->service->name }}
        </p>
    </div>

    <article class="portfolio-detail-body">
        @foreach(['AFTER' => 'Hasil akhir'] as $stage => $label)
            @if($portfolio->images->where('stage', $stage)->isNotEmpty())
                @php($stageImages = $portfolio->images->where('stage', $stage))

                <section aria-labelledby="stage-{{ $stage }}" class="card">
                    <div class="portfolio-stage__head">
                        <div class="portfolio-stage__headings">
                            <span class="pill-badge badge-stage-{{ strtolower($stage) }}">{{ $stage }}</span>
                            <h2 id="stage-{{ $stage }}" class="portfolio-stage__title">{{ $label }}</h2>
                        </div>
                    </div>

                    @if($stageImages->isEmpty())
                        <p class="portfolio-stage__empty">Belum ada foto pada tahap ini.</p>
                    @else
                        <div class="grid portfolio-stage__grid">
                            @foreach($stageImages as $image)
                                <figure class="portfolio-shot">
                                    <div class="portfolio-media">
                                        @if($image->media_type === 'video')
                                            <video
                                                src="{{ $image->image_url }}"
                                                controls
                                                preload="metadata"
                                                playsinline
                                                aria-label="Video {{ $portfolio->title }} — {{ $label }}"
                                            ></video>
                                        @else
                                            <img
                                                src="{{ $image->image_url }}"
                                                alt="{{ $portfolio->title }} — {{ $label }}"
                                                loading="lazy"
                                                decoding="async"
                                            >
                                        @endif
                                    </div>

                                    <figcaption class="portfolio-shot__caption">
                                        <span>{{ $label }}</span>
                                        <span class="pill-badge badge-stage-{{ strtolower($stage) }}">{{ $stage }}</span>
                                    </figcaption>
                                </figure>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endif
        @endforeach

        {{-- Ajakan konsultasi untuk kerusakan serupa --}}
        <div class="card portfolio-cta">
            <h3 class="portfolio-cta__title">Kendaraan Anda Mengalami Kerusakan Serupa?</h3>

            <p class="portfolio-cta__text">
                Kirimkan foto bagian bodi yang lecet, penyok, atau berkarat via WhatsApp kami.
                Teknisi kami akan memberikan perkiraan biaya dan durasi perbaikan.
            </p>

            <div class="portfolio-cta__actions">
                <a
                    class="btn btn-wa"
                    href="https://wa.me/{{ $whatsapp }}?text={{ urlencode('Halo Bengkel Rudi, saya melihat hasil pengerjaan ' . $portfolio->title . ' dan ingin konsultasi kondisi mobil saya.') }}"
                    target="_blank"
                    rel="noopener"
                >Konsultasi WhatsApp Sekarang</a>

                <a class="btn btn-white" href="/portfolio">Lihat Portfolio Lain</a>
            </div>
        </div>
    </article>
@endsection