<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use SoftDeletes, HasSlug, InteractsWithMedia;

    protected $fillable = [
        'sku', 'product_type_id', 'name', 'brand_id', 'model',
        'size_raw', 'size_width', 'size_profile', 'rim_diameter',
        'rd_type', 'tube_type', 'ply_rating', 'load_speed_index', 'specification',
        'stock_status', 'price_mode', 'price', 'currency', 'exchange_rate',
        'discount_value', 'discount_type', 'merchant_enabled',
        'seo_title', 'seo_description', 'seo_h1', 'slug', 'is_active',
    ];

    protected $casts = [
        'merchant_enabled' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'size_width' => 'decimal:2',
        'size_profile' => 'decimal:2',
        'rim_diameter' => 'decimal:2',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('catalog_photos');
        $this->addMediaCollection('live_photos');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(400)->height(400);
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    public function machineryCompatibility(): HasMany
    {
        return $this->hasMany(ProductMachineryCompatibility::class);
    }

    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'related_products',
            'product_id',
            'related_product_id'
        )->withPivot('type');
    }

    public function relatedTo(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'related_products',
            'related_product_id',
            'product_id'
        )->withPivot('type');
    }

    public function leadItems(): HasMany
    {
        return $this->hasMany(LeadItem::class);
    }
}
