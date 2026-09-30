<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    private const EMAIL = 'adit.bachtiar091@gmail.com';

    public function up(): void
    {
        if (DB::table('users')->where('email', self::EMAIL)->exists()) {
            return;
        }

        $password = env('ADMIN_INITIAL_PASSWORD');

        if (!is_string($password) || $password === '') {
            throw new RuntimeException('ADMIN_INITIAL_PASSWORD wajib diisi sebelum migration dijalankan.');
        }

        DB::table('users')->insert([
            'name' => 'admin',
            'email' => self::EMAIL,
            'email_verified_at' => now(),
            'password' => Hash::make($password),
            'role' => 'SUPER_ADMIN',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('users')->where('email', self::EMAIL)->delete();
    }
};
