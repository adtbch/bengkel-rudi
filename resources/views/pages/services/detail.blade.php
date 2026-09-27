@extends('layouts.public')
@section('title', $service->name . ' — ' . ($settings['business_name'] ?? 'Bengkel Rudi'))
@section('description', trim(strip_tags($service->description)))

@section('content')
<div style="margin-bottom:1.75rem">
    <a href="/layanan" style="font-size:0.85rem;color:var(--text-muted);display:inline-flex;align-items:center;gap:0.35rem;margin-bottom:0.75rem;font-weight:600">
        &larr; Kembali ke Daftar Layanan
    </a>
    <h1 style="font-size:clamp(1.75rem, 4vw, 2.35rem);font-weight:800;color:var(--text-heading);letter-spacing:-0.02em">
        {{ $service->name }}
    </h1>
</div>

<div class="grid" style="grid-template-columns:1fr;gap:1.5rem">
    <article class="card" style="padding:1.75rem">
        <div style="font-size:0.8rem;color:var(--brand-primary);font-weight:800;text-transform:uppercase;letter-spacing:0.04em;margin-bottom:0.5rem">
            Deskripsi Layanan &amp; Ketentuan
        </div>
        <p style="color:var(--text-body);font-size:1.02rem;line-height:1.7;margin-bottom:1.5rem">
            {{ $service->description }}
        </p>

        @if($service->min_price || $service->max_price)
            <div style="background:#f8fafc;border:1px solid var(--border-color);border-radius:var(--radius-sm);padding:1.35rem;margin-bottom:1.5rem">
                <span style="font-size:0.8rem;color:var(--text-muted);display:block;margin-bottom:0.25rem;text-transform:uppercase;font-weight:700">Estimasi Biaya Pengerjaan:</span>
                <p style="font-size:1.45rem;font-weight:800;color:var(--brand-primary);margin:0">
                    @if($service->min_price)
                        Rp{{ number_format($service->min_price, 0, ',', '.') }}
                    @endif
                    @if($service->max_price)
                        – Rp{{ number_format($service->max_price, 0, ',', '.') }}
                    @endif
                </p>
                <small style="color:var(--text-muted);display:block;margin-top:0.4rem;font-size:0.78rem">
                    *Estimasi final disesuaikan dengan luas bidang panel, tingkat keparahan bodi, dan jenis warna cat kendaraan.
                </small>
            </div>
        @endif

        @if(!empty($service->features) && count($service->features) > 0)
            <div style="margin-bottom:1.75rem">
                <h2 style="font-size:1.1rem;font-weight:700;color:var(--text-heading);margin-bottom:0.85rem">Keunggulan &amp; Cakupan Pekerjaan:</h2>
                <ul style="list-style:none;padding:0;display:flex;flex-direction:column;gap:0.6rem">
                    @foreach($service->features as $feature)
                        <li style="display:flex;align-items:center;gap:0.6rem;color:var(--text-body);font-size:0.95rem">
                            <span style="color:var(--wa-color);font-weight:bold;font-size:1.1rem">&#10003;</span>
                            <span>{{ $feature }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div style="border-top:1px solid var(--border-color);padding-top:1.5rem;display:flex;flex-wrap:wrap;gap:0.75rem">
            <a class="btn btn-wa" href="https://wa.me/{{ $settings['whatsapp_number'] ?? '628123456789' }}?text={{ urlencode('Halo Bengkel Rudi, saya ingin konsultasi estimasi layanan ' . $service->name) }}" target="_blank" rel="noopener" style="flex:1">
                Konsultasi Layanan via WhatsApp
            </a>
            <a class="btn btn-white" href="/portfolio">
                Lihat Contoh Hasil di Galeri
            </a>
        </div>
    </article>
</div>
@endsection
