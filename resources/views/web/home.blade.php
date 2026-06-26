{{-- resources/views/web/home.blade.php --}}
@extends('layouts.app')

@php($years = now()->year - config('site.founded_year'))
@php($stats = config('site.stats'))
@php($foundedDate = config('site.founded_date'))

{{-- Категорії: з БД ($dbCategories), інакше — демо-заглушка. --}}
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
{{-- ===================== HERO-СЛАЙДЕР ======================= --}}
@php($slides = [
['w1' => 'ВЕЛИКА', 'w2' => 'ДОВІРА', 'sub' => $years . ' років досвіду'],
['w1' => 'ВЕЛИКИЙ', 'w2' => 'СКЛАД', 'sub' => 'офіційні поставки з усього світу'],
['w1' => 'ВЕЛИКІ', 'w2' => 'ПРОФІ', 'sub' => 'підбираємо правильні шини'],
])
<section class="hero-slider" x-data="heroSlider(@js($slides))" @mouseenter="stop()" @mouseleave="start()">
    <div class="hs-bg">
        <div class="hs-slide" :class="{ active: active === 0 }">
            <video class="hs-media" x-ref="v0" muted loop playsinline preload="none"
                poster="/images/details/slide1.png">
                <source src="/images/details/slide1.mp4" type="video/mp4" />
            </video>
        </div>
        <div class="hs-slide" :class="{ active: active === 1 }">
            <video class="hs-media" x-ref="v1" muted loop playsinline preload="none"
                poster="/images/details/kara.png">
                <source src="/images/details/slide2.mp4" type="video/mp4" />
            </video>
        </div>
        <div class="hs-slide" :class="{ active: active === 2 }">
            <img class="hs-media" src="/images/details/slide3.png" alt="" />
        </div>
    </div>
    <div class="hs-shade"></div>

    <div class="hs-content">
        <div class="container">
            <div class="hs-text" :key="active" x-transition.opacity.duration.500ms>
                <h1 class="hs-title">
                    <span x-text="slides[active].w1">ВЕЛИКА</span>
                    <span x-text="slides[active].w2">ДОВІРА</span>
                </h1>
                <p class="hs-sub" x-text="slides[active].sub">{{ $years }} років досвіду</p>
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

