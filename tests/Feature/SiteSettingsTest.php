<?php
namespace Tests\Feature;
use App\Models\{SiteSetting,User};
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
class SiteSettingsTest extends TestCase
{
 use DatabaseMigrations;
 public function test_default_location_uses_confirmed_wonokerto_address_and_map_link(): void
 {
  $address='RT.05/RW.01, Krajan, Wonokerto, Kec. Bandar, Kabupaten Batang, Jawa Tengah 51254';
  $mapUrl='https://maps.app.goo.gl/2ZnVyFhLXDU7AfS2A';
  $embedUrl='https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d989.9742268573294!2d109.7993573!3d-7.0214031!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e70172829a60f21%3A0x1bea261386e94517!2sBengkel%20Kenteng%20dan%20Cat%20Pak%20Rudi!5e0!3m2!1sid!2sid!4v1790510040185!5m2!1sid!2sid';

  $this->get('/kontak')->assertOk()->assertSee($address)->assertSee($mapUrl, false)->assertSee($embedUrl, false);
  $this->get('/')->assertOk()->assertSee($address)->assertSee($embedUrl, false);
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
