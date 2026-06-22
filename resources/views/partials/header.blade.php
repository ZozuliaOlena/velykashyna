{{-- resources/views/partials/header.blade.php --}}
<div class="header-wrapper" x-data="{ searchOpen: false, menuOpen: false }">
    <div class="container">
        <header class="top-header">
            <div class="logo-area">
                <a href="{{ route('home') }}">
                    <img src="/images/logo.png" alt="Велика Шина Логотип" class="main-logo" />
                </a>
                <span class="divider"></span>
                <span class="slogan">Експерти у світі великих коліс</span>
            </div>
            <div class="contact-area">
                <div class="phones">
                    <a href="tel:+380679282086">+38 (067) 928 20 86</a>
                </div>
                <div class="header-icons">
                    <div class="header-icon" id="search-btn" @click="searchOpen = !searchOpen" role="button"
                        tabindex="0" @keydown.enter="searchOpen = !searchOpen">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <span>Пошук</span>
                    </div>
                    <div class="header-icon hide-on-mobile">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                        <span>Запит</span>
                    </div>
                    <div class="header-icon burger-menu" id="burger-btn" @click="menuOpen = !menuOpen" role="button"
                        tabindex="0" @keydown.enter="menuOpen = !menuOpen">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                        <span>Меню</span>
                    </div>
                </div>
            </div>
        </header>

        <nav class="main-nav" id="mobile-menu" :class="{ 'active': menuOpen }">
            <div class="mobile-only-phone">
                <a href="tel:+380679282086">+38 (067) 928 20 86</a>
            </div>

            <ul class="nav-links">
                <li><a href="#" class="active">Шини</a></li>
                <li><a href="#">Камери</a></li>
                <li><a href="#">Новини</a></li>
                <li><a href="#">Про нас</a></li>
                <li><a href="#">Контакти</a></li>
            </ul>
            <div class="sub-nav">
                <a href="#">
                    <img src="/images/svg/loaders.svg" alt="Спецтехніка" />
                    Спецтехніка
                </a>
                <a href="#">
                    <img src="/images/svg/tractor.svg" alt="Агрошини" />
                    Агрошини
                </a>
                <a href="#">
                    <img src="/images/svg/truck.svg" alt="Вантажні" />
                    Вантажні
                </a>
            </div>
        </nav>

        <div class="search-overlay" id="search-overlay" :class="{ 'active': searchOpen }">
            <div class="container search-overlay-inner">
                <form class="search-form" action="{{ route('catalog') }}" method="GET">
                    <input type="text" name="q" id="search-input" placeholder="Наприклад: 710/70 R42"
                        autocomplete="off" x-ref="searchInput"
                        x-effect="if (searchOpen) $nextTick(() => $refs.searchInput.focus())" />
                    <button type="submit" aria-label="Шукати">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                </form>
                <div class="close-search" id="close-search" title="Закрити" @click="searchOpen = false" role="button"
                    tabindex="0" @keydown.enter="searchOpen = false">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </div>
                <div class="search-decoration">
                    <img src="/images/svg/wheel.svg" alt="Tire Background" />
                </div>
            </div>
        </div>
    </div>
</div>
