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
    <nav>
        <a href="{{ route('admin.dashboard') }}">Головна</a>
        <a href="{{ route('admin.products.index') }}">Товари</a>
        <a href="{{ route('admin.brands.index') }}">Бренди</a>

        {{-- Тимчасово ховаємо те, чого ще немає --}}
        {{-- <a href="{{ route('admin.categories.index') }}">Категорії</a> --}}
        {{-- <a href="{{ route('admin.attributes.index') }}">Характеристики</a> --}}

        <form method="POST" action="{{ route('logout') }}" style="display:inline">
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
