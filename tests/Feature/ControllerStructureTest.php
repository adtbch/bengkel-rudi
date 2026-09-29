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

        $css = file_get_contents(public_path('css/site.css'));
        $this->assertStringContainsString('width: min(48vw, 200px);', $css);
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
}
