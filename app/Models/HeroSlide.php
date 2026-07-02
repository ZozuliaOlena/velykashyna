<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Слайд головного hero-слайдера (керується в адмінці «Налаштування сайту»).
 * Фон слайда — завантажене фото/відео або відео з YouTube.
 */
class HeroSlide extends Model
{
    protected $fillable = [
        'title', 'subtitle', 'bg_type', 'bg_path', 'youtube_url', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    /** Активні слайди у порядку показу. */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /** URL завантаженого фону (фото/відео), або null. */
    public function bgUrl(): ?string
    {
        return $this->bg_path ? '/storage/' . ltrim($this->bg_path, '/') : null;
    }

    /**
     * ID відео з YouTube-посилання (watch?v=, youtu.be/, embed/, shorts/).
     * Повертає null, якщо не вдалося розпізнати.
     */
    public function youtubeId(): ?string
    {
        if (! $this->youtube_url) {
            return null;
        }

        if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/|v/))([\w-]{11})~i', $this->youtube_url, $m)) {
            return $m[1];
        }

        // Якщо вставили просто ID (11 символів).
        if (preg_match('~^[\w-]{11}$~', trim($this->youtube_url))) {
            return trim($this->youtube_url);
        }

        return null;
    }

    /** Масив для фронтенду (заголовок + опис + дані фону). */
    public function toHeroArray(): array
    {
        return [
            'title'    => $this->title,
            'subtitle' => $this->subtitle,
            'type'     => $this->bg_type,
            'src'      => $this->bg_type === 'youtube' ? $this->youtubeId() : $this->bgUrl(),
        ];
    }
}
