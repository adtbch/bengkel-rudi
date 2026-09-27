@extends('layouts.admin')
@section('title', 'Site Settings')
@section('content')
<h1>Site Settings</h1>

@if(session('status'))
    <div style="background:#dcfce7;color:#166534;padding:0.75rem;border-radius:6px;margin-bottom:1rem">
        {{ session('status') }}
    </div>
@endif

@if($errors->any())
    <div style="background:#fee2e2;color:#991b1b;padding:0.75rem;border-radius:6px;margin-bottom:1rem" role="alert">
        {{ $errors->first() }}
    </div>
@endif

<form method="post" action="/admin/settings" style="display:flex;flex-direction:column;gap:1rem;max-width:600px">
    @csrf
    @method('PUT')

    <label>
        Nama Bengkel
        <input name="business_name" value="{{ old('business_name', $settings['business_name'] ?? '') }}" required style="width:100%;padding:0.5rem">
    </label>

    <label>
        Nomor WhatsApp (Contoh: 08123456789)
        <input name="whatsapp_number" value="{{ old('whatsapp_number', $settings['whatsapp_number'] ?? '') }}" required style="width:100%;padding:0.5rem">
    </label>

    <label>
        Alamat Lengkap
        <textarea name="address" required style="width:100%;padding:0.5rem" rows="3">{{ old('address', $settings['address'] ?? '') }}</textarea>
    </label>

    <label>
        Jam Operasional
        <input name="opening_hours" value="{{ old('opening_hours', $settings['opening_hours'] ?? '') }}" required style="width:100%;padding:0.5rem">
    </label>

    <label>
        Google Maps Link URL
        <input name="google_maps_url" value="{{ old('google_maps_url', $settings['google_maps_url'] ?? '') }}" style="width:100%;padding:0.5rem">
    </label>

    <label>
        Google Maps Embed URL (Hanya URL embed, bukan tag iframe)
        <input name="google_maps_embed_url" value="{{ old('google_maps_embed_url', $settings['google_maps_embed_url'] ?? '') }}" style="width:100%;padding:0.5rem">
    </label>

    <label>
        Instagram URL
        <input name="instagram_url" value="{{ old('instagram_url', $settings['instagram_url'] ?? '') }}" style="width:100%;padding:0.5rem">
    </label>

    <label>
        Google Business / Review URL
        <input name="google_business_url" value="{{ old('google_business_url', $settings['google_business_url'] ?? '') }}" style="width:100%;padding:0.5rem">
    </label>

    <label>
        Meta Title
        <input name="meta_title" value="{{ old('meta_title', $settings['meta_title'] ?? '') }}" style="width:100%;padding:0.5rem">
    </label>

    <label>
        Meta Description
        <textarea name="meta_description" style="width:100%;padding:0.5rem" rows="2">{{ old('meta_description', $settings['meta_description'] ?? '') }}</textarea>
    </label>

    <label>
        Tentang Bengkel
        <textarea name="about_content" required style="width:100%;padding:0.5rem" rows="5">{{ old('about_content', $settings['about_content'] ?? '') }}</textarea>
    </label>

    <button type="submit" style="padding:0.75rem;background:#171717;color:#fff;border:0;border-radius:9999px;cursor:pointer">Simpan Perubahan</button>
</form>
@endsection
