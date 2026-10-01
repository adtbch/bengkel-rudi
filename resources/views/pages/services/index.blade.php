@extends('layouts.public')

@section('title', 'Layanan & Paket Cat — ' . ($settings['business_name'] ?? 'Bengkel Rudi'))
@section('description', 'Daftar layanan pengecatan, body repair, perbaikan bodi penyok, dan perawatan kendaraan di Bengkel Rudi.')

@push('styles')
    <link rel="stylesheet" href="/css/public/page.css?v={{ filemtime(public_path('css/public/page.css')) }}">
    <link rel="stylesheet" href="/css/public/services.css?v={{ filemtime(public_path('css/public/services.css')) }}">
@endpush

@section('content')
    @php($whatsapp = $settings['whatsapp_number'] ?? '628123456789')

    <div class="page-header">
        <div class="pill-badge page-header__badge page-header__badge--red">Paket &amp; Layanan Bengkel</div>
        <h1 class="page-title page-title--index">Daftar Layanan Perbaikan &amp; Cat Kendaraan</h1>
        <p class="page-lead">
            Pilihan pengecatan dan body repair dengan pengerjaan rapi serta estimasi transparan.
        </p>
    </div>

    @if($services->isEmpty())
        <div class="page-empty">
            <p class="page-empty__text">Belum ada layanan tersedia.</p>
        </div>
    @else
        <div class="grid">
            @foreach($services as $service)
                <article class="card service-card">
                    <div>
                        <h2 class="service-card__title">
                            <a href="/layanan/{{ $service->slug }}">{{ $service->name }}</a>
                        </h2>
                        <p class="service-card__excerpt">{{ $service->description }}</p>
                    </div>

                    <div class="service-card__footer">
                        @if($service->min_price)
                            <div class="service-card__price-label">Estimasi Biaya</div>
                            <p class="service-card__price">
                                Rp {{ number_format($service->min_price, 0, ',', '.') }}
                                @if($service->max_price)
                                    <span>s/d Rp {{ number_format($service->max_price, 0, ',', '.') }}</span>
                                @endif
                            </p>
                        @endif

                        <div class="service-card__actions">
                            <a href="/layanan/{{ $service->slug }}" class="btn btn-white service-card__detail">
                                Detail Layanan
                            </a>

                            <a
                                href="https://wa.me/{{ $whatsapp }}?text={{ urlencode('Halo Bengkel Rudi, saya ingin tanya estimasi untuk layanan ' . $service->name) }}"
                                target="_blank"
                                rel="noopener"
                                class="btn btn-wa service-card__wa"
                                title="Chat WhatsApp"
                            >Chat WA</a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
@endsection