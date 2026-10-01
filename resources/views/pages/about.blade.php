@extends('layouts.public')

@section('title', 'Tentang Kami — ' . ($settings['business_name'] ?? 'Bengkel Rudi'))
@section('description', 'Mengenal Bengkel Rudi, spesialis perbaikan bodi, pengecatan panel dan seluruh bodi kendaraan.')

@push('styles')
    <link rel="stylesheet" href="/css/public/page.css?v={{ filemtime(public_path('css/public/page.css')) }}">
    <link rel="stylesheet" href="/css/public/about.css?v={{ filemtime(public_path('css/public/about.css')) }}">
@endpush

@section('content')
    @php($whatsapp = $settings['whatsapp_number'] ?? '628123456789')

    @php($pillars = [
        [
            'title' => 'Pengecatan Rapi dan Terkontrol',
            'text' => 'Area kerja dijaga bersih untuk membantu hasil pengecatan rapi dan merata.',
        ],
        [
            'title' => 'Tukang Las & Ketok Presisi',
            'text' => 'Menangani bodi sobek, rangka bengkok, hingga panel keropos dengan presisi simetris pabrikan.',
        ],
        [
            'title' => 'Estimasi Pasti & Garansi',
            'text' => 'Penawaran harga transparan di muka tanpa pembengkakan biaya, lengkap jaminan kepuasan pengerjaan.',
        ],
    ])

    <div class="page-header">
        <div class="pill-badge page-header__badge page-header__badge--red">Tentang Bengkel Rudi</div>

        <h1 class="page-title page-title--page page-title--soft">
            Bengkel Cat &amp; Body Repair di Kabupaten Batang
        </h1>

        <p class="page-lead page-lead--plain">
            Membangun kepercayaan pelanggan melalui ketelitian pengerjaan, material cat berkualitas, dan garansi rapi.
        </p>
    </div>

    <div class="grid page-single">
        <article class="card page-card">
            <h2 class="about__heading">Dedikasi Kualitas &amp; Kepuasan Pelanggan</h2>

            <p class="about__content">
                {{ $settings['about_content'] ?? 'Bengkel Rudi bergerak di bidang body repair dan pengecatan kendaraan. Kami mengutamakan ketepatan bentuk bodi, perbaikan penyok, pendempulan rapi, serta pengecatan mobil maupun motor.' }}
            </p>

            <div class="pillars">
                @foreach($pillars as $pillar)
                    <div class="pillar">
                        <div class="pillar__title">✓ {{ $pillar['title'] }}</div>
                        <p class="pillar__text">{{ $pillar['text'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="page-actions page-actions--bordered">
                <a
                    class="btn btn-wa"
                    href="https://wa.me/{{ $whatsapp }}?text={{ urlencode('Halo Bengkel Rudi, saya ingin tanya seputar pengerjaan bengkel.') }}"
                    target="_blank"
                    rel="noopener"
                >Konsultasi WhatsApp Sekarang</a>

                <a class="btn btn-white" href="/portfolio">Lihat Bukti Hasil Pengerjaan</a>
            </div>
        </article>
    </div>
@endsection