<div>
    <div style="display:flex; justify-content:space-between; align-items:center">
        <h1>Моделі техніки</h1>
        <button wire:click="openCreate">+ Додати модель</button>
    </div>

    @if(session('success')) <p style="color:green">{{ session('success') }}</p> @endif

    <div class="admin-filters">
        <input wire:model.live.debounce.300ms="search" placeholder="Пошук по назві...">
        <select wire:model.live="filterBrand">
            <option value="">— Виробник —</option>
            @foreach($brands as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
        </select>
        <select wire:model.live="filterType">
            <option value="">— Тип техніки —</option>
            @foreach($types as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
        </select>
    </div>

    <table border="1" cellpadding="6" style="width:100%; border-collapse:collapse">
        <thead>
            <tr><th>Модель</th><th>Виробник</th><th>Тип техніки</th><th>Дії</th></tr>
        </thead>
        <tbody>
            @forelse($items as $item)
            <tr wire:key="mmodel-{{ $item->id }}">
                <td>{{ $item->name }}</td>
                <td>{{ $item->brand?->name ?? '—' }}</td>
                <td>{{ $item->type?->name ?? '—' }}</td>
                <td>
                    <button wire:click="openEdit({{ $item->id }})">Редагувати</button>
                    <button wire:click="delete({{ $item->id }})" wire:confirm="Видалити модель?">Видалити</button>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center">Нічого не знайдено</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:1rem">{{ $items->links() }}</div>

    @if($showModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center">
        <div style="background:#fff;padding:2rem;min-width:420px">
            <h2>{{ $editingId ? 'Редагувати модель' : 'Нова модель' }}</h2>
            <div>
                <label>Виробник техніки *</label><br>
                <select wire:model="machinery_brand_id" style="width:100%">
                    <option value="">— Оберіть —</option>
                    @foreach($brands as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
                </select>
                @error('machinery_brand_id') <span style="color:red">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Тип техніки *</label><br>
                <select wire:model="machinery_type_id" style="width:100%">
                    <option value="">— Оберіть —</option>
                    @foreach($types as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
                </select>
                @error('machinery_type_id') <span style="color:red">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Назва моделі *</label><br>
                <input wire:model="name" type="text" style="width:100%" placeholder="8400, MX230, Steiger...">
                @error('name') <span style="color:red">{{ $message }}</span> @enderror
            </div>
            <div style="margin-top:1rem">
                <button wire:click="save">Зберегти</button>
                <button wire:click="$set('showModal', false)">Скасувати</button>
            </div>
        </div>
    </div>
    @endif
</div>
