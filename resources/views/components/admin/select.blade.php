@props(['model', 'placeholder' => '—', 'options' => [], 'live' => true, 'clearable' => true])

{{--
    Кастомний випадний список для фільтрів.
    Панель прив'язана до ширини контролу (left:0;right:0) — тому НІКОЛИ
    не вилазить за рамки на вузьких екранах (нативний <select> цього не вміє).
    Значення синхронізується з Livewire-властивістю через $wire.entangle (live).
--}}
<div class="aselect"
     x-data="{
        open: false,
        value: @js($live) ? $wire.entangle(@js($model)).live : $wire.entangle(@js($model)),
        options: @js(array_values($options)),
        ph: @js($placeholder),
        current() {
            return this.options.find(o => String(o.value) === String(this.value));
        },
        label() {
            const o = this.current();
            return (o && o.label) ? o.label : this.ph;
        }
     }"
     @click.outside="open = false"
     @keydown.escape.window="open = false">

    <button type="button" class="aselect__control" :class="{ 'is-open': open }" @click="open = !open">
        <span class="aselect__value" x-text="label()"></span>
    </button>

    <ul class="aselect__panel" x-show="open" x-cloak x-transition.opacity.duration.120ms>
        @if($clearable)
        <li>
            <button type="button" class="aselect__opt" :class="{ 'is-active': String(value) === '' }"
                @click="value = ''; open = false" x-text="ph"></button>
        </li>
        @endif
        <template x-for="opt in options" :key="String(opt.value)">
            <li>
                <button type="button" class="aselect__opt" :style="opt.style || ''"
                    :class="{ 'is-active': String(value) === String(opt.value) }"
                    @click="value = opt.value; open = false" x-text="opt.label"></button>
            </li>
        </template>
    </ul>
</div>
