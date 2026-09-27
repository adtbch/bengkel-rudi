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

    public function test_admin_can_upload_and_delete_portfolio_images_with_validation(): void
    {
        $mock = $this->createMock(CloudinaryService::class);
        $mock->method('upload')->willReturn([
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
