<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\Service;
use App\Support\SiteSettings;

class PageController extends Controller
{
    public function home()
    {
        $published = Portfolio::where('is_published', true);

        return view('pages.home', [
            'services' => Service::where('is_active', true)->orderBy('sort_order')->get(),
            'portfolios' => (clone $published)->with(['service', 'images'])->latest()->orderByDesc('id')->limit(6)->get(),
            'portfolioCount' => $published->count(),
            'settings' => SiteSettings::all(),
        ]);
    }

    public function about()
    {
        return view('pages.about', ['settings' => SiteSettings::all()]);
    }

    public function contact()
    {
        return view('pages.contact', ['settings' => SiteSettings::all()]);
    }
}
