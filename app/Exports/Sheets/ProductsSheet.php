<?php

namespace App\Exports\Sheets;

use App\Models\Product;
use App\Support\CatalogColumns;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProductsSheet implements FromArray, WithHeadings, WithTitle
{
    /** @param Collection $attributes динамічні колонки характеристик */
    public function __construct(private Collection $attributes)
    {
    }

    public function title(): string
    {
        return 'Товари';
    }

    public function headings(): array
    {
        return array_merge([
            'Артикул', 'Найменування', 'Тип', 'Виробник', 'Типорозмір',
            'Посадковий діаметр', 'R/D', 'Протектор', 'Специфікація',
            'TT/TL', 'PR', 'LI/SS', 'Наявність', 'Режим ціни', 'Ціна',
            'Валюта', 'Знижка', 'Тип знижки', 'Merchant', 'Активний',
            'SEO Title', 'SEO Description', 'SEO H1', 'URL', 'Категорії', 'Фото', 'Каталожне фото',
        ], $this->attributes->pluck('name')->all());
    }

    public function array(): array
    {
        $products = Product::with([
            'productType', 'brand', 'categories.parent',
            'attributeValues.option', 'media', 'catalogImage',
        ])->orderBy('sku')->get();

        $rows = [];

        foreach ($products as $p) {
            $categories = $p->categories
                ->map(fn ($c) => CatalogColumns::categoryPath($c))
                ->implode(' | ');

            $row = [
                $p->sku,
                $p->name,
                $p->productType?->code,
                $p->brand?->name,
                $p->size_raw,
                $p->rim_diameter,
                $p->rd_type,
                $p->model,
                $p->specification,
                $p->tube_type,
                $p->ply_rating,
                $p->load_speed_index,
                CatalogColumns::stockLabel($p->stock_status),
                CatalogColumns::priceModeLabel($p->price_mode),
                $p->price,
                $p->currency,
                $p->discount_value,
                CatalogColumns::discountLabel($p->discount_type),
                CatalogColumns::boolLabel((bool) $p->merchant_enabled),
                CatalogColumns::boolLabel((bool) $p->is_active),
                $p->seo_title,
                $p->seo_description,
                $p->seo_h1,
                $p->slug,
                $categories,
                $p->getFirstMedia('main')?->file_name,
                $p->catalogImage?->filename,
            ];

            // значення характеристик за attribute_id
            $byAttr = $p->attributeValues->keyBy('attribute_id');
            foreach ($this->attributes as $attr) {
                $v = $byAttr->get($attr->id);
                $row[] = $v ? match ($attr->data_type) {
                    'select'  => $v->option?->value,
                    'number'  => $v->value_number,
                    'boolean' => $v->value_text === '1' ? 'Так' : 'Ні',
                    default   => $v->value_text,
                } : null;
            }

            $rows[] = $row;
        }

        return $rows;
    }
}
