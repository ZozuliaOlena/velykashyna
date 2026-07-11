<?php

namespace App\Models;

use App\Support\Translit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
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
        
        return SlugOptions::create()
            ->generateSlugsFrom(fn (self $model) => Translit::uk($model->title))
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
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
            ->fit(Fit::Crop, 400, 260)
            ->format('webp')
            ->quality(80)
            ->nonOptimized()
            ->nonQueued();

        $this->addMediaConversion('large')
            ->fit(Fit::Max, 1200, 800)
            ->format('webp')
            ->quality(80)
            ->nonOptimized()
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

    /** Опубліковані статті, відсортовані від найновіших (для публічного блогу). */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_published', true)
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }

    /** Анонс для списку: явний excerpt або автоматично з тексту. */
    public function teaser(int $words = 28): string
    {
        if ($this->excerpt) {
            return $this->excerpt;
        }

        return Str::words(trim(preg_replace('/\s+/', ' ', strip_tags((string) $this->content))), $words, '…');
    }

    /**
     * Автоматичні SEO-теги статті (title / description) з її даних.
     *
     * @return array{title: string, description: string}
     */
    public function defaultSeo(): array
    {
        $title = trim($this->title . ' - Блог | ВЕЛИКА ШИНА');
        $desc  = trim(preg_replace('/\s+/', ' ', strip_tags((string) $this->teaser(32))));

        return [
            'title'       => mb_substr($title, 0, 255),
            'description' => mb_substr($desc, 0, 300),
        ];
    }

    /** Приблизний час читання у хвилинах (≈200 слів/хв). */
    public function readingTime(): int
    {
        $count = Str::wordCount(strip_tags((string) $this->content));

        return max(1, (int) ceil($count / 200));
    }

    /** Дата публікації у форматі «29 червня 2026» (укр. родовий відмінок). */
    public function formattedDate(): ?string
    {
        if (! $this->published_at) {
            return null;
        }

        $months = [
            1 => 'січня', 2 => 'лютого', 3 => 'березня', 4 => 'квітня',
            5 => 'травня', 6 => 'червня', 7 => 'липня', 8 => 'серпня',
            9 => 'вересня', 10 => 'жовтня', 11 => 'листопада', 12 => 'грудня',
        ];

        return $this->published_at->day . ' ' . $months[$this->published_at->month] . ' ' . $this->published_at->year;
    }
}
