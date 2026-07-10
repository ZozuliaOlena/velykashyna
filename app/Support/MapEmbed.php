<?php

namespace App\Support;

/**
 * Нормалізує значення вбудованої карти Google Maps з адмінки.
 * Приймає як «чистий» src-URL, так і повний код <iframe ... src="...">
 * (саме його дає Google → «Поділитися» → «Вставити карту»), і завжди
 * повертає лише URL з українською мовою/регіоном.
 */
class MapEmbed
{
    public static function src(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/src=["\']([^"\']+)["\']/i', $value, $m)) {
            $value = trim($m[1]);
        }

        $value = preg_replace('/(![35]m2!1s)[a-z]{2}(!2s)[a-z]{2}/', '${1}uk${2}ua', $value);

        $value = preg_replace('/([?&]hl=)[a-z]{2}/i', '${1}uk', $value);

        return $value;
    }
}
