{{-- resources/views/web/about.blade.php --}}
@extends('layouts.app')

@section('body_class', 'page-about')

@section('title', 'Про нас - ВЕЛИКА ШИНА | Шини для агро, спец та вантажної техніки з 2009 року')
@section('meta_description', 'ВЕЛИКА ШИНА - український постачальник шин, камер і дисків для сільгосп-, спеціальної та вантажної техніки з 2009 року. 20 000+ позицій, 253 бренди, 14 складів, доставка по всій Україні.')

@php($years = now()->year - config('site.founded_year'))
@php($stats = config('site.stats'))
@php($foundedYear = config('site.founded_year'))
@php($foundedDate = config('site.founded_date'))
@php($c = config('site.contacts'))

@section('content')

{{-- ======================== HERO ============================= --}}
<section class="about-hero">
    <div class="about-hero__bg">
        <img src="/images/about/portfolio5.jpg" alt="ВЕЛИКА ШИНА - великогабаритні шини" />
    </div>
    <div class="about-hero__shade"></div>

    <div class="container about-hero__inner">
        <nav class="about-crumbs" aria-label="Хлібні крихти">
            <a href="{{ route('home') }}">Головна</a>
            <span>/</span>
            <span class="current">Про нас</span>
        </nav>

        <span class="about-eyebrow">Офіційний сайт &middot; на ринку з {{ $foundedYear }} року</span>

        <h1 class="about-hero__title">ВЕЛИКА ШИНА -<br><span>великий досвід</span> у кожній шині</h1>

        <p class="about-hero__lead">
            Підбираємо та постачаємо шини, камери й диски для сільськогосподарської,
            спеціальної та вантажної техніки по всій Україні. Не з каталогу - з практики.
        </p>

        <div class="about-hero__actions">
            <a href="{{ route('catalog') }}" class="btn btn--primary">Обрати шину</a>
            <a href="tel:{{ $c['phone_href'] }}" class="btn btn--dark">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                </svg>
                {{ $c['phone'] }}
            </a>
        </div>

        <div class="about-hero__stats">
            <div class="about-hs">
                <span class="num">{{ $foundedYear }}</span>
                <span class="lbl">рік заснування</span>
            </div>
            <div class="about-hs">
                <span class="num">{{ $stats['positions'] }}</span>
                <span class="lbl">товарних позицій</span>
            </div>
            <div class="about-hs">
                <span class="num">100%</span>
                <span class="lbl">оригінальна продукція</span>
            </div>
            <div class="about-hs">
                <span class="num">24/7</span>
                <span class="lbl">ми на зв'язку</span>
            </div>
        </div>
    </div>
</section>

{{-- ===================== ХТО МИ ============================== --}}
<section class="section about-intro">
    <div class="container about-intro__grid">
        <div class="about-intro__media" data-aos="fade-right">
            <img src="/images/about/portfolio8.jpg" alt="Склад та відвантаження ВЕЛИКА ШИНА" loading="lazy" />
            <div class="about-intro__badge">
                <span class="big">з {{ $foundedYear }}</span>
                <span class="small">року на ринку</span>
            </div>
        </div>

        <div class="about-intro__text" data-aos="fade-left">
            <span class="about-kicker">Хто ми</span>
            <h2 class="section-title">Та сама <span>ВЕЛИКА ШИНА</span> - лише сучасніша</h2>

            <p class="about-lead">
                ВЕЛИКА ШИНА - українська компанія, що працює на ринку шин для агро-, спец-
                та вантажної техніки з {{ $foundedYear }} року. За цей час ми стали одним із
                найбільших постачальників великогабаритних шин у країні.
            </p>
            <p>
                Ми не просто продаємо шини - ми знаємо техніку, умови експлуатації та задачі,
                які вона виконує. Тому підбираємо саме те, що працюватиме у полі, у кар'єрі
                чи на дорозі, а не просто «підходить за розміром».
            </p>

            <ul class="about-points">
                <li>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                    Шини, камери та диски для <b>агро-, спец- та вантажної</b> техніки
                </li>
                <li>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                    Бренди <b>на будь-який бюджет</b> - від світових лідерів до робочих рішень
                </li>
                <li>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                    Ходові розміри <b>в наявності</b>, решта - оперативно під замовлення
                </li>
                <li>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                    Офіційні поставки та оригінальна продукція <b>з гарантією</b>
                </li>
            </ul>
        </div>
    </div>
