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
                @if($portfolio->images->count())
            <form method="post" action="/admin/portfolio/{{ $portfolio->id }}/images/order" class="admin-form-grid" style="margin-top:1rem;">
                @csrf
                @foreach($portfolio->images as $img)
                    <div class="admin-image-item" style="display:flex;flex-direction:column;gap:0.5rem;">
                        <img src="{{ $img->image_url }}" alt="Foto" style="max-width:100%;border-radius:var(--radius-sm);" />
                        <span class="pill-badge @if($img->stage==='BEFORE')badge-stage-before@elseif($img->stage==='PROCESS')badge-stage-process@elsebadge-stage-after@endif">{{ $img->stage }}</span>
                        <label class="admin-field">Urutan
                            <input type="number" name="order[{{ $img->id }}]" value="{{ $img->sort_order }}" min="0" />
                        </label>
                        <form method="post" action="/admin/portfolio/{{ $portfolio->id }}/images/{{ $img->id }}" onsubmit="return confirm('Hapus foto?')">
                            @csrf
                            @method('DELETE')
                            <button class="admin-button admin-button--danger" type="submit">Hapus</button>
                        </form>
                    </div>
                @endforeach
                <button class="admin-button" type="submit" style="grid-column:1/-1;">Simpan urutan</button>
            </form>
        @endif
        @empty
            <p>Belum ada portfolio.</p>
        @endforelse
    </div>
</section>
@endsection
