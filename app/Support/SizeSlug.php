<?php

namespace App\Support;

/**
 * Форматування типорозміру для ЧПУ-URL. Str::slug прибирає крапки/слеші
 * («23.5R25» → «235r25»), через що розмір «злипається». Тут ми заздалегідь
 * перетворюємо роздільники всередині розміру на дефіс і відділяємо буквений
 * префікс від цифр, щоб у слагу розмір читався правильно:
 *   23.5R25      → 23-5R25      (→ slug 23-5r25)
 *   VF270/95R32  → VF-270-95R32 (→ slug vf-270-95r32)
 *   710/70R38    → 710-70R38    (→ slug 710-70r38)
 */
class SizeSlug
{
    public static function format(string $size): string
    {
        $s = trim($size);
        if ($s === '') {
            return $s;
        }

        // Провідні літери (VF, IF, LR…) відділяємо від наступних цифр.
        $s = preg_replace('~^([A-Za-z]+)(?=\d)~', '$1-', $s);
        // Крапки / слеші / пробіли всередині розміру → дефіс.
        $s = preg_replace('~[./\s]+~u', '-', $s);
        // Здвоєні та крайні дефіси прибираємо.
        return trim(preg_replace('~-+~', '-', $s), '-');
    }

    /**
     * Підставляє відформатований розмір у рядок-джерело слага (зазвичай назву),
     * щоб у підсумковому слагу розмір був розділений. Якщо розмір порожній або
     * не потребує змін - повертає джерело без змін.
     */
    public static function inSource(string $source, ?string $size): string
    {
        $size = trim((string) $size);
        if ($size === '') {
            return $source;
        }

        $formatted = self::format($size);

        return $formatted === $size ? $source : str_ireplace($size, $formatted, $source);
    }
}
