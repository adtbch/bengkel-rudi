<?php

namespace Tests\Feature;

use Tests\TestCase;

class InitialAdminMigrationTest extends TestCase
{
    public function test_initial_admin_migration_uses_environment_password_and_hashes_it(): void
    {
        $files = glob(database_path('migrations/*_create_initial_admin_user.php'));

        $this->assertCount(1, $files);
        $source = file_get_contents($files[0]);

        $this->assertStringContainsString("'name' => 'admin'", $source);
        $this->assertStringContainsString("private const EMAIL = 'adit.bachtiar091@gmail.com'", $source);
        $this->assertStringContainsString("'role' => 'SUPER_ADMIN'", $source);
        $this->assertStringContainsString("'is_active' => true", $source);
        $this->assertStringContainsString("env('ADMIN_INITIAL_PASSWORD')", $source);
        $this->assertStringContainsString('Hash::make($password)', $source);
        $this->assertStringNotContainsString('admin123#', $source);
    }
}
