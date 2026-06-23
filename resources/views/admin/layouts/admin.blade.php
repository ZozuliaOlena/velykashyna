<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Адмін панель — Велика Шина</title>
    @vite(['resources/css/app.css', 'resources/css/admin.scss', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="admin-body">
    <nav class="admin-topbar">
        <a class="admin-topbar__brand" href="{{ route('admin.dashboard') }}">Велика Шина</a>

        <div class="admin-topbar__group">
            <span class="admin-topbar__label">Каталог</span>
            <a href="{{ route('admin.products.index') }}">Товари</a>
            <a href="{{ route('admin.categories.index') }}">Категорії</a>
            <a href="{{ route('admin.attributes.index') }}">Характеристики</a>
            <a href="{{ route('admin.brands.index') }}">Бренди</a>
            <a href="{{ route('admin.product-types.index') }}">Типи товарів</a>
        </div>

        <div class="admin-topbar__group">
            <span class="admin-topbar__label">Техніка</span>
            <a href="{{ route('admin.machinery-types.index') }}">Типи</a>
            <a href="{{ route('admin.machinery-brands.index') }}">Виробники</a>
            <a href="{{ route('admin.machinery-models.index') }}">Моделі</a>
            <a href="{{ route('admin.machinery-positions.index') }}">Позиції</a>
        </div>

        <div class="admin-topbar__group">
            <a href="{{ route('admin.leads.index') }}">Заявки</a>
            <a href="{{ route('admin.import-export.index') }}">Імпорт/Експорт</a>
            <a href="{{ route('admin.users.index') }}">Користувачі</a>
            <a href="{{ route('admin.settings.index') }}">Налаштування</a>
        </div>

        <form class="admin-topbar__logout" method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Вийти</button>
        </form>
    </nav>

    <main class="admin-main">
        {{ $slot }}
    </main>

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
