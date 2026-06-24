{{-- resources/views/web/catalog.blade.php --}}
@extends('layouts.app')

@section('title', 'Каталог шин — Велика Шина')

{{-- Тимчасові дані. На етапі БД замінюються вибіркою з пагінацією. --}}
@php($catalogProducts = [
['size' => '710/70 R38', 'brand' => 'Michelin', 'model' => 'AxioBib 2', 'constr' => 'Радіальна (TL)', 'li' => '179D', 'app' => 'Трактори', 'stock' => true, 'img' => 'MICHELIN MEGAXBIB.jpg', 'price_mode' => 'fixed', 'price' => 142500],
['size' => '800/65 R32', 'brand' => 'Trelleborg', 'model' => 'TM1000', 'constr' => 'Радіальна (TL)', 'li' => '178A8', 'app' => 'Трактори', 'stock' => true, 'img' => 'MICHELIN megaxbib1.jpg', 'price_mode' => 'from', 'price' => 98000],
['size' => '600/70 R30', 'brand' => 'BKT', 'model' => 'Agrimax RT 765', 'constr' => 'Радіальна (TL)', 'li' => '152D', 'app' => 'Трактори', 'stock' => true, 'img' => 'continental AW-FARMER.jpg', 'price_mode' => 'fixed', 'price' => 64200],
['size' => '405/70 R20', 'brand' => 'BKT', 'model' => 'Multimax MP 527', 'constr' => 'Радіальна (TL)', 'li' => '149A8', 'app' => 'Навантажувачі', 'stock' => true, 'img' => 'continental M 159.jpg', 'price_mode' => 'inquiry'],
['size' => '650/65 R42', 'brand' => 'Trelleborg', 'model' => 'TM900 HP', 'constr' => 'Радіальна (TL)', 'li' => '165D', 'app' => 'Трактори', 'stock' => false, 'img' => '1050-50R32.jpg', 'price_mode' => 'from', 'price' => 156000],
['size' => 'VF 710/60 R30', 'brand' => 'Michelin', 'model' => 'AxioBib 2', 'constr' => 'Радіальна (TL)', 'li' => '165D', 'app' => 'Трактори', 'stock' => true, 'img' => 'Michelin XMCL.jpg', 'price_mode' => 'inquiry'],
['size' => '320/85 R28', 'brand' => 'Alliance', 'model' => 'Agriflex 372', 'constr' => 'Діагональна (TT)', 'li' => '123A8', 'app' => 'Обприскувачі', 'stock' => false, 'img' => 'MICHELIN MEGAXBIB.jpg', 'price_mode' => 'fixed', 'price' => 28900],
['size' => '12.5/80-18', 'brand' => 'Galaxy', 'model' => 'Hulk Skidder', 'constr' => 'Діагональна (TT)', 'li' => '131A2', 'app' => 'Спецтехніка', 'stock' => true, 'img' => 'continental AW-FARMER.jpg', 'price_mode' => 'fixed', 'price' => 18400],
['size' => '23.1-26', 'brand' => 'BKT', 'model' => 'TR 135', 'constr' => 'Діагональна (TT)', 'li' => '155A8', 'app' => 'Комбайни', 'stock' => true, 'img' => 'MICHELIN megaxbib1.jpg', 'price_mode' => 'from', 'price' => 41000],
['size' => '500/70 R24', 'brand' => 'BKT', 'model' => 'RM 500', 'constr' => 'Радіальна (TL)', 'li' => '167A8', 'app' => 'Комбайни', 'stock' => false, 'img' => 'continental M 159.jpg', 'price_mode' => 'inquiry'],
['size' => '16.00-24', 'brand' => 'BKT', 'model' => 'Super Grader', 'constr' => 'Діагональна (TT)', 'li' => '160A8', 'app' => 'Грейдери', 'stock' => true, 'img' => '1050-50R32.jpg', 'price_mode' => 'fixed', 'price' => 52300],
['size' => '315/80 R22.5', 'brand' => 'Rovelo', 'model' => 'Drive R2', 'constr' => 'Радіальна (TL)', 'li' => '156L', 'app' => 'Вантажівки', 'stock' => true, 'img' => 'Michelin XMCL.jpg', 'price_mode' => 'fixed', 'price' => 9800],
])

