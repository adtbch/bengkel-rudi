<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
class AdminAuthTest extends TestCase {
 use DatabaseMigrations;
 protected function setUp(): void { parent::setUp(); \Illuminate\Support\Facades\Cache::flush(); }
 protected function user(string $role='ADMIN', bool $active=true): User { return User::create(['name'=>'Admin','email'=>'admin@example.com','password'=>Hash::make('Secret123!'),'role'=>$role,'is_active'=>$active]); }
 public function test_public_root_and_guest_redirect(): void { $this->get('/')->assertOk(); $this->get('/admin')->assertRedirect('/admin/login'); }
 public function test_login_validation_bad_password_and_inactive_are_generic(): void { $this->user(); $this->post('/admin/login',['email'=>"bad\r\n@example.com",'password'=>'x'])->assertSessionHasErrors('email'); $this->post('/admin/login',['email'=>'admin@example.com','password'=>'wrong'])->assertSessionHasErrors('email'); User::query()->update(['is_active'=>false]); $this->post('/admin/login',['email'=>'admin@example.com','password'=>'Secret123!'])->assertSessionHasErrors('email'); }
 public function test_login_sets_encrypted_secure_cookie_and_dashboard_works(): void { $this->user(); $r=$this->withServerVariables(['HTTPS'=>'on'])->post('/admin/login',['email'=>'admin@example.com','password'=>'Secret123!']); $r->assertRedirect('/admin'); $c=collect($r->headers->getCookies())->first(fn($x)=>$x->getName()==='admin_token'); $this->assertNotNull($c); $this->assertTrue($c->isHttpOnly()); $this->assertTrue($c->isSecure()); $this->assertSame('lax',$c->getSameSite()); $token=auth('admin')->attempt(['email'=>'admin@example.com','password'=>'Secret123!']); $this->withCookie('admin_token',$token)->get('/admin')->assertOk(); }
 public function test_login_is_rate_limited(): void { $this->user(); for($i=0;$i<5;$i++) $this->post('/admin/login',['email'=>'admin@example.com','password'=>'wrong']); $this->post('/admin/login',['email'=>'admin@example.com','password'=>'wrong'])->assertStatus(429); }
 public function test_role_and_current_active_state_are_enforced(): void { $u=$this->user(); $token=auth('admin')->login($u); $this->withCookie('admin_token',$token)->get('/admin/users')->assertForbidden(); $u->update(['is_active'=>false]); $this->withCookie('admin_token',$token)->get('/admin')->assertRedirect('/admin/login'); }
 public function test_superadmin_routes_and_logout_blacklist_token(): void { $u=$this->user('SUPER_ADMIN'); $token=auth('admin')->login($u); $this->withCookie('admin_token',$token)->get('/admin/users')->assertOk(); $this->withCookie('admin_token',$token)->post('/admin/logout')->assertRedirect('/admin/login'); $this->withCookie('admin_token',$token)->get('/admin')->assertRedirect('/admin/login'); }
 public function test_expired_token_rejected(): void { config(['jwt.ttl'=>-1]); $this->expectException(\PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException::class); auth('admin')->login($this->user()); }
 public function test_csrf_is_not_bypassed(): void { $route=collect(app('router')->getRoutes())->first(fn($r)=>$r->uri()==='admin/login' && in_array('POST',$r->methods())); $this->assertContains('web',$route->gatherMiddleware()); $this->assertSame([], (new \ReflectionClass(\App\Http\Middleware\VerifyCsrfToken::class))->getDefaultProperties()['except']); }
}
