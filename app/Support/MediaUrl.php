<?php

namespace App\Support;

class MediaUrl
{
    /**
     * Корене-відносний URL ("/storage/...") з абсолютного.
     * Потрібно, бо APP_URL (http://localhost) не збігається з хостом
     * дев-сервера (127.0.0.1:8000) — інакше зображення не вантажаться.
     */
    public static function rel(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        return parse_url($url, PHP_URL_PATH) ?: $url;
    }
}
