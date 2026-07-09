<?php

namespace App\Livewire\Admin\Security;

use App\Livewire\Concerns\WithAdminToast;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Livewire\Component;

class SecurityPage extends Component
{
    use WithAdminToast;

    public string $password = '';
    public string $code = '';

    public bool $showingQr = false;
    public bool $showingRecoveryCodes = false;

    private function user()
    {
        return Auth::user()->fresh();
    }

    private function assertPassword(): void
    {
        if (! Hash::check($this->password, Auth::user()->password)) {
            throw ValidationException::withMessages(['password' => 'Невірний пароль.']);
        }
    }

    public function enable(EnableTwoFactorAuthentication $enable): void
    {
        $this->assertPassword();

        $enable(Auth::user());

        $this->reset('password');
        $this->showingQr = true;
        $this->showingRecoveryCodes = false;
    }

    public function confirmTwoFactor(ConfirmTwoFactorAuthentication $confirm): void
    {
        $this->validate(['code' => ['required', 'string']]);

        try {
            $confirm(Auth::user(), $this->code);
        } catch (ValidationException $e) {
            throw ValidationException::withMessages(['code' => 'Невірний код. Спробуйте ще раз.']);
        }

        $this->reset('code');
        $this->showingQr = false;
        $this->showingRecoveryCodes = true;
        session()->flash('success', 'Двофакторну автентифікацію увімкнено.');
    }

    public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generate): void
    {
        $generate(Auth::user());
        $this->showingRecoveryCodes = true;
        session()->flash('success', 'Коди відновлення оновлено.');
    }

    public function showRecoveryCodes(): void
    {
        $this->showingRecoveryCodes = true;
    }

    public function disable(DisableTwoFactorAuthentication $disable): void
    {
        $this->assertPassword();

        $disable(Auth::user());

        $this->reset('password', 'code');
        $this->showingQr = false;
        $this->showingRecoveryCodes = false;
        session()->flash('success', 'Двофакторну автентифікацію вимкнено.');
    }

    /** Увімкнути/вимкнути вхід за одноразовим кодом з пошти (для всіх адмінів). */
    public function toggleEmailCode(): void
    {
        $enabled = ! (bool) Setting::get('email_login_code_enabled');
        Setting::set('email_login_code_enabled', $enabled ? '1' : '');

        // Щоб адмін, який щойно увімкнув, не був одразу «вибитий» на ввід коду.
        session()->put('email_otp.verified', true);

        session()->flash('success', $enabled
            ? 'Вхід за кодом з пошти увімкнено.'
            : 'Вхід за кодом з пошти вимкнено.');
    }

    public function render()
    {
        $user = $this->user();

        $enabled = ! is_null($user->two_factor_secret);
        $confirmed = ! is_null($user->two_factor_confirmed_at);

        return view('admin.security.security-page', [
            'enabled'   => $enabled,
            'confirmed' => $confirmed,
            'qrSvg'     => ($enabled && $this->showingQr) ? $user->twoFactorQrCodeSvg() : null,
            'setupKey'  => ($enabled && $this->showingQr) ? decrypt($user->two_factor_secret) : null,
            'recovery'  => ($enabled && $this->showingRecoveryCodes) ? $user->recoveryCodes() : [],
            'emailCodeEnabled' => (bool) Setting::get('email_login_code_enabled'),
        ])->layout('admin.layouts.admin');
    }
}
