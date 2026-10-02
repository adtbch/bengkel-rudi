<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\PortfolioImage;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PortfolioImageController extends Controller
{
    public function destroy(Portfolio $portfolio, PortfolioImage $image, CloudinaryService $cloudinary)
    {
        abort_unless($image->portfolio_id === $portfolio->id, 404);

        if ($image->cloudinary_public_id) {
            try {
                if (! $cloudinary->delete($image->cloudinary_public_id, $image->media_type ?? 'image')) {
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
