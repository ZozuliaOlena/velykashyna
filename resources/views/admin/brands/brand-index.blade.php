<div>
    <div style="display:flex; justify-content:space-between; align-items:center">
        <h1>Бренди</h1>
        <button wire:click="openCreate">+ Додати бренд</button>
    </div>

    @if(session('success'))
        <p style="color:green">{{ session('success') }}</p>
    @endif

    <input wire:model.live="search" placeholder="Пошук по назві...">

    <table border="1" cellpadding="6" style="width:100%; margin-top:1rem">
        <thead>
            <tr>
                <th>Назва</th>
                <th>Країна</th>
                <th>Активний</th>
                <th>Дії</th>
            </tr>
        </thead>
        <tbody>
            @foreach($brands as $brand)
            <tr>
                <td>{{ $brand->name }}</td>
                <td>{{ $brand->country ?? '—' }}</td>
                <td>
                    <button wire:click="toggleActive({{ $brand->id }})">
                        {{ $brand->is_active ? 'Так' : 'Ні' }}
                    </button>
                </td>
                <td>
                    <button wire:click="openEdit({{ $brand->id }})">Редагувати</button>
                    <button wire:click="delete({{ $brand->id }})"
                        wire:confirm="Видалити бренд?">Видалити</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $brands->links() }}

    {{-- Модальне вікно --}}
    @if($showModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center">
        <div style="background:#fff;padding:2rem;min-width:400px">
            <h2>{{ $editingId ? 'Редагувати бренд' : 'Новий бренд' }}</h2>

            <div>
                <label>Назва *</label>
                <input wire:model="name" type="text">
                @error('name') <span style="color:red">{{ $message }}</span> @enderror
            </div>

            <div>
                <label>Країна</label>
                <input wire:model="country" type="text">
            </div>

            <div>
                <label>
                    <input wire:model="is_active" type="checkbox"> Активний
                </label>
            </div>

            <div style="margin-top:1rem">
                <button wire:click="save">Зберегти</button>
                <button wire:click="$set('showModal', false)">Скасувати</button>
            </div>
        </div>
    </div>
    @endif
</div>
