@extends('layouts.public')
@section('title', $settings['meta_title'] ?? 'Bengkel Rudi - Bengkel Cat dan Body Repair Batang')
@section('description', $settings['meta_description'] ?? 'Bengkel cat dan body repair mobil motor di Wonokerto, Bandar, Kabupaten Batang. Konsultasi langsung melalui WhatsApp.')

@section('structured_data')
@php($structuredData = [
    '@context' => 'https://schema.org',
    '@type' => ['LocalBusiness', 'AutoRepair'],
    'name' => $settings['business_name'] ?? 'Bengkel Cat & Body Repair Rudi',
    'description' => $settings['meta_description'] ?? 'Layanan cat dan body repair mobil dan motor di Wonokerto, Bandar, Kabupaten Batang.',
    'telephone' => '+' . ($settings['whatsapp_number'] ?? '628123456789'),
    'url' => url('/'),
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => $settings['address'] ?? 'RT.05/RW.01, Krajan, Wonokerto, Kec. Bandar, Kabupaten Batang, Jawa Tengah 51254',
        'addressLocality' => 'Bandar',
        'addressRegion' => 'Jawa Tengah',
        'addressCountry' => 'ID',
    ],
    'openingHours' => $settings['opening_hours'] ?? 'Senin - Sabtu: 08.00 - 17.00 WIB',
    'sameAs' => array_values(array_filter([$settings['instagram_url'] ?? null, $settings['google_business_url'] ?? null])),
])
<script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endsection

@section('content')

<section class="workshop-hero" id="beranda" aria-labelledby="hero-title">
    <div class="workshop-hero__content">
        <img class="workshop-hero__brand" src="https://res.cloudinary.com/dkv2rn5ax/image/upload/logo-bengkel-rudi.png_calja6.png" alt="Bengkel Rudi Cat dan Body Repair" width="200" height="200">
        <h1 id="hero-title">Solusi Cat dan Body Repair Terpercaya di Batang</h1>
        <p class="workshop-hero__subtitle">Perbaikan bodi, pengecatan, dan restorasi mobil maupun motor dengan pengerjaan rapi.</p>
        <a class="btn btn-primary workshop-hero__cta" href="https://wa.me/{{ $settings['whatsapp_number'] ?? '628123456789' }}?text={{ urlencode('Halo Bengkel Rudi, saya ingin tanya tentang layanan cat dan body repair.') }}" target="_blank" rel="noopener">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            Hubungi Kami via WhatsApp
        </a>
    </div>
</section>

<section class="quick-info" aria-label="Informasi bengkel">
    <div><strong>Buat janji</strong><span>Hindari antrean dengan konfirmasi jadwal.</span></div>
    <div><strong>Jam buka</strong><span>{{ $settings['opening_hours'] ?? 'Senin - Sabtu: 08.00 - 17.00 WIB' }}</span></div>
    <div><strong>Konsultasi awal</strong><span>Kirim foto kerusakan melalui WhatsApp.</span></div>
</section>

<section class="home-section" aria-labelledby="layanan-title">
    <div class="home-section__heading">
        <h2 id="layanan-title">Layanan kami</h2>
        <p>Penanganan disesuaikan dengan kondisi bodi, luas kerusakan, dan hasil yang Anda inginkan.</p>
    </div>
    <div class="service-cards">
        @forelse($services as $service)
            <article class="service-card">
                <div>
                    <h3><a href="/layanan/{{ $service->slug }}">{{ $service->name }}</a></h3>
                    <p>{{ Str::limit($service->description, 125) }}</p>
                </div>
                <div class="service-card__footer">
                    @if($service->min_price)
                        <span>Mulai Rp {{ number_format($service->min_price, 0, ',', '.') }}</span>
                    @else
                        <span>Harga sesuai kondisi</span>
                    @endif
                    <a href="/layanan/{{ $service->slug }}">Detail <span aria-hidden="true">→</span></a>
                </div>
            </article>
        @empty
            <div class="home-empty">Layanan belum tersedia. Hubungi kami untuk konsultasi langsung.</div>
        @endforelse
    </div>
    <a class="home-text-link" href="/layanan">Lihat semua layanan <span aria-hidden="true">→</span></a>
</section>