</section>

{{-- ================== РЕБРЕНДИНГ (слайдер до/після) ========= --}}
@php($rebrandPoints = [
['t' => 'Новий вигляд', 'svg' => '<rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>'],
['t' => 'Новий сайт', 'svg' => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>'],
['t' => 'Ті самі люди', 'svg' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
['t' => 'Ті самі цінності', 'svg' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>'],
])
<section class="section rebrand">
    <div class="container">
        <div class="section-head about-center" data-aos="fade-up">
            <div>
                <span class="about-kicker">{{ $years }} років розвитку</span>
                <h2 class="section-title">Оновились зовні - <span>лишились собою</span></h2>
                <p class="about-sub">Новий сайт і сучасний вигляд - та сама команда, досвід і цінності з {{ $foundedYear }} року.</p>
            </div>
        </div>

        {{-- Інтерактивний слайдер «до / після» --}}
        <div class="ba" x-data="logoReveal()" x-ref="frame" data-aos="fade-up"
            @pointerdown="down($event)" @pointermove="moveTo($event)"
            @pointerup="up()" @pointerleave="up()" @pointercancel="up()">

            {{-- Нове лого - базовий шар --}}
            <div class="ba__pane ba__pane--new">
                <img src="/images/logo.png" alt="Нове лого ВЕЛИКА ШИНА" draggable="false" loading="lazy" />
            </div>

            {{-- Старе лого - верхній шар, обрізається повзунком --}}
            <div class="ba__pane ba__pane--old" :style="`clip-path: inset(0 ${100 - pos}% 0 0)`">
                <img src="/images/old-logo.png" alt="Старе лого ВЕЛИКА ШИНА" draggable="false" loading="lazy" />
            </div>

            {{-- Роздільник + ручка --}}
            <div class="ba__divider" :style="`left: ${pos}%`">
                <span class="ba__handle">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m9 7-5 5 5 5" /><path d="m15 7 5 5-5 5" />
                    </svg>
                </span>
            </div>
        </div>

        {{-- Що змінилось / що лишилось --}}
        <ul class="rebrand__points" data-aos="fade-up">
            @foreach ($rebrandPoints as $p)
            <li>
                <span class="rebrand__ico">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $p['svg'] !!}</svg>
                </span>
                {{ $p['t'] }}
            </li>
            @endforeach
        </ul>
    </div>
</section>

{{-- ================== ЛІЧИЛЬНИК ДОСВІДУ ====================== --}}
@include('partials.experience-counter')

{{-- =============== ЧОМУ ОБИРАЮТЬ ВЕЛИКУ ШИНУ ================= --}}
@php($values = [
['t' => $years . ' років досвіду', 'd' => 'Працюємо в агро, індустрії та з вантажною технікою. Нам довіряють з ' . $foundedYear . ' року.', 'svg' => '
<circle cx="12" cy="8" r="6" />
<path d="M15.5 13.5 17 22l-5-3-5 3 1.5-8.5" />'],
['t' => 'Великий асортимент', 'd' => 'Шини, камери та диски під різну техніку - від світових брендів до робочих бюджетних рішень.', 'svg' => '
<rect x="3" y="3" width="7" height="7" />
<rect x="14" y="3" width="7" height="7" />
<rect x="14" y="14" width="7" height="7" />
<rect x="3" y="14" width="7" height="7" />'],
['t' => 'Власний склад', 'd' => 'Ходові розміри тримаємо в наявності - швидка доставка по всій Україні.', 'svg' => '
<path d="M3 21V8l9-5 9 5v13" />
<path d="M9 21v-7h6v7" />
<line x1="2" y1="21" x2="22" y2="21" />'],
['t' => 'Якісний сервіс', 'd' => 'Проконсультуємо та допоможемо замовити. Самі користуємось технікою - тож знаємо, що радимо.', 'svg' => '
<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
<polyline points="9 12 11 14 15 10" />'],
])
<section class="section">
    <div class="container">
        <div class="section-head about-center" data-aos="fade-up">
            <div>
                <span class="about-kicker">Наші переваги</span>
                <h2 class="section-title">Чому обирають <span>ВЕЛИКА ШИНА</span></h2>
            </div>
        </div>
        <div class="about-values">
            @foreach ($values as $i => $v)
            <div class="about-value" data-aos="fade-up" data-aos-delay="{{ $i % 3 * 80 }}">
                <div class="about-value__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">{!! $v['svg'] !!}</svg>
                </div>
                <h3 class="about-value__title">{{ $v['t'] }}</h3>
                <p class="about-value__text">{{ $v['d'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- =============== БРЕНДИ, ЯКІ МИ ПОСТАЧАЄМО ================= --}}
@php($brandLogos = [
['name' => 'Michelin', 'img' => 'michelin.png'],
['name' => 'BKT', 'img' => 'BKTlogo.jpg'],
['name' => 'Mitas', 'img' => 'mitas.png'],
['name' => 'Alliance', 'img' => 'alliance.png'],
['name' => 'Trelleborg', 'img' => 'Trelleborg.svg'],
['name' => 'Ceat', 'img' => 'Ceat.jpg'],
['name' => 'Kabat', 'img' => 'kabat.jpg'],
])
<section class="section about-brands-sec">
    <div class="container">
        <div class="section-head about-center" data-aos="fade-up">
            <div>
                <span class="about-kicker">Офіційні поставки</span>
                <h2 class="section-title">Працюємо з провідними <span>світовими брендами</span></h2>
                <p class="about-sub">Допомагаємо знайти правильну шину серед продукції провідних світових брендів.</p>
            </div>
        </div>

        {{-- Логотипи брендів: тягнемо з БД (активні бренди з лого), статичний
             список - резерв, якщо в БД ще нічого не завантажено. Стрічка
             автопрокручується - гарно виглядає і на 7, і на 40 брендів. --}}
        @php($logos = ($brands ?? collect())
            ->map(fn ($b) => ['name' => $b->name, 'src' => $b->logoUrl()])
            ->filter(fn ($x) => ! empty($x['src']))
            ->values())
        @php($logos = $logos->isNotEmpty()
            ? $logos
            : collect($brandLogos)->map(fn ($b) => ['name' => $b['name'], 'src' => '/images/company-logo/' . $b['img']]))
        @php($marqueeDur = max(18, $logos->count() * 3.2))

        <div class="brand-marquee" data-aos="fade-up" style="--marquee-dur: {{ $marqueeDur }}s">
            <div class="brand-marquee__track">
                {{-- Клік по лого → каталог, відфільтрований за цим брендом.
                     Два однакові набори поспіль - для безшовного циклу прокрутки;
                     клони (другий набір) ховаємо від фокусу/скрінрідерів. --}}
                @foreach ($logos as $b)
                <a href="{{ route('catalog') }}?brand={{ urlencode($b['name']) }}"
                    class="about-logo brand-marquee__item" title="Усі товари {{ $b['name'] }}">
                    <img src="{{ $b['src'] }}" alt="{{ $b['name'] }}" loading="lazy" />
                </a>
                @endforeach
                @foreach ($logos as $b)
                <a href="{{ route('catalog') }}?brand={{ urlencode($b['name']) }}"
                    class="about-logo brand-marquee__item" aria-hidden="true" tabindex="-1">
                    <img src="{{ $b['src'] }}" alt="" loading="lazy" />
                </a>
                @endforeach
            </div>
        </div>

        <div class="about-brands__note" data-aos="fade-up">
            @foreach (['Офіційний постачальник', 'Оригінальна продукція', 'Гарантія від виробника'] as $note)
            <span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12" />
                </svg>
                {{ $note }}
            </span>
            @endforeach
        </div>
    </div>
</section>

{{-- ==================== ЯК МИ ПРАЦЮЄМО ======================= --}}
@php($steps = [
['t' => 'Заявка або дзвінок', 'd' => 'Ви називаєте типорозмір (напр. 800/65R32) або просто свою техніку.', 'svg' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>'],
['t' => 'Підбір', 'd' => 'Наші спеціалісти підбирають оптимальний варіант під ваші умови та бюджет.', 'svg' => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>'],
['t' => 'Наявність і ціна', 'd' => 'Перевіряємо склад, узгоджуємо ціну та умови оплати й доставки.', 'svg' => '<path d="M20.59 13.41 13.42 20.6a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/>'],
['t' => 'Доставка', 'd' => 'Відправляємо по всій Україні. Залишаємось на зв\'язку після покупки.', 'svg' => '<rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>'],
])
<section class="section about-steps-sec">
    <div class="container">
        <div class="section-head about-center" data-aos="fade-up">
            <div>
                <span class="about-kicker">Все просто</span>
                <h2 class="section-title">Як ми <span>працюємо</span></h2>
            </div>
        </div>
        <div class="about-steps">
            @foreach ($steps as $i => $s)
            <div class="about-step" data-aos="fade-up" data-aos-delay="{{ $i * 70 }}">
                <span class="about-step__num">{{ $i + 1 }}</span>
                <span class="about-step__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $s['svg'] !!}</svg>
                </span>
                <h3 class="about-step__title">{{ $s['t'] }}</h3>
                <p class="about-step__text">{{ $s['d'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ================= ОФІЦІЙНИЙ САЙТ / ДОВІРА ================= --}}
<section class="about-trust">
    <div class="about-trust__bg">
        <img src="/images/details/back_wheels.jpg" alt="Вивіска ВЕЛИКА ШИНА на складі" loading="lazy" />
    </div>
    <div class="about-trust__shade"></div>
    <div class="container about-trust__inner" data-aos="fade-up">
        <div class="about-trust__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                <polyline points="9 12 11 14 15 10" />
            </svg>
        </div>
        <h2 class="about-trust__title">Це офіційний сайт компанії ВЕЛИКА&nbsp;ШИНА</h2>
        <p class="about-trust__text">
            Остерігайтеся шахраїв! Перед вами офіційний ресурс компанії ВЕЛИКА&nbsp;ШИНА,
            яка працює на ринку з {{ $foundedYear }} року. Тут ви завжди отримаєте оригінальну
            продукцію, чесну консультацію, реальну підтримку та правильну шину для вашої техніки.
        </p>
        <p class="about-trust__text about-trust__text--accent">
            Та сама ВЕЛИКА&nbsp;ШИНА, яку усі добре знають з {{ $foundedYear }} року.
        </p>
        <div class="about-trust__actions">
            <a href="{{ route('catalog') }}" class="btn btn--primary">Обрати шину</a>
            <a href="tel:{{ $c['phone_href'] }}" class="btn btn--dark">{{ $c['phone'] }}</a>
        </div>
    </div>
</section>

{{-- ===================== ГАЛЕРЕЯ РОБІТ ======================= --}}
@php($gallery = \App\Livewire\Admin\SiteSettings\SiteContent::readJson('work_gallery', \App\Livewire\Admin\SiteSettings\SiteContent::DEFAULT_GALLERY))
<section class="section" x-data="{ open: false, src: '' }">
    <div class="container">
        <div class="section-head about-center" data-aos="fade-up">
            <div>
                <h2 class="section-title">ВЕЛИКА ШИНА <span>В РОБОТІ</span></h2>
            </div>
        </div>

        <div class="about-gallery">
            @foreach ($gallery as $i => $g)
            <button type="button" class="about-gallery__item" data-aos="zoom-in" data-aos-delay="{{ $i % 3 * 70 }}"
                @click="src = @js($g['img']); open = true">
                {{-- alt лишаємо описовим - саме воно несе SEO/доступність;
                     видимі підписи-бейджі прибрано (візуально чистіше). --}}
                <img src="{{ $g['img'] }}" alt="{{ $g['cap'] ?? '' }}" loading="lazy" />
                <span class="about-gallery__zoom">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" /><line x1="21" y1="21" x2="16.65" y2="16.65" /><line x1="11" y1="8" x2="11" y2="14" /><line x1="8" y1="11" x2="14" y2="11" />
                    </svg>
                </span>
            </button>
            @endforeach
        </div>
    </div>

    {{-- Лайтбокс --}}
    <div class="about-lightbox" x-show="open" x-cloak x-transition.opacity @click="open = false"
        @keydown.escape.window="open = false">
        <button type="button" class="about-lightbox__close" aria-label="Закрити">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
        </button>
        <img :src="src" alt="" @click.stop />
    </div>
</section>

{{-- ==================== НАШ ПІДХІД =========================== --}}
@php($approach = [
['t' => 'Техніка', 'ico' => '<span class="mask-ico" style="-webkit-mask-image:url(\'/images/svg/tehnics/tractor.svg\');mask-image:url(\'/images/svg/tehnics/tractor.svg\')"></span>'],
['t' => 'Умови роботи', 'ico' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/></svg>'],
['t' => 'Завдання', 'ico' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M9 14l2 2 4-4"/></svg>'],
['t' => 'Підбір', 'ico' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18h6"/><path d="M10 22h4"/><path d="M15.09 14c.18-.98.65-1.74 1.41-2.5A4.65 4.65 0 0 0 18 8 6 6 0 0 0 6 8c0 1 .23 2.23 1.5 3.5.76.76 1.23 1.52 1.41 2.5"/></svg>'],
['t' => 'Результат', 'ico' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>'],
])
<section class="section approach-sec">
    <div class="container">
        <div class="section-head about-center" data-aos="fade-up">
            <div>
                <span class="about-kicker">Наш підхід</span>
                <h2 class="section-title">Спочатку розуміємо техніку.<br>Потім <span>підбираємо шину</span></h2>
            </div>
        </div>

        <div class="approach-flow" data-aos="fade-up">
            @foreach ($approach as $a)
            <div class="approach-step">
                <span class="approach-step__icon">{!! $a['ico'] !!}</span>
                <span class="approach-step__label">{{ $a['t'] }}</span>
            </div>
            @if (! $loop->last)
            <svg class="approach-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12" /><polyline points="12 5 19 12 12 19" />
            </svg>
            @endif
            @endforeach
        </div>

        <p class="approach-note">Чесний підбір та індивідуальний підхід з {{ $foundedYear }} року</p>
    </div>
</section>

{{-- ==================== ЧАСТІ ЗАПИТАННЯ ====================== --}}
@php($faqs = \App\Livewire\Admin\SiteSettings\SiteContent::readJson('faq_items', \App\Livewire\Admin\SiteSettings\SiteContent::DEFAULT_FAQS))
<section class="section faq-sec">
    <div class="container">
        <div class="section-head about-center" data-aos="fade-up">
            <div>
                <span class="about-kicker">FAQ</span>
                <h2 class="section-title">Часті <span>запитання</span></h2>
            </div>
        </div>

        <div class="faq" x-data="{ open: 0 }" data-aos="fade-up">
            @foreach ($faqs as $i => $f)
            <div class="faq-item" :class="{ 'is-open': open === {{ $i }} }">
                <button type="button" class="faq-q" @click="open = open === {{ $i }} ? null : {{ $i }}">
                    <span>{{ $f['q'] }}</span>
                    <svg class="faq-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9" />
                    </svg>
                </button>
                <div class="faq-a">
                    <p>{{ $f['a'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ========================= CTA ============================= --}}
<section class="section" style="padding-top:0">
    <div class="container">
        <div class="cta-band" data-aos="fade-up">
            <div class="cta-bg"><img src="/images/back.png" alt="" /></div>
            <div class="cta-content">
                <h3>Не впевнені, які шини потрібні?</h3>
                <p>Правильний підбір шин починається з розуміння вашої техніки та умов роботи. Ми допоможемо знайти правильне рішення.</p>
            </div>
            <div class="cta-actions">
                <a href="{{ route('catalog') }}" class="btn btn--primary">Підібрати шини</a>
                <a href="tel:{{ $c['phone_href'] }}" class="btn btn--ghost-light">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                    </svg>
                    {{ $c['phone'] }}
                </a>
            </div>
        </div>
    </div>
</section>
@endsection