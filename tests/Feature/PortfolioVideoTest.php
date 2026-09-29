<?php

namespace Tests\Feature;

use App\Models\{Portfolio, Service, User};
use App\Services\CloudinaryService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PortfolioVideoTest extends TestCase
{
    use DatabaseMigrations;

    private function portfolio(): Portfolio
    {
        $service = Service::create(['name' => 'Video', 'slug' => 'video', 'description' => 'Test', 'features' => []]);

        return Portfolio::create(['service_id' => $service->id, 'title' => 'Video Portfolio', 'slug' => 'video-portfolio', 'vehicle_type' => 'CAR', 'is_published' => true]);
    }

    private function adminClient()
    {
        $admin = User::factory()->create(['role' => 'ADMIN', 'is_active' => true]);

        return $this->withCookie('admin_token', auth('admin')->login($admin));
    }

    public function test_admin_uploads_video_with_cloudinary_resource_type_and_metadata(): void
    {
        $mock = $this->createMock(CloudinaryService::class);
        $mock->expects($this->once())->method('upload')
            ->with($this->isInstanceOf(UploadedFile::class), 'BengkelRudi/portfolio', 'video')
            ->willReturn(['secure_url' => 'https://example.test/demo.mp4', 'public_id' => 'BengkelRudi/portfolio/demo', 'resource_type' => 'video']);
        $this->app->instance(CloudinaryService::class, $mock);
        $portfolio = $this->portfolio();

        $this->adminClient()->post("/admin/portfolio/{$portfolio->id}/images", [
            'images' => [UploadedFile::fake()->create('demo.mp4', 2048, 'video/mp4')],
            'stage' => 'PROCESS',
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertDatabaseHas('portfolio_images', [
            'portfolio_id' => $portfolio->id,
            'image_url' => 'https://example.test/demo.mp4',
            'cloudinary_public_id' => 'BengkelRudi/portfolio/demo',
            'media_type' => 'video',
        ]);
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
            ->assertSee('preload="metadata"', false)->assertSee('playsinline', false)
            ->assertSee('aria-label="Video Video Portfolio — Hasil akhir"', false)
            ->assertSee('https://example.test/demo.mp4', false);

        $this->adminClient()->get("/admin/portfolio/{$portfolio->id}")->assertOk()
            ->assertSee('<video', false)->assertSee('preload="metadata"', false)
            ->assertSee('Video after Video Portfolio', false);
    }

    public function test_deleting_video_uses_video_resource_type(): void
    {
        $mock = $this->createMock(CloudinaryService::class);
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

        $this->adminClient()->delete("/admin/portfolio/{$portfolio->id}/images/{$video->id}")->assertRedirect();
        $this->assertDatabaseMissing('portfolio_images', ['id' => $video->id]);
    }

    public function test_invalid_video_and_oversized_media_are_rejected(): void
    {
        $portfolio = $this->portfolio();
        $client = $this->adminClient();

        $client->post("/admin/portfolio/{$portfolio->id}/images", [
            'images' => [UploadedFile::fake()->create('unsafe.avi', 100, 'video/x-msvideo')],
            'stage' => 'BEFORE',
        ])->assertSessionHasErrors('images.0');

        $client->post("/admin/portfolio/{$portfolio->id}/images", [
            'images' => [UploadedFile::fake()->create('huge.mp4', 51201, 'video/mp4')],
            'stage' => 'BEFORE',
        ])->assertSessionHasErrors('images.0');
    }

    public function test_mixed_upload_failure_cleans_assets_with_matching_resource_types(): void
    {
        $mock = $this->createMock(CloudinaryService::class);
        $mock->expects($this->exactly(2))->method('upload')->willReturnCallback(function (UploadedFile $file, string $folder, string $resourceType) {
            if ($resourceType === 'video') {
                throw new \RuntimeException('failed');
            }
            return ['secure_url' => 'https://example.test/first.jpg', 'public_id' => 'BengkelRudi/portfolio/first', 'resource_type' => 'image'];
        });
        $mock->expects($this->once())->method('delete')->with('BengkelRudi/portfolio/first', 'image')->willReturn(true);
        $this->app->instance(CloudinaryService::class, $mock);
        $portfolio = $this->portfolio();

        $this->adminClient()->post("/admin/portfolio/{$portfolio->id}/images", [
            'images' => [UploadedFile::fake()->image('first.jpg'), UploadedFile::fake()->create('second.mp4', 100, 'video/mp4')],
            'stage' => 'PROCESS',
        ])->assertSessionHasErrors('integration');

        $this->assertDatabaseCount('portfolio_images', 0);
    }
}
