<div>
    <div style="display:flex; justify-content:space-between; align-items:center">
        <h1>Характеристики</h1>
        <button wire:click="openCreate">+ Додати характеристику</button>
    </div>

    @if(session('success')) <p style="color:green">{{ session('success') }}</p> @endif
    @if(session('error')) <p style="color:red">{{ session('error') }}</p> @endif

    <div class="admin-filters">
        <input wire:model.live.debounce.300ms="search" placeholder="Пошук: назва, код...">
        <select wire:model.live="filterType">
            <option value="">— Усі типи товарів —</option>
            @foreach($productTypes as $pt)<option value="{{ $pt->id }}">{{ $pt->name }}</option>@endforeach
        </select>
    </div>

    <table border="1" cellpadding="6" style="width:100%; border-collapse:collapse">
        <thead>
            <tr><th>Назва</th><th>Код</th><th>Тип товару</th><th>Тип даних</th><th>Од.</th><th>Фільтр</th><th>Варіантів</th><th>Дії</th></tr>
        </thead>
        <tbody>
            @forelse($attrs as $attr)
            <tr wire:key="attr-{{ $attr->id }}">
                <td>{{ $attr->name }}</td>
                <td>{{ $attr->code }}</td>
                <td>{{ $attr->productType?->name ?? 'Спільна' }}</td>
                <td>{{ $attr->data_type }}</td>
                <td>{{ $attr->unit ?? '—' }}</td>
                <td>{{ $attr->is_filterable ? 'Так' : 'Ні' }}</td>
                <td>{{ $attr->data_type === 'select' ? $attr->options_count : '—' }}</td>
                <td>
                    <button wire:click="openEdit({{ $attr->id }})">Редагувати</button>
                    <button wire:click="delete({{ $attr->id }})" wire:confirm="Видалити характеристику?">Видалити</button>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center">Нічого не знайдено</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:1rem">{{ $attrs->links() }}</div>

    @if($showModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;overflow:auto">
        <div style="background:#fff;padding:2rem;min-width:460px;max-height:90vh;overflow:auto">
            <h2>{{ $editingId ? 'Редагувати характеристику' : 'Нова характеристика' }}</h2>

            <div>
                <label>Тип товару</label><br>
                <select wire:model="product_type_id" style="width:100%">
                    <option value="">— Спільна для всіх типів —</option>
                    @foreach($productTypes as $pt)<option value="{{ $pt->id }}">{{ $pt->name }}</option>@endforeach
                </select>
                @error('product_type_id') <span style="color:red">{{ $message }}</span> @enderror
            </div>

            <div>
                <label>Код *</label><br>
                <input wire:model="code" type="text" placeholder="width, season...">
                @error('code') <span style="color:red">{{ $message }}</span> @enderror
            </div>

            <div>
                <label>Назва *</label><br>
                <input wire:model="name" type="text" style="width:100%">
                @error('name') <span style="color:red">{{ $message }}</span> @enderror
            </div>

            <div style="display:flex; gap:.5rem; flex-wrap:wrap">
                <div>
                    <label>Тип даних *</label><br>
                    <select wire:model.live="data_type">
                        <option value="text">Текст</option>
                        <option value="number">Число</option>
                        <option value="select">Список (варіанти)</option>
                        <option value="boolean">Так/Ні</option>
                    </select>
                    @error('data_type') <span style="color:red">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label>Одиниця</label><br>
                    <input wire:model="unit" type="text" style="width:100px" placeholder="мм, кг...">
                </div>
                <div>
                    <label>Сортування</label><br>
                    <input wire:model="sort_order" type="number" style="width:90px">
                </div>
            </div>

            <div>
                <label><input wire:model="is_filterable" type="checkbox"> Використовувати у фільтрах</label>
            </div>

            {{-- Варіанти для типу select --}}
            @if($data_type === 'select')
                <fieldset style="margin-top:1rem">
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

            <div style="margin-top:1rem">
                <button wire:click="save">Зберегти</button>
                <button wire:click="$set('showModal', false)">Закрити</button>
            </div>
        </div>
    </div>
    @endif
</div>
