<?php

namespace App\Support;

use Illuminate\Support\Str;

class UniqueSlug
{
    public static function make(string $value, string $model, ?int $ignore = null): string
    {
        $base = Str::slug($value) ?: 'item';
        $slug = $base;
        $suffix = 2;

        while ($model::where('slug', $slug)->when($ignore, fn ($query) => $query->whereKeyNot($ignore))->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }
}
