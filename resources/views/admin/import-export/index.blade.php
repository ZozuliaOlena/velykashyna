<div>
    <h1>Імпорт / Експорт</h1>

    <fieldset style="margin-top:1rem">
        <legend><strong>Google Merchant (фід)</strong></legend>
        <p style="color:#666">
            XML-фід генерується автоматично й завжди актуальний. Додайте це посилання
            як джерело даних у Google Merchant Center (Products → Feeds) і налаштуйте
            періодичне отримання - товари оновлюватимуться самі.
        </p>

        <div class="is-full" style="margin-bottom:.75rem" x-data="{ copied: false }">
            <label>Посилання на фід</label>
            <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center">
                <input type="text" readonly value="{{ $feedUrl }}" style="flex:1; min-width:280px"
                    x-ref="feedUrl" onclick="this.select()">
                <button type="button" class="btn-primary"
                    x-on:click="navigator.clipboard.writeText($refs.feedUrl.value); copied = true; setTimeout(() => copied = false, 1500)">
                    <span x-show="!copied">Копіювати</span><span x-show="copied" x-cloak>Скопійовано ✓</span>
                </button>
                <a href="{{ $feedUrl }}" target="_blank" rel="noopener">Відкрити фід ↗</a>
            </div>
        </div>

        <p style="margin:0 0 .25rem">
            Зараз у фіді: <strong>{{ $feedCount }}</strong> товар(ів).
        </p>
        <p style="color:#666; font-size:13px; margin:0 0 1rem">
            У фід потрапляють лише товари: <strong>Merchant = ON</strong>, з <strong>фіксованою ціною</strong>,
            з брендом і фото. Режими «Ціна від» та «Уточнюйте» виключаються автоматично.
        </p>

        <div class="is-full" style="max-width:420px">
            <label>Назва магазину у фіді</label>
            <div style="display:flex; gap:8px; flex-wrap:wrap">
                <input wire:model="merchantStoreName" type="text" style="flex:1; min-width:220px">
                <button wire:click="saveMerchantSettings">Зберегти</button>
            </div>
            @error('merchantStoreName') <span style="color:red">{{ $message }}</span> @enderror
        </div>
    </fieldset>

    <fieldset style="margin-top:1rem">
        <legend><strong>Карта сайту та robots.txt</strong></legend>
        <p style="color:#666">
            Готові посилання для пошукових систем (Google Search Center тощо). Формуються автоматично -
            просто скопіюйте або відкрийте, щоб переглянути.
        </p>

        <div class="is-full" style="margin-bottom:.75rem" x-data="{ copied: false }">
            <label>Карта сайту (sitemap.xml)</label>
            <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center">
                <input type="text" readonly value="{{ $sitemapUrl }}" style="flex:1; min-width:280px"
                    x-ref="sitemapUrl" onclick="this.select()">
                <button type="button" class="btn-primary"
                    x-on:click="navigator.clipboard.writeText($refs.sitemapUrl.value); copied = true; setTimeout(() => copied = false, 1500)">
                    <span x-show="!copied">Копіювати</span><span x-show="copied" x-cloak>Скопійовано ✓</span>
                </button>
                <a href="{{ $sitemapUrl }}" target="_blank" rel="noopener">Відкрити ↗</a>
            </div>
        </div>

        <div class="is-full" x-data="{ copied: false }">
            <label>robots.txt</label>
            <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center">
                <input type="text" readonly value="{{ $robotsUrl }}" style="flex:1; min-width:280px"
                    x-ref="robotsUrl" onclick="this.select()">
                <button type="button" class="btn-primary"
                    x-on:click="navigator.clipboard.writeText($refs.robotsUrl.value); copied = true; setTimeout(() => copied = false, 1500)">
                    <span x-show="!copied">Копіювати</span><span x-show="copied" x-cloak>Скопійовано ✓</span>
                </button>
                <a href="{{ $robotsUrl }}" target="_blank" rel="noopener">Відкрити ↗</a>
            </div>
        </div>
    </fieldset>

    <fieldset style="margin-top:1rem">
        <legend><strong>Експорт</strong></legend>
        <p style="color:#666">Повне вивантаження всіх товарів з характеристиками, категоріями та сумісністю (3 листи Excel).</p>
        <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap">
            <button wire:click="export" class="btn-primary" wire:loading.attr="disabled" wire:target="export">Завантажити Excel</button>
            <span class="spinner-line" wire:loading wire:target="export"><span class="spinner"></span> Готуємо файл…</span>
        </div>
    </fieldset>

    <fieldset style="margin-top:1rem">
        <legend><strong>Імпорт каталогу</strong></legend>
        <p style="color:#666">
            Файл Excel/CSV з листами: <strong>Товари</strong> / <strong>Категорії</strong> / <strong>Сумісність</strong>.
            Оновлюються лише ті колонки, що присутні у файлі (зручно для масової зміни цін або SEO).
            Прив'язка за артикулом.
        </p>

        <input type="file" wire:model="importFile" accept=".xlsx,.xls,.csv">
        <div wire:loading wire:target="importFile" style="margin-top:6px; color:#666; font-size:13px">Завантаження файлу…</div>
        @error('importFile') <span style="color:red">{{ $message }}</span> @enderror

        <div style="margin-top:.5rem; display:flex; align-items:center; gap:12px; flex-wrap:wrap">
            <button wire:click="import" wire:loading.attr="disabled" wire:target="import">Імпортувати</button>
            <span class="spinner-line" wire:loading wire:target="import"><span class="spinner"></span> Обробка…</span>
        </div>

        @if($importReport)
            <div style="margin-top:1rem; background:#f8f9fa; padding:12px; border-radius:8px">
                <strong>Результат імпорту:</strong>
                <ul>
                    <li>Створено товарів: {{ $importReport['products_created'] ?? 0 }}</li>
                    <li>Оновлено товарів: {{ $importReport['products_updated'] ?? 0 }}</li>
                    <li>Категорій оброблено: {{ $importReport['categories_touched'] ?? 0 }}</li>
                    <li>Записів сумісності: {{ $importReport['compat_created'] ?? 0 }}</li>
                </ul>
                @if(!empty($importReport['errors']))
                    <strong style="color:#d32f2f">Помилки ({{ count($importReport['errors']) }}):</strong>
                    <ul>
                        @foreach(array_slice($importReport['errors'], 0, 50) as $err)
                            <li style="color:#d32f2f">{{ $err }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif
    </fieldset>

    <fieldset style="margin-top:1rem">
        <legend><strong>Фото архівом (ZIP)</strong></legend>
        <p style="color:#666">
            Імена файлів = артикул: <code>036898.jpg</code> - основне фото,
            <code>036898_2.jpg</code> - додаткове. Розмір/пропорція уніфікуються автоматично.
        </p>

        <input type="file" wire:model="photoArchive" accept=".zip">
        <div wire:loading wire:target="photoArchive" style="margin-top:6px; color:#666; font-size:13px">Завантаження файлу…</div>
        @error('photoArchive') <span style="color:red">{{ $message }}</span> @enderror

        <div style="margin-top:.5rem; display:flex; align-items:center; gap:12px; flex-wrap:wrap">
            <button wire:click="uploadPhotos" wire:loading.attr="disabled" wire:target="uploadPhotos">Завантажити фото</button>
            <span class="spinner-line" wire:loading wire:target="uploadPhotos"><span class="spinner"></span> Обробка…</span>
        </div>

        @if($photoReport)
            <div style="margin-top:1rem; background:#f8f9fa; padding:12px; border-radius:8px">
                <strong>Результат:</strong>
                <ul>
                    <li>Основних фото: {{ $photoReport['main'] ?? 0 }}</li>
                    <li>Додаткових фото: {{ $photoReport['gallery'] ?? 0 }}</li>
                    <li>Пропущено (немає товару): {{ $photoReport['skipped'] ?? 0 }}</li>
                </ul>
                @if(!empty($photoReport['errors']))
                    <ul>
                        @foreach($photoReport['errors'] as $err)
                            <li style="color:#d32f2f">{{ $err }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif
    </fieldset>

    <fieldset style="margin-top:1rem">
        <legend><strong>Основні фото (за іменем файлу)</strong></legend>
        <p style="color:#666">
            Спільне основне фото для кількох товарів. У колонці
            <code>Основне фото</code> імпорту вкажіть імʼя файлу (напр. <code>agro-710.jpg</code>) -
            однакове для всіх товарів з цим зображенням. Потім завантажте сюди самі файли:
            вони звʼяжуться за іменем одразу з усіма такими товарами. Порядок не важливий
            (можна спершу фото, потім імпорт). Пізніше кожному товару можна додати власні «живі» фото.
        </p>

        <input type="file" wire:model="catalogImages" accept="image/*,.svg" multiple>
        <div wire:loading wire:target="catalogImages" style="margin-top:6px; color:#666; font-size:13px">Завантаження файлів…</div>
        @error('catalogImages.*') <span style="color:red">{{ $message }}</span> @enderror

        @if($catalogImages)
            <div class="photo-grid" style="margin-top:.6rem">
                @foreach($catalogImages as $ci)
                    <div class="photo-thumb" wire:key="ci-prev-{{ $loop->index }}">
                        <img src="{{ $ci->temporaryUrl() }}" alt="">
                    </div>
                @endforeach
            </div>
        @endif

        <div style="margin-top:.5rem; display:flex; align-items:center; gap:12px; flex-wrap:wrap">
            <button wire:click="uploadCatalogImages" wire:loading.attr="disabled" wire:target="uploadCatalogImages">Завантажити основні фото</button>
            <span class="spinner-line" wire:loading wire:target="uploadCatalogImages"><span class="spinner"></span> Обробка…</span>
        </div>

        @if($catalogImageReport)
            <div style="margin-top:1rem; background:#f8f9fa; padding:12px; border-radius:8px">
                <strong>Результат:</strong>
                <ul>
                    <li>Завантажено файлів: {{ $catalogImageReport['uploaded'] ?? 0 }}</li>
                    <li>Створено нових записів: {{ $catalogImageReport['created'] ?? 0 }}</li>
                    <li>Привʼязано до товарів: {{ $catalogImageReport['linked'] ?? 0 }}</li>
                </ul>
            </div>
        @endif
    </fieldset>

    <fieldset style="margin-top:1rem">
        <legend><strong>Резервне копіювання</strong></legend>
        <p style="color:#666">
            Повна копія сайту одним файлом: база даних (товари, категорії, заявки, налаштування)
            та, за бажанням, завантажені фото. Зберігайте копію в надійному місці -
            з неї можна повністю відновити сайт.
        </p>

        <label style="display:inline-flex; align-items:center; gap:6px; margin-bottom:.6rem">
            <input type="checkbox" wire:model="backupMedia"> Включити фото (архів буде більшим)
        </label>

        <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap">
            <button wire:click="backup" class="btn-primary" wire:loading.attr="disabled" wire:target="backup">
                Завантажити резервну копію
            </button>
            <span class="spinner-line" wire:loading wire:target="backup"><span class="spinner"></span> Готуємо копію…</span>
        </div>

        <hr style="margin:1.25rem 0; border:none; border-top:1px solid #eee">

        <p style="margin:0 0 .5rem"><strong>Відновлення з копії</strong></p>
        <p style="color:#d32f2f; font-size:13px; margin:0 0 .6rem">
            Увага: відновлення повністю замінить поточні дані сайту даними з копії.
            Спершу зробіть свіжу резервну копію.
        </p>

        <input type="file" wire:model="restoreFile" accept=".zip">
        <div wire:loading wire:target="restoreFile" style="margin-top:6px; color:#666; font-size:13px">Завантаження файлу…</div>
        @error('restoreFile') <span style="color:red">{{ $message }}</span> @enderror

        <div style="margin-top:.5rem; display:flex; align-items:center; gap:12px; flex-wrap:wrap">
            <button wire:click="restore" wire:loading.attr="disabled" wire:target="restore"
                data-confirm="Ви дійсно хочете відновити сайт із копії? Поточні дані буде замінено.">
                Відновити
            </button>
            <span class="spinner-line" wire:loading wire:target="restore"><span class="spinner"></span> Відновлюємо…</span>
        </div>

        @if($restoreReport)
            <div style="margin-top:1rem; background:#f8f9fa; padding:12px; border-radius:8px">
                <strong>Результат відновлення:</strong>
                <ul>
                    <li>База даних: {{ ($restoreReport['db'] ?? false) ? 'відновлено' : 'без змін' }}</li>
                    <li>Фото: {{ ($restoreReport['media'] ?? false) ? 'відновлено' : 'не було в копії' }}</li>
                </ul>
                @if(!empty($restoreReport['errors']))
                    <strong style="color:#d32f2f">Помилки:</strong>
                    <ul>
                        @foreach($restoreReport['errors'] as $err)
                            <li style="color:#d32f2f">{{ $err }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif
    </fieldset>
</div>
