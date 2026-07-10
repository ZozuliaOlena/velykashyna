<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вхід в адмін-панель - ВЕЛИКА ШИНА</title>
    <link rel="icon" href="{{ \App\Models\Setting::get('favicon') ?: '/favicon.ico' }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ \App\Models\Setting::get('favicon') ?: '/images/apple-touch-icon.png' }}">
    <style>
        :root {
            --red: #d32f2f;
            --red-hover: #b71c1c;
            --text: #121212;
            --muted: #6b7280;
            --border: #e0e0e0;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: system-ui, "Segoe UI", Arial, sans-serif;
            color: var(--text);
            background:
                radial-gradient(1200px 600px at 100% -10%, rgba(211, 47, 47, 0.18), transparent 60%),
                radial-gradient(900px 500px at -10% 110%, rgba(211, 47, 47, 0.12), transparent 55%),
                #15171c;
        }

        .login-card {
            width: 100%;
            max-width: 410px;
            background: #fff;
            border-radius: 18px;
            padding: 36px 34px 30px;
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.45);
        }

        .login-brand {
            margin-bottom: 26px;
        }
        .login-brand img {
            display: block;
            height: 40px;
            width: auto;
            max-width: 100%;
        }

        .login-card h1 {
            font-size: 21px;
            font-weight: 800;
            margin: 0 0 4px;
        }
        .login-sub {
            margin: 0 0 22px;
            font-size: 14px;
            color: var(--muted);
        }

        .login-field { margin-bottom: 16px; }
        .login-field label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--muted);
            margin-bottom: 6px;
        }
        .login-field input[type="email"],
        .login-field input[type="password"],
        .login-field input[type="text"] {
            width: 100%;
            padding: 12px 13px;
            font-size: 15px;
            font-family: inherit;
            color: var(--text);
            background: #f7f8fa;
            border: 1px solid var(--border);
            border-radius: 9px;
            transition: border-color .15s, box-shadow .15s, background .15s;
        }
        .login-field input:focus {
            outline: none;
            background: #fff;
            border-color: var(--red);
            box-shadow: 0 0 0 3px rgba(211, 47, 47, 0.14);
        }

        .login-input-wrap { position: relative; }
        .login-input-wrap input { padding-right: 44px; }
        .login-eye {
            position: absolute;
            top: 50%;
            right: 6px;
            transform: translateY(-50%);
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            border: none;
            background: none;
            color: var(--muted);
            cursor: pointer;
            border-radius: 7px;
        }
        .login-eye:hover { color: var(--text); background: #f0f1f4; }
        .login-eye svg { width: 19px; height: 19px; }
        .login-eye .icon-off { display: none; }
        .login-eye.is-on .icon-on { display: none; }
        .login-eye.is-on .icon-off { display: block; }

        .login-remember {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: var(--muted);
            margin: 4px 0 22px;
            cursor: pointer;
        }
        .login-remember input { width: 16px; height: 16px; accent-color: var(--red); }

        .login-btn {
            width: 100%;
            padding: 13px;
            font-size: 15px;
            font-weight: 700;
            font-family: inherit;
            color: #fff;
            background: var(--red);
            border: none;
            border-radius: 9px;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(211, 47, 47, 0.3);
            transition: background .15s, box-shadow .15s;
        }
        .login-btn:hover { background: var(--red-hover); box-shadow: 0 10px 24px rgba(211, 47, 47, 0.38); }

        .login-errors {
            background: #fdecea;
            border: 1px solid #f5c6c2;
            color: var(--red);
            border-radius: 9px;
            padding: 10px 14px;
            margin-bottom: 18px;
            font-size: 14px;
        }
        .login-errors ul { margin: 0; padding-left: 18px; }
    </style>
</head>
<body>
    <main class="login-card">
        <div class="login-brand">
            <img src="/images/logo.png" alt="ВЕЛИКА ШИНА">
        </div>

        <h1>Вхід в адмін-панель</h1>
        <p class="login-sub">Введіть свої облікові дані для входу.</p>

        @if ($errors->any())
            <div class="login-errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="login-field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                       placeholder="admin@velykashyna.com.ua" required autofocus>
            </div>

            <div class="login-field">
                <label for="password">Пароль</label>
                <div class="login-input-wrap">
                    <input id="password" type="password" name="password" placeholder="••••••••" required>
                    <button type="button" class="login-eye" id="togglePassword"
                            aria-label="Показати пароль" title="Показати пароль">
                        <svg class="icon-on" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg class="icon-off" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </button>
                </div>
            </div>

            <label class="login-remember">
                <input type="checkbox" name="remember"> Запам'ятати мене
            </label>

            <button type="submit" class="login-btn">Увійти</button>
        </form>
    </main>

    <script>
        (function () {
            var btn = document.getElementById('togglePassword');
            var input = document.getElementById('password');
            btn.addEventListener('click', function () {
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.classList.toggle('is-on', show);
                btn.setAttribute('aria-label', show ? 'Сховати пароль' : 'Показати пароль');
                btn.setAttribute('title', show ? 'Сховати пароль' : 'Показати пароль');
                input.focus();
            });
        })();
    </script>
</body>
</html>
