<?php

namespace App\Console\Commands;

use App\Models\MediaUpload;
use App\Services\CloudinaryService;
use Illuminate\Console\Command;

class PruneOrphanMedia extends Command
{
    protected $signature = 'media:prune-orphans {--hours= : Umur upload pending sebelum dihapus}';

    protected $description = 'Hapus asset Cloudinary dari upload yang tidak pernah tersimpan ke portfolio';

    public function handle(CloudinaryService $cloudinary): int
    {
        $hours = (int) ($this->option('hours') ?: config('cloudinary.orphan_ttl_hours', 24));
        $cutoff = now()->subHours($hours);

        $pruned = 0;
        $failed = 0;

        MediaUpload::where('created_at', '<', $cutoff)->chunkById(200, function ($uploads) use ($cloudinary, &$pruned, &$failed): void {
            foreach ($uploads as $upload) {
                try {
                    $cloudinary->delete($upload->public_id, $upload->resource_type);
                    $upload->delete();
                    $pruned++;
                } catch (\Throwable $exception) {
                    $failed++;
                    $this->warn("Gagal menghapus {$upload->public_id}: {$exception->getMessage()}");
                }
            }
        });

        $this->info("Upload pending lebih tua dari {$hours} jam: {$pruned} dihapus, {$failed} gagal.");

        return self::SUCCESS;
    }
}
