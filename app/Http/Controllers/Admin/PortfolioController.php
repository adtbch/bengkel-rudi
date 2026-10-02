<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaUpload;
use App\Models\Portfolio;
use App\Models\Service;
use App\Services\CloudinaryService;
use App\Support\UniqueSlug;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
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
            'uploads' => 'nullable|array',
            'uploads.*' => 'required|array',
            'uploads.*.media_upload_id' => 'required|integer',
            'uploads.*.public_id' => 'required|string|max:255',
            'uploads.*.secure_url' => 'required|string|max:2048',
            'uploads.*.resource_type' => ['required', Rule::in(['image', 'video'])],
            'uploads.*.token' => 'required|string',
            'stage' => ['required_with:uploads', 'nullable', Rule::in(['BEFORE', 'PROCESS', 'AFTER'])],
            'sort_order' => 'nullable|integer|min:1',
        ], [
            'order.*.min' => 'Urutan foto minimal 1.',
            'uploads.*.media_upload_id' => 'Unggahan tidak valid.',
            'uploads.*.public_id' => 'Unggahan tidak valid.',
            'uploads.*.secure_url' => 'Alamat media tidak valid.',
            'uploads.*.resource_type' => 'Jenis media tidak valid.',
            'uploads.*.token' => 'Token unggahan tidak valid.',
        ]);

        $order = $data['order'] ?? [];
        if (count($order) !== count(array_unique(array_values($order)))) {
            throw ValidationException::withMessages(['order' => 'Urutan foto harus unik dalam satu portfolio.']);
        }
        $ids = array_map('intval', array_keys($order));
        $ownedIds = $portfolio->images()->whereIn('id', $ids)->pluck('id')->map(fn ($id) => (int) $id)->all();
        if (count($ids) !== count($ownedIds)) {
            throw ValidationException::withMessages(['order' => 'Daftar foto tidak valid untuk portfolio ini.']);
        }

        $claimedUploads = $this->resolveUploads($portfolio, $data['uploads'] ?? [], $cloudinary);
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
            foreach ($claimedUploads as $upload) {
                $uploadedAssets[$upload['public_id']] = $upload['resource_type'];
                $portfolio->images()->create([
                    'image_url' => $upload['secure_url'],
                    'cloudinary_public_id' => $upload['public_id'],
                    'media_type' => $upload['resource_type'],
                    'stage' => $data['stage'],
                    'sort_order' => $sortOrder++,
                ]);
            }
            MediaUpload::whereIn('id', array_column($claimedUploads, 'media_upload_id'))->delete();
            DB::commit();
        } catch (\Throwable) {
            DB::rollBack();
            foreach ($uploadedAssets as $publicId => $resourceType) {
                try {
                    $cloudinary->delete($publicId, $resourceType);
                } catch (\Throwable) {
                }
            }
            if ($claimedUploads !== []) {
                MediaUpload::whereIn('id', array_column($claimedUploads, 'media_upload_id'))->delete();
            }

            return back()->withInput()->withErrors(['integration' => 'Penyimpanan gagal. Tidak ada perubahan yang disimpan.']);
        }

        return redirect("/admin/portfolio/{$portfolio->id}")->with('status', 'Semua perubahan berhasil disimpan.');
    }

    /**
     * Validate the metadata of files already uploaded straight to Cloudinary.
     * No file bytes reach this server, so ownership, freshness, and the delivery
     * host are verified from the signed token and the pending media_uploads row.
     *
     * @param  array<int, array<string, mixed>>  $uploads
     * @return array<int, array{media_upload_id: int, public_id: string, secure_url: string, resource_type: string}>
     */
    private function resolveUploads(Portfolio $portfolio, array $uploads, CloudinaryService $cloudinary): array
    {
        $userId = (int) auth('admin')->id();
        $resolved = [];

        foreach ($uploads as $index => $upload) {
            try {
                $claim = json_decode(Crypt::decryptString($upload['token']), true);
            } catch (\Throwable) {
                $claim = null;
            }

            $invalid = ! is_array($claim)
                || (int) ($claim['portfolio_id'] ?? 0) !== $portfolio->id
                || (int) ($claim['user_id'] ?? 0) !== $userId
                || ($claim['resource_type'] ?? null) !== $upload['resource_type']
                || ($claim['public_id'] ?? null) !== $upload['public_id']
                || (int) ($claim['expires_at'] ?? 0) < time();

            $mediaUpload = MediaUpload::find($upload['media_upload_id']);
            $invalid = $invalid
                || ! $mediaUpload
                || $mediaUpload->portfolio_id !== $portfolio->id
                || $mediaUpload->user_id !== $userId
                || $mediaUpload->public_id !== $upload['public_id']
                || $mediaUpload->resource_type !== $upload['resource_type'];

            $secureUrl = (string) $upload['secure_url'];
            $deliveryPrefix = $cloudinary->deliveryUrl($upload['resource_type']);
            if (! str_starts_with($secureUrl, $deliveryPrefix) || ! str_contains($secureUrl, $upload['public_id'])) {
                $invalid = true;
            }

            if ($invalid) {
                throw ValidationException::withMessages([
                    "uploads.{$index}" => 'Unggahan tidak lagi valid. Muat ulang halaman dan coba lagi.',
                ]);
            }

            $resolved[] = [
                'media_upload_id' => (int) $upload['media_upload_id'],
                'public_id' => $upload['public_id'],
                'secure_url' => $secureUrl,
                'resource_type' => $upload['resource_type'],
            ];
        }

        return $resolved;
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
        $portfolio->update(['is_published' => ! $portfolio->is_published]);

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
