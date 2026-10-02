<?php

namespace Tests\Feature;

use App\Models\MediaUpload;
use App\Models\Portfolio;
use App\Models\Service;
use App\Models\User;
use App\Services\CloudinaryService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class PortfolioVideoTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cloudinary.cloud_url' => 'cloudinary://test-key:test-secret@test-cloud',
            'cloudinary.cloud_name' => 'test-cloud',
            'cloudinary.api_key' => 'test-key',
            'cloudinary.api_secret' => 'test-secret',
        ]);
    }

    private function portfolio(): Portfolio
    {
        $service = Service::create(['name' => 'Video', 'slug' => 'video', 'description' => 'Test', 'features' => []]);

        return Portfolio::create(['service_id' => $service->id, 'title' => 'Video Portfolio', 'slug' => 'video-portfolio', 'vehicle_type' => 'CAR', 'is_published' => true]);
    }

    private function adminClient()
    {
        $admin = User::factory()->create(['role' => 'ADMIN', 'is_active' => true]);

        return [$this->withCookie('admin_token', auth('admin')->login($admin)), $admin];
    }

    /**
     * Simulate the browser: sign the upload, then post only the metadata the
     * admin form carries for a file that already lives on Cloudinary.
     */
    private function signedUpload(Portfolio $portfolio, User $admin, string $resourceType, string $filename): array
    {
        $payload = $this->withCookie('admin_token', auth('admin')->login($admin))
            ->postJson("/admin/portfolio/{$portfolio->id}/uploads/sign", [
                'resource_type' => $resourceType,
                'filename' => $filename,
            ])->assertOk()->json();
        $publicId = $payload['params']['public_id'];

        return [
            'media_upload_id' => $payload['media_upload_id'],
            'public_id' => $publicId,
            'secure_url' => 'https://res.cloudinary.com/test-cloud/'.$resourceType.'/upload/v1/'.$publicId,
            'resource_type' => $resourceType,
            'token' => Crypt::encryptString(json_encode([
                'user_id' => $admin->id,
                'portfolio_id' => $portfolio->id,
                'media_upload_id' => $payload['media_upload_id'],
                'resource_type' => $resourceType,
                'public_id' => $publicId,
                'expires_at' => time() + 3600,
            ])),
        ];
    }

    public function test_admin_uploads_video_with_cloudinary_resource_type_and_metadata(): void
    {
        [$client, $admin] = $this->adminClient();
        $portfolio = $this->portfolio();
        $upload = $this->signedUpload($portfolio, $admin, 'video', 'demo.mp4');

        $client->post("/admin/portfolio/{$portfolio->id}/save", [
            'title' => $portfolio->title,
            'service_id' => $portfolio->service_id,
            'vehicle_type' => 'CAR',
            'uploads' => [$upload],
            'stage' => 'PROCESS',
        ])->assertRedirect("/admin/portfolio/{$portfolio->id}")->assertSessionHas('status');

        $this->assertDatabaseHas('portfolio_images', [
            'portfolio_id' => $portfolio->id,
            'image_url' => 'https://res.cloudinary.com/test-cloud/video/upload/v1/'.$upload['public_id'],
            'cloudinary_public_id' => $upload['public_id'],
            'media_type' => 'video',
        ]);
        $this->assertDatabaseCount('media_uploads', 0);
    }

    public function test_public_and_admin_gallery_render_playable_accessible_video(): void
    {
        $portfolio = $this->portfolio();
        $portfolio->images()->create([
            'image_url' => 'https://example.test/demo.mp4',
            'cloudinary_public_id' => 'BengkelRudi/portfolio/demo',
            'media_type' => 'video',
            'stage' => 'AFTER',
            'sort_order' => 1,
        ]);

        $this->get('/portfolio/video-portfolio')->assertOk()
            ->assertSee('<video', false)->assertSee('controls', false)
            ->assertSee('preload="metadata"', false)
            ->assertSee('playsinline', false)
            ->assertSee('aria-label="Video Video Portfolio — Hasil akhir"', false)
            ->assertSee('https://example.test/demo.mp4', false);

        [$client] = $this->adminClient();
        $client->get("/admin/portfolio/{$portfolio->id}")->assertOk()
            ->assertSee('<video', false)
            ->assertSee('preload="metadata"', false)
            ->assertSee('Video after Video Portfolio', false);
    }

    public function test_deleting_video_uses_video_resource_type(): void
    {
        $mock = $this->getMockBuilder(CloudinaryService::class)
            ->onlyMethods(['delete'])->getMock();
        $mock->expects($this->once())->method('delete')->with('BengkelRudi/portfolio/demo', 'video')->willReturn(true);
        $this->app->instance(CloudinaryService::class, $mock);
        $portfolio = $this->portfolio();
        $video = $portfolio->images()->create([
            'image_url' => 'https://example.test/demo.mp4',
            'cloudinary_public_id' => 'BengkelRudi/portfolio/demo',
            'media_type' => 'video',
            'stage' => 'AFTER',
            'sort_order' => 1,
        ]);

        [$client] = $this->adminClient();
        $client->delete("/admin/portfolio/{$portfolio->id}/images/{$video->id}")->assertRedirect();
        $this->assertDatabaseMissing('portfolio_images', ['id' => $video->id]);
    }

    /**
     * The file bytes never reach this server, so the size limit is only enforced
     * from the declared size at the sign endpoint (and client-side in JS).
     */
    public function test_sign_rejects_declared_size_beyond_the_per_resource_type_limit(): void
    {
        [$client] = $this->adminClient();
        $portfolio = $this->portfolio();
        $url = "/admin/portfolio/{$portfolio->id}/uploads/sign";

        $client->postJson($url, ['resource_type' => 'image', 'declared_size' => 10 * 1024 * 1024 + 1])
            ->assertStatus(422)->assertJsonPath('errors.declared_size.0', 'Gambar maksimal 10 MB.');
        $client->postJson($url, ['resource_type' => 'video', 'declared_size' => 50 * 1024 * 1024 + 1])
            ->assertStatus(422)->assertJsonPath('errors.declared_size.0', 'Video maksimal 50 MB.');

        $client->postJson($url, ['resource_type' => 'image', 'declared_size' => 10 * 1024 * 1024])->assertOk();
        $client->postJson($url, ['resource_type' => 'video', 'declared_size' => 50 * 1024 * 1024])->assertOk();
        $this->assertDatabaseCount('media_uploads', 2);
    }

    public function test_save_rolls_back_every_asset_when_the_database_write_fails(): void
    {
        [$client, $admin] = $this->adminClient();
        $portfolio = $this->portfolio();
        $image = $this->signedUpload($portfolio, $admin, 'image', 'first.jpg');
        $publicId = $image['public_id'];

        $mock = $this->getMockBuilder(CloudinaryService::class)
            ->onlyMethods(['delete'])->getMock();
        $mock->expects($this->once())->method('delete')->with($publicId, 'image')->willReturn(true);
        $this->app->instance(CloudinaryService::class, $mock);

        // The same signed upload twice: the second insert violates the unique
        // cloudinary_public_id index after the first row is already written.
        $client->from("/admin/portfolio/{$portfolio->id}")->post("/admin/portfolio/{$portfolio->id}/save", [
            'title' => $portfolio->title,
            'service_id' => $portfolio->service_id,
            'vehicle_type' => 'CAR',
            'uploads' => [$image, $image],
            'stage' => 'PROCESS',
        ])->assertSessionHasErrors('integration');

        $this->assertDatabaseCount('portfolio_images', 0);
        $this->assertDatabaseCount('media_uploads', 0);
    }

    public function test_pending_media_uploads_are_pruned_only_after_the_ttl(): void
    {
        $mock = $this->createMock(CloudinaryService::class);
        $mock->expects($this->once())->method('delete')->with('BengkelRudi/portfolio/stale', 'video')->willReturn(true);
        $this->app->instance(CloudinaryService::class, $mock);

        [$client, $admin] = $this->adminClient();
        $portfolio = $this->portfolio();
        $stale = MediaUpload::create([
            'portfolio_id' => $portfolio->id,
            'public_id' => 'BengkelRudi/portfolio/stale',
            'resource_type' => 'video',
            'user_id' => $admin->id,
        ]);
        $fresh = MediaUpload::create([
            'portfolio_id' => $portfolio->id,
            'public_id' => 'BengkelRudi/portfolio/fresh',
            'resource_type' => 'image',
            'user_id' => $admin->id,
        ]);
        $stale->forceFill(['created_at' => now()->subHours(30), 'updated_at' => now()->subHours(30)])->save();

        $this->artisan('media:prune-orphans', ['--hours' => 24])->assertSuccessful();

        $this->assertDatabaseMissing('media_uploads', ['id' => $stale->id]);
        $this->assertDatabaseHas('media_uploads', ['id' => $fresh->id]);
    }
}
