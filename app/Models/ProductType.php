<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductType extends Model
{
    protected $fillable = ['code', 'name', 'google_category'];

    /** Дефолтні Google-категорії за кодом типу (fallback, якщо поле порожнє). */
    private const GOOGLE_DEFAULTS = [
        'tire' => 'Vehicles & Parts > Vehicle Parts & Accessories > Motor Vehicle Parts > Motor Vehicle Tires',
        'disk' => 'Vehicles & Parts > Vehicle Parts & Accessories > Motor Vehicle Parts > Motor Vehicle Rims & Wheels',
    ];

    /** Google-категорія для фіду Merchant: задана в адмінці або дефолт за кодом. */
    public function googleCategory(): string
    {
        return $this->google_category
            ?: (self::GOOGLE_DEFAULTS[$this->code]
                ?? 'Vehicles & Parts > Vehicle Parts & Accessories > Motor Vehicle Parts');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(Attribute::class);
    }
}
