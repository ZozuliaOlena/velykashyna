{{--
    Спільний каркас сторінок помилок. Навмисно САМОДОСТАТНІЙ (стилі inline,
    без @vite і без лейаутів): сторінка помилки має відкритися навіть тоді,
    коли зламано збірку ассетів або лейаут.
--}}
@php
    $isAdmin = request()->is('admin') || request()->is('admin/*');
    $backUrl = $isAdmin ? url('/admin') : url('/');
    $backLabel = $isAdmin ? 'До адмін-панелі' : 'На головну';
@endphp
<!doctype html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') - ВЕЛИКА ШИНА</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: #f5f6f8;
            color: #222;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            line-height: 1.6;
        }
        .box {
            width: 100%;
            max-width: 520px;
            background: #fff;
            border: 1px solid #e3e5e8;
            border-top: 4px solid #d32f2f;
            border-radius: 12px;
            padding: 32px 28px;
            text-align: center;
            box-shadow: 0 6px 24px rgba(0, 0, 0, .06);
        }
        .code { font-size: 13px; font-weight: 700; letter-spacing: .12em; color: #d32f2f; }
        h1 { margin: 10px 0 8px; font-size: 22px; line-height: 1.3; }
        p { margin: 0 0 22px; color: #5a6068; }
        .btn {
            display: inline-block;
            padding: 11px 22px;
            background: #d32f2f;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }
        .btn:hover { background: #b52626; }
        .btn--ghost { background: #fff; color: #444; border: 1px solid #d5d8dc; margin-left: 8px; }
        .btn--ghost:hover { background: #f3f4f6; }
    </style>
</head>
<body>
    <div class="box">
        <div class="code">@yield('code')</div>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>
        <a class="btn" href="{{ $backUrl }}">{{ $backLabel }}</a>
        @hasSection('reload')
            <a class="btn btn--ghost" href="{{ request()->fullUrl() }}">Спробувати ще раз</a>
        @endif
    </div>
</body>
</html>
