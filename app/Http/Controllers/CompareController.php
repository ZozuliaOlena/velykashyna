<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CompareController extends Controller
{
    /** Максимум товарів у порівнянні (синхронно з JS-стором). */
    private const MAX = 4;

    public function index(Request $request)
    {
        $ids = collect(explode(',', (string) $request->query('ids', '')))
            ->map(fn ($v) => (int) trim($v))
            ->filter()
            ->unique()
            ->take(self::MAX)
            ->values();

        $products = $ids->isEmpty()
            ? collect()
            : Product::query()
                ->whereIn('id', $ids)
                ->where('is_active', true)
                ->with([
                    'brand', 'productType', 'catalogImage',
                    'attributeValues.attribute', 'attributeValues.option',
                ])
                ->get()
                // зберігаємо порядок, у якому користувач додавав товари
                ->sortBy(fn (Product $p) => $ids->search($p->id))
                ->values();

        return view('web.compare', [
            'products' => $products,
            'cards' => $products->map->toCard()->values()->all(),
            'rows' => $this->rows($products),
            'showFooterCta' => false,
        ]);
    }

    /**
     * Матриця порівняння: рядок = характеристика, значення — по кожному товару.
     * 'differs' = true, якщо значення відрізняються (для підсвічування).
     */
    private function rows($products): array
    {
        if ($products->isEmpty()) {
            return [];
        }

        // Стандартні поля (заголовок → функція отримання значення).
        $standard = [
            'Тип' => fn (Product $p) => $p->productType?->name,
            'Бренд' => fn (Product $p) => $p->brand?->name,
            'Модель / протектор' => fn (Product $p) => $p->model,
            'Розмір' => fn (Product $p) => $p->size_raw,
            'Камерність' => fn (Product $p) => $p->constructionLabel() ?: null,
            'Індекс навантаж./швидк.' => fn (Product $p) => $p->load_speed_index,
            'Норма шарів (PR)' => fn (Product $p) => $p->ply_rating,
            'Посадковий діаметр' => fn (Product $p) => $p->rim_diameter ? 'R' . (int) $p->rim_diameter : null,
            'Специфікація' => fn (Product $p) => $p->specification,
            'Наявність' => fn (Product $p) => $p->stockLabel(),
        ];

        $rows = [];
        foreach ($standard as $label => $getter) {
            $rows[] = $this->buildRow($label, $products->map($getter)->all());
        }

        // Практичні параметри з динамічних атрибутів (Steel Belted, IF/VF,
        // CHO/CFO, тип каркаса тощо) — об'єднання всіх атрибутів товарів.
        $attrOrder = [];
        $perProduct = [];
        foreach ($products as $idx => $product) {
            $perProduct[$idx] = [];
            foreach ($product->attributeValues as $av) {
                $name = $av->attribute?->name;
                if (! $name) {
                    continue;
                }
                $value = $av->option?->value
                    ?? $av->value_text
                    ?? ($av->value_number !== null ? rtrim(rtrim((string) $av->value_number, '0'), '.') : null);
                if ($value === null || $value === '') {
                    continue;
                }
                $unit = $av->attribute?->unit;
                $perProduct[$idx][$name] = trim($value . ' ' . ($unit ?? ''));
                $attrOrder[$name] = true;
            }
        }

        foreach (array_keys($attrOrder) as $name) {
            $values = [];
            foreach ($products as $idx => $product) {
                $values[] = $perProduct[$idx][$name] ?? null;
            }
            $rows[] = $this->buildRow($name, $values);
        }

        return $rows;
    }

    private function buildRow(string $label, array $values): array
    {
        $values = array_map(fn ($v) => ($v === null || $v === '') ? null : (string) $v, $values);
        $distinct = collect($values)->filter()->unique();

        return [
            'label' => $label,
            'values' => $values,
            'differs' => $distinct->count() > 1,
        ];
    }
}
