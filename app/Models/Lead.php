<?php

// app/Models/Lead.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    protected $fillable = [
        'customer_name', 'phone', 'contact_method', 'status', 'manager_comment', 'source',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(LeadItem::class);
    }
}
