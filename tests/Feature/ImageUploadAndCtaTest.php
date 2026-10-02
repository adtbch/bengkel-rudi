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

class ImageUploadAndCtaTest extends TestCase
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

    private function admin(): User
    {
        return User::factory()->create(['role' => 'ADMIN', 'is_active' => true]);
    }

    private function client(User $admin)
    {
        return $this->withCookie('admin_token', auth('admin')->login($admin));
    }

    private function portfolio(string $title = 'Judul Lama', string $slug = 'judul-lama'): Portfolio
    {
        $service = Service::create(['name' => 'Cat Panel', 'slug' => $slug.'-service', 'description' => 'Layanan cat', 'features' => []]);

        return Portfolio::create(['service_id' => $service->id, 'title' => $title, 'slug' => $slug, 'vehicle_type' => 'CAR']);
    }

    /**
     * The direct-upload client only posts metadata for files that already live
     * on Cloudinary, so build the payload exactly as the browser would.
     */
    private function signedUpload(Portfolio $portfolio, User $admin, string $resourceType, string $filename): array
    {
        $payload = $this->client($admin)->postJson("/admin/portfolio/{$portfolio->id}/uploads/sign", [
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

    public function test_single_management_form_updates_details_order_publication_and_attaches_uploaded_images(): void
    {
        $admin = $this->admin();
        $service = Service::create(['name' => 'Cat Panel', 'slug' => 'cat-panel', 'description' => 'Layanan cat', 'features' => []]);
        $portfolio = Portfolio::create(['service_id' => $service->id, 'title' => 'Judul Lama', 'slug' => 'judul-lama', 'vehicle_type' => 'CAR']);
        $first = $portfolio->images()->create(['image_url' => 'https://example.test/1.jpg', 'cloudinary_public_id' => 'one', 'stage' => 'BEFORE', 'sort_order' => 1]);
        $second = $portfolio->images()->create(['image_url' => 'https://example.test/2.jpg', 'cloudinary_public_id' => 'two', 'stage' => 'AFTER', 'sort_order' => 2]);
        $upload = $this->signedUpload($portfolio, $admin, 'image', 'new.jpg');

        $this->client($admin)->post("/admin/portfolio/{$portfolio->id}/save", [
            'title' => 'Judul Baru',
            'service_id' => $service->id,
            'vehicle_type' => 'MOTOR',
            'is_published' => 1,
            'order' => [$first->id => 2, $second->id => 1],
            'uploads' => [$upload],
            'stage' => 'PROCESS',
            'sort_order' => 3,
        ])->assertRedirect("/admin/portfolio/{$portfolio->id}")
            ->assertSessionHas('status', 'Semua perubahan berhasil disimpan.');

        $this->assertDatabaseHas('portfolios', ['id' => $portfolio->id, 'title' => 'Judul Baru', 'vehicle_type' => 'MOTOR', 'is_published' => true]);
        $this->assertDatabaseHas('portfolio_images', ['id' => $first->id, 'sort_order' => 2]);
        $this->assertDatabaseHas('portfolio_images', ['id' => $second->id, 'sort_order' => 1]);
        $this->assertDatabaseHas('portfolio_images', [
            'cloudinary_public_id' => $upload['public_id'],
            'stage' => 'PROCESS',
            'sort_order' => 3,
            'media_type' => 'image',
        ]);
        $this->assertDatabaseCount('media_uploads', 0);
    }

    public function test_admin_can_attach_multiple_portfolio_images_with_consecutive_order(): void
    {
        $admin = $this->admin();
        $portfolio = $this->portfolio('Jazz', 'jazz');
        $portfolio->images()->create(['image_url' => 'https://example.test/0.jpg', 'cloudinary_public_id' => 'zero', 'stage' => 'BEFORE', 'sort_order' => 3]);
        $first = $this->signedUpload($portfolio, $admin, 'image', 'first.jpg');
        $second = $this->signedUpload($portfolio, $admin, 'image', 'second.webp');

        $this->client($admin)->post("/admin/portfolio/{$portfolio->id}/save", [
            'title' => $portfolio->title,
            'service_id' => $portfolio->service_id,
            'vehicle_type' => 'CAR',
            'uploads' => [$first, $second],
            'stage' => 'PROCESS',
            'sort_order' => 4,
        ])->assertRedirect("/admin/portfolio/{$portfolio->id}")->assertSessionHas('status');

        $this->assertDatabaseHas('portfolio_images', ['cloudinary_public_id' => $first['public_id'], 'stage' => 'PROCESS', 'sort_order' => 4]);
        $this->assertDatabaseHas('portfolio_images', ['cloudinary_public_id' => $second['public_id'], 'stage' => 'PROCESS', 'sort_order' => 5]);
    }

    public function test_save_rejects_uploads_with_a_missing_stage(): void
    {
        $admin = $this->admin();
        $portfolio = $this->portfolio('Brio', 'brio');
        $upload = $this->signedUpload($portfolio, $admin, 'image', 'oversized.jpg');

        $this->client($admin)->post("/admin/portfolio/{$portfolio->id}/save", [
            'title' => $portfolio->title,
            'service_id' => $portfolio->service_id,
            'vehicle_type' => 'CAR',
            'uploads' => [$upload],
        ])->assertSessionHasErrors('stage');
    }

    public function test_reorder_updates_only_images_owned_by_portfolio(): void
    {
        $admin = $this->admin();
        $service = Service::create(['name' => 'Reorder', 'slug' => 'reorder', 'description' => 'Test', 'features' => []]);
        $portfolio = Portfolio::create(['service_id' => $service->id, 'title' => 'Satu', 'slug' => 'satu', 'vehicle_type' => 'CAR']);
        $other = Portfolio::create(['service_id' => $service->id, 'title' => 'Dua', 'slug' => 'dua', 'vehicle_type' => 'CAR']);
        $first = $portfolio->images()->create(['image_url' => 'https://example.test/1.jpg', 'cloudinary_public_id' => 'one', 'stage' => 'BEFORE', 'sort_order' => 1]);
        $second = $portfolio->images()->create(['image_url' => 'https://example.test/2.jpg', 'cloudinary_public_id' => 'two', 'stage' => 'AFTER', 'sort_order' => 2]);
        $foreign = $other->images()->create(['image_url' => 'https://example.test/3.jpg', 'cloudinary_public_id' => 'three', 'stage' => 'AFTER', 'sort_order' => 9]);
        $client = $this->client($admin);

        $client->post("/admin/portfolio/{$portfolio->id}/images/order", [
            'order' => [$first->id => 2, $second->id => 1],
        ])->assertRedirect()->assertSessionHas('status', 'Urutan foto berhasil disimpan.');

        $this->assertDatabaseHas('portfolio_images', ['id' => $first->id, 'sort_order' => 2]);
        $this->assertDatabaseHas('portfolio_images', ['id' => $second->id, 'sort_order' => 1]);

        $client->from('/admin/portfolio')->post("/admin/portfolio/{$portfolio->id}/images/order", [
            'order' => [$foreign->id => 1],
        ])->assertRedirect('/admin/portfolio')->assertSessionHasErrors('order');
        $this->assertDatabaseHas('portfolio_images', ['id' => $foreign->id, 'sort_order' => 9]);
    }

    public function test_delete_keeps_database_record_when_cloudinary_delete_fails(): void
    {
        $mock = $this->getMockBuilder(CloudinaryService::class)->onlyMethods(['delete'])->getMock();
        $mock->expects($this->once())->method('delete')->with('portfolio/keep')->willReturn(false);
        $this->app->instance(CloudinaryService::class, $mock);

        $admin = $this->admin();
        $portfolio = $this->portfolio('Keep', 'keep');
        $image = $portfolio->images()->create(['image_url' => 'https://example.test/keep.jpg', 'cloudinary_public_id' => 'portfolio/keep', 'stage' => 'BEFORE', 'sort_order' => 0]);

        $this->client($admin)
            ->from('/admin/portfolio')
            ->delete("/admin/portfolio/{$portfolio->id}/images/{$image->id}")
            ->assertRedirect('/admin/portfolio')
            ->assertSessionHasErrors('integration');

        $this->assertDatabaseHas('portfolio_images', ['id' => $image->id]);
    }

    public function test_save_rejects_upload_metadata_belonging_to_another_user_portfolio_or_token(): void
    {
        $admin = $this->admin();
        $stranger = $this->admin();
        $portfolio = $this->portfolio('Cat Full', 'cat-full');
        $other = $this->portfolio('Lain', 'lain');
        $upload = $this->signedUpload($portfolio, $admin, 'image', 'sample.jpg');

        $payload = [
            'title' => $portfolio->title,
            'service_id' => $portfolio->service_id,
            'vehicle_type' => 'CAR',
            'stage' => 'BEFORE',
        ];

        // Token issued for another portfolio.
        $this->client($admin)->post("/admin/portfolio/{$other->id}/save", $payload + ['uploads' => [$upload]])
            ->assertSessionHasErrors('uploads.0');
        $this->assertDatabaseCount('portfolio_images', 0);

        // Token issued for another user.
        $this->client($stranger)->post("/admin/portfolio/{$portfolio->id}/save", $payload + ['uploads' => [$upload]])
            ->assertSessionHasErrors('uploads.0');
        $this->assertDatabaseCount('portfolio_images', 0);

        // Expired token.
        $expired = $this->signedUpload($portfolio, $admin, 'image', 'expired.jpg');
        $expired['token'] = Crypt::encryptString(json_encode([
            'user_id' => $admin->id,
            'portfolio_id' => $portfolio->id,
            'media_upload_id' => $expired['media_upload_id'],
            'resource_type' => 'image',
            'public_id' => $expired['public_id'],
            'expires_at' => time() - 1,
        ]));
        $this->client($admin)->post("/admin/portfolio/{$portfolio->id}/save", $payload + ['uploads' => [$expired]])
            ->assertSessionHasErrors('uploads.0');
        $this->assertDatabaseCount('portfolio_images', 0);

        // public_id that does not match the token.
        $mismatched = $this->signedUpload($portfolio, $admin, 'image', 'mismatch.jpg');
        $mismatched['public_id'] = 'BengkelRudi/portfolio/ somebody-else.jpg';
        $mismatched['secure_url'] = 'https://res.cloudinary.com/test-cloud/image/upload/v1/'.$mismatched['public_id'];
        $this->client($admin)->post("/admin/portfolio/{$portfolio->id}/save", $payload + ['uploads' => [$mismatched]])
            ->assertSessionHasErrors('uploads.0');
        $this->assertDatabaseCount('portfolio_images', 0);
    }

    public function test_save_rejects_secure_url_outside_the_project_cloud(): void
    {
        $admin = $this->admin();
        $portfolio = $this->portfolio('Innova Reborn', 'innova-reborn');
        $upload = $this->signedUpload($portfolio, $admin, 'image', 'before.jpg');
        $upload['secure_url'] = 'https://evil.test/'.$upload['public_id'];

        $this->client($admin)->post("/admin/portfolio/{$portfolio->id}/save", [
            'title' => $portfolio->title,
            'service_id' => $portfolio->service_id,
            'vehicle_type' => 'CAR',
            'uploads' => [$upload],
            'stage' => 'BEFORE',
        ])->assertSessionHasErrors('uploads.0');

        $this->assertDatabaseCount('portfolio_images', 0);
    }

    public function test_discarding_an_uploaded_file_removes_the_asset_and_its_pending_row(): void
    {
        $admin = $this->admin();
        $portfolio = $this->portfolio('Cat Full', 'cat-full');
        $pending = MediaUpload::create([
            'portfolio_id' => $portfolio->id,
            'public_id' => 'BengkelRudi/portfolio/discard-me',
            'resource_type' => 'image',
            'user_id' => $admin->id,
        ]);

        $mock = $this->getMockBuilder(CloudinaryService::class)->onlyMethods(['delete'])->getMock();
        $mock->expects($this->once())->method('delete')->with('BengkelRudi/portfolio/discard-me', 'image')->willReturn(true);
        $this->app->instance(CloudinaryService::class, $mock);

        $this->client($admin)
            ->deleteJson("/admin/portfolio/{$portfolio->id}/uploads/{$pending->id}")
            ->assertOk();

        $this->assertDatabaseMissing('media_uploads', ['id' => $pending->id]);
    }

    public function test_discarding_an_upload_from_another_user_is_not_found(): void
    {
        $admin = $this->admin();
        $stranger = $this->admin();
        $portfolio = $this->portfolio('Cat Full', 'cat-full');
        $pending = MediaUpload::create([
            'portfolio_id' => $portfolio->id,
            'public_id' => 'BengkelRudi/portfolio/not-mine',
            'resource_type' => 'image',
            'user_id' => $stranger->id,
        ]);

        $this->client($admin)
            ->deleteJson("/admin/portfolio/{$portfolio->id}/uploads/{$pending->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('media_uploads', ['id' => $pending->id]);
    }

    public function test_admin_can_attach_and_delete_portfolio_images_end_to_end(): void
    {
        $admin = $this->admin();
        $service = Service::create(['name' => 'Cat Full', 'slug' => 'cat-full-svc', 'description' => 'Layanan cat', 'features' => []]);
        $portfolio = Portfolio::create(['service_id' => $service->id, 'title' => 'Innova Reborn', 'slug' => 'innova-reborn', 'vehicle_type' => 'CAR']);

        // The sign endpoint refuses anything that is not image or video.
        $this->client($admin)->postJson("/admin/portfolio/{$portfolio->id}/uploads/sign", [
            'resource_type' => 'raw',
            'filename' => 'before.pdf',
        ])->assertStatus(422);

        $upload = $this->signedUpload($portfolio, $admin, 'image', 'before.jpg');
        $this->client($admin)->post("/admin/portfolio/{$portfolio->id}/save", [
            'title' => $portfolio->title,
            'service_id' => $service->id,
            'vehicle_type' => 'CAR',
            'uploads' => [$upload],
            'stage' => 'BEFORE',
            'sort_order' => 1,
        ])->assertRedirect("/admin/portfolio/{$portfolio->id}");

        $this->assertDatabaseHas('portfolio_images', [
            'portfolio_id' => $portfolio->id,
            'cloudinary_public_id' => $upload['public_id'],
            'stage' => 'BEFORE',
            'sort_order' => 1,
        ]);

        $image = $portfolio->images()->first();

        $mock = $this->getMockBuilder(CloudinaryService::class)->onlyMethods(['delete'])->getMock();
        $mock->method('delete')->willReturn(true);
        $this->app->instance(CloudinaryService::class, $mock);

        $this->client($admin)
            ->delete("/admin/portfolio/{$portfolio->id}/images/{$image->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('portfolio_images', ['id' => $image->id]);
    }
}
