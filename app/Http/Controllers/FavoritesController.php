<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class FavoritesController extends Controller
{
    /**
     * Повертає готові каталожні картки (partials/product-card) для товарів,
     * ID яких зберігаються в localStorage «обраного». Так обране виглядає
     * так само, як каталог (повна назва, характеристики, бейджі).
     */
    public function cards(Request $request)
    {
        $ids = collect(explode(',', (string) $request->query('ids', '')))
            ->map(fn ($v) => (int) trim($v))
            ->filter()
            ->unique()
            ->values();

        $cards = $ids->isEmpty()
            ? []
            : Product::query()
                ->whereIn('id', $ids)
                ->where('is_active', true)
                ->with([
                    'brand', 'productType', 'catalogImage',
                    'machineryCompatibility.machineryType',
                ])
                ->get()
                
                ->sortBy(fn (Product $p) => $ids->search($p->id))
                ->map->toCard()
                ->values()
                ->all();

        return view('web.partials.fav-cards', ['cards' => $cards]);
    }
}
