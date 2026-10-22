<?php

namespace App\Services;

use Cloudinary\Api\ApiUtils;
use Cloudinary\Cloudinary;
use Cloudinary\Configuration\ConfigUtils;

class CloudinaryService
{
    protected ?Cloudinary $client = null;

    protected bool $clientResolved = false;

    protected ?array $credentialsCache = null;

    public function client(): ?Cloudinary
    {
        if (! $this->clientResolved) {
            $this->clientResolved = true;
            $url = config('cloudinary.cloud_url');
            if ($url) {
                $this->client = new Cloudinary($url);
            }
        }

        return $this->client;
    }

    /**
     * Cloud name, API key, and API secret. Explicit config wins; otherwise the
     * credentials are parsed from CLOUDINARY_URL.
     *
     * @return array{cloud_name: string, api_key: string, api_secret: string}
     */
    public function credentials(): array
    {
        if ($this->credentialsCache !== null) {
            return $this->credentialsCache;
        }

        $cloudName = (string) (config('cloudinary.cloud_name') ?: '');
        $apiKey = (string) (config('cloudinary.api_key') ?: '');
        $apiSecret = (string) (config('cloudinary.api_secret') ?: '');

        if ($cloudName === '' || $apiKey === '' || $apiSecret === '') {
            $parsed = ConfigUtils::parseCloudinaryUrl(config('cloudinary.cloud_url'));
            $cloud = $parsed['cloud'] ?? [];
            $cloudName = $cloudName !== '' ? $cloudName : (string) ($cloud['cloud_name'] ?? '');
            $apiKey = $apiKey !== '' ? $apiKey : (string) ($cloud['api_key'] ?? '');
            $apiSecret = $apiSecret !== '' ? $apiSecret : (string) ($cloud['api_secret'] ?? '');
        }

        return $this->credentialsCache = [
            'cloud_name' => $cloudName,
            'api_key' => $apiKey,
            'api_secret' => $apiSecret,
        ];
    }

    public function deliveryUrl(string $resourceType): string
    {
        return 'https://res.cloudinary.com/'.$this->credentials()['cloud_name'].'/'.$resourceType.'/upload/';
    }

    public function portfolioFolder(): string
    {
        $root = trim((string) config('cloudinary.folder', 'BengkelRudi'), '/');
        $sub = trim((string) config('cloudinary.portfolio_folder', 'portofolio'), '/');

        return $root === '' ? $sub : ($sub === '' ? $root : $root.'/'.$sub);
    }

    public function videoTransformation(): array
    {
        return (array) config('cloudinary.video_transformation', [
            'format' => 'mp4', 'video_codec' => 'h264', 'quality' => 'auto:eco',
        ]);
    }

    public function transformationString(array $transformation): string
    {
        $prefixes = [
            'format' => 'f_', 'video_codec' => 'vc_', 'quality' => 'q_',
            'bit_rate' => 'br_', 'width' => 'w_', 'height' => 'h_',
            'crop' => 'c_', 'audio_codec' => 'ac_',
        ];

        $parts = [];
        foreach ($transformation as $key => $value) {
            if (isset($prefixes[$key]) && $value !== null && $value !== '') {
                $parts[] = $prefixes[$key].$value;
            }
        }

        return implode(',', $parts);
    }

    public function assetUrl(string $resourceType, string $publicId, ?string $transformation = null): string
    {
        $base = $this->deliveryUrl($resourceType).$publicId;

        return ($transformation === null || $transformation === '')
            ? $base
            : $this->deliveryUrl($resourceType).$transformation.'/'.$publicId;
    }

    public function uploadUrl(string $resourceType = 'image'): string
    {
        return 'https://api.cloudinary.com/v1_1/'.$this->credentials()['cloud_name'].'/'.$resourceType.'/upload';
    }

    /**
     * Signature for a direct browser -> Cloudinary upload. The signed parameters
     * must be sent back to Cloudinary exactly as returned here.
     *
     * @param  array<string, mixed>  $params
     * @return array{signature: string, api_key: string, params: array<string, mixed>}
     */
    public function signUpload(array $params): array
    {
        $credentials = $this->credentials();
        if ($credentials['api_secret'] === '' || $credentials['api_key'] === '') {
            throw new \RuntimeException('Cloudinary is not configured.');
        }

        return [
            'signature' => ApiUtils::signParameters($params, $credentials['api_secret']),
            'api_key' => $credentials['api_key'],
            'params' => $params,
        ];
    }

    public function delete(string $publicId, string $resourceType = 'image'): bool
    {
        $client = $this->client();
        if (! $client) {
            return false;
        }

        $response = $client->uploadApi()->destroy($publicId, [
            'resource_type' => $resourceType,
        ]);

        return in_array($response['result'] ?? '', ['ok', 'not found'], true);
    }

    public function imageSrc(string $publicId, int $width = 768): string
    {
        $transformation = 'f_auto,q_auto:good,c_limit,w_'.$width;

        return $this->assetUrl('image', $publicId, $transformation);
    }

    public function imageSrcset(string $publicId, array $widths = [320, 640, 768, 1024, 1280]): string
    {
        $parts = [];
        foreach ($widths as $width) {
            $parts[] = $this->imageSrc($publicId, (int) $width).' '.(int) $width.'w';
        }

        return implode(', ', $parts);
    }
}

