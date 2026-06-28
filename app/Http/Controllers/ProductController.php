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
        ]);

        return view('web.product', [
            'product' => $product,
            'images' => $this->images($product),
            'specs' => $this->specs($product),
            'compat' => $this->compatibility($product),
            'related' => $this->related($product),
        ]);
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

        $push('Артикул', $product->sku);
        $push('Бренд', $product->brand?->name);
        $push('Модель / протектор', $product->model);
        $push('Розмір', $product->size_raw);
        $push('Тип конструкції', $product->constructionLabel() ?: null);
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

    /** Супутні товари: явні зв'язки, інакше — з тих самих категорій/бренду. */
    private function related(Product $product): array
    {
        $with = ['brand', 'catalogImage', 'machineryCompatibility.machineryType'];

        $items = $product->relatedProducts()
            ->where('is_active', true)
            ->with($with)
            ->take(4)
            ->get();

        if ($items->isEmpty()) {
            $catIds = $product->categories->pluck('id');

            $items = Product::query()
                ->where('is_active', true)
                ->where('id', '!=', $product->id)
                ->when($catIds->isNotEmpty(), fn ($q) => $q->whereHas('categories', fn ($x) => $x->whereIn('categories.id', $catIds)))
                ->when($catIds->isEmpty() && $product->brand_id, fn ($q) => $q->where('brand_id', $product->brand_id))
                ->with($with)
                ->latest()
                ->take(4)
                ->get();
        }

        return $items->map->toCard()->all();
    }
}
