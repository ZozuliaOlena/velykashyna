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
                <td data-label="Модель">{{ $item->name }}</td>
                <td data-label="Виробник">{{ $item->brand?->name ?? '—' }}</td>
                <td data-label="Тип техніки">{{ $item->type?->name ?? '—' }}</td>
                <td class="cell-actions">
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
    <x-admin.modal :title="$editingId ? 'Редагувати модель' : 'Нова модель'">
        <div>
            <label>Виробник техніки *</label>
            <select wire:model="machinery_brand_id" style="width:100%">
                <option value="">— Оберіть —</option>
                @foreach($brands as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
            </select>
            @error('machinery_brand_id') <span style="color:red">{{ $message }}</span> @enderror
        </div>
        <div>
            <label>Тип техніки *</label>
            <select wire:model="machinery_type_id" style="width:100%">
                <option value="">— Оберіть —</option>
                @foreach($types as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
            </select>
            @error('machinery_type_id') <span style="color:red">{{ $message }}</span> @enderror
        </div>
        <div class="is-full">
            <label>Назва моделі *</label>
            <input wire:model="name" type="text" style="width:100%" placeholder="8400, MX230, Steiger...">
            @error('name') <span style="color:red">{{ $message }}</span> @enderror
        </div>

        <x-slot:footer>
            <button wire:click="save">Зберегти</button>
            <button wire:click="$set('showModal', false)">Скасувати</button>
        </x-slot:footer>
    </x-admin.modal>
    @endif
</div>
