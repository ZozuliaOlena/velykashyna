<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Адмін панель — Велика Шина</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>
    <nav style="display:flex; gap:1rem; flex-wrap:wrap; align-items:center; padding:.75rem; border-bottom:1px solid #ccc">
        <a href="{{ route('admin.dashboard') }}">Головна</a>

        <span style="color:#999">|</span>
        <strong>Каталог:</strong>
        <a href="{{ route('admin.products.index') }}">Товари</a>
        <a href="{{ route('admin.categories.index') }}">Категорії</a>
        <a href="{{ route('admin.attributes.index') }}">Характеристики</a>
        <a href="{{ route('admin.brands.index') }}">Бренди</a>
        <a href="{{ route('admin.product-types.index') }}">Типи товарів</a>

        <span style="color:#999">|</span>
        <strong>Техніка:</strong>
        <a href="{{ route('admin.machinery-types.index') }}">Типи</a>
        <a href="{{ route('admin.machinery-brands.index') }}">Виробники</a>
        <a href="{{ route('admin.machinery-models.index') }}">Моделі</a>
        <a href="{{ route('admin.machinery-positions.index') }}">Позиції</a>

        <span style="color:#999">|</span>
        <a href="{{ route('admin.leads.index') }}">Заявки</a>
        <a href="{{ route('admin.users.index') }}">Користувачі</a>
        <a href="{{ route('admin.settings.index') }}">Налаштування</a>

        <form method="POST" action="{{ route('logout') }}" style="display:inline; margin-left:auto">
            @csrf
            <button type="submit">Вийти</button>
        </form>
    </nav>

    <main>
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
