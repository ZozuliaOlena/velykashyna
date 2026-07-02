<div>
    <div style="display:flex; justify-content:space-between; align-items:center">
        <h1>Серії техніки</h1>
        <button wire:click="openCreate">+ Додати серію</button>
    </div>


    <div class="admin-filters">
        <input wire:model.live.debounce.300ms="search" placeholder="Пошук по назві...">
        <x-admin.select model="filterBrand" placeholder="— Виробник —"
            :options="$brands->map(fn ($b) => ['value' => $b->id, 'label' => $b->name])->all()" />
    </div>

    <div class="table-scroll">
    <table border="1" cellpadding="6" style="width:100%; border-collapse:collapse">
        <thead>
            <tr><th>Серія</th><th>Виробник</th><th>Моделей</th><th>Дії</th></tr>
        </thead>
        <tbody>
            @forelse($items as $item)
            <tr wire:key="mseries-{{ $item->id }}">
                <td data-label="Серія">{{ $item->name }}</td>
                <td data-label="Виробник">{{ $item->brand?->name ?? '—' }}</td>
                <td data-label="Моделей">{{ $item->models_count }}</td>
                <td class="cell-actions">
                    <button class="icon-btn" wire:click="openEdit({{ $item->id }})" title="Редагувати" aria-label="Редагувати"><x-icon name="edit"/></button>
                    <button class="icon-btn" wire:click="delete({{ $item->id }})" data-confirm="Ви дійсно хочете видалити серію?" title="Видалити" aria-label="Видалити"><x-icon name="trash"/></button>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center">Нічого не знайдено</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <div style="margin-top:1rem">{{ $items->links('pagination.admin') }}</div>

    @if($showModal)
    <x-admin.modal :title="$editingId ? 'Редагувати серію' : 'Нова серія'">
        <div>
            <label>Виробник техніки *</label>
            <x-admin.select model="machinery_brand_id" placeholder="— Оберіть —" :live="false"
                :options="$brands->map(fn ($b) => ['value' => $b->id, 'label' => $b->name])->all()" />
            @error('machinery_brand_id') <span style="color:red">{{ $message }}</span> @enderror
        </div>
        <div class="is-full">
            <label>Назва серії *</label>
            <input wire:model="name" type="text" style="width:100%" placeholder="8R, Magnum, Axion, Vario...">
            @error('name') <span style="color:red">{{ $message }}</span> @enderror
        </div>

        <x-slot:footer>
            <button wire:click="save" data-confirm="Ви дійсно хочете зберегти зміни?">Зберегти</button>
            <button wire:click="$set('showModal', false)">Скасувати</button>
        </x-slot:footer>
    </x-admin.modal>
    @endif
</div>
