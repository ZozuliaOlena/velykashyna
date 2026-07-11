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
                        Збережіть ці коди в безпечному місці. Кожен діє один раз - допоможе
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

    <fieldset style="margin-top:1rem">
        <legend><strong>Вхід за кодом з пошти</strong></legend>
        <p style="color:#666">
            Додатковий крок під час входу: після пароля на пошту адміністратора
            надсилається одноразовий код, який потрібно ввести. Діє для всіх адмінів.
        </p>

        <p style="margin:.25rem 0 .75rem">
            Статус:
            <strong style="color:{{ $emailCodeEnabled ? '#1b7a2a' : '#b71c1c' }}">
                {{ $emailCodeEnabled ? 'Увімкнено' : 'Вимкнено' }}
            </strong>
        </p>

        @if($emailCodeEnabled)
            <button wire:click="toggleEmailCode"
                data-confirm="Вимкнути вхід за кодом з пошти?">Вимкнути</button>
        @else
            <button wire:click="toggleEmailCode" class="btn-primary"
                data-confirm="Увімкнути вхід за кодом з пошти для всіх адмінів?">Увімкнути</button>
        @endif

        <p style="color:#888; font-size:13px; margin-top:.75rem">
            Потрібне налаштоване надсилання пошти (SMTP). Переконайтеся, що email
            адміністратора вказано правильно.
        </p>
    </fieldset>

    <fieldset style="margin-top:1rem; max-width:560px">
        <legend><strong>Технічна підтримка</strong></legend>

        <a href="https://ksibe.dev" target="_blank" rel="noopener" aria-label="Powered by ksibe.dev"
            style="display:inline-flex; align-items:center; gap:8px; background:#15171c; padding:9px 16px;
                   border-radius:10px; text-decoration:none; margin:.25rem 0 1.1rem">
            <span style="color:#fff; opacity:.8; font-size:13px; letter-spacing:.3px">Powered by</span>
            <img src="https://ksibe.dev/img/logo_for_site.svg" alt="ksibe.dev"
                style="height:16px; width:auto; display:block; filter:brightness(0) invert(1)">
        </a>

        <p style="color:#666; margin:0 0 1.1rem">
            З будь-яких питань щодо роботи сайту та адмінпанелі, доробок або технічної
            підтримки - звертайтесь до розробника.
        </p>

        <a href="https://ksibe.dev" target="_blank" rel="noopener"
            style="display:inline-flex; align-items:center; gap:8px; padding:10px 18px; background:#d32f2f;
                   color:#fff; border-radius:8px; font-weight:600; font-size:14px; text-decoration:none;
                   box-shadow:0 2px 8px rgba(211,47,47,.28)">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" style="display:block">
                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                <polyline points="15 3 21 3 21 9" />
                <line x1="10" y1="14" x2="21" y2="3" />
            </svg>
            Звернутися до розробника
        </a>
    </fieldset>
</div>
