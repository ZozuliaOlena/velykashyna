<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\MediaUrl;

class ProductController extends Controller
{
    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);

        $product->load([
            'brand', 'catalogImage', 'productType', 'categories',
            'attributeValues.attribute', 'attributeValues.option',
            'machineryCompatibility.machineryType',
            'machineryCompatibility.machineryBrand',
            'machineryCompatibility.machineryModel',
            'machineryCompatibility.position',
            'fieldPhotos.media',
            'fieldPhotos.machineryType',
            'fieldPhotos.machineryBrand',
            'fieldPhotos.machineryModel',
        ]);

        return view('web.product', [
            'product' => $product,
            'images' => $this->images($product),
            'specs' => $this->specs($product),
            'compat' => $this->compatibility($product),
            'crumbs' => $this->breadcrumbs($product),
            'alternatives' => $this->alternatives($product),
            'accessories' => $this->accessories($product),
        ]);
    }

    /**
     * Ланцюжок категорій товару для «хлібних крихт»: від кореня до
     * найконкретнішої (найглибшої) категорії. Кожна - з посиланням у каталог.
     */
    private function breadcrumbs(Product $product): array
    {
        // Найбільш конкретна (глибша) категорія товару.
        $category = $product->categories
            ->sortByDesc(fn ($c) => $c->level ?? 0)
            ->first();

        $chain = [];
        $node = $category;
        $guard = 0;

        while ($node && $guard++ < 12) {
            $chain[] = [
                'name' => $node->name,
                'url' => route('catalog', ['category' => $node->slug]),
            ];
            $node = $node->parent; // піднімаємось до кореня
        }

        return array_reverse($chain);
    }

    /** Усі зображення товару: власні (main → gallery), інакше каталожне. */
    private function images(Product $product): array
    {
        $urls = [];

        if ($main = $product->getFirstMedia('main')) {
            $urls[] = $this->mediaUrl($main);
        }
        foreach ($product->getMedia('gallery') as $g) {
            $urls[] = $this->mediaUrl($g);
        }

        if (empty($urls) && ($ci = $product->catalogImage?->imageUrl('uniform'))) {
            $urls[] = $ci;
        }

        return array_values(array_filter($urls));
    }

    private function mediaUrl($media): ?string
    {
        return MediaUrl::rel(
            $media->hasGeneratedConversion('uniform') ? $media->getUrl('uniform') : $media->getUrl()
        );
    }

    /** Характеристики: основні поля + динамічні атрибути типу товару. */
    private function specs(Product $product): array
    {
        $specs = [];
        $push = function (string $label, $value) use (&$specs) {
            if ($value !== null && $value !== '') {
                $specs[] = ['label' => $label, 'value' => (string) $value];
            }
        };

        // Артикул не дублюємо в характеристиках - він показаний окремим рядком
        // під заголовком товару.
        $push('Бренд', $product->brand?->name);
        $push('Модель / протектор', $product->model);
        $push('Розмір', $product->size_raw);
        $push('TL / TT', $product->tube_type);
        $push('Індекс навантаж./швидк.', $product->load_speed_index);
        $push('Норма шарів (PR)', $product->ply_rating);
        $push('Посадковий діаметр', $product->rim_diameter ? 'R' . (int) $product->rim_diameter : null);
        $push('Специфікація', $product->specification);

        foreach ($product->attributeValues as $av) {
            $value = $av->option?->value
                ?? $av->value_text
                ?? ($av->value_number !== null ? rtrim(rtrim((string) $av->value_number, '0'), '.') : null);

            if ($value !== null && $value !== '') {
                $unit = $av->attribute?->unit;
                $push($av->attribute?->name ?? 'Параметр', trim($value . ' ' . ($unit ?? '')));
            }
        }

        return $specs;
    }

    /** Сумісність із технікою: «Тип Бренд Модель (позиція)». */
    private function compatibility(Product $product): array
    {
        return $product->machineryCompatibility
            ->map(function ($c) {
                $line = trim(implode(' ', array_filter([
                    $c->machineryType?->name,
                    $c->machineryBrand?->name,
                    $c->machineryModel?->name,
                ])));
                if ($c->position) {
                    $line .= " ({$c->position->name})";
                }

                return $line;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Альтернативні шини - швидкі переходи в каталог із попередньо
     * застосованими фільтрами (за моделлю, розміром, брендом, технікою).
     * Кожен елемент: ['label', 'value', 'url']. Порожні дані пропускаємо.
     */
    private function alternatives(Product $product): array
    {
        $isTire = $product->productType?->code === 'tire';
        $tire = $isTire ? ['type' => ['tire']] : [];

        $brand = $product->brand?->name;
        $size = $product->size_raw;
        $model = $product->model;

        $links = [];

        // 1) Усі розміри цієї моделі (пошук за назвою моделі, звужений брендом).
        if ($model) {
            $links[] = [
                'label' => 'Всі розміри моделі',
                'value' => trim(($brand ? $brand . ' ' : '') . $model),
                'url' => route('catalog', array_filter(['q' => $model, 'brand' => $brand ? [$brand] : null])),
            ];
        }

        // 2) Усі шини в цьому розмірі.
        if ($size) {
            $links[] = [
                'label' => 'Усі шини в цьому розмірі',
                'value' => $size,
                'url' => route('catalog', ['size' => [$size]] + $tire),
            ];
        }

        // 3) Усі шини цього розміру та бренду.
        if ($size && $brand) {
            $links[] = [
                'label' => 'Усі шини',
                'value' => $size . ' ' . $brand,
                'url' => route('catalog', ['size' => [$size], 'brand' => [$brand]] + $tire),
            ];
        }

        // 4) Усі шини на сумісну техніку (по кожному типу техніки).
        $machineryTypes = $product->machineryCompatibility
            ->map(fn ($c) => $c->machineryType?->name)
            ->filter()->unique()->values();

        foreach ($machineryTypes as $name) {
            $links[] = [
                'label' => 'Усі шини на',
                'value' => mb_strtolower($name),
                'url' => route('catalog', ['machinery' => [$name]] + $tire),
            ];
        }

        return $links;
    }

    /**
     * Супутні товари - виключно ручний підбір адміністратора (зв'язка
     * relatedProducts). Без автоматичного формування: показуємо лише те, що
     * додано в картці товару, і тільки активні позиції.
     */
    private function accessories(Product $product): array
    {
        return $product->relatedProducts()
            ->where('products.is_active', true)
            ->with(['brand', 'catalogImage', 'productType', 'machineryCompatibility.machineryType'])
            ->get()
            ->map->toCard()
            ->all();
    }
}
