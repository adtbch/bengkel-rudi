<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Support\SiteSettings;

class PortfolioController extends Controller
{
    public function index()
    {
        return view('pages.portfolio.index', [
            'portfolios' => Portfolio::where('is_published', true)->with(['service', 'images'])->latest()->get(),
            'settings' => SiteSettings::all(),
        ]);
    }

    public function show(string $slug)
    {
        return view('pages.portfolio.detail', [
            'portfolio' => Portfolio::where('is_published', true)->where('slug', $slug)->with(['service', 'images'])->firstOrFail(),
            'settings' => SiteSettings::all(),
        ]);
    }
}
