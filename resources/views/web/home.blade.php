{{-- resources/views/web/home.blade.php --}}
@extends('layouts.app')

@php($years = now()->year - config('site.founded_year'))
@php($stats = config('site.stats'))
@php($foundedDate = config('site.founded_date'))

@php($categories = [
['name' => 'Тракторні', 'count' => 'Понад 8 000 позицій', 'img' => 'MICHELIN MEGAXBIB.jpg'],
['name' => 'Комбайні', 'count' => 'Понад 3 000 позицій', 'img' => 'Michelin XMCL.jpg'],
['name' => 'Обприскувачі', 'count' => 'Понад 2 000 позицій', 'img' => 'MICHELIN megaxbib1.jpg'],
['name' => 'Навантажувачі', 'count' => 'Понад 4 500 позицій', 'img' => 'continental AW-FARMER.jpg'],
['name' => 'Спецтехніка', 'count' => 'Понад 6 000 позицій', 'img' => 'continental M 159.jpg'],
['name' => 'Вантажні', 'count' => 'Понад 12 000 позицій', 'img' => '1050-50R32.jpg'],
])

@section('content')
{{-- ============================ HERO ============================ --}}
<section class="hero">
    <div class="hero-media">
        <img src="/images/details/kara.png" alt="" />
    </div>
    <div class="container">
        <div class="hero-inner" data-aos="fade-up">
            <h1 class="hero-title">Підбираємо <span>правильні шини</span> з {{ config('site.founded_year') }} року</h1>
            <p class="hero-sub">Допомагаємо підібрати шини для агро-, спец- та вантажної техніки.</p>

            {{-- Живий лічильник досвіду роботи (з 2009 року) --}}
            <div class="hero-stats" x-data="experienceCounter('{{ $foundedDate }}')">
                <div class="hero-stats-row">
                    <div class="stat stat--accent">
                        <div class="stat-num" x-text="years">{{ $years }}</div>
                        <span class="stat-label">років</span>
                    </div>
                    <div class="stat">
                        <div class="stat-num" x-text="days">0</div>
                        <span class="stat-label">днів</span>
                    </div>
                    <div class="stat">
                        <div class="stat-num" x-text="hours">0</div>
                        <span class="stat-label">годин</span>
                    </div>
                    <div class="stat">
                        <div class="stat-num" x-text="minutes">0</div>
                        <span class="stat-label">хвилин</span>
                    </div>
                </div>
                <div class="hero-stats-caption">підбираємо <b>правильні шини</b></div>
            </div>

            <div class="hero-actions">
                <a href="#pidbir" class="btn btn--light">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                    Знайти шину самостійно
                </a>
                <a href="tel:{{ config('site.contacts.phone_href') }}" class="btn btn--primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                    </svg>
                    Отримати консультацію
                </a>
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
        <form class="filter-bar" action="{{ route('catalog') }}" method="GET">
            <div class="field">
                <label>Тип техніки</label>
                <div class="select">
                    <select name="machinery">
                        <option value="">Оберіть тип</option>
                        <option>Трактори</option>
                        <option>Комбайни</option>
                        <option>Навантажувачі</option>
                        <option>Спецтехніка</option>
                    </select>
                    {!! $chev !!}
                </div>
            </div>
            <div class="field">
                <label>Категорія</label>
                <div class="select">
                    <select name="category">
                        <option value="">Оберіть категорію</option>
                        <option>Агрошини</option>
                        <option>Спецшини</option>
                        <option>Вантажні</option>
                    </select>
                    {!! $chev !!}
                </div>
            </div>
            <div class="field">
                <label>Бренд</label>
                <div class="select">
                    <select name="brand">
                        <option value="">Оберіть бренд</option>
                        <option>BKT</option>
                        <option>Michelin</option>
                        <option>Mitas</option>
                        <option>Continental</option>
                    </select>
                    {!! $chev !!}
                </div>
            </div>
            <div class="field">
                <label>Розмір</label>
                <div class="select">
                    <select name="size">
                        <option value="">Оберіть розмір</option>
                        <option>710/70R38</option>
                        <option>800/65R32</option>
                        <option>520/85R42</option>
                    </select>
                    {!! $chev !!}
                </div>
            </div>
            <button type="submit" class="btn btn--primary filter-submit">Підібрати шини</button>
        </form>
    </div>
