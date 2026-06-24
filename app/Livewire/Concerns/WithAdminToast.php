<?php

namespace App\Livewire\Concerns;

/**
 * Перетворює flash-повідомлення ('success' / 'error') на браузерну подію 'notify',
 * яку ловить глобальний тост у лейауті адмінки. Працює і для дій у модалці
 * (без перезавантаження), і після redirect (повідомлення читається на новій сторінці).
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
}
