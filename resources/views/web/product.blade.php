{{-- resources/views/web/product.blade.php — детальна картка товару --}}
@extends('layouts.app')

@php($title = $product->size_raw ?: $product->name)
@php($subtitle = trim(($product->brand?->name ? $product->brand->name . ' ' : '') . $product->model))
{{-- Тип товару (Шина / Диск / Камера…) — додаємо перед розміром у заголовку.
     Лише коли заголовок — це типорозмір (інакше назва вже може містити тип). --}}
@php($typeName = $product->productType?->name)
@php($typeCode = $product->productType?->code)
@php($typePrefix = ($typeName && $product->size_raw) ? $typeName . ' ' : '')
@php($fullName = trim($typePrefix . $title . ($subtitle ? ' ' . $subtitle : '')))
{{-- Повне найменування (для заголовка): тип винесено окремим акцентом,
     решта — рядком. --}}
@php($fullDesignation = $product->fullName())
@php($fullRest = ($typeName && str_starts_with($fullDesignation, $typeName)) ? trim(mb_substr($fullDesignation, mb_strlen($typeName))) : $fullDesignation)
{{-- Ціни на сайті — завжди у гривнях (валютні перераховуються за курсом exchange_rate;
     якщо курс не заданий — показуємо «уточнюйте», а не некоректну суму). --}}
@php($priceMode = $product->priceModeForSite())
@php($price = $product->priceUah())
@php($oldPrice = $product->oldPriceUah())
@php($cur = 'грн')
{{-- «Знижка» прибираємо — на фото показуємо бейдж відсотка («-20%»). --}}
@php($promos = collect($product->cardPromos())->reject(fn ($x) => $x === 'Знижка')->values()->all())
@php($discountBadge = $product->discount_type === 'percent' ? $product->discountLabel() : null)
@php($promoStyles = ['Акція' => 'sale', 'Знижка' => 'discount', 'Запитуй знижку' => 'ask', 'Уточніть вашу ціну' => 'ask', 'Безкоштовна доставка' => 'ship', 'Можлива безкоштовна доставка' => 'ship'])
@php($brandLogos = ['Michelin' => 'michelin.svg', 'Continental' => 'continental.svg'])
@php($brandLogo = $product->brand?->logoUrl() ?? (isset($brandLogos[$product->brand?->name]) ? '/images/svg/brands/' . $brandLogos[$product->brand->name] : null))
@php($inStock = $product->stock_status === 'in_stock')
@php($buyItem = [
'id' => $product->id,
'slug' => $product->slug,
'url' => route('product', $product->slug),
'type' => trim($typePrefix),
'size' => $title,
'brand' => $product->brand?->name ?? '',
'model' => $product->model ?? '',
'img' => $images[0] ?? null,
'price' => $price,
'price_mode' => $priceMode,
'cur' => $cur,
'stock' => $inStock,
])

@section('title', $product->seo_title ?: $fullName . ' — ВЕЛИКА ШИНА')
@section('meta_description', $product->seo_description ?: 'Купити ' . $fullName . ' у компанії ВЕЛИКА ШИНА. Підбір, консультація та доставка по Україні.')

