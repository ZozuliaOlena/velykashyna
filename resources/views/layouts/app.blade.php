{{-- resources/views/layouts/app.blade.php --}}
<!doctype html>
<html lang="uk">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>@yield('title', 'Велика Шина — шини для агро, спец та вантажної техніки')</title>
    <meta name="description"
        content="@yield('meta_description', 'Велика Шина — каталог шин, камер та дисків для сільськогосподарської, спеціальної та вантажної техніки. Підбір за розміром, брендом і технікою з 2009 року.')" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;600&family=Montserrat:wght@400;500;600;700;800&display=swap"
        rel="stylesheet" />

    @vite(['resources/css/app.scss', 'resources/js/app.js'])

    @stack('head')
</head>

<body>
    @include('partials.header')
    @include('partials.mobile-menu')

    <main>
        @yield('content')
    </main>

    @include('partials.footer')

    {{-- Плаваюча кнопка зв'язку (на моб. з'являється після ~10% прокрутки) --}}
    <a href="tel:{{ config('site.contacts.phone_href') }}" class="fab" aria-label="Зв'язатися з нами"
        x-data="{ show: false }" :class="{ 'is-visible': show }"
        @scroll.window.throttle.100ms="show = window.scrollY > (document.documentElement.scrollHeight - window.innerHeight) * 0.1">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path
                d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
        </svg>
        <span class="fab-label">Зв'язатися</span>
    </a>

    @stack('scripts')
</body>

</html>
