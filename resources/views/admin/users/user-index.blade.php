<div>
    <div style="display:flex; justify-content:space-between; align-items:center">
        <h1>Користувачі</h1>
        <button wire:click="openCreate">+ Додати користувача</button>
    </div>

    @if(session('success')) <p style="color:green">{{ session('success') }}</p> @endif
    @if(session('error')) <p style="color:red">{{ session('error') }}</p> @endif

    <div class="admin-filters">
        <input wire:model.live.debounce.300ms="search" placeholder="Пошук: ім'я, email...">
    </div>

    <table border="1" cellpadding="6" style="width:100%; border-collapse:collapse; margin-top:1rem">
        <thead>
            <tr><th>Ім'я</th><th>Email</th><th>Роль</th><th>Дії</th></tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr wire:key="user-{{ $user->id }}">
                <td data-label="Ім'я">{{ $user->name }}</td>
                <td data-label="Email">{{ $user->email }}</td>
                <td data-label="Роль">{{ $user->role === 'admin' ? 'Адміністратор' : 'Менеджер' }}</td>
                <td class="cell-actions">
                    <button wire:click="openEdit({{ $user->id }})">Редагувати</button>
                    <button wire:click="delete({{ $user->id }})" wire:confirm="Видалити користувача?">Видалити</button>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center">Нічого не знайдено</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:1rem">{{ $users->links() }}</div>

    @if($showModal)
    <x-admin.modal :title="$editingId ? 'Редагувати користувача' : 'Новий користувач'">
        <div>
            <label>Ім'я *</label>
            <input wire:model="name" type="text" style="width:100%">
            @error('name') <span style="color:red">{{ $message }}</span> @enderror
        </div>
        <div>
            <label>Email *</label>
            <input wire:model="email" type="email" style="width:100%">
            @error('email') <span style="color:red">{{ $message }}</span> @enderror
        </div>
        <div>
            <label>Роль *</label>
            <select wire:model="role" style="width:100%">
                <option value="manager">Менеджер</option>
                <option value="admin">Адміністратор</option>
            </select>
            @error('role') <span style="color:red">{{ $message }}</span> @enderror
        </div>
        <div>
            <label>Пароль {{ $editingId ? '(залиште порожнім, щоб не міняти)' : '*' }}</label>
            <input wire:model="password" type="password" style="width:100%">
            @error('password') <span style="color:red">{{ $message }}</span> @enderror
        </div>

        <x-slot:footer>
            <button wire:click="save">Зберегти</button>
            <button wire:click="$set('showModal', false)">Скасувати</button>
        </x-slot:footer>
    </x-admin.modal>
    @endif
</div>
