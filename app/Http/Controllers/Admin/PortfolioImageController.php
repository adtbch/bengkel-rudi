<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\PortfolioImage;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PortfolioImageController extends Controller
{
    public function store(Request $request, Portfolio $portfolio, CloudinaryService $cloudinary)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => [
                'required', 'file', 'max:51200',
                function (string $attribute, mixed $file, \Closure $fail): void {
                    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'video/mp4', 'video/webm', 'video/quicktime'];
                    if (!in_array($file->getMimeType(), $allowed, true)
                        || (str_starts_with($file->getMimeType(), 'image/') && $file->getSize() > 10 * 1024 * 1024)) {
                        $fail('File harus JPG, PNG, WebP (maks. 10 MB), MP4, WebM, atau MOV (maks. 50 MB).');
                    }
                },
            ],
            'stage' => ['required', Rule::in(['BEFORE', 'PROCESS', 'AFTER'])],
            'sort_order' => 'nullable|integer|min:1',
        ]);

        $requestedOrder = (int) $request->input('sort_order', 1);
        $sortOrder = max($requestedOrder, ((int) $portfolio->images()->max('sort_order')) + 1);
        $uploadedAssets = [];

        try {
            DB::beginTransaction();
            foreach ($request->file('images') as $file) {
                $resourceType = str_starts_with($file->getMimeType(), 'video/') ? 'video' : 'image';
                $uploaded = $cloudinary->upload($file, trim(config('cloudinary.folder', 'BengkelRudi'), '/') . '/portfolio', $resourceType);
                $uploadedAssets[] = ['public_id' => $uploaded['public_id'], 'resource_type' => $resourceType];
                $portfolio->images()->create([
                    'image_url' => $uploaded['secure_url'],
                    'cloudinary_public_id' => $uploaded['public_id'],
                    'media_type' => $resourceType,
                    'stage' => $request->input('stage'),
                    'sort_order' => $sortOrder++,
                ]);
            }
            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            foreach (array_reverse($uploadedAssets) as $asset) {
                try {
                    $cloudinary->delete($asset['public_id'], $asset['resource_type']);
                } catch (\Throwable) {
                    // Cleanup is best-effort; original upload failure remains primary.
                }
            }

            return back()->withErrors(['integration' => 'Unggah foto gagal. Tidak ada perubahan yang disimpan.']);
        }

        return back()->with('status', 'Foto berhasil diunggah.');
    }

    public function destroy(Portfolio $portfolio, PortfolioImage $image, CloudinaryService $cloudinary)
    {
        abort_unless($image->portfolio_id === $portfolio->id, 404);

        if ($image->cloudinary_public_id) {
            try {
                if (!$cloudinary->delete($image->cloudinary_public_id, $image->media_type ?? 'image')) {
                    return back()->withErrors(['integration' => 'Foto gagal dihapus dari penyimpanan. Data tetap disimpan.']);
                }
            } catch (\Throwable) {
                return back()->withErrors(['integration' => 'Foto gagal dihapus dari penyimpanan. Data tetap disimpan.']);
            }
        }
        $image->delete();

        return back()->with('status', 'Foto berhasil dihapus.');
    }

    public function reorder(Request $request, Portfolio $portfolio)
    {
        $data = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['required', 'integer', 'min:1'],
        ], [
            'order.*.min' => 'Urutan foto minimal 1.',
        ]);

        if (count($data['order']) !== count(array_unique(array_values($data['order'])))) {
            throw ValidationException::withMessages(['order' => 'Urutan foto harus unik dalam satu portfolio.']);
        }

        $ids = array_map('intval', array_keys($data['order']));
        $ownedIds = $portfolio->images()->whereIn('id', $ids)->pluck('id')->map(fn ($id) => (int) $id)->all();

        if (count($ids) !== count($ownedIds)) {
            throw ValidationException::withMessages(['order' => 'Daftar foto tidak valid untuk portfolio ini.']);
        }

        DB::transaction(function () use ($data, $portfolio): void {
            foreach ($data['order'] as $id => $sortOrder) {
                $portfolio->images()->whereKey((int) $id)->update(['sort_order' => $sortOrder]);
            }
        });

        return back()->with('status', 'Urutan foto berhasil disimpan.');
    }
}
