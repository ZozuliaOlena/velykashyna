<div>
    <div style="display:flex; justify-content:space-between; align-items:center">
        <h1>Типи товарів</h1>
        <button wire:click="openCreate">+ Додати тип</button>
    </div>

    @if(session('success')) <p style="color:green">{{ session('success') }}</p> @endif
    @if(session('error')) <p style="color:red">{{ session('error') }}</p> @endif

    <div class="admin-filters">
        <input wire:model.live.debounce.300ms="search" placeholder="Пошук...">
    </div>

    <table border="1" cellpadding="6" style="width:100%; border-collapse:collapse; margin-top:1rem">
        <thead>
            <tr><th>Код</th><th>Назва</th><th>Товарів</th><th>Дії</th></tr>
        </thead>
        <tbody>
            @forelse($types as $type)
            <tr wire:key="ptype-{{ $type->id }}">
                <td data-label="Код">{{ $type->code }}</td>
                <td data-label="Назва">{{ $type->name }}</td>
                <td data-label="Товарів">{{ $type->products_count }}</td>
                <td class="cell-actions">
                    <button wire:click="openEdit({{ $type->id }})">Редагувати</button>
                    <button wire:click="delete({{ $type->id }})" wire:confirm="Видалити тип?">Видалити</button>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center">Нічого не знайдено</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:1rem">{{ $types->links() }}</div>

    @if($showModal)
    <x-admin.modal :title="$editingId ? 'Редагувати тип' : 'Новий тип'">
        <div>
            <label>Код *</label>
            <input wire:model="code" type="text" placeholder="tire, tube, disk..." style="width:100%">
            @error('code') <span style="color:red">{{ $message }}</span> @enderror
        </div>
        <div>
            <label>Назва *</label>
            <input wire:model="name" type="text" style="width:100%">
            @error('name') <span style="color:red">{{ $message }}</span> @enderror
        </div>

        <x-slot:footer>
            <button wire:click="save">Зберегти</button>
            <button wire:click="$set('showModal', false)">Скасувати</button>
        </x-slot:footer>
    </x-admin.modal>
    @endif
</div>
