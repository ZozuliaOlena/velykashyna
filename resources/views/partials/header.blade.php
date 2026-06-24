{{-- resources/views/partials/header.blade.php --}}
@php($c = config('site.contacts'))

@php($phoneIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>')
@php($searchIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>')
@php($cartIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>')
@php($heartIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>')

<header class="site-header {{ ($transparentHeader ?? false) ? 'site-header--overlay' : '' }}"
    x-data="{ scrolled: false }" @scroll.window.throttle.50ms="scrolled = window.scrollY > 80"
    :class="{ 'is-scrolled': scrolled }">
    <div class="container">
        {{-- ============== ДЕСКТОП: верхній ярус ============== --}}
        <div class="header-top">
            <a href="{{ route('home') }}" class="header-logo" aria-label="Велика Шина">
                <img src="/images/logo.png" alt="Велика Шина" />
                <span class="h-tagline">Підбираємо правильні шини з {{ config('site.founded_year') }} року</span>
            </a>

            <form class="header-search" action="{{ route('catalog') }}" method="GET" role="search">
                <input type="text" name="q" placeholder="Пошук за типорозміром або артикулом, напр. 800/65R32"
                    autocomplete="off" />
                <button type="submit" aria-label="Шукати">{!! $searchIcon !!}</button>
            </form>

            <div class="header-actions">
                <div class="header-phone">
                    <span class="hp-row">
                        {!! $phoneIcon !!}
                        <a href="tel:{{ $c['phone_href'] }}">{{ $c['phone'] }}</a>
                    </span>
                    <span class="hp-note">Пн–Пт з 9:00 до 18:00</span>
                </div>

                <div class="header-icons">
                    <a href="#" class="h-icon" aria-label="Обране">{!! $heartIcon !!}</a>
                    <a href="#" class="h-icon" aria-label="Кошик">
                        {!! $cartIcon !!}
                        <span class="badge">2</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- ============== ДЕСКТОП: нижній ярус (навігація + категорії) ============== --}}
        <div class="header-bar">
            <nav class="header-nav">
                <a href="{{ route('catalog') }}" class="is-active">Шини</a>
                <a href="#">Камери</a>
                <a href="#">Новини</a>
                <a href="#">Про нас</a>
                <a href="#">Контакти</a>
            </nav>

            <div class="header-cats">
                <a href="#">
                    <span class="mask-ico" style="--m:url('/images/svg/tehnics/loaders.svg');width:18px;height:18px;color:#e31e24"></span>
                    Спецтехніка
                </a>
                <a href="#">
                    <span class="mask-ico" style="--m:url('/images/svg/tehnics/tractor.svg');width:18px;height:18px;color:#e31e24"></span>
                    Агрошини
                </a>
                <a href="#">
                    <span class="mask-ico" style="--m:url('/images/svg/tehnics/truck.svg');width:18px;height:18px;color:#e31e24"></span>
                    Вантажні
                </a>
            </div>
        </div>

        {{-- ============== МОБІЛЬНИЙ ============== --}}
        <div class="header-mobile">
            <a href="{{ route('home') }}" class="hm-logo" aria-label="Велика Шина">
                <img src="/images/logo.png" alt="Велика Шина" />
            </a>

            <div class="hm-side">
                <button class="hm-btn" type="button" aria-label="Пошук" @click="$store.ui.toggleSearch()">
                    {!! $searchIcon !!}
                </button>
                <a href="tel:{{ $c['phone_href'] }}" class="hm-btn" aria-label="Подзвонити">
                    {!! $phoneIcon !!}
                </a>
                <button class="hm-btn hm-burger" type="button" aria-label="Меню" @click="$store.ui.toggleMenu()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <line x1="3" y1="12" x2="21" y2="12" />
                        <line x1="3" y1="18" x2="21" y2="18" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</header>

@include('partials.search-overlay')
