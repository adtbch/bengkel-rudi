<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
foreach (['Service','Portfolio','PortfolioImage','SiteSetting'] as $name) {
    if (!class_exists('App\\Models\\'.$name)) throw new RuntimeException("Missing model: $name");
}
$s = new App\Models\Service(['features' => ['Cat panel'], 'is_active' => 1]);
if ($s->features !== ['Cat panel'] || $s->is_active !== true) throw new RuntimeException('Service casts failed');
$p = new App\Models\Portfolio(['is_published' => 0]);
if ($p->is_published !== false) throw new RuntimeException('Portfolio cast failed');
if (!$p->images() instanceof Illuminate\Database\Eloquent\Relations\HasMany) throw new RuntimeException('Images relation failed');
if (!$p->service() instanceof Illuminate\Database\Eloquent\Relations\BelongsTo) throw new RuntimeException('Service relation failed');
echo "Model checks passed\n";
