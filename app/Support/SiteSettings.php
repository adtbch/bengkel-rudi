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
            'address' => 'RT.05/RW.01, Krajan, Wonokerto, Kec. Bandar, Kabupaten Batang, Jawa Tengah 51254',
            'opening_hours' => 'Senin - Sabtu: 08.00 - 17.00 WIB',
            'google_maps_url' => 'https://maps.app.goo.gl/2ZnVyFhLXDU7AfS2A',
            'google_maps_embed_url' => '',
            'instagram_url' => '',
            'google_business_url' => '',
            'meta_title' => 'Bengkel Cat & Body Repair Rudi',
            'meta_description' => 'Layanan cat dan body repair mobil dan motor di Wonokerto, Bandar, Kabupaten Batang.',
            'about_content' => 'Bengkel Rudi melayani perbaikan bodi, pengecatan, poles, dan rekondisi kendaraan mobil maupun motor.',
        ], SiteSetting::pluck('value', 'key')->all());
    }
}
