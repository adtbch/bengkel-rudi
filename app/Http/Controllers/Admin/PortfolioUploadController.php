<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaUpload;
use App\Models\Portfolio;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PortfolioUploadController extends Controller
{
    private const EXTENSIONS = [
        'image' => ['jpg', 'jpeg', 'png', 'webp'],
        'video' => ['mp4', 'webm', 'mov'],
    ];

    /**
     * Sign a single direct browser -> Cloudinary upload. The file bytes never
     * reach this server, so `declared_size` is client-reported and only used to
     * reject obviously oversized uploads early.
     */
    public function sign(Request $request, Portfolio $portfolio, CloudinaryService $cloudinary)
    {
        $data = $request->validate([
            'resource_type' => ['required', Rule::in(['image', 'video'])],
            'declared_size' => ['nullable', 'integer', 'min:1'],
            'filename' => ['nullable', 'string', 'max:255'],
        ]);

        $resourceType = $data['resource_type'];
        $limitBytes = ($resourceType === 'video'
            ? (int) config('cloudinary.max_video_kb', 51200)
            : (int) config('cloudinary.max_image_kb', 10240)) * 1024;

        if (isset($data['declared_size']) && $data['declared_size'] > $limitBytes) {
            return response()->json([
                'message' => $resourceType === 'video'
                    ? 'Video maksimal 50 MB.'
                    : 'Gambar maksimal 10 MB.',
                'errors' => ['declared_size' => [
                    $resourceType === 'video'
                        ? 'Video maksimal 50 MB.'
                        : 'Gambar maksimal 10 MB.',
                ]],
            ], 422);
        }

        $publicId = $this->publicId($resourceType, $data['filename'] ?? null);
        $timestamp = time();
        $params = [
            'public_id' => $publicId,
            'timestamp' => $timestamp,
            'overwrite' => 'false',
        ];

        $signed = $cloudinary->signUpload($params);
        $credentials = $cloudinary->credentials();
        $userId = (int) auth('admin')->id();
        $expiresAt = now()->addMinutes((int) config('cloudinary.token_ttl_minutes', 360));

        $mediaUpload = MediaUpload::create([
            'portfolio_id' => $portfolio->id,
            'public_id' => $publicId,
            'resource_type' => $resourceType,
            'user_id' => $userId,
        ]);

        return response()->json([
            'cloud_name' => $credentials['cloud_name'],
            'api_key' => $credentials['api_key'],
            'upload_url' => $cloudinary->uploadUrl($resourceType),
            'resource_type' => $resourceType,
            'params' => $params,
            'signature' => $signed['signature'],
            'token' => Crypt::encryptString(json_encode([
                'user_id' => $userId,
                'portfolio_id' => $portfolio->id,
                'media_upload_id' => $mediaUpload->id,
                'resource_type' => $resourceType,
                'public_id' => $publicId,
                'expires_at' => $expiresAt->timestamp,
            ], JSON_THROW_ON_ERROR)),
            'media_upload_id' => $mediaUpload->id,
        ]);
    }

    /**
     * Discard an upload that finished on Cloudinary but was not attached to the
     * portfolio (user pressed "Hapus" or closed the tab before saving).
     */
    public function destroy(Portfolio $portfolio, MediaUpload $mediaUpload, CloudinaryService $cloudinary)
    {
        abort_unless(
            $mediaUpload->portfolio_id === $portfolio->id
                && $mediaUpload->user_id === auth('admin')->id(),
            404
        );

        try {
            $cloudinary->delete($mediaUpload->public_id, $mediaUpload->resource_type);
        } catch (\Throwable) {
            // Cleanup is best-effort; media:prune-orphans retries the leftovers.
        }

        $mediaUpload->delete();

        return response()->json(['status' => 'ok']);
    }

    private function publicId(string $resourceType, ?string $filename): string
    {
        $folder = trim((string) config('cloudinary.folder', 'BengkelRudi'), '/').'/portfolio';
        $extension = strtolower(pathinfo((string) $filename, PATHINFO_EXTENSION));
        if (! in_array($extension, self::EXTENSIONS[$resourceType], true)) {
            $extension = $resourceType === 'video' ? 'mp4' : 'jpg';
        }

        return $folder.'/'.Str::uuid()->getHex().'.'.$extension;
    }
}
