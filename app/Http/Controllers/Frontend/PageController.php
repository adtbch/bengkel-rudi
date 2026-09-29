<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\Service;
use App\Support\SiteSettings;
use Illuminate\Support\Facades\Cache;

class PageController extends Controller
{
    public function home()
    {
        $load = function (): array {

            $published = Portfolio::where('is_published', true);

            return [
                'services' => Service::where('is_active', true)->orderBy('sort_order')->get(),
                'portfolios' => (clone $published)->with(['service', 'images'])->latest()->orderByDesc('id')->limit(6)->get(),
                'portfolioCount' => $published->count(),
            ];
        };

        $homepageData = app()->environment('testing')
            ? $load()
            : Cache::remember('public.homepage_data', now()->addSeconds(60), $load);

        return view('pages.home', [
            ...$homepageData,
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
