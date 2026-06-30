<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Адмін панель — Велика Шина</title>
    {{-- Без resources/js/app.js: Alpine надає сам Livewire (@livewireScripts).
         admin.js не стартує Alpine — лише drag-and-drop (SortableJS). --}}
    @vite(['resources/css/app.css', 'resources/css/admin.scss', 'resources/js/admin.js'])
    @livewireStyles
</head>
<body class="admin-body" x-data="{ sidebar: false }">
@php
    $nav = fn (...$p) => request()->routeIs(...$p) ? 'is-active' : '';
    $on = fn (...$p) => request()->routeIs(...$p);
    // Кількість нових (необроблених) заявок — для бейджа в меню
    $newLeads = \App\Models\Lead::where('status', 'new')->count();
    $icons = [
        'home'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/></svg>',
        'box'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8l-9-5-9 5v8l9 5 9-5V8z"/><path d="M3 8l9 5 9-5"/><path d="M12 13v8"/></svg>',
        'inbox'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 13h5l2 3h4l2-3h5"/><path d="M5 5h14l2 8v6H3v-6l2-8z"/></svg>',
        'swap'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 8h13l-3-3"/><path d="M20 16H7l3 3"/></svg>',
        'grid'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>',
        'truck'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="6" width="13" height="10" rx="1"/><path d="M14 9h4l3 3v4h-7z"/><circle cx="6" cy="18" r="2"/><circle cx="17" cy="18" r="2"/></svg>',
        'gear'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M5 5l2 2M17 17l2 2M19 5l-2 2M7 17l-2 2"/></svg>',
        'post'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>',
    ];
@endphp

