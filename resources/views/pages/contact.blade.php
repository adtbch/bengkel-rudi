@extends('layouts.public')
@section('title', 'Kontak & Lokasi Workshop — ' . ($settings['business_name'] ?? 'Bengkel Rudi'))
@section('description', 'Hubungi kami dan temukan petunjuk arah workshop Bengkel Rudi di Wonokerto, Bandar, Kabupaten Batang.')

@section('content')
<div style="margin-bottom:2rem">
    <div class="pill-badge" style="background:#fee2e2;color:#b91c1c;margin-bottom:0.5rem">Kontak &amp; Kedatangan</div>
    <h1 style="font-size:clamp(1.75rem, 4vw, 2.35rem);font-weight:800;color:var(--text-heading);letter-spacing:-0.02em;margin-bottom:0.35rem">
        Alamat Workshop &amp; Hotline
    </h1>
    <p style="color:var(--text-muted);font-size:0.95rem">Kunjungi workshop kami langsung untuk cek kondisi mobil atau hubungi via WhatsApp untuk reservasi.</p>
</div>

<div class="grid" style="grid-template-columns:1fr;gap:1.5rem">
    <article class="card" style="padding:1.75rem">
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:1.5rem;margin-bottom:1.75rem">
            <div style="background:#f8fafc;padding:1.25rem;border-radius:var(--radius-sm);border:1px solid var(--border-color)">
                <span style="font-size:0.75rem;color:var(--brand-primary);text-transform:uppercase;letter-spacing:0.04em;font-weight:800;display:block;margin-bottom:0.35rem">Alamat Workshop</span>
                <p style="font-size:1.05rem;color:var(--text-heading);margin:0;line-height:1.6">
                    <strong>Alamat:</strong> {{ $settings['address'] ?? 'RT.05/RW.01, Krajan, Wonokerto, Kec. Bandar, Kabupaten Batang, Jawa Tengah 51254' }}
                </p>
            </div>
            <div style="background:#f8fafc;padding:1.25rem;border-radius:var(--radius-sm);border:1px solid var(--border-color)">
                <span style="font-size:0.75rem;color:var(--brand-primary);text-transform:uppercase;letter-spacing:0.04em;font-weight:800;display:block;margin-bottom:0.35rem">Jam Kerja &amp; Operasional</span>
                <p style="font-size:1.05rem;color:var(--text-heading);margin:0;line-height:1.6">
                    <strong>Jam Operasional:</strong> {{ $settings['opening_hours'] ?? 'Senin - Sabtu: 08.00 - 17.00 WIB' }}
                </p>
                <small style="color:var(--text-muted);display:block;margin-top:0.25rem">Minggu / Tanggal Merah: Janji Temu</small>
            </div>
        </div>

        <div style="display:flex;flex-wrap:wrap;gap:0.75rem;margin-bottom:1.75rem">
            <a class="btn btn-wa" href="https://wa.me/{{ $settings['whatsapp_number'] ?? '628123456789' }}?text={{ urlencode('Halo Bengkel Rudi, saya ingin datang konsultasi perbaikan bodi.') }}" target="_blank" rel="noopener">
                Hubungi Langsung via WhatsApp
            </a>
            @if(!empty($settings['google_maps_url']))
                <a href="{{ $settings['google_maps_url'] }}" target="_blank" rel="noopener" class="btn btn-white">
                    Buka Rute di Google Maps &rarr;
                </a>
            @endif
        </div>

        @if(!empty($settings['google_maps_embed_url']))
            <section aria-labelledby="map-heading" style="border-top:1px solid var(--border-color);padding-top:1.5rem">
                <h2 id="map-heading" style="font-size:1.15rem;font-weight:700;color:var(--text-heading);margin-bottom:0.75rem">Peta Petunjuk Arah Workshop</h2>
                <div style="max-width:100%;overflow:hidden;border:1px solid var(--border-color);border-radius:var(--radius-sm);box-shadow:var(--shadow-sm)">
                    <iframe src="{{ $settings['google_maps_embed_url'] }}" width="100%" height="360" style="border:0;display:block" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </section>
        @endif
    </article>
</div>
@endsection
