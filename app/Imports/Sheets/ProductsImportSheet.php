<?php

namespace App\Imports\Sheets;

use App\Imports\CatalogImport;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\CatalogImage;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductType;
use App\Support\CatalogColumns;
use App\Support\Translit;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;

class ProductsImportSheet implements ToCollection
{
    use ParsesSheet;

    /** базові (не-EAV) заголовки */
    private const BASE = [
        'артикул', 'найменування', 'тип', 'виробник', 'типорозмір',
        'посадковий діаметр', 'r/d', 'протектор', 'специфікація',
        'tt/tl', 'pr', 'li/ss', 'наявність', 'режим ціни', 'ціна',
        'валюта', 'знижка', 'тип знижки', 'merchant', 'стан', 'активний',
        'seo title', 'seo description', 'seo h1', 'url', 'категорії', 'фото', 'каталожне фото',
    ];

    public function __construct(private CatalogImport $import)
    {
    }

    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            return;
        }

        $header = $rows->shift();
        $this->mapHeader($header);

        if (! $this->has('артикул')) {
            $this->import->addError('Лист «Товари»: відсутня колонка «Артикул»');
            return;
        }

        // EAV-колонки: заголовки, що збігаються з назвою характеристики
        $attributes = Attribute::all();
        $attrByName = $attributes->keyBy(fn ($a) => mb_strtolower(trim($a->name)));
        $attrColumns = [];
        foreach ($this->cols as $headerName => $idx) {
            if (! in_array($headerName, self::BASE, true) && $attrByName->has($headerName)) {
                $attrColumns[$headerName] = $attrByName->get($headerName);
            }
        }

        foreach ($rows as $row) {
            $sku = $this->val($row, 'Артикул');
            if ($sku === null) {
                continue;
            }

            $product = Product::withTrashed()->where('sku', $sku)->first();
            $isNew = false;

            if ($product) {
                if ($product->trashed()) {
                    $product->restore();
                }
            } else {
                $isNew = true;
                $product = new Product(['sku' => $sku]);

                // обов'язкові для створення
                $name = $this->val($row, 'Найменування');
                $typeCode = $this->val($row, 'Тип');
                if ($name === null || $typeCode === null) {
                    $this->import->addError("Товар {$sku}: для створення потрібні «Найменування» і «Тип»");
                    continue;
                }
                $type = ProductType::where('code', $typeCode)->first();
                if (! $type) {
                    $this->import->addError("Товар {$sku}: невідомий тип «{$typeCode}»");
                    continue;
                }
                $product->name = $name;
                $product->product_type_id = $type->id;
            }

            $this->applyScalars($product, $row);
            $product->save();

            $this->applyCategories($product, $row);
            $this->applyAttributes($product, $row, $attrColumns);

            $isNew ? $this->import->report['products_created']++
                   : $this->import->report['products_updated']++;
        }
    }

    private function applyScalars(Product $product, Collection $row): void
    {
        if ($this->has('найменування') && ($v = $this->val($row, 'Найменування')) !== null) {
            $product->name = $v;
        }
        if ($this->has('тип') && ($v = $this->val($row, 'Тип')) !== null) {
            if ($type = ProductType::where('code', $v)->first()) {
                $product->product_type_id = $type->id;
            }
        }
        if ($this->has('виробник')) {
            $v = $this->val($row, 'Виробник');
            $product->brand_id = $v ? $this->resolveBrand($v) : null;
        }

        $map = [
            'типорозмір'         => 'size_raw',
            'посадковий діаметр' => 'rim_diameter',
            'r/d'                => 'rd_type',
            'протектор'          => 'model',
            'специфікація'       => 'specification',
            'tt/tl'              => 'tube_type',
            'pr'                 => 'ply_rating',
            'li/ss'              => 'load_speed_index',
            'ціна'               => 'price',
            'валюта'             => 'currency',
            'знижка'             => 'discount_value',
            'seo title'          => 'seo_title',
            'seo description'    => 'seo_description',
            'seo h1'             => 'seo_h1',
        ];
        foreach ($map as $col => $field) {
            if ($this->has($col)) {
                $product->{$field} = $this->val($row, $col);
            }
        }

        if ($this->has('наявність')) {
            $product->stock_status = CatalogColumns::stockFromCell($this->val($row, 'Наявність'));
        }
        if ($this->has('режим ціни')) {
            $product->price_mode = CatalogColumns::priceModeFromCell($this->val($row, 'Режим ціни'));
            if ($product->price_mode === 'inquiry') {
                $product->price = null;
            }
        }
        if ($this->has('тип знижки')) {
            $product->discount_type = CatalogColumns::discountFromCell($this->val($row, 'Тип знижки'));
        }
        if ($this->has('merchant')) {
            $product->merchant_enabled = CatalogColumns::boolFromCell($this->val($row, 'Merchant'));
        }
        if ($this->has('стан')) {
            $product->condition = CatalogColumns::conditionFromCell($this->val($row, 'Стан'));
        }
        if ($this->has('активний')) {
            $product->is_active = CatalogColumns::boolFromCell($this->val($row, 'Активний'));
        }
        if ($this->has('url') && ($v = $this->val($row, 'URL')) !== null) {
            $product->slug = $this->uniqueSlug($v, $product->id);
        }

        // Каталожне фото за іменем файлу: один файл — багато товарів.
        // Створюємо запис CatalogImage за іменем (сам файл підвантажується масово окремо).
        if ($this->has('каталожне фото')) {
            $fname = trim((string) $this->val($row, 'Каталожне фото'));
            if ($fname === '') {
                $product->catalog_image_id = null;
            } else {
                $product->catalog_image_id = CatalogImage::firstOrCreate(
                    ['filename' => $fname],
                    ['label' => $fname]
                )->id;
            }
        }
    }

    private function applyCategories(Product $product, Collection $row): void
    {
        if (! $this->has('категорії')) {
            return; // колонки немає — не чіпаємо прив'язки
        }
        $value = $this->val($row, 'Категорії');
        if ($value === null) {
            $product->categories()->sync([]);
            return;
        }
        $ids = [];
        foreach (explode('|', $value) as $path) {
            if ($id = CatalogColumns::resolveCategoryPath($path)) {
                $ids[] = $id;
            }
        }
        $product->categories()->sync(array_unique($ids));
    }

    private function applyAttributes(Product $product, Collection $row, array $attrColumns): void
    {
        foreach ($attrColumns as $attr) {
            // лише власні для типу або спільні
            if ($attr->product_type_id !== null && $attr->product_type_id !== $product->product_type_id) {
                continue;
            }

            $raw = $this->val($row, $attr->name);
            $payload = ['value_text' => null, 'value_number' => null, 'option_id' => null];
            $empty = false;

            switch ($attr->data_type) {
                case 'number':
                    if ($raw === null || ! is_numeric($raw)) {
                        $empty = true;
                    } else {
                        $payload['value_number'] = $raw;
                    }
                    break;
                case 'select':
                    $opt = $raw ? $attr->options()->whereRaw('LOWER(value) = ?', [mb_strtolower($raw)])->first() : null;
                    $opt ? $payload['option_id'] = $opt->id : $empty = true;
                    break;
                case 'boolean':
                    CatalogColumns::boolFromCell($raw) ? $payload['value_text'] = '1' : $empty = true;
                    break;
                default:
                    $raw === null ? $empty = true : $payload['value_text'] = $raw;
            }

            if ($empty) {
                ProductAttributeValue::where('product_id', $product->id)
                    ->where('attribute_id', $attr->id)->delete();
            } else {
                ProductAttributeValue::updateOrCreate(
                    ['product_id' => $product->id, 'attribute_id' => $attr->id],
                    $payload
                );
            }
        }
    }

    private function resolveBrand(string $name): int
    {
        return Brand::firstOrCreate(
            ['name' => $name],
            ['slug' => Str::slug(Translit::uk($name)), 'is_active' => true]
        )->id;
    }

    private function uniqueSlug(string $source, ?int $ignoreId): string
    {
        $base = Str::slug(Translit::uk($source)) ?: 'tovar';
        $slug = $base;
        $i = 1;
        while (
            Product::withTrashed()->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()
        ) {
            $slug = $base . '-' . (++$i);
        }
        return $slug;
    }
}
