@extends('layouts.app')

@php($seoCat = ($selected['category'] ?? '') !== ''
    ? \App\Models\Category::where('slug', $selected['category'])->orWhere('name', $selected['category'])->first()
    : null)
@php($isFiltered =
    collect(['brand', 'size', 'constr', 'machinery', 'mbrand', 'mmodel', 'type'])
        ->contains(fn ($k) => ! empty(array_filter((array) ($selected[$k] ?? []))))
    || ($selected['diameter'] ?? '') !== ''
    || ($selected['q'] ?? '') !== ''
    || (($selected['sort'] ?? 'popular') !== 'popular' && ($selected['sort'] ?? '') !== '')
    || (int) request()->query('page', 1) > 1)

@section('title', $seoCat
    ? ($seoCat->seo_title ?: $seoCat->name . ' - купити за найкращою ціною | ВЕЛИКА ШИНА')
    : 'Каталог шин, дисків та камер для техніки - ВЕЛИКА ШИНА')
@section('meta_description', $seoCat
    ? ($seoCat->seo_description ?: 'Купити ' . mb_strtolower($seoCat->name) . ' у ВЕЛИКА ШИНА: великий вибір, чесні ціни, підбір і доставка по Україні.')
    : 'Каталог шин, дисків і камер для сільгосп-, спец- та вантажної техніки. Підбір за розміром, брендом і технікою. Доставка по Україні.')
@section('canonical', $seoCat ? route('catalog', ['category' => $seoCat->slug]) : route('catalog'))
@if($isFiltered)
    @section('robots', 'noindex, follow')
@endif

