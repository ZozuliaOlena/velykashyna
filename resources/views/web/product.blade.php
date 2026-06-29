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
@php($priceMode = $product->price_mode)
@php($price = $product->effectivePrice())
@php($oldPrice = $product->oldPrice())
@php($cur = $product->currency === 'UAH' ? 'грн' : $product->currency)
@php($promos = $product->cardPromos())
@php($promoStyles = ['Акція' => 'sale', 'Знижка' => 'discount', 'Запитуй знижку' => 'ask', 'Безкоштовна доставка' => 'ship'])
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

@section('title', $product->seo_title ?: $fullName . ' — Велика Шина')
@section('meta_description', $product->seo_description ?: 'Купити ' . $fullName . ' у компанії «Велика Шина». Підбір, консультація та доставка по Україні.')

{{-- Структуровані дані Product (узгоджені з Merchant-фідом) --}}
@push('head')
@php($ld = array_filter([
'@context' => 'https://schema.org',
'@type' => 'Product',
'name' => $fullName,
'sku' => $product->sku,
'mpn' => $product->sku,
'description' => $product->seo_description ?: ('Купити ' . $fullName . ' у «Велика Шина».'),
'image' => collect($images)->map(fn ($u) => url($u))->values()->all(),
'brand' => $product->brand?->name ? ['@type' => 'Brand', 'name' => $product->brand->name] : null,
]))
@if ($priceMode !== 'inquiry' && $price !== null)
@php($ld['offers'] = [
'@type' => 'Offer',
'url' => route('product', $product->slug),
'priceCurrency' => $product->currency ?: 'UAH',
'price' => number_format((float) $price, 2, '.', ''),
'availability' => $inStock ? 'https://schema.org/InStock' : ($product->stock_status === 'on_order' ? 'https://schema.org/BackOrder' : 'https://schema.org/OutOfStock'),
'itemCondition' => 'https://schema.org/NewCondition',
])
@endif
<script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')
<section class="product">
    <div class="container">
        <nav class="breadcrumbs">
            <a href="{{ route('home') }}">Головна</a>
            <span class="sep">/</span>
            <a href="{{ route('catalog') }}">Каталог шин</a>
            <span class="sep">/</span>
            <span class="current">{{ $fullName }}</span>
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

                    @if (!empty($promos))
                    <div class="product-gallery__promos">
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
                        <h1 class="product-title">@if ($typePrefix)<span class="product-title__type {{ $typeCode !== 'tire' ? 'is-accent' : '' }}">{{ $typeName }}</span> @endif{{ $title }}</h1>
                        @if ($subtitle)
                        <p class="product-subtitle">{{ $subtitle }}</p>
                        @endif
                    </div>
                    @if ($brandLogo)
                    <span class="product-brand"><img src="{{ $brandLogo }}" alt="{{ $product->brand?->name }}" /></span>
                    @endif
                </div>

                <div class="product-meta">
                    <span class="product-sku">Артикул: <b>{{ $product->sku }}</b></span>
                    <span class="product-stock {{ $inStock ? 'in' : 'order' }}">
                        {{ $inStock ? 'В наявності' : 'Під замовлення' }}
                    </span>
                </div>

                {{-- Короткі характеристики --}}
                @if (count($specs))
                <ul class="product-highlights">
                    @foreach (array_slice($specs, 0, 5) as $s)
                    <li><span>{{ $s['label'] }}</span><b>{{ $s['value'] }}</b></li>
                    @endforeach
                </ul>
                @endif

                {{-- БЛОК КУПІВЛІ --}}
                <div class="product-buy" x-data="{ qty: 1, item: @js($buyItem), added: false }">
                    <div class="product-price product-price--{{ $priceMode }} {{ $oldPrice ? 'product-price--sale' : '' }}">
                        @if ($priceMode === 'fixed' || $priceMode === 'from')
                        @if ($oldPrice)
                        <span class="product-price__old">
                            @if ($priceMode === 'from')від @endif{{ number_format($oldPrice, 0, '', ' ') }} {{ $cur }}
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

                    <div class="product-buy__row">
                        <div class="qty">
                            <button type="button" @click="qty = Math.max(1, qty - 1)" aria-label="Менше">−</button>
                            <input type="text" x-model.number="qty" readonly />
                            <button type="button" @click="qty = Math.min(1000, qty + 1)" aria-label="Більше">+</button>
                        </div>
                        <button type="button" class="btn btn--primary"
                            @click="$store.cart.add(item, qty); added = true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="21" r="1" /><circle cx="20" cy="21" r="1" />
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                            </svg>
                            {{ $priceMode === 'inquiry' ? 'У кошик (за запитом)' : 'Додати в кошик' }}
                        </button>
                    </div>

                    <a href="{{ route('cart') }}" class="product-buy__tocart" x-show="added" x-cloak x-transition>
                        ✓ Додано — <b>перейти в кошик</b>
                    </a>

                    <div class="product-buy__secondary">
                        <button type="button" class="product-buy__fav" :class="{ active: $store.fav.has(item.id) }"
                            @click="$store.fav.toggle(item)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                            </svg>
                            <span x-text="$store.fav.has(item.id) ? 'В обраному' : 'В обране'">В обране</span>
                        </button>
                        <button type="button" class="product-buy__compare" :class="{ active: $store.compare.has(item.id) }"
                            @click="$store.compare.toggle(item)"
                            :title="$store.compare.full() && !$store.compare.has(item.id) ? 'Максимум ' + $store.compare.max : ''">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="6" y1="20" x2="6" y2="14" />
                                <line x1="12" y1="20" x2="12" y2="4" />
                                <line x1="18" y1="20" x2="18" y2="10" />
                            </svg>
                            <span x-text="$store.compare.has(item.id) ? 'У порівнянні' : 'Порівняти'">Порівняти</span>
                        </button>
                        <a href="tel:{{ config('site.contacts.phone_href') }}" class="btn btn--dark product-buy__call">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                            </svg>
                            {{ config('site.contacts.phone') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ПОВНІ ХАРАКТЕРИСТИКИ --}}
        @if (count($specs))
        <div class="product-section">
            <h2 class="product-section__title">Характеристики</h2>
            <div class="product-specs">
                @foreach ($specs as $s)
                <div class="product-specs__row">
                    <span class="product-specs__label">{{ $s['label'] }}</span>
                    <span class="product-specs__value">{{ $s['value'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- СУМІСНІСТЬ ІЗ ТЕХНІКОЮ --}}
        @if (count($compat))
        <div class="product-section">
            <h2 class="product-section__title">Підходить для техніки</h2>
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
    </div>

    {{-- АЛЬТЕРНАТИВНІ ШИНИ — швидкі переходи у каталог із фільтрами --}}
    @if (count($alternatives))
    <div class="section" style="padding-bottom:0">
        <div class="container">
            <div class="section-head">
                <h2 class="section-title">Альтернативні шини в цьому ж розмірі</h2>
            </div>
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
