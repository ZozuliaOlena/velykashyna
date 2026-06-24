<div>
    <div style="display:flex; justify-content:space-between; align-items:center">
        <h1>Товари</h1>
        <a href="{{ route('admin.products.create') }}">
            <button class="btn-primary">+ Додати товар</button>
        </a>
    </div>

    @if(session('success')) <p style="color:green">{{ session('success') }}</p> @endif
    @if(session('error')) <p style="color:red">{{ session('error') }}</p> @endif

    {{-- Фільтри --}}
    <div class="admin-filters">
        <input wire:model.live.debounce.400ms="search" placeholder="Пошук: артикул, розмір (710/70R38)...">

        <select wire:model.live="size">
            <option value="">— Типорозмір —</option>
            @foreach($sizes as $s)
                <option value="{{ $s }}">{{ $s }}</option>
            @endforeach
        </select>

        <select wire:model.live="type">
            <option value="">— Тип товару —</option>
            @foreach($productTypes as $pt)
                <option value="{{ $pt->id }}">{{ $pt->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="brand">
            <option value="">— Виробник —</option>
            @foreach($brands as $b)
                <option value="{{ $b->id }}">{{ $b->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="category">
            <option value="">— Категорія —</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}">
                    {{ str_repeat('— ', max(0, $c->level - 1)) }}{{ $c->name }}
                </option>
            @endforeach
        </select>

        <select wire:model.live="stock">
            <option value="">— Наявність —</option>
            <option value="in_stock">В наявності</option>
            <option value="on_order">Під замовлення</option>
            <option value="inquiry">Уточнюйте</option>
        </select>

        <button wire:click="resetFilters">Скинути</button>
    </div>

    {{-- Панель масових дій --}}
    @if(count($selected))
        <div class="bulk-bar">
            <strong>Вибрано: {{ count($selected) }}</strong>

            {{-- Наявність --}}
            <span class="bulk-group">
                <select wire:model="bulkStock">
                    <option value="">Наявність…</option>
                    <option value="in_stock">В наявності</option>
                    <option value="on_order">Під замовлення</option>
                    <option value="inquiry">Уточнюйте</option>
                </select>
                <button wire:click="bulkSetStock">Застосувати</button>
            </span>

            {{-- Ціна / режим ціни --}}
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
                <button wire:click="bulkSetPrice">Застосувати</button>
            </span>

            {{-- Merchant --}}
            <span class="bulk-group">
                <button wire:click="bulkSetMerchant(true)">Merchant ON</button>
                <button wire:click="bulkSetMerchant(false)">Merchant OFF</button>
            </span>

            {{-- Активність --}}
            <span class="bulk-group">
                <button wire:click="bulkSetActive(true)">Активувати</button>
                <button wire:click="bulkSetActive(false)">Деактивувати</button>
            </span>

            <button wire:click="bulkDelete" wire:confirm="Видалити вибрані товари?">Видалити</button>
        </div>
    @endif

    <table border="1" cellpadding="6" style="width:100%; border-collapse:collapse">
        <thead>
            <tr>
                <th><input type="checkbox" wire:model.live="selectPage"></th>
                <th>Артикул</th>
                <th>Найменування</th>
                <th>Типорозмір</th>
                <th>Виробник</th>
                <th>Наявність</th>
                <th>Ціна</th>
                <th>Merchant</th>
                <th>Активний</th>
                <th>Дії</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
            <tr wire:key="product-{{ $product->id }}">
                <td data-label="Вибір"><input type="checkbox" wire:model.live="selected" value="{{ $product->id }}"></td>
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
                    @elseif($product->price_mode === 'from')
                        від {{ $product->price }} {{ $product->currency }}
                    @else
                        {{ $product->price }} {{ $product->currency }}
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
                    <a href="{{ route('admin.products.edit', $product->id) }}">Редагувати</a>
                    <button wire:click="delete({{ $product->id }})"
                        wire:confirm="Видалити товар?">Видалити</button>
                </td>
            </tr>
            @empty
            <tr><td colspan="10" style="text-align:center">Товарів не знайдено</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:1rem">
        {{ $products->links() }}
    </div>
</div>
