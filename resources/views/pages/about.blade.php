@extends('layouts.public')
@section('title', 'Tentang Kami — ' . ($settings['business_name'] ?? 'Bengkel Rudi'))
@section('description', 'Mengenal Bengkel Rudi, spesialis perbaikan bodi, pengecatan panel dan seluruh bodi kendaraan.')

@section('content')
<div style="margin-bottom:2rem">
    <div class="pill-badge" style="background:#fee2e2;color:#b91c1c;margin-bottom:0.5rem">Tentang Bengkel Rudi</div>
    <h1 style="font-size:clamp(1.75rem, 4vw, 2.35rem);font-weight:800;color:var(--text-heading);letter-spacing:-0.02em;margin-bottom:0.35rem">
        Bengkel Cat Oven &amp; Body Repair Semarang
    </h1>
    <p style="color:var(--text-muted);font-size:0.95rem">Membangun kepercayaan pelanggan melalui ketelitian pengerjaan, material cat berkualitas, dan garansi rapi.</p>
</div>

<div class="grid" style="grid-template-columns:1fr;gap:1.5rem">
    <article class="card" style="padding:1.75rem">
        <h2 style="font-size:1.25rem;font-weight:700;color:var(--text-heading);margin-bottom:0.85rem">Dedikasi Kualitas &amp; Kepuasan Pelanggan</h2>
        <p style="color:var(--text-body);font-size:1rem;line-height:1.75;margin-bottom:1.75rem">
            {{ $settings['about_content'] ?? 'Bengkel Rudi bergerak di bidang body repair dan pengecatan kendaraan. Kami mengutamakan ketepatan bentuk bodi, perbaikan penyok, pendempulan rapi, serta pengecatan mobil maupun motor.' }}
        </p>

        <!-- 3 Pilar Keunggulan (seperti Bengkel Anga & Bengkel Cat) -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:1rem;margin-bottom:1.75rem">
            <div style="background:#f8fafc;padding:1.2rem;border-radius:var(--radius-sm);border:1px solid var(--border-color)">
                <div style="color:var(--brand-primary);font-weight:800;font-size:1.05rem;margin-bottom:0.35rem">✓ Ruang Cat Oven Standar</div>
                <p style="color:var(--text-muted);font-size:0.85rem;margin:0;line-height:1.5">Proses spray booth tertutup meminimalkan debu dan partikel asing, menjamin kilap bening merata.</p>
            </div>
            <div style="background:#f8fafc;padding:1.2rem;border-radius:var(--radius-sm);border:1px solid var(--border-color)">
                <div style="color:var(--brand-primary);font-weight:800;font-size:1.05rem;margin-bottom:0.35rem">✓ Tukang Las &amp; Ketok Presisi</div>
                <p style="color:var(--text-muted);font-size:0.85rem;margin:0;line-height:1.5">Menangani bodi sobek, rangka bengkok, hingga panel keropos dengan presisi simetris pabrikan.</p>
            </div>
            <div style="background:#f8fafc;padding:1.2rem;border-radius:var(--radius-sm);border:1px solid var(--border-color)">
                <div style="color:var(--brand-primary);font-weight:800;font-size:1.05rem;margin-bottom:0.35rem">✓ Estimasi Pasti &amp; Garansi</div>
                <p style="color:var(--text-muted);font-size:0.85rem;margin:0;line-height:1.5">Penawaran harga transparan di muka tanpa pembengkakan biaya, lengkap jaminan kepuasan pengerjaan.</p>
            </div>
        </div>

        <div style="border-top:1px solid var(--border-color);padding-top:1.5rem;display:flex;flex-wrap:wrap;gap:0.75rem">
            <a class="btn btn-wa" href="https://wa.me/{{ $settings['whatsapp_number'] ?? '628123456789' }}?text={{ urlencode('Halo Bengkel Rudi, saya ingin tanya seputar pengerjaan bengkel.') }}" target="_blank" rel="noopener">
                Konsultasi WhatsApp Sekarang
            </a>
            <a class="btn btn-white" href="/portfolio">
                Lihat Bukti Hasil Pengerjaan
            </a>
        </div>
    </article>
</div>
@endsection
