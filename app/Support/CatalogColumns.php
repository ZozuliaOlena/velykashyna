<?php

namespace App\Support;

use App\Models\Category;

/**
 * Спільні мапи значень та резолвери для імпорту/експорту каталогу.
 * Тримаємо людиночитані підписи в Excel, а в БД - технічні коди.
 */
class CatalogColumns
{
    public const STOCK = [
        'in_stock' => 'В наявності',
        'on_order' => 'Під замовлення',
        'inquiry'  => 'Уточнюйте',
    ];

    public const PRICE_MODE = [
        'fixed'   => 'Є ціна',
        'from'    => 'Ціна від',
        'inquiry' => 'Уточнюйте',
    ];

    public const DISCOUNT = [
        'percent' => 'Відсоток',
        'amount'  => 'Сума',
    ];

    // Стан товару для Google Merchant.
    public const CONDITION = [
        'new'         => 'Новий',
        'used'        => 'Вживаний',
        'refurbished' => 'Відновлений',
    ];

    public static function stockLabel(?string $code): string
    {
        return self::STOCK[$code] ?? self::STOCK['inquiry'];
    }

    public static function stockFromCell(?string $value): string
    {
        return self::codeFromCell($value, self::STOCK, 'inquiry');
    }

    public static function priceModeLabel(?string $code): string
    {
        return self::PRICE_MODE[$code] ?? self::PRICE_MODE['inquiry'];
    }

    public static function priceModeFromCell(?string $value): string
    {
        return self::codeFromCell($value, self::PRICE_MODE, 'inquiry');
    }

    public static function discountLabel(?string $code): string
    {
        return $code ? (self::DISCOUNT[$code] ?? '') : '';
    }

    public static function discountFromCell(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        return self::codeFromCell($value, self::DISCOUNT, 'percent');
    }

    public static function conditionLabel(?string $code): string
    {
        return self::CONDITION[$code] ?? self::CONDITION['new'];
    }

    public static function conditionFromCell(?string $value): string
    {
        return self::codeFromCell($value, self::CONDITION, 'new');
    }

    /** Текст комірки → технічний код (приймає і код, і підпис, без регістру). */
    private static function codeFromCell(?string $value, array $map, string $default): string
    {
        $value = mb_strtolower(trim((string) $value));
        if ($value === '') {
            return $default;
        }
        foreach ($map as $code => $label) {
            if ($value === $code || $value === mb_strtolower($label)) {
                return $code;
            }
        }
        return $default;
    }

    public static function boolFromCell($value): bool
    {
        $value = mb_strtolower(trim((string) $value));
        return in_array($value, ['1', 'так', 'yes', 'y', 'on', 'true', '+', 'да'], true);
    }

    public static function boolLabel(bool $value): string
    {
        return $value ? 'Так' : 'Ні';
    }

    /** Повний шлях категорії: "Шини / Агрошина / Для тракторів". */
    public static function categoryPath(Category $category): string
    {
        $parts = [];
        $node = $category;
        $guard = 0;
        while ($node && $guard++ < 6) {
            array_unshift($parts, $node->name);
            $node = $node->parent;
        }
        return implode(' / ', $parts);
    }

    /**
     * Розгортає шлях "A / B / C" у дерево категорій (створює відсутні рівні)
     * та повертає id листової категорії.
     */
    public static function resolveCategoryPath(string $path): ?int
    {
        $names = array_values(array_filter(array_map('trim', explode('/', $path)), fn ($n) => $n !== ''));
        if (empty($names)) {
            return null;
        }

        $parentId = null;
        $level = 1;
        $leafId = null;

        foreach ($names as $name) {
            if ($level > 4) {
                break;
            }
            $category = Category::firstOrCreate(
                ['parent_id' => $parentId, 'name' => $name],
                ['level' => $level]
            );
            $parentId = $category->id;
            $leafId = $category->id;
            $level++;
        }

        return $leafId;
    }
}
