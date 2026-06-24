<?php

namespace App\Imports\Sheets;

use Illuminate\Support\Collection;

/**
 * Ручний розбір листа: перший рядок — заголовки (укр.), далі дані.
 * Дозволяє оновлювати лише ті колонки, що присутні у файлі.
 */
trait ParsesSheet
{
    /** normalized header => column index */
    protected array $cols = [];

    protected function mapHeader(Collection $header): void
    {
        $this->cols = [];
        foreach ($header as $i => $h) {
            $key = mb_strtolower(trim((string) $h));
            if ($key !== '') {
                $this->cols[$key] = $i;
            }
        }
    }

    protected function has(string $name): bool
    {
        return isset($this->cols[mb_strtolower($name)]);
    }

    protected function val(Collection $row, string $name): ?string
    {
        $i = $this->cols[mb_strtolower($name)] ?? null;
        if ($i === null) {
            return null;
        }
        $v = $row->get($i);
        $v = is_string($v) ? trim($v) : $v;

        return ($v === '' || $v === null) ? null : (string) $v;
    }
}