{{-- ========================= ФІЛЬТР ============================ --}}
@php($chev = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
    <polyline points="6 9 12 15 18 9" />
</svg>')
<div class="filter-bar-wrap" id="pidbir">
    <div class="container">
        <form class="filter-bar" action="{{ route('catalog') }}" method="GET"
            x-data="homeFilter(@js($filters))" :class="{ 'is-loading': loading }">
            <div class="field">
                <label>Тип техніки</label>
                <div class="select">
                    <select name="machinery" x-model="machinery" @change="refresh('machinery')">
                        <option value="">Всі</option>
                        <template x-for="o in machineryOptions" :key="o.value">
                            <option :value="o.value" x-text="o.label"></option>
                        </template>
                    </select>
                    {!! $chev !!}
                </div>
            </div>
            <div class="field">
                <label>Категорія</label>
                <div class="select">
                    <select name="category" x-model="category" @change="refresh('category')">
                        <option value="">Всі</option>
                        <template x-for="o in categoryOptions" :key="o.value">
                            <option :value="o.value" x-text="o.label"></option>
                        </template>
                    </select>
                    {!! $chev !!}
                </div>
            </div>
            <div class="field">
                <label>Бренд</label>
                <div class="select">
                    <select name="brand" x-model="brand" @change="refresh('brand')">
                        <option value="">Всі</option>
                        <template x-for="o in brandOptions" :key="o.value">
                            <option :value="o.value" x-text="o.label"></option>
                        </template>
                    </select>
                    {!! $chev !!}
                </div>
            </div>
            <div class="field">
                <label>Розмір</label>
                <div class="select">
                    <select name="size" x-model="size" @change="refresh('size')">
                        <option value="">Всі</option>
                        <template x-for="o in sizeOptions" :key="o.value">
                            <option :value="o.value" x-text="o.label"></option>
                        </template>
                    </select>
                    {!! $chev !!}
                </div>
            </div>
            <button type="submit" class="btn btn--primary filter-submit">Підібрати шини</button>
        </form>
    </div>
</div>

{{-- ===================== ДЛЯ ВАШОЇ ТЕХНІКИ ===================== --}}
<section class="section machinery"
    x-data="{ scroll(dir) { this.$refs.track.scrollBy({ left: dir * 320, behavior: 'smooth' }) } }">
    <div class="container">
        <div class="section-head" data-aos="fade-up">
            <h2 class="section-title">Для вашої техніки</h2>
        </div>
        <div class="mach-wrap">
            <button type="button" class="mach-arrow mach-arrow--prev" aria-label="Прокрутити назад"
                @click="scroll(-1)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
            </button>
            <div class="mach-track" x-ref="track">
            {{-- Техніка: з БД ($dbMachinery), інакше — демо-заглушка. --}}
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

{{-- ================= ЛІЧИЛЬНИК ДОСВІДУ ======================== --}}
@include('partials.experience-counter')

{{-- ================== КАТАЛОГ ШИН (товари) =================== --}}
{{-- Товари: з БД ($dbProducts), інакше — демо-заглушка. --}}
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

{{-- ======================= ПЕРЕВАГИ =========================== --}}
<section class="section features">
    <div class="container">
        <div class="features-grid">
            <div class="feature" data-aos="fade-up">
                <div class="f-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2l2.4 7.4H22l-6 4.6 2.3 7.4-6.3-4.6L5.7 21l2.3-7.4-6-4.6h7.6z" />
                    </svg>
                </div>
                <div class="f-title">Великий досвід</div>
                <div class="f-text">Працюємо з {{ config('site.founded_year') }} року. Знаємо шини та техніку не з
                    каталогу, а з практики.</div>
            </div>
            <div class="feature" data-aos="fade-up" data-aos-delay="80">
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

{{-- ==================== ЧОМУ ВЕЛИКА ШИНА ====================== --}}
<section class="section">
    <div class="container">
        <div class="section-head" data-aos="fade-up" style="flex-direction:column;align-items:flex-start;gap:8px">
            <h2 class="section-title">Чому <span>Велика Шина</span>?</h2>
            <p style="color:#6b7280"><b style="color:#e31e24">ВЕЛИКА</b> — не тільки про розмір шини.</p>
        </div>
        @php($why = [
        ['p' => 'Великий', 't' => 'Досвід', 'd' => 'Працюємо з ' . config('site.founded_year') . ' року. Знаємо шини та техніку не з каталогу, а з практики.', 'ico' => '<span class="mask-ico" style="-webkit-mask-image:url(\'/images/svg/others/star.svg\');mask-image:url(\'/images/svg/others/star.svg\')"></span>'],
        ['p' => 'Велика', 't' => 'Довіра', 'd' => 'Нам довіряють клієнти, які працюють з нами роками.', 'ico' => '<span class="mask-ico" style="-webkit-mask-image:url(\'/images/svg/others/user-shield.svg\');mask-image:url(\'/images/svg/others/user-shield.svg\')"></span>'],
        ['p' => 'Велика', 't' => 'Відповідальність', 'd' => 'Підбираємо шини під задачу, а не просто продаємо товар.', 'ico' => '<span class="mask-ico" style="-webkit-mask-image:url(\'/images/svg/others/handshake.svg\');mask-image:url(\'/images/svg/others/handshake.svg\')"></span>'],
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

{{-- ======================== CTA БАНЕР ========================= --}}
<section class="section">
    <div class="container">
        <div class="cta-band" data-aos="fade-up">
            <div class="cta-bg"><img src="/images/back.png" alt="" /></div>
            <div class="cta-content">
                <h3>Не впевнені, які шини потрібні?</h3>
                <p>Наші спеціалісти допоможуть підібрати оптимальний варіант для вашої техніки.</p>
            </div>
            <div class="cta-actions">
                <a href="#" class="btn btn--primary">Отримати консультацію</a>
                <a href="tel:{{ config('site.contacts.phone_href') }}" class="btn btn--ghost-light">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                    </svg>
                    {{ config('site.contacts.phone') }}
                </a>
            </div>
        </div>
    </div>
</section>
@endsection