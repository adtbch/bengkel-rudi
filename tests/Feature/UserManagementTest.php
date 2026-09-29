<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use DatabaseMigrations;

    private function as(User $user): self
    {
        return $this->withCookie('admin_token', auth('admin')->login($user));
    }

    private function user(array $attributes = []): User
    {
        return User::create(array_merge([
            'name' => 'Admin Utama',
            'email' => uniqid().'@example.test',
            'password' => Hash::make('Secret123!'),
            'role' => 'SUPER_ADMIN',
            'is_active' => true,
        ], $attributes));
    }

    public function test_superadmin_gets_scannable_user_index_and_dedicated_management_page(): void
    {
        $superadmin = $this->user();
        $admin = $this->user(['name' => 'Admin Bengkel', 'role' => 'ADMIN']);

        $this->as($superadmin)->get('/admin/users')
            ->assertOk()
            ->assertSee('admin-user-list', false)
            ->assertSee('Admin Bengkel')
            ->assertSee("href=\"/admin/users/{$admin->id}\"", false)
            ->assertDontSee('name="_method" value="PUT"', false);

        $this->as($superadmin)->get("/admin/users/{$admin->id}")
            ->assertOk()
            ->assertSee('Kembali ke daftar')
            ->assertSee('Simpan semua perubahan')
            ->assertSee('name="role"', false)
            ->assertSee('name="is_active"', false)
            ->assertSee('name="password"', false);
    }

    public function test_superadmin_can_create_and_update_user_with_validated_role_active_state_and_optional_password(): void
    {
        $superadmin = $this->user();

        $this->as($superadmin)->post('/admin/users', [
            'name' => 'Operator Baru',
            'email' => 'operator@example.test',
            'password' => 'PasswordBaru123!',
            'password_confirmation' => 'PasswordBaru123!',
            'role' => 'ADMIN',
            'is_active' => '1',
        ])->assertRedirect('/admin/users');

        $admin = User::where('email', 'operator@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('PasswordBaru123!', $admin->password));

        $this->as($superadmin)->put("/admin/users/{$admin->id}", [
            'name' => 'Operator Nonaktif',
            'email' => 'operator@example.test',
            'role' => 'ADMIN',
        ])->assertRedirect('/admin/users');

        $this->assertSame('Operator Nonaktif', $admin->fresh()->name);
        $this->assertFalse($admin->fresh()->is_active);
        $this->assertTrue(Hash::check('PasswordBaru123!', $admin->fresh()->password));
    }

    public function test_user_management_rejects_invalid_data_and_prevents_self_lockout(): void
    {
        $superadmin = $this->user();

        $this->as($superadmin)->post('/admin/users', [
            'name' => '',
            'email' => 'not-email',
            'password' => 'short',
            'role' => 'VISITOR',
        ])->assertSessionHasErrors(['name', 'email', 'password', 'role']);

        $this->as($superadmin)->put("/admin/users/{$superadmin->id}", [
            'name' => $superadmin->name,
            'email' => $superadmin->email,
            'role' => 'ADMIN',
        ])->assertSessionHasErrors('user');

        $this->assertSame('SUPER_ADMIN', $superadmin->fresh()->role);
        $this->assertTrue($superadmin->fresh()->is_active);
    }

    public function test_regular_admin_cannot_access_or_mutate_users(): void
    {
        $admin = $this->user(['role' => 'ADMIN']);
        $target = $this->user(['email' => 'target@example.test']);

        $this->as($admin)->get('/admin/users')->assertForbidden();
        $this->as($admin)->get("/admin/users/{$target->id}")->assertForbidden();
        $this->as($admin)->post('/admin/users', [])->assertForbidden();
        $this->as($admin)->put("/admin/users/{$target->id}", [])->assertForbidden();
    }
}
