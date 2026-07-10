<?php

namespace App\Livewire\Concerns;

use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

/**
 * Перетворює flash-повідомлення ('success' / 'error') на браузерну подію 'notify',
 * яку ловить глобальний тост у лейауті адмінки. Працює і для дій у модалці
 * (без перезавантаження), і після redirect (повідомлення читається на новій сторінці).
 *
 * Також перехоплює будь-яку неочікувану помилку в діях компонента і показує
 * зрозумілий тост знизу замість «страшної» сторінки з помилкою.
 */
trait WithAdminToast
{
    public function rendered($view, $html): void
    {
        foreach (['success' => 'success', 'error' => 'error'] as $key => $type) {
            if (session()->has($key)) {
                $this->dispatch('notify', message: (string) session($key), type: $type);
                session()->forget($key);
            }
        }
    }

    /**
     * Livewire викликає цей хук, коли дія кидає виняток. Помилки валідації
     * лишаємо як є (підписи під полями), решту - показуємо тостом і глушимо,
     * щоб користувач ніколи не бачив незрозумілої сторінки з помилкою.
     */
    public function exception($e, $stopPropagation): void
    {
        if ($e instanceof ValidationException) {
            return; // валідація показує помилки під полями - не чіпаємо
        }

        report($e);

        $message = 'Дію не виконано. Спробуйте ще раз.';

        if ($e instanceof QueryException) {
            $sql = $e->getMessage();
            if (str_contains($sql, 'foreign key constraint') || str_contains($sql, 'a foreign key')) {
                $message = 'Не можна видалити: цей запис використовується в інших даних. Спершу приберіть звʼязки.';
            } elseif (str_contains($sql, 'Duplicate entry') || str_contains($sql, '1062')) {
                $message = 'Такий запис уже існує (повторюване значення).';
            }
        }

        $this->dispatch('notify', message: $message, type: 'error');
        $stopPropagation();
    }
}
