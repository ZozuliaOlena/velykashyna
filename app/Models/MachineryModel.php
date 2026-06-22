<?php

// app/Models/MachineryModel.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MachineryModel extends Model
{
    protected $fillable = ['machinery_brand_id', 'machinery_type_id', 'name'];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(MachineryBrand::class, 'machinery_brand_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(MachineryType::class, 'machinery_type_id');
    }

    public function compatibility(): HasMany
    {
        return $this->hasMany(ProductMachineryCompatibility::class);
    }
}
