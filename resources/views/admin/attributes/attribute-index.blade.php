<div>
    <div style="display:flex; justify-content:space-between; align-items:center">
        <h1>Характеристики</h1>
        <button wire:click="openCreate">+ Додати характеристику</button>
    </div>

    @if(session('success')) <p style="color:green">{{ session('success') }}</p> @endif
    @if(session('error')) <p style="color:red">{{ session('error') }}</p> @endif

    <div class="admin-filters">
        <input wire:model.live.debounce.300ms="search" placeholder="Пошук: назва, код...">
        <x-admin.select model="filterType" placeholder="— Усі типи товарів —"
            :options="$productTypes->map(fn ($pt) => ['value' => $pt->id, 'label' => $pt->name])->all()" />
    </div>

    <div class="table-scroll">
    <table border="1" cellpadding="6" style="width:100%; border-collapse:collapse">
        <thead>
            <tr><th>Назва</th><th>Код</th><th>Тип товару</th><th>Тип даних</th><th>Од.</th><th>Фільтр</th><th>Варіантів</th><th>Дії</th></tr>
        </thead>
        <tbody>
            @forelse($attrs as $attr)
            <tr wire:key="attr-{{ $attr->id }}">
                <td data-label="Назва">{{ $attr->name }}</td>
                <td data-label="Код">{{ $attr->code }}</td>
                <td data-label="Тип товару">{{ $attr->productType?->name ?? 'Спільна' }}</td>
                <td data-label="Тип даних">{{ $attr->data_type }}</td>
                <td data-label="Од.">{{ $attr->unit ?? '—' }}</td>
                <td data-label="Фільтр">{{ $attr->is_filterable ? 'Так' : 'Ні' }}</td>
                <td data-label="Варіантів">{{ $attr->data_type === 'select' ? $attr->options_count : '—' }}</td>
                <td class="cell-actions">
                    <button class="icon-btn" wire:click="openEdit({{ $attr->id }})" title="Редагувати" aria-label="Редагувати"><x-icon name="edit"/></button>
                    <button class="icon-btn" wire:click="delete({{ $attr->id }})" data-confirm="Ви дійсно хочете видалити характеристику?" title="Видалити" aria-label="Видалити"><x-icon name="trash"/></button>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center">Нічого не знайдено</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <div style="margin-top:1rem">{{ $attrs->links('pagination.admin') }}</div>

    @if($showModal)
    <x-admin.modal :title="$editingId ? 'Редагувати характеристику' : 'Нова характеристика'" :wide="true">
        <div>
            <label>Тип товару</label>
            <x-admin.select model="product_type_id" placeholder="— Спільна для всіх типів —" :live="false"
                :options="$productTypes->map(fn ($pt) => ['value' => $pt->id, 'label' => $pt->name])->all()" />
            @error('product_type_id') <span style="color:red">{{ $message }}</span> @enderror
        </div>

        <div>
            <label>Код *</label>
            <input wire:model="code" type="text" placeholder="width, season..." style="width:100%">
            @error('code') <span style="color:red">{{ $message }}</span> @enderror
        </div>

        <div>
            <label>Назва *</label>
            <input wire:model="name" type="text" style="width:100%">
            @error('name') <span style="color:red">{{ $message }}</span> @enderror
        </div>

        <div>
            <label>Тип даних *</label>
            <x-admin.select model="data_type" :clearable="false"
                :options="[
                    ['value' => 'text', 'label' => 'Текст'],
                    ['value' => 'number', 'label' => 'Число'],
                    ['value' => 'select', 'label' => 'Список (варіанти)'],
                    ['value' => 'boolean', 'label' => 'Так/Ні'],
                ]" />
            @error('data_type') <span style="color:red">{{ $message }}</span> @enderror
        </div>

        <div>
            <label>Одиниця</label>
            <input wire:model="unit" type="text" placeholder="мм, кг..." style="width:100%">
        </div>

        <div>
            <label>Сортування</label>
            <input wire:model="sort_order" type="number" style="width:100%">
        </div>

        <div class="is-full" style="display:flex; align-items:center">
            <label style="margin:0"><input wire:model="is_filterable" type="checkbox"> Використовувати у фільтрах</label>
        </div>

        {{-- Варіанти для типу select --}}
        @if($data_type === 'select')
            <fieldset class="is-full">
                <legend>Варіанти значень</legend>
                @if(! $editingId)
                    <p style="color:#666">Спочатку збережіть характеристику, потім додайте варіанти.</p>
                @else
                    <ul>
                        @foreach($options as $opt)
                            <li wire:key="opt-{{ $opt->id }}">
                                {{ $opt->value }}
                                <button wire:click="deleteOption({{ $opt->id }})" style="color:red">×</button>
                            </li>
                        @endforeach
                    </ul>
                    <div style="display:flex; gap:.5rem">
                        <input wire:model="newOption" type="text" placeholder="Новий варіант" wire:keydown.enter="addOption">
                        <button wire:click="addOption">Додати варіант</button>
                    </div>
                    @error('newOption') <span style="color:red">{{ $message }}</span> @enderror
                @endif
            </fieldset>
        @endif

        <x-slot:footer>
            <button wire:click="save" data-confirm="Ви дійсно хочете зберегти зміни?">Зберегти</button>
            <button wire:click="$set('showModal', false)">Закрити</button>
        </x-slot:footer>
    </x-admin.modal>
    @endif
</div>
