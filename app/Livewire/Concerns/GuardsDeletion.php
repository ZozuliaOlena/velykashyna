<?php

namespace App\Livewire\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Захист від видалення того, що десь використовується, зі ЗРОЗУМІЛИМ
 * повідомленням: показує, ДЕ саме об'єкт задіяно (кілька прикладів за назвою
 * + скільки всього), щоб адміністратору було очевидно, що спершу відв'язати.
 */
trait GuardsDeletion
{
    /**
     * Якщо хоч один із переданих запитів має записи - показує помилку
     * зі списком і повертає true (тоді у виклику робимо return, не видаляючи).
     *
     * @param  string  $subject  людяна назва об'єкта (напр. назва характеристики)
     * @param  array<string, Builder>  $usedIn  мітка => запит зв'язаних записів
     */
    protected function blockIfUsed(string $subject, array $usedIn, string $nameColumn = 'name'): bool
    {
        $lines = [];

        foreach ($usedIn as $label => $query) {
            $total = (clone $query)->count();
            if ($total === 0) {
                continue;
            }

            $sample = (clone $query)->orderBy($nameColumn)->limit(5)
                ->pluck($nameColumn)->filter()->implode(', ');
            $rest = $total - min(5, $total);

            $lines[] = $label . ': ' . $sample . ($rest > 0 ? " та ще {$rest}" : '');
        }

        if ($lines === []) {
            return false;
        }

        session()->flash('error', "Неможливо видалити «{$subject}»: використовується → "
            . implode('; ', $lines) . '. Спершу приберіть ці зв\'язки.');

        return true;
    }
}
