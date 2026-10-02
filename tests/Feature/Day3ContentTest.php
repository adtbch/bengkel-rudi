<?php
namespace Tests\Feature;

use App\Models\{Portfolio, PortfolioImage, Service, User};
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Day3ContentTest extends TestCase
{
    use DatabaseMigrations;

    private function admin(): self
    {
        $user = User::create(['name'=>'Admin','email'=>uniqid().'@example.com','password'=>Hash::make('Secret123!'),'role'=>'ADMIN','is_active'=>true]);
        return $this->withCookie('admin_token', auth('admin')->login($user));
    }

    private function service(array $data=[]): Service
    {
        return Service::create(array_merge(['name'=>'Cat Panel','slug'=>'cat-panel','description'=>'Perbaikan cat panel kendaraan.','min_price'=>100000,'max_price'=>300000,'features'=>['Rapi'],'is_active'=>true,'sort_order'=>1], $data));
    }

    public function test_service_admin_requires_auth_and_rejects_duplicate_name(): void
    {
        $this->get('/admin/layanan')->assertRedirect('/admin/login');
        $this->admin()->post('/admin/layanan', ['name'=>'Cat Panel','description'=>'Deskripsi cukup','min_price'=>100,'max_price'=>200,'is_active'=>1,'sort_order'=>2])->assertRedirect('/admin/layanan');
        $this->admin()->post('/admin/layanan', ['name'=>'Cat Panel','description'=>'Deskripsi cukup','min_price'=>100,'max_price'=>200,'sort_order'=>3])->assertSessionHasErrors('name');
        $this->assertDatabaseHas('services',['slug'=>'cat-panel']);
        $this->assertDatabaseCount('services', 1);
        $service=Service::where('slug','cat-panel')->firstOrFail();
        $this->admin()->put("/admin/layanan/{$service->id}", ['name'=>'Body Repair','description'=>'Body kendaraan diperbaiki','min_price'=>200,'max_price'=>500,'is_active'=>1,'sort_order'=>4])->assertRedirect('/admin/layanan');
        $this->assertSame('body-repair', $service->fresh()->slug);
        $this->assertSame([], $service->fresh()->features);
        $this->admin()->patch("/admin/layanan/{$service->id}/toggle")->assertRedirect('/admin/layanan');
        $this->assertFalse($service->fresh()->is_active);
        $this->admin()->delete("/admin/layanan/{$service->id}")->assertRedirect('/admin/layanan');
        $this->assertModelMissing($service);
    }

    public function test_service_validation_and_delete_constraint(): void
    {
        $this->admin()->post('/admin/layanan', ['name'=>'','description'=>'x','min_price'=>500,'max_price'=>100,'sort_order'=>-1])->assertSessionHasErrors(['name','description','max_price','sort_order']);
        $service=$this->service();
        Portfolio::create(['service_id'=>$service->id,'title'=>'Avanza','slug'=>'avanza','vehicle_type'=>'CAR','is_published'=>true]);
        $this->admin()->delete("/admin/layanan/{$service->id}")->assertSessionHasErrors('service');
        $this->assertModelExists($service);
    }

    public function test_service_name_and_display_order_must_be_unique(): void
    {
        $service = $this->service(['name' => 'Cat Unik', 'sort_order' => 7]);

        $this->admin()->post('/admin/layanan', [
            'name' => $service->name,
            'description' => 'Deskripsi layanan yang cukup panjang.',
            'sort_order' => 8,
        ])->assertSessionHasErrors('name');

        $this->admin()->post('/admin/layanan', [
            'name' => 'Nama Lain',
            'description' => 'Deskripsi layanan yang cukup panjang.',
            'sort_order' => $service->sort_order,
        ])->assertSessionHasErrors('sort_order');

        $this->assertSame(1, Service::count());
    }

    public function test_admin_mutation_forms_prevent_repeat_submission_and_show_popups(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/admin.blade.php'));
        $script = file_get_contents(public_path('js/admin-form-feedback.js'));

        $this->assertStringContainsString('admin-toast', $layout);
        $this->assertStringContainsString('admin-form-feedback.js', $layout);
        $this->assertStringContainsString("form.dataset.submitting", $script);
        $this->assertStringContainsString("button.disabled = true", $script);
    }

    public function test_public_services_show_active_only_detail_and_empty_state(): void
    {
        $active=$this->service();
        $this->service(['name'=>'Rahasia','slug'=>'rahasia','is_active'=>false]);
        $this->get('/layanan')->assertOk()->assertSee('Cat Panel')->assertDontSee('Rahasia')->assertSee('<meta name="description"', false);
        $this->get("/layanan/{$active->slug}")->assertOk()->assertSee('Rp100.000');
        $this->get('/layanan/rahasia')->assertNotFound();
        Service::query()->delete();
        $this->get('/layanan')->assertOk()->assertSee('Belum ada layanan');
    }

    public function test_public_pages_render_specific_title_and_description_metadata(): void
    {
        $service = $this->service();
        $portfolio = Portfolio::create(['service_id'=>$service->id, 'title'=>'Avanza Body Repair', 'slug'=>'avanza-body-repair', 'vehicle_type'=>'CAR', 'is_published'=>true]);

        $this->get('/')->assertOk()->assertSee('<title>', false)->assertSee('<meta name="description"', false);
        $this->get('/tentang')->assertOk()->assertSee('Tentang Kami', false)->assertSee('<meta name="description"', false);
        $this->get('/kontak')->assertOk()->assertSee('Kontak &amp; Lokasi Workshop', false)->assertSee('<meta name="description"', false);
        $this->get('/layanan')->assertOk()->assertSee('Layanan &amp; Paket Cat', false)->assertSee('<meta name="description"', false);
        $this->get("/layanan/{$service->slug}")->assertOk()->assertSee('<title>'.$service->name, false)->assertSee('<meta name="description"', false);
        $this->get('/portfolio')->assertOk()->assertSee('<title>Portfolio', false)->assertSee('<meta name="description"', false);
        $this->get("/portfolio/{$portfolio->slug}")->assertOk()->assertSee('<title>'.$portfolio->title, false)->assertSee('<meta name="description"', false);
        $this->assertStringNotContainsString('Oven', file_get_contents(resource_path('views/pages/about.blade.php')));
    }

    public function test_portfolio_admin_validation_crud_publish_and_image_delete_constraint(): void
    {
        $service=$this->service();
        $this->get('/admin/portfolio')->assertRedirect('/admin/login');
        $this->admin()->post('/admin/portfolio',['title'=>'','service_id'=>999,'vehicle_type'=>'TRUCK'])->assertSessionHasErrors(['title','service_id','vehicle_type']);
        $payload=['title'=>'Avanza Body Repair','service_id'=>$service->id,'vehicle_type'=>'CAR','is_published'=>1];
        $this->admin()->post('/admin/portfolio',$payload)
            ->assertRedirect('/admin/portfolio')
            ->assertSessionHas('status', 'Portfolio berhasil ditambahkan.');
        $this->admin()->post('/admin/portfolio',$payload)->assertRedirect('/admin/portfolio');
        $this->assertDatabaseHas('portfolios',['slug'=>'avanza-body-repair-2']);
        $portfolio=Portfolio::where('slug','avanza-body-repair')->firstOrFail();
        $this->admin()->put("/admin/portfolio/{$portfolio->id}",['title'=>'Motor Custom','service_id'=>$service->id,'vehicle_type'=>'MOTOR','is_published'=>1])
            ->assertRedirect('/admin/portfolio')
            ->assertSessionHas('status', 'Portfolio berhasil diperbarui.');
        $this->assertSame('motor-custom',$portfolio->fresh()->slug);
        $this->assertTrue($portfolio->fresh()->is_published);
        $this->admin()->patch("/admin/portfolio/{$portfolio->id}/toggle")
            ->assertRedirect('/admin/portfolio')
            ->assertSessionHas('status', 'Portfolio dijadikan draft.');
        $this->assertFalse($portfolio->fresh()->is_published);
        PortfolioImage::create(['portfolio_id'=>$portfolio->id,'image_url'=>'https://example.test/x.jpg','cloudinary_public_id'=>'x','stage'=>'BEFORE','sort_order'=>0]);
        $this->admin()->delete("/admin/portfolio/{$portfolio->id}")->assertSessionHasErrors('portfolio');
        $this->assertModelExists($portfolio);
        $portfolio->images()->delete();
        $this->admin()->delete("/admin/portfolio/{$portfolio->id}")
            ->assertRedirect('/admin/portfolio')
            ->assertSessionHas('status', 'Portfolio berhasil dihapus.');
        $this->assertModelMissing($portfolio);
    }

    public function test_admin_frontend_matches_prd_clean_premium_minimal_direction(): void
    {
        $css = file_get_contents(public_path('css/admin.css'));
        $dashboard = file_get_contents(resource_path('views/admin/dashboard.blade.php'));
        $portfolio = file_get_contents(resource_path('views/admin/portfolios.blade.php'));

        $this->assertStringContainsString('--admin-page: #f8fafc;', $css);
        $this->assertStringContainsString('--admin-surface: #ffffff;', $css);
        $this->assertStringContainsString('--admin-charcoal: #0f172a;', $css);
        $this->assertStringContainsString('--admin-accent: #dc2626;', $css);
        $this->assertStringNotContainsString('linear-gradient', $css);
        $this->assertStringNotContainsString('rotate(', $css);
        $this->assertStringContainsString('admin-page-head', $dashboard);
        $this->assertStringContainsString('admin-metric-grid', $dashboard);
        $this->assertStringContainsString('Portfolio terbaru', $dashboard);
        $this->assertStringContainsString('admin-page-head', $portfolio);
        $this->assertStringContainsString('admin-card-grid', $portfolio);
    }

    public function test_admin_services_uses_scalable_master_detail_management(): void
    {
        $service = $this->service();

        $this->admin()->get('/admin/layanan')
            ->assertOk()
            ->assertSee('admin-service-list', false)
            ->assertSee("href=\"/admin/layanan/{$service->id}\"", false)
            ->assertSee('Kelola')
            ->assertDontSee('name="_method" value="PUT"', false);

        $this->admin()->get("/admin/layanan/{$service->id}")
            ->assertOk()
            ->assertSee('Kembali ke daftar')
            ->assertSee('Simpan semua perubahan')
            ->assertSee('Hapus layanan')
            ->assertSee('name="_method" value="PUT"', false);
    }

    public function test_portfolio_uses_clear_admin_hierarchy(): void
    {
        $view = file_get_contents(resource_path('views/admin/portfolios.blade.php'));

        $this->assertStringContainsString('admin-page-head__eyebrow', $view);
        $this->assertStringContainsString('admin-page-head__mark', $view);
        $this->assertStringContainsString('admin-section-head', $view);
    }

    public function test_portfolio_create_form_exposes_accessible_field_errors(): void
    {
        $source = file_get_contents(resource_path('views/admin/portfolios.blade.php'));

        $this->assertStringContainsString("@error('title')", $source);
        $this->assertStringContainsString("@error('service_id')", $source);
        $this->assertStringContainsString("@error('vehicle_type')", $source);
        $this->assertStringContainsString('aria-describedby="create-title-error"', $source);
        $this->assertStringContainsString('id="create-title-error"', $source);
    }

    public function test_admin_portfolio_keeps_navigation_and_errors_accessible_on_mobile(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/admin.blade.php'));
        $css = file_get_contents(public_path('css/admin.css'));
        $view = file_get_contents(resource_path('views/admin/portfolios.blade.php'));

        $this->assertStringContainsString('/css/admin.css?v=', $layout);
        $this->assertStringContainsString('https://res.cloudinary.com/dkv2rn5ax/image/upload/logo-bengkel-rudi.png_calja6.png', $layout);
        $this->assertStringContainsString('alt="Logo Bengkel Rudi"', $layout);
        $this->assertStringContainsString('class="admin-brand"', $layout);
        $this->assertDoesNotMatchRegularExpression('/\.admin-nav\s*\{[^}]*flex-wrap:\s*wrap/s', $css);
        $this->assertStringContainsString('.admin-nav__links', $css);
        $this->assertStringContainsString('.admin-mobile-menu__list', $css);
        $this->assertStringContainsString('.admin-brand__logo', $css);
        $this->assertStringContainsString('class="admin-field-error" role="alert"', $view);
        $this->assertStringContainsString(':focus-visible', $css);
        $this->assertStringContainsString('min-height: 44px', $css);
    }

    public function test_portfolio_upload_exposes_local_preview_with_cleanup(): void
    {
        $view = file_get_contents(resource_path('views/admin/portfolio-edit.blade.php'));
        $script = file_get_contents(public_path('js/admin-portfolio.js')) ?: '';

        $this->assertStringContainsString('data-image-input', $view);
        $this->assertStringContainsString('data-image-preview', $view);
        $this->assertStringContainsString('URL.createObjectURL', $script);
        $this->assertStringContainsString('URL.revokeObjectURL', $script);
        $this->assertStringContainsString('DataTransfer', $script);
    }

    public function test_portfolio_admin_uses_scannable_index_and_dedicated_management_page(): void
    {
        $service = $this->service();
        $portfolio = Portfolio::create([
            'service_id' => $service->id,
            'title' => 'Avanza Body Repair',
            'slug' => 'avanza-body-repair',
            'vehicle_type' => 'CAR',
            'is_published' => false,
        ]);

        $portfolio->images()->create([
            'image_url' => 'https://example.test/before.jpg',
            'cloudinary_public_id' => 'portfolio/before',
            'stage' => 'BEFORE',
            'sort_order' => 1,
        ]);

        $this->withoutExceptionHandling();

        $this->admin()->get('/admin/portfolio')
            ->assertOk()
            ->assertSee('admin-portfolio-list', false)
            ->assertSee("href=\"/admin/portfolio/{$portfolio->id}\"", false)
            ->assertSee('Kelola')
            ->assertDontSee('enctype="multipart/form-data"', false)
            ->assertDontSee("action=\"/admin/portfolio/{$portfolio->id}/images/order\"", false);

        $this->admin()->get("/admin/portfolio/{$portfolio->id}")
            ->assertOk()
            ->assertSee('Avanza Body Repair')
            ->assertDontSee('enctype="multipart/form-data"', false)
            ->assertSee('data-media-input', false)
            ->assertSee('data-upload-list', false)
            ->assertSee("data-sign-url=\"/admin/portfolio/{$portfolio->id}/uploads/sign\"", false)
            ->assertDontSee('name="images[]"', false)
            ->assertSee('name="stage"', false)
            ->assertSee('name="sort_order"', false)
            ->assertSee("action=\"/admin/portfolio/{$portfolio->id}/save\"", false)
            ->assertSee('Tampilkan portfolio di website')
            ->assertSee('Simpan semua perubahan')
            ->assertSee('Hapus portfolio')
            ->assertSee("form=\"delete-image-{$portfolio->id}-", false)
            ->assertSee("id=\"delete-image-{$portfolio->id}-", false);
    }

    public function test_public_portfolio_filters_drafts_and_displays_optional_relations(): void
    {
        $service=$this->service();
        $published=Portfolio::create(['service_id'=>$service->id,'title'=>'Mobil Jadi','slug'=>'mobil-jadi','vehicle_type'=>'CAR','is_published'=>true]);
        $published->images()->create(['image_url'=>'https://example.test/result.jpg','cloudinary_public_id'=>'result','stage'=>'AFTER','sort_order'=>0]);
        Portfolio::create(['service_id'=>$service->id,'title'=>'Draft Rahasia','slug'=>'draft-rahasia','vehicle_type'=>'MOTOR','is_published'=>false]);
        $this->get('/portfolio')->assertOk()->assertSee('Mobil Jadi')->assertDontSee('Draft Rahasia');
        $this->get('/portfolio/mobil-jadi')->assertOk()->assertSee('Cat Panel')->assertSee('result.jpg');
        $this->get('/portfolio/draft-rahasia')->assertNotFound();
        PortfolioImage::query()->delete(); Portfolio::query()->delete();
        $this->get('/portfolio')->assertOk()->assertSee('Belum ada portfolio');
    }

    public function test_homepage_uses_customer_focused_centered_hero(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('id="beranda"', false)
            ->assertSee('class="workshop-hero__brand"', false)
            ->assertDontSee('workshop-hero__media', false)
            ->assertSee('Solusi Cat dan Body Repair Terpercaya di')
            ->assertSee('Hubungi Kami via WhatsApp')
            ->assertSee('Puas dengan layanan kami?')
            ->assertSee('Bagikan pengalaman Anda di Google')
            ->assertSee('Beri Ulasan di Google')
            ->assertSee('Sebelum datang', false)
            ->assertSee('Kirim foto kerusakan', false)
            ->assertSee('Cek kondisi dulu, baru tentukan pekerjaan.', false)
            ->assertSee('Sebelum serah terima', false)
            ->assertSee('class="service-list"', false)
            ->assertSee('class="review-cta review-cta--light"', false)
            ->assertDontSee('about-preview__steps li::before', false)
            ->assertSee('https://g.page/r/CRdF6YYTJuobEBM/review', false)
            ->assertDontSee('Lihat Galeri', false)
            ->assertDontSee('workshop-hero__copy', false);
    }

    public function test_homepage_limits_latest_portfolios_and_button_depends_on_total(): void
    {
        $service=$this->service();
        foreach(range(1,6) as $i) Portfolio::create(['service_id'=>$service->id,'title'=>"Karya {$i}",'slug'=>"karya-{$i}",'vehicle_type'=>'CAR','is_published'=>true]);
        $this->get('/')->assertOk()->assertSee('Karya 6')->assertDontSee('Lihat Semua Portfolio');
        Portfolio::create(['service_id'=>$service->id,'title'=>'Karya 7','slug'=>'karya-7','vehicle_type'=>'CAR','is_published'=>true]);
        $this->get('/')->assertOk()->assertSee('Karya 7')->assertDontSee('Karya 1')->assertSee('Lihat Semua Portfolio');
    }

    public function test_duplicate_image_order_is_rejected_without_database_changes(): void
    {
        $service = $this->service();
        $portfolio = Portfolio::create(['service_id' => $service->id, 'title' => 'Urutan Foto', 'slug' => 'urutan-foto', 'vehicle_type' => 'CAR', 'is_published' => true]);
        $first = $portfolio->images()->create(['image_url' => 'https://example.test/1.jpg', 'cloudinary_public_id' => 'order/1', 'stage' => 'BEFORE', 'sort_order' => 1]);
        $second = $portfolio->images()->create(['image_url' => 'https://example.test/2.jpg', 'cloudinary_public_id' => 'order/2', 'stage' => 'AFTER', 'sort_order' => 2]);

        $this->admin()->post("/admin/portfolio/{$portfolio->id}/images/order", ['order' => [$first->id => 1, $second->id => 1]])
            ->assertSessionHasErrors(['order' => 'Urutan foto harus unik dalam satu portfolio.']);

        $this->assertSame(1, $first->fresh()->sort_order);
        $this->assertSame(2, $second->fresh()->sort_order);
    }

    public function test_image_order_must_start_at_one(): void
    {
        $service = $this->service();
        $portfolio = Portfolio::create(['service_id' => $service->id, 'title' => 'Urutan Minimum', 'slug' => 'urutan-minimum', 'vehicle_type' => 'CAR', 'is_published' => true]);
        $image = $portfolio->images()->create(['image_url' => 'https://example.test/min.jpg', 'cloudinary_public_id' => 'order/min', 'stage' => 'BEFORE', 'sort_order' => 1]);

        $this->admin()->post("/admin/portfolio/{$portfolio->id}/images/order", ['order' => [$image->id => 0]])
            ->assertSessionHasErrors('order.'.$image->id);
        $this->assertSame(1, $image->fresh()->sort_order);
    }

    public function test_public_vehicle_labels_are_indonesian_without_changing_storage(): void
    {
        $service = $this->service();
        foreach (['CAR' => 'Mobil', 'MOTOR' => 'Motor'] as $type => $label) {
            $portfolio = Portfolio::create(['service_id' => $service->id, 'title' => 'Karya '.$type, 'slug' => strtolower($type), 'vehicle_type' => $type, 'is_published' => true]);
            $this->assertSame($label, $portfolio->vehicle_label);
            foreach (['/', '/portfolio', '/portfolio/'.$portfolio->slug] as $url) {
                $this->get($url)->assertOk()->assertSee($label)->assertDontSee('>'.$type.'<', false)->assertDontSee($type.' ·', false);
            }
            $this->assertSame($type, $portfolio->fresh()->vehicle_type);
        }
    }

    public function test_mutating_admin_routes_keep_web_csrf_middleware(): void
    {
        foreach(['admin/layanan','admin/portfolio'] as $uri) {
            $route=collect(app('router')->getRoutes())->first(fn($r)=>$r->uri()===$uri && in_array('POST',$r->methods()));
            $this->assertNotNull($route);
            $this->assertContains('web',$route->gatherMiddleware());
            $this->assertContains('admin.jwt',$route->gatherMiddleware());
        }
    }
}