{{-- Соц-прев'ю: фото товару замість дефолтної картинки. --}}
@section('og_type', 'product')
@if(!empty($images))
@section('og_image', url($images[0]))
@endif

{{-- Структуровані дані Product (узгоджені з Merchant-фідом) --}}
@push('head')
@php($ld = array_filter([
'@context' => 'https://schema.org',
'@type' => 'Product',
'name' => $fullName,
'sku' => $product->sku,
'mpn' => $product->sku,
'description' => $product->seo_description ?: ('Купити ' . $fullName . ' у ВЕЛИКА ШИНА.'),
'image' => collect($images)->map(fn ($u) => url($u))->values()->all(),
'brand' => $product->brand?->name ? ['@type' => 'Brand', 'name' => $product->brand->name] : null,
]))
@if ($priceMode !== 'inquiry' && $price !== null)
@php($ld['offers'] = [
'@type' => 'Offer',
'url' => route('product', $product->slug),
'priceCurrency' => 'UAH',
'price' => number_format((float) $price, 2, '.', ''),
'availability' => $inStock ? 'https://schema.org/InStock' : ($product->stock_status === 'on_order' ? 'https://schema.org/BackOrder' : 'https://schema.org/OutOfStock'),
'itemCondition' => 'https://schema.org/NewCondition',
])
@endif
<script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>

{{-- Хлібні крихти для Google (BreadcrumbList). --}}
@php($crumbItems = collect([
    ['name' => 'Головна', 'item' => route('home')],
    ['name' => 'Каталог', 'item' => route('catalog')],
])->concat(collect($crumbs)->map(fn ($c) => ['name' => $c['name'], 'item' => url($c['url'])]))
    ->push(['name' => trim($typePrefix . $title), 'item' => route('product', $product->slug)])
    ->values())
@php($breadcrumbLd = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList',
    'itemListElement' => $crumbItems->map(fn ($c, $i) => [
        '@type' => 'ListItem', 'position' => $i + 1, 'name' => $c['name'], 'item' => $c['item'],
    ])->all()])
<script type="application/ld+json">{!! json_encode($breadcrumbLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')
<section class="product">
    <div class="container">
        <nav class="breadcrumbs">
            <a href="{{ route('home') }}">Головна</a>
            <span class="sep">/</span>
            <a href="{{ route('catalog') }}">Каталог</a>
            @foreach ($crumbs as $crumb)
            <span class="sep">/</span>
            <a href="{{ $crumb['url'] }}">{{ $crumb['name'] }}</a>
            @endforeach
            <span class="sep">/</span>
            <span class="current">{{ trim($typePrefix . $title) }}</span>
        </nav>

        <div class="product-top">
            {{-- ГАЛЕРЕЯ --}}
            <div class="product-gallery" x-data="{ active: 0, images: @js($images) }">
                <div class="product-gallery__main">
                    @if (count($images))
                    <img :src="images[active]" alt="{{ $fullName }}" />
                    @else
                    <span class="product-gallery__ph mask-ico"
                        style="-webkit-mask-image:url('/images/svg/tehnics/wheel.svg');mask-image:url('/images/svg/tehnics/wheel.svg')"></span>
                    @endif

                    @if ($discountBadge || !empty($promos))
                    <div class="product-gallery__promos">
                        @if ($discountBadge)
                        <span class="promo promo--disc">{{ $discountBadge }}</span>
                        @endif
                        @foreach ($promos as $promo)
                        <span class="promo promo--{{ $promoStyles[$promo] ?? 'sale' }}">{{ $promo }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>

                @if (count($images) > 1)
                <div class="product-gallery__thumbs">
                    @foreach ($images as $i => $img)
                    <button type="button" :class="{ active: active === {{ $i }} }" @click="active = {{ $i }}">
                        <img src="{{ $img }}" alt="" loading="lazy" />
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- ОСНОВНА ІНФОРМАЦІЯ --}}
            <div class="product-main">
                <div class="product-main__head">
                    <div>
                        <h1 class="product-title">@if ($typeName)<span class="product-title__type {{ ($typeCode ?? '') !== 'tire' ? 'is-accent' : '' }}">{{ $typeName }}</span> @endif{{ $fullRest }}</h1>
                    </div>
                    @if ($brandLogo)
                    <span class="product-brand"><img src="{{ $brandLogo }}" alt="{{ $product->brand?->name }}" /></span>
                    @endif
                </div>

                <div class="product-meta">
                    <span class="product-sku">Артикул: <b>{{ $product->sku }}</b></span>
                </div>

                {{-- Характеристики під назвою: показуємо перші 5, решту — за кнопкою --}}
                @if (count($specs))
                <div class="product-highlights-wrap" x-data="{ open: false }">
                    <ul class="product-highlights">
                        @foreach ($specs as $i => $s)
                        <li @if ($i >= 5) x-show="open" x-cloak @endif><span>{{ $s['label'] }}</span><b>{{ $s['value'] }}</b></li>
                        @endforeach
                    </ul>
                    @if (count($specs) > 5)
                    <button type="button" class="product-highlights__toggle" @click="open = !open" :aria-expanded="open">
                        <span x-text="open ? 'Згорнути характеристики' : 'Усі характеристики'"></span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" :class="{ 'is-open': open }">
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </button>
                    @endif
                </div>
                @endif

                {{-- БЛОК КУПІВЛІ --}}
                <div class="product-buy" x-data="{ qty: 1, item: @js($buyItem), added: false }">
                    <div class="product-buy__head">
                    <div class="product-price product-price--{{ $priceMode }} {{ $oldPrice ? 'product-price--sale' : '' }}">
                        @if ($priceMode === 'fixed' || $priceMode === 'from')
                        @if ($oldPrice)
                        @php($save = $product->toUah($product->savedAmount()))
                        <span class="product-price__oldrow">
                            <span class="product-price__old">
                                @if ($priceMode === 'from')від @endif{{ number_format($oldPrice, 0, '', ' ') }} {{ $cur }}
                            </span>
                            @if ($product->discountLabel())
                            <span class="product-price__disc">{{ $product->discountLabel() }}</span>
                            @endif
                            @if ($save)
                            <span class="product-price__save">Економія {{ number_format($save, 0, '', ' ') }} {{ $cur }}</span>
                            @endif
                        </span>
                        @endif
                        <span class="product-price__now">
                            @if ($priceMode === 'from')<span class="from">від</span>@endif
                            <span class="amount">{{ number_format($price, 0, '', ' ') }}</span>
                            <span class="cur">{{ $cur }}</span>
                        </span>
                        @else
                        <span class="product-price__ask">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                                <line x1="12" y1="17" x2="12.01" y2="17" />
                            </svg>
                            Уточнюйте ціну
                        </span>
                        @endif
                    </div>
                    @php($stockClass = $product->stock_status === 'in_stock' ? 'in' : ($product->stock_status === 'inquiry' ? 'inquiry' : 'order'))
                    <span class="product-buy__stock {{ $stockClass }}">
                        <span class="dot"></span>{{ $product->stockLabel() }}
                    </span>
                    </div>

                    <div class="product-buy__row">
                        <div class="qty">
                            <button type="button" @click="qty = Math.max(1, qty - 1)" aria-label="Менше">−</button>
                            <input type="text" x-model.number="qty" readonly />
                            <button type="button" @click="qty = Math.min(1000, qty + 1)" aria-label="Більше">+</button>
                        </div>

                        <button type="button" class="product-buy__cart" :class="{ 'is-added': $store.cart.has(item.id) }"
                            @click="$store.cart.add(item, qty); added = true"
                            :aria-label="$store.cart.has(item.id) ? 'У кошику' : 'Додати в кошик'"
                            :title="$store.cart.has(item.id) ? 'У кошику' : 'Додати в кошик'">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="21" r="1" /><circle cx="20" cy="21" r="1" />
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                            </svg>
                        </button>

                        <button type="button" class="product-buy__icon" :class="{ active: $store.fav.has(item.id) }"
                            @click="$store.fav.toggle(item)"
                            :aria-label="$store.fav.has(item.id) ? 'В обраному' : 'В обране'"
                            :title="$store.fav.has(item.id) ? 'В обраному' : 'В обране'">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                            </svg>
                        </button>

                        <button type="button" class="product-buy__icon"
                            :class="{ active: $store.compare.has(item.id), 'is-disabled': !$store.compare.has(item.id) && $store.compare.full() }"
                            @click="$store.compare.toggle(item)"
                            :aria-label="$store.compare.has(item.id) ? 'У порівнянні' : 'Порівняти'"
                            :title="$store.compare.has(item.id) ? 'У порівнянні' : ($store.compare.full() ? 'Максимум ' + $store.compare.max : 'Порівняти')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/>
                                <path d="m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/>
                                <path d="M7 21h10"/>
                                <path d="M12 3v18"/>
                                <path d="M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2"/>
                            </svg>
                        </button>
                    </div>

                    <a href="{{ route('cart') }}" class="product-buy__tocart" x-show="added" x-cloak x-transition>
                        ✓ Додано — <b>перейти в кошик</b>
                    </a>
                </div>
            </div>
        </div>

        {{-- Характеристики перенесені праворуч під заголовок (product-highlights). --}}

        {{-- ОПИС ТОВАРУ --}}
        @php($db = $product->description_blocks ?? [])
        @if (!empty($db) || filled($product->description))
        <div class="product-section product-desc" x-data="{ open: false, long: true }"
            x-init="$nextTick(() => { long = $refs.prose.scrollHeight > $refs.prose.clientHeight + 8 })">
            <h2 class="product-section__title">Опис</h2>
            <div class="product-prose" x-ref="prose" :class="{ 'is-clamp': long && !open }">
                @if (!empty($db))
                    {{-- Рендеримо зі структурних блоків: чистимо рядки від
                         зайвих маркерів «•/-», галочки малюємо самі. --}}
                    @php($clean = fn ($s) => trim(preg_replace('/^[\s•\-–·*]+/u', '', (string) $s)))
                    @php($paras = fn ($t) => collect(preg_split('/\r\n|\r|\n/', (string) $t))->map(fn ($l) => trim($l))->filter())

                    @foreach ($paras($db['intro'] ?? '') as $p)
                        <p>{{ $p }}</p>
                    @endforeach

                    @if (!empty($db['advantages']))
                        <h3>Ключові переваги</h3>
                        <ul class="prose-checks">
                            @foreach ($db['advantages'] as $i)
                                @if ($clean($i) !== '')<li>{{ $clean($i) }}</li>@endif
                            @endforeach
                        </ul>
                    @endif

                    @if (!empty($db['vs_competitors']))
                        <h3>Переваги над конкурентами</h3>
                        <ul class="prose-checks">
                            @foreach ($db['vs_competitors'] as $i)
                                @if ($clean($i) !== '')<li>{{ $clean($i) }}</li>@endif
                            @endforeach
                        </ul>
                    @endif

                    @if (filled($db['features'] ?? null))
                        <h3>Особливості експлуатації</h3>
                        @foreach ($paras($db['features']) as $p)<p>{{ $p }}</p>@endforeach
                    @endif

                    @if (filled($db['why_buy'] ?? null))
                        <h3>Чому варто придбати</h3>
                        @foreach ($paras($db['why_buy']) as $p)<p>{{ $p }}</p>@endforeach
                    @endif
                @else
                    {{-- Старий простий опис без блоків — текст із переносами. --}}
                    {!! nl2br(e($product->description)) !!}
                @endif
            </div>
            <button type="button" class="product-desc__toggle" x-show="long" @click="open = !open" :aria-expanded="open" x-cloak>
                <span x-text="open ? 'Згорнути' : 'Читати повністю'"></span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" :class="{ 'is-open': open }">
                    <polyline points="6 9 12 15 18 9" />
                </svg>
            </button>
        </div>
        @endif

        {{-- 3. ДУМКА ЕКСПЕРТА «ВЕЛИКА ШИНА» --}}
        @if (filled($product->expert_note))
        <div class="product-section">
            <div class="expert-note">
                <div class="expert-note__badge">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5" />
                        <path d="M9 18h6" />
                        <path d="M10 22h4" />
                    </svg>
                </div>
                <div class="expert-note__body">
                    <span class="expert-note__title">Думка експерта ВЕЛИКА ШИНА</span>
                    <div class="product-prose">{!! nl2br(e($product->expert_note)) !!}</div>
                </div>
            </div>
        </div>
        @endif

        {{-- 4. СУМІСНІСТЬ ІЗ ТЕХНІКОЮ --}}
        @if (count($compat))
        <div class="product-section">
            <h2 class="product-section__title">Техніка</h2>
            <div class="product-compat">
                @foreach ($compat as $line)
                <span class="product-compat__item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                    {{ $line }}
                </span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- 5. ФОТО «В РОБОТІ» --}}
        @if ($product->fieldPhotos->isNotEmpty())
        <div class="product-section" x-data="{ open: false, src: '', cap: '' }">
            <h2 class="product-section__title">Шини в роботі</h2>
            <div class="field-photos">
                @foreach ($product->fieldPhotos as $fp)
                @php($thumb = $fp->imageUrl('thumb'))
                @if ($thumb)
                @php($mach = $fp->machineryLabel())
                @php($label = trim($mach . ($fp->caption ? ($mach ? ' — ' : '') . $fp->caption : '')))
                <figure class="field-photo">
                    <button type="button" class="field-photo__img"
                        @click="src = '{{ $fp->imageUrl('large') }}'; cap = @js($label); open = true">
                        <img src="{{ $thumb }}" alt="{{ $label ?: $product->model }}" loading="lazy" />
                    </button>
                    @if ($mach || $fp->caption)
                    <figcaption class="field-photo__cap">
                        @if ($fp->machinery_model_id)
                            <a href="{{ route('in-work', $fp->machinery_model_id) }}" class="field-photo__mach"
                                title="Усі застосування «{{ $mach }}» в роботі">{{ $mach }}</a>
                        @elseif ($mach)
                            <span class="field-photo__mach">{{ $mach }}</span>
                        @endif
                        @if ($fp->caption)<span class="field-photo__note">{{ $fp->caption }}</span>@endif
                    </figcaption>
                    @endif
                </figure>
                @endif
                @endforeach
            </div>

            {{-- Лайтбокс --}}
            <div class="about-lightbox" x-show="open" x-cloak x-transition.opacity @click="open = false"
                @keydown.escape.window="open = false">
                <button type="button" class="about-lightbox__close" aria-label="Закрити">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
                <figure class="field-lightbox__fig" @click.stop>
                    <img :src="src" alt="" />
                    <figcaption x-show="cap" x-text="cap"></figcaption>
                </figure>
            </div>
        </div>
        @endif
    </div>

    {{-- АЛЬТЕРНАТИВНІ ШИНИ — швидкі переходи у каталог із фільтрами --}}
    @if (count($alternatives))
    <div class="section" style="padding-bottom:0">
        <div class="container">
            <div class="alt-links">
                @foreach ($alternatives as $a)
                <a href="{{ $a['url'] }}" class="alt-link">
                    <span class="alt-link__text">
                        <span class="alt-link__label">{{ $a['label'] }}</span>
                        <span class="alt-link__value">{{ $a['value'] }}</span>
                    </span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="5" y1="12" x2="19" y2="12" />
                        <polyline points="12 5 19 12 12 19" />
                    </svg>
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- СУПУТНІ ТОВАРИ (камери, вентилі, флапи) --}}
    @if (count($accessories))
    <div class="section" style="padding-bottom:0">
        <div class="container">
            <div class="section-head">
                <h2 class="section-title">Супутні товари</h2>
                <a href="{{ route('catalog', ['type' => ['tube', 'valve', 'flap']]) }}" class="section-link">Усі камери та аксесуари
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="5" y1="12" x2="19" y2="12" />
                        <polyline points="12 5 19 12 12 19" />
                    </svg>
                </a>
            </div>
            <div class="product-grid product-related">
                @foreach ($accessories as $p)
                @include('partials.product-card', ['p' => $p, 'showCountry' => false])
                @endforeach
            </div>
        </div>
    </div>
    @endif
</section>
@endsection
