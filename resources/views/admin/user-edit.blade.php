@extends('layouts.admin')
@section('title', 'Kelola '.$user->name)

@section('content')
<a class="admin-back-link" href="/admin/users">‹ Kembali ke daftar</a>
<header class="admin-page-head admin-page-head--edit">
    <div><p class="admin-page-head__eyebrow">Kelola user</p><h1>{{ $user->name }}</h1><p>{{ $user->email }} / {{ $user->role === 'SUPER_ADMIN' ? 'Super Admin' : 'Admin' }}</p></div>
    <span class="admin-status{{ $user->is_active ? '' : ' admin-status--draft' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span>
</header>

@if($errors->any())<div class="admin-alert" role="alert">{{ $errors->first() }}</div>@endif

<form method="post" action="/admin/users/{{ $user->id }}" class="admin-portfolio-workspace">
    @csrf
    @method('PUT')
    <section class="admin-workspace-section" aria-labelledby="detail-user">
        <div class="admin-workspace-section__head"><span>01</span><div><h2 id="detail-user">Detail dan akses</h2><p>Identitas, peran, status aktif, dan password akun.</p></div></div>
        <div class="admin-form-grid">
            <label class="admin-field" for="user-name">Nama
                <input id="user-name" name="name" value="{{ old('name', $user->name) }}" required maxlength="255">
            </label>
            <label class="admin-field" for="user-email">Email
                <input id="user-email" name="email" type="email" value="{{ old('email', $user->email) }}" required maxlength="255" autocomplete="email">
            </label>
            <label class="admin-field" for="user-role">Peran
                <select id="user-role" name="role" required><option value="ADMIN" @selected(old('role', $user->role) === 'ADMIN')>Admin</option><option value="SUPER_ADMIN" @selected(old('role', $user->role) === 'SUPER_ADMIN')>Super Admin</option></select>
            </label>
            <label class="admin-check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active))> Akun aktif</label>
            <label class="admin-field" for="user-password">Password baru
                <input id="user-password" name="password" type="password" minlength="12" autocomplete="new-password">
                <span class="admin-field-help">Kosongkan jika password tidak berubah.</span>
            </label>
            <label class="admin-field" for="user-password-confirmation">Ulangi password baru
                <input id="user-password-confirmation" name="password_confirmation" type="password" minlength="12" autocomplete="new-password">
            </label>
        </div>
    </section>
    <div class="admin-save-bar"><p>Simpan identitas dan hak akses user sekaligus.</p><button class="admin-button" type="submit">Simpan semua perubahan</button></div>
</form>
@endsection
