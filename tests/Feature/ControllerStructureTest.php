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
