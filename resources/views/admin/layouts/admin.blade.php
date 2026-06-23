<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Адмін панель — Велика Шина</title>
    @vite(['resources/css/app.css', 'resources/css/admin.scss', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="admin-body" x-data="{ sidebar: false }">
@php
    $nav = fn (string $pattern) => request()->routeIs($pattern) ? 'is-active' : '';
@endphp

<div class="admin-shell">
    {{-- Бічне меню --}}
    <aside class="admin-sidebar" :class="{ 'is-open': sidebar }" x-on:click.away="sidebar = false">
        <a href="{{ route('admin.dashboard') }}" class="admin-sidebar__brand" wire:navigate>
            Велика Шина
        </a>

        <nav class="admin-nav">
            <a href="{{ route('admin.dashboard') }}" class="{{ $nav('admin.dashboard') }}" wire:navigate>Головна</a>

            <div class="admin-nav__group">
                <span class="admin-nav__label">Каталог</span>
                <a href="{{ route('admin.products.index') }}" class="{{ $nav('admin.products.*') }}" wire:navigate>Товари</a>
                <a href="{{ route('admin.categories.index') }}" class="{{ $nav('admin.categories.*') }}" wire:navigate>Категорії</a>
                <a href="{{ route('admin.attributes.index') }}" class="{{ $nav('admin.attributes.*') }}" wire:navigate>Характеристики</a>
                <a href="{{ route('admin.brands.index') }}" class="{{ $nav('admin.brands.*') }}" wire:navigate>Бренди</a>
                <a href="{{ route('admin.product-types.index') }}" class="{{ $nav('admin.product-types.*') }}" wire:navigate>Типи товарів</a>
            </div>

            <div class="admin-nav__group">
                <span class="admin-nav__label">Техніка</span>
                <a href="{{ route('admin.machinery-types.index') }}" class="{{ $nav('admin.machinery-types.*') }}" wire:navigate>Типи техніки</a>
                <a href="{{ route('admin.machinery-brands.index') }}" class="{{ $nav('admin.machinery-brands.*') }}" wire:navigate>Виробники</a>
                <a href="{{ route('admin.machinery-models.index') }}" class="{{ $nav('admin.machinery-models.*') }}" wire:navigate>Моделі</a>
                <a href="{{ route('admin.machinery-positions.index') }}" class="{{ $nav('admin.machinery-positions.*') }}" wire:navigate>Позиції</a>
            </div>

            <div class="admin-nav__group">
                <span class="admin-nav__label">Управління</span>
                <a href="{{ route('admin.leads.index') }}" class="{{ $nav('admin.leads.*') }}" wire:navigate>Заявки</a>
                <a href="{{ route('admin.import-export.index') }}" class="{{ $nav('admin.import-export.*') }}" wire:navigate>Імпорт / Експорт</a>
                <a href="{{ route('admin.users.index') }}" class="{{ $nav('admin.users.*') }}" wire:navigate>Користувачі</a>
                <a href="{{ route('admin.settings.index') }}" class="{{ $nav('admin.settings.*') }}" wire:navigate>Налаштування</a>
            </div>
        </nav>
    </aside>

    {{-- Затемнення під меню (мобільні) --}}
    <div class="admin-overlay" :class="{ 'is-open': sidebar }" x-on:click="sidebar = false"></div>

    {{-- Контент --}}
    <div class="admin-content">
        <header class="admin-header">
            <button class="admin-burger" x-on:click="sidebar = !sidebar" aria-label="Меню">☰</button>
            <span class="admin-header__title">Адмін-панель</span>
            <div class="admin-header__spacer"></div>
            <form class="admin-logout" method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Вийти</button>
            </form>
        </header>

        <main class="admin-main">
            {{ $slot }}
        </main>
    </div>
</div>

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
