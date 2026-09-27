<?php
namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;

class CloudinaryService
{
    protected ?Cloudinary $client = null;

    public function __construct()
    {
        $url = config('cloudinary.cloud_url');
        if ($url) {
            $this->client = new Cloudinary($url);
        }
    }

    public function upload(UploadedFile $file, string $folder = 'BengkelRudi'): array
    {
        if (!$this->client) {
            throw new \RuntimeException('Cloudinary is not configured.');
        }

        $response = $this->client->uploadApi()->upload($file->getRealPath(), [
            'folder' => $folder,
            'resource_type' => 'image',
        ]);

        return [
            'secure_url' => $response['secure_url'],
            'public_id' => $response['public_id'],
        ];
    }

    public function delete(string $publicId): bool
    {
        if (!$this->client) {
            return false;
        }

        $response = $this->client->uploadApi()->destroy($publicId, [
            'resource_type' => 'image',
        ]);

        return ($response['result'] ?? '') === 'ok';
    }
}
