<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Setting;
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
        $products = Product::forMerchantFeed()
            ->with(['brand', 'productType', 'catalogImage', 'categories', 'media'])
            ->get();

        $items = $products->map(fn (Product $p) => $this->item($p))->filter()->values();

        $store = Setting::get('merchant_store_name') ?: 'Велика Шина';

        return response()
            ->view('feeds.merchant', ['items' => $items, 'store' => $store])
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

        return [
            'id' => $p->sku,
            'title' => mb_substr($title !== '' ? $title : $p->name, 0, 150),
            'description' => $p->seo_description ?: $this->description($p),
            'link' => route('product', $p->slug),
            'image_link' => $image,
            'availability' => $availability,
            'condition' => in_array($p->condition, ['new', 'used', 'refurbished'], true)
                ? $p->condition
                : 'new',
            'price' => $this->feedPrice((float) $p->price, $p),
            'sale_price' => $p->hasDiscount()
                ? $this->feedPrice((float) $p->effectivePrice(), $p)
                : null,
            'brand' => $p->brand?->name,
            // Немає GTIN/MPN виробника → чесно повідомляємо Google, що
            // ідентифікатора немає (для шин це допустимо). Не вигадуємо mpn.
            'identifier_exists' => 'no',
            'product_type' => $p->categories->pluck('name')->implode(' > ') ?: null,
            'google_product_category' => $p->productType?->googleCategory()
                ?? 'Vehicles & Parts > Vehicle Parts & Accessories > Motor Vehicle Parts > Motor Vehicle Tires',
        ];
    }

    /**
     * Ціна для фіда. Якщо товар у валюті (USD/EUR) і задано курс — перераховуємо
     * в гривні (Google Merchant краще приймає локальну валюту). Інакше — лишаємо
     * ціну у валюті товара. Курс береться з поля exchange_rate (масово виставляється
     * в адмінці для всіх товарів обраної валюти).
     */
    private function feedPrice(float $amount, Product $p): string
    {
        $cur  = $p->currency ?: 'UAH';
        $rate = (float) ($p->exchange_rate ?? 0);

        if ($cur !== 'UAH' && $rate > 0) {
            $amount *= $rate;
            $cur = 'UAH';
        }

        return number_format($amount, 2, '.', '') . ' ' . $cur;
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