</div>

{{-- ============== ПІДБЕРЕМО ДЛЯ ВАШОЇ ТЕХНІКИ ================= --}}
<section class="section machinery"
    x-data="{ scroll(dir) { this.$refs.track.scrollBy({ left: dir * 320, behavior: 'smooth' }) } }">
    <div class="container">
        <div class="section-head" data-aos="fade-up">
            <h2 class="section-title">Підберемо для вашої техніки</h2>
            <div class="mach-nav">
                <button type="button" class="mach-arrow" aria-label="Прокрутити назад" @click="scroll(-1)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6" />
                    </svg>
                </button>
                <button type="button" class="mach-arrow" aria-label="Прокрутити вперед" @click="scroll(1)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6" />
                    </svg>
                </button>
            </div>
        </div>
        <div class="mach-track" x-ref="track">
            @php($machinery = [
            ['Трактори', 'tractor.svg'],
            ['Комбайни', 'combine.svg'],
            ['Обприскувачі', 'sprayer.svg'],
            ['Навантажувачі', 'loaders.svg'],
            ['Вантажні', 'truck.svg'],
            ['Інше', 'wheel.svg'],
            ])
            @foreach ($machinery as $m)
            <a href="{{ route('catalog') }}" class="mach-item">
                <span class="mach-ico mask-ico" style="--m:url('/images/svg/tehnics/{{ $m[1] }}')"></span>
                <span>{{ $m[0] }}</span>
            </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ====================== КАТАЛОГ ШИН ========================== --}}
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
        <div class="cat-grid">
            @foreach ($categories as $i => $cat)
            <a href="{{ route('catalog') }}" class="cat-card" data-aos="fade-up" data-aos-delay="{{ $i * 60 }}">
                <div class="cat-img"><img src="/images/wheels/{{ $cat['img'] }}" alt="{{ $cat['name'] }}"
                        loading="lazy" /></div>
                <div class="cat-name">{{ $cat['name'] }}</div>
                <div class="cat-count">
                    {{ $cat['count'] }}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="5" y1="12" x2="19" y2="12" />
                        <polyline points="12 5 19 12 12 19" />
                    </svg>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ================== ПОПУЛЯРНІ МОДЕЛІ (товари) =============== --}}
