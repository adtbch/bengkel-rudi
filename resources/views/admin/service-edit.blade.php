@extends('layouts.admin')
@section('title', 'Kelola '.$service->name)

@push('styles')
    <link rel="stylesheet" href="/css/admin/services.css?v={{ filemtime(public_path('css/admin/services.css')) }}">
@endpush

@section('content')
<a class="admin-back-link" href="/admin/layanan">‹ Kembali ke daftar</a>
<header class="admin-page-head admin-page-head--edit">
    <div>
        <p class="admin-page-head__eyebrow">Kelola layanan</p>
        <h1>{{ $service->name }}</h1>
        <p>Urutan {{ $service->sort_order }} / {{ $service->min_price !== null ? 'Mulai Rp'.number_format($service->min_price, 0, ',', '.') : 'Harga konsultasi' }}</p>
    </div>
    <span class="admin-status{{ $service->is_active ? '' : ' admin-status--draft' }}">{{ $service->is_active ? 'Aktif' : 'Nonaktif' }}</span>
</header>

@if($errors->any())
    <div class="admin-alert" role="alert">{{ $errors->first() }}</div>
@endif

<form method="post" action="/admin/layanan/{{ $service->id }}" class="admin-portfolio-workspace">
    @csrf
    @method('PUT')
    <section class="admin-workspace-section" aria-labelledby="detail-layanan">
        <div class="admin-workspace-section__head">
            <span>01</span>
            <div><h2 id="detail-layanan">Detail layanan</h2><p>Informasi, harga, status, dan urutan yang tampil pada website.</p></div>
        </div>
        <div class="admin-form-grid">
            <label class="admin-field admin-field--wide" for="service-name">Nama layanan
                <input id="service-name" name="name" value="{{ old('name', $service->name) }}" required maxlength="120" @error('name') aria-invalid="true" aria-describedby="service-name-error" @enderror>
                @error('name')<span id="service-name-error" class="admin-field-error" role="alert">{{ $message }}</span>@enderror
            </label>
            <label class="admin-field admin-field--wide" for="service-description">Detail layanan
                <textarea id="service-description" name="description" required maxlength="5000" @error('description') aria-invalid="true" aria-describedby="service-description-error" @enderror>{{ old('description', $service->description) }}</textarea>
                @error('description')<span id="service-description-error" class="admin-field-error" role="alert">{{ $message }}</span>@enderror
            </label>
            <label class="admin-field" for="service-min-price">Harga minimum
                <input id="service-min-price" name="min_price" type="number" value="{{ old('min_price', $service->min_price) }}" min="0" step="1" inputmode="numeric">
            </label>
            <label class="admin-field" for="service-max-price">Harga maksimum
                <input id="service-max-price" name="max_price" type="number" value="{{ old('max_price', $service->max_price) }}" min="0" step="1" inputmode="numeric" @error('max_price') aria-invalid="true" aria-describedby="service-max-price-error" @enderror>
                @error('max_price')<span id="service-max-price-error" class="admin-field-error" role="alert">{{ $message }}</span>@enderror
            </label>
            <label class="admin-field" for="service-sort-order">Urutan tampil
                <input id="service-sort-order" name="sort_order" type="number" value="{{ old('sort_order', $service->sort_order) }}" min="1" step="1" required>
            </label>
            <label class="admin-check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $service->is_active))> Tampilkan di website</label>
        </div>
    </section>
    <div class="admin-save-bar">
        <p>Simpan seluruh perubahan layanan sekaligus.</p>
        <button class="admin-button" type="submit">Simpan semua perubahan</button>
    </div>
</form>

<section class="admin-danger-zone" aria-labelledby="hapus-layanan">
    <div><h2 id="hapus-layanan">Hapus layanan</h2><p>Layanan yang masih dipakai portfolio tidak dapat dihapus.</p></div>
    <form method="post" action="/admin/layanan/{{ $service->id }}" onsubmit="return confirm('Hapus layanan ini?')">
        @csrf
        @method('DELETE')
        <button class="admin-button admin-button--danger-quiet" type="submit">Hapus layanan</button>
    </form>
</section>
@endsection
