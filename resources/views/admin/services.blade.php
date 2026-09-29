@extends('layouts.admin')
@section('title', 'Layanan')

@section('content')
<header class="admin-page-head">
    <div>
        <p class="admin-page-head__eyebrow">Katalog layanan</p>
        <h1>Layanan</h1>
        <p>Pilih layanan untuk mengubah detail, harga, status, dan urutan tampil.</p>
    </div>
    <span class="admin-page-head__mark" aria-hidden="true">L</span>
</header>

@if($errors->any())
    <div class="admin-alert" role="alert">{{ $errors->first() }}</div>
@endif
@if(session('status'))
    <div class="admin-alert admin-alert--success" role="status">{{ session('status') }}</div>
@endif

<details class="admin-panel admin-create-panel" @if($errors->any()) open @endif>
    <summary class="admin-create-panel__summary">Tambah layanan baru</summary>
    <form method="post" action="/admin/layanan" class="admin-form-grid admin-create-panel__form">
        @csrf
        <label class="admin-field" for="service-name">Nama layanan
            <input id="service-name" name="name" value="{{ old('name') }}" required maxlength="120" @error('name') aria-invalid="true" aria-describedby="service-name-error" @enderror>
            @error('name')<span id="service-name-error" class="admin-field-error" role="alert">{{ $message }}</span>@enderror
        </label>
        <label class="admin-field" for="service-sort-order">Urutan tampil
            <input id="service-sort-order" name="sort_order" type="number" value="{{ old('sort_order', 1) }}" min="1" step="1" required @error('sort_order') aria-invalid="true" aria-describedby="service-sort-order-error" @enderror>
            @error('sort_order')<span id="service-sort-order-error" class="admin-field-error" role="alert">{{ $message }}</span>@enderror
        </label>
        <label class="admin-field admin-field--wide" for="service-description">Detail layanan
            <textarea id="service-description" name="description" required maxlength="5000" @error('description') aria-invalid="true" aria-describedby="service-description-error" @enderror>{{ old('description') }}</textarea>
            @error('description')<span id="service-description-error" class="admin-field-error" role="alert">{{ $message }}</span>@enderror
        </label>
        <label class="admin-field" for="service-min-price">Harga minimum
            <input id="service-min-price" name="min_price" type="number" value="{{ old('min_price') }}" min="0" step="1" inputmode="numeric">
        </label>
        <label class="admin-field" for="service-max-price">Harga maksimum
            <input id="service-max-price" name="max_price" type="number" value="{{ old('max_price') }}" min="0" step="1" inputmode="numeric">
        </label>
        <label class="admin-check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))> Tampilkan di website</label>
        <div class="admin-actions"><button class="admin-button" type="submit">Tambah layanan</button></div>
    </form>
</details>

<section aria-labelledby="daftar-layanan">
    <div class="admin-section-head">
        <div><p class="admin-page-head__eyebrow">Layanan tersedia</p><h2 id="daftar-layanan">Daftar layanan</h2></div>
        <p>{{ $services->count() }} item</p>
    </div>
    <div class="admin-service-list">
        @forelse($services as $service)
            <article class="admin-service-row">
                <a class="admin-service-row__link" href="/admin/layanan/{{ $service->id }}" aria-label="Kelola {{ $service->name }}">
                    <span class="admin-service-row__order" aria-label="Urutan {{ $service->sort_order }}">{{ $service->sort_order }}</span>
                    <span class="admin-service-row__body">
                        <span class="admin-service-row__heading"><strong>{{ $service->name }}</strong><span class="admin-status{{ $service->is_active ? '' : ' admin-status--draft' }}">{{ $service->is_active ? 'Aktif' : 'Nonaktif' }}</span></span>
                        <span class="admin-service-row__meta">{{ $service->min_price !== null ? 'Mulai Rp'.number_format($service->min_price, 0, ',', '.') : 'Harga konsultasi' }}{{ $service->max_price !== null ? ' - Rp'.number_format($service->max_price, 0, ',', '.') : '' }}</span>
                    </span>
                    <span class="admin-service-row__action">Kelola <span aria-hidden="true">›</span></span>
                </a>
            </article>
        @empty
            <div class="admin-empty"><h3>Belum ada layanan</h3><p>Tambahkan layanan pertama lewat tombol di atas.</p></div>
        @endforelse
    </div>
</section>
@endsection
