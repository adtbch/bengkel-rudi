<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Support\SiteSettings;

class ServiceController extends Controller
{
    public function index()
    {
        return view('pages.services.index', [
            'services' => Service::where('is_active', true)->orderBy('sort_order')->get(),
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
