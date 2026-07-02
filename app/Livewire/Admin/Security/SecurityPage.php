<?php

namespace App\Livewire\Admin\Security;

use App\Livewire\Concerns\WithAdminToast;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Livewire\Component;

/**
 * Сторінка «Безпека»: керування двофакторною автентифікацією (2FA)
 * для поточного адміна. Опційно (самообслуговування): кожен вмикає сам.
 * Вмикання/вимикання захищене повторним введенням пароля.
 */
class SecurityPage extends Component
{
    use WithAdminToast;

    public string $password = '';      // підтвердження пароля для критичних дій
    public string $code = '';          // 6-значний код із застосунку (для підтвердження)

    public bool $showingQr = false;            // показуємо QR під час налаштування
    public bool $showingRecoveryCodes = false; // показуємо коди відновлення

    /** Поточний користувач із актуальним станом 2FA. */
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

    /** Крок 1: підтвердити пароль і згенерувати секрет + QR. */
    public function enable(EnableTwoFactorAuthentication $enable): void
    {
        $this->assertPassword();

        $enable(Auth::user());

        $this->reset('password');
        $this->showingQr = true;
        $this->showingRecoveryCodes = false;
    }

    /** Крок 2: підтвердити код із застосунку — 2FA стає активною. */
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
        $this->showingRecoveryCodes = true;     // показуємо коди відновлення один раз
        session()->flash('success', 'Двофакторну автентифікацію увімкнено.');
    }

    /** Перегенерувати коди відновлення. */
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

    /** Вимкнути 2FA (з підтвердженням пароля). */
    public function disable(DisableTwoFactorAuthentication $disable): void
    {
        $this->assertPassword();

        $disable(Auth::user());

        $this->reset('password', 'code');
        $this->showingQr = false;
        $this->showingRecoveryCodes = false;
        session()->flash('success', 'Двофакторну автентифікацію вимкнено.');
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
        ])->layout('admin.layouts.admin');
    }
}
