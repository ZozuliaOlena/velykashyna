{{-- resources/views/partials/product-card.blade.php
     Картка товару (єдиний дизайн для каталогу та головної).
     $p: size, brand, model, constr, li, app, stock, price_mode, price, promos[].
     Зображення: img_url (готовий URL з БД) АБО img (ім'я файлу в /images/wheels).
     Іконки/лого: app_icon_url, brand_logo_url - необов'язкові (перекривають дефолт).
     Необов'язково: $showCountry. --}}
@php($brandLogos = ['Michelin' => 'michelin.svg', 'Continental' => 'continental.svg'])
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
@php($appIcons = [
'Трактори' => 'tractor.svg', 'Комбайни' => 'combine.svg', 'Обприскувачі' => 'sprayer.svg',
'Навантажувачі' => 'loaders.svg', 'Спецтехніка' => 'loaders.svg', 'Грейдери' => 'loaders.svg',
'Вантажівки' => 'truck.svg', 'Причіпна' => 'wheel.svg',
])
@php($appIcon = $appIcons[$p['app'] ?? ''] ?? 'wheel.svg')
@php($priceMode = $p['price_mode'] ?? 'inquiry')
@php($price = $p['price'] ?? null)
@php($cur = $p['cur'] ?? 'грн')
@php($showCountry = $showCountry ?? true)

{{-- Уніфіковані джерела (БД-URL має пріоритет над дефолтними шляхами) --}}
@php($imgSrc = $p['img_url'] ?? (!empty($p['img']) ? '/images/wheels/' . $p['img'] : null))
@php($appIconSrc = $p['app_icon_url'] ?? '/images/svg/tehnics/' . $appIcon)
@php($brandLogoSrc = $p['brand_logo_url'] ?? (isset($brandLogos[$p['brand'] ?? '']) ? '/images/svg/brands/' . $brandLogos[$p['brand']] : null))
@php($hasApp = !empty($p['app']) || !empty($p['app_icon_url']))
{{-- Промо: стиль + іконка --}}
@php($promoConfig = [
'Акція' => ['s' => 'sale', 'i' => '<path d="M20.59 13.41 13.42 20.6a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/>'],
'Знижка' => ['s' => 'discount', 'i' => '<line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/>'],
'Запитуй знижку' => ['s' => 'ask', 'i' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>'],
'Безкоштовна доставка' => ['s' => 'ship', 'i' => '<rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>'],
'Можлива безкоштовна доставка' => ['s' => 'ship', 'i' => '<rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>'],
'Уточніть вашу ціну' => ['s' => 'ask', 'i' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>'],
])
{{-- Мітки доставки (перша з них показується зверху зі своєю іконкою). --}}
@php($shippingLabels = ['Безкоштовна доставка', 'Можлива безкоштовна доставка'])
{{-- Безкоштовна доставка завжди першою (зверху), далі - решта.
     «Знижка» прибираємо - замість неї показуємо бейдж відсотка («-10%»). --}}
@php($promoOrder = ['Безкоштовна доставка' => 0, 'Можлива безкоштовна доставка' => 0, 'Акція' => 1, 'Знижка' => 2, 'Запитуй знижку' => 3, 'Уточніть вашу ціну' => 4])
@php($promos = collect($p['promos'] ?? [])->reject(fn ($x) => $x === 'Знижка')->sortBy(fn ($x) => $promoOrder[$x] ?? 99)->values()->all())

{{-- Компактний об'єкт товару для кошика/обраного --}}
@php($cardItem = [
'id' => $p['id'] ?? null,
'slug' => $p['slug'] ?? null,
'url' => $p['url'] ?? null,
'type' => $p['type'] ?? '',
'size' => $p['size'] ?? '',
'brand' => $p['brand'] ?? '',
'model' => $p['model'] ?? '',
'img' => $imgSrc,
'price' => $p['price'] ?? null,
'price_mode' => $priceMode,
'cur' => $cur,
'stock' => (bool) ($p['stock'] ?? false),
])

<div class="cat-prod" x-data="{ item: @js($cardItem) }">
    {{-- Клік по будь-якій частині картки → сторінка товару (інтерактивні
         кнопки лежать вище за z-index і працюють як зазвичай). --}}
    <a href="{{ $p['url'] ?? '#' }}" class="cat-prod__stretch"
        aria-label="{{ trim(($p['type'] ?? '') . ' ' . ($p['size'] ?? '') . ' ' . ($p['brand'] ?? '')) }}" tabindex="-1"></a>
    <div class="cat-prod__media">
        {{-- Доставка завжди першою (зверху), далі бейдж знижки (-%), далі решта. --}}
        @php($shipping = collect($promos)->first(fn ($x) => in_array($x, $shippingLabels, true)))
        @php($restPromos = array_values(array_filter($promos, fn ($x) => ! in_array($x, $shippingLabels, true))))
        @if ($shipping || !empty($p['discount']) || !empty($restPromos))
        <div class="cat-prod__promos">
            @if ($shipping)
            @php($pc = $promoConfig[$shipping])
            <span class="promo promo--{{ $pc['s'] }}" title="{{ $shipping }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $pc['i'] !!}</svg>
                <span>{{ $shipping }}</span>
            </span>
            @endif
            {{-- Ієрархія «від масивного до дрібного»: доставка → промо → відсоток. --}}
            @foreach ($restPromos as $promo)
            @php($pc = $promoConfig[$promo] ?? ['s' => 'sale', 'i' => ''])
            <span class="promo promo--{{ $pc['s'] }}" title="{{ $promo }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $pc['i'] !!}</svg>
                <span>{{ $promo }}</span>
            </span>
            @endforeach
            @if (!empty($p['discount']))
            <span class="promo cat-prod__disc">{{ $p['discount'] }}</span>
            @endif
        </div>
        @endif

        {{-- На сторінці «Обране» ($favConfirm) - не знімаємо одразу, а просимо
             підтвердження через модалку (подія fav-remove). --}}
        @php($favClick = ($favConfirm ?? false) ? "\$dispatch('fav-remove', item)" : "\$store.fav.toggle(item)")
        <button type="button" class="cat-prod__fav" :class="{ active: $store.fav.has(item.id) }"
            @click="{{ $favClick }}" aria-label="В обране">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path
                    d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
            </svg>
        </button>

        <button type="button" class="cat-prod__compare"
            :class="{ active: $store.compare.has(item.id), 'is-disabled': !$store.compare.has(item.id) && $store.compare.full() }"
            @click="$store.compare.toggle(item)"
            :aria-label="$store.compare.has(item.id) ? 'Прибрати з порівняння' : 'Додати до порівняння'"
            :title="$store.compare.has(item.id) ? 'У порівнянні' : ($store.compare.full() ? 'Максимум ' + $store.compare.max : 'Порівняти')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/>
                <path d="m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/>
                <path d="M7 21h10"/>
                <path d="M12 3v18"/>
                <path d="M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2"/>
            </svg>
        </button>

        <span class="cat-prod__brand">
            @if ($brandLogoSrc)
            <img src="{{ $brandLogoSrc }}" alt="{{ $p['brand'] }}" />
            @else
            {{ $p['brand'] }}
            @endif
        </span>


        <a href="{{ $p['url'] ?? '#' }}" class="cat-prod__photolink" aria-label="{{ $p['brand'] }} {{ $p['model'] }}">
            @if ($imgSrc)
            <img class="cat-prod__photo" src="{{ $imgSrc }}" alt="{{ $p['brand'] }} {{ $p['model'] }}" loading="lazy" />
            @else
            <span class="cat-prod__photo cat-prod__photo--ph mask-ico"
                style="-webkit-mask-image:url('/images/svg/tehnics/wheel.svg');mask-image:url('/images/svg/tehnics/wheel.svg')"
                aria-hidden="true"></span>
            @endif
        </a>
    </div>

    <div class="cat-prod__body">
        <div class="cat-prod__info">
            @if ($brandLogoSrc)
            <span class="cat-prod__brand-inline">
                <img src="{{ $brandLogoSrc }}" alt="{{ $p['brand'] }}" />
            </span>
            @endif
            <div class="cat-prod__head">
                <div class="cat-prod__size">
                    <a href="{{ $p['url'] ?? '#' }}">@if (!empty($p['type']))<span class="cat-prod__size-type {{ ($p['type_code'] ?? '') !== 'tire' ? 'is-accent' : '' }}">{{ $p['type'] }}</span> @endif{{ $p['size'] }}</a>
                </div>
                @if ($country && $showCountry)
                <span class="cat-prod__country">
                    <img class="flag" src="https://flagcdn.com/{{ $country['code'] }}.svg" alt="{{ $country['name'] }}"
                        loading="lazy" />{{ $country['name'] }}
                </span>
                @endif
                @if ($hasApp)
                <span class="cat-prod__app-badge" title="{{ $p['app'] }}" aria-label="{{ $p['app'] }}">
                    <span class="mask-ico"
                        style="-webkit-mask-image:url('{{ $appIconSrc }}');mask-image:url('{{ $appIconSrc }}')"></span>
                </span>
                @endif
            </div>
            <div class="cat-prod__model"><b>{{ $p['brand'] }}</b> {{ $p['model'] }}</div>

            <ul class="cat-prod__specs">
                @if (!empty($p['constr']))<li>{{ $p['constr'] }}</li>@endif
                @if (!empty($p['li']))<li>Індекс навантаження <b>{{ $p['li'] }}</b></li>@endif
                @if ($hasApp)<li class="cat-prod__app">{{ $p['app'] }}</li>@endif
            </ul>

            {{-- Характеристики у вигляді колонок (прайс-таблиця) - видно лише
                 у режимі списку. Набір колонок фіксований, щоб значення в усіх
                 рядках вишиковувались стовпчик під стовпчиком. --}}
            <div class="cat-prod__listspecs">
                <div class="lf"><span class="lf-label">Артикул</span><span class="lf-val">{{ $p['sku'] ?: '-' }}</span></div>
                <div class="lf"><span class="lf-label">Розмір</span><span class="lf-val">{{ $p['size'] ?: '-' }}</span></div>
                <div class="lf"><span class="lf-label">Бренд</span><span class="lf-val">{{ $p['brand'] ?: '-' }}</span></div>
                <div class="lf"><span class="lf-label">Профіль</span><span class="lf-val">{{ $p['model'] ?: '-' }}</span></div>
                <div class="lf"><span class="lf-label">LI / SI / PR</span><span class="lf-val">{{ $p['li'] ?: '-' }}</span></div>
                <div class="lf"><span class="lf-label">TL / TT</span><span class="lf-val">{{ $p['tube'] ?: '-' }}</span></div>
            </div>
        </div>

        @php($oldPrice = $p['old_price'] ?? null)
        <div class="cat-prod__buy">
            {{-- Промо-бейджі (видно у режимі списку) - біля ціни; доставка перша. --}}
            <div class="cat-prod__meta">
                @if (!empty($promos))
                @foreach ($promos as $promo)
                @php($pc = $promoConfig[$promo] ?? ['s' => 'sale', 'i' => ''])
                <span class="promo promo--{{ $pc['s'] }}" title="{{ $promo }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $pc['i'] !!}</svg>
                    <span>{{ $promo }}</span>
                </span>
                @endforeach
                @endif
            </div>
            @php($stockClass = ($p['stock_status'] ?? '') === 'in_stock' ? 'in' : (($p['stock_status'] ?? '') === 'inquiry' ? 'inquiry' : 'order'))
            <span class="cat-prod__avail {{ $stockClass }}">
                <span class="dot"></span>{{ $p['stock_label'] ?? ($p['stock'] ? 'В наявності' : 'Під замовлення') }}
            </span>
            <div class="cat-prod__buyline">
            <div class="cat-prod__price cat-prod__price--{{ $priceMode }} {{ $oldPrice ? 'cat-prod__price--sale' : '' }}">
                @if ($priceMode === 'fixed' || $priceMode === 'from')
                @if ($oldPrice)
                <span class="old">
                    @if ($priceMode === 'from')<span class="from">від</span>@endif
                    {{ number_format($oldPrice, 0, '', ' ') }} {{ $cur }}
                </span>
                @endif
                <span class="now">
                    @if ($priceMode === 'from')<span class="from">від</span>@endif
                    <span class="amount">{{ number_format($price, 0, '', ' ') }}</span>
                    <span class="cur">{{ $cur }}</span>
                </span>
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

            <button type="button" class="cat-prod__cart" :class="{ added: $store.cart.has(item.id) }"
                @click.stop="$store.cart.add(item)"
                :aria-label="$store.cart.has(item.id) ? 'У кошику' : 'Додати в кошик'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1" />
                    <circle cx="20" cy="21" r="1" />
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                </svg>
            </button>
            </div>
        </div>
    </div>
</div>
