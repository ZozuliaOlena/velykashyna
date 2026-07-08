<div>
    <h1>Безпека</h1>


    <fieldset style="margin-top:1rem; max-width:560px">
        <legend><strong>Двофакторна автентифікація (2FA)</strong></legend>
        <p style="color:#666">
            Додатковий захист входу: окрім пароля знадобиться одноразовий код
            із застосунку (Google Authenticator, Authy тощо).
        </p>

        @if(! $confirmed && ! $showingQr)
            <p>Статус: <strong style="color:#b71c1c">вимкнено</strong></p>
            <div class="is-full" style="max-width:320px">
                <label>Підтвердіть пароль, щоб увімкнути</label>
                <input wire:model="password" type="password" style="width:100%" autocomplete="current-password">
                @error('password') <span style="color:red">{{ $message }}</span> @enderror
            </div>
            <div style="margin-top:.75rem">
                <button wire:click="enable" class="btn-primary">Увімкнути 2FA</button>
            </div>

        @elseif($showingQr)
            <p>1. Відскануйте QR-код у застосунку автентифікації:</p>
            <div style="background:#fff; display:inline-block; padding:10px; border:1px solid #e0e0e0; border-radius:10px">
                {!! $qrSvg !!}
            </div>
            <p style="margin-top:.5rem; color:#666">
                Або введіть ключ вручну: <code>{{ $setupKey }}</code>
            </p>

            <div class="is-full" style="max-width:240px; margin-top:.5rem">
                <label>2. Введіть код із застосунку</label>
                <input wire:model="code" type="text" inputmode="numeric" autocomplete="one-time-code"
                    placeholder="000000" style="width:100%">
                @error('code') <span style="color:red">{{ $message }}</span> @enderror
            </div>
            <div style="margin-top:.75rem">
                <button wire:click="confirmTwoFactor" class="btn-primary">Підтвердити та увімкнути</button>
            </div>

        @else
            <p>Статус: <strong style="color:#2e7d32">увімкнено</strong></p>

            @if($recovery)
                <div style="margin-top:.5rem">
                    <strong>Коди відновлення</strong>
                    <p style="color:#666; font-size:13px; margin:.25rem 0">
                        Збережіть ці коди в безпечному місці. Кожен діє один раз — допоможе
                        увійти, якщо втратите доступ до застосунку.
                    </p>
                    <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:6px 16px;
                                background:#f8f9fa; padding:12px; border-radius:8px; max-width:360px; font-family:monospace">
                        @foreach($recovery as $rc)
                            <span>{{ $rc }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            <div style="margin-top:1rem; display:flex; gap:10px; flex-wrap:wrap">
                @unless($recovery)
                    <button wire:click="showRecoveryCodes">Показати коди відновлення</button>
                @endunless
                <button wire:click="regenerateRecoveryCodes">Оновити коди відновлення</button>
            </div>

            <hr style="margin:1.25rem 0; border:none; border-top:1px solid #eee">

            <div class="is-full" style="max-width:320px">
                <label>Підтвердіть пароль, щоб вимкнути</label>
                <input wire:model="password" type="password" style="width:100%" autocomplete="current-password">
                @error('password') <span style="color:red">{{ $message }}</span> @enderror
            </div>
            <div style="margin-top:.75rem">
                <button wire:click="disable"
                    data-confirm="Ви дійсно хочете вимкнути двофакторну автентифікацію?">Вимкнути 2FA</button>
            </div>
        @endif
    </fieldset>
</div>
