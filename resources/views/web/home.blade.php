{{-- resources/views/web/home.blade.php --}}
@extends('layouts.app')

@section('title', 'Велика Шина — Великі шини для великих машин')

@php
    // Тимчасові дані «Популярних моделей». На етапі каталогу замінюються вибіркою з БД.
    $popular = [
        ['brand' => 'michelin', 'brand_alt' => 'Michelin', 'in_stock' => true, 'image' => 'Michelin XMCL.jpg', 'size' => '460/70R24', 'model' => 'MICHELIN XMCL', 'price' => 32400],
        ['brand' => 'michelin', 'brand_alt' => 'Michelin', 'in_stock' => false, 'image' => 'MICHELIN MEGAXBIB.jpg', 'size' => '620/75R30', 'model' => 'MICHELIN MEGAXBIB', 'price' => null],
        ['brand' => 'michelin', 'brand_alt' => 'Michelin', 'in_stock' => true, 'image' => 'MICHELIN megaxbib1.jpg', 'size' => '800/65R32', 'model' => 'MICHELIN MEGAXBIB', 'price' => 45100],
        ['brand' => 'continental', 'brand_alt' => 'Continental', 'in_stock' => false, 'image' => 'continental AW-FARMER.jpg', 'size' => '10.0/75-12', 'model' => 'CONTINENTAL AW-FARMER', 'price' => null],
        ['brand' => 'continental', 'brand_alt' => 'Continental', 'in_stock' => true, 'image' => 'continental M 159.jpg', 'size' => '10.0/75-15.3', 'size_alt' => '(10-15)', 'model' => 'CONTINENTAL M 159', 'price' => 8900],
        ['brand' => 'continental', 'brand_alt' => 'Continental', 'in_stock' => true, 'image' => 'continental AW-FARMER.jpg', 'size' => '10.0/75-15.3', 'size_alt' => '(10-15)', 'model' => 'CONTINENTAL AW-FARMER', 'price' => 9200],
    ];
@endphp

