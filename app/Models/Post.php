<?php

namespace App\Models;

use App\Support\Translit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * Стаття блогу: заголовок, текст і головне фото.
 */
class Post extends Model implements HasMedia
{
    use SoftDeletes, HasSlug, InteractsWithMedia;

    protected $fillable = [
        'title', 'slug', 'excerpt', 'content',
        'is_published', 'published_at',
        'seo_title', 'seo_description',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function getSlugOptions(): SlugOptions
    {
        // slug із заголовка лише при створенні; на оновленні — керує форма.
        return SlugOptions::create()
            ->generateSlugsFrom(fn (self $model) => Translit::uk($model->title))
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function registerMediaCollections(): void
    {
        // одне головне фото статті
        $this->addMediaCollection('image')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        if (! extension_loaded('gd') && ! extension_loaded('imagick')) {
            return;
        }

        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 400, 260)
            ->nonQueued();

        $this->addMediaConversion('large')
            ->fit(Fit::Max, 1200, 800)
            ->nonQueued();
    }

    /** URL головного фото (thumb за замовчуванням), або null. */
    public function imageUrl(string $conversion = 'thumb'): ?string
    {
        $media = $this->getFirstMedia('image');
        if (! $media) {
            return null;
        }

        return \App\Support\MediaUrl::rel(
            $media->hasGeneratedConversion($conversion)
                ? $media->getUrl($conversion)
                : $media->getUrl()
        );
    }
}
