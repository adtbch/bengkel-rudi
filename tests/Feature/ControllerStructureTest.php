<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\PortfolioController as AdminPortfolioController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\PortfolioController;
use App\Http\Controllers\Frontend\ServiceController;
use Tests\TestCase;

class ControllerStructureTest extends TestCase
{
    public function test_public_header_and_hero_use_supplied_logo(): void
    {
        $logo = 'https://res.cloudinary.com/dkv2rn5ax/image/upload/logo-bengkel-rudi.png_calja6.png';
        foreach (['layouts/partials/header', 'pages/home'] as $view) {
            $source = file_get_contents(resource_path("views/{$view}.blade.php"));
            $this->assertStringContainsString($logo, $source);
            $this->assertStringNotContainsString('>BR</span>', $source);
        }

        $home = file_get_contents(resource_path('views/pages/home.blade.php'));
        $this->assertStringNotContainsString('workshop-hero__media', $home);

        $css = file_get_contents(public_path('css/site.css'));
        $this->assertStringContainsString('width: 160px;', $css);
        $this->assertStringContainsString('height: 160px;', $css);
        $this->assertStringContainsString('white-space: nowrap;', $css);
        $this->assertStringContainsString('@media (max-width: 1099px)', $css);
        $this->assertStringContainsString('white-space: normal;', $css);
        $this->assertStringContainsString('font-size: clamp(1.45rem, 6.4vw, 2.2rem);', $css);
    }

    public function test_all_public_and_admin_buttons_share_the_same_radius(): void
    {
        $publicCss = file_get_contents(public_path('css/site.css'));
        $adminCss = file_get_contents(public_path('css/admin.css'));

        $this->assertStringContainsString('--radius-button: 9999px;', $publicCss);
        $this->assertStringContainsString('nav.nav-links-desktop a.btn', $publicCss);
        $this->assertStringContainsString('border-radius: var(--radius-button);', $publicCss);
        $this->assertStringContainsString('--radius-button: 9999px;', $adminCss);
        $this->assertStringContainsString('border-radius: var(--radius-button);', $adminCss);
    }

    public function test_controllers_are_split_by_area_and_feature(): void
    {
        $this->assertTrue(class_exists(AdminServiceController::class));
        $this->assertTrue(class_exists(AdminPortfolioController::class));
        $this->assertTrue(class_exists(SettingController::class));
        $this->assertTrue(class_exists(PageController::class));
        $this->assertTrue(class_exists(ServiceController::class));
        $this->assertTrue(class_exists(PortfolioController::class));
    }

    public function test_admin_layout_uses_compact_desktop_nav_and_mobile_list_menu(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/admin.blade.php'));
        $css = file_get_contents(public_path('css/admin.css'));

        $this->assertStringContainsString('class="admin-nav__links"', $layout);
        $this->assertStringContainsString('class="admin-mobile-menu"', $layout);
        $this->assertStringContainsString('<summary', $layout);
        $this->assertStringContainsString('@media (max-width: 767px)', $css);
        $this->assertStringContainsString('.admin-nav__links { display: none; }', $css);
        $this->assertStringContainsString('.admin-mobile-menu { display: block; }', $css);
    }

    public function test_public_layout_has_no_top_notice_bar_or_bottom_mobile_dock(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/public.blade.php'));
        $header = file_get_contents(resource_path('views/layouts/partials/header.blade.php'));

        $this->assertStringNotContainsString('top-notice-bar', $layout);
        $this->assertStringNotContainsString('top-notice-inner', $layout);
        $this->assertStringNotContainsString("layouts.partials.mobile-dock", $layout);
        $this->assertStringContainsString('class="mobile-menu"', $header);
        $this->assertStringContainsString('<summary', $header);
        $this->assertStringContainsString('site-header--hero', $header);
    }

    public function test_vercel_deployment_uses_laravel_handler_and_persistent_runtime_stores(): void
    {
        $vercel = file_get_contents(base_path('vercel.json'));
        $handler = file_get_contents(base_path('api/index.php'));
        $environment = file_get_contents(base_path('.env.example'));

        $this->assertStringContainsString('vercel-php@0.9.0', $vercel);
        $this->assertStringContainsString('"outputDirectory": "public"', $vercel);
        $this->assertStringContainsString('"dest": "/api/index.php"', $vercel);
        $this->assertStringContainsString("require __DIR__.'/../public/index.php';", $handler);
        $this->assertStringContainsString('DB_CONNECTION=pgsql', $environment);
        $this->assertStringContainsString('CACHE_DRIVER=database', $environment);
        $this->assertStringContainsString('SESSION_DRIVER=database', $environment);
        $this->assertStringContainsString('LOG_CHANNEL=stderr', $environment);
    }
}