@section('content')
    <section class="hero">
        <div class="container hero-inner">
            <div class="filter-box glass-panel" data-aos="fade-up" data-aos-duration="1000">
                <h1 class="hero-title">Знайдіть ідеальні шини для вашої техніки</h1>
                <form action="{{ route('catalog') }}" method="GET">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label>Посадочний діаметр</label>
                            <div class="custom-select">
                                <select name="diameter">
                                    <option value="">Оберіть діаметр</option>
                                    <option value="24">24"</option>
                                    <option value="38">38"</option>
                                </select>
                                <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </div>
                        </div>
                        <div class="filter-group">
                            <label>Типорозмір</label>
                            <div class="custom-select">
                                <select name="size">
                                    <option value="">Оберіть розмір</option>
                                    <option value="710/70">710/70</option>
                                </select>
                                <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </div>
                        </div>
                        <div class="filter-group">
                            <label>Бренд</label>
                            <div class="custom-select">
                                <select name="brand">
                                    <option value="">Всі бренди</option>
                                    <option value="michelin">Michelin</option>
                                    <option value="mitas">Mitas</option>
                                </select>
                                <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </div>
                        </div>
                        <button type="submit" class="btn-submit">Підібрати</button>
                    </div>
                    <div class="search-bar">
                        <input type="text" name="q" id="hero-search-input" placeholder="Наприклад: 710/70 R42" />
                        <button type="submit" aria-label="Шукати">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>

            <div class="features">
                <div class="feature-item glass-panel" data-aos="fade-up" data-aos-delay="200">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <circle cx="12" cy="12" r="6"></circle>
                        <circle cx="12" cy="12" r="2"></circle>
                        <line x1="12" y1="2" x2="12" y2="6"></line>
                        <line x1="12" y1="18" x2="12" y2="22"></line>
                        <line x1="2" y1="12" x2="6" y2="12"></line>
                        <line x1="18" y1="12" x2="22" y2="12"></line>
                    </svg>
                    <span>Максимальна продуктивність</span>
                </div>
                <div class="feature-item glass-panel" data-aos="fade-up" data-aos-delay="400">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <polyline points="12 6 12 12 16 14" />
                    </svg>
                    <span>Швидка відправка</span>
                </div>
                <div class="feature-item glass-panel" data-aos="fade-up" data-aos-delay="600">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        <polyline points="9 12 11 14 15 10"></polyline>
                    </svg>
                    <span>Гарантія якості</span>
                </div>
            </div>
        </div>
    </section>

    <section class="machinery-bar">
        <div class="container">
            <div class="machinery-grid">
                <a href="#" class="machinery-card" data-aos="zoom-in" data-aos-delay="100">
                    <img src="/images/svg/tractor.svg" alt="Трактори" />
                    <span>Трактори</span>
                </a>
                <a href="#" class="machinery-card" data-aos="zoom-in" data-aos-delay="200">
                    <img src="/images/svg/combine.svg" alt="Комбайни" />
                    <span>Комбайни</span>
                </a>
                <a href="#" class="machinery-card" data-aos="zoom-in" data-aos-delay="300">
                    <img src="/images/svg/loaders.svg" alt="Навантажувачі" />
                    <span>Навантажувачі</span>
                </a>
                <a href="#" class="machinery-card" data-aos="zoom-in" data-aos-delay="400">
                    <img src="/images/svg/truck.svg" alt="Вантажівки" />
                    <span>Вантажівки</span>
                </a>
            </div>
        </div>
    </section>

    <section class="catalog-section">
        <div class="container">
            <h2 class="section-title" data-aos="fade-right">Популярні моделі шин</h2>
            <div class="catalog-grid">
                @foreach ($popular as $i => $item)
                    <a href="#" class="product-card" data-aos="fade-up" data-aos-delay="{{ ($i + 1) * 100 }}"
                        x-data="{ fav: false }">
                        <div class="product-image-wrapper">
                            <img src="/images/svg/brands/{{ $item['brand'] }}.svg" alt="{{ $item['brand_alt'] }}"
                                class="brand-logo" />
                            <div class="favorite-btn" :class="{ 'active': fav }" title="Додати в улюблене"
                                @click.prevent.stop="fav = !fav">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path
                                        d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z">
                                    </path>
                                </svg>
                            </div>
                            @if ($item['in_stock'])
                                <div class="status-badge in-stock">В наявності</div>
                            @endif
                            <img src="/images/wheels/{{ $item['image'] }}" alt="{{ $item['model'] }}"
                                class="tire-image" />
                        </div>
                        <div class="product-info">
                            <div class="product-size">
                                {{ $item['size'] }}
                                @isset($item['size_alt'])
                                    <span class="size-alt">{{ $item['size_alt'] }}</span>
                                @endisset
                            </div>
                            <div class="product-model">{{ $item['model'] }}</div>
                            <div class="product-footer">
                                @if (! is_null($item['price']))
                                    <div class="product-price">
                                        {{ number_format($item['price'], 0, '', ' ') }}
                                        <span class="currency">грн</span>
                                    </div>
                                @else
                                    <div class="product-price status-ask">Уточнюйте у менеджера</div>
                                @endif
                                <div class="product-arrow">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                        <polyline points="12 5 19 12 12 19"></polyline>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="about-section">
        <div class="container about-inner">
            <div class="about-content" data-aos="fade-right" data-aos-duration="1000">
                <h2 class="about-title">
                    Велика Шина — допомагаємо зробити правильний вибір з 2009 р.
                </h2>
                <div class="title-divider"></div>
                <p class="about-text">
                    <strong>Велика Шина</strong> — ваш надійний постачальник шин та
                    камер для сільськогосподарської та спеціалізованої техніки. У нас
                    широкий вибір шин та камер на навантажувач, екскаватор, кран,
                    грейдер, комбайн, трактор, обприскувач та ін. техніку.
                </p>
                <p class="about-text">
                    Наша команда експертів готова допомогти вам з правильним вибором шин
                    для забезпечення оптимальної продуктивності вашої техніки. Ми любимо
                    і знаємо те, що робимо та пишаємось цим.
                </p>
            </div>
            <div class="about-visual" data-aos="fade-left" data-aos-duration="1000">
                <img src="/images/logo.png" alt="Велика Шина" class="about-logo" />
            </div>
        </div>
    </section>
@endsection
