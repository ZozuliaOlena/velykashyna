{{-- resources/views/layouts/app.blade.php --}}
<!doctype html>
<html lang="uk">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>@yield('title', 'Велика Шина — Великі шини для великих машин')</title>
    <meta name="description" content="@yield('meta_description', 'Велика Шина — каталог шин, камер та дисків для сільськогосподарської, спеціальної та вантажної техніки. Підбір за розміром, брендом і технікою.')" />

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

    <main>
        @yield('content')
    </main>

    @include('partials.footer')

    @stack('scripts')
</body>

</html>
