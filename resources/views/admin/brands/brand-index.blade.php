<div>
    <div style="display:flex; justify-content:space-between; align-items:center">
        <h1>Бренди</h1>
        <button wire:click="openCreate">+ Додати бренд</button>
    </div>

    <div class="admin-filters">
        <input wire:model.live="search" placeholder="Пошук по назві...">
    </div>

    <div class="table-scroll">
    <table border="1" cellpadding="6" style="width:100%">
        <thead>
            <tr>
                <th>Лого</th>
                <th>Назва</th>
                <th>Країна</th>
                <th>Активний</th>
                <th>Дії</th>
            </tr>
        </thead>
        <tbody>
            @foreach($brands as $brand)
            <tr wire:key="brand-{{ $brand->id }}">
                <td data-label="Лого">
                    @if($brand->logo)
                        <img src="{{ $brand->logoUrl() }}" alt="{{ $brand->name }}"
                            style="height:38px; width:auto; object-fit:contain">
                    @else
                        <span style="color:#bbb">—</span>
                    @endif
                </td>
                <td data-label="Назва">{{ $brand->name }}</td>
                <td data-label="Країна">{{ $brand->country ?? '—' }}</td>
                <td data-label="Активний">
                    <button wire:click="toggleActive({{ $brand->id }})">
                        {{ $brand->is_active ? 'Так' : 'Ні' }}
                    </button>
                </td>
                <td class="cell-actions">
                    <button class="icon-btn" wire:click="openEdit({{ $brand->id }})" title="Редагувати" aria-label="Редагувати"><x-icon name="edit"/></button>
                    <button class="icon-btn" wire:click="delete({{ $brand->id }})"
                        data-confirm="Ви дійсно хочете видалити бренд?" title="Видалити" aria-label="Видалити"><x-icon name="trash"/></button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>

    {{ $brands->links('pagination.admin') }}

    {{-- Модальне вікно --}}
    @if($showModal)
    <x-admin.modal :title="$editingId ? 'Редагувати бренд' : 'Новий бренд'">
        <div>
            <label>Назва *</label>
            <input wire:model="name" type="text" style="width:100%">
            @error('name') <span style="color:red">{{ $message }}</span> @enderror
        </div>

        <div>
            <label>Країна</label>
            <input wire:model="country" type="text" style="width:100%">
        </div>

        <div class="is-full">
            <label>Логотип</label><br>

            {{-- прев'ю нового файлу або вже збереженого --}}
            @if($logo)
                <div class="photo-thumb">
                    <img src="{{ $logo->temporaryUrl() }}" alt="">
                </div>
            @elseif($currentLogo)
                <div class="photo-thumb">
                    <img src="/storage/{{ ltrim($currentLogo, '/') }}" alt="">
                    <button type="button" class="photo-del" wire:click="deleteLogo({{ $editingId }})"
                        data-confirm="Ви дійсно хочете видалити логотип?">×</button>
                </div>
            @endif

            <input wire:model="logo" type="file" accept="image/*,.svg,image/svg+xml">
            <div wire:loading wire:target="logo" style="color:#666">Завантаження…</div>
            <small style="color:#666; display:block">PNG, JPG, WEBP або SVG, до 5 МБ.</small>
            @error('logo') <span style="color:red">{{ $message }}</span> @enderror
        </div>

        <div class="is-full" style="display:flex; align-items:center">
            <label style="margin:0"><input wire:model="is_active" type="checkbox"> Активний</label>
        </div>

        <x-slot:footer>
            <button wire:click="save" data-confirm="Ви дійсно хочете зберегти зміни?">Зберегти</button>
            <button wire:click="$set('showModal', false)">Скасувати</button>
        </x-slot:footer>
    </x-admin.modal>
    @endif
</div>
