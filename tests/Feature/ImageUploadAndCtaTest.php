<?php
namespace Tests\Feature;

use App\Models\{Portfolio, Service, User};
use App\Services\CloudinaryService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImageUploadAndCtaTest extends TestCase
{
    use DatabaseMigrations;

    public function test_admin_can_upload_multiple_portfolio_images_with_consecutive_order(): void
    {
        $mock = $this->createMock(CloudinaryService::class);
        config(['cloudinary.folder' => 'BengkelRudi']);
        $mock->expects($this->exactly(2))->method('upload')
            ->with($this->isInstanceOf(UploadedFile::class), 'BengkelRudi/portfolio')
            ->willReturnOnConsecutiveCalls(
                ['secure_url' => 'https://example.test/first.jpg', 'public_id' => 'BengkelRudi/portfolio/first'],
                ['secure_url' => 'https://example.test/second.webp', 'public_id' => 'BengkelRudi/portfolio/second'],
            );
        $this->app->instance(CloudinaryService::class, $mock);

        $admin = User::factory()->create(['role' => 'ADMIN', 'is_active' => true]);
        $service = Service::create(['name' => 'Cat Panel', 'slug' => 'cat-panel', 'description' => 'Layanan cat', 'features' => []]);
        $portfolio = Portfolio::create(['service_id' => $service->id, 'title' => 'Jazz', 'slug' => 'jazz', 'vehicle_type' => 'CAR']);
        $client = $this->withCookie('admin_token', auth('admin')->login($admin));

        $client->post("/admin/portfolio/{$portfolio->id}/images", [
            'images' => [
                UploadedFile::fake()->image('first.jpg'),
                UploadedFile::fake()->image('second.webp'),
            ],
            'stage' => 'PROCESS',
            'sort_order' => 4,
        ])->assertRedirect()->assertSessionHas('status', 'Foto berhasil diunggah.');

        $this->assertDatabaseHas('portfolio_images', ['cloudinary_public_id' => 'BengkelRudi/portfolio/first', 'stage' => 'PROCESS', 'sort_order' => 4]);
        $this->assertDatabaseHas('portfolio_images', ['cloudinary_public_id' => 'BengkelRudi/portfolio/second', 'stage' => 'PROCESS', 'sort_order' => 5]);
    }

    public function test_portfolio_image_upload_rejects_missing_stage_and_files_over_ten_megabytes(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN', 'is_active' => true]);
        $service = Service::create(['name' => 'Body Repair', 'slug' => 'body-repair', 'description' => 'Layanan body', 'features' => []]);
        $portfolio = Portfolio::create(['service_id' => $service->id, 'title' => 'Brio', 'slug' => 'brio', 'vehicle_type' => 'CAR']);
        $client = $this->withCookie('admin_token', auth('admin')->login($admin));

        $client->post("/admin/portfolio/{$portfolio->id}/images", [
            'images' => [UploadedFile::fake()->create('oversized.jpg', 10241, 'image/jpeg')],
        ])->assertSessionHasErrors(['images.0', 'stage']);
    }

    public function test_reorder_updates_only_images_owned_by_portfolio(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN', 'is_active' => true]);
        $service = Service::create(['name' => 'Reorder', 'slug' => 'reorder', 'description' => 'Test', 'features' => []]);
        $portfolio = Portfolio::create(['service_id' => $service->id, 'title' => 'Satu', 'slug' => 'satu', 'vehicle_type' => 'CAR']);
        $other = Portfolio::create(['service_id' => $service->id, 'title' => 'Dua', 'slug' => 'dua', 'vehicle_type' => 'CAR']);
        $first = $portfolio->images()->create(['image_url' => 'https://example.test/1.jpg', 'cloudinary_public_id' => 'one', 'stage' => 'BEFORE', 'sort_order' => 0]);
        $second = $portfolio->images()->create(['image_url' => 'https://example.test/2.jpg', 'cloudinary_public_id' => 'two', 'stage' => 'AFTER', 'sort_order' => 1]);
        $foreign = $other->images()->create(['image_url' => 'https://example.test/3.jpg', 'cloudinary_public_id' => 'three', 'stage' => 'AFTER', 'sort_order' => 9]);
        $client = $this->withCookie('admin_token', auth('admin')->login($admin));

        $client->post("/admin/portfolio/{$portfolio->id}/images/order", [
            'order' => [$first->id => 2, $second->id => 0],
        ])->assertRedirect()->assertSessionHas('status', 'Urutan foto berhasil disimpan.');

        $this->assertDatabaseHas('portfolio_images', ['id' => $first->id, 'sort_order' => 2]);
        $this->assertDatabaseHas('portfolio_images', ['id' => $second->id, 'sort_order' => 0]);

        $client->from('/admin/portfolio')->post("/admin/portfolio/{$portfolio->id}/images/order", [
            'order' => [$foreign->id => 0],
        ])->assertRedirect('/admin/portfolio')->assertSessionHasErrors('order');
        $this->assertDatabaseHas('portfolio_images', ['id' => $foreign->id, 'sort_order' => 9]);
    }

    public function test_delete_keeps_database_record_when_cloudinary_delete_fails(): void
    {
        $mock = $this->createMock(CloudinaryService::class);
        $mock->expects($this->once())->method('delete')->with('portfolio/keep')->willReturn(false);
        $this->app->instance(CloudinaryService::class, $mock);

        $admin = User::factory()->create(['role' => 'ADMIN', 'is_active' => true]);
        $service = Service::create(['name' => 'Delete', 'slug' => 'delete', 'description' => 'Test', 'features' => []]);
        $portfolio = Portfolio::create(['service_id' => $service->id, 'title' => 'Keep', 'slug' => 'keep', 'vehicle_type' => 'CAR']);
        $image = $portfolio->images()->create(['image_url' => 'https://example.test/keep.jpg', 'cloudinary_public_id' => 'portfolio/keep', 'stage' => 'BEFORE', 'sort_order' => 0]);

        $this->withCookie('admin_token', auth('admin')->login($admin))
            ->from('/admin/portfolio')
            ->delete("/admin/portfolio/{$portfolio->id}/images/{$image->id}")
            ->assertRedirect('/admin/portfolio')
            ->assertSessionHasErrors('integration');

        $this->assertDatabaseHas('portfolio_images', ['id' => $image->id]);
    }

    public function test_partial_upload_failure_removes_uploaded_assets_and_database_rows(): void
    {
        $mock = $this->createMock(CloudinaryService::class);
        $mock->expects($this->exactly(2))->method('upload')
            ->willReturnCallback(function (UploadedFile $file) {
                if ($file->getClientOriginalName() === 'second.jpg') {
                    throw new \RuntimeException('Upload failed');
                }
                return ['secure_url' => 'https://example.test/first.jpg', 'public_id' => 'portfolio/first'];
            });
        $mock->expects($this->once())->method('delete')->with('portfolio/first')->willReturn(true);
        $this->app->instance(CloudinaryService::class, $mock);

        $admin = User::factory()->create(['role' => 'ADMIN', 'is_active' => true]);
        $service = Service::create(['name' => 'Rollback', 'slug' => 'rollback', 'description' => 'Test', 'features' => []]);
        $portfolio = Portfolio::create(['service_id' => $service->id, 'title' => 'Rollback', 'slug' => 'rollback', 'vehicle_type' => 'CAR']);

        $this->withCookie('admin_token', auth('admin')->login($admin))
            ->from('/admin/portfolio')
            ->post("/admin/portfolio/{$portfolio->id}/images", [
                'images' => [UploadedFile::fake()->image('first.jpg'), UploadedFile::fake()->image('second.jpg')],
                'stage' => 'PROCESS',
            ])->assertRedirect('/admin/portfolio')->assertSessionHasErrors('integration');

        $this->assertDatabaseCount('portfolio_images', 0);
    }

    public function test_admin_can_upload_and_delete_portfolio_images_with_validation(): void
    {
        $mock = $this->createMock(CloudinaryService::class);
        config(['cloudinary.folder' => 'BengkelRudi']);
        $mock->expects($this->once())->method('upload')
            ->with($this->isInstanceOf(UploadedFile::class), 'BengkelRudi/portfolio')
            ->willReturn([
            'secure_url' => 'https://res.cloudinary.com/test/image/upload/v1/BengkelRudi/sample.jpg',
            'public_id' => 'BengkelRudi/sample',
        ]);
        $mock->method('delete')->willReturn(true);
        $this->app->instance(CloudinaryService::class, $mock);

        $admin = User::factory()->create(['role' => 'ADMIN', 'is_active' => true]);
        $service = Service::create(['name' => 'Cat Full', 'slug' => 'cat-full', 'description' => 'Layanan cat', 'features' => []]);
        $portfolio = Portfolio::create(['service_id' => $service->id, 'title' => 'Innova Reborn', 'slug' => 'innova-reborn', 'vehicle_type' => 'CAR']);

        // Bad validation: wrong format
        $badFile = UploadedFile::fake()->create('file.pdf', 100);
        $this->withCookie('admin_token', auth('admin')->login($admin))
            ->post("/admin/portfolio/{$portfolio->id}/images", [
                'images' => [$badFile],
                'stage' => 'BEFORE',
            ])->assertSessionHasErrors('images.0');

        // Valid upload
        $validFile = UploadedFile::fake()->image('before.jpg', 600, 600);
        $this->withCookie('admin_token', auth('admin')->login($admin))
            ->post("/admin/portfolio/{$portfolio->id}/images", [
                'images' => [$validFile],
                'stage' => 'BEFORE',
                'sort_order' => 1,
            ])->assertRedirect();

        $this->assertDatabaseHas('portfolio_images', [
            'portfolio_id' => $portfolio->id,
            'cloudinary_public_id' => 'BengkelRudi/sample',
            'stage' => 'BEFORE',
            'sort_order' => 1,
        ]);

        $image = $portfolio->images()->first();

        // Delete image
        $this->withCookie('admin_token', auth('admin')->login($admin))
            ->delete("/admin/portfolio/{$portfolio->id}/images/{$image->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('portfolio_images', ['id' => $image->id]);
    }
}
