<div>
    <div style="display:flex; justify-content:space-between; align-items:center">
        <h1>Типи товарів</h1>
        <button wire:click="openCreate">+ Додати тип</button>
    </div>

    <div class="admin-filters">
        <input wire:model.live.debounce.300ms="search" placeholder="Пошук...">
    </div>

    <div class="table-scroll">
    <table border="1" cellpadding="6" style="width:100%; border-collapse:collapse">
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
                    <button class="icon-btn" wire:click="openEdit({{ $type->id }})" title="Редагувати" aria-label="Редагувати"><x-icon name="edit"/></button>
                    <button class="icon-btn" wire:click="delete({{ $type->id }})" data-confirm="Ви дійсно хочете видалити тип товару?" title="Видалити" aria-label="Видалити"><x-icon name="trash"/></button>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center">Нічого не знайдено</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <div style="margin-top:1rem">{{ $types->links('pagination.admin') }}</div>

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
        <div class="is-full">
            <label>Google-категорія (для фіду Merchant)</label>
            <input wire:model="google_category" type="text" style="width:100%"
                placeholder="Vehicles & Parts > Vehicle Parts & Accessories > Motor Vehicle Parts > Motor Vehicle Tires">
            @error('google_category') <span style="color:red">{{ $message }}</span> @enderror
            <small style="color:#666">Залиште порожнім - застосується розумний дефолт за кодом типу (шини / диски / інше).</small>
        </div>

        <x-slot:footer>
            <button wire:click="save" data-confirm="Ви дійсно хочете зберегти зміни?">Зберегти</button>
            <button wire:click="$set('showModal', false)">Скасувати</button>
        </x-slot:footer>
    </x-admin.modal>
    @endif
</div>
