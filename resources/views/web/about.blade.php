{{-- resources/views/web/about.blade.php --}}
@extends('layouts.app')

@section('title', 'Про нас — Велика Шина | Шини для агро, спец та вантажної техніки з 2009 року')
@section('meta_description', 'Велика Шина — український постачальник шин, камер і дисків для сільгосп-, спеціальної та вантажної техніки з 2009 року. 20 000+ позицій, 253 бренди, 14 складів, доставка по всій Україні.')

@php($years = now()->year - config('site.founded_year'))
@php($stats = config('site.stats'))
@php($foundedYear = config('site.founded_year'))
@php($foundedDate = config('site.founded_date'))
@php($c = config('site.contacts'))

@section('content')

{{-- ======================== HERO ============================= --}}
<section class="about-hero">
    <div class="about-hero__bg">
        <img src="/images/about/portfolio5.jpg" alt="Велика Шина — великогабаритні шини" />
    </div>
    <div class="about-hero__shade"></div>

    <div class="container about-hero__inner">
        <nav class="about-crumbs" aria-label="Хлібні крихти">
            <a href="{{ route('home') }}">Головна</a>
            <span>/</span>
            <span class="current">Про нас</span>
        </nav>

        <span class="about-eyebrow">Офіційний сайт &middot; на ринку з {{ $foundedYear }} року</span>

        <h1 class="about-hero__title">Велика Шина —<br><span>великий досвід</span> у кожній шині</h1>

        <p class="about-hero__lead">
            Підбираємо та постачаємо шини, камери й диски для сільськогосподарської,
            спеціальної та вантажної техніки по всій Україні. Не з каталогу — з практики.
        </p>

        <div class="about-hero__actions">
            <a href="{{ route('catalog') }}" class="btn btn--primary">Перейти до каталогу</a>
            <a href="tel:{{ $c['phone_href'] }}" class="btn btn--ghost-light">
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
                <span class="num">{{ $years }}+</span>
                <span class="lbl">років досвіду</span>
            </div>
            <div class="about-hs">
                <span class="num">500+</span>
                <span class="lbl">задоволених клієнтів</span>
            </div>
            <div class="about-hs">
                <span class="num">100%</span>
                <span class="lbl">оригінальна продукція</span>
            </div>
        </div>
    </div>
</section>

{{-- ===================== ХТО МИ ============================== --}}
<section class="section about-intro">
    <div class="container about-intro__grid">
        <div class="about-intro__media" data-aos="fade-right">
            <img src="/images/about/portfolio8.jpg" alt="Склад та відвантаження Велика Шина" loading="lazy" />
            <div class="about-intro__badge">
                <span class="big">з {{ $foundedYear }}</span>
                <span class="small">року на ринку</span>
            </div>
        </div>

        <div class="about-intro__text" data-aos="fade-left">
            <span class="about-kicker">Хто ми</span>
            <h2 class="section-title">Та сама <span>Велика Шина</span> — лише сучасніша</h2>

            <p class="about-lead">
                «Велика Шина» — українська компанія, що працює на ринку шин для агро-, спец-
                та вантажної техніки з {{ $foundedYear }} року. За цей час ми стали одним із
                найбільших постачальників великогабаритних шин у країні.
            </p>
            <p>
                Ми не просто продаємо шини — ми знаємо техніку, умови експлуатації та задачі,
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
                    Бренди <b>на будь-який бюджет</b> — від світових лідерів до робочих рішень
                </li>
                <li>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                    Ходові розміри <b>в наявності</b>, решта — оперативно під замовлення
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

