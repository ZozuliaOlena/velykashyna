{{-- resources/views/partials/product-card.blade.php
     Картка товару (єдиний дизайн для каталогу та головної).
     $p: size, brand, model, constr, li, app, stock, price_mode, price, promos[].
     Зображення: img_url (готовий URL з БД) АБО img (ім'я файлу в /images/wheels).
     Іконки/лого: app_icon_url, brand_logo_url — необов'язкові (перекривають дефолт).
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
])
{{-- Безкоштовна доставка завжди першою (зверху), далі — решта --}}
@php($promoOrder = ['Безкоштовна доставка' => 0, 'Акція' => 1, 'Знижка' => 2, 'Запитуй знижку' => 3])
@php($promos = collect($p['promos'] ?? [])->sortBy(fn ($x) => $promoOrder[$x] ?? 99)->values()->all())

<div class="cat-prod" x-data="{ fav: false }">
    <div class="cat-prod__media">
        @if (!empty($promos))
        <div class="cat-prod__promos">
            @foreach ($promos as $promo)
            @php($pc = $promoConfig[$promo] ?? ['s' => 'sale', 'i' => ''])
            <span class="promo promo--{{ $pc['s'] }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $pc['i'] !!}</svg>
                <span>{{ $promo }}</span>
            </span>
            @endforeach
        </div>
        @endif

        <button type="button" class="cat-prod__fav" :class="{ active: fav }" @click="fav = !fav" aria-label="В обране">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path
                    d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
            </svg>
        </button>

        <span class="cat-prod__brand">
            @if ($brandLogoSrc)
            <img src="{{ $brandLogoSrc }}" alt="{{ $p['brand'] }}" />
            @else
            {{ $p['brand'] }}
            @endif
        </span>

        <span class="cat-prod__stock {{ $p['stock'] ? 'in' : 'order' }}">
            {{ $p['stock'] ? 'В наявності' : 'Під замовлення' }}
        </span>

        @if ($imgSrc)
        <img class="cat-prod__photo" src="{{ $imgSrc }}" alt="{{ $p['brand'] }} {{ $p['model'] }}" loading="lazy" />
        @else
        <span class="cat-prod__photo cat-prod__photo--ph mask-ico"
            style="-webkit-mask-image:url('/images/svg/tehnics/wheel.svg');mask-image:url('/images/svg/tehnics/wheel.svg')"
            aria-hidden="true"></span>
        @endif
    </div>

    <div class="cat-prod__body">
        <div class="cat-prod__info">
            @if ($brandLogoSrc)
            <span class="cat-prod__brand-inline">
                <img src="{{ $brandLogoSrc }}" alt="{{ $p['brand'] }}" />
            </span>
            @endif
            <div class="cat-prod__head">
                <div class="cat-prod__size">{{ $p['size'] }}</div>
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

            {{-- Характеристики у вигляді колонок — видно лише у режимі списку --}}
            @php($specCol = trim(($p['constr'] ?? '') . (!empty($p['spec']) ? ', ' . $p['spec'] : ''), ', '))
            <div class="cat-prod__listspecs">
                @if (!empty($p['sku']))
                <div class="lf"><span class="lf-label">Артикул</span><span class="lf-val">{{ $p['sku'] }}</span></div>
                @endif
                <div class="lf"><span class="lf-label">Розмір</span><span class="lf-val">{{ $p['size'] ?: '—' }}</span></div>
                <div class="lf"><span class="lf-label">Бренд</span><span class="lf-val">{{ $p['brand'] ?: '—' }}</span></div>
                <div class="lf"><span class="lf-label">Профіль</span><span class="lf-val">{{ $p['model'] ?: '—' }}</span></div>
                <div class="lf"><span class="lf-label">LI / SI / PR</span><span class="lf-val">{{ $p['li'] ?: '—' }}</span></div>
                <div class="lf"><span class="lf-label">Специфікація</span><span class="lf-val">{{ $specCol ?: '—' }}</span></div>
            </div>

            {{-- Наявність + промо текстом (видно у режимі списку) --}}
            <div class="cat-prod__meta">
                <span class="cat-prod__stockline {{ $p['stock'] ? 'in' : 'order' }}">
                    {{ $p['stock'] ? 'В наявності' : 'Під замовлення' }}
                </span>
                @if (!empty($promos))
                @foreach ($promos as $promo)
                @php($pc = $promoConfig[$promo] ?? ['s' => 'sale', 'i' => ''])
                <span class="promo promo--{{ $pc['s'] }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $pc['i'] !!}</svg>
                    <span>{{ $promo }}</span>
                </span>
                @endforeach
                @endif
            </div>
        </div>

        @php($oldPrice = $p['old_price'] ?? null)
        @php($cur = $p['cur'] ?? 'грн')
        <div class="cat-prod__buy">
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

            <div class="cat-prod__footer">
                <a href="#" class="btn btn--outline cat-prod__more">Переглянути</a>
                <button type="button" class="cat-prod__cart" aria-label="Додати в кошик">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1" />
                        <circle cx="20" cy="21" r="1" />
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                    </svg>
                </button>
                <a href="tel:{{ config('site.contacts.phone_href') }}"
                    class="btn btn--primary cat-prod__consult">Консультація</a>
            </div>
        </div>
    </div>
</div>
