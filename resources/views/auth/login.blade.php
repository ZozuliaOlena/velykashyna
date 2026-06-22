<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вхід в Адмінку</title>
</head>
<body>
    <h2>Вхід у систему</h2>
    
    {{-- Вивід помилок (наприклад, неправильний пароль) --}}
    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div>
            <label>Email:</label><br>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus>
        </div>
        <br>
        <div>
            <label>Пароль:</label><br>
            <input type="password" name="password" required>
        </div>
        <br>
        <label>
            <input type="checkbox" name="remember"> Запам'ятати мене
        </label>
        <br><br>
        <button type="submit">Увійти</button>
    </form>
</body>
</html>