{{-- ================== ЛІЧИЛЬНИК ДОСВІДУ ====================== --}}
<section class="about-counter">
    <div class="container">
        <div class="about-counter__exp" x-data="experienceCounter('{{ $foundedDate }}')">
            <span class="about-counter__kicker">Працюємо без зупину з {{ $foundedYear }} року</span>
            <div class="about-exp">
                <div class="about-exp__cell"><span class="num" x-text="years">{{ $years }}</span><span class="lbl">років</span></div>
                <span class="about-exp__sep">:</span>
                <div class="about-exp__cell"><span class="num" x-text="days">0</span><span class="lbl">днів</span></div>
                <span class="about-exp__sep">:</span>
                <div class="about-exp__cell"><span class="num" x-text="hours">0</span><span class="lbl">годин</span></div>
                <span class="about-exp__sep">:</span>
                <div class="about-exp__cell"><span class="num" x-text="minutes">0</span><span class="lbl">хвилин</span></div>
            </div>
        </div>
    </div>
</section>

{{-- =============== ЧОМУ ОБИРАЮТЬ ВЕЛИКУ ШИНУ ================= --}}
@php($values = [
['t' => 'Досвід з ' . $foundedYear . ' року', 'd' => 'Понад ' . $years . ' років у шинному бізнесі. Знаємо шини та техніку не з каталогу, а з реальної практики.', 'svg' => '
<path d="M12 2l2.4 7.4H22l-6 4.6 2.3 7.4-6.3-4.6L5.7 21l2.3-7.4-6-4.6h7.6z" />'],
['t' => 'Широкий вибір', 'd' => 'Шини, камери та диски під різну техніку — від світових брендів до робочих бюджетних рішень.', 'svg' => '
<rect x="3" y="3" width="7" height="7" />
<rect x="14" y="3" width="7" height="7" />
<rect x="14" y="14" width="7" height="7" />
<rect x="3" y="14" width="7" height="7" />'],
['t' => 'Експертний підбір', 'd' => 'Підбираємо шину під конкретну техніку та задачу, а не просто «за розміром». Радимо те, що дійсно працює.', 'svg' => '
<circle cx="12" cy="12" r="10" />
<path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
<line x1="12" y1="17" x2="12.01" y2="17" />'],
['t' => 'Наявність і доставка', 'd' => 'Ходові розміри тримаємо в наявності, решту — оперативно привозимо. Доставка по всій Україні.', 'svg' => '
<rect x="1" y="3" width="15" height="13" />
<polygon points="16 8 20 8 23 11 23 16 16 16 16 8" />
<circle cx="5.5" cy="18.5" r="2.5" />
<circle cx="18.5" cy="18.5" r="2.5" />'],
['t' => 'Оригінальна продукція', 'd' => 'Офіційні поставки та оригінальні шини з гарантією від виробника. Жодних сумнівних аналогів.', 'svg' => '
<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
<polyline points="9 12 11 14 15 10" />'],
['t' => 'Чесність і підтримка', 'd' => 'Супроводжуємо до, під час і після покупки. Цінуємо клієнтів, які працюють з нами роками.', 'svg' => '
<path d="M3 18v-6a9 9 0 0 1 18 0v6" />
<path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z" />'],
])
<section class="section">
    <div class="container">
        <div class="section-head about-center" data-aos="fade-up">
            <div>
                <span class="about-kicker">Наші переваги</span>
                <h2 class="section-title">Чому обирають <span>Велику Шину</span></h2>
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
@php($brandsWall = ['Michelin', 'Continental', 'BKT', 'Trelleborg', 'Mitas', 'Galaxy', 'Alliance', 'Nexen', 'Rovelo', 'Kenda'])
@php($brandsRev = array_reverse($brandsWall))
<section class="section about-brands-sec">
    <div class="container">
        <div class="section-head about-center" data-aos="fade-up">
            <div>
                <span class="about-kicker">Офіційні поставки</span>
                <h2 class="section-title">Бренди, яким <span>довіряють</span> аграрії</h2>
                <p class="about-sub">Працюємо напряму зі світовими виробниками шин. Лише оригінальна продукція — жодних сумнівних аналогів.</p>
            </div>
        </div>
    </div>

    <div class="about-brands">
        <div class="about-brands__row">
            <div class="about-brands__track">
                @foreach (array_merge($brandsWall, $brandsWall) as $b)
                <span class="about-brand">{{ $b }}</span>
                @endforeach
            </div>
        </div>
        <div class="about-brands__row">
            <div class="about-brands__track about-brands__track--rev">
                @foreach (array_merge($brandsRev, $brandsRev) as $b)
                <span class="about-brand">{{ $b }}</span>
                @endforeach
            </div>
        </div>
    </div>

    <div class="container">
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
['t' => 'Заявка або дзвінок', 'd' => 'Ви називаєте типорозмір (напр. 800/65R32) або просто свою техніку.'],
['t' => 'Підбір', 'd' => 'Наші спеціалісти підбирають оптимальний варіант під ваші умови та бюджет.'],
['t' => 'Наявність і ціна', 'd' => 'Перевіряємо склад, узгоджуємо ціну та умови оплати й доставки.'],
['t' => 'Доставка', 'd' => 'Відправляємо по всій Україні. Залишаємось на зв\'язку після покупки.'],
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
        <img src="/images/about/portfolio10.jpg" alt="Велика Шина" loading="lazy" />
    </div>
    <div class="about-trust__shade"></div>
    <div class="container about-trust__inner" data-aos="fade-up">
        <div class="about-trust__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                <polyline points="9 12 11 14 15 10" />
            </svg>
        </div>
        <h2 class="about-trust__title">Це офіційний сайт компанії «Велика Шина»</h2>
        <p class="about-trust__text">
            Остерігайтеся підробок. Перед вами офіційний ресурс компанії «Велика Шина»,
            що працює на ринку з {{ $foundedYear }} року. Тут ви завжди отримаєте оригінальну
            продукцію, чесну консультацію та реальну підтримку — ті самі, що й роками раніше.
        </p>
        <div class="about-trust__actions">
            <a href="tel:{{ $c['phone_href'] }}" class="btn btn--primary">Зв'язатися з нами</a>
            <a href="{{ route('catalog') }}" class="btn btn--ghost-light">Перейти до каталогу</a>
        </div>
    </div>
</section>

{{-- ===================== ГАЛЕРЕЯ РОБІТ ======================= --}}
@php($gallery = ['portfolio3.jpg', 'portfolio7.jpg', 'portfolio4.jpg', 'portfolio9.jpg', 'portfolio6.jpg', 'portfolio8.jpg'])
<section class="section" x-data="{ open: false, src: '' }">
    <div class="container">
        <div class="section-head about-center" data-aos="fade-up">
            <div>
                <span class="about-kicker">Наша робота</span>
                <h2 class="section-title">Реальні фото зі <span>складу</span></h2>
                <p class="about-sub">Великогабаритні шини, відвантаження та щоденна робота — без стокових картинок.</p>
            </div>
        </div>

        <div class="about-gallery">
            @foreach ($gallery as $i => $img)
            <button type="button" class="about-gallery__item" data-aos="zoom-in" data-aos-delay="{{ $i % 3 * 70 }}"
                @click="src = '/images/about/{{ $img }}'; open = true">
                <img src="/images/about/{{ $img }}" alt="Велика Шина — фото {{ $i + 1 }}" loading="lazy" />
                <span class="about-gallery__zoom">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        <line x1="11" y1="8" x2="11" y2="14" />
                        <line x1="8" y1="11" x2="14" y2="11" />
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

{{-- ========================= CTA ============================= --}}
<section class="section" style="padding-top:0">
    <div class="container">
        <div class="cta-band" data-aos="fade-up">
            <div class="cta-bg"><img src="/images/back.png" alt="" /></div>
            <div class="cta-content">
                <h3>Не впевнені, які шини потрібні?</h3>
                <p>Наші спеціалісти допоможуть підібрати оптимальний варіант для вашої техніки — швидко й безкоштовно.</p>
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