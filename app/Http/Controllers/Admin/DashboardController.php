<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\Service;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('admin.dashboard', [
            'portfolioCount' => Portfolio::count(),
            'activeServiceCount' => Service::where('is_active', true)->count(),
            'newestPortfolios' => Portfolio::latest()->limit(5)->get(),
        ]);
    }
}
