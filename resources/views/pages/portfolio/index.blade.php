@extends('layouts.public')
@section('title', 'Portfolio — ' . ($settings['business_name'] ?? 'Bengkel Rudi'))
@section('description', 'Koleksi dokumentasi hasil pengerjaan body repair dan pengecatan kendaraan oleh Bengkel Rudi.')

@section('content')
<div style="margin-bottom:2rem">
    <div class="pill-badge badge-stage-after" style="margin-bottom:0.5rem">Hasil Pekerjaan Nyata</div>
    <h1 style="font-size:1.95rem;font-weight:800;color:var(--text-heading);letter-spacing:-0.03em">Portfolio &amp; Galeri Pengerjaan</h1>
    <p style="color:var(--text-muted);font-size:0.95rem;margin-top:0.25rem">Dokumentasi tahapan perbaikan bodi, pengecatan, dan pemolesan mobil maupun motor di bengkel kami.</p>
</div>

@if($portfolios->isEmpty())
    <div style="padding:2.5rem 1rem;text-align:center;background:#ffffff;border:1px solid var(--border-color);border-radius:var(--radius-md)">
        <p style="color:var(--text-muted)">Belum ada portfolio tersedia.</p>
    </div>
@else
    <div class="grid">
        @foreach($portfolios as $portfolio)
            <article class="card" style="padding:0;overflow:hidden">
                <div style="position:relative;background:#0f172a;aspect-ratio:16/10;overflow:hidden">
                    @if($cover = $portfolio->images->first())
                        <img src="{{ $cover->image_url }}" alt="{{ $portfolio->title }}" loading="lazy" style="width:100%;height:100%;object-fit:cover">
                    @else
                        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:0.8rem">
                            Belum ada foto
                        </div>
                    @endif
                    <div style="position:absolute;top:0.65rem;left:0.65rem;display:flex;gap:0.35rem">
                        <span class="pill-badge badge-vehicle">{{ $portfolio->vehicle_label }}</span>
                        @if($cover && !empty($cover->stage))
                            <span class="pill-badge badge-stage-{{ strtolower($cover->stage) }}">{{ $cover->stage }}</span>
                        @endif
                    </div>
                </div>

                <div style="padding:1.25rem;display:flex;flex-direction:column;flex:1">
                    <div style="font-size:0.75rem;color:var(--brand-primary);font-weight:700;margin-bottom:0.35rem;text-transform:uppercase;letter-spacing:0.04em">
                        {{ $portfolio->service->name }}
                    </div>
                    <h2 style="font-size:1.15rem;font-weight:700;line-height:1.4;margin-bottom:0.4rem">
                        <a href="/portfolio/{{ $portfolio->slug }}" style="color:var(--text-heading)">{{ $portfolio->title }}</a>
                    </h2>
                    <p style="color:var(--text-muted);font-size:0.85rem;margin-bottom:1.1rem">
                        {{ $portfolio->vehicle_label }} · {{ $portfolio->service->name }}
                    </p>
                    <div style="margin-top:auto">
                        <a href="/portfolio/{{ $portfolio->slug }}" class="btn btn-white" style="width:100%;font-size:0.85rem;padding:0.55rem;min-height:40px">
                            Lihat Foto Lengkap (Before/After) &rarr;
                        </a>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
@endif
@endsection
