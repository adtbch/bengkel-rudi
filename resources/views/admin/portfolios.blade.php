@extends('layouts.admin')
@section('title', 'Portfolio')

@section('content')
<header class="admin-page-head">
    <div>
        <p class="admin-page-head__eyebrow">Katalog pengerjaan</p>
        <h1>Portfolio</h1>
        <p>Pilih portfolio untuk mengubah detail, foto, urutan, dan status publikasi.</p>
    </div>
    <span class="admin-page-head__mark" aria-hidden="true">P</span>
</header>

@if($errors->any())
    <div class="admin-alert" role="alert">{{ $errors->first() }}</div>
@endif
@if(session('status'))
    <div class="admin-alert admin-alert--success" role="status">{{ session('status') }}</div>
@endif

<details class="admin-panel admin-create-panel" @if($errors->any()) open @endif>
    <summary class="admin-create-panel__summary">Tambah portfolio baru</summary>
    <form method="post" action="/admin/portfolio" class="admin-form-grid admin-create-panel__form">
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
        <label class="admin-check"><input type="checkbox" name="is_published" value="1" @checked(old('is_published'))> Langsung terbitkan</label>
        <div class="admin-actions"><button class="admin-button" type="submit">Tambah portfolio</button></div>
    </form>
</details>

<section aria-labelledby="daftar-portfolio">
    <div class="admin-section-head">
        <div>
            <p class="admin-page-head__eyebrow">Arsip visual</p>
            <h2 id="daftar-portfolio">Daftar portfolio</h2>
        </div>
        <p>{{ $portfolios->count() }} item</p>
    </div>
    <div class="admin-card-grid admin-portfolio-list">
        @forelse($portfolios as $portfolio)
            @php($cover = $portfolio->images->first())
            <article class="admin-portfolio-row">
                <a class="admin-portfolio-row__link" href="/admin/portfolio/{{ $portfolio->id }}" aria-label="Kelola {{ $portfolio->title }}">
                    <span class="admin-portfolio-row__cover">
                        @if($cover)
                            <img src="{{ $cover->image_url }}" alt="Foto utama {{ $portfolio->title }}" loading="lazy" decoding="async">
                        @else
                            <span>Belum ada foto</span>
                        @endif
                    </span>
                    <span class="admin-portfolio-row__body">
                        <span class="admin-portfolio-row__heading">
                            <strong>{{ $portfolio->title }}</strong>
                            <span class="admin-status{{ $portfolio->is_published ? '' : ' admin-status--draft' }}">{{ $portfolio->is_published ? 'Terbit' : 'Draf' }}</span>
                        </span>
                        <span class="admin-portfolio-row__meta">{{ $portfolio->vehicle_label }} / {{ $portfolio->service->name }} / {{ $portfolio->images->count() }} foto</span>
                    </span>
                    <span class="admin-portfolio-row__action">Kelola <span aria-hidden="true">›</span></span>
                </a>
            </article>
        @empty
            <div class="admin-empty">
                <h3>Belum ada portfolio</h3>
                <p>Tambahkan pekerjaan pertama lewat tombol di atas.</p>
            </div>
        @endforelse
    </div>
</section>
@endsection
