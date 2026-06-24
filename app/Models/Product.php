<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Support\Translit;
use Spatie\Image\Enums\Fit;
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
        // Авто-генерація з назви лише при створенні (якщо slug не заданий явно).
        // На оновленні slug не чіпаємо — ним керує форма (редагований URL).
        return SlugOptions::create()
            ->generateSlugsFrom(fn (self $model) => Translit::uk($model->name))
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function registerMediaCollections(): void
    {
        // одне основне фото
        $this->addMediaCollection('main')->singleFile();
        // кілька додаткових
        $this->addMediaCollection('gallery');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // Уніфікація розміру/пропорції: вписуємо у квадрат на білому тлі.
        // Реєструємо лише за наявності графічного драйвера (gd/imagick),
        // щоб завантаження не падало в середовищах без нього.
        if (! extension_loaded('gd') && ! extension_loaded('imagick')) {
            return;
        }

        $this->addMediaConversion('uniform')
            ->fit(Fit::Contain, 800, 800)
            ->background('ffffff')
            ->nonQueued();

        $this->addMediaConversion('thumb')
            ->fit(Fit::Contain, 300, 300)
            ->background('ffffff')
            ->nonQueued();
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

    /**
     * Ціна з урахуванням знижки на поточний момент.
     * Для режиму «за запитом» (inquiry) або без ціни повертає null —
     * саме це значення фіксується в заявці як price_at_request.
     */
    public function effectivePrice(): ?float
    {
        if ($this->price_mode === 'inquiry' || $this->price === null) {
            return null;
        }

        $price = (float) $this->price;

        if ($this->discount_value) {
            if ($this->discount_type === 'percent') {
                $price -= $price * ((float) $this->discount_value) / 100;
            } elseif ($this->discount_type === 'amount') {
                $price -= (float) $this->discount_value;
            }
        }

        return round(max(0, $price), 2);
    }
}
