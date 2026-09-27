<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\PortfolioImage;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PortfolioImageController extends Controller
{
    public function store(Request $request, Portfolio $portfolio, CloudinaryService $cloudinary)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'required|file|mimes:jpg,jpeg,png,webp|max:10240',
            'stage' => ['required', Rule::in(['BEFORE', 'PROCESS', 'AFTER'])],
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $sortOrder = (int) $request->input('sort_order', 0);
        foreach ($request->file('images') as $file) {
            $uploaded = $cloudinary->upload($file, trim(config('cloudinary.folder', 'BengkelRudi'), '/') . '/portfolio');
            $portfolio->images()->create([
                'image_url' => $uploaded['secure_url'],
                'cloudinary_public_id' => $uploaded['public_id'],
                'stage' => $request->input('stage'),
                'sort_order' => $sortOrder++,
            ]);
        }

        return back()->with('status', 'Foto berhasil diunggah.');
    }

    public function destroy(Portfolio $portfolio, PortfolioImage $image, CloudinaryService $cloudinary)
    {
        abort_unless($image->portfolio_id === $portfolio->id, 404);

        if ($image->cloudinary_public_id) {
            $cloudinary->delete($image->cloudinary_public_id);
        }
        $image->delete();

        return back()->with('status', 'Foto berhasil dihapus.');
    }
}
