{{-- resources/views/layouts/app.blade.php --}}
<!doctype html>
<html lang="uk">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Велика Шина - Великі шини для великих машин')</title>
    <link href="https://fonts.googleapis.com/..." rel="stylesheet" />
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>

<body>
    <div class="header-wrapper">
        {{-- весь ваш header + nav звідси --}}
    </div>

    @yield('content')

    {{-- footer тут --}}
</body>

</html>