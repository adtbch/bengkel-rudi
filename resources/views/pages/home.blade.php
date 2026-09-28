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
        <img class="workshop-hero__brand" src="https://res.cloudinary.com/dkv2rn5ax/image/upload/logo-bengkel-rudi.png_calja6.png" alt="Bengkel Rudi - Cat &amp; Body Repair" width="180" height="180">
        <h1 id="hero-title">Cat dan Body Repair Terpercaya di Batang</h1>
        <p class="workshop-hero__subtitle">Perbaikan penyok dan pengecatan mobil maupun motor dengan hasil rapi dan Terpercaya.</p>
        <div class="home-actions">
            <a class="btn btn-wa" href="https://wa.me/{{ $settings['whatsapp_number'] ?? '628123456789' }}?text={{ urlencode('Halo Bengkel Rudi, saya ingin konsultasi perbaikan kendaraan dan mengirim foto kondisinya.') }}" target="_blank" rel="noopener">Konsultasi via WhatsApp</a>
        </div>
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