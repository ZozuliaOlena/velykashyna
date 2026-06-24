{{-- resources/views/partials/product-card.blade.php
     Картка товару (єдиний дизайн для каталогу та головної).
     Очікує масив $p: size, brand, model, constr, li, app, stock, img. --}}
@php($brandLogos = ['Michelin' => 'michelin.svg', 'Continental' => 'continental.svg'])
{{-- Країна походження бренду (прапор + назва). --}}
@php($brandCountries = [
'Michelin' => ['name' => 'Франція', 'code' => 'fr'],
'Continental' => ['name' => 'Німеччина', 'code' => 'de'],
'BKT' => ['name' => 'Індія', 'code' => 'in'],
'Galaxy' => ['name' => 'Індія', 'code' => 'in'],
'Trelleborg' => ['name' => 'Швеція', 'code' => 'se'],
'Alliance' => ['name' => 'Ізраїль', 'code' => 'il'],
'Mitas' => ['name' => 'Чехія', 'code' => 'cz'],
'Rovelo' => ['name' => 'Китай', 'code' => 'cn'],
'Nexen' => ['name' => 'Корея', 'code' => 'kr'],
])
@php($country = $brandCountries[$p['brand']] ?? null)
{{-- Режим ціни: fixed (фіксована) / from (ціна від) / inquiry (уточнюйте). --}}
@php($priceMode = $p['price_mode'] ?? 'inquiry')
@php($price = $p['price'] ?? null)
<div class="cat-prod" x-data="{ fav: false }">
    <div class="cat-prod__media">
        <span class="cat-prod__stock {{ $p['stock'] ? 'in' : 'order' }}">
            {{ $p['stock'] ? 'В наявності' : 'Під замовлення' }}
        </span>
        <button type="button" class="cat-prod__fav" :class="{ active: fav }" @click="fav = !fav" aria-label="В обране">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path
                    d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
            </svg>
        </button>
        <span class="cat-prod__brand">
            @isset($brandLogos[$p['brand']])
            <img src="/images/svg/brands/{{ $brandLogos[$p['brand']] }}" alt="{{ $p['brand'] }}" />
            @else
            {{ $p['brand'] }}
            @endisset
        </span>
        <img class="cat-prod__photo" src="/images/wheels/{{ $p['img'] }}" alt="{{ $p['brand'] }} {{ $p['model'] }}"
            loading="lazy" />
    </div>
    <div class="cat-prod__body">
        <div class="cat-prod__head">
            <div class="cat-prod__size">{{ $p['size'] }}</div>
            @if ($country)
            <span class="cat-prod__country">
                <img class="flag" src="https://flagcdn.com/{{ $country['code'] }}.svg" alt="{{ $country['name'] }}"
                    loading="lazy" />{{ $country['name'] }}
            </span>
            @endif
        </div>
        <div class="cat-prod__model"><b>{{ $p['brand'] }}</b> {{ $p['model'] }}</div>
        <ul class="cat-prod__specs">
            <li>{{ $p['constr'] }}</li>
            <li>Індекс навантаження <b>{{ $p['li'] }}</b></li>
            <li>Застосування: {{ $p['app'] }}</li>
        </ul>

        <div class="cat-prod__price cat-prod__price--{{ $priceMode }}">
            @if ($priceMode === 'fixed')
            <span class="amount">{{ number_format($price, 0, '', ' ') }}</span>
            <span class="cur">грн</span>
            @elseif ($priceMode === 'from')
            <span class="from">від</span>
            <span class="amount">{{ number_format($price, 0, '', ' ') }}</span>
            <span class="cur">грн</span>
            @else
            <span class="ask">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                    <line x1="12" y1="17" x2="12.01" y2="17" />
                    <circle cx="12" cy="12" r="10" />
                </svg>
                Уточнюйте ціну
            </span>
            @endif
        </div>

        <div class="cat-prod__footer">
            <a href="#" class="btn btn--outline cat-prod__more">Детальніше</a>
            <button type="button" class="cat-prod__cart" aria-label="Додати в кошик">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1" />
                    <circle cx="20" cy="21" r="1" />
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                </svg>
            </button>
        </div>
    </div>
</div>
