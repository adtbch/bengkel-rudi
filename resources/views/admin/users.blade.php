@extends('layouts.admin')
@section('title', 'Users')

@push('styles')
    <link rel="stylesheet" href="/css/admin/users.css?v={{ filemtime(public_path('css/admin/users.css')) }}">
@endpush

@section('content')
<header class="admin-page-head">
    <div>
        <p class="admin-page-head__eyebrow">Akses admin</p>
        <h1>Users</h1>
        <p>Kelola akun, peran, dan status akses administrator.</p>
    </div>
    <span class="admin-page-head__mark" aria-hidden="true">U</span>
</header>

@if($errors->any())<div class="admin-alert" role="alert">{{ $errors->first() }}</div>@endif
@if(session('status'))<div class="admin-alert admin-alert--success" role="status">{{ session('status') }}</div>@endif

<details class="admin-panel admin-create-panel" @if($errors->any()) open @endif>
    <summary class="admin-create-panel__summary">Tambah user baru</summary>
    <form method="post" action="/admin/users" class="admin-form-grid admin-create-panel__form">
        @csrf
        <label class="admin-field" for="user-name">Nama
            <input id="user-name" name="name" value="{{ old('name') }}" required maxlength="255">
        </label>
        <label class="admin-field" for="user-email">Email
            <input id="user-email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email">
        </label>
        <label class="admin-field" for="user-password">Password
            <input id="user-password" name="password" type="password" required minlength="12" autocomplete="new-password">
            <span class="admin-field-help">Minimum 12 karakter.</span>
        </label>
        <label class="admin-field" for="user-password-confirmation">Ulangi password
            <input id="user-password-confirmation" name="password_confirmation" type="password" required minlength="12" autocomplete="new-password">
        </label>
        <label class="admin-field" for="user-role">Peran
            <select id="user-role" name="role" required><option value="ADMIN" @selected(old('role') === 'ADMIN')>Admin</option><option value="SUPER_ADMIN" @selected(old('role') === 'SUPER_ADMIN')>Super Admin</option></select>
        </label>
        <label class="admin-check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))> Akun aktif</label>
        <div class="admin-actions"><button class="admin-button" type="submit">Tambah user</button></div>
    </form>
</details>

<section aria-labelledby="daftar-user">
    <div class="admin-section-head"><div><p class="admin-page-head__eyebrow">Akun terdaftar</p><h2 id="daftar-user">Daftar user</h2></div><p>{{ $users->total() }} akun</p></div>
    <div class="admin-user-list">
        @forelse($users as $user)
            <article class="admin-user-row"><a class="admin-user-row__link" href="/admin/users/{{ $user->id }}" aria-label="Kelola {{ $user->name }}">
                <span class="admin-user-row__initial" aria-hidden="true">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                <span class="admin-user-row__body"><span class="admin-user-row__heading"><strong>{{ $user->name }}</strong><span class="admin-status{{ $user->is_active ? '' : ' admin-status--draft' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span></span><span class="admin-user-row__meta">{{ $user->email }} / {{ $user->role === 'SUPER_ADMIN' ? 'Super Admin' : 'Admin' }}</span></span>
                <span class="admin-user-row__action">Kelola <span aria-hidden="true">›</span></span>
            </a></article>
        @empty
            <div class="admin-empty"><h3>Belum ada user</h3><p>Tambahkan akun administrator pertama.</p></div>
        @endforelse
    </div>
    {{ $users->links() }}
</section>
@endsection
