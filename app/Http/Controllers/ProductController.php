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
            'showFooterCta' => false,
        ]);
    }

    /**
     * Ланцюжок категорій товару для «хлібних крихт»: від кореня до
     * найконкретнішої (найглибшої) категорії. Кожна - з посиланням у каталог.
     */
    private function breadcrumbs(Product $product): array
    {
        
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
            $node = $node->parent; 
        }

        return array_reverse($chain);
    }

    /** Усі зображення товару: власні (main → gallery), інакше каталожне. */
    private function images(Product $product): array
    {
        $urls = [];

        // Головне фото: власне (колекція main) або спільне каталожне.
        // Галерея - це ДОДАТКОВІ фото, тож вона не підміняє головне.
        if ($main = $product->getFirstMedia('main')) {
            $urls[] = $this->mediaUrl($main);
        } elseif ($ci = $product->catalogImage?->imageUrl('uniform')) {
            $urls[] = $ci;
        }

        foreach ($product->getMedia('gallery') as $g) {
            $urls[] = $this->mediaUrl($g);
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

        if ($model) {
            $links[] = [
                'label' => 'Всі розміри моделі',
                'value' => trim(($brand ? $brand . ' ' : '') . $model),
                'url' => route('catalog', array_filter(['q' => $model, 'brand' => $brand ? [$brand] : null])),
            ];
        }

        if ($size) {
            $links[] = [
                'label' => 'Усі шини в цьому розмірі',
                'value' => $size,
                'url' => route('catalog', ['size' => [$size]] + $tire),
            ];
        }

        if ($size && $brand) {
            $links[] = [
                'label' => 'Усі шини',
                'value' => $size . ' ' . $brand,
                'url' => route('catalog', ['size' => [$size], 'brand' => [$brand]] + $tire),
            ];
        }

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
     * Супутні товари (камери, вентилі, флапи, кільця): спершу того самого
     * розміру, потім того ж посадкового діаметра, далі - будь-які з цих типів.
     */
    private function accessories(Product $product): array
    {
        $codes = ['tube', 'valve', 'flap', 'ring'];
        $with = ['brand', 'catalogImage', 'productType', 'machineryCompatibility.machineryType'];
        $limit = 8;

        $base = fn () => Product::query()
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->whereHas('productType', fn ($q) => $q->whereIn('code', $codes))
            ->with($with);

        $items = collect();

        if ($product->size_raw) {
            $items = $base()->where('size_raw', $product->size_raw)->take($limit)->get();
        }

        if ($items->count() < $limit && $product->rim_diameter) {
            $more = $base()->whereNotIn('id', $items->pluck('id'))
                ->where('rim_diameter', $product->rim_diameter)
                ->take($limit - $items->count())->get();
            $items = $items->concat($more);
        }

        if ($items->count() < $limit) {
            $more = $base()->whereNotIn('id', $items->pluck('id'))
                ->latest()->take($limit - $items->count())->get();
            $items = $items->concat($more);
        }

        return $items->take($limit)->map->toCard()->all();
    }
}
