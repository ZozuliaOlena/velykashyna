@extends('layouts.app')

@php($years = now()->year - config('site.founded_year'))
@php($stats = config('site.stats'))
@php($foundedDate = config('site.founded_date'))

@php($categoriesFallback = [
['name' => 'Тракторні', 'count' => 'Понад 8 000 позицій', 'img' => 'MICHELIN MEGAXBIB.jpg'],
['name' => 'Комбайні', 'count' => 'Понад 3 000 позицій', 'img' => 'Michelin XMCL.jpg'],
['name' => 'Обприскувачі', 'count' => 'Понад 2 000 позицій', 'img' => 'MICHELIN megaxbib1.jpg'],
['name' => 'Навантажувачі', 'count' => 'Понад 4 500 позицій', 'img' => 'continental AW-FARMER.jpg'],
['name' => 'Спецтехніка', 'count' => 'Понад 6 000 позицій', 'img' => 'continental M 159.jpg'],
['name' => 'Вантажні', 'count' => 'Понад 12 000 позицій', 'img' => '1050-50R32.jpg'],
])
@php($categories = !empty($dbCategories ?? []) ? $dbCategories : $categoriesFallback)

@section('content')
@php($slides = (! empty($heroSlides)) ? $heroSlides : [
    ['title' => 'ВЕЛИКА ДОВІРА', 'subtitle' => $years . ' років досвіду', 'type' => 'video', 'src' => '/images/details/slide1.mp4', 'poster' => '/images/details/slide1.png'],
    ['title' => 'ВЕЛИКИЙ СКЛАД', 'subtitle' => 'офіційні поставки з усього світу', 'type' => 'video', 'src' => '/images/details/slide2.mp4', 'poster' => '/images/details/kara.png'],
    ['title' => 'ВЕЛИКІ ПРОФІ', 'subtitle' => 'підбираємо правильні шини', 'type' => 'image', 'src' => '/images/details/slide3.png'],
])
<section class="hero-slider" x-data="heroSlider(@js(array_map(fn ($s) => ['title' => $s['title'] ?? '', 'subtitle' => $s['subtitle'] ?? ''], $slides)))"
    @mouseenter="stop()" @mouseleave="start()"
    @pointerdown="dragStart($event)" @pointermove="dragMove($event)" @pointerup="dragEnd($event)" @pointercancel="dragEnd($event)">
    @php($sv = array_values($slides))
    @php($loop = count($sv) > 1 ? array_merge([$sv[count($sv) - 1]], $sv, [$sv[0]]) : $sv)
    <div class="hs-bg" :style="trackStyle()" @if(count($sv) > 1) style="transform:translateX(-100%)" @endif>
        @foreach($loop as $s)
            <div class="hs-slide">
                @if(($s['type'] ?? 'image') === 'youtube' && ! empty($s['src']))
                    <iframe class="hs-media" tabindex="-1" frameborder="0" allow="autoplay; encrypted-media"
                        style="pointer-events:none; border:0"
                        src="https://www.youtube.com/embed/{{ $s['src'] }}?autoplay=1&mute=1&controls=0&loop=1&playlist={{ $s['src'] }}&playsinline=1&modestbranding=1&rel=0&showinfo=0&cc_load_policy=0&iv_load_policy=3&disablekb=1&fs=0"></iframe>
                    <span class="hs-yt-cover" style="background-image:url('https://i.ytimg.com/vi/{{ $s['src'] }}/maxresdefault.jpg')"></span>
                @elseif(($s['type'] ?? 'image') === 'video' && ! empty($s['src']))
                    <video class="hs-media" muted loop playsinline preload="metadata" @if(! empty($s['poster'])) poster="{{ $s['poster'] }}" @endif>
                        <source src="{{ $s['src'] }}" type="video/mp4" />
                    </video>
                @elseif(! empty($s['src']))
                    <img class="hs-media" src="{{ $s['src'] }}" alt="" />
                @endif
            </div>
        @endforeach
    </div>
    <div class="hs-shade"></div>

    <div class="hs-content">
        <div class="container">
            <div class="hs-text" :key="active" x-transition.opacity.duration.500ms>
                <h1 class="hs-title"><span x-text="slides[active].title"></span></h1>
                <p class="hs-sub" x-text="slides[active].subtitle"></p>
            </div>
            <div class="hs-progress">
                <template x-for="(s, i) in slides" :key="i">
                    <button type="button" class="hs-bar" :class="{ active: active === i }" @click="go(i)"
                        :aria-label="'Слайд ' + (i + 1)"></button>
                </template>
            </div>
            <div class="hs-actions">
                <a href="{{ route('catalog') }}" class="btn btn--primary">Обрати самостійно</a>
                <a href="tel:{{ config('site.contacts.phone_href') }}" class="btn btn--dark">Консультація</a>
            </div>
        </div>
    </div>
