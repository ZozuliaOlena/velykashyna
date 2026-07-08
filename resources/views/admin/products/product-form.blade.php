<div>
    <div style="display:flex; justify-content:space-between; align-items:center">
        <h1>{{ $productId ? 'Редагувати товар' : 'Новий товар' }}</h1>
        <a href="{{ route('admin.products.index') }}" wire:navigate>← До списку</a>
    </div>

    <form wire:submit="save" style="max-width:760px" data-dirty-guard>

        <fieldset style="margin-top:1rem">
            <legend><strong>Основне</strong></legend>

            <div>
                <label>Артикул *</label><br>
                <input wire:model="sku" type="text">
                @error('sku') <span style="color:red">{{ $message }}</span> @enderror
            </div>

            <div>
                <label>Тип товару *</label><br>
                <x-admin.select model="product_type_id" placeholder="— Оберіть —"
                    :options="$productTypes->map(fn ($pt) => ['value' => $pt->id, 'label' => $pt->name])->all()" />
                @error('product_type_id') <span style="color:red">{{ $message }}</span> @enderror
            </div>

            <div>
                <label>Найменування *</label><br>
                <input wire:model="name" type="text" style="width:100%">
                @error('name') <span style="color:red">{{ $message }}</span> @enderror
            </div>

            <div>
                <label>Виробник</label><br>
                <x-admin.select model="brand_id" placeholder="— Не вказано —" :live="false"
                    :options="$brands->map(fn ($b) => ['value' => $b->id, 'label' => $b->name])->all()" />
                @error('brand_id') <span style="color:red">{{ $message }}</span> @enderror
            </div>

            <div>
                <label>Протектор / модель</label><br>
                <input wire:model="model" type="text">
            </div>
        </fieldset>

        <fieldset style="margin-top:1rem">
            <legend><strong>Типорозмір та характеристики</strong></legend>

            <div>
                <label>Типорозмір (як є)</label><br>
                <input wire:model="size_raw" type="text" placeholder="710/70R38">
            </div>

            <div style="display:flex; gap:.5rem; flex-wrap:wrap">
                <div>
                    <label>Ширина</label><br>
                    <input wire:model="size_width" type="text" style="width:90px">
                    @error('size_width') <span style="color:red">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label>Профіль</label><br>
                    <input wire:model="size_profile" type="text" style="width:90px">
                    @error('size_profile') <span style="color:red">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label>Посадковий діаметр</label><br>
                    <input wire:model="rim_diameter" type="text" style="width:120px">
                    @error('rim_diameter') <span style="color:red">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label>R/D</label><br>
                    <select wire:model="rd_type" style="width:70px">
                        <option value="">—</option>
                        <option value="R">R</option>
                        <option value="D">D</option>
                    </select>
                </div>
            </div>

            <div style="display:flex; gap:.5rem; flex-wrap:wrap">
                <div>
                    <label>TT/TL</label><br>
                    <select wire:model="tube_type" style="width:80px">
                        <option value="">—</option>
                        <option value="TT">TT</option>
                        <option value="TL">TL</option>
                    </select>
                </div>
                <div>
                    <label>PR</label><br>
                    <input wire:model="ply_rating" type="text" style="width:80px">
                </div>
                <div>
                    <label>LI/SS (індекс)</label><br>
                    <input wire:model="load_speed_index" type="text" style="width:140px">
                </div>
            </div>

            <div>
                <label>Специфікація</label><br>
                <input wire:model="specification" type="text" placeholder="STEEL BELTED..." style="width:100%">
            </div>
        </fieldset>

        <fieldset style="margin-top:1rem">
            <legend><strong>Характеристики</strong></legend>

            @if(! $product_type_id)
                <p style="color:#666">Оберіть тип товару, щоб з'явились його характеристики.</p>
            @elseif($typeAttributes->isEmpty())
                <p style="color:#666">Для цього типу ще немає характеристик. Додайте їх у розділі «Характеристики».</p>
            @else
                @foreach($typeAttributes as $attr)
                    <div wire:key="attr-{{ $attr->id }}">
                        <label>
                            {{ $attr->name }}@if($attr->unit) <small style="color:#888">({{ $attr->unit }})</small>@endif
                            @if($attr->product_type_id === null) <small style="color:#888">· спільна</small>@endif
                        </label><br>
                        @switch($attr->data_type)
                            @case('select')
                                <select wire:model="attrValues.{{ $attr->id }}">
                                    <option value="">—</option>
                                    @foreach($attr->options as $opt)
                                        <option value="{{ $opt->id }}">{{ $opt->value }}</option>
                                    @endforeach
                                </select>
                                @break
                            @case('boolean')
                                <label><input type="checkbox" wire:model="attrValues.{{ $attr->id }}"> Так</label>
                                @break
                            @case('number')
                                <input type="text" wire:model="attrValues.{{ $attr->id }}" style="width:160px">
                                @break
                            @default
                                <input type="text" wire:model="attrValues.{{ $attr->id }}" style="width:100%">
                        @endswitch
                        @error('attrValues.'.$attr->id) <span style="color:red">{{ $message }}</span> @enderror
                    </div>
                @endforeach
            @endif
        </fieldset>

        <fieldset style="margin-top:1rem">
            <legend><strong>Опис товару</strong></legend>
            <p style="color:#666; margin:0 0 .75rem">
                Заповніть розділи — заголовки фіксовані (їх не можна змінити). Зі вступу й розділів
                автоматично складеться готовий опис товару (для сайту та PDF).
            </p>

            <div class="is-full">
                <label>Опис</label>
                <textarea wire:model="descrIntro" rows="4" style="width:100%; line-height:1.6"
                    placeholder="Вступний опис: що це за шина, для чого, ключові технології."></textarea>
                @error('descrIntro') <span style="color:red">{{ $message }}</span> @enderror
            </div>

            <div class="is-full">
                <label>Ключові переваги <small style="color:#888">(по одному пункту в рядок)</small></label>
                <textarea wire:model="descrAdvantages" rows="5" style="width:100%; line-height:1.6"
                    placeholder="Технологія VF для роботи при зниженому тиску&#10;Посилений металевий брекер Steel Belted&#10;Мінімальне пошкодження рослин"></textarea>
                @error('descrAdvantages') <span style="color:red">{{ $message }}</span> @enderror
            </div>

            <div class="is-full">
                <label>Переваги над конкурентами <small style="color:#888">(по одному пункту в рядок)</small></label>
                <textarea wire:model="descrVsCompetitors" rows="5" style="width:100%; line-height:1.6"
                    placeholder="Кращa стабільність на високих швидкостях&#10;Ефективне самоочищення на вологому полі"></textarea>
                @error('descrVsCompetitors') <span style="color:red">{{ $message }}</span> @enderror
            </div>

            <div class="is-full">
                <label>Особливості експлуатації</label>
                <textarea wire:model="descrFeatures" rows="4" style="width:100%; line-height:1.6"
                    placeholder="У яких умовах працює, як поводиться, нюанси використання."></textarea>
                @error('descrFeatures') <span style="color:red">{{ $message }}</span> @enderror
            </div>

            <div class="is-full">
                <label>Чому варто придбати</label>
                <textarea wire:model="descrWhyBuy" rows="4" style="width:100%; line-height:1.6"
                    placeholder="Підсумок: для кого, чому це практичний вибір."></textarea>
                @error('descrWhyBuy') <span style="color:red">{{ $message }}</span> @enderror
            </div>
        </fieldset>

        <fieldset style="margin-top:1rem">
            <legend><strong>Думка експерта «Велика Шина» / особливості</strong></legend>
            <div class="is-full">
                <textarea wire:model="expert_note" rows="5" style="width:100%; line-height:1.6"
                    placeholder="Особливості, нюанси застосування. Напр.: передня шина на екскаваторах JCB 3CX, чудово працює на твердому покритті, але на болоті забивається."></textarea>
                @error('expert_note') <span style="color:red">{{ $message }}</span> @enderror
            </div>
        </fieldset>

        <fieldset style="margin-top:1rem">
            <legend><strong>Наявність та ціна</strong></legend>

            <div>
                <label>Наявність</label><br>
                <x-admin.select model="stock_status" :live="false" :clearable="false"
                    :options="[
                        ['value' => 'in_stock', 'label' => 'В наявності'],
                        ['value' => 'on_order', 'label' => 'Під замовлення'],
                        ['value' => 'inquiry', 'label' => 'Уточнюйте'],
                    ]" />
            </div>

            <div>
                <label>Режим ціни</label><br>
                <x-admin.select model="price_mode" :clearable="false"
                    :options="[
                        ['value' => 'fixed', 'label' => 'Є ціна'],
                        ['value' => 'from', 'label' => 'Ціна від'],
                        ['value' => 'inquiry', 'label' => 'Уточнюйте ціну'],
                    ]" />
            </div>

            @if($price_mode !== 'inquiry')
                <div style="display:flex; gap:.5rem; flex-wrap:wrap">
                    <div>
                        <label>{{ $price_mode === 'from' ? 'Ціна від *' : 'Ціна *' }}</label><br>
                        <input wire:model="price" type="text" style="width:140px">
                        @error('price') <span style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label>Валюта</label><br>
                        <select wire:model.live="currency" style="width:90px">
                            <option value="UAH">UAH</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                        </select>
                        @error('currency') <span style="color:red">{{ $message }}</span> @enderror
                    </div>
                    @if($currency !== 'UAH')
                    <div>
                        <label>Курс до грн (опц.)</label><br>
                        <input wire:model="exchange_rate" type="text" style="width:100px">
                    </div>
                    @endif
                </div>

                <div style="display:flex; gap:.5rem; flex-wrap:wrap">
                    <div>
                        <label>Знижка</label><br>
                        <input wire:model="discount_value" type="text" style="width:100px">
                    </div>
                    <div>
                        <label>Тип знижки</label><br>
                        <select wire:model="discount_type" style="width:120px">
                            <option value="">—</option>
                            <option value="percent">Відсоток</option>
                            <option value="amount">Сума</option>
                        </select>
                    </div>
                </div>
            @else
                <p style="color:#666">Ціна не показується — клієнт надсилає заявку менеджеру.</p>
            @endif

            <div style="margin-top:.75rem; display:flex; gap:1.5rem; flex-wrap:wrap">
                <div>
                    <label>Бейдж акції / ціни</label><br>
                    <x-admin.select model="promo_badge" :live="false" placeholder="— без бейджа"
                        :options="collect(\App\Models\Product::PROMO_BADGES)->map(fn ($b) => ['value' => $b, 'label' => $b])->all()" />
                </div>
                <div>
                    <label>Бейдж доставки</label><br>
                    <x-admin.select model="shipping_badge" :live="false" placeholder="— без бейджа"
                        :options="collect(\App\Models\Product::SHIPPING_BADGES)->map(fn ($b) => ['value' => $b, 'label' => $b])->all()" />
                </div>
            </div>

            <div style="margin-top:.5rem">
                <label>
                    <input wire:model="merchant_enabled" type="checkbox">
                    Передавати в Google Merchant (фід)
                </label>
            </div>

            <div style="margin-top:.75rem">
                <label>Стан товару (для Google Merchant)</label><br>
                <select wire:model="condition" style="min-width:240px">
                    <option value="new">Новий</option>
                    <option value="used">Вживаний</option>
                    <option value="refurbished">Відновлений</option>
                </select>
            </div>
        </fieldset>

        <fieldset style="margin-top:1rem">
            <legend><strong>Категорії (мультивибір)</strong></legend>
            <select wire:model="categoryIds" multiple size="8" style="width:100%">
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" style="{{ ($c->level === 1 || $c->is_branch) ? 'font-weight:700' : 'color:#6b7280' }}">{{ $c->tree_prefix }}{{ $c->name }}</option>
                @endforeach
            </select>
            <small style="color:#666">Ctrl/Cmd + клік — обрати декілька.</small>
            @error('categoryIds') <span style="color:red">{{ $message }}</span> @enderror
        </fieldset>

        <fieldset style="margin-top:1rem">
            <legend><strong>Супутні товари</strong></legend>
            <select wire:model="relatedIds" multiple size="8" style="width:100%">
                @foreach($allProducts as $p)
                    <option value="{{ $p->id }}">{{ $p->sku }} — {{ $p->name }}</option>
                @endforeach
            </select>
            <small style="color:#666">Пов'язані позиції (камери, диски, аналоги). Ctrl/Cmd + клік — декілька.</small>
            @error('relatedIds') <span style="color:red">{{ $message }}</span> @enderror
        </fieldset>

        <fieldset style="margin-top:1rem">
            <legend><strong>SEO</strong></legend>
            <div style="margin-bottom:.6rem">
                <button type="button" wire:click="generateSeo">Згенерувати SEO з даних товару</button>
                <small style="color:#666; display:block; margin-top:.25rem">Заповнить лише порожні поля. Щоб перегенерувати — очистіть поле й натисніть знову.</small>
            </div>
            <div>
                <label>Title</label><br>
                <input wire:model="seo_title" type="text" style="width:100%">
            </div>
            <div>
                <label>Description</label><br>
                <textarea wire:model="seo_description" rows="2" style="width:100%"></textarea>
            </div>
            <div>
                <label>H1</label><br>
                <input wire:model="seo_h1" type="text" style="width:100%">
            </div>
            <div>
                <label>URL (slug)</label><br>
                <input wire:model="slug" type="text" style="width:100%" placeholder="залиште порожнім — згенерується з назви">
                @error('slug') <span style="color:red">{{ $message }}</span> @enderror
            </div>
        </fieldset>

        <fieldset style="margin-top:1rem">
            <legend><strong>Фото</strong></legend>

            @unless($productId)
                <p style="color:#666">Фото завантажаться разом зі збереженням товару.</p>
            @endunless

            <div style="margin-bottom:1rem">
                <label>Основне фото</label><br>
                @if($mainMedia)
                    <div class="photo-thumb">
                        <img src="{{ \App\Support\MediaUrl::rel($mainMedia->hasGeneratedConversion('thumb') ? $mainMedia->getUrl('thumb') : $mainMedia->getUrl()) }}" alt="" data-zoom-src="{{ \App\Support\MediaUrl::rel($mainMedia->getUrl()) }}">
                        <button type="button" class="photo-del" wire:click="deleteMedia({{ $mainMedia->id }})"
                            data-confirm="Ви дійсно хочете видалити основне фото?">×</button>
                    </div>
                @elseif($catalogImageUrl)
                    <div class="photo-thumb"><img src="{{ $catalogImageUrl }}" alt=""></div>
                    <small style="color:#666; display:block; margin-bottom:.4rem">Спільне фото (призначене масово). Завантажте, щоб задати власне для цього товару.</small>
                @endif
                <x-admin.image-upload model="mainPhoto" />
                @error('mainPhoto') <span style="color:red">{{ $message }}</span> @enderror
            </div>

            <div>
                <label>Додаткові фото</label><br>
                <small style="color:#666; display:block; margin-bottom:.5rem">
                    Можна додати до {{ $galleryMax }} фото. Перетягуйте за ⠿, щоб змінити порядок.
                </small>

                @if($galleryMedia->count())
                    <div class="photo-grid" data-reorder-gallery wire:key="gallery-saved">
                        @foreach($galleryMedia as $m)
                            <div class="photo-thumb" wire:key="media-{{ $m->id }}" data-id="{{ $m->id }}">
                                <span class="drag-handle" title="Перетягнути">⠿</span>
                                <img src="{{ \App\Support\MediaUrl::rel($m->hasGeneratedConversion('thumb') ? $m->getUrl('thumb') : $m->getUrl()) }}" alt="" data-zoom-src="{{ \App\Support\MediaUrl::rel($m->getUrl()) }}">
                                <button type="button" class="photo-del" wire:click="deleteMedia({{ $m->id }})"
                                    data-confirm="Ви дійсно хочете видалити фото?">×</button>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="gallery-uploader" wire:ignore
                     data-model="galleryPhotos"
                     data-max="{{ $galleryMax }}"
                     data-saved="{{ $galleryMedia->count() }}"
                     data-immediate="{{ $productId ? '1' : '' }}">
                    <div class="photo-grid" data-pending-grid></div>
                    <label class="upload-btn" data-add>
                        <input type="file" accept="image/*" multiple hidden>
                        <span>+ Додати фото</span>
                    </label>
                    <p class="gallery-uploader__hint" data-hint style="color:#666; font-size:13px; margin:.4rem 0 0"></p>
                </div>
                @error('galleryPhotos.*') <span style="color:red">{{ $message }}</span> @enderror
            </div>

            <small style="color:#666">Розмір/пропорція уніфікуються автоматично при завантаженні.</small>
        </fieldset>

        <div style="margin:1rem 0">
            <label>
                <input wire:model="is_active" type="checkbox"> Активний (показувати на сайті)
            </label>
        </div>

        <div style="margin-bottom:2rem">
            <button type="submit" data-confirm="Ви дійсно хочете зберегти зміни?">Зберегти</button>
            <a href="{{ route('admin.products.index') }}" wire:navigate>
                <button type="button">Скасувати</button>
            </a>
        </div>
    </form>

    @if($productId)
        <details class="collapse-block" wire:ignore.self>
            <summary>PDF-картка (для Viber / Telegram)</summary>
            <div class="collapse-block__body">
            <p style="color:#666; margin:0 0 .5rem">
                Кнопка відкриває готову PDF-картку товару в новій вкладці — на телефоні
                тисніть «Поділитися» й оберіть Viber/Telegram. «Завантажити» — зберегти файл.
            </p>
            <ul style="color:#666; font-size:13px; margin:0 0 .9rem; padding-left:1.2rem">
                <li><strong>Повна</strong> — фото, всі характеристики, опис, думка експерта, фото «в роботі».</li>
                <li><strong>Коротка</strong> — лише головне: фото, основні характеристики, наявність.</li>
                <li><strong>з ціною / без ціни</strong> — показувати ціну в картці чи ні.</li>
            </ul>

            <div style="margin-bottom:.6rem">
                <span style="font-size:13px; color:#555">Повна картка:</span><br>
                <a target="_blank" rel="noopener" href="{{ route('admin.products.pdf', $productId) }}?variant=full&price=1&mode=inline"><button type="button" class="btn-primary">Повна — з ціною</button></a>
                <a target="_blank" rel="noopener" href="{{ route('admin.products.pdf', $productId) }}?variant=full&price=0&mode=inline"><button type="button">Повна — без ціни</button></a>
            </div>
            <div>
                <span style="font-size:13px; color:#555">Коротка картка:</span><br>
                <a target="_blank" rel="noopener" href="{{ route('admin.products.pdf', $productId) }}?variant=short&price=1&mode=inline"><button type="button" class="btn-primary">Коротка — з ціною</button></a>
                <a target="_blank" rel="noopener" href="{{ route('admin.products.pdf', $productId) }}?variant=short&price=0&mode=inline"><button type="button">Коротка — без ціни</button></a>
            </div>
            <div style="margin-top:.75rem; padding-top:.6rem; border-top:1px solid #eee">
                <a href="{{ route('admin.products.pdf', $productId) }}?variant=full&price=1"><button type="button">⬇ Завантажити файл (повна з ціною)</button></a>
            </div>
            </div>
        </details>
    @endif

    @if($productId)
        @livewire('admin.products.product-field-photos', ['productId' => $productId], key('field-photos-'.$productId))
    @else
        <fieldset style="margin-top:1rem">
            <legend><strong>Фото «в роботі» (застосування)</strong></legend>
            <p style="color:#666">Доступно після збереження товару — відкрийте товар на редагування, щоб додати фото з техніки.</p>
        </fieldset>
    @endif
</div>
