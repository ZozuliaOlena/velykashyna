{{-- resources/views/partials/search-results.blade.php
     Дропдаун живого пошуку. Працює в межах x-data="liveSearch(...)". --}}
<div class="search-drop" x-show="open" x-cloak x-transition.opacity.duration.150ms>
    <div class="search-drop__loading" x-show="loading">Шукаємо…</div>

    <template x-if="!loading && items.length === 0 && q.trim().length >= 1">
        <div class="search-drop__empty">Нічого не знайдено за «<span x-text="q"></span>»</div>
    </template>

    <template x-for="it in items" :key="it.url">
        <a :href="it.url" class="search-drop__item">
            <img class="search-drop__img" :src="it.img || '/images/svg/tehnics/wheel.svg'" :alt="it.title" />
            <span class="search-drop__name" x-text="it.title"></span>
            <span class="search-drop__price"
                x-text="(it.price && it.price_mode !== 'inquiry')
                    ? ((it.price_mode === 'from' ? 'від ' : '') + Number(it.price).toLocaleString('uk-UA') + ' ' + it.cur)
                    : 'Уточнюйте'"></span>
        </a>
    </template>

    <a :href="catalogLink()" class="search-drop__all" x-show="total > 0">
        Показати всі результати <span x-text="'(' + total + ')'"></span>
    </a>
</div>
