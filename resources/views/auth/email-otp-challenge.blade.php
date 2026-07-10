<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Код для входу - ВЕЛИКА ШИНА</title>
    <link rel="icon" href="{{ \App\Models\Setting::get('favicon') ?: '/favicon.ico' }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ \App\Models\Setting::get('favicon') ?: '/images/apple-touch-icon.png' }}">
    <style>
        :root { --red: #d32f2f; --red-hover: #b71c1c; --text: #121212; --muted: #6b7280; --border: #e0e0e0; }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            padding: 24px; font-family: system-ui, "Segoe UI", Arial, sans-serif; color: var(--text);
            background:
                radial-gradient(1200px 600px at 100% -10%, rgba(211, 47, 47, 0.18), transparent 60%),
                radial-gradient(900px 500px at -10% 110%, rgba(211, 47, 47, 0.12), transparent 55%),
                #15171c;
        }
        .login-card {
            width: 100%; max-width: 410px; background: #fff; border-radius: 18px;
            padding: 36px 34px 30px; box-shadow: 0 30px 70px rgba(0, 0, 0, 0.45);
        }
        .login-brand { margin-bottom: 26px; }
        .login-brand img { display: block; height: 40px; width: auto; max-width: 100%; }
        .login-card h1 { font-size: 21px; font-weight: 800; margin: 0 0 4px; }
        .login-sub { margin: 0 0 22px; font-size: 14px; color: var(--muted); }
        .login-field { margin-bottom: 16px; }
        .login-field label { display: block; font-size: 13px; font-weight: 600; color: var(--muted); margin-bottom: 6px; }
        .login-field input[type="text"] {
            width: 100%; padding: 12px 13px; font-size: 22px; letter-spacing: 6px; text-align: center;
            font-family: inherit; color: var(--text); background: #f7f8fa; border: 1px solid var(--border);
            border-radius: 9px; transition: border-color .15s, box-shadow .15s, background .15s;
        }
        .login-field input:focus { outline: none; background: #fff; border-color: var(--red); box-shadow: 0 0 0 3px rgba(211, 47, 47, 0.14); }
        .login-btn {
            width: 100%; padding: 13px; font-size: 15px; font-weight: 700; font-family: inherit; color: #fff;
            background: var(--red); border: none; border-radius: 9px; cursor: pointer;
            box-shadow: 0 8px 20px rgba(211, 47, 47, 0.3); transition: background .15s, box-shadow .15s;
        }
        .login-btn:hover { background: var(--red-hover); box-shadow: 0 10px 24px rgba(211, 47, 47, 0.38); }
        .login-errors { background: #fdecea; border: 1px solid #f5c6c2; color: var(--red); border-radius: 9px; padding: 10px 14px; margin-bottom: 18px; font-size: 14px; }
        .login-errors ul { margin: 0; padding-left: 18px; }
        .login-status { background: #e8f5e9; border: 1px solid #c1e6c4; color: #1b7a2a; border-radius: 9px; padding: 10px 14px; margin-bottom: 18px; font-size: 14px; }
        .login-alt { margin: 16px 0 0; text-align: center; font-size: 14px; color: var(--muted); }
        .login-alt button { background: none; border: none; color: var(--red); font: inherit; cursor: pointer; padding: 0; text-decoration: underline; }
        .login-logout { margin: 8px 0 0; text-align: center; font-size: 13px; }
        .login-logout button { background: none; border: none; color: var(--muted); font: inherit; cursor: pointer; text-decoration: underline; padding: 0; }
    </style>
</head>
<body>
    <main class="login-card">
        <div class="login-brand">
            <img src="/images/logo.png" alt="ВЕЛИКА ШИНА">
        </div>

        <h1>Підтвердження входу</h1>
        <p class="login-sub">Ми надіслали код на <strong>{{ $email }}</strong>. Введіть його нижче.</p>

        @if (session('status'))
            <div class="login-status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="login-errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.verify-code.verify') }}">
            @csrf
            <div class="login-field">
                <label for="code">Код з листа</label>
                <input id="code" type="text" name="code" inputmode="numeric" autocomplete="one-time-code"
                       maxlength="6" placeholder="------" required autofocus>
            </div>
            <button type="submit" class="login-btn">Підтвердити</button>
        </form>

        <p class="login-alt">
            Не отримали код?
            <form method="POST" action="{{ route('admin.verify-code.resend') }}" style="display:inline">
                @csrf
                <button type="submit">Надіслати ще раз</button>
            </form>
        </p>

        <p class="login-logout">
            <form method="POST" action="{{ route('logout') }}" style="display:inline">
                @csrf
                <button type="submit">Вийти й увійти іншим акаунтом</button>
            </form>
        </p>
    </main>
</body>
</html>
