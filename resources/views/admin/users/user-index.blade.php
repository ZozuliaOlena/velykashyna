<div>
    <div style="display:flex; justify-content:space-between; align-items:center">
        <h1>Користувачі</h1>
        <button wire:click="openCreate">+ Додати користувача</button>
    </div>

    @if(session('success')) <p style="color:green">{{ session('success') }}</p> @endif
    @if(session('error')) <p style="color:red">{{ session('error') }}</p> @endif

    <input wire:model.live.debounce.300ms="search" placeholder="Пошук: ім'я, email...">

    <table border="1" cellpadding="6" style="width:100%; border-collapse:collapse; margin-top:1rem">
        <thead>
            <tr><th>Ім'я</th><th>Email</th><th>Роль</th><th>Дії</th></tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr wire:key="user-{{ $user->id }}">
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->role === 'admin' ? 'Адміністратор' : 'Менеджер' }}</td>
                <td>
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
    <div style="position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center">
        <div style="background:#fff;padding:2rem;min-width:420px">
            <h2>{{ $editingId ? 'Редагувати користувача' : 'Новий користувач' }}</h2>

            <div>
                <label>Ім'я *</label><br>
                <input wire:model="name" type="text" style="width:100%">
                @error('name') <span style="color:red">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Email *</label><br>
                <input wire:model="email" type="email" style="width:100%">
                @error('email') <span style="color:red">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Роль *</label><br>
                <select wire:model="role">
                    <option value="manager">Менеджер</option>
                    <option value="admin">Адміністратор</option>
                </select>
                @error('role') <span style="color:red">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Пароль {{ $editingId ? '(залиште порожнім, щоб не міняти)' : '*' }}</label><br>
                <input wire:model="password" type="password" style="width:100%">
                @error('password') <span style="color:red">{{ $message }}</span> @enderror
            </div>

            <div style="margin-top:1rem">
                <button wire:click="save">Зберегти</button>
                <button wire:click="$set('showModal', false)">Скасувати</button>
            </div>
        </div>
    </div>
    @endif
</div>
