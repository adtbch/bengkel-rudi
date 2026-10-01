<?php

namespace Tests\Feature;

use App\Models\{Portfolio, Service, User};
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class DirectMediaUploadTest extends TestCase
{
    use DatabaseMigrations;

    private function portfolio(): Portfolio
    {
        $service = Service::create(['name' => 'Direct', 'slug' => 'direct', 'description' => 'Test', 'features' => []]);
        return Portfolio::create(['service_id' => $service->id, 'title' => 'Direct', 'slug' => 'direct', 'vehicle_type' => 'CAR']);
    }

    public function test_signature_is_authenticated_scoped_and_does_not_expose_secret(): void
    {
        config(['cloudinary.cloud_url' => 'cloudinary://test-key:test-secret@test-cloud']);
        $portfolio = $this->portfolio();
        $url = "/admin/portfolio/{$portfolio->id}/uploads/sign";
        $this->postJson($url, ['resource_type' => 'video'])->assertRedirect('/admin/login');
        $admin = User::factory()->create(['role' => 'ADMIN', 'is_active' => true]);
        $response = $this->withCookie('admin_token', auth('admin')->login($admin))->postJson($url, [
            'resource_type' => 'video', 'public_id' => 'victim', 'overwrite' => true,
        ])->assertOk()->assertJsonPath('params.overwrite', 'false');
        $data = $response->json();
        $this->assertStringNotContainsString('test-secret', $response->getContent());
        $this->assertStringStartsWith('BengkelRudi/portfolio/', $data['params']['public_id']);
        $this->assertNotSame('victim', $data['params']['public_id']);
        $claim = json_decode(Crypt::decryptString($data['token']), true);
        $this->assertSame($admin->id, $claim['user_id']);
        $this->assertSame($portfolio->id, $claim['portfolio_id']);
        $this->assertSame('video', $claim['resource_type']);
        $this->postJson($url, ['resource_type' => 'raw'])->assertUnprocessable();
    }
}
