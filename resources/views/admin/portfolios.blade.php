@extends('layouts.admin')
@section('title', 'Portfolio')

@section('content')
<header class="admin-page-head">
    <div>
        <p class="admin-page-head__eyebrow">Katalog pengerjaan</p>
        <h1>Portfolio</h1>
        <p>Kelola karya, status publikasi, dan bukti visual kendaraan.</p>
    </div>
    <span class="admin-page-head__mark" aria-hidden="true">P</span>
</header>

@if($errors->any())
    <div class="admin-alert" role="alert">{{ $errors->first() }}</div>
@endif
@if(session('status'))
    <div class="admin-alert" role="status">{{ session('status') }}</div>
@endif

<section class="admin-panel" aria-labelledby="portfolio-baru">
    <h2 id="portfolio-baru">Tambah portfolio</h2>
    <form method="post" action="/admin/portfolio" class="admin-form-grid">
        @csrf
        <label class="admin-field" for="create-title">Judul
            <input id="create-title" name="title" value="{{ old('title') }}" required maxlength="160" @error('title') aria-invalid="true" aria-describedby="create-title-error" @enderror>
            @error('title')<span id="create-title-error" class="admin-field-error" role="alert">{{ $message }}</span>@enderror
        </label>
        <label class="admin-field" for="create-service">Layanan
            <select id="create-service" name="service_id" required @error('service_id') aria-invalid="true" aria-describedby="create-service-error" @enderror>
                @foreach($services as $service)
                    <option value="{{ $service->id }}" @selected(old('service_id') == $service->id)>{{ $service->name }}</option>
                @endforeach
            </select>
            @error('service_id')<span id="create-service-error" class="admin-field-error" role="alert">{{ $message }}</span>@enderror
        </label>
        <label class="admin-field" for="create-vehicle">Kendaraan
            <select id="create-vehicle" name="vehicle_type" required @error('vehicle_type') aria-invalid="true" aria-describedby="create-vehicle-error" @enderror>
                <option value="CAR" @selected(old('vehicle_type', 'CAR') === 'CAR')>Mobil</option>
                <option value="MOTOR" @selected(old('vehicle_type') === 'MOTOR')>Motor</option>
            </select>
            @error('vehicle_type')<span id="create-vehicle-error" class="admin-field-error" role="alert">{{ $message }}</span>@enderror
        </label>
        <label class="admin-check"><input type="checkbox" name="is_published" value="1" @checked(old('is_published'))> Langsung publish</label>
        <div class="admin-actions"><button class="admin-button" type="submit">Tambah portfolio</button></div>
    </form>
</section>

