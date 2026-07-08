<div>
    <div style="display:flex; justify-content:space-between; align-items:center">
        <h1>Товари</h1>
        <a href="{{ route('admin.products.create') }}">
            <button class="btn-primary">+ Додати товар</button>
        </a>
    </div>


    <div class="admin-filters">
        <input wire:model.live.debounce.400ms="search" placeholder="Пошук: артикул, розмір (710/70R38)...">

        <x-admin.select model="size" placeholder="— Типорозмір —"
            :options="collect($sizes)->map(fn ($s) => ['value' => $s, 'label' => $s])->all()" />

        <x-admin.select model="type" placeholder="— Тип товару —"
            :options="$productTypes->map(fn ($pt) => ['value' => $pt->id, 'label' => $pt->name])->all()" />

        <x-admin.select model="brand" placeholder="— Виробник —"
            :options="$brands->map(fn ($b) => ['value' => $b->id, 'label' => $b->name])->all()" />

        <x-admin.select model="category" placeholder="— Категорія —"
            :options="$categories->map(fn ($c) => [
                'value' => $c->id,
                'label' => $c->tree_prefix . $c->name,
                'style' => ($c->level === 1 || $c->is_branch) ? 'font-weight:700' : 'color:#6b7280',
            ])->all()" />

        <x-admin.select model="stock" placeholder="— Наявність —"
            :options="[
                ['value' => 'in_stock', 'label' => 'В наявності'],
                ['value' => 'on_order', 'label' => 'Під замовлення'],
                ['value' => 'inquiry', 'label' => 'Уточнюйте'],
            ]" />

        <button wire:click="resetFilters">Скинути</button>
    </div>

    <div class="rate-bar">
        <strong>Курс валют (масово):</strong>
        <span class="bulk-group">
            <select wire:model="bulkRateCurrency">
                <option value="">Валюта товарів…</option>
                <option value="USD">дол (USD) — {{ $currencyCounts['USD'] ?? 0 }} тов.</option>
                <option value="EUR">євро (EUR) — {{ $currencyCounts['EUR'] ?? 0 }} тов.</option>
            </select>
            <input wire:model="bulkRateValue" type="number" step="0.0001" min="0"
                placeholder="курс, грн" style="width:110px" title="Скільки гривень за 1 одиницю валюти">
            <button wire:click="bulkSetExchangeRate">Застосувати курс усім</button>
        </span>
        <small style="color:#666">Напр.: усім товарам у EUR — курс 45. Курс збережеться у полі «Курс» кожного такого товару.</small>
    </div>

    @if(count($selected))
        <div class="bulk-bar">
            <strong>Вибрано: {{ count($selected) }}</strong>

            <span class="bulk-group">
                <select wire:model="bulkStock">
                    <option value="">Наявність…</option>
                    <option value="in_stock">В наявності</option>
                    <option value="on_order">Під замовлення</option>
                    <option value="inquiry">Уточнюйте</option>
                </select>
                <button wire:click="bulkSetStock">Застосувати</button>
            </span>

            <span class="bulk-group">
                <select wire:model.live="bulkPriceMode">
                    <option value="">Режим ціни…</option>
                    <option value="fixed">Є ціна</option>
                    <option value="from">Ціна від</option>
                    <option value="inquiry">Уточнюйте</option>
                </select>
                @if($bulkPriceMode === 'fixed' || $bulkPriceMode === 'from')
                    <input wire:model="bulkPrice" type="text" placeholder="Ціна" style="width:90px">
                @endif
                <select wire:model="bulkCurrency" title="Валюта (можна змінити й без ціни)">
                    <option value="">Валюта…</option>
                    <option value="UAH">грн (UAH)</option>
                    <option value="USD">дол (USD)</option>
                    <option value="EUR">євро (EUR)</option>
                </select>
                <button wire:click="bulkSetPrice">Застосувати</button>
            </span>

            <span class="bulk-group">
                <input wire:model="bulkPricePercent" type="number" step="0.1"
                    placeholder="%" style="width:80px" title="Напр. 10 = +10%, -5 = знижка">
                <button wire:click="bulkRaisePrice"
                    data-confirm="Ви дійсно хочете змінити ціну вибраних товарів на вказаний відсоток?">Змінити ціну на %</button>
            </span>

            <span class="bulk-group">
                <select wire:model.live="bulkDiscountType">
                    <option value="">Знижка…</option>
                    <option value="percent">Відсоток (%)</option>
                    <option value="amount">Сума (грн)</option>
                </select>
                @if($bulkDiscountType === 'percent' || $bulkDiscountType === 'amount')
                    <input wire:model="bulkDiscountValue" type="number" step="0.01" min="0"
                        placeholder="{{ $bulkDiscountType === 'percent' ? '%' : 'грн' }}" style="width:90px">
                    <button wire:click="bulkSetDiscount"
                        data-confirm="Встановити знижку для вибраних товарів?">Застосувати</button>
                @endif
                <button wire:click="bulkClearDiscount"
                    data-confirm="Прибрати знижку з вибраних товарів?">Прибрати знижку</button>
            </span>

            <span class="bulk-group">
                <label class="file-pick" title="Основне фото для вибраних товарів (одне на кілька)">
                    <input type="file" wire:model="bulkCatalogPhoto" accept="image/*">
                    <span>📷 Основне фото…</span>
                </label>
                @if($bulkCatalogPhoto)
                    <img src="{{ $bulkCatalogPhoto->temporaryUrl() }}" alt="" data-zoom
                        style="height:34px; width:34px; object-fit:contain; border:1px solid #ddd; border-radius:6px; background:#fff">
                    <button wire:click="bulkSetCatalogPhoto">Застосувати</button>
                @endif
                <span wire:loading wire:target="bulkCatalogPhoto" style="color:#666">Завантаження…</span>
                @error('bulkCatalogPhoto') <span style="color:red">{{ $message }}</span> @enderror
            </span>

            <span class="bulk-group">
                <button wire:click="bulkSetMerchant(true)">Merchant ON</button>
                <button wire:click="bulkSetMerchant(false)">Merchant OFF</button>
            </span>

            <span class="bulk-group">
                <button wire:click="bulkSetActive(true)">Активувати</button>
                <button wire:click="bulkSetActive(false)">Деактивувати</button>
            </span>

            <span class="bulk-group">
                <button wire:click="bulkGenerateSeo"
                    data-confirm="Згенерувати SEO для вибраних товарів? (заповняться лише порожні поля)">Згенерувати SEO</button>
            </span>

            <span class="bulk-group">
                <button wire:click="bulkSetPromo(true)">Акція ON</button>
                <button wire:click="bulkSetPromo(false)">Акція OFF</button>
            </span>

            <span class="bulk-group">
                <button wire:click="bulkSetFreeShipping(true)">Доставка ON</button>
                <button wire:click="bulkSetFreeShipping(false)">Доставка OFF</button>
            </span>

            <button wire:click="bulkDelete" data-confirm="Ви дійсно хочете видалити вибрані товари?">Видалити</button>
        </div>
    @endif

    <div class="table-scroll">
    <table border="1" cellpadding="6" style="width:100%; border-collapse:collapse">
        <thead>
            <tr>
                <th><input type="checkbox" wire:model.live="selectPage"></th>
                <th>Фото</th>
                <th>Артикул</th>
                <th>Найменування</th>
                <th>Типорозмір</th>
                <th>Виробник</th>
                <th>Наявність</th>
                <th>Ціна</th>
                <th>Знижка</th>
                <th>Акція</th>
                <th>Доставка</th>
                <th>Merchant</th>
                <th>Активний</th>
                <th>Дії</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
            <tr wire:key="product-{{ $product->id }}">
                <td data-label="Вибір"><input type="checkbox" wire:model.live="selected" value="{{ $product->id }}"></td>
                <td data-label="Фото">
                    @php($thumb = $product->thumbUrl())
                    @if($thumb)
                        <img src="{{ $thumb }}" alt="" class="product-thumb">
                    @else
                        <span class="product-thumb product-thumb--empty">—</span>
                    @endif
                </td>
                <td data-label="Артикул">{{ $product->sku }}</td>
                <td data-label="Найменування">{{ $product->name }}</td>
                <td data-label="Типорозмір">{{ $product->size_raw ?? '—' }}</td>
                <td data-label="Виробник">{{ $product->brand?->name ?? '—' }}</td>
                <td data-label="Наявність">
                    @switch($product->stock_status)
                        @case('in_stock') В наявності @break
                        @case('on_order') Під замовлення @break
                        @default Уточнюйте
                    @endswitch
                </td>
                <td data-label="Ціна">
                    @if($product->price_mode === 'inquiry')
                        Уточнюйте
                    @else
                        @php($pref = $product->price_mode === 'from' ? 'від ' : '')
                        @if($product->hasDiscount())
                            <span style="text-decoration:line-through; color:#9aa0aa">{{ $pref }}{{ $product->oldPrice() }} {{ $product->currency }}</span><br>
                            <strong style="color:#d32f2f">{{ $pref }}{{ $product->effectivePrice() }} {{ $product->currency }}</strong>
                        @else
                            {{ $pref }}{{ $product->price }} {{ $product->currency }}
                        @endif
                    @endif
                </td>
                <td data-label="Знижка">
                    @if($product->discountLabel())
                        <span class="badge-discount">{{ $product->discountLabel() }}</span>
                    @else
                        <span style="color:#bbb">—</span>
                    @endif
                </td>
                @php($promoShort = ['Акція' => 'Акція', 'Запитуй знижку' => 'Знижка?', 'Уточніть вашу ціну' => 'Ціна?'])
                @php($shipShort = ['Безкоштовна доставка' => 'Безкошт.', 'Можлива безкоштовна доставка' => 'Можл. безкошт.'])
                <td data-label="Акція">
                    @if($product->promo_badge)
                        <span class="badge-tag" title="{{ $product->promo_badge }}">{{ $promoShort[$product->promo_badge] ?? $product->promo_badge }}</span>
                    @else
                        <span style="color:#bbb">—</span>
                    @endif
                </td>
                <td data-label="Доставка">
                    @if($product->shipping_badge)
                        <span class="badge-tag" title="{{ $product->shipping_badge }}">{{ $shipShort[$product->shipping_badge] ?? $product->shipping_badge }}</span>
                    @else
                        <span style="color:#bbb">—</span>
                    @endif
                </td>
                <td data-label="Merchant">
                    <button wire:click="toggleMerchant({{ $product->id }})"
                        class="row-toggle {{ $product->merchant_enabled ? 'is-on' : 'is-off' }}">
                        {{ $product->merchant_enabled ? 'ON' : 'OFF' }}
                    </button>
                </td>
                <td data-label="Активний">
                    <button wire:click="toggleActive({{ $product->id }})"
                        class="row-toggle {{ $product->is_active ? 'is-on' : 'is-off' }}">
                        {{ $product->is_active ? 'Так' : 'Ні' }}
                    </button>
                </td>
                <td class="cell-actions">
                    <a class="icon-btn" href="{{ route('admin.products.edit', $product->id) }}" title="Редагувати" aria-label="Редагувати"><x-icon name="edit"/></a>
                    <a class="icon-btn" href="{{ route('admin.products.pdf', $product->id) }}?mode=inline" target="_blank" rel="noopener" title="PDF-картка" aria-label="PDF-картка"><x-icon name="file"/></a>
                    <button class="icon-btn" wire:click="delete({{ $product->id }})"
                        data-confirm="Ви дійсно хочете видалити товар?" title="Видалити" aria-label="Видалити"><x-icon name="trash"/></button>
                </td>
            </tr>
            @empty
            <tr><td colspan="14" style="text-align:center">Товарів не знайдено</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <div style="margin-top:1rem">
        {{ $products->links('pagination.admin') }}
    </div>
</div>