@php($products = [
['brand' => 'michelin', 'model' => 'Michelin XMCL', 'size' => '460/70R24', 'price' => 32400, 'stock' => true, 'img' => 'Michelin XMCL.jpg'],
['brand' => 'michelin', 'model' => 'Michelin MegaXBib', 'size' => '620/75R30', 'price' => null, 'stock' => false, 'img' => 'MICHELIN MEGAXBIB.jpg'],
['brand' => 'michelin', 'model' => 'Michelin MegaXBib', 'size' => '800/65R32', 'price' => 45100, 'stock' => true, 'img' => 'MICHELIN megaxbib1.jpg'],
['brand' => 'continental', 'model' => 'Continental AW-Farmer', 'size' => '10.0/75-12', 'price' => null, 'stock' => false, 'img' => 'continental AW-FARMER.jpg'],
['brand' => 'continental', 'model' => 'Continental M 159', 'size' => '10.0/75-15.3', 'size_alt' => '(10-15)', 'price' => 8900, 'stock' => true, 'img' => 'continental M 159.jpg'],
['brand' => 'continental', 'model' => 'Continental AW-Farmer', 'size' => '10.0/75-15.3', 'size_alt' => '(10-15)', 'price' => 9200, 'stock' => true, 'img' => 'continental AW-FARMER.jpg'],
['brand' => 'michelin', 'model' => 'BKT Earthmax SR41', 'size' => '1050/50R32', 'price' => null, 'stock' => true, 'img' => '1050-50R32.jpg'],
['brand' => 'continental', 'model' => 'Michelin XMCL', 'size' => '540/65R28', 'price' => 27800, 'stock' => true, 'img' => 'Michelin XMCL.jpg'],
])
<section class="section" style="padding-top:0">
    <div class="container">
        <div class="section-head" data-aos="fade-up">
            <h2 class="section-title">Популярні моделі</h2>
            <a href="{{ route('catalog') }}" class="section-link">Весь каталог
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="5" y1="12" x2="19" y2="12" />
                    <polyline points="12 5 19 12 12 19" />
                </svg>
            </a>
        </div>
        <div class="product-grid">
            @foreach ($products as $i => $p)
            <a href="{{ route('catalog') }}" class="product-card" data-aos="fade-up" data-aos-delay="{{ ($i % 4) * 60 }}"
                x-data="{ fav: false }">
                <div class="product-image-wrapper">
                    <img class="brand-logo" src="/images/svg/brands/{{ $p['brand'] }}.svg" alt="{{ $p['brand'] }}" />
                    <button type="button" class="favorite-btn" :class="{ active: fav }" @click.prevent.stop="fav = !fav"
                        aria-label="Додати в обране">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path
                                d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                        </svg>
                    </button>
                    @if ($p['stock'])
                    <span class="status-badge in-stock">В наявності</span>
                    @endif
                    <img class="tire-image" src="/images/wheels/{{ $p['img'] }}" alt="{{ $p['model'] }}" loading="lazy" />
                </div>
                <div class="product-info">
                    <div class="product-size">{{ $p['size'] }}
                        @isset($p['size_alt'])<span class="size-alt">{{ $p['size_alt'] }}</span>@endisset
                    </div>
                    <div class="product-model">{{ $p['model'] }}</div>
                    <div class="product-footer">
                        @if ($p['price'])
                        <div class="product-price">{{ number_format($p['price'], 0, '', ' ') }} <span
                                class="currency">грн</span></div>
                        @else
                        <div class="product-price status-ask">Уточнюйте</div>
                        @endif
                        <span class="product-arrow">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="5" y1="12" x2="19" y2="12" />
                                <polyline points="12 5 19 12 12 19" />
                            </svg>
                        </span>
                    </div>
                </div>
            </a>
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
        ['p' => 'Великий', 't' => 'Досвід', 'd' => 'Працюємо з ' . config('site.founded_year') . ' року. Знаємо шини та техніку не з каталогу, а з практики.', 'ico' => '<span class="mask-ico" style="--m:url(\'/images/svg/others/star.svg\')"></span>'],
        ['p' => 'Велика', 't' => 'Довіра', 'd' => 'Нам довіряють клієнти, які працюють з нами роками.', 'ico' => '<span class="mask-ico" style="--m:url(\'/images/svg/others/user-shield.svg\')"></span>'],
        ['p' => 'Велика', 't' => 'Відповідальність', 'd' => 'Підбираємо шини під задачу, а не просто продаємо товар.', 'ico' => '<span class="mask-ico" style="--m:url(\'/images/svg/others/handshake.svg\')"></span>'],
        ['p' => 'Велика', 't' => 'Порядність', 'd' => 'Чесно радимо те, що дійсно підходить і працює.', 'ico' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="6"/><path d="M15.5 13.5 17 22l-5-3-5 3 1.5-8.5"/><path d="M9.5 8l1.7 1.7L14.5 6.5"/></svg>'],
        ['p' => 'Велика', 't' => 'Допомога', 'd' => 'Допомагаємо до, під час і після покупки.', 'ico' => '<span class="mask-ico" style="--m:url(\'/images/svg/others/help.svg\')"></span>'],
        ['p' => 'Велика', 't' => 'Надійність', 'd' => 'Гарантуємо якість та результат.', 'ico' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>'],
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