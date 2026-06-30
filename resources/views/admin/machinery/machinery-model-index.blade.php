<div>
    <div style="display:flex; justify-content:space-between; align-items:center">
        <h1>Моделі техніки</h1>
        <button wire:click="openCreate">+ Додати модель</button>
    </div>

    @if(session('success')) <p style="color:green">{{ session('success') }}</p> @endif

    <div class="admin-filters">
        <input wire:model.live.debounce.300ms="search" placeholder="Пошук по назві...">
        <x-admin.select model="filterBrand" placeholder="— Виробник —"
            :options="$brands->map(fn ($b) => ['value' => $b->id, 'label' => $b->name])->all()" />
        <x-admin.select model="filterType" placeholder="— Тип техніки —"
            :options="$types->map(fn ($t) => ['value' => $t->id, 'label' => $t->name])->all()" />
    </div>

    <div class="table-scroll">
    <table border="1" cellpadding="6" style="width:100%; border-collapse:collapse">
        <thead>
            <tr><th>Модель</th><th>Виробник</th><th>Серія</th><th>Тип техніки</th><th>Дії</th></tr>
        </thead>
        <tbody>
            @forelse($items as $item)
            <tr wire:key="mmodel-{{ $item->id }}">
                <td data-label="Модель">{{ $item->name }}</td>
                <td data-label="Виробник">{{ $item->brand?->name ?? '—' }}</td>
                <td data-label="Серія">{{ $item->series?->name ?? '—' }}</td>
                <td data-label="Тип техніки">{{ $item->type?->name ?? '—' }}</td>
                <td class="cell-actions">
                    <a class="icon-btn" href="{{ route('admin.field-photos.index', ['filterModel' => $item->id]) }}" wire:navigate title="Фото в роботі" aria-label="Фото в роботі"><x-icon name="eye"/></a>
                    <button class="icon-btn" wire:click="openEdit({{ $item->id }})" title="Редагувати" aria-label="Редагувати"><x-icon name="edit"/></button>
                    <button class="icon-btn" wire:click="delete({{ $item->id }})" data-confirm="Ви дійсно хочете видалити модель?" title="Видалити" aria-label="Видалити"><x-icon name="trash"/></button>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center">Нічого не знайдено</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <div style="margin-top:1rem">{{ $items->links('pagination.admin') }}</div>

    @if($showModal)
    <x-admin.modal :title="$editingId ? 'Редагувати модель' : 'Нова модель'">
        <div>
            <label>Виробник техніки *</label>
            <x-admin.select model="machinery_brand_id" placeholder="— Оберіть —" :live="false"
                :options="$brands->map(fn ($b) => ['value' => $b->id, 'label' => $b->name])->all()" />
            @error('machinery_brand_id') <span style="color:red">{{ $message }}</span> @enderror
        </div>
        <div>
            <label>Серія (необов'язково)</label>
            <x-admin.select model="machinery_series_id" placeholder="— Без серії —" :live="false"
                :options="$series->map(fn ($s) => ['value' => $s->id, 'label' => ($s->brand?->name ? $s->brand->name.' — ' : '').$s->name])->all()" />
            @error('machinery_series_id') <span style="color:red">{{ $message }}</span> @enderror
        </div>
        <div>
            <label>Тип техніки *</label>
            <x-admin.select model="machinery_type_id" placeholder="— Оберіть —" :live="false"
                :options="$types->map(fn ($t) => ['value' => $t->id, 'label' => $t->name])->all()" />
            @error('machinery_type_id') <span style="color:red">{{ $message }}</span> @enderror
        </div>
        <div class="is-full">
            <label>Назва моделі *</label>
            <input wire:model="name" type="text" style="width:100%" placeholder="8400, MX230, Steiger...">
            @error('name') <span style="color:red">{{ $message }}</span> @enderror
        </div>

        <x-slot:footer>
            <button wire:click="save" data-confirm="Ви дійсно хочете зберегти зміни?">Зберегти</button>
            <button wire:click="$set('showModal', false)">Скасувати</button>
        </x-slot:footer>
    </x-admin.modal>
    @endif
</div>
