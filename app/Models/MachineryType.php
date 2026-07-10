<?php

namespace App\Models;

use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class MachineryType extends Model
{
    protected $fillable = ['name', 'icon'];

    /** Корене-відносний URL SVG-іконки (або null). */
    public function iconUrl(): ?string
    {
        if (! $this->icon) {
            return null;
        }

        return MediaUrl::rel(Storage::disk('public')->url($this->icon));
    }

    public function models(): HasMany
    {
        return $this->hasMany(MachineryModel::class);
    }

    public function compatibility(): HasMany
    {
        return $this->hasMany(ProductMachineryCompatibility::class);
    }
}
