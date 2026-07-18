<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 301-перенаправлення зі старого ЧПУ товару на актуальний. Створюється при
 * зміні slug (напр. коли розділяємо типорозмір), щоб старі посилання й
 * пошукова видача не отримували 404.
 */
class ProductSlugRedirect extends Model
{
    protected $fillable = ['old_slug', 'product_id'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
