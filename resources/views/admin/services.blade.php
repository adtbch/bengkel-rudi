@extends('layouts.admin')
@section('title', 'Layanan')

@section('content')
<header class="admin-page-head">
    <div>
        <p class="admin-page-head__eyebrow">Katalog layanan</p>
        <h1>Layanan</h1>
        <p>Atur detail, kisaran harga, status publikasi, dan urutan layanan.</p>
    </div>
    <span class="admin-page-head__mark" aria-hidden="true">L</span>
</header>

@if ($errors->any())
    <div class="admin-alert" role="alert">{{ $errors->first() }}</div>
@endif

<section class="admin-panel" aria-labelledby="layanan-baru">
    <h2 id="layanan-baru">Tambah layanan</h2>
    <form method="post" action="/admin/layanan" class="admin-form-grid">
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
            <input id="service-min-price" name="min_price" type="number" value="{{ old('min_price') }}" min="0" step="1" inputmode="numeric" @error('min_price') aria-invalid="true" aria-describedby="service-min-price-error" @enderror>
            @error('min_price')<span id="service-min-price-error" class="admin-field-error" role="alert">{{ $message }}</span>@enderror
        </label>
        <label class="admin-field" for="service-max-price">Harga maksimum
            <input id="service-max-price" name="max_price" type="number" value="{{ old('max_price') }}" min="0" step="1" inputmode="numeric" @error('max_price') aria-invalid="true" aria-describedby="service-max-price-error" @enderror>
            @error('max_price')<span id="service-max-price-error" class="admin-field-error" role="alert">{{ $message }}</span>@enderror
        </label>
        <label class="admin-check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))> Tampilkan di website</label>
        <div class="admin-actions"><button class="admin-button" type="submit">Tambah layanan</button></div>
    </form>
</section>

<section aria-labelledby="daftar-layanan">
    <div class="admin-section-head">
        <div>
            <p class="admin-page-head__eyebrow">Layanan tersedia</p>
            <h2 id="daftar-layanan">Daftar layanan</h2>
        </div>
        <p>{{ $services->count() }} item</p>
    </div>
    <div class="admin-card-grid">
        @forelse ($services as $service)
            <article class="admin-card">
                <span class="admin-status{{ $service->is_active ? '' : ' admin-status--draft' }}">{{ $service->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                <form method="post" action="/admin/layanan/{{ $service->id }}" class="admin-form-grid">
                    @csrf
                    @method('PUT')
                    <label class="admin-field admin-field--wide">Nama layanan
                        <input name="name" value="{{ $service->name }}" required maxlength="120">
                    </label>
                    <label class="admin-field admin-field--wide">Detail layanan
                        <textarea name="description" required maxlength="5000">{{ $service->description }}</textarea>
                    </label>
                    <label class="admin-field">Harga minimum
                        <input name="min_price" type="number" value="{{ $service->min_price }}" min="0" step="1" inputmode="numeric">
                    </label>
                    <label class="admin-field">Harga maksimum
                        <input name="max_price" type="number" value="{{ $service->max_price }}" min="0" step="1" inputmode="numeric">
                    </label>
                    <label class="admin-field">Urutan tampil
                        <input name="sort_order" type="number" value="{{ $service->sort_order }}" min="1" step="1" required>
                    </label>
                    <label class="admin-check"><input type="checkbox" name="is_active" value="1" @checked($service->is_active)> Tampilkan di website</label>
                    <div class="admin-actions"><button class="admin-button" type="submit">Simpan perubahan</button></div>
                </form>
                <p class="admin-service-price">{{ $service->min_price !== null ? 'Mulai Rp'.number_format($service->min_price, 0, ',', '.') : 'Harga konsultasi' }}{{ $service->max_price !== null ? ' - Rp'.number_format($service->max_price, 0, ',', '.') : '' }}</p>
                <div class="admin-actions admin-card-actions">
                    <form method="post" action="/admin/layanan/{{ $service->id }}/toggle">@csrf @method('PATCH')<button class="admin-button admin-button--secondary" type="submit">{{ $service->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
                    <form method="post" action="/admin/layanan/{{ $service->id }}" onsubmit="return confirm('Hapus layanan ini?')">@csrf @method('DELETE')<button class="admin-button admin-button--danger" type="submit">Hapus</button></form>
                </div>
            </article>
        @empty
            <div class="admin-panel"><p>Belum ada layanan. Tambahkan layanan pertama untuk ditampilkan di website.</p></div>
        @endforelse
    </div>
</section>
@endsection
