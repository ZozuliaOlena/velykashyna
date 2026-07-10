<!doctype html>
<html lang="uk">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    @php
        $metaTitleDefault = \App\Models\Setting::get('share_title')
            ?: 'ВЕЛИКА ШИНА - шини для агро, спец та вантажної техніки';
        $metaDescDefault = \App\Models\Setting::get('share_description')
            ?: 'ВЕЛИКА ШИНА - каталог шин, камер та дисків для сільськогосподарської, спеціальної та вантажної техніки. Підбір за розміром, брендом і технікою з 2009 року.';
        $shareImageUrl = url(\App\Models\Setting::get('share_image') ?: '/images/og-default.png');
        $faviconPngUrl = \App\Models\Setting::get('favicon') ?: '/images/apple-touch-icon.png';
        $siteNoindex = (bool) \App\Models\Setting::get('site_noindex');
        // Канонічний URL за замовчуванням - поточний шлях без параметрів фільтрів.
        // Сторінки з «чистими» параметрами (напр. каталог з категорією) перевизначають @section('canonical').
        $canonicalDefault = url()->current();
    @endphp

    <title>@yield('title', $metaTitleDefault)</title>
    <meta name="description" content="@yield('meta_description', $metaDescDefault)" />

    <link rel="canonical" href="@yield('canonical', $canonicalDefault)" />

    @if($siteNoindex)
        <meta name="robots" content="noindex, nofollow" />
    @elseif(View::hasSection('robots'))
        <meta name="robots" content="@yield('robots')" />
    @endif

    @if($gsv = \App\Models\Setting::get('google_site_verification'))
        <meta name="google-site-verification" content="{{ $gsv }}" />
    @endif

    <link rel="icon" href="{{ \App\Models\Setting::get('favicon') ?: '/favicon.ico' }}" sizes="any" />
    <link rel="icon" type="image/png" href="{{ $faviconPngUrl }}" />
    <link rel="apple-touch-icon" href="{{ $faviconPngUrl }}" />
    <meta name="theme-color" content="#d32f2f" />

    <meta property="og:site_name" content="ВЕЛИКА ШИНА" />
    <meta property="og:locale" content="uk_UA" />
    <meta property="og:type" content="@yield('og_type', 'website')" />
    <meta property="og:title" content="@yield('og_title', View::yieldContent('title', $metaTitleDefault))" />
    <meta property="og:description" content="@yield('og_description', View::yieldContent('meta_description', $metaDescDefault))" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:image" content="@yield('og_image', $shareImageUrl)" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="@yield('og_title', View::yieldContent('title', $metaTitleDefault))" />
    <meta name="twitter:description" content="@yield('og_description', View::yieldContent('meta_description', $metaDescDefault))" />
    <meta name="twitter:image" content="@yield('og_image', $shareImageUrl)" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;600&family=Montserrat:wght@400;500;600;700;800&display=swap"
        rel="stylesheet" />

    @vite(['resources/css/app.scss', 'resources/js/app.js'])

    @include('partials.analytics')

    @include('partials.structured-data')

    @stack('head')
</head>

<body class="@yield('body_class')">
    @php($gtmId = trim((string) \App\Models\Setting::get('gtm_container_id')))
    @if($gtmId)
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtmId }}"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    @endif

    @include('partials.header')
    @include('partials.mobile-menu')

    <main>
        @yield('content')
    </main>

    @include('partials.footer')

    <a href="{{ route('contacts') }}" class="fab" aria-label="Зв'язатися з нами"
        x-data="{ show: false }" :class="{ 'is-visible': show }"
        @scroll.window.throttle.100ms="show = window.scrollY > (document.documentElement.scrollHeight - window.innerHeight) * 0.1">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path
                d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
        </svg>
        <span class="fab-label">Зв'язатися</span>
    </a>

    <div class="cart-toast" x-data="cartToast('{{ route('cart') }}')" @cart-added.window="show($event.detail)"
        x-show="open" x-cloak x-transition.opacity.duration.250ms>
        <div class="cart-toast__head">
            <span class="cart-toast__check">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12" />
                </svg>
            </span>
            <img class="cart-toast__img" :src="item.img || '/images/svg/tehnics/wheel.svg'" :alt="item.brand" />
            <div class="cart-toast__info">
                <span class="cart-toast__title">Додано в кошик</span>
                <span class="cart-toast__name" x-text="(item.size + ' ' + (item.brand || '')).trim()"></span>
            </div>
            <button type="button" class="cart-toast__close" @click="close()" aria-label="Закрити">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
        </div>
        <div class="cart-toast__actions">
            <a :href="cartUrl" class="btn btn--primary">Перейти в кошик</a>
            <button type="button" class="btn btn--outline" @click="close()">Продовжити покупки</button>
        </div>
    </div>

    <div class="cart-toast compare-toast" x-data="compareToast()"
        @compare-added.window="showItem($event.detail)" @compare-full.window="showLimit()"
        x-show="open" x-cloak x-transition.opacity.duration.250ms>
        <div class="cart-toast__head">
            <span class="cart-toast__check">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12" />
                </svg>
            </span>
            <img class="cart-toast__img" x-show="!limit" :src="item.img || '/images/svg/tehnics/wheel.svg'" :alt="item.brand" />
            <div class="cart-toast__info">
                <span class="cart-toast__title" x-text="limit ? ('Максимум ' + $store.compare.max + ' шини') : 'Додано до порівняння'"></span>
                <span class="cart-toast__name" x-show="!limit"
                    x-text="((item.type ? item.type + ' ' : '') + (item.size || '') + ' ' + (item.brand || '')).trim()"></span>
                <span class="cart-toast__name" x-show="limit">Спершу приберіть одну шину</span>
            </div>
            <button type="button" class="cart-toast__close" @click="close()" aria-label="Закрити">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
        </div>
        <div class="cart-toast__actions" x-show="!limit">
            <a :href="$store.compare.url" class="btn btn--primary"
                :class="{ 'is-disabled': $store.compare.count < 2 }"
                @click="if ($store.compare.count < 2) $event.preventDefault()">
                Перейти до порівняння
            </a>
            <button type="button" class="btn btn--outline" @click="close()">Продовжити</button>
        </div>
    </div>

    @stack('scripts')
</body>

</html>
