<div>
    <div style="display:flex; justify-content:space-between; align-items:center">
        <h1>{{ $productId ? 'Редагувати товар' : 'Новий товар' }}</h1>
        <a href="{{ route('admin.products.index') }}" wire:navigate>← До списку</a>
    </div>

    <form wire:submit="save" style="max-width:760px">

        {{-- ── Основне ─────────────────────────────────────── --}}
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

        {{-- ── Типорозмір ──────────────────────────────────── --}}
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

        {{-- ── Гнучкі характеристики (за типом товару) ─────── --}}
        <fieldset style="margin-top:1rem">
            <legend><strong>Характеристики</strong></legend>

            @if(! $product_type_id)
                <p style="color:#666">Оберіть тип товару, щоб з'явились його характеристики.</p>
            @elseif($attributes->isEmpty())
                <p style="color:#666">Для цього типу ще немає характеристик. Додайте їх у розділі «Характеристики».</p>
            @else
                @foreach($attributes as $attr)
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

        {{-- ── Наявність та ціна ───────────────────────────── --}}
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
                        <select wire:model="currency" style="width:90px">
                            <option value="UAH">UAH</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                        </select>
                        @error('currency') <span style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label>Курс (опц.)</label><br>
                        <input wire:model="exchange_rate" type="text" style="width:100px">
                    </div>
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

                <div style="margin-top:.5rem; display:flex; gap:1.5rem; flex-wrap:wrap">
                    <label><input wire:model="is_promo" type="checkbox"> Акція (бейдж на сайті)</label>
                    <label><input wire:model="free_shipping" type="checkbox"> Безкоштовна доставка</label>
                </div>
            @else
                <p style="color:#666">Ціна не показується — клієнт надсилає заявку менеджеру.</p>
            @endif

            <div style="margin-top:.5rem">
                <label>
                    <input wire:model="merchant_enabled" type="checkbox">
                    Передавати в Google Merchant (фід)
                </label>
            </div>
        </fieldset>

        {{-- ── Категорії ───────────────────────────────────── --}}
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

        {{-- ── Супутні товари ──────────────────────────────── --}}
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

        {{-- ── SEO ─────────────────────────────────────────── --}}
        <fieldset style="margin-top:1rem">
            <legend><strong>SEO</strong></legend>
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

        {{-- ── Фото ────────────────────────────────────────── --}}
        <fieldset style="margin-top:1rem">
            <legend><strong>Фото</strong></legend>

            @unless($productId)
                <p style="color:#666">Фото завантажаться разом зі збереженням товару.</p>
            @endunless

            {{-- Спільне каталожне фото (призначається масово на сторінці товарів) --}}
            @if($catalogImageUrl)
                <div style="margin-bottom:1rem">
                    <label>Каталожне фото (спільне)</label><br>
                    <div class="photo-thumb"><img src="{{ $catalogImageUrl }}" alt=""></div>
                    <small style="color:#666">Використовується, поки немає власних фото. Призначається масово на сторінці «Товари».</small>
                </div>
            @endif

            {{-- Основне фото --}}
            <div style="margin-bottom:1rem">
                <label>Основне фото</label><br>
                @if($mainMedia)
                    <div class="photo-thumb">
                        <img src="{{ \App\Support\MediaUrl::rel($mainMedia->hasGeneratedConversion('thumb') ? $mainMedia->getUrl('thumb') : $mainMedia->getUrl()) }}" alt="">
                        <button type="button" class="photo-del" wire:click="deleteMedia({{ $mainMedia->id }})"
                            data-confirm="Ви дійсно хочете видалити основне фото?">×</button>
                    </div>
                @endif
                <input wire:model="mainPhoto" type="file" accept="image/*">
                <div wire:loading wire:target="mainPhoto" style="color:#666">Завантаження…</div>
                @error('mainPhoto') <span style="color:red">{{ $message }}</span> @enderror
            </div>

            {{-- Додаткові фото --}}
            <div>
                <label>Додаткові фото</label><br>
                @if($galleryMedia->count())
                    <div class="photo-grid">
                        @foreach($galleryMedia as $m)
                            <div class="photo-thumb" wire:key="media-{{ $m->id }}">
                                <img src="{{ \App\Support\MediaUrl::rel($m->hasGeneratedConversion('thumb') ? $m->getUrl('thumb') : $m->getUrl()) }}" alt="">
                                <button type="button" class="photo-del" wire:click="deleteMedia({{ $m->id }})"
                                    data-confirm="Ви дійсно хочете видалити фото?">×</button>
                            </div>
                        @endforeach
                    </div>
                @endif
                <input wire:model="galleryPhotos" type="file" accept="image/*" multiple>
                <div wire:loading wire:target="galleryPhotos" style="color:#666">Завантаження…</div>
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
</div>
