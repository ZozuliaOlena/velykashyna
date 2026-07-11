<?php

namespace App\Models;

use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Каталожне (стокове, "не живе") фото товару.
 * Одне зображення може бути спільним для кількох товарів -
 * призначається масово, а потім кожному товару можна додати власні «живі» фото.
 */
class CatalogImage extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = ['label', 'filename'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** Чи завантажено сам файл зображення. */
    public function hasImage(): bool
    {
        return $this->getFirstMedia('image') !== null;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        if (! extension_loaded('gd') && ! extension_loaded('imagick')) {
            return;
        }

        $this->addMediaConversion('thumb')
            ->fit(Fit::Contain, 300, 300)
            ->background('ffffff')
            ->format('webp')
            ->quality(80)
            ->nonOptimized()
            ->nonQueued();

        $this->addMediaConversion('uniform')
            ->fit(Fit::Contain, 800, 800)
            ->background('ffffff')
            ->format('webp')
            ->quality(80)
            ->nonOptimized()
            ->nonQueued();
    }

    /** URL зображення (thumb за замовчуванням), або null. */
    public function imageUrl(string $conversion = 'thumb'): ?string
    {
        $media = $this->getFirstMedia('image');
        if (! $media) {
            return null;
        }

        return MediaUrl::rel(
            $media->hasGeneratedConversion($conversion)
                ? $media->getUrl($conversion)
                : $media->getUrl()
        );
    }
}
