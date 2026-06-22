<div>
    <div style="display:flex; justify-content:space-between; align-items:center">
        <h1>Типи товарів</h1>
        <button wire:click="openCreate">+ Додати тип</button>
    </div>

    @if(session('success')) <p style="color:green">{{ session('success') }}</p> @endif
    @if(session('error')) <p style="color:red">{{ session('error') }}</p> @endif

    <input wire:model.live.debounce.300ms="search" placeholder="Пошук...">

    <table border="1" cellpadding="6" style="width:100%; border-collapse:collapse; margin-top:1rem">
        <thead>
            <tr><th>Код</th><th>Назва</th><th>Товарів</th><th>Дії</th></tr>
        </thead>
        <tbody>
            @forelse($types as $type)
            <tr wire:key="ptype-{{ $type->id }}">
                <td>{{ $type->code }}</td>
                <td>{{ $type->name }}</td>
                <td>{{ $type->products_count }}</td>
                <td>
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
    <div style="position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center">
        <div style="background:#fff;padding:2rem;min-width:400px">
            <h2>{{ $editingId ? 'Редагувати тип' : 'Новий тип' }}</h2>
            <div>
                <label>Код *</label><br>
                <input wire:model="code" type="text" placeholder="tire, tube, disk...">
                @error('code') <span style="color:red">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Назва *</label><br>
                <input wire:model="name" type="text">
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
