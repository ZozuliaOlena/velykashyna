<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Фото товару «в роботі»: встановлена/інспектована шина на конкретній техніці
 * з підписом. Призначення - реальні приклади застосування, перегляд за технікою.
 */
class ProductFieldPhoto extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'product_id', 'machinery_type_id', 'machinery_brand_id',
        'machinery_model_id', 'caption', 'sort_order',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        if (! extension_loaded('gd') && ! extension_loaded('imagick')) {
            return;
        }

        $this->addMediaConversion('thumb')->fit(Fit::Crop, 300, 300)->format('webp')->quality(80)->nonQueued();
        $this->addMediaConversion('large')->fit(Fit::Max, 1200, 1200)->format('webp')->quality(80)->nonQueued();
    }

    public function imageUrl(string $conversion = 'thumb'): ?string
    {
        $media = $this->getFirstMedia('photo');
        if (! $media) {
            return null;
        }

        return \App\Support\MediaUrl::rel(
            $media->hasGeneratedConversion($conversion)
                ? $media->getUrl($conversion)
                : $media->getUrl()
        );
    }

    /** Підпис техніки: "Комбайн CASE 310" (пропускаючи порожні рівні). */
    public function machineryLabel(): string
    {
        return collect([
            $this->machineryType?->name,
            $this->machineryBrand?->name,
            $this->machineryModel?->name,
        ])->filter()->implode(' ');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function machineryType(): BelongsTo
    {
        return $this->belongsTo(MachineryType::class);
    }

    public function machineryBrand(): BelongsTo
    {
        return $this->belongsTo(MachineryBrand::class);
    }

    public function machineryModel(): BelongsTo
    {
        return $this->belongsTo(MachineryModel::class);
    }
}
