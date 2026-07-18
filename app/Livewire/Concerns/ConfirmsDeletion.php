<?php

namespace App\Livewire\Concerns;

/**
 * Будує текст підтвердження видалення для атрибута data-confirm.
 * Якщо об'єкт десь задіяний - підставляє зрозуміле попередження з кількістю,
 * щоб адміністратор бачив наслідки ще ДО видалення (а не «зникло мовчки»).
 * Працює в парі з глобальною <x-admin.confirm-modal/> (resources/js/admin.js).
 */
trait ConfirmsDeletion
{
    protected function confirmText(string $name, ?string $usage): string
    {
        return $usage !== null && $usage !== ''
            ? "«{$name}» {$usage}. Все одно видалити?"
            : "Видалити «{$name}»?";
    }
}
