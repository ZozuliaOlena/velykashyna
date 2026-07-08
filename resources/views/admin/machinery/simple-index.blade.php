@php($countKey = $countKey ?? null)
@php($withIcon = $withIcon ?? false)
<div>
    <div style="display:flex; justify-content:space-between; align-items:center">
        <h1>{{ $title }}</h1>
        <button wire:click="openCreate">{{ $addLabel }}</button>
    </div>


    <div class="admin-filters">
        <input wire:model.live.debounce.300ms="search" placeholder="Пошук...">
    </div>

    <div class="table-scroll">
    <table border="1" cellpadding="6" style="width:100%; border-collapse:collapse">
        <thead>
            <tr>
                @if($withIcon) <th>Іконка</th> @endif
                <th>Назва</th>
                @if($countKey) <th>{{ $countLabel ?? 'Використання' }}</th> @endif
                <th>Дії</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
            <tr wire:key="item-{{ $item->id }}">
                @if($withIcon)
                <td data-label="Іконка">
                    @if($item->iconUrl())
                        <img src="{{ $item->iconUrl() }}" alt="" class="mtype-icon">
                    @else
                        <span style="color:#bbb">—</span>
                    @endif
                </td>
                @endif
                <td data-label="Назва">{{ $item->name }}</td>
                @if($countKey) <td data-label="{{ $countLabel ?? 'Використання' }}">{{ $item->{$countKey} }}</td> @endif
                <td class="cell-actions">
                    <button class="icon-btn" wire:click="openEdit({{ $item->id }})" title="Редагувати" aria-label="Редагувати"><x-icon name="edit"/></button>
                    <button class="icon-btn" wire:click="delete({{ $item->id }})" data-confirm="Ви дійсно хочете видалити запис?" title="Видалити" aria-label="Видалити"><x-icon name="trash"/></button>
                </td>
            </tr>
            @empty
            <tr><td colspan="{{ ($countKey ? 3 : 2) + ($withIcon ? 1 : 0) }}" style="text-align:center">Нічого не знайдено</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <div style="margin-top:1rem">{{ $items->links('pagination.admin') }}</div>

    @if($showModal)
    <x-admin.modal :title="$editingId ? 'Редагувати' : 'Новий запис'">
        <div class="is-full">
            <label>Назва *</label>
            <input wire:model="name" type="text" style="width:100%">
            @error('name') <span style="color:red">{{ $message }}</span> @enderror
        </div>

        @if($withIcon)
        <div class="is-full">
            <label>SVG-іконка</label><br>
            @if($icon)
                <div class="photo-thumb">
                    <img src="{{ $icon->temporaryUrl() }}" alt="">
                </div>
            @elseif($currentIcon)
                <div class="photo-thumb">
                    <img src="/storage/{{ ltrim($currentIcon, '/') }}" alt="">
                    <button type="button" class="photo-del" wire:click="deleteIcon({{ $editingId }})"
                        data-confirm="Ви дійсно хочете видалити іконку?">×</button>
                </div>
            @endif
            <input wire:model="icon" type="file" accept=".svg,image/svg+xml">
            <div wire:loading wire:target="icon" style="color:#666">Завантаження…</div>
            @error('icon') <span style="color:red">{{ $message }}</span> @enderror
            <small style="color:#666">Лише SVG, до 1 МБ.</small>
        </div>
        @endif

        <x-slot:footer>
            <button wire:click="save" data-confirm="Ви дійсно хочете зберегти зміни?">Зберегти</button>
            <button wire:click="$set('showModal', false)">Скасувати</button>
        </x-slot:footer>
    </x-admin.modal>
    @endif
</div>
