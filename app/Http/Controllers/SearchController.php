<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /** Живий (випадаючий) пошук у навігації: підказки товарів за запитом. */
    public function suggest(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 1) {
            return response()->json(['items' => [], 'total' => 0]);
        }

        $base = Product::query()
            ->where('is_active', true)
            ->search($q);

        $total = (clone $base)->count();

        $items = $base
            ->with(['brand', 'catalogImage', 'media'])
            ->orderByRaw("stock_status = 'in_stock' DESC")
            ->orderByDesc('id')
            ->take(6)
            ->get()
            ->map(function (Product $p) {
                $c = $p->toCard();

                return [
                    'title' => trim(($c['size'] ?: '') . ' ' . ($c['brand'] ?: '') . ' ' . ($c['model'] ?: '')),
                    'url' => $c['url'],
                    'img' => $c['img_url'],
                    'price' => $c['price'],
                    'price_mode' => $c['price_mode'],
                    'cur' => $c['cur'],
                ];
            })
            ->all();

        return response()->json(['items' => $items, 'total' => $total]);
    }
}
