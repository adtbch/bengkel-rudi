<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class PortfolioImage extends Model
{
    protected $fillable = ['portfolio_id', 'image_url', 'cloudinary_public_id', 'media_type', 'stage', 'sort_order'];
    protected $casts = ['sort_order' => 'integer'];
    public function portfolio(): BelongsTo { return $this->belongsTo(Portfolio::class); }
}
