@php($c = config('site.contacts'))
@php($chev = '<svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>')

<div class="mobile-menu" x-data x-cloak :class="{ 'is-open': $store.ui.menu }"
    @keydown.escape.window="$store.ui.closeMenu()">
    <div class="mm-top">
        <a href="{{ route('home') }}" @click="$store.ui.closeMenu()">
            <img src="/images/logo.png" alt="ВЕЛИКА ШИНА" />
        </a>
        <div class="mm-top-actions">
            <a href="tel:{{ $c['phone_href'] }}" class="mm-call" aria-label="Подзвонити">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path
                        d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                </svg>
            </a>
            <button class="mm-close" type="button" aria-label="Закрити меню" @click="$store.ui.closeMenu()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
        </div>
    </div>

    <div class="search-field" x-data="liveSearch('{{ route('search.suggest') }}', '{{ route('catalog') }}')">
        <form class="mm-search" action="{{ route('catalog') }}" method="GET" role="search">
            <input type="text" name="q" placeholder="Пошук за розміром або артикулом" autocomplete="off"
                x-model="q" @input="onInput()" />
            <button type="submit" aria-label="Шукати">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
            </button>
        </form>
        @include('partials.search-results')
    </div>

    <div class="mm-quick">
        <a href="{{ route('favorites') }}" @click="$store.ui.closeMenu()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
            </svg>
            <span>Обране</span>
            <span class="mm-quick__badge" x-show="$store.fav.count" x-text="$store.fav.count" x-cloak></span>
        </a>
        <a href="{{ route('compare') }}" :href="$store.compare.url"
            @click="$store.compare.count < 1 ? $event.preventDefault() : $store.ui.closeMenu()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/>
                <path d="m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/>
                <path d="M7 21h10"/>
                <path d="M12 3v18"/>
                <path d="M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2"/>
            </svg>
            <span>Порівняння</span>
            <span class="mm-quick__badge" x-show="$store.compare.count" x-text="$store.compare.count" x-cloak></span>
        </a>
    </div>

    <div class="mm-label">Каталог</div>
    <nav class="mm-links">
        <a href="{{ route('catalog') }}" @click="$store.ui.closeMenu()">Усі товари {!! $chev !!}</a>
        @foreach ($catalogMenu['types'] ?? [] as $t)
        <a href="{{ $t['url'] }}" @click="$store.ui.closeMenu()">{{ $t['name'] }} {!! $chev !!}</a>
        @endforeach
    </nav>

    @if (!empty($catalogMenu['machinery']))
    <div class="mm-label">За технікою</div>
    <nav class="mm-links">
        @foreach (array_slice($catalogMenu['machinery'], 0, 7) as $m)
        <a href="{{ $m['url'] }}" @click="$store.ui.closeMenu()">{{ $m['name'] }} {!! $chev !!}</a>
        @endforeach
    </nav>
    @endif

    <div class="mm-label">Інформація</div>
    <nav class="mm-links">
        <a href="{{ route('blog.index') }}" @click="$store.ui.closeMenu()">Статті {!! $chev !!}</a>
        <a href="{{ route('about') }}" @click="$store.ui.closeMenu()">Про нас {!! $chev !!}</a>
        <a href="{{ route('contacts') }}" @click="$store.ui.closeMenu()">Контакти {!! $chev !!}</a>
    </nav>

    <div class="mm-contacts">
        <a href="tel:{{ $c['phone_href'] }}" class="mm-phone">{{ $c['phone'] }}</a>
        @if(!empty($c['phone2']))
        <a href="tel:{{ $c['phone2_href'] }}" class="mm-phone">{{ $c['phone2'] }}</a>
        @endif
        <p class="mm-note">Ми на зв'язку 24/7</p>
        <a href="{{ route('contacts') }}" class="btn btn--primary btn--block" style="margin-bottom:20px">Замовити дзвінок</a>
        <p style="font-size:12px;color:#9aa0a8;margin-bottom:14px;text-transform:uppercase;letter-spacing:.5px">Ми в
            соцмережах</p>
        @include('partials.socials')
    </div>
</div>
