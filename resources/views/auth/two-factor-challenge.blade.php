<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Двофакторна авторизація</title>
    <link rel="icon" href="{{ \App\Models\Setting::get('favicon') ?: '/favicon.ico' }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ \App\Models\Setting::get('favicon') ?: '/images/apple-touch-icon.png' }}">
</head>
<body>
    <h2>Підтвердження входу</h2>
    
    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('two-factor.login') }}">
        @csrf
        <div>
            <label>Код з додатка (Google Authenticator):</label><br>
            <input type="text" name="code" placeholder="123456" autofocus>
        </div>
        <br>
        <button type="submit">Підтвердити</button>
    </form>
</body>
</html>
