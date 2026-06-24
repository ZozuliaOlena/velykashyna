<div>
    <h1>Заявки</h1>

    @if(session('success')) <p style="color:green">{{ session('success') }}</p> @endif

    <div class="admin-filters">
        <input wire:model.live.debounce.300ms="search" placeholder="Пошук: ім'я, телефон...">
        <select wire:model.live="filterStatus">
            <option value="">— Усі статуси —</option>
            @foreach($statuses as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <table border="1" cellpadding="6" style="width:100%; border-collapse:collapse">
        <thead>
            <tr><th>#</th><th>Клієнт</th><th>Телефон</th><th>Спосіб</th><th>Позицій</th><th>Статус</th><th>Дата</th><th>Дії</th></tr>
        </thead>
        <tbody>
            @forelse($leads as $lead)
            <tr wire:key="lead-{{ $lead->id }}">
                <td>{{ $lead->id }}</td>
                <td>{{ $lead->customer_name }}</td>
                <td>{{ $lead->phone }}</td>
                <td>{{ $lead->contact_method ?? '—' }}</td>
                <td>{{ $lead->items_count }}</td>
                <td>{{ $statuses[$lead->status] ?? $lead->status }}</td>
                <td>{{ $lead->created_at?->format('d.m.Y H:i') }}</td>
                <td>
                    <button wire:click="openView({{ $lead->id }})">Переглянути</button>
                    <button wire:click="delete({{ $lead->id }})" wire:confirm="Видалити заявку?">Видалити</button>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center">Заявок не знайдено</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:1rem">{{ $leads->links() }}</div>

    @if($showModal && $current)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;overflow:auto">
        <div style="background:#fff;padding:2rem;min-width:520px;max-height:90vh;overflow:auto">
            <h2>Заявка #{{ $current->id }}</h2>

            <div style="display:flex; gap:.5rem; flex-wrap:wrap">
                <div>
                    <label>Клієнт *</label><br>
                    <input wire:model="customer_name" type="text">
                    @error('customer_name') <span style="color:red">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label>Телефон *</label><br>
                    <input wire:model="phone" type="text">
                    @error('phone') <span style="color:red">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label>Спосіб зв'язку</label><br>
                    <input wire:model="contact_method" type="text">
                </div>
            </div>

            <div>
                <label>Статус *</label><br>
                <select wire:model="status">
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label>Коментар менеджера</label><br>
                <textarea wire:model="manager_comment" rows="3" style="width:100%"></textarea>
            </div>

            <h3 style="margin-top:1rem">Позиції заявки</h3>
            <table border="1" cellpadding="6" style="width:100%; border-collapse:collapse">
                <thead>
                    <tr><th>Товар</th><th>Артикул</th><th>К-ть</th><th>Ціна на момент заявки</th></tr>
                </thead>
                <tbody>
                    @forelse($current->items as $item)
                    <tr wire:key="leaditem-{{ $item->id }}">
                        <td>{{ $item->product?->name ?? '— видалений товар —' }}</td>
                        <td>{{ $item->product?->sku ?? '—' }}</td>
                        <td>{{ $item->qty }}</td>
                        <td>{{ $item->price_at_request ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center">Без позицій</td></tr>
                    @endforelse
                </tbody>
            </table>

            <div style="margin-top:1rem">
                <button wire:click="save">Зберегти</button>
                <button wire:click="$set('showModal', false)">Закрити</button>
            </div>
        </div>
    </div>
    @endif
</div>