@push('head')
    @php($catCrumbs = collect([
        ['name' => 'Головна', 'item' => route('home')],
        ['name' => 'Каталог', 'item' => route('catalog')],
    ]))
    @if($seoCat)
        @php($catCrumbs->push(['name' => $seoCat->name, 'item' => route('catalog', ['category' => $seoCat->slug])]))
    @endif
    @php($catBreadcrumbLd = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList',
        'itemListElement' => $catCrumbs->values()->map(fn ($c, $i) => [
            '@type' => 'ListItem', 'position' => $i + 1, 'name' => $c['name'], 'item' => $c['item'],
        ])->all()])
    <script type="application/ld+json">{!! json_encode($catBreadcrumbLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@php($chev = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>')

@section('content')
<section class="catalog" x-data="catalogUi()"
    x-effect="document.body.style.overflow = filtersOpen ? 'hidden' : ''">
    <div class="container">
        @php($machCrumbs = collect($selected['machinery'] ?? [])->filter()->values())
        <nav class="breadcrumbs">
            <a href="{{ route('home') }}">Головна</a>
            <span class="sep">/</span>
            @if($seoCat || $machCrumbs->isNotEmpty())
                <a href="{{ route('catalog') }}">Каталог</a>
                @if($seoCat)
                    <span class="sep">/</span>
                    @if($machCrumbs->isNotEmpty())
                        <a href="{{ route('catalog') }}?category={{ $seoCat->slug }}">{{ $seoCat->name }}</a>
                    @else
                        <span class="current">{{ $seoCat->name }}</span>
                    @endif
                @endif
                @if($machCrumbs->isNotEmpty())
                    <span class="sep">/</span>
                    <span class="current">{{ $machCrumbs->join(', ') }}</span>
                @endif
            @else
                <span class="current">Каталог</span>
            @endif
        </nav>

        <div class="catalog-top">
            <h1 class="catalog-title">{{ $seoCat ? ($seoCat->seo_h1 ?: $seoCat->name) : 'Каталог' }}</h1>
            <span class="catalog-count">Знайдено: <b>{{ $total }}</b></span>
        </div>

        <div class="catalog-quick">
            @php($searchIco = '<svg class="ssel__search-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>')
            @php($diameterOpts = collect($diameters)->map(fn ($d) => ['value' => request()->fullUrlWithQuery(['diameter' => $d, 'page' => null]), 'label' => (string) $d])->values())
            @php($sizeOpts = collect($sizes)->map(fn ($s) => ['value' => request()->fullUrlWithQuery(['size' => $s, 'page' => null]), 'label' => (string) $s, 'active' => in_array($s, $selected['size'], true)])->values())
            @php($brandOpts = collect($brands)->map(fn ($b) => ['value' => request()->fullUrlWithQuery(['brand' => $b, 'page' => null]), 'label' => (string) $b, 'active' => in_array($b, $selected['brand'], true)])->values())

            <div class="catalog-quick__selects">
                <div class="ssel" x-data="searchSelect({ placeholder: 'Діаметр', options: @js($diameterOpts) })"
                    @click.outside="close()" @keydown.escape.window="close()">
                    <button type="button" class="ssel__btn {{ $selected['diameter'] ? 'is-set' : '' }}" @click="toggle()">
                        <span class="ssel__val">{{ $selected['diameter'] ?: 'Діаметр' }}</span>
                        {!! $chev !!}
                    </button>
                    <div class="ssel__backdrop" x-show="open" x-cloak @click="close()"></div>
                    <div class="ssel__panel" x-show="open" x-cloak x-transition.opacity.duration.150ms>
                        <div class="ssel__search">{!! $searchIco !!}<input x-ref="search" x-model="term" type="text" placeholder="Пошук діаметра…" /></div>
                        <div class="ssel__list">
                            <a href="{{ request()->fullUrlWithQuery(['diameter' => null, 'page' => null]) }}" class="ssel__opt {{ !$selected['diameter'] ? 'is-active' : '' }}">Усі діаметри</a>
                            <template x-for="o in filtered()" :key="o.value">
                                <a :href="o.value" class="ssel__opt {{ '' }}" :class="{ 'is-active': o.label === @js((string) $selected['diameter']) }" x-text="o.label"></a>
                            </template>
                            <p class="ssel__empty" x-show="filtered().length === 0">Нічого не знайдено</p>
                        </div>
                    </div>
                </div>

                <div class="ssel" x-data="searchSelect({ placeholder: 'Розмір', options: @js($sizeOpts) })"
                    @click.outside="close()" @keydown.escape.window="close()">
                    <button type="button" class="ssel__btn {{ count($selected['size']) ? 'is-set' : '' }}" @click="toggle()">
                        <span class="ssel__val">{{ count($selected['size']) === 1 ? $selected['size'][0] : (count($selected['size']) > 1 ? 'Розміри: ' . count($selected['size']) : 'Розмір') }}</span>
                        {!! $chev !!}
                    </button>
                    <div class="ssel__backdrop" x-show="open" x-cloak @click="close()"></div>
                    <div class="ssel__panel" x-show="open" x-cloak x-transition.opacity.duration.150ms>
                        <div class="ssel__search">{!! $searchIco !!}<input x-ref="search" x-model="term" type="text" placeholder="Пошук розміру, напр. 800/65R32" /></div>
                        <div class="ssel__list">
                            <a href="{{ request()->fullUrlWithQuery(['size' => null, 'page' => null]) }}" class="ssel__opt {{ empty($selected['size']) ? 'is-active' : '' }}">Усі розміри</a>
                            <template x-for="o in filtered()" :key="o.value">
                                <a :href="o.value" class="ssel__opt" :class="{ 'is-active': o.active }" x-text="o.label"></a>
                            </template>
                            <p class="ssel__empty" x-show="filtered().length === 0">Нічого не знайдено</p>
                        </div>
                    </div>
                </div>

                <div class="ssel" x-data="searchSelect({ placeholder: 'Виробник', options: @js($brandOpts) })"
                    @click.outside="close()" @keydown.escape.window="close()">
                    <button type="button" class="ssel__btn {{ count($selected['brand']) ? 'is-set' : '' }}" @click="toggle()">
                        <span class="ssel__val">{{ count($selected['brand']) === 1 ? $selected['brand'][0] : (count($selected['brand']) > 1 ? 'Бренди: ' . count($selected['brand']) : 'Виробник') }}</span>
                        {!! $chev !!}
                    </button>
                    <div class="ssel__backdrop" x-show="open" x-cloak @click="close()"></div>
                    <div class="ssel__panel" x-show="open" x-cloak x-transition.opacity.duration.150ms>
                        <div class="ssel__search">{!! $searchIco !!}<input x-ref="search" x-model="term" type="text" placeholder="Пошук бренду…" /></div>
                        <div class="ssel__list">
                            <a href="{{ request()->fullUrlWithQuery(['brand' => null, 'page' => null]) }}" class="ssel__opt {{ empty($selected['brand']) ? 'is-active' : '' }}">Усі бренди</a>
                            <template x-for="o in filtered()" :key="o.value">
                                <a :href="o.value" class="ssel__opt" :class="{ 'is-active': o.active }" x-text="o.label"></a>
                            </template>
                            <p class="ssel__empty" x-show="filtered().length === 0">Нічого не знайдено</p>
                        </div>
                    </div>
                </div>

            </div>
            <form class="catalog-search" action="{{ route('catalog') }}" method="GET" role="search">
                <input type="hidden" name="sort" value="{{ $selected['sort'] }}" />
                @if ($selected['category'])
                <input type="hidden" name="category" value="{{ $selected['category'] }}" />
                @endif
                @foreach ($selected['mbrand'] as $v)<input type="hidden" name="mbrand[]" value="{{ $v }}" />@endforeach
                @foreach ($selected['mmodel'] as $v)<input type="hidden" name="mmodel[]" value="{{ $v }}" />@endforeach
                @foreach ($selected['type'] as $t)
                <input type="hidden" name="type[]" value="{{ $t }}" />
                @endforeach
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <input type="text" name="q" value="{{ $selected['q'] }}"
                    placeholder="Пошук за назвою або артикулом" autocomplete="off" />
            </form>
        </div>

        <div class="catalog-tabs-wrap" x-data="tabsScroller()">
            <button type="button" class="tabs-arrow tabs-arrow--prev" x-show="canLeft" x-cloak x-transition.opacity
                @click="scroll(-1)" aria-label="Прокрутити назад">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
            </button>
            <div class="catalog-tabs" x-ref="track" @scroll.passive="update()"
                @pointerdown="dragStart($event)" @pointermove="dragMove($event)"
                @pointerup="dragEnd()" @pointerleave="dragEnd()" @pointercancel="dragEnd()"
                @click.capture="dragClick($event)">
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
        <div class="catalog-backdrop" x-show="filtersOpen" x-cloak x-transition.opacity
            @click="filtersOpen = false"></div>

        <aside class="catalog-filters" :class="{ 'is-open': filtersOpen }"
            @keydown.escape.window="filtersOpen = false">
            <div class="cf-head">
                <span class="cf-title">Фільтри</span>
                <a href="{{ route('catalog') }}" class="cf-reset">Скинути все</a>
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

                <button type="submit" class="cf-pill" x-show="pill.show" x-cloak
                    :style="`top:${pill.y}px; left:${pill.x}px`">
                    Показати <span x-text="loading ? '…' : count">{{ $total }}</span> товарів
                </button>

                <input type="hidden" name="q" value="{{ $selected['q'] }}" />
                <input type="hidden" name="sort" value="{{ $selected['sort'] }}" />
                @if ($selected['diameter'])
                <input type="hidden" name="diameter" value="{{ $selected['diameter'] }}" />
                @endif
                @if ($selected['category'])
                <input type="hidden" name="category" value="{{ $selected['category'] }}" />
                @endif

                <div class="cf-scroll">
                <div class="cf-avail">
                    <label class="switch">
                        <input type="checkbox" name="in_stock" value="1" @checked($selected['in_stock']) />
                        <span class="switch-track"></span>
                        <span class="switch-text">В наявності</span>
                    </label>
                    <p class="cf-hint">Показувати тільки товари в наявності</p>
                </div>

                @if (count($productTypes))
                <div class="cf-group" x-data="{ open: true }">
                    <button type="button" class="cf-group__head" :class="{ open }" @click="open = !open">
                        Тип товару
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </button>
                    <div class="cf-group__body" x-show="open">
                        @foreach ($productTypes as $t)
                        <label class="cf-check">
                            <input type="checkbox" name="type[]" value="{{ $t->code }}"
                                @checked(in_array($t->code, $selected['type'], true)) />
                            <span>{{ $t->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

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

                @if (count($machineryBrands))
                <div class="cf-group" x-data="{ open: {{ $selected['mbrand'] ? 'true' : 'false' }}, term: '' }">
                    <button type="button" class="cf-group__head" :class="{ open }" @click="open = !open">
                        Марка техніки
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </button>
                    <div class="cf-group__body" x-show="open">
                        @if (count($machineryBrands) > 6)
                        <div class="cf-search">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8" />
                                <line x1="21" y1="21" x2="16.65" y2="16.65" />
                            </svg>
                            <input type="text" placeholder="Пошук марки" x-model="term" />
                        </div>
                        @endif
                        <div class="cf-options">
                            @foreach ($machineryBrands as $b)
                            <label class="cf-check"
                                x-show="!term || '{{ mb_strtolower($b['name']) }}'.includes(term.toLowerCase())">
                                <input type="checkbox" name="mbrand[]" value="{{ $b['id'] }}"
                                    @checked(in_array($b['id'], $selected['mbrand'])) />
                                <span>{{ $b['name'] }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                @if (count($machineryModels))
                <div class="cf-group" x-data="{ open: {{ $selected['mmodel'] ? 'true' : 'false' }}, term: '' }">
                    <button type="button" class="cf-group__head" :class="{ open }" @click="open = !open">
                        Модель техніки
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </button>
                    <div class="cf-group__body" x-show="open">
                        @if (count($machineryModels) > 6)
                        <div class="cf-search">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8" />
                                <line x1="21" y1="21" x2="16.65" y2="16.65" />
                            </svg>
                            <input type="text" placeholder="Пошук моделі" x-model="term" />
                        </div>
                        @endif
                        <div class="cf-options">
                            @foreach ($machineryModels as $m)
                            <label class="cf-check"
                                x-show="!term || '{{ mb_strtolower($m['name']) }}'.includes(term.toLowerCase())">
                                <input type="checkbox" name="mmodel[]" value="{{ $m['id'] }}"
                                    @checked(in_array($m['id'], $selected['mmodel'])) />
                                <span>{{ $m['name'] }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <div class="cf-group" x-data="{ open: true }">
                    <button type="button" class="cf-group__head" :class="{ open }" @click="open = !open">
                        TL/TT
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </button>
                    <div class="cf-group__body" x-show="open">
                        <label class="cf-check"><input type="checkbox" name="constr[]" value="TL"
                                @checked(in_array('TL', $selected['constr'], true)) /><span>Безкамерна (TL)</span></label>
                        <label class="cf-check"><input type="checkbox" name="constr[]" value="TT"
                                @checked(in_array('TT', $selected['constr'], true)) /><span>Камерна (TT)</span></label>
                    </div>
                </div>

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
                </div><div class="cf-foot">
                    <button type="submit" class="btn btn--primary btn--block cf-apply">
                        Застосувати <span class="cf-apply__count" x-text="loading ? '…' : count">{{ $total }}</span>
                    </button>
                </div>
            </form>
        </aside>

        <div class="catalog-content">
            <div class="catalog-toolbar">
                <div class="toolbar-side"></div>

                <div class="toolbar-center">
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

            @if (!empty($activeFilters))
            <div class="active-filters">
                @foreach ($activeFilters as $chip)
                <a href="{{ $chip['url'] }}" class="active-chip">
                    {{ $chip['label'] }}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </a>
                @endforeach
                <a href="{{ route('catalog') }}" class="active-filters__reset">Скинути все</a>
            </div>
            @endif

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

            @php($cc = config('site.contacts'))
            @php($ctaMach = collect($selected['machinery'] ?? [])->filter()->map(fn ($m) => mb_strtolower($m))->values())
            @php($ctaHeading = $ctaMach->isNotEmpty()
                ? 'Шукаєте шину на ' . $ctaMach->join(', ', ' та ') . '?'
                : 'Не знайшли потрібну шину?')
            <div class="footer-cta footer-cta--catalog" data-aos="fade-up">
                <div class="fc-text">
                    <div class="fc-line"></div>
                    <h3>{{ $ctaHeading }}</h3>
                    <p>Правильний підбір шин починається з розуміння вашої техніки та умов роботи. Ми допоможемо знайти правильне рішення.</p>
                    <div class="fc-actions">
                        <a href="tel:{{ $cc['phone_href'] }}" class="btn btn--primary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                            </svg>
                            Отримати консультацію
                        </a>
                        <a href="tel:{{ $cc['phone_href'] }}" class="btn btn--outline">{{ $cc['phone'] }}</a>
                    </div>
                </div>
                <div class="fc-media">
                    <img src="/images/details/back_wheels.jpg" alt="ВЕЛИКА ШИНА - підбір шин під техніку" loading="lazy" />
                </div>
            </div>
        </div>
    </div>

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
