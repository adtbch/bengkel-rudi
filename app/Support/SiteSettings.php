<?php

namespace App\Support;

use App\Models\SiteSetting;

class SiteSettings
{
    public static function all(): array
    {
        return array_merge([
            'business_name' => 'Bengkel Cat & Body Repair Rudi',
            'whatsapp_number' => '628123456789',
            'address' => 'Semarang, Jawa Tengah',
            'opening_hours' => 'Senin - Sabtu: 08.00 - 17.00 WIB',
            'google_maps_url' => '',
            'google_maps_embed_url' => '',
            'instagram_url' => '',
            'google_business_url' => '',
            'meta_title' => 'Bengkel Cat & Body Repair Rudi',
            'meta_description' => 'Layanan cat dan body repair mobil dan motor profesional di Semarang.',
            'about_content' => 'Bengkel Rudi melayani perbaikan bodi, pengecatan, poles, dan rekondisi kendaraan mobil maupun motor.',
        ], SiteSetting::pluck('value', 'key')->all());
    }
}
