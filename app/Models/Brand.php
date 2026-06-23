<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Brand extends Model
{
    protected $fillable = ['name', 'slug', 'logo', 'country', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    /** Повний URL логотипа (або null). Шлях у БД: "brands/xxx.png". */
    public function logoUrl(): ?string
    {
        return $this->logo ? Storage::disk('public')->url($this->logo) : null;
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
