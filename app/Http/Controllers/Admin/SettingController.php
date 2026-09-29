<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Support\SiteSettings;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        return view('admin.settings', ['settings' => SiteSettings::all()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'business_name' => 'required|string|max:150',
            'whatsapp_number' => 'required|string|max:30',
            'address' => 'required|string|max:300',
            'opening_hours' => 'required|string|max:200',
            'google_maps_url' => 'nullable|url|max:500',
            'google_maps_embed_url' => 'nullable|url|max:1000',
            'instagram_url' => 'nullable|url|max:500',
            'google_business_url' => 'nullable|url|max:500',
            'meta_title' => 'nullable|string|max:150',
            'meta_description' => 'nullable|string|max:300',
            'about_content' => 'required|string|max:5000',
        ]);
        $data['whatsapp_number'] = $this->normalizeWhatsApp($data['whatsapp_number']);

        foreach ($data as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        }

        SiteSettings::forget();

        return redirect('/admin/settings')->with('status', 'Pengaturan berhasil disimpan.');
    }

    private function normalizeWhatsApp(string $number): string
    {
        $cleaned = preg_replace('/\D+/', '', $number);

        return str_starts_with($cleaned, '0') ? '62' . substr($cleaned, 1) : $cleaned;
    }
}
