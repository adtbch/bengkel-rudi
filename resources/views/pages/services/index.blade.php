@extends('layouts.public')
@section('title', 'Layanan & Paket Cat — ' . ($settings['business_name'] ?? 'Bengkel Rudi'))
@section('description', 'Daftar layanan pengecatan, body repair, perbaikan bodi penyok, dan perawatan kendaraan di Bengkel Rudi.')

@section('content')
<div style="margin-bottom:2rem">
    <div class="pill-badge" style="background:#fee2e2;color:#b91c1c;margin-bottom:0.5rem">Paket &amp; Layanan Bengkel</div>
    <h1 style="font-size:1.95rem;font-weight:800;color:var(--text-heading);letter-spacing:-0.03em">Daftar Layanan Perbaikan &amp; Cat Kendaraan</h1>
    <p style="color:var(--text-muted);font-size:0.95rem;margin-top:0.25rem">Pilihan pengecatan dan body repair dengan pengerjaan rapi serta estimasi transparan.</p>
</div>

@if($services->isEmpty())
    <div style="padding:2.5rem 1rem;text-align:center;background:#ffffff;border:1px solid var(--border-color);border-radius:var(--radius-md)">
        <p style="color:var(--text-muted)">Belum ada layanan tersedia.</p>
    </div>
@else
    <div class="grid">
        @foreach($services as $service)
            <article class="card" style="display:flex;flex-direction:column;justify-content:space-between">
                <div>
                    <h2 style="font-size:1.25rem;font-weight:700;margin-bottom:0.5rem">
                        <a href="/layanan/{{ $service->slug }}" style="color:var(--text-heading)">{{ $service->name }}</a>
                    </h2>
                    <p style="color:var(--text-muted);font-size:0.9rem;margin-bottom:1.25rem;line-height:1.6">
                        {{ $service->description }}
                    </p>
                </div>
                <div style="border-top:1px solid var(--border-color);padding-top:1rem;margin-top:auto">
                    @if($service->min_price)
                        <div style="font-size:0.75rem;color:var(--text-muted);text-transform:uppercase;font-weight:600">Estimasi Biaya</div>
                        <p style="font-size:1.2rem;font-weight:800;color:var(--brand-primary);margin-bottom:0.75rem">
                            Rp {{ number_format($service->min_price, 0, ',', '.') }}
                            @if($service->max_price)
                                <span style="font-size:0.9rem;font-weight:600;color:var(--text-muted)">s/d Rp {{ number_format($service->max_price, 0, ',', '.') }}</span>
                            @endif
                        </p>
                    @endif
                    <div style="display:flex;gap:0.5rem">
                        <a href="/layanan/{{ $service->slug }}" class="btn btn-white" style="flex:1;font-size:0.85rem;padding:0.5rem;min-height:40px">
                            Detail Layanan
                        </a>
                        <a href="https://wa.me/{{ $settings['whatsapp_number'] ?? '628123456789' }}?text={{ urlencode('Halo Bengkel Rudi, saya ingin tanya estimasi untuk layanan ' . $service->name) }}" target="_blank" rel="noopener" class="btn btn-wa" style="padding:0.5rem 0.85rem;min-height:40px" title="Chat WhatsApp">
                            Chat WA
                        </a>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
@endif
@endsection
