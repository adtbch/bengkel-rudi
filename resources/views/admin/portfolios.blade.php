@extends('layouts.admin')
@section('title', 'Portfolio')

@section('content')
<header class="admin-page-head">
    <h1>Portfolio</h1>
    <p>Kelola karya, status publikasi, dan data kendaraan.</p>
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
        <label class="admin-field">Judul
            <input name="title" value="{{ old('title') }}" required maxlength="160">
        </label>
        <label class="admin-field">Layanan
            <select name="service_id" required>
                @foreach($services as $service)
                    <option value="{{ $service->id }}" @selected(old('service_id') == $service->id)>{{ $service->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="admin-field">Kendaraan
            <select name="vehicle_type" required>
                <option value="CAR" @selected(old('vehicle_type') === 'CAR')>Mobil</option>
                <option value="MOTOR" @selected(old('vehicle_type') === 'MOTOR')>Motor</option>
            </select>
        </label>
        <label class="admin-check"><input type="checkbox" name="is_published" value="1" @checked(old('is_published'))> Langsung publish</label>
        <div class="admin-actions"><button class="admin-button" type="submit">Tambah portfolio</button></div>
    </form>
</section>

<section aria-labelledby="daftar-portfolio">
    <h2 id="daftar-portfolio">Daftar portfolio</h2>
    <div class="admin-card-grid">
        @forelse($portfolios as $portfolio)
            <article class="admin-card">
                <span class="admin-status{{ $portfolio->is_published ? '' : ' admin-status--draft' }}">{{ $portfolio->is_published ? 'Published' : 'Draft' }}</span>
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
                <h3>Tambah foto</h3>
                <form method="post" action="/admin/portfolio/{{ $portfolio->id }}/images" enctype="multipart/form-data" class="admin-form-grid">
                    @csrf
                    <label class="admin-field admin-field--wide">Foto (JPG, PNG, WebP; maksimal 10 MB per foto)
                        <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple required>
                    </label>
                    <label class="admin-field">Tahap foto
                        <select name="stage" required>
                            <option value="BEFORE">Sebelum perbaikan</option>
                            <option value="PROCESS">Proses perbaikan</option>
                            <option value="AFTER">Hasil akhir</option>
                        </select>
                    </label>
                    <label class="admin-field">Urutan awal
                        <input type="number" name="sort_order" value="0" min="0" step="1">
                    </label>
                    <div class="admin-actions"><button class="admin-button" type="submit">Unggah foto</button></div>
                </form>
                <div class="admin-actions">
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
            </article>
        @empty
            <p>Belum ada portfolio.</p>
        @endforelse
    </div>
</section>
@endsection
