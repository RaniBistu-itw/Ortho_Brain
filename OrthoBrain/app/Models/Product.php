<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    public const MAX_IMAGES = 4;

    protected $fillable = [
        'name', 'category_id', 'subcategory_id', 'base_price',
        'number_of_revisions', 'product_term_months', 'from_step', 'to_step',
        'url', 'status', 'description',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(ProductSubcategory::class, 'subcategory_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function coverImage(): ?ProductImage
    {
        return $this->images->first();
    }
}
