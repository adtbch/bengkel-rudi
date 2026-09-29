<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.users', ['users' => User::orderBy('name')->orderBy('id')->paginate(25)]);
    }

    public function show(User $user)
    {
        return view('admin.user-edit', compact('user'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:12|confirmed',
            'role' => ['required', Rule::in(['ADMIN', 'SUPER_ADMIN'])],
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        User::create($data);

        return redirect('/admin/users')->with('status', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'password' => 'nullable|string|min:12|confirmed',
            'role' => ['required', Rule::in(['ADMIN', 'SUPER_ADMIN'])],
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');

        if ($user->is(auth('admin')->user()) && ($data['role'] !== 'SUPER_ADMIN' || !$data['is_active'])) {
            return back()->withErrors(['user' => 'Akun sendiri tidak dapat dinonaktifkan atau diturunkan dari SUPER_ADMIN.'])->withInput();
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }
        $user->update($data);

        return redirect('/admin/users')->with('status', 'User berhasil diperbarui.');
    }
}
