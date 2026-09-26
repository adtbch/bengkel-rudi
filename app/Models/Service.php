<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Service extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'min_price', 'max_price', 'features', 'is_active', 'sort_order'];
    protected $casts = ['features' => 'array', 'is_active' => 'boolean', 'min_price' => 'integer', 'max_price' => 'integer', 'sort_order' => 'integer'];
    public function portfolios(): HasMany { return $this->hasMany(Portfolio::class); }
}
