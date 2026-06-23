{{-- resources/views/partials/mobile-menu.blade.php --}}
@php($c = config('site.contacts'))
@php($chev = '<svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>')

<div class="mobile-menu" x-data x-cloak :class="{ 'is-open': $store.ui.menu }"
    @keydown.escape.window="$store.ui.closeMenu()">
    <div class="mm-top">
        <button class="mm-close" type="button" aria-label="Закрити меню" @click="$store.ui.closeMenu()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
        </button>
        <a href="{{ route('home') }}" @click="$store.ui.closeMenu()">
            <img src="/images/logo.png" alt="Велика Шина" />
        </a>
        <a href="tel:{{ $c['phone_href'] }}" class="mm-call" aria-label="Подзвонити">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path
                    d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
            </svg>
        </a>
    </div>

    <form class="mm-search" action="{{ route('catalog') }}" method="GET" role="search">
        <input type="text" name="q" placeholder="Пошук за розміром або артикулом" autocomplete="off" />
        <button type="submit" aria-label="Шукати">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
        </button>
    </form>

    <div class="mm-label">Каталог</div>
    <nav class="mm-links">
        <a href="{{ route('catalog') }}" @click="$store.ui.closeMenu()">Шини {!! $chev !!}</a>
        <a href="#" @click="$store.ui.closeMenu()">Камери {!! $chev !!}</a>
        <a href="#" @click="$store.ui.closeMenu()">Агрошини {!! $chev !!}</a>
        <a href="#" @click="$store.ui.closeMenu()">Спецтехніка {!! $chev !!}</a>
        <a href="#" @click="$store.ui.closeMenu()">Вантажні шини {!! $chev !!}</a>
    </nav>

    <div class="mm-label">Інформація</div>
    <nav class="mm-links">
        <a href="#" @click="$store.ui.closeMenu()">Новини {!! $chev !!}</a>
        <a href="#" @click="$store.ui.closeMenu()">Про нас {!! $chev !!}</a>
        <a href="#" @click="$store.ui.closeMenu()">Контакти {!! $chev !!}</a>
    </nav>

    <div class="mm-label">Сервіси</div>
    <div class="mm-secondary">
        <a href="#" @click="$store.ui.closeMenu()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
            </svg>
            Співпраця
        </a>
        <a href="#" @click="$store.ui.closeMenu()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20.59 13.41 13.42 20.6a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z" />
                <line x1="7" y1="7" x2="7.01" y2="7" />
            </svg>
            Дилерам
        </a>
        <a href="#" @click="$store.ui.closeMenu()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="7" width="20" height="14" rx="2" />
                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
            </svg>
            Вакансії
        </a>
    </div>

    <div class="mm-contacts">
        <a href="tel:{{ $c['phone_href'] }}" class="mm-phone">{{ $c['phone'] }}</a>
        <p class="mm-note">Пн–Пт з 9:00 до 18:00</p>
        <a href="#" class="btn btn--primary btn--block" style="margin-bottom:20px">Замовити дзвінок</a>
        <p style="font-size:12px;color:#9aa0a8;margin-bottom:14px;text-transform:uppercase;letter-spacing:.5px">Ми в
            соцмережах</p>
        @include('partials.socials')
    </div>
</div>
