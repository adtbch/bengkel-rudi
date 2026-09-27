@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<header class="admin-page-head">
    <div>
        <p class="admin-page-head__eyebrow">Admin Bengkel Rudi</p>
        <h1>Ringkasan katalog</h1>
        <p>Kelola layanan dan hasil pengerjaan yang akan dilihat calon pelanggan.</p>
    </div>
    <span class="admin-page-head__mark" aria-hidden="true">R</span>
</header>

<section class="admin-metric-grid" aria-label="Ringkasan operasional">
    <article class="admin-metric" data-index="01">
        <p class="admin-metric__label">Total portfolio</p>
        <p class="admin-metric__number">{{ $portfolioCount }}</p>
        <p class="admin-metric__hint">Karya tercatat di katalog.</p>
    </article>
    <article class="admin-metric" data-index="02">
        <p class="admin-metric__label">Layanan aktif</p>
        <p class="admin-metric__number">{{ $activeServiceCount }}</p>
        <p class="admin-metric__hint">Layanan siap ditampilkan.</p>
    </article>
</section>

<section class="admin-recent-section" aria-labelledby="portfolio-terbaru">
    <div class="admin-section-head">
        <div>
            <p class="admin-page-head__eyebrow">Aktivitas katalog</p>
            <h2 id="portfolio-terbaru">Portfolio terbaru</h2>
        </div>
        <a class="admin-button admin-button--secondary" href="/admin/portfolio">Kelola portfolio</a>
    </div>

    <div class="admin-recent-work">
        @forelse($newestPortfolios as $portfolio)
            <a class="admin-recent-work__item" href="/admin/portfolio">
                <span class="admin-recent-work__id" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                <span>
                    <span class="admin-recent-work__title">{{ $portfolio->title }}</span>
                    <span class="admin-recent-work__meta">{{ $portfolio->vehicle_type === 'CAR' ? 'Mobil' : 'Motor' }} · {{ $portfolio->is_published ? 'Published' : 'Draft' }}</span>
                </span>
                <span class="admin-recent-work__arrow" aria-hidden="true">↗</span>
            </a>
        @empty
            <div class="admin-panel">
                <h2>Belum ada portfolio.</h2>
                <p>Tambahkan karya pertama untuk mulai membangun katalog.</p>
                <div class="admin-actions"><a class="admin-button" href="/admin/portfolio">Tambah portfolio</a></div>
            </div>
        @endforelse
    </div>
</section>
@endsection
