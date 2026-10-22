<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Support\SiteSettings;
use Illuminate\Support\Facades\Cache;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Cache::remember('public.services.active', 60, function () {
            return Service::where('is_active', true)->orderBy('sort_order')->get();
        });

        return view('pages.services.index', [
            'services' => $services,
            'settings' => SiteSettings::all(),
        ]);
    }

    public function show(string $slug)
    {
        return view('pages.services.detail', [
            'service' => Service::where('is_active', true)->where('slug', $slug)->firstOrFail(),
            'settings' => SiteSettings::all(),
        ]);
    }
}
