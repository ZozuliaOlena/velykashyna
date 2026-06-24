@props(['title' => null, 'wide' => false])

{{--
    Уніфікована модалка адмінки.
    Використання:
        <x-admin.modal title="Заголовок" :wide="true">
            ...поля форми (кожен <div> — клітинка сітки; .is-full — на всю ширину)...
            <x-slot:footer>
                <button wire:click="save">Зберегти</button>
                <button wire:click="$set('showModal', false)">Скасувати</button>
            </x-slot:footer>
        </x-admin.modal>
--}}
<div class="admin-modal">
    <div {{ $attributes->merge(['class' => 'admin-modal__box'.($wide ? ' admin-modal__box--wide' : '')]) }}>
        @if($title !== null)
            <div class="admin-modal__head"><h2>{{ $title }}</h2></div>
        @endif

        <div class="admin-modal__body">
            {{ $slot }}
        </div>

        @isset($footer)
            <div class="admin-modal__foot">{{ $footer }}</div>
        @endisset
    </div>
</div>
