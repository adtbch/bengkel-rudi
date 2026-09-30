<?php
namespace Tests\Feature;
use App\Models\{SiteSetting,User};
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
class SiteSettingsTest extends TestCase
{
 use DatabaseMigrations;
 public function test_public_css_prevents_viewport_overflow_and_keeps_mobile_navigation_visible(): void
 {
  $css=file_get_contents(public_path('css/site.css'));
  $header=file_get_contents(resource_path('views/layouts/partials/header.blade.php'));
  $this->assertStringNotContainsString('calc(50% - 50vw)', $css);
  $this->assertStringContainsString('clip-path: inset(0 -100vmax);', $css);
  $this->assertStringContainsString('box-shadow: 0 0 0 100vmax var(--brand-dark);', $css);
  $this->assertStringContainsString('.workshop-hero__brand {', $css);
  $this->assertStringContainsString('width: 160px;', $css);
  $this->assertStringContainsString('alt="Logo Bengkel Rudi"', $header);
  $this->assertStringContainsString('class="mobile-menu"', $header);
 }

 public function test_default_location_uses_confirmed_wonokerto_address_and_map_link(): void
 {
  $address='RT.05/RW.01, Krajan, Wonokerto, Kec. Bandar, Kabupaten Batang, Jawa Tengah 51254';
  $mapUrl='https://maps.app.goo.gl/2ZnVyFhLXDU7AfS2A';
  $embedUrl='https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d989.9742268573294!2d109.7993573!3d-7.0214031!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e70172829a60f21%3A0x1bea261386e94517!2sBengkel%20Kenteng%20dan%20Cat%20Pak%20Rudi!5e0!3m2!1sid!2sid!4v1790510040185!5m2!1sid!2sid';

  $this->get('/kontak')->assertOk()->assertSee($address)->assertSee($mapUrl, false)->assertSee($embedUrl, false);
  $this->get('/')->assertOk()->assertSee($address)->assertSee($embedUrl, false);
 }

 public function test_homepage_publishes_local_business_structured_data_without_unconfirmed_coordinates(): void
 {
  $this->withoutExceptionHandling();
  $response=$this->get('/')->assertOk();
  $response->assertSee('application/ld+json',false)->assertSee('"LocalBusiness"',false)->assertSee('"AutoRepair"',false)->assertSee('"address"',false)->assertSee('Wonokerto',false);
  $response->assertDontSee('"geo"',false);
 }

 public function test_public_pages_publish_canonical_social_metadata_and_detail_breadcrumbs(): void
 {
  $service=\App\Models\Service::create(['name'=>'Cat Panel','slug'=>'cat-panel','description'=>'Cat panel rapi','features'=>[],'is_active'=>true]);
  $portfolio=\App\Models\Portfolio::create(['service_id'=>$service->id,'title'=>'Jazz','slug'=>'jazz','vehicle_type'=>'CAR','is_published'=>true]);
  $canonical='https://bengkelrudi.my.id';
  $image=$canonical.'/images/og-bengkel-rudi.png';

  $this->get('/')->assertOk()->assertSee('<link rel="canonical" href="'.$canonical.'/">',false)->assertSee('property="og:image" content="'.$image.'"',false)->assertSee('name="twitter:card" content="summary_large_image"',false)->assertSee('OpeningHoursSpecification',false)->assertSee('Kabupaten Batang',false)->assertSee('https://share.google/8pHQ7sXmkUWGf9YHI',false);
  $this->get('/layanan/cat-panel')->assertOk()->assertSee('<link rel="canonical" href="'.$canonical.'/layanan/cat-panel">',false)->assertSee('BreadcrumbList',false)->assertSee('Cat Panel',false);
  $this->get('/portfolio/jazz')->assertOk()->assertSee('<link rel="canonical" href="'.$canonical.'/portfolio/jazz">',false)->assertSee('BreadcrumbList',false)->assertSee('Jazz',false);
 }

 public function test_robots_and_sitemap_expose_only_public_active_content(): void
 {
  $service=\App\Models\Service::create(['name'=>'Cat Panel','slug'=>'cat-panel','description'=>'Cat panel rapi','features'=>[],'is_active'=>true]);
  \App\Models\Service::create(['name'=>'Rahasia','slug'=>'rahasia','description'=>'Tidak tampil','features'=>[],'is_active'=>false]);
  \App\Models\Portfolio::create(['service_id'=>$service->id,'title'=>'Jazz','slug'=>'jazz','vehicle_type'=>'CAR','is_published'=>true]);
  \App\Models\Portfolio::create(['service_id'=>$service->id,'title'=>'Draft','slug'=>'draft','vehicle_type'=>'CAR','is_published'=>false]);

  $this->get('/robots.txt')->assertOk()->assertHeader('Content-Type','text/plain; charset=UTF-8')->assertSee('Sitemap: '.url('/sitemap.xml'),false);
  $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type','application/xml; charset=UTF-8')->assertSee('/layanan/cat-panel',false)->assertSee('/portfolio/jazz',false)->assertSee('<lastmod>',false)->assertDontSee('/layanan/rahasia',false)->assertDontSee('/portfolio/draft',false);
 }

 public function test_superadmin_updates_settings_and_public_pages_use_them(): void
 {
  $user=User::factory()->create(['role'=>'SUPER_ADMIN','is_active'=>true]);
  $this->withCookie('admin_token',auth('admin')->login($user))->put('/admin/settings',['business_name'=>'Workshop Test','whatsapp_number'=>'0812 3456 7890','address'=>'Jalan Test','opening_hours'=>'Senin 08:00','about_content'=>'Cerita bengkel kami','google_maps_embed_url'=>'https://www.google.com/maps/embed?pb=test'])->assertRedirect('/admin/settings');
  $this->assertDatabaseHas('site_settings',['key'=>'whatsapp_number','value'=>'6281234567890']);
  foreach(['/','/tentang','/kontak','/layanan','/portfolio'] as $url) $this->get($url)->assertOk()->assertSee('Workshop Test')->assertSee('https://wa.me/6281234567890?text=',false);
  $this->get('/kontak')->assertSee('https://www.google.com/maps/embed?pb=test',false)->assertSee('Jalan Test');
 }
}
