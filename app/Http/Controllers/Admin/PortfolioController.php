<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\Service;
use App\Services\CloudinaryService;
use App\Support\UniqueSlug;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PortfolioController extends Controller
{
    public function index()
    {
        return view('admin.portfolios', [
            'portfolios' => Portfolio::with(['service', 'images'])->latest()->get(),
            'services' => Service::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = UniqueSlug::make($data['title'], Portfolio::class);
        Portfolio::create($data);

        return redirect('/admin/portfolio')->with('status', 'Portfolio berhasil ditambahkan.');
    }

    public function show(Portfolio $portfolio)
    {
        return view('admin.portfolio-edit', [
            'portfolio' => $portfolio->load(['service', 'images']),
            'services' => Service::orderBy('name')->get(),
        ]);
    }

    public function save(Request $request, Portfolio $portfolio, CloudinaryService $cloudinary)
    {
        $data = $request->validate([
            'title' => 'required|string|max:160',
            'service_id' => 'required|exists:services,id',
            'vehicle_type' => ['required', Rule::in(['CAR', 'MOTOR'])],
            'is_published' => 'nullable|boolean',
            'order' => 'nullable|array',
            'order.*' => 'required|integer|min:1',
            'images' => 'nullable|array',
            'images.*' => 'required|file|mimes:jpg,jpeg,png,webp|max:10240',
            'stage' => ['required_with:images', 'nullable', Rule::in(['BEFORE', 'PROCESS', 'AFTER'])],
            'sort_order' => 'nullable|integer|min:1',
        ], ['order.*.min' => 'Urutan foto minimal 1.']);

        $order = $data['order'] ?? [];
        if (count($order) !== count(array_unique(array_values($order)))) {
            throw ValidationException::withMessages(['order' => 'Urutan foto harus unik dalam satu portfolio.']);
        }
        $ids = array_map('intval', array_keys($order));
        $ownedIds = $portfolio->images()->whereIn('id', $ids)->pluck('id')->map(fn ($id) => (int) $id)->all();
        if (count($ids) !== count($ownedIds)) {
            throw ValidationException::withMessages(['order' => 'Daftar foto tidak valid untuk portfolio ini.']);
        }

        $uploadedAssets = [];
        try {
            DB::beginTransaction();
            $portfolio->update([
                'title' => $data['title'],
                'slug' => UniqueSlug::make($data['title'], Portfolio::class, $portfolio->id),
                'service_id' => $data['service_id'],
                'vehicle_type' => $data['vehicle_type'],
                'is_published' => $request->boolean('is_published'),
            ]);
            foreach ($order as $id => $sortOrder) {
                $portfolio->images()->whereKey((int) $id)->update(['sort_order' => $sortOrder]);
            }
            $sortOrder = max((int) ($data['sort_order'] ?? 1), ((int) $portfolio->images()->max('sort_order')) + 1);
            foreach ($request->file('images', []) as $file) {
                $uploaded = $cloudinary->upload($file, trim(config('cloudinary.folder', 'BengkelRudi'), '/').'/portfolio');
                $uploadedAssets[] = $uploaded['public_id'];
                $portfolio->images()->create([
                    'image_url' => $uploaded['secure_url'],
                    'cloudinary_public_id' => $uploaded['public_id'],
                    'stage' => $data['stage'],
                    'sort_order' => $sortOrder++,
                ]);
            }
            DB::commit();
        } catch (\Throwable) {
            DB::rollBack();
            foreach (array_reverse($uploadedAssets) as $publicId) {
                try { $cloudinary->delete($publicId); } catch (\Throwable) {}
            }
            return back()->withInput()->withErrors(['integration' => 'Penyimpanan gagal. Tidak ada perubahan yang disimpan.']);
        }

        return redirect("/admin/portfolio/{$portfolio->id}")->with('status', 'Semua perubahan berhasil disimpan.');
    }

    public function update(Request $request, Portfolio $portfolio)
    {
        $data = $this->validated($request);
        $data['slug'] = UniqueSlug::make($data['title'], Portfolio::class, $portfolio->id);
        $portfolio->update($data);

        return redirect('/admin/portfolio')->with('status', 'Portfolio berhasil diperbarui.');
    }

    public function toggle(Portfolio $portfolio)
    {
        $portfolio->update(['is_published' => !$portfolio->is_published]);

        return redirect('/admin/portfolio')->with(
            'status',
            $portfolio->is_published ? 'Portfolio dipublikasikan.' : 'Portfolio dijadikan draft.'
        );
    }

    public function destroy(Portfolio $portfolio)
    {
        if ($portfolio->images()->exists()) {
            return back()->withErrors(['portfolio' => 'Portfolio masih memiliki gambar.']);
        }

        $portfolio->delete();

        return redirect('/admin/portfolio')->with('status', 'Portfolio berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:160',
            'service_id' => 'required|exists:services,id',
            'vehicle_type' => ['required', Rule::in(['CAR', 'MOTOR'])],
            'is_published' => 'nullable|boolean',
        ]);
        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }
}
