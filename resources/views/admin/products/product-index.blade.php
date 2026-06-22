<div>
    <div style="display:flex; justify-content:space-between; align-items:center">
        <h1>Товари</h1>
        <a href="{{ route('admin.products.create') }}">
            <button>+ Додати товар</button>
        </a>
    </div>

    @if(session('success'))
        <p style="color:green">{{ session('success') }}</p>
    @endif

    {{-- Фільтри --}}
    <div style="display:flex; gap:.5rem; flex-wrap:wrap; align-items:center; margin:1rem 0">
        <input wire:model.live.debounce.400ms="search" placeholder="Пошук: артикул, назва, розмір...">

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
        <div style="background:#eef; padding:.5rem; margin-bottom:.5rem; display:flex; gap:.5rem; flex-wrap:wrap; align-items:center">
            <strong>Вибрано: {{ count($selected) }}</strong>
            <button wire:click="bulkSetActive(true)">Активувати</button>
            <button wire:click="bulkSetActive(false)">Деактивувати</button>
            <button wire:click="bulkSetMerchant(true)">Merchant ON</button>
            <button wire:click="bulkSetMerchant(false)">Merchant OFF</button>
            <button wire:click="bulkDelete" wire:confirm="Видалити вибрані товари?"
                style="color:red">Видалити</button>
        </div>
    @endif

    <table border="1" cellpadding="6" style="width:100%; border-collapse:collapse">
        <thead>
            <tr>
                <th><input type="checkbox" wire:model.live="selectPage"></th>
                <th>Артикул</th>
                <th>Назва</th>
                <th>Тип</th>
                <th>Виробник</th>
                <th>Розмір</th>
                <th>Ціна</th>
                <th>Наявність</th>
                <th>Активний</th>
                <th>Merchant</th>
                <th>Дії</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
            <tr wire:key="product-{{ $product->id }}">
                <td><input type="checkbox" wire:model.live="selected" value="{{ $product->id }}"></td>
                <td>{{ $product->sku }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->productType?->name ?? '—' }}</td>
                <td>{{ $product->brand?->name ?? '—' }}</td>
                <td>{{ $product->size_raw ?? '—' }}</td>
                <td>
                    @if($product->price_mode === 'inquiry')
                        Уточнюйте
                    @elseif($product->price_mode === 'from')
                        від {{ $product->price }} {{ $product->currency }}
                    @else
                        {{ $product->price }} {{ $product->currency }}
                    @endif
                </td>
                <td>
                    @switch($product->stock_status)
                        @case('in_stock') В наявності @break
                        @case('on_order') Під замовлення @break
                        @default Уточнюйте
                    @endswitch
                </td>
                <td>
                    <button wire:click="toggleActive({{ $product->id }})">
                        {{ $product->is_active ? 'Так' : 'Ні' }}
                    </button>
                </td>
                <td>
                    <button wire:click="toggleMerchant({{ $product->id }})">
                        {{ $product->merchant_enabled ? 'ON' : 'OFF' }}
                    </button>
                </td>
                <td>
                    <a href="{{ route('admin.products.edit', $product->id) }}">Редагувати</a>
                    <button wire:click="delete({{ $product->id }})"
                        wire:confirm="Видалити товар?">Видалити</button>
                </td>
            </tr>
            @empty
            <tr><td colspan="11" style="text-align:center">Товарів не знайдено</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:1rem">
        {{ $products->links() }}
    </div>
</div>