<div class="admin-shell">
    <aside class="admin-sidebar" :class="{ 'is-open': sidebar }">
        <div class="admin-sidebar__top">
            <button type="button" class="admin-sidebar__toggle" x-on:click.stop="sidebar = !sidebar" aria-label="Меню">
                <span x-show="!sidebar">☰</span>
                <span x-show="sidebar" x-cloak>✕</span>
            </button>
            <a href="{{ route('admin.dashboard') }}" class="admin-sidebar__brand" wire:navigate x-show="sidebar" x-cloak>
                <img src="/images/logo.png" alt="Велика Шина">
            </a>
        </div>

        @php
            // Яка група розгорнута за замовчуванням — за поточним маршрутом.
            $openGroup = $on('admin.categories.*','admin.attributes.*','admin.brands.*','admin.product-types.*') ? 'catalog'
                : ($on('admin.machinery-types.*','admin.machinery-brands.*','admin.machinery-series.*','admin.machinery-models.*','admin.machinery-positions.*','admin.field-photos.*') ? 'tech'
                : ($on('admin.users.*','admin.settings.*','admin.security.*') ? 'system' : ''));
        @endphp
        {{-- openGroup = акордеон: одночасно відкрита лише одна група --}}
        <nav class="admin-nav" x-data="{ openGroup: '{{ $openGroup }}' }">
            <a href="{{ route('admin.dashboard') }}" class="admin-nav__item {{ $nav('admin.dashboard') }}" wire:navigate>
                {!! $icons['home'] !!}<span>Головна</span>
            </a>
            <a href="{{ route('admin.products.index') }}" class="admin-nav__item {{ $nav('admin.products.*') }}" wire:navigate>
                {!! $icons['box'] !!}<span>Товари</span>
            </a>
            <a href="{{ route('admin.leads.index') }}" class="admin-nav__item {{ $nav('admin.leads.*') }}" wire:navigate>
                {!! $icons['inbox'] !!}<span>Заявки</span>
                @if($newLeads > 0)
                    <span class="admin-nav__badge">{{ $newLeads > 99 ? '99+' : $newLeads }}</span>
                @endif
            </a>
            <a href="{{ route('admin.posts.index') }}" class="admin-nav__item {{ $nav('admin.posts.*') }}" wire:navigate>
                {!! $icons['post'] !!}<span>Блог</span>
            </a>
            <a href="{{ route('admin.import-export.index') }}" class="admin-nav__item {{ $nav('admin.import-export.*') }}" wire:navigate>
                {!! $icons['swap'] !!}<span>Імпорт / Експорт</span>
            </a>

            {{-- Каталог --}}
            <div class="admin-nav__group">
                <button type="button" class="admin-nav__head {{ $on('admin.categories.*','admin.attributes.*','admin.brands.*','admin.product-types.*') ? 'is-active' : '' }}" x-on:click="if (sidebar) { openGroup = openGroup === 'catalog' ? '' : 'catalog' } else { sidebar = true; openGroup = 'catalog' }">
                    {!! $icons['grid'] !!}<span>Каталог</span><span class="admin-nav__chev" :class="{ 'is-open': openGroup === 'catalog' }">▶</span>
                </button>
                <div class="admin-nav__sub" x-show="openGroup === 'catalog'" x-transition x-cloak>
                    <a href="{{ route('admin.categories.index') }}" class="admin-nav__item {{ $nav('admin.categories.*') }}" wire:navigate>Категорії</a>
                    <a href="{{ route('admin.attributes.index') }}" class="admin-nav__item {{ $nav('admin.attributes.*') }}" wire:navigate>Характеристики</a>
                    <a href="{{ route('admin.brands.index') }}" class="admin-nav__item {{ $nav('admin.brands.*') }}" wire:navigate>Бренди</a>
                    <a href="{{ route('admin.product-types.index') }}" class="admin-nav__item {{ $nav('admin.product-types.*') }}" wire:navigate>Типи товарів</a>
                </div>
            </div>

            {{-- Техніка --}}
            <div class="admin-nav__group">
                <button type="button" class="admin-nav__head {{ $on('admin.machinery-types.*','admin.machinery-brands.*','admin.machinery-series.*','admin.machinery-models.*','admin.machinery-positions.*','admin.field-photos.*') ? 'is-active' : '' }}" x-on:click="if (sidebar) { openGroup = openGroup === 'tech' ? '' : 'tech' } else { sidebar = true; openGroup = 'tech' }">
                    {!! $icons['truck'] !!}<span>Техніка</span><span class="admin-nav__chev" :class="{ 'is-open': openGroup === 'tech' }">▶</span>
                </button>
                <div class="admin-nav__sub" x-show="openGroup === 'tech'" x-transition x-cloak>
                    <a href="{{ route('admin.machinery-types.index') }}" class="admin-nav__item {{ $nav('admin.machinery-types.*') }}" wire:navigate>Типи техніки</a>
                    <a href="{{ route('admin.machinery-brands.index') }}" class="admin-nav__item {{ $nav('admin.machinery-brands.*') }}" wire:navigate>Виробники</a>
                    <a href="{{ route('admin.machinery-series.index') }}" class="admin-nav__item {{ $nav('admin.machinery-series.*') }}" wire:navigate>Серії</a>
                    <a href="{{ route('admin.machinery-models.index') }}" class="admin-nav__item {{ $nav('admin.machinery-models.*') }}" wire:navigate>Моделі</a>
                    <a href="{{ route('admin.machinery-positions.index') }}" class="admin-nav__item {{ $nav('admin.machinery-positions.*','admin.field-photos.*') }}" wire:navigate>Позиції</a>
                    <a href="{{ route('admin.field-photos.index') }}" class="admin-nav__item {{ $nav('admin.field-photos.*') }}" wire:navigate>Фото в роботі</a>
                </div>
            </div>

            {{-- Система --}}
            <div class="admin-nav__group">
                <button type="button" class="admin-nav__head {{ $on('admin.users.*','admin.settings.*','admin.security.*') ? 'is-active' : '' }}" x-on:click="if (sidebar) { openGroup = openGroup === 'system' ? '' : 'system' } else { sidebar = true; openGroup = 'system' }">
                    {!! $icons['gear'] !!}<span>Система</span><span class="admin-nav__chev" :class="{ 'is-open': openGroup === 'system' }">▶</span>
                </button>
                <div class="admin-nav__sub" x-show="openGroup === 'system'" x-transition x-cloak>
                    <a href="{{ route('admin.users.index') }}" class="admin-nav__item {{ $nav('admin.users.*') }}" wire:navigate>Користувачі</a>
                    <a href="{{ route('admin.settings.index') }}" class="admin-nav__item {{ $nav('admin.settings.*') }}" wire:navigate>Налаштування</a>
                    <a href="{{ route('admin.security.index') }}" class="admin-nav__item {{ $nav('admin.security.*') }}" wire:navigate>Безпека</a>
                </div>
            </div>
        </nav>
    </aside>

    <div class="admin-overlay" :class="{ 'is-open': sidebar }" x-on:click="sidebar = false"></div>

    <div class="admin-content">
        <header class="admin-header">
            <button class="admin-burger" x-on:click.stop="sidebar = !sidebar" aria-label="Меню">☰</button>
            <span class="admin-header__title">Адмін-панель</span>
            <div class="admin-header__spacer"></div>

            <a href="{{ route('admin.leads.index') }}" class="admin-header__leads" wire:navigate title="Нові заявки" aria-label="Заявки">
                {!! $icons['inbox'] !!}
                @if($newLeads > 0)
                    <span class="admin-header__leads-badge">{{ $newLeads > 99 ? '99+' : $newLeads }}</span>
                @endif
            </a>

            <form class="admin-logout" method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" data-confirm="Ви дійсно хочете вийти з акаунту?">Вийти</button>
            </form>
        </header>

        <main class="admin-main">
            {{ $slot }}
        </main>
    </div>
</div>

{{-- Глобальна модалка підтвердження дій (видалення, вихід, збереження тощо) --}}
<x-admin.confirm-modal />

{{-- Глобальні сповіщення (тости) --}}
<div class="admin-toasts"
     x-data="{ items: [] }"
     x-on:notify.window="
        const id = Date.now() + Math.random();
        items.push({ id, message: $event.detail.message, type: $event.detail.type || 'success' });
        setTimeout(() => { items = items.filter(i => i.id !== id) }, 3500);
     ">
    <template x-for="t in items" :key="t.id">
        <div class="admin-toast" :class="'admin-toast--' + t.type"
             x-on:click="items = items.filter(i => i.id !== t.id)">
            <span x-text="t.message"></span>
        </div>
    </template>
</div>

@livewireScripts
</body>
</html>
