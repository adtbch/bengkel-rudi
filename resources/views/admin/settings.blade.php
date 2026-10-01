@extends('layouts.admin')

@section('title', 'Site Settings')

@push('styles')
    <link rel="stylesheet" href="/css/admin/settings.css?v={{ filemtime(public_path('css/admin/settings.css')) }}">
@endpush

@section('content')
    <h1>Site Settings</h1>

    @if(session('status'))
        <div class="admin-setting-alert admin-setting-alert--success">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="admin-setting-alert admin-setting-alert--error" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="post" action="/admin/settings" class="admin-setting-form">
        @csrf
        @method('PUT')

        <label>
            Nama Bengkel
            <input name="business_name" value="{{ old('business_name', $settings['business_name'] ?? '') }}" required>
        </label>

        <label>
            Nomor WhatsApp (Contoh: 08123456789)
            <input name="whatsapp_number" value="{{ old('whatsapp_number', $settings['whatsapp_number'] ?? '') }}" required>
        </label>

        <label>
            Alamat Lengkap
            <textarea name="address" required rows="3">{{ old('address', $settings['address'] ?? '') }}</textarea>
        </label>

        <label>
            Jam Operasional
            <input name="opening_hours" value="{{ old('opening_hours', $settings['opening_hours'] ?? '') }}" required>
        </label>

        <label>
            Google Maps Link URL
            <input name="google_maps_url" value="{{ old('google_maps_url', $settings['google_maps_url'] ?? '') }}">
        </label>

        <label>
            Google Maps Embed URL (Hanya URL embed, bukan tag iframe)
            <input name="google_maps_embed_url" value="{{ old('google_maps_embed_url', $settings['google_maps_embed_url'] ?? '') }}">
        </label>

        <label>
            Instagram URL
            <input name="instagram_url" value="{{ old('instagram_url', $settings['instagram_url'] ?? '') }}">
        </label>

        <label>
            Google Business / Review URL
            <input name="google_business_url" value="{{ old('google_business_url', $settings['google_business_url'] ?? '') }}">
        </label>

        <label>
            Meta Title
            <input name="meta_title" value="{{ old('meta_title', $settings['meta_title'] ?? '') }}">
        </label>

        <label>
            Meta Description
            <textarea name="meta_description" rows="2">{{ old('meta_description', $settings['meta_description'] ?? '') }}</textarea>
        </label>

        <label>
            Tentang Bengkel
            <textarea name="about_content" required rows="5">{{ old('about_content', $settings['about_content'] ?? '') }}</textarea>
        </label>

        <button type="submit" class="admin-setting-submit">Simpan Perubahan</button>
    </form>
@endsection