<div>
    <div style="display:flex; justify-content:space-between; align-items:center">
        <h1>Налаштування</h1>
        <button wire:click="openCreate">+ Додати параметр</button>
    </div>

    <fieldset style="margin:0 0 1.5rem; padding:16px; border:1px solid #e3e6ec; border-radius:10px">
        <legend style="padding:0 8px"><strong>Google Аналітика та реклама</strong></legend>
        <p style="color:#666; margin:0 0 1rem">
            Вставте <strong>ідентифікатор</strong> - код підключиться автоматично. Заповнюйте лише те, що потрібно.
        </p>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:12px">
            <div>
                <label>Google Analytics (GA4)</label>
                <input wire:model="gaId" type="text" style="width:100%" placeholder="G-XXXXXXXXXX">
                <small style="color:#888">Ідентифікатор потоку даних GA4.</small>
            </div>
            <div>
                <label>Google Tag Manager</label>
                <input wire:model="gtmId" type="text" style="width:100%" placeholder="GTM-XXXXXXX">
                <small style="color:#888">Якщо користуєтесь GTM-контейнером.</small>
            </div>
            <div>
                <label>Google Ads</label>
                <input wire:model="adsId" type="text" style="width:100%" placeholder="AW-XXXXXXXXX">
                <small style="color:#888">ID для реклами / конверсій.</small>
            </div>
        </div>

        <div style="margin-top:1rem">
            <label>Власний код у &lt;head&gt; (необов'язково)</label>
            <textarea wire:model="trackingHead" rows="4" style="width:100%; font-family:monospace; font-size:13px"
                placeholder="Сюди можна вставити цілий скрипт (Google Ads, піксель тощо), якщо не хочете вводити лише ID."></textarea>
            <small style="color:#888">Код вставляється на сайт «як є». Додавайте лише перевірені скрипти.</small>
        </div>

        <div style="margin-top:1rem">
            <button wire:click="saveAnalytics" data-confirm="Зберегти налаштування аналітики?">Зберегти аналітику</button>
        </div>
    </fieldset>

    <fieldset style="margin:0 0 1.5rem; padding:16px; border:1px solid #e3e6ec; border-radius:10px">
        <legend style="padding:0 8px"><strong>Пошта (SMTP)</strong></legend>
        <p style="color:#666; margin:0 0 1rem">
            Дані вашої поштової скриньки, з якої сайт надсилатиме листи (код для входу, тестові листи).
            Візьміть їх із листа хостингу: <em>SMTP server, Login, Password</em>.
        </p>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:12px">
            <div>
                <label>SMTP-сервер (host)</label>
                <input wire:model="mailHost" type="text" style="width:100%" placeholder="s47.hostia.name">
                @error('mailHost') <span style="color:red">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Порт</label>
                <input wire:model="mailPort" type="text" style="width:100%" placeholder="465">
                <small style="color:#888">Зазвичай 465 (SSL) або 587 (TLS).</small>
                @error('mailPort') <span style="color:red">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Шифрування</label>
                <select wire:model="mailEncryption" style="width:100%">
                    <option value="ssl">SSL (порт 465)</option>
                    <option value="tls">TLS (порт 587)</option>
                    <option value="none">Без шифрування</option>
                </select>
                @error('mailEncryption') <span style="color:red">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Логін (email)</label>
                <input wire:model="mailUsername" type="text" style="width:100%" placeholder="info@lavika.in.ua">
                @error('mailUsername') <span style="color:red">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Пароль</label>
                <input wire:model="mailPassword" type="password" style="width:100%"
                    placeholder="{{ $mailPasswordSet ? '•••••••• (збережено - залиште порожнім, щоб не міняти)' : 'пароль скриньки' }}"
                    autocomplete="new-password">
                @error('mailPassword') <span style="color:red">{{ $message }}</span> @enderror
            </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:12px; margin-top:12px">
            <div>
                <label>Ім'я відправника (від кого)</label>
                <input wire:model="mailFromName" type="text" style="width:100%" placeholder="ВЕЛИКА ШИНА">
                @error('mailFromName') <span style="color:red">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Email відправника</label>
                <input wire:model="mailFromAddress" type="text" style="width:100%" placeholder="info@lavika.in.ua">
                <small style="color:#888">Зазвичай = логін скриньки.</small>
                @error('mailFromAddress') <span style="color:red">{{ $message }}</span> @enderror
            </div>
        </div>

        <div style="margin-top:1rem; display:flex; gap:10px; flex-wrap:wrap; align-items:center">
            <button wire:click="saveMail" data-confirm="Зберегти налаштування пошти?">Зберегти пошту</button>
            <button wire:click="sendMailTest" wire:loading.attr="disabled" wire:target="sendMailTest">Надіслати тестовий лист</button>
            <span class="spinner-line" wire:loading wire:target="sendMailTest"><span class="spinner"></span> Надсилаємо…</span>
        </div>
    </fieldset>

    <fieldset style="margin:0 0 1.5rem; padding:16px; border:1px solid #e3e6ec; border-radius:10px">
        <legend style="padding:0 8px"><strong>Прев'ю при поширенні (соцмережі, месенджери) та фавіконка</strong></legend>
        <p style="color:#666; margin:0 0 1rem">
            Те, що бачать люди, коли діляться посиланням на сайт: заголовок, опис і картинка.
            На сторінках товарів автоматично підставляється фото товару, тут - значення за замовчуванням для решти сторінок.
        </p>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:12px">
            <div>
                <label>Заголовок за замовчуванням</label>
                <input wire:model="shareTitle" type="text" style="width:100%"
                    placeholder="ВЕЛИКА ШИНА - шини для агро, спец та вантажної техніки">
                @error('shareTitle') <span style="color:red">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Опис за замовчуванням</label>
                <textarea wire:model="shareDescription" rows="3" style="width:100%"
                    placeholder="Короткий опис сайту (до 500 символів)"></textarea>
                @error('shareDescription') <span style="color:red">{{ $message }}</span> @enderror
            </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:16px; margin-top:1rem">
            <div>
                <label>Картинка прев'ю (рекомендовано 1200×630)</label><br>
                <div class="photo-thumb" style="width:200px; height:105px">
                    <img src="{{ $shareImageUpload ? $shareImageUpload->temporaryUrl() : ($shareImage ?: '/images/og-default.png') }}"
                        alt="" data-zoom-src="{{ $shareImageUpload ? '' : ($shareImage ?: '/images/og-default.png') }}">
                </div>
                <div style="margin-top:.4rem; display:flex; gap:8px; flex-wrap:wrap; align-items:center">
                    <label class="upload-btn">
                        <input type="file" wire:model="shareImageUpload" accept="image/*" hidden>
                        <span>Вибрати картинку</span>
                    </label>
                    @if($shareImage)
                        <button type="button" wire:click="deleteShareImage"
                            data-confirm="Скинути картинку прев'ю на стандартну?">Скинути</button>
                    @endif
                </div>
                <div wire:loading wire:target="shareImageUpload" style="color:#666; font-size:13px; margin-top:4px">Завантаження…</div>
                @error('shareImageUpload') <span style="color:red">{{ $message }}</span> @enderror
            </div>

            <div>
                <label>Фавіконка (PNG / SVG / ICO)</label><br>
                <div class="photo-thumb" style="width:64px; height:64px">
                    <img src="{{ $faviconUpload ? $faviconUpload->temporaryUrl() : ($favicon ?: '/favicon.ico') }}" alt="">
                </div>
                <div style="margin-top:.4rem; display:flex; gap:8px; flex-wrap:wrap; align-items:center">
                    <label class="upload-btn">
                        <input type="file" wire:model="faviconUpload" accept=".png,.svg,.ico,image/png,image/svg+xml" hidden>
                        <span>Вибрати фавіконку</span>
                    </label>
                    @if($favicon)
                        <button type="button" wire:click="deleteFavicon"
                            data-confirm="Скинути фавіконку на стандартну?">Скинути</button>
                    @endif
                </div>
                <div wire:loading wire:target="faviconUpload" style="color:#666; font-size:13px; margin-top:4px">Завантаження…</div>
                @error('faviconUpload') <span style="color:red">{{ $message }}</span> @enderror
            </div>
        </div>

        <hr style="margin:1.25rem 0; border:none; border-top:1px solid #eee">

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:12px">
            <div>
                <label>Код підтвердження Google Search Console</label>
                <input wire:model="googleSiteVerification" type="text" style="width:100%"
                    placeholder="напр. AbCdEf123... (лише значення content)">
                <small style="color:#888">З Google Search Console → «HTML-тег»: скопіюйте значення <code>content</code>.</small>
                @error('googleSiteVerification') <span style="color:red">{{ $message }}</span> @enderror
            </div>
            <div>
                <label style="display:flex; align-items:center; gap:8px; margin-top:1.6rem">
                    <input type="checkbox" wire:model="siteNoindex">
                    Сховати сайт від пошуковиків (noindex)
                </label>
                <small style="color:#888">Вмикайте лише на час розробки. На робочому сайті має бути <strong>вимкнено</strong>.</small>
            </div>
        </div>

        <div style="margin-top:1rem">
            <button wire:click="saveSharing" data-confirm="Зберегти налаштування прев'ю та SEO?">Зберегти прев'ю та SEO</button>
        </div>
    </fieldset>

    <fieldset style="margin:0 0 1.5rem; padding:16px; border:1px solid #e3e6ec; border-radius:10px">
        <legend style="padding:0 8px"><strong>Telegram-сповіщення про заявки</strong></legend>
        <p style="color:#666; margin:0 0 1rem">
            Бот надсилатиме повідомлення про кожну нову заявку з сайту - і з кошика, і консультації.
        </p>

        <div style="margin-bottom:14px">
            <label>Токен бота</label>
            <input wire:model="tgBotToken" type="text" style="width:100%" placeholder="1234567890:AA...">
            @error('tgBotToken') <span style="color:red">{{ $message }}</span> @enderror
        </div>

        <div>
            <label>ID отримувачів</label>
            <textarea wire:model="tgChatIds" rows="2" style="width:100%"
                placeholder="Напр.: 123456789, 987654321"></textarea>
            @error('tgChatIds') <span style="color:red">{{ $message }}</span> @enderror
            <small style="color:#888; display:block">Кілька ID - через кому. Кожному приходитиме сповіщення.</small>
        </div>

        <div style="margin-top:1rem; display:flex; gap:10px; flex-wrap:wrap">
            <button wire:click="saveTelegram" data-confirm="Зберегти налаштування Telegram?">Зберегти</button>
            <button wire:click="sendTelegramTest">Надіслати тест</button>
        </div>

        <div style="margin-top:1rem; padding:12px 14px; background:#f4f8ff; border:1px solid #cfe0ff; border-radius:8px; font-size:13px; color:#33455f">
            <strong>Як налаштувати:</strong>
            <ol style="margin:.4rem 0 0; padding-left:1.2rem">
                <li>У Telegram напишіть <b>@BotFather</b> → <code>/newbot</code> → отримайте <b>токен</b> і вставте вище.</li>
                <li>Кожен отримувач має знайти вашого бота й натиснути <b>«Запустити» (/start)</b> - інакше бот не зможе йому написати.</li>
                <li>Щоб дізнатись свій <b>ID</b>: напишіть боту <b>@userinfobot</b> - він відповість числом. Це і є ID.</li>
            </ol>
        </div>
    </fieldset>

    <h2 style="font-size:18px; margin:0 0 .5rem">Інші параметри</h2>
    <div class="admin-filters">
        <input wire:model.live.debounce.300ms="search" placeholder="Пошук по ключу...">
    </div>

    <div class="table-scroll">
    <table border="1" cellpadding="6" style="width:100%; border-collapse:collapse">
        <thead>
            <tr><th>Ключ</th><th>Значення</th><th>Дії</th></tr>
        </thead>
        <tbody>
            @forelse($settings as $setting)
            <tr wire:key="setting-{{ $setting->key }}">
                <td data-label="Ключ">{{ $setting->key }}</td>
                <td data-label="Значення" style="word-break:break-all">{{ \Illuminate\Support\Str::limit($setting->value, 80) }}</td>
                <td class="cell-actions">
                    <button class="icon-btn" wire:click="openEdit('{{ $setting->key }}')" title="Редагувати" aria-label="Редагувати"><x-icon name="edit"/></button>
                    <button class="icon-btn" wire:click="delete('{{ $setting->key }}')" data-confirm="Ви дійсно хочете видалити параметр?" title="Видалити" aria-label="Видалити"><x-icon name="trash"/></button>
                </td>
            </tr>
            @empty
            <tr><td colspan="3" style="text-align:center">Параметрів немає</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <div style="margin-top:1rem">{{ $settings->links('pagination.admin') }}</div>

    @if($showModal)
    <x-admin.modal :title="$editingKey ? 'Редагувати параметр' : 'Новий параметр'">
        <div class="is-full">
            <label>Ключ *</label>
            <input wire:model="key" type="text" style="width:100%"
                placeholder="gtm_container_id, merchant_feed_url..."
                @if($editingKey) readonly @endif>
            @error('key') <span style="color:red">{{ $message }}</span> @enderror
        </div>
        <div class="is-full">
            <label>Значення</label>
            <textarea wire:model="value" rows="3" style="width:100%"></textarea>
            @error('value') <span style="color:red">{{ $message }}</span> @enderror
        </div>

        <x-slot:footer>
            <button wire:click="save" data-confirm="Ви дійсно хочете зберегти зміни?">Зберегти</button>
            <button wire:click="$set('showModal', false)">Скасувати</button>
        </x-slot:footer>
    </x-admin.modal>
    @endif
</div>
