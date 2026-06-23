<?php

// app/Models/ProductMachineryCompatibility.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductMachineryCompatibility extends Model
{
    protected $table = 'product_machinery_compatibility';

    protected $fillable = [
        'product_id', 'machinery_type_id', 'machinery_brand_id',
        'machinery_model_id', 'position_id', 'notes',
    ];

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

    public function position(): BelongsTo
    {
        return $this->belongsTo(MachineryPosition::class, 'position_id');
    }
}
