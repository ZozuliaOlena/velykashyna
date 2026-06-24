{{--
    Спільний шаблон для довідників з одним полем "name"
    (типи техніки, виробники техніки, позиції).
    Очікує: $items, $title, $addLabel, опц. $countKey + $countLabel.
--}}
@php($countKey = $countKey ?? null)
<div>
    <div style="display:flex; justify-content:space-between; align-items:center">
        <h1>{{ $title }}</h1>
        <button wire:click="openCreate">{{ $addLabel }}</button>
    </div>

    @if(session('success')) <p style="color:green">{{ session('success') }}</p> @endif
    @if(session('error')) <p style="color:red">{{ session('error') }}</p> @endif

    <div class="admin-filters">
        <input wire:model.live.debounce.300ms="search" placeholder="Пошук...">
    </div>

    <table border="1" cellpadding="6" style="width:100%; border-collapse:collapse; margin-top:1rem">
        <thead>
            <tr>
                <th>Назва</th>
                @if($countKey) <th>{{ $countLabel ?? 'Використання' }}</th> @endif
                <th>Дії</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
            <tr wire:key="item-{{ $item->id }}">
                <td>{{ $item->name }}</td>
                @if($countKey) <td>{{ $item->{$countKey} }}</td> @endif
                <td>
                    <button wire:click="openEdit({{ $item->id }})">Редагувати</button>
                    <button wire:click="delete({{ $item->id }})" wire:confirm="Видалити запис?">Видалити</button>
                </td>
            </tr>
            @empty
            <tr><td colspan="{{ $countKey ? 3 : 2 }}" style="text-align:center">Нічого не знайдено</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:1rem">{{ $items->links() }}</div>

    @if($showModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center">
        <div style="background:#fff;padding:2rem;min-width:400px">
            <h2>{{ $editingId ? 'Редагувати' : 'Новий запис' }}</h2>
            <div>
                <label>Назва *</label><br>
                <input wire:model="name" type="text" style="width:100%">
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
