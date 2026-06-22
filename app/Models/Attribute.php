<?php

// app/Models/Attribute.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attribute extends Model
{
    protected $fillable = [
        'product_type_id', 'code', 'name', 'data_type',
        'unit', 'is_filterable', 'sort_order',
    ];

    protected $casts = ['is_filterable' => 'boolean'];

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(AttributeOption::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }
}
