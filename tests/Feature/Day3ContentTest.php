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

    public function test_service_admin_requires_auth_and_crud_generates_collision_safe_slug(): void
    {
        $this->get('/admin/layanan')->assertRedirect('/admin/login');
        $this->admin()->post('/admin/layanan', ['name'=>'Cat Panel','description'=>'Deskripsi cukup','min_price'=>100,'max_price'=>200,'features'=>"Rapi\nCepat",'is_active'=>1,'sort_order'=>2])->assertRedirect('/admin/layanan');
        $this->admin()->post('/admin/layanan', ['name'=>'Cat Panel','description'=>'Deskripsi cukup','min_price'=>100,'max_price'=>200,'features'=>'Garansi','sort_order'=>3])->assertRedirect('/admin/layanan');
        $this->assertDatabaseHas('services',['slug'=>'cat-panel']);
        $this->assertDatabaseHas('services',['slug'=>'cat-panel-2']);
        $service=Service::where('slug','cat-panel')->firstOrFail();
        $this->admin()->put("/admin/layanan/{$service->id}", ['name'=>'Body Repair','description'=>'Body kendaraan diperbaiki','min_price'=>200,'max_price'=>500,'features'=>'Presisi','is_active'=>1,'sort_order'=>4])->assertRedirect('/admin/layanan');
        $this->assertSame('body-repair', $service->fresh()->slug);
        $this->admin()->patch("/admin/layanan/{$service->id}/toggle")->assertRedirect('/admin/layanan');
        $this->assertFalse($service->fresh()->is_active);
        $this->admin()->delete("/admin/layanan/{$service->id}")->assertRedirect('/admin/layanan');
        $this->assertModelMissing($service);
    }

    public function test_service_validation_and_delete_constraint(): void
    {
        $this->admin()->post('/admin/layanan', ['name'=>'','description'=>'x','min_price'=>500,'max_price'=>100,'features'=>'','sort_order'=>-1])->assertSessionHasErrors(['name','description','max_price','features','sort_order']);
        $service=$this->service();
        Portfolio::create(['service_id'=>$service->id,'title'=>'Avanza','slug'=>'avanza','vehicle_type'=>'CAR','is_published'=>true]);
        $this->admin()->delete("/admin/layanan/{$service->id}")->assertSessionHasErrors('service');
        $this->assertModelExists($service);
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

    public function test_portfolio_admin_validation_crud_publish_and_image_delete_constraint(): void
    {
        $service=$this->service();
        $this->get('/admin/portfolio')->assertRedirect('/admin/login');
        $this->admin()->post('/admin/portfolio',['title'=>'','service_id'=>999,'vehicle_type'=>'TRUCK'])->assertSessionHasErrors(['title','service_id','vehicle_type']);
        $payload=['title'=>'Avanza Body Repair','service_id'=>$service->id,'vehicle_type'=>'CAR','is_published'=>1];
        $this->admin()->post('/admin/portfolio',$payload)->assertRedirect('/admin/portfolio');
        $this->admin()->post('/admin/portfolio',$payload)->assertRedirect('/admin/portfolio');
        $this->assertDatabaseHas('portfolios',['slug'=>'avanza-body-repair-2']);
        $portfolio=Portfolio::where('slug','avanza-body-repair')->firstOrFail();
        $this->admin()->put("/admin/portfolio/{$portfolio->id}",['title'=>'Motor Custom','service_id'=>$service->id,'vehicle_type'=>'MOTOR'])->assertRedirect('/admin/portfolio');
        $this->assertSame('motor-custom',$portfolio->fresh()->slug);
        $this->admin()->patch("/admin/portfolio/{$portfolio->id}/toggle")->assertRedirect('/admin/portfolio');
        PortfolioImage::create(['portfolio_id'=>$portfolio->id,'image_url'=>'https://example.test/x.jpg','cloudinary_public_id'=>'x','stage'=>'BEFORE','sort_order'=>0]);
        $this->admin()->delete("/admin/portfolio/{$portfolio->id}")->assertSessionHasErrors('portfolio');
        $portfolio->images()->delete();
        $this->admin()->delete("/admin/portfolio/{$portfolio->id}")->assertRedirect('/admin/portfolio');
        $this->assertModelMissing($portfolio);
    }

    public function test_portfolio_admin_page_exposes_mobile_friendly_edit_and_actions(): void
    {
        $service = $this->service();
        $portfolio = Portfolio::create([
            'service_id' => $service->id,
            'title' => 'Avanza Body Repair',
            'slug' => 'avanza-body-repair',
            'vehicle_type' => 'CAR',
            'is_published' => false,
        ]);

        $this->admin()->get('/admin/portfolio')
            ->assertOk()
            ->assertSee('class="admin-shell"', false)
            ->assertSee('class="admin-card-grid"', false)
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('name="images[]"', false)
            ->assertSee('name="stage"', false)
            ->assertSee('name="sort_order"', false)
            ->assertSee("action=\"/admin/portfolio/{$portfolio->id}\"", false)
            ->assertSee('value="Avanza Body Repair"', false)
            ->assertSee('Publish')
            ->assertSee('Hapus');
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
            ->assertSee('Cat dan Body Repair Terpercaya di')
            ->assertSee('Konsultasi via WhatsApp')
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
