<?php

return [
    'cloud_url' => env('CLOUDINARY_URL'),
    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
    'api_key' => env('CLOUDINARY_API_KEY'),
    'api_secret' => env('CLOUDINARY_API_SECRET'),
    'folder' => env('CLOUDINARY_FOLDER', 'BengkelRudi'),
    'token_ttl_minutes' => (int) env('CLOUDINARY_TOKEN_TTL_MINUTES', 360),
    'orphan_ttl_hours' => (int) env('CLOUDINARY_ORPHAN_TTL_HOURS', 24),
    'max_image_kb' => 10240,
    'max_video_kb' => 51200,
];