<section aria-labelledby="daftar-portfolio">
    <div class="admin-section-head">
        <div>
            <p class="admin-page-head__eyebrow">Arsip visual</p>
            <h2 id="daftar-portfolio">Daftar portfolio</h2>
        </div>
        <p>{{ $portfolios->count() }} item</p>
    </div>
    <div class="admin-card-grid">
        @forelse($portfolios as $portfolio)
            <article class="admin-card admin-portfolio-card">
                @php($cover = $portfolio->images->first())
                <div class="admin-portfolio-card__summary">
                    <div class="admin-portfolio-card__cover">
                        @if($cover)
                            <img src="{{ $cover->image_url }}" alt="Foto utama {{ $portfolio->title }}" loading="lazy" decoding="async">
                        @else
                            <span aria-hidden="true">Belum ada foto</span>
                        @endif
                    </div>
                    <div class="admin-portfolio-card__info">
                        <div class="admin-portfolio-card__title-row">
                            <h3>{{ $portfolio->title }}</h3>
                            <span class="admin-status{{ $portfolio->is_published ? '' : ' admin-status--draft' }}">{{ $portfolio->is_published ? 'Terbit' : 'Draf' }}</span>
                        </div>
                        <p>{{ $portfolio->vehicle_label }} · {{ $portfolio->service->name }} · {{ $portfolio->images->count() }} foto</p>
                    </div>
                </div>
            <details class="admin-section admin-portfolio-editor">
                <summary class="admin-section-summary">Kelola portfolio</summary>
                <form method="post" action="/admin/portfolio/{{ $portfolio->id }}" class="admin-form-grid">
                    @csrf
                    @method('PUT')
                    <label class="admin-field admin-field--wide">Judul
                        <input name="title" value="{{ $portfolio->title }}" required maxlength="160">
                    </label>
                    <label class="admin-field">Layanan
                        <select name="service_id" required>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}" @selected($portfolio->service_id === $service->id)>{{ $service->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="admin-field">Kendaraan
                        <select name="vehicle_type" required>
                            <option value="CAR" @selected($portfolio->vehicle_type === 'CAR')>Mobil</option>
                            <option value="MOTOR" @selected($portfolio->vehicle_type === 'MOTOR')>Motor</option>
                        </select>
                    </label>
                    <input type="hidden" name="is_published" value="{{ $portfolio->is_published ? 1 : 0 }}">
                    <div class="admin-actions"><button class="admin-button" type="submit">Simpan perubahan</button></div>
                </form>
            </details>

                <details class="admin-section">
                    <summary class="admin-section-summary">Tambah foto</summary>
                    <form method="post" action="/admin/portfolio/{{ $portfolio->id }}/images" enctype="multipart/form-data" class="admin-form-grid">
                        @csrf
                        <label class="admin-field admin-field--wide">Foto (JPG, PNG, WebP; maksimal 10 MB per foto)
                            <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple required data-image-input>
                            <span class="admin-field-help">Pilih satu atau beberapa foto. Preview hanya tampil di perangkat ini.</span>
                        </label>
                        <div class="admin-image-preview admin-field--wide" data-image-preview aria-live="polite"></div>
                        <label class="admin-field">Tahap foto
                            <select name="stage" required>
                                <option value="BEFORE">Sebelum perbaikan</option>
                                <option value="PROCESS">Proses perbaikan</option>
                                <option value="AFTER">Hasil akhir</option>
                            </select>
                        </label>
                        <label class="admin-field">Urutan awal
                            <input type="number" name="sort_order" value="1" min="1" step="1">
                        </label>
                        <div class="admin-actions"><button class="admin-button" type="submit">Simpan foto</button></div>
                    </form>
                </details>

                @if($portfolio->images->count())
                    <details class="admin-section">
                    <summary class="admin-section-summary">Kelola foto ({{ $portfolio->images->count() }})</summary>
                    <h4 class="admin-image-heading">Foto Terunggah</h4>
                    <form method="post" action="/admin/portfolio/{{ $portfolio->id }}/images/order" class="admin-form-grid admin-image-order">
                        @csrf
                        @foreach($portfolio->images as $img)
                            <div class="admin-image-item">
                                <img src="{{ $img->image_url }}" alt="Foto {{ strtolower($img->stage) }} {{ $portfolio->title }}" loading="lazy" decoding="async" />
                                <span class="pill-badge @if($img->stage === 'BEFORE') badge-stage-before @elseif($img->stage === 'PROCESS') badge-stage-process @else badge-stage-after @endif">{{ $img->stage }}</span>
                                <label class="admin-field admin-image-order__field">Urutan
                                    <input type="number" name="order[{{ $img->id }}]" value="{{ max(1, $img->sort_order) }}" min="1" step="1" />
                                </label>
                                <button type="submit" form="delete-image-{{ $portfolio->id }}-{{ $img->id }}" onclick="return confirm('Hapus foto?')" class="admin-button admin-button--danger admin-image-delete">
                                    Hapus Foto
                                </button>
                            </div>
                        @endforeach
                        <div class="admin-actions admin-field--wide">
                            <button class="admin-button" type="submit">Simpan urutan foto</button>
                        </div>
                    </form>
                    @foreach($portfolio->images as $img)
                        <form id="delete-image-{{ $portfolio->id }}-{{ $img->id }}" method="post" action="/admin/portfolio/{{ $portfolio->id }}/images/{{ $img->id }}">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endforeach
                    </details>
                @endif

                <details class="admin-section">
                <summary class="admin-section-summary">Publikasi dan hapus</summary>
                <div class="admin-actions admin-card-actions">
                    <form method="post" action="/admin/portfolio/{{ $portfolio->id }}/toggle">
                        @csrf
                        @method('PATCH')
                        <button class="admin-button admin-button--secondary" type="submit">{{ $portfolio->is_published ? 'Jadikan draft' : 'Publish' }}</button>
                    </form>
                    <form method="post" action="/admin/portfolio/{{ $portfolio->id }}" onsubmit="return confirm('Hapus portfolio ini?')">
                        @csrf
                        @method('DELETE')
                        <button class="admin-button admin-button--danger" type="submit">Hapus</button>
                    </form>
                </div>
                </details>
            </article>
        @empty
            <p>Belum ada portfolio.</p>
        @endforelse
    </div>
</section>
<script src="{{ asset('js/admin-portfolio.js') }}" defer></script>
@endsection