<section class="about-preview" aria-labelledby="tentang-title">
    <div>
        <h2 id="tentang-title">Bengkel lokal untuk mobil dan motor.</h2>
        <p>{{ $settings['about_content'] ?? 'Bengkel Rudi melayani perbaikan bodi, pengecatan, dan perawatan kendaraan dengan komunikasi terbuka sejak pemeriksaan awal sampai serah terima.' }}</p>
        <a class="home-text-link" href="/tentang">Tentang Bengkel Rudi <span aria-hidden="true">→</span></a>
    </div>
    <ul>
        <li><strong>Pemeriksaan awal</strong><span>Kondisi kendaraan dicek sebelum menentukan pekerjaan.</span></li>
        <li><strong>Estimasi transparan</strong><span>Biaya dan lingkup pengerjaan dibahas lebih dulu.</span></li>
        <li><strong>Quality control</strong><span>Panel, warna, dan finishing diperiksa sebelum serah terima.</span></li>
    </ul>
</section>

<section class="home-section portfolio-preview" aria-labelledby="portfolio-title">
    <div class="home-section__heading">
        <h2 id="portfolio-title">Hasil pengerjaan terbaru</h2>
        <p>Lihat dokumentasi before, process, dan after dari kendaraan yang kami tangani.</p>
    </div>
    <div class="portfolio-strip">
        @forelse($portfolios as $portfolio)
            @php($thumb = $portfolio->images->first())
            <article class="portfolio-preview-card">
                <a href="/portfolio/{{ $portfolio->slug }}">
                    @if($thumb)
                        <img src="{{ $thumb->image_url }}" alt="{{ $portfolio->title }}" loading="lazy" decoding="async">
                    @else
                        <div class="home-photo-placeholder"><span>Foto belum tersedia</span></div>
                    @endif
                    <div>
                        <span>{{ $portfolio->service->name }}</span>
                        <h3>{{ $portfolio->title }}</h3>
                        <p>{{ $portfolio->vehicle_label }}</p>
                    </div>
                </a>
            </article>
        @empty
            <div class="home-empty">Portfolio belum dipublikasikan.</div>
        @endforelse
    </div>
    @if($portfolioCount > 6)
        <a class="home-text-link" href="/portfolio">Lihat Semua Portfolio <span aria-hidden="true">→</span></a>
    @endif
</section>

<section class="review-cta" aria-labelledby="ulasan-title">
    <div>
        <h2 id="ulasan-title">Lihat ulasan pelanggan</h2>
        <p>Pengalaman pelanggan membantu Anda menilai pelayanan Bengkel Rudi sebelum datang.</p>
    </div>
    @if(!empty($settings['google_business_url']))
        <a class="btn btn-white" href="{{ $settings['google_business_url'] }}" target="_blank" rel="noopener">Buka Google Reviews</a>
    @else
        <a class="btn btn-white" href="/kontak">Hubungi bengkel</a>
    @endif
</section>

@if(!empty($settings['google_maps_embed_url']))
<section class="home-location" aria-labelledby="lokasi-title">
    <div class="home-location__copy">
        <h2 id="lokasi-title">Lokasi workshop</h2>
        <p>{{ $settings['address'] ?? 'RT.05/RW.01, Krajan, Wonokerto, Kec. Bandar, Kabupaten Batang, Jawa Tengah 51254' }}</p>
        <span>{{ $settings['opening_hours'] ?? 'Senin - Sabtu: 08.00 - 17.00 WIB' }}</span>
        <a class="home-text-link" href="/kontak">Lihat kontak dan petunjuk arah <span aria-hidden="true">→</span></a>
    </div>
    <iframe src="{{ $settings['google_maps_embed_url'] }}" title="Lokasi Bengkel Rudi" width="100%" height="360" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
</section>
@endif

<section class="home-cta" aria-labelledby="cta-title">
    <div>
        <h2 id="cta-title">Perlu estimasi perbaikan?</h2>
        <p>Kirim foto bagian kendaraan yang rusak. Kami bantu menentukan penanganan awal.</p>
    </div>
    <a class="btn btn-wa" href="https://wa.me/{{ $settings['whatsapp_number'] ?? '628123456789' }}?text={{ urlencode('Halo Bengkel Rudi, saya ingin meminta estimasi awal. Berikut foto kondisi kendaraan saya.') }}" target="_blank" rel="noopener">Kirim foto via WhatsApp</a>
</section>
@endsection