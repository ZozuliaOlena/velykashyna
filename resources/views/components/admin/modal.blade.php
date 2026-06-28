@props(['title' => null, 'wide' => false])

{{--
    Уніфікована модалка адмінки.
    Закриття по кліку на тло:
      • якщо у формі нічого не змінювали — закриваємо одразу;
      • якщо є незбережені зміни — спершу запитуємо підтвердження
        (через глобальну <x-admin.confirm-modal/>).
    Усі модалки закриваються через властивість Livewire `showModal`.

    Використання:
        <x-admin.modal title="Заголовок" :wide="true">
            ...поля форми (кожен <div> — клітинка сітки; .is-full — на всю ширину)...
            <x-slot:footer>
                <button wire:click="save">Зберегти</button>
                <button wire:click="$set('showModal', false)">Скасувати</button>
            </x-slot:footer>
        </x-admin.modal>
--}}
<div class="admin-modal"
     x-data="{
        snapshot: '',
        // Знімок стану форми: значення нативних полів + підписи кастомних селектів.
        serialize() {
            const box = this.$refs.box;
            if (! box) return '';
            const parts = [];
            box.querySelectorAll('input, textarea, select').forEach((el) => {
                parts.push((el.type === 'checkbox' || el.type === 'radio') ? (el.checked ? '1' : '0') : el.value);
            });
            box.querySelectorAll('.aselect__value').forEach((el) => parts.push(el.textContent.trim()));
            return parts.join('');
        },
        init() {
            // Після рендеру (в т.ч. реактивних підписів кастомних селектів)
            // фіксуємо початковий стан. Подвійний $nextTick — щоб дочірні
            // Alpine-компоненти встигли відрендерити свої значення.
            this.$nextTick(() => this.$nextTick(() => { this.snapshot = this.serialize(); }));
        },
        attemptClose() {
            if (this.serialize() === this.snapshot) {
                this.close();
                return;
            }
            window.dispatchEvent(new CustomEvent('admin-confirm', {
                detail: {
                    message: 'Є незбережені зміни. Закрити без збереження?',
                    accept: () => this.close(),
                },
            }));
        },
        close() { this.$wire.set('showModal', false); }
     }"
     x-on:click.self="attemptClose()">
    <div x-ref="box" {{ $attributes->merge(['class' => 'admin-modal__box'.($wide ? ' admin-modal__box--wide' : '')]) }}>
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
