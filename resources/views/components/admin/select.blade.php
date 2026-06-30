@props(['model', 'placeholder' => '—', 'options' => [], 'live' => true, 'clearable' => true])

{{--
    Кастомний випадний список для фільтрів.
    Панель прив'язана до ширини контролу (left:0;right:0) — тому НІКОЛИ
    не вилазить за рамки на вузьких екранах (нативний <select> цього не вміє).
    Значення синхронізується з Livewire-властивістю через $wire.entangle (live).
    Якщо опцій багато (> 6) — у списку зʼявляється поле пошуку.
--}}
<div class="aselect" {{ $attributes }}
     x-data="{
        open: false,
        search: '',
        value: @js($live) ? $wire.entangle(@js($model)).live : $wire.entangle(@js($model)),
        options: @js(array_values($options)),
        ph: @js($placeholder),
        current() {
            return this.options.find(o => String(o.value) === String(this.value));
        },
        label() {
            const o = this.current();
            return (o && o.label) ? o.label : this.ph;
        },
        filtered() {
            const q = this.search.trim().toLowerCase();
            if (! q) return this.options;
            return this.options.filter(o => String(o.label ?? '').toLowerCase().includes(q));
        },
        toggle() {
            this.open = ! this.open;
            this.search = '';
            if (this.open) this.$nextTick(() => this.$refs.search && this.$refs.search.focus());
        },
        close() { this.open = false; this.search = ''; }
     }"
     @click.outside="close()"
     @keydown.escape.window="close()">

    <button type="button" class="aselect__control" :class="{ 'is-open': open }" @click="toggle()">
        <span class="aselect__value" x-text="label()"></span>
    </button>

    <ul class="aselect__panel" x-show="open" x-cloak x-transition.opacity.duration.120ms>
        @if(count($options) > 6)
        <li class="aselect__search">
            <input type="text" x-model="search" x-ref="search" placeholder="Пошук..."
                @keydown.enter.prevent>
        </li>
        @endif

        @if($clearable)
        <li x-show="! search">
            <button type="button" class="aselect__opt" :class="{ 'is-active': String(value) === '' }"
                @click="value = ''; close()" x-text="ph"></button>
        </li>
        @endif

        <template x-for="opt in filtered()" :key="String(opt.value)">
            <li>
                <button type="button" class="aselect__opt" :style="opt.style || ''"
                    :class="{ 'is-active': String(value) === String(opt.value) }"
                    @click="value = opt.value; close()" x-text="opt.label"></button>
            </li>
        </template>

        <li x-show="! filtered().length" class="aselect__empty">Нічого не знайдено</li>
    </ul>
</div>
