@extends('layouts.public')

@section('title', 'Kontak & Lokasi Workshop — ' . ($settings['business_name'] ?? 'Bengkel Rudi'))
@section('description', 'Hubungi kami dan temukan petunjuk arah workshop Bengkel Rudi di Wonokerto, Bandar, Kabupaten Batang.')

@push('styles')
    <link rel="stylesheet" href="/css/public/page.css?v={{ filemtime(public_path('css/public/page.css')) }}">
    <link rel="stylesheet" href="/css/public/contact.css?v={{ filemtime(public_path('css/public/contact.css')) }}">
@endpush

@section('content')
    @php($whatsapp = $settings['whatsapp_number'] ?? '628123456789')
    @php($mapsUrl = $settings['google_maps_url'] ?? null)
    @php($mapsEmbedUrl = $settings['google_maps_embed_url'] ?? null)

    @php($contactCards = [
        [
            'label' => 'Alamat Workshop',
            'title' => 'Alamat',
            'value' => $settings['address'] ?? 'RT.05/RW.01, Krajan, Wonokerto, Kec. Bandar, Kabupaten Batang, Jawa Tengah 51254',
            'note' => null,
        ],
        [
            'label' => 'Jam Kerja & Operasional',
            'title' => 'Jam Operasional',
            'value' => $settings['opening_hours'] ?? 'Senin - Sabtu: 08.00 - 17.00 WIB',
            'note' => 'Minggu / Tanggal Merah: Janji Temu',
        ],
    ])

    <div class="page-header">
        <div class="pill-badge page-header__badge page-header__badge--red">Kontak &amp; Kedatangan</div>

        <h1 class="page-title page-title--page page-title--soft">Alamat Workshop &amp; Hotline</h1>

        <p class="page-lead page-lead--plain">
            Kunjungi workshop kami langsung untuk cek kondisi mobil atau hubungi via WhatsApp untuk reservasi.
        </p>
    </div>

    <div class="grid page-single">
        <article class="card page-card">
            <div class="contact__cards">
                @foreach($contactCards as $card)
                    <div class="contact-card">
                        <span class="contact-card__label">{{ $card['label'] }}</span>

                        <p class="contact-card__value">
                            <strong>{{ $card['title'] }}:</strong> {{ $card['value'] }}
                        </p>

                        @if($card['note'])
                            <small class="contact-card__note">{{ $card['note'] }}</small>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="page-actions contact__actions">
                <a
                    class="btn btn-wa"
                    href="https://wa.me/{{ $whatsapp }}?text={{ urlencode('Halo Bengkel Rudi, saya ingin datang konsultasi perbaikan bodi.') }}"
                    target="_blank"
                    rel="noopener"
                >Hubungi Langsung via WhatsApp</a>

                @if(!empty($mapsUrl))
                    <a href="{{ $mapsUrl }}" target="_blank" rel="noopener" class="btn btn-white">
                        Buka Rute di Google Maps &rarr;
                    </a>
                @endif
            </div>

            @if(!empty($mapsEmbedUrl))
                <section aria-labelledby="map-heading" class="contact__map-section">
                    <h2 id="map-heading" class="contact__map-title">Peta Petunjuk Arah Workshop</h2>

                    <div class="contact__map-frame">
                        <iframe
                            src="{{ $mapsEmbedUrl }}"
                            width="100%"
                            height="360"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                        ></iframe>
                    </div>
                </section>
            @endif
        </article>
    </div>
@endsection