</section>

@php($chev = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
    <polyline points="6 9 12 15 18 9" />
</svg>')
@php($searchIco = '<svg class="ssel__search-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>')
<div class="filter-bar-wrap" id="pidbir">
    <div class="container">
        <form class="filter-bar" action="{{ route('catalog') }}" method="GET"
            x-data="homeFilter(@js($filters))" :class="{ 'is-loading': loading }">
            @php($homeFields = [
                ['name' => 'diameter', 'label' => 'Діаметр', 'opts' => 'diameterOptions', 'search' => 'Пошук діаметра…'],
                ['name' => 'size', 'label' => 'Розмір', 'opts' => 'sizeOptions', 'search' => 'Пошук розміру…'],
                ['name' => 'machinery', 'label' => 'Тип техніки', 'opts' => 'machineryOptions', 'search' => 'Пошук техніки…'],
                ['name' => 'brand', 'label' => 'Бренд', 'opts' => 'brandOptions', 'search' => 'Пошук бренду…'],
            ])
            @foreach ($homeFields as $f)
            <div class="field">
                <label>{{ $f['label'] }}</label>
                <div class="ssel" x-data="searchSelect({ placeholder: 'Всі' })"
                    @click.outside="close()" @keydown.escape.window="close()">
                    <button type="button" class="ssel__btn" :class="{ 'is-set': {{ $f['name'] }} }" @click="toggle()">
                        <span class="ssel__val" x-text="{{ $f['name'] }} ? ({{ $f['opts'] }}.find(o => o.value === {{ $f['name'] }})?.label || {{ $f['name'] }}) : 'Всі'"></span>
                        {!! $chev !!}
                    </button>
                    <div class="ssel__backdrop" x-show="open" x-cloak @click="close()"></div>
                    <div class="ssel__panel" x-show="open" x-cloak x-transition.opacity.duration.150ms>
                        <div class="ssel__search">{!! $searchIco !!}<input x-ref="search" x-model="term" type="text" placeholder="{{ $f['search'] }}" /></div>
                        <div class="ssel__list">
                            <button type="button" class="ssel__opt" :class="{ 'is-active': !{{ $f['name'] }} }" @click="{{ $f['name'] }} = ''; refresh('{{ $f['name'] }}'); close()">Всі</button>
                            <template x-for="o in {{ $f['opts'] }}.filter(o => matches(o.label))" :key="o.value">
                                <button type="button" class="ssel__opt" :class="{ 'is-active': {{ $f['name'] }} === o.value }" @click="{{ $f['name'] }} = o.value; refresh('{{ $f['name'] }}'); close()" x-text="o.label"></button>
                            </template>
                            <p class="ssel__empty" x-show="{{ $f['opts'] }}.filter(o => matches(o.label)).length === 0">Нічого не знайдено</p>
                        </div>
                    </div>
                    <input type="hidden" name="{{ $f['name'] }}" :value="{{ $f['name'] }}" />
                </div>
            </div>
            @endforeach
            <button type="submit" class="btn btn--primary filter-submit">Підібрати шини</button>
        </form>
    </div>
</div>

<section class="section machinery" x-data="dragScroll()">
    <div class="container">
        <div class="section-head" data-aos="fade-up">
            <h2 class="section-title">Шини на усю техніку</h2>
        </div>
        <div class="mach-wrap">
            <button type="button" class="mach-arrow mach-arrow--prev" aria-label="Прокрутити назад"
                @click="scroll(-1)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
            </button>
            <div class="mach-track" x-ref="track"
                @pointerdown="dragStart($event)" @pointermove="dragMove($event)"
                @pointerup="dragEnd()" @pointerleave="dragEnd()" @pointercancel="dragEnd()"
                @click.capture="dragClick($event)">
            @php($machineryFallback = [
            ['name' => 'Трактори', 'icon' => '/images/svg/tehnics/tractor.svg', 'url' => route('catalog')],
            ['name' => 'Комбайни', 'icon' => '/images/svg/tehnics/combine.svg', 'url' => route('catalog')],
            ['name' => 'Обприскувачі', 'icon' => '/images/svg/tehnics/sprayer.svg', 'url' => route('catalog')],
            ['name' => 'Навантажувачі', 'icon' => '/images/svg/tehnics/loaders.svg', 'url' => route('catalog')],
            ['name' => 'Вантажні', 'icon' => '/images/svg/tehnics/truck.svg', 'url' => route('catalog')],
            ['name' => 'Інше', 'icon' => '/images/svg/tehnics/wheel.svg', 'url' => route('catalog')],
            ])
            @php($machinery = !empty($dbMachinery ?? []) ? $dbMachinery : $machineryFallback)
            @foreach ($machinery as $m)
            <a href="{{ $m['url'] ?? route('catalog') }}" class="mach-item">
                <span class="mach-ico mask-ico"
                    style="-webkit-mask-image:url('{{ $m['icon'] }}');mask-image:url('{{ $m['icon'] }}')"></span>
                <span>{{ $m['name'] }}</span>
            </a>
            @endforeach
            </div>
            <button type="button" class="mach-arrow mach-arrow--next" aria-label="Прокрутити вперед"
                @click="scroll(1)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6" />
                </svg>
            </button>
        </div>
    </div>
</section>

@include('partials.experience-counter')

@php($productsFallback = [
['brand' => 'Michelin', 'model' => 'XMCL', 'size' => '460/70 R24', 'constr' => 'Радіальна (TL)', 'li' => '159A8', 'app' => 'Навантажувачі', 'stock' => true, 'img' => 'Michelin XMCL.jpg', 'price_mode' => 'fixed', 'price' => 47800, 'promos' => ['Акція', 'Безкоштовна доставка']],
['brand' => 'Michelin', 'model' => 'MegaXBib', 'size' => '620/75 R30', 'constr' => 'Радіальна (TL)', 'li' => '170D', 'app' => 'Комбайни', 'stock' => false, 'img' => 'MICHELIN MEGAXBIB.jpg', 'price_mode' => 'inquiry'],
['brand' => 'Michelin', 'model' => 'MegaXBib', 'size' => '800/65 R32', 'constr' => 'Радіальна (TL)', 'li' => '178A8', 'app' => 'Комбайни', 'stock' => true, 'img' => 'MICHELIN megaxbib1.jpg', 'price_mode' => 'from', 'price' => 132000],
['brand' => 'Continental', 'model' => 'AW-Farmer', 'size' => '10.0/75-12', 'constr' => 'Діагональна (TT)', 'li' => '123A8', 'app' => 'Причіпна', 'stock' => false, 'img' => 'continental AW-FARMER.jpg', 'price_mode' => 'inquiry'],
['brand' => 'Continental', 'model' => 'M 159', 'size' => '10.0/75-15.3', 'constr' => 'Діагональна (TT)', 'li' => '131A8', 'app' => 'Причіпна', 'stock' => true, 'img' => 'continental M 159.jpg', 'price_mode' => 'fixed', 'price' => 8900],
['brand' => 'BKT', 'model' => 'Agrimax RT 600', 'size' => '710/70 R38', 'constr' => 'Радіальна (TL)', 'li' => '181A8', 'app' => 'Трактори', 'stock' => true, 'img' => 'continental AW-FARMER.jpg', 'price_mode' => 'from', 'price' => 89000, 'promos' => ['Знижка']],
['brand' => 'BKT', 'model' => 'Earthmax SR41', 'size' => '1050/50 R32', 'constr' => 'Радіальна (TL)', 'li' => '178A8', 'app' => 'Спецтехніка', 'stock' => true, 'img' => '1050-50R32.jpg', 'price_mode' => 'inquiry'],
['brand' => 'Trelleborg', 'model' => 'TM1000', 'size' => '540/65 R28', 'constr' => 'Радіальна (TL)', 'li' => '149D', 'app' => 'Трактори', 'stock' => true, 'img' => 'Michelin XMCL.jpg', 'price_mode' => 'fixed', 'price' => 54600],
])
@php($products = !empty($dbProducts ?? []) ? $dbProducts : $productsFallback)
<section class="section">
    <div class="container">
        <div class="section-head" data-aos="fade-up">
            <h2 class="section-title">Каталог шин</h2>
            <a href="{{ route('catalog') }}" class="section-link">Весь каталог
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12" />
                    <polyline points="12 5 19 12 12 19" />
                </svg>
            </a>
        </div>
        <div class="product-grid">
            @foreach ($products as $p)
            @include('partials.product-card', ['p' => $p, 'showCountry' => false])
            @endforeach
        </div>
    </div>
</section>

<section class="section features">
    <div class="container">
        <div class="features-grid">
            <div class="feature" data-aos="fade-up">
                <div class="f-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7" />
                        <rect x="14" y="3" width="7" height="7" />
                        <rect x="14" y="14" width="7" height="7" />
                        <rect x="3" y="14" width="7" height="7" />
                    </svg>
                </div>
                <div class="f-title">Широкий вибір</div>
                <div class="f-text">Понад {{ $stats['positions'] }} позицій шин від світових брендів.</div>
            </div>
            <div class="feature" data-aos="fade-up" data-aos-delay="160">
                <div class="f-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="1" y="3" width="15" height="13" />
                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8" />
                        <circle cx="5.5" cy="18.5" r="2.5" />
                        <circle cx="18.5" cy="18.5" r="2.5" />
                    </svg>
                </div>
                <div class="f-title">Швидка доставка</div>
                <div class="f-text">Доставка по всій Україні. Відправляємо щодня.</div>
            </div>
            <div class="feature" data-aos="fade-up" data-aos-delay="240">
                <div class="f-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 18v-6a9 9 0 0 1 18 0v6" />
                        <path
                            d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z" />
                    </svg>
                </div>
                <div class="f-title">Професійна підтримка</div>
                <div class="f-text">Наші спеціалісти підберуть оптимальний варіант.</div>
            </div>
            <div class="feature" data-aos="fade-up" data-aos-delay="320">
                <div class="f-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                        <polyline points="9 12 11 14 15 10" />
                    </svg>
                </div>
                <div class="f-title">Гарантія якості</div>
                <div class="f-text">Оригінальна продукція з гарантією від виробника.</div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head" data-aos="fade-up" style="flex-direction:column;align-items:flex-start;gap:8px">
            <h2 class="section-title">Чому <span>ВЕЛИКА ШИНА</span>?</h2>
            <p style="color:#6b7280"><b style="color:#e31e24">ВЕЛИКА</b> - не тільки про розмір шини.</p>
        </div>
        @php($why = [
        ['p' => 'Великий', 't' => 'Досвід', 'd' => 'Працюємо з ' . config('site.founded_year') . ' року. Знаємо шини та техніку не з каталогу, а з практики.', 'ico' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18h6"/><path d="M10 22h4"/><path d="M12 2a7 7 0 0 0-4 12.7c.6.5 1 1.3 1 2.1V18h6v-1.2c0-.8.4-1.6 1-2.1A7 7 0 0 0 12 2z"/></svg>'],
        ['p' => 'Велика', 't' => 'Довіра', 'd' => 'Нам довіряють клієнти, які працюють з нами роками.', 'ico' => '<span class="mask-ico" style="-webkit-mask-image:url(\'/images/svg/others/user-shield.svg\');mask-image:url(\'/images/svg/others/user-shield.svg\')"></span>'],
        ['p' => 'Велика', 't' => 'Турбота', 'd' => 'Підбираємо шини під задачу, а не просто продаємо товар.', 'ico' => '<span class="mask-ico" style="-webkit-mask-image:url(\'/images/svg/others/handshake.svg\');mask-image:url(\'/images/svg/others/handshake.svg\')"></span>'],
        ['p' => 'Велика', 't' => 'Порядність', 'd' => 'Чесно радимо те, що дійсно підходить і працює.', 'ico' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="8" r="6" />
            <path d="M15.5 13.5 17 22l-5-3-5 3 1.5-8.5" />
            <path d="M9.5 8l1.7 1.7L14.5 6.5" />
        </svg>'],
        ['p' => 'Велика', 't' => 'Допомога', 'd' => 'Допомагаємо до, під час і після покупки.', 'ico' => '<span class="mask-ico" style="-webkit-mask-image:url(\'/images/svg/others/help.svg\');mask-image:url(\'/images/svg/others/help.svg\')"></span>'],
        ['p' => 'Велика', 't' => 'Надійність', 'd' => 'Гарантуємо якість та результат.', 'ico' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
            <polyline points="9 12 11 14 15 10" />
        </svg>'],
        ])
        <div class="why-grid">
            @foreach ($why as $i => $w)
            <div class="why-card" data-aos="fade-up" data-aos-delay="{{ $i * 60 }}">
                <div class="why-icon">{!! $w['ico'] !!}</div>
                <div class="why-title"><span>{{ $w['p'] }}</span> {{ $w['t'] }}</div>
                <div class="why-text">{{ $w['d'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        @include('partials.cta-band')
    </div>
</section>
@endsection