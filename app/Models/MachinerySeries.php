<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Серія техніки в межах виробника (напр. John Deere «8R»).
 * Об'єднує моделі.
 */
class MachinerySeries extends Model
{
    protected $table = 'machinery_series';

    protected $fillable = ['machinery_brand_id', 'name'];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(MachineryBrand::class, 'machinery_brand_id');
    }

    public function models(): HasMany
    {
        return $this->hasMany(MachineryModel::class);
    }
}
