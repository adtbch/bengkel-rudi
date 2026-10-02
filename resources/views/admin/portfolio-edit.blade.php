@extends('layouts.admin')
@section('title', 'Kelola '.$portfolio->title)

@push('styles')
    <link rel="stylesheet" href="/css/admin/portfolios.css?v={{ filemtime(public_path('css/admin/portfolios.css')) }}">
@endpush

@section('content')
<a class="admin-back-link" href="/admin/portfolio">‹ Kembali ke daftar</a>
<header class="admin-page-head admin-page-head--edit">
    <div>
        <p class="admin-page-head__eyebrow">Kelola portfolio</p>
        <h1>{{ $portfolio->title }}</h1>
        <p>{{ $portfolio->vehicle_label }} / {{ $portfolio->service->name }} / {{ $portfolio->images->count() }} foto</p>
    </div>
    <span class="admin-status{{ $portfolio->is_published ? '' : ' admin-status--draft' }}">{{ $portfolio->is_published ? 'Terbit' : 'Draf' }}</span>
</header>

@if($errors->any())
    <div class="admin-alert" role="alert">{{ $errors->first() }}</div>
@endif
@if(session('status'))
    <div class="admin-alert admin-alert--success" role="status">{{ session('status') }}</div>
@endif

<form method="post" action="/admin/portfolio/{{ $portfolio->id }}/save" class="admin-portfolio-workspace" data-portfolio-form>
    @csrf
    <section class="admin-workspace-section" aria-labelledby="detail-portfolio">
        <div class="admin-workspace-section__head">
            <span>01</span>
            <div><h2 id="detail-portfolio">Detail portfolio</h2><p>Informasi yang tampil pada website.</p></div>
        </div>
        <div class="admin-form-grid">
            <label class="admin-field admin-field--wide">Judul
                <input name="title" value="{{ old('title', $portfolio->title) }}" required maxlength="160">
            </label>
            <label class="admin-field">Layanan
                <select name="service_id" required>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" @selected(old('service_id', $portfolio->service_id) == $service->id)>{{ $service->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="admin-field">Kendaraan
                <select name="vehicle_type" required>
                    <option value="CAR" @selected(old('vehicle_type', $portfolio->vehicle_type) === 'CAR')>Mobil</option>
                    <option value="MOTOR" @selected(old('vehicle_type', $portfolio->vehicle_type) === 'MOTOR')>Motor</option>
                </select>
            </label>
            <label class="admin-check admin-field--wide"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $portfolio->is_published))> Tampilkan portfolio di website</label>
        </div>
    </section>

    <section class="admin-workspace-section" aria-labelledby="tambah-foto">
        <div class="admin-workspace-section__head">
            <span>02</span>
            <div><h2 id="tambah-foto">Tambah media</h2><p>Opsional. Lewati jika tidak menambah foto atau video.</p></div>
        </div>
        <div class="admin-form-grid">
            <label class="admin-field admin-field--wide">Pilih foto atau video
                <input type="file" accept="image/jpeg,image/png,image/webp,video/mp4,video/webm,video/quicktime" multiple
                       data-image-input
                       data-media-input
                       data-sign-url="/admin/portfolio/{{ $portfolio->id }}/uploads/sign"
                       data-discard-url-base="/admin/portfolio/{{ $portfolio->id }}/uploads">
                <span class="admin-field-help">JPG, PNG, WebP maks. 10 MB; MP4, WebM, MOV maks. 50 MB. File dikirim langsung ke penyimpanan, tidak melalui server.</span>
            </label>
            <div class="admin-image-preview admin-field--wide" data-image-preview aria-live="polite"></div>
            <div class="admin-field--wide" data-upload-list aria-live="polite"></div>
            <p class="admin-field-help admin-field--wide" data-upload-summary role="status"></p>
            <label class="admin-field">Tahap foto
                <select name="stage">
                    <option value="BEFORE" @selected(old('stage') === 'BEFORE')>Sebelum perbaikan</option>
                    <option value="PROCESS" @selected(old('stage') === 'PROCESS')>Proses perbaikan</option>
                    <option value="AFTER" @selected(old('stage') === 'AFTER')>Hasil akhir</option>
                </select>
            </label>
            <label class="admin-field">Urutan awal
                <input type="number" name="sort_order" value="{{ old('sort_order', max(1, ((int) $portfolio->images->max('sort_order')) + 1)) }}" min="1" step="1">
            </label>
        </div>
    </section>

    <section class="admin-workspace-section" aria-labelledby="kelola-foto">
        <div class="admin-workspace-section__head">
            <span>03</span>
            <div><h2 id="kelola-foto">Kelola foto</h2><p>Setiap foto wajib punya nomor urut berbeda.</p></div>
        </div>
        @if($portfolio->images->count())
            <div class="admin-photo-grid">
                @foreach($portfolio->images as $img)
                    <article class="admin-photo-card">
                        @if($img->media_type === 'video')
                            <video src="{{ $img->image_url }}" controls preload="metadata" playsinline aria-label="Video {{ strtolower($img->stage) }} {{ $portfolio->title }}"></video>
                        @else
                            <img src="{{ $img->image_url }}" alt="Foto {{ strtolower($img->stage) }} {{ $portfolio->title }}" loading="lazy" decoding="async">
                        @endif
                        <div class="admin-photo-card__body">
                            <span class="pill-badge @if($img->stage === 'BEFORE') badge-stage-before @elseif($img->stage === 'PROCESS') badge-stage-process @else badge-stage-after @endif">{{ ['BEFORE' => 'Sebelum', 'PROCESS' => 'Proses', 'AFTER' => 'Hasil'][$img->stage] }}</span>
                            <label class="admin-field admin-image-order__field">Urutan
                                <input type="number" name="order[{{ $img->id }}]" value="{{ old('order.'.$img->id, max(1, $img->sort_order)) }}" min="1" step="1">
                            </label>
                            <button type="submit" form="delete-image-{{ $portfolio->id }}-{{ $img->id }}" onclick="return confirm('Hapus foto ini?')" class="admin-button admin-button--danger-quiet">Hapus foto</button>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="admin-empty"><h3>Belum ada foto</h3><p>Gunakan bagian Tambah foto di atas.</p></div>
        @endif
    </section>

    <div class="admin-save-bar">
        <p>Simpan detail, publikasi, foto baru, dan seluruh urutan sekaligus.</p>
        <button class="admin-button" type="submit">Simpan semua perubahan</button>
    </div>
</form>

@foreach($portfolio->images as $img)
    <form id="delete-image-{{ $portfolio->id }}-{{ $img->id }}" method="post" action="/admin/portfolio/{{ $portfolio->id }}/images/{{ $img->id }}">
        @csrf
        @method('DELETE')
    </form>
@endforeach

<section class="admin-danger-zone" aria-labelledby="hapus-portfolio">
    <div><h2 id="hapus-portfolio">Hapus portfolio</h2><p>Portfolio hanya bisa dihapus setelah semua fotonya dihapus.</p></div>
    <form method="post" action="/admin/portfolio/{{ $portfolio->id }}" onsubmit="return confirm('Hapus portfolio ini?')">
        @csrf
        @method('DELETE')
        <button class="admin-button admin-button--danger-quiet" type="submit">Hapus portfolio</button>
    </form>
</section>
<script src="{{ asset('js/admin-portfolio.js') }}" defer></script>
@endsection