@php($tabs = [
['Всі шини', 'wheel.svg', true],
['Тракторні', 'tractor.svg', false],
['Комбайні', 'combine.svg', false],
['Обприскувачі', 'sprayer.svg', false],
['Навантажувачі', 'loaders.svg', false],
['Вантажні', 'truck.svg', false],
])

{{-- Лого брендів, для яких є файл; решта показується назвою. --}}
@php($brandLogos = ['Michelin' => 'michelin.svg', 'Continental' => 'continental.svg'])
@php($brands = ['Michelin', 'Trelleborg', 'BKT', 'Alliance', 'Galaxy', 'Continental', 'Mitas'])
@php($sizes = ['710/70 R38', '800/65 R32', '600/70 R30', '520/85 R42', '480/70 R34', '420/85 R30'])
@php($machinery = ['Тракторні', 'Комбайні', 'Обприскувачі', 'Навантажувачі', 'Спецтехніка', 'Вантажні', 'Інше'])

@section('content')
<section class="catalog" x-data="{ filtersOpen: false }"
    x-effect="document.body.style.overflow = filtersOpen ? 'hidden' : ''">
    <div class="container">
        <nav class="breadcrumbs">
            <a href="{{ route('home') }}">Головна</a>
            <span class="sep">/</span>
            <span class="current">Каталог шин</span>
        </nav>

        <div class="catalog-top">
            <h1 class="catalog-title">Каталог шин</h1>
            <p class="catalog-sub">Понад 20 000 позицій в наявності</p>
        </div>

        {{-- Вкладки за технікою (зі стрілками, якщо не влазять) --}}
        <div class="catalog-tabs-wrap" x-data="tabsScroller()">
            <button type="button" class="tabs-arrow tabs-arrow--prev" x-show="canLeft" x-cloak x-transition.opacity
                @click="scroll(-1)" aria-label="Прокрутити назад">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
            </button>
            <div class="catalog-tabs" x-ref="track" @scroll.passive="update()">
                @foreach ($tabs as $t)
                <a href="#" class="cat-tab {{ $t[2] ? 'is-active' : '' }}">
                    <span class="mask-ico" style="--m:url('/images/svg/tehnics/{{ $t[1] }}')"></span>
                    {{ $t[0] }}
                </a>
                @endforeach
            </div>
            <button type="button" class="tabs-arrow tabs-arrow--next" x-show="canRight" x-cloak x-transition.opacity
                @click="scroll(1)" aria-label="Прокрутити вперед">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6" />
                </svg>
            </button>
        </div>
    </div>

    <div class="container catalog-layout">
        {{-- Затемнення під фільтр-шторку (моб.) --}}
        <div class="catalog-backdrop" x-show="filtersOpen" x-cloak x-transition.opacity
            @click="filtersOpen = false"></div>

        {{-- ФІЛЬТРИ --}}
        <aside class="catalog-filters" :class="{ 'is-open': filtersOpen }"
            @keydown.escape.window="filtersOpen = false">
            <div class="cf-head">
                <span class="cf-title">Фільтри</span>
                <button type="button" class="cf-reset">Скинути все</button>
                <button type="button" class="cf-close" @click="filtersOpen = false" aria-label="Закрити фільтри">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
            </div>

            <form class="cf-form" action="{{ route('catalog') }}" method="GET">
                {{-- Наявність --}}
                <div class="cf-avail">
                    <label class="switch">
                        <input type="checkbox" name="in_stock" value="1" />
                        <span class="switch-track"></span>
                        <span class="switch-text">В наявності</span>
                    </label>
                    <p class="cf-hint">Показувати тільки товари в наявності</p>
                </div>

                {{-- Тип техніки --}}
                <div class="cf-group" x-data="{ open: true }">
                    <button type="button" class="cf-group__head" :class="{ open }" @click="open = !open">
                        Тип техніки
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </button>
                    <div class="cf-group__body" x-show="open">
                        @foreach ($machinery as $m)
                        <label class="cf-check">
                            <input type="checkbox" name="machinery[]" value="{{ $m }}" />
                            <span>{{ $m }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Тип шини --}}
                <div class="cf-group" x-data="{ open: true }">
                    <button type="button" class="cf-group__head" :class="{ open }" @click="open = !open">
                        Тип шини
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </button>
                    <div class="cf-group__body" x-show="open">
                        <label class="cf-check"><input type="checkbox" name="constr[]" value="TL" /><span>Радіальні
                                (TL)</span></label>
                        <label class="cf-check"><input type="checkbox" name="constr[]" value="TT" /><span>Діагональні
                                (TT)</span></label>
                    </div>
                </div>

                {{-- Бренд --}}
                <div class="cf-group" x-data="{ open: true }">
                    <button type="button" class="cf-group__head" :class="{ open }" @click="open = !open">
                        Бренд
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </button>
                    <div class="cf-group__body" x-show="open">
                        <div class="cf-search">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8" />
                                <line x1="21" y1="21" x2="16.65" y2="16.65" />
                            </svg>
                            <input type="text" placeholder="Пошук бренду" />
                        </div>
                        @foreach ($brands as $b)
                        <label class="cf-check">
                            <input type="checkbox" name="brand[]" value="{{ $b }}" />
                            <span>{{ $b }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Розмір --}}
                <div class="cf-group" x-data="{ open: true }">
                    <button type="button" class="cf-group__head" :class="{ open }" @click="open = !open">
                        Розмір
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </button>
                    <div class="cf-group__body" x-show="open">
                        <div class="cf-search">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8" />
                                <line x1="21" y1="21" x2="16.65" y2="16.65" />
                            </svg>
                            <input type="text" placeholder="Напр. 800/65 R32" />
                        </div>
                        @foreach ($sizes as $s)
                        <label class="cf-check">
                            <input type="checkbox" name="size[]" value="{{ $s }}" />
                            <span>{{ $s }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="btn btn--primary btn--block cf-apply">Застосувати</button>
            </form>
        </aside>

        {{-- КОНТЕНТ --}}
        <div class="catalog-content">
            <div class="catalog-toolbar">
                <button type="button" class="toolbar-filters" @click="filtersOpen = true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="4" y1="6" x2="20" y2="6" />
                        <line x1="7" y1="12" x2="17" y2="12" />
                        <line x1="10" y1="18" x2="14" y2="18" />
                    </svg>
                    Фільтри
                </button>
                <span class="toolbar-count">Знайдено <b>1 284</b> товари</span>
                <label class="toolbar-sort">
                    <span>Сортування</span>
                    <div class="select">
                        <select name="sort">
                            <option value="popular">Популярні</option>
                            <option value="cheap">Спочатку дешевші</option>
                            <option value="expensive">Спочатку дорожчі</option>
                            <option value="new">Новинки</option>
                        </select>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </div>
                </label>
            </div>

            <div class="product-grid catalog-grid">
                @foreach ($catalogProducts as $p)
                @include('partials.product-card', ['p' => $p])
                @endforeach
            </div>

            {{-- Пагінація --}}
            <nav class="pagination" aria-label="Сторінки">
                <a href="#" class="pg-arrow" aria-label="Назад">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6" />
                    </svg>
                </a>
                <a href="#" class="pg-num is-active">1</a>
                <a href="#" class="pg-num">2</a>
                <a href="#" class="pg-num">3</a>
                <span class="pg-dots">…</span>
                <a href="#" class="pg-num">36</a>
                <a href="#" class="pg-arrow" aria-label="Вперед">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6" />
                    </svg>
                </a>
            </nav>
        </div>
    </div>
</section>
@endsection
