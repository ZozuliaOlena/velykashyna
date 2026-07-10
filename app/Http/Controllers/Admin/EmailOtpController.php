<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

/**
 * Вхід за одноразовим кодом з пошти (додатковий крок після пароля).
 * Код зберігається у сесії (хеш + строк дії), лист надсилається на email
 * поточного користувача. Гейт вмикає middleware EnsureEmailOtpVerified.
 */
class EmailOtpController extends Controller
{
    private const TTL_SECONDS = 600; // 10 хвилин

    public function show(Request $request)
    {
        if (! Setting::get('email_login_code_enabled') || $request->session()->get('email_otp.verified')) {
            return redirect()->route('admin.dashboard');
        }

        if (! $this->hasValidPending($request)) {
            $this->sendCode($request);
        }

        return view('auth.email-otp-challenge', [
            'email' => $this->maskEmail((string) $request->user()->email),
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => ['required', 'string']]);

        $session = $request->session();
        $hash = $session->get('email_otp.hash');
        $expires = (int) $session->get('email_otp.expires', 0);

        if (! $hash || now()->timestamp > $expires) {
            throw ValidationException::withMessages([
                'code' => 'Код прострочено. Надішліть новий.',
            ]);
        }

        if (! Hash::check(trim($request->input('code')), $hash)) {
            throw ValidationException::withMessages([
                'code' => 'Невірний код. Перевірте лист і спробуйте ще раз.',
            ]);
        }

        $session->put('email_otp.verified', true);
        $session->forget(['email_otp.hash', 'email_otp.expires']);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function resend(Request $request)
    {
        $this->sendCode($request);

        return back()->with('status', 'Новий код надіслано на пошту.');
    }

    private function hasValidPending(Request $request): bool
    {
        return (bool) $request->session()->get('email_otp.hash')
            && now()->timestamp <= (int) $request->session()->get('email_otp.expires', 0);
    }

    private function sendCode(Request $request): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $request->session()->put('email_otp.hash', Hash::make($code));
        $request->session()->put('email_otp.expires', now()->addSeconds(self::TTL_SECONDS)->timestamp);

        $user = $request->user();
        Mail::raw(
            "Ваш код для входу в адмін-панель «ВЕЛИКА ШИНА»: {$code}\n\n"
            . "Код дійсний 10 хвилин. Якщо ви не входили - просто проігноруйте цей лист.",
            function ($message) use ($user) {
                $message->to($user->email)->subject('Код для входу - ВЕЛИКА ШИНА');
            }
        );
    }

    private function maskEmail(string $email): string
    {
        if (! str_contains($email, '@')) {
            return $email;
        }

        [$name, $domain] = explode('@', $email, 2);
        $visible = mb_substr($name, 0, 2);
        $masked = $visible . str_repeat('*', max(1, mb_strlen($name) - mb_strlen($visible)));

        return $masked . '@' . $domain;
    }
}
