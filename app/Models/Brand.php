<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Brand extends Model
{
    protected $fillable = ['name', 'slug', 'logo', 'country', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    /**
     * URL логотипа (або null). Шлях у БД: "brands/xxx.png".
     * Повертаємо корене-відносний шлях (/storage/...), щоб працювало
     * незалежно від хоста/порту (localhost:8000, MAMP:8888, домен тощо).
     */
    public function logoUrl(): ?string
    {
        if (! $this->logo) {
            return null;
        }

        $url = Storage::disk('public')->url($this->logo);

        return parse_url($url, PHP_URL_PATH) ?: $url;
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
