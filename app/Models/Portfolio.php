<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Portfolio extends Model
{
    protected $fillable = ['service_id', 'title', 'slug', 'vehicle_type', 'is_published'];
    protected $casts = ['is_published' => 'boolean'];
    public function getVehicleLabelAttribute(): string
    {
        return match ($this->vehicle_type) {
            'CAR' => 'Mobil',
            'MOTOR' => 'Motor',
            default => 'Kendaraan',
        };
    }
    public function service(): BelongsTo { return $this->belongsTo(Service::class); }
    public function images(): HasMany { return $this->hasMany(PortfolioImage::class)->orderBy('sort_order')->orderBy('id'); }
}
