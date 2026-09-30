@extends('layouts.public')
@section('title', $portfolio->title . ' — ' . ($settings['business_name'] ?? 'Bengkel Rudi'))
@section('description', 'Dokumentasi BEFORE, PROCESS, dan AFTER pengerjaan ' . $portfolio->title . ' di Bengkel Rudi.')

@section('breadcrumb_structured_data')
@php($breadcrumbs = [
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => rtrim(config('app.url'), '/') . '/'],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Portfolio', 'item' => rtrim(config('app.url'), '/') . '/portfolio'],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $portfolio->title, 'item' => rtrim(config('app.url'), '/') . '/portfolio/'.$portfolio->slug],
    ],
])
<script type="application/ld+json">{!! json_encode($breadcrumbs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endsection

@section('content')
<div style="margin-bottom:1.75rem">
    <a href="/portfolio" style="font-size:0.85rem;color:var(--text-muted);display:inline-flex;align-items:center;gap:0.35rem;margin-bottom:0.75rem;font-weight:600">
        &larr; Kembali ke Galeri Portfolio
    </a>
    <div style="display:flex;flex-wrap:wrap;gap:0.5rem;align-items:center;margin-bottom:0.5rem">
        <span class="pill-badge badge-vehicle">{{ $portfolio->vehicle_label }}</span>
        <span style="font-size:0.85rem;color:var(--brand-primary);font-weight:700">{{ $portfolio->service->name }}</span>
    </div>
    <h1 style="font-size:clamp(1.75rem, 4vw, 2.35rem);font-weight:800;color:var(--text-heading);letter-spacing:-0.03em;margin-bottom:0.35rem">
        {{ $portfolio->title }}
    </h1>
    <p style="color:var(--text-muted);font-size:0.88rem">
        {{ $portfolio->vehicle_label }} · {{ $portfolio->service->name }}
    </p>
</div>

<article style="display:flex;flex-direction:column;gap:1.75rem">
    @foreach(['BEFORE' => 'Sebelum pengerjaan', 'PROCESS' => 'Proses pengerjaan', 'AFTER' => 'Hasil akhir'] as $stage => $label)
        <section aria-labelledby="stage-{{ $stage }}" class="card" style="padding:1.35rem">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;border-bottom:1px solid var(--border-color);padding-bottom:0.75rem">
                <div style="display:flex;align-items:center;gap:0.5rem">
                    <span class="pill-badge badge-stage-{{ strtolower($stage) }}">{{ $stage }}</span>
                    <h2 id="stage-{{ $stage }}" style="font-size:1.15rem;font-weight:700;color:var(--text-heading);margin:0">
                        {{ $label }}
                    </h2>
                </div>
            </div>

            @php($stageImages = $portfolio->images->where('stage', $stage))

            @if($stageImages->isEmpty())
                <p style="color:var(--text-muted);font-size:0.88rem;padding:0.75rem 0">Belum ada foto pada tahap ini.</p>
            @else
                <div class="grid" style="grid-template-columns:repeat(auto-fill, minmax(260px, 1fr));gap:1rem">
                    @foreach($stageImages as $image)
                        <figure style="margin:0;background:#ffffff;border:1px solid var(--border-color);border-radius:var(--radius-sm);overflow:hidden;box-shadow:var(--shadow-sm)">
                            <div class="portfolio-media">
                                @if($image->media_type === 'video')
                                    <video src="{{ $image->image_url }}" controls preload="metadata" playsinline aria-label="Video {{ $portfolio->title }} — {{ $label }}"></video>
                                @else
                                    <img src="{{ $image->image_url }}" alt="{{ $portfolio->title }} — {{ $label }}" loading="lazy" decoding="async">
                                @endif
                            </div>
                            <figcaption style="padding:0.65rem 0.75rem;font-size:0.75rem;color:var(--text-muted);display:flex;justify-content:space-between;align-items:center;background:#fafafa">
                                <span>{{ $label }}</span>
                                <span class="pill-badge badge-stage-{{ strtolower($stage) }}">{{ $stage }}</span>
                            </figcaption>
                        </figure>
                    @endforeach
                </div>
            @endif
        </section>
    @endforeach

    <!-- Bottom Conversion Card (Bengkel Cat / Bengkel Anga style) -->
    <div class="card" style="padding:1.75rem;text-align:center;background:#ffffff;border:2px solid #fee2e2">
        <h3 style="font-size:1.25rem;font-weight:800;color:var(--text-heading);margin-bottom:0.5rem">
            Kendaraan Anda Mengalami Kerusakan Serupa?
        </h3>
        <p style="color:var(--text-body);font-size:0.92rem;max-width:540px;margin:0 auto 1.25rem;line-height:1.6">
            Kirimkan foto bagian bodi yang lecet, penyok, atau berkarat via WhatsApp kami. Teknisi kami akan memberikan perkiraan biaya dan durasi perbaikan.
        </p>
        <div style="display:flex;justify-content:center;gap:0.75rem;flex-wrap:wrap">
            <a class="btn btn-wa" href="https://wa.me/{{ $settings['whatsapp_number'] ?? '628123456789' }}?text={{ urlencode('Halo Bengkel Rudi, saya melihat hasil pengerjaan ' . $portfolio->title . ' dan ingin konsultasi kondisi mobil saya.') }}" target="_blank" rel="noopener">
                Konsultasi WhatsApp Sekarang
            </a>
            <a class="btn btn-white" href="/portfolio">
                Lihat Portfolio Lain
            </a>
        </div>
    </div>
</article>
@endsection
