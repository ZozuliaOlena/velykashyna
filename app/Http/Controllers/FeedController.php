<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\MediaUrl;

class FeedController extends Controller
{
    /**
     * Фід для Google Merchant Center (RSS 2.0 + namespace g:).
     * Лише товари: активні, merchant_enabled, з фіксованою ціною,
     * брендом і зображенням — щоб пройти модерацію без помилок.
     */
    public function merchant()
    {
        $products = Product::query()
            ->where('is_active', true)
            ->where('merchant_enabled', true)
            ->where('price_mode', 'fixed')
            ->whereNotNull('price')
            ->whereHas('brand')
            ->with(['brand', 'catalogImage', 'categories', 'media'])
            ->get();

        $items = $products->map(fn (Product $p) => $this->item($p))->filter()->values();

        return response()
            ->view('feeds.merchant', ['items' => $items, 'store' => 'Велика Шина'])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    private function item(Product $p): ?array
    {
        $image = $this->image($p);
        if (! $image) {
            return null; // Merchant вимагає зображення товару
        }

        $title = trim(($p->size_raw ?: $p->name) . ' '
            . ($p->brand?->name ? $p->brand->name . ' ' : '') . $p->model);

        $availability = match ($p->stock_status) {
            'in_stock' => 'in_stock',
            'on_order' => 'backorder',
            default => 'out_of_stock',
        };

        $cur = $p->currency ?: 'UAH';

        return [
            'id' => $p->sku,
            'title' => mb_substr($title !== '' ? $title : $p->name, 0, 150),
            'description' => $p->seo_description ?: $this->description($p),
            'link' => route('product', $p->slug),
            'image_link' => $image,
            'availability' => $availability,
            'condition' => 'new',
            'price' => number_format((float) $p->price, 2, '.', '') . ' ' . $cur,
            'sale_price' => $p->hasDiscount()
                ? number_format((float) $p->effectivePrice(), 2, '.', '') . ' ' . $cur
                : null,
            'brand' => $p->brand?->name,
            'mpn' => $p->sku,
            'product_type' => $p->categories->pluck('name')->implode(' > ') ?: null,
            'google_product_category' => 'Vehicles & Parts > Vehicle Parts & Accessories > Motor Vehicle Parts > Motor Vehicle Tires',
        ];
    }

    private function image(Product $p): ?string
    {
        $media = $p->getFirstMedia('main') ?: $p->getFirstMedia('gallery');
        if ($media) {
            $rel = $media->hasGeneratedConversion('uniform') ? $media->getUrl('uniform') : $media->getUrl();

            return url(MediaUrl::rel($rel));
        }

        if ($ci = $p->catalogImage?->imageUrl('uniform')) {
            return url($ci);
        }

        return null;
    }

    private function description(Product $p): string
    {
        $parts = array_filter([
            $p->size_raw,
            $p->brand?->name,
            $p->model,
            $p->constructionLabel() ?: null,
            $p->load_speed_index ? 'Індекс навантаження ' . $p->load_speed_index : null,
        ]);

        return trim(implode(', ', $parts)) ?: $p->name;
    }
}
