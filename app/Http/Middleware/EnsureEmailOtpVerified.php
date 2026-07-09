<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Гейт входу за кодом з пошти. Якщо в адмінці увімкнено «Вхід за кодом з пошти»
 * і поточна сесія ще не підтверджена кодом — перекидає на сторінку вводу коду.
 * Вимикається перемикачем у розділі «Безпека» (Setting: email_login_code_enabled).
 */
class EnsureEmailOtpVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Setting::get('email_login_code_enabled')) {
            return $next($request);
        }

        if ($request->session()->get('email_otp.verified')) {
            return $next($request);
        }

        return redirect()->route('admin.verify-code');
    }
}
