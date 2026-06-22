<?php

// app/Models/MachineryType.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MachineryType extends Model
{
    protected $fillable = ['name'];

    public function models(): HasMany
    {
        return $this->hasMany(MachineryModel::class);
    }

    public function compatibility(): HasMany
    {
        return $this->hasMany(ProductMachineryCompatibility::class);
    }
}
