{{-- resources/views/web/catalog.blade.php — каталог із БД (фільтри, сортування, пагінація) --}}
@extends('layouts.app')

@section('title', 'Каталог шин — Велика Шина')

@php($chev = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>')

@section('content')
<section class="catalog" x-data="catalogUi()"
    x-effect="document.body.style.overflow = filtersOpen ? 'hidden' : ''">
    <div class="container">
        <nav class="breadcrumbs">
            <a href="{{ route('home') }}">Головна</a>
            <span class="sep">/</span>
            <span class="current">Каталог шин</span>
        </nav>

        <div class="catalog-top">
            <h1 class="catalog-title">Каталог шин</h1>
            <span class="catalog-count">Знайдено: <b>{{ $total }}</b></span>
        </div>

        {{-- Швидкий вибір + пошук за назвою --}}
        <div class="catalog-quick">
            <div class="catalog-quick__selects">
                <div class="select">
                    <select onchange="if(this.value)location.href=this.value">
                        <option value="{{ request()->fullUrlWithQuery(['diameter' => null, 'page' => null]) }}"
                            @selected(!$selected['diameter'])>Діаметр</option>
                        @foreach ($diameters as $d)
                        <option value="{{ request()->fullUrlWithQuery(['diameter' => $d, 'page' => null]) }}"
                            @selected($selected['diameter'] === $d)>{{ $d }}</option>
                        @endforeach
                    </select>
                    {!! $chev !!}
                </div>
                <div class="select">
                    <select onchange="if(this.value)location.href=this.value">
                        <option value="{{ request()->fullUrlWithQuery(['size' => null, 'page' => null]) }}"
                            @selected(empty($selected['size']))>Розмір</option>
                        @foreach ($sizes as $s)
                        <option value="{{ request()->fullUrlWithQuery(['size' => $s, 'page' => null]) }}"
                            @selected(in_array($s, $selected['size'], true))>{{ $s }}</option>
                        @endforeach
                    </select>
                    {!! $chev !!}
                </div>
                <div class="select">
                    <select onchange="if(this.value)location.href=this.value">
                        <option value="{{ request()->fullUrlWithQuery(['brand' => null, 'page' => null]) }}"
                            @selected(empty($selected['brand']))>Виробник</option>
                        @foreach ($brands as $b)
                        <option value="{{ request()->fullUrlWithQuery(['brand' => $b, 'page' => null]) }}"
                            @selected(in_array($b, $selected['brand'], true))>{{ $b }}</option>
                        @endforeach
                    </select>
                    {!! $chev !!}
                </div>
            </div>
            <form class="catalog-search" action="{{ route('catalog') }}" method="GET" role="search">
                <input type="hidden" name="sort" value="{{ $selected['sort'] }}" />
                @if ($selected['category'])
                <input type="hidden" name="category" value="{{ $selected['category'] }}" />
                @endif
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <input type="text" name="q" value="{{ $selected['q'] }}"
                    placeholder="Пошук за назвою або артикулом" autocomplete="off" />
            </form>
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
                <a href="{{ $t['url'] }}" class="cat-tab {{ $t['active'] ? 'is-active' : '' }}">
                    <span class="mask-ico"
                        style="-webkit-mask-image:url('{{ $t['icon'] }}');mask-image:url('{{ $t['icon'] }}')"></span>
                    {{ $t['label'] }}
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

    <div class="container catalog-layout" :class="{ 'is-collapsed': filtersCollapsed }">
        {{-- Затемнення під фільтр-шторку (моб.) --}}
        <div class="catalog-backdrop" x-show="filtersOpen" x-cloak x-transition.opacity
            @click="filtersOpen = false"></div>

        {{-- ФІЛЬТРИ --}}
        <aside class="catalog-filters" :class="{ 'is-open': filtersOpen }"
            @keydown.escape.window="filtersOpen = false">
            <div class="cf-head">
                <span class="cf-title">Фільтри</span>
                <a href="{{ route('catalog') }}" class="cf-reset">Скинути все</a>
                {{-- Згорнути фільтри (десктоп) --}}
                <button type="button" class="cf-collapse" @click="filtersCollapsed = true" aria-label="Згорнути фільтри"
                    title="Згорнути фільтри">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6" />
                        <polyline points="21 18 15 12 21 6" />
                    </svg>
                </button>
                <button type="button" class="cf-close" @click="filtersOpen = false" aria-label="Закрити фільтри">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
            </div>

            <form class="cf-form" action="{{ route('catalog') }}" method="GET"
                x-data="catalogFilter('{{ route('catalog.count') }}', {{ $total }})"
                @change="onChange($event)" @scroll.window="pill.show = false">

                {{-- Спливаюча кнопка (ПК): з'являється біля зміненого фільтра --}}
                <button type="submit" class="cf-pill" x-show="pill.show" x-cloak
                    :style="`top:${pill.y}px; left:${pill.x}px`">
                    Показати <span x-text="loading ? '…' : count">{{ $total }}</span> товарів
                </button>

                {{-- Зберігаємо пошук/сортування/діаметр при застосуванні фільтрів --}}
                <input type="hidden" name="q" value="{{ $selected['q'] }}" />
                <input type="hidden" name="sort" value="{{ $selected['sort'] }}" />
                @if ($selected['diameter'])
                <input type="hidden" name="diameter" value="{{ $selected['diameter'] }}" />
                @endif
                @if ($selected['category'])
                <input type="hidden" name="category" value="{{ $selected['category'] }}" />
                @endif

                {{-- Прокручувана частина (групи фільтрів) --}}
                <div class="cf-scroll">
                {{-- Наявність --}}
                <div class="cf-avail">
                    <label class="switch">
                        <input type="checkbox" name="in_stock" value="1" @checked($selected['in_stock']) />
                        <span class="switch-track"></span>
                        <span class="switch-text">В наявності</span>
                    </label>
                    <p class="cf-hint">Показувати тільки товари в наявності</p>
                </div>

                {{-- Тип техніки --}}
                @if (count($machineryNames))
                <div class="cf-group" x-data="{ open: true, term: '' }">
                    <button type="button" class="cf-group__head" :class="{ open }" @click="open = !open">
                        Тип техніки
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </button>
                    <div class="cf-group__body" x-show="open">
                        @if (count($machineryNames) > 6)
                        <div class="cf-search">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8" />
                                <line x1="21" y1="21" x2="16.65" y2="16.65" />
                            </svg>
                            <input type="text" placeholder="Пошук техніки" x-model="term" />
                        </div>
                        @endif
                        <div class="cf-options">
                            @foreach ($machineryNames as $m)
                            <label class="cf-check"
                                x-show="!term || '{{ mb_strtolower($m) }}'.includes(term.toLowerCase())">
                                <input type="checkbox" name="machinery[]" value="{{ $m }}"
                                    @checked(in_array($m, $selected['machinery'], true)) />
                                <span>{{ $m }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                {{-- Тип шини --}}
                <div class="cf-group" x-data="{ open: true }">
                    <button type="button" class="cf-group__head" :class="{ open }" @click="open = !open">
                        Тип шини
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </button>
                    <div class="cf-group__body" x-show="open">
                        <label class="cf-check"><input type="checkbox" name="constr[]" value="TL"
                                @checked(in_array('TL', $selected['constr'], true)) /><span>Радіальні (TL)</span></label>
                        <label class="cf-check"><input type="checkbox" name="constr[]" value="TT"
                                @checked(in_array('TT', $selected['constr'], true)) /><span>Діагональні (TT)</span></label>
                    </div>
                </div>

                {{-- Бренд --}}
                @if (count($brands))
                <div class="cf-group" x-data="{ open: true, term: '' }">
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
                            <input type="text" placeholder="Пошук бренду" x-model="term" />
                        </div>
                        <div class="cf-options">
                            @foreach ($brands as $b)
                            <label class="cf-check"
                                x-show="!term || '{{ mb_strtolower($b) }}'.includes(term.toLowerCase())">
                                <input type="checkbox" name="brand[]" value="{{ $b }}"
                                    @checked(in_array($b, $selected['brand'], true)) />
                                <span>{{ $b }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                {{-- Розмір --}}
                @if (count($sizes))
                <div class="cf-group" x-data="{ open: true, term: '' }">
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
                            <input type="text" placeholder="Напр. 800/65 R32" x-model="term" />
                        </div>
                        <div class="cf-options">
                            @foreach ($sizes as $s)
                            <label class="cf-check"
                                x-show="!term || '{{ mb_strtolower($s) }}'.includes(term.toLowerCase())">
                                <input type="checkbox" name="size[]" value="{{ $s }}"
                                    @checked(in_array($s, $selected['size'], true)) />
                                <span>{{ $s }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
                </div>{{-- /.cf-scroll --}}

                <div class="cf-foot">
                    <button type="submit" class="btn btn--primary btn--block cf-apply">
                        Застосувати <span class="cf-apply__count" x-text="loading ? '…' : count">{{ $total }}</span>
                    </button>
                </div>
            </form>
        </aside>

        {{-- КОНТЕНТ --}}
        <div class="catalog-content">
            <div class="catalog-toolbar">
                <div class="toolbar-side"></div>

                <div class="toolbar-center">
                    {{-- Перемикач фільтрів (десктоп) — поряд із видом, по центру --}}
                    <button type="button" class="toolbar-ftoggle" :class="{ 'is-active': !filtersCollapsed }"
                        @click="filtersCollapsed = !filtersCollapsed">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="4" y1="6" x2="20" y2="6" />
                            <line x1="7" y1="12" x2="17" y2="12" />
                            <line x1="10" y1="18" x2="14" y2="18" />
                        </svg>
                        Фільтри
                    </button>

                    <div class="view-toggle">
                        <button type="button" :class="{ active: view === 'grid' }" @click="view = 'grid'"
                            aria-label="Сітка">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="7" height="7" />
                                <rect x="14" y="3" width="7" height="7" />
                                <rect x="14" y="14" width="7" height="7" />
                                <rect x="3" y="14" width="7" height="7" />
                            </svg>
                        </button>
                        <button type="button" :class="{ active: view === 'list' }" @click="view = 'list'"
                            aria-label="Список">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="8" y1="6" x2="21" y2="6" />
                                <line x1="8" y1="12" x2="21" y2="12" />
                                <line x1="8" y1="18" x2="21" y2="18" />
                                <line x1="3" y1="6" x2="3.01" y2="6" />
                                <line x1="3" y1="12" x2="3.01" y2="12" />
                                <line x1="3" y1="18" x2="3.01" y2="18" />
                            </svg>
                        </button>
                    </div>
                </div>

                <label class="toolbar-sort">
                    <span>Сортування</span>
                    <div class="select">
                        <select onchange="if(this.value)location.href=this.value">
                            @php($sortOpts = ['popular' => 'Популярні', 'cheap' => 'Спочатку дешевші', 'expensive' => 'Спочатку дорожчі', 'new' => 'Новинки'])
                            @foreach ($sortOpts as $val => $label)
                            <option value="{{ request()->fullUrlWithQuery(['sort' => $val, 'page' => null]) }}"
                                @selected($selected['sort'] === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </div>
                </label>
            </div>

            <div class="product-grid catalog-grid" :class="{ 'is-list': view === 'list' }">
                @forelse ($products as $p)
                @include('partials.product-card', ['p' => $p, 'showCountry' => false])
                @empty
                <div class="catalog-empty">
                    <p>За вашим запитом нічого не знайдено.</p>
                    <a href="{{ route('catalog') }}" class="btn btn--outline">Скинути фільтри</a>
                </div>
                @endforelse
            </div>

            {{-- Пагінація --}}
            @if ($products->hasPages())
            <nav class="pagination" aria-label="Сторінки">
                @if ($products->onFirstPage())
                <span class="pg-arrow is-disabled" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6" />
                    </svg>
                </span>
                @else
                <a href="{{ $products->previousPageUrl() }}" class="pg-arrow" aria-label="Назад">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6" />
                    </svg>
                </a>
                @endif

                @php($last = $products->lastPage())
                @php($cur = $products->currentPage())
                @php($pages = collect(range(1, $last))->filter(fn ($pg) => $pg == 1 || $pg == $last || ($pg >= $cur - 1 && $pg <= $cur + 1)))
                @php($prev = 0)
                @foreach ($pages as $pg)
                @if ($prev && $pg - $prev > 1)<span class="pg-dots">…</span>@endif
                <a href="{{ $products->url($pg) }}" class="pg-num {{ $pg == $cur ? 'is-active' : '' }}">{{ $pg }}</a>
                @php($prev = $pg)
                @endforeach

                @if ($products->hasMorePages())
                <a href="{{ $products->nextPageUrl() }}" class="pg-arrow" aria-label="Вперед">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6" />
                    </svg>
                </a>
                @else
                <span class="pg-arrow is-disabled" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6" />
                    </svg>
                </span>
                @endif
            </nav>
            @endif
        </div>
    </div>

    {{-- Плаваюча кнопка фільтрів (лише мобільний) --}}
    <button type="button" class="catalog-fab-filters" @click="filtersOpen = true"
        x-show="!filtersOpen" x-cloak aria-label="Фільтри">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="4" y1="6" x2="20" y2="6" />
            <line x1="7" y1="12" x2="17" y2="12" />
            <line x1="10" y1="18" x2="14" y2="18" />
        </svg>
        Фільтри
    </button>
</section>
@endsection
