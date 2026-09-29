<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

class SiteSettings
{
    public static function all(): array
    {
        $load = function (): array {
            return array_merge([
                'business_name' => 'Bengkel Rudi',
                'whatsapp_number' => '628123456789',
                'address' => 'RT.05/RW.01, Krajan, Wonokerto, Kec. Bandar, Kabupaten Batang, Jawa Tengah 51254',
                'opening_hours' => 'Setiap hari: 08:00–17:00 WIB',
                'google_business_profile_url' => 'https://share.google/8pHQ7sXmkUWGf9YHI',
                'service_area' => 'Kecamatan Bandar dan seluruh Kabupaten Batang',
                'google_maps_url' => 'https://maps.app.goo.gl/2ZnVyFhLXDU7AfS2A',
                'google_maps_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d989.9742268573294!2d109.7993573!3d-7.0214031!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e70172829a60f21%3A0x1bea261386e94517!2sBengkel%20Kenteng%20dan%20Cat%20Pak%20Rudi!5e0!3m2!1sid!2sid!4v1790510040185!5m2!1sid!2sid',
                'instagram_url' => '',
                'google_business_url' => 'https://g.page/r/CRdF6YYTJuobEBM/review',
                'meta_title' => 'Bengkel Cat & Body Repair Rudi',
                'meta_description' => 'Layanan cat dan body repair mobil dan motor di Wonokerto, Bandar, Kabupaten Batang.',
                'about_content' => 'Bengkel Rudi melayani perbaikan bodi, pengecatan, poles, dan rekondisi kendaraan mobil maupun motor.',
            ], SiteSetting::pluck('value', 'key')->all());
        };

        return app()->environment('testing')
            ? $load()
            : Cache::remember('public.site_settings', now()->addSeconds(60), $load);
    }

    public static function forget(): void
    {
        Cache::forget('public.site_settings');
    }
}
