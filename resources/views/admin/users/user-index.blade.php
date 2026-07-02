<div>
    <div style="display:flex; justify-content:space-between; align-items:center">
        <h1>Користувачі</h1>
        <button wire:click="openCreate">+ Додати користувача</button>
    </div>


    <div class="admin-filters">
        <input wire:model.live.debounce.300ms="search" placeholder="Пошук: ім'я, email...">
    </div>

    <div class="table-scroll">
    <table border="1" cellpadding="6" style="width:100%; border-collapse:collapse">
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
                    <button class="icon-btn" wire:click="openEdit({{ $user->id }})" title="Редагувати" aria-label="Редагувати"><x-icon name="edit"/></button>
                    <button class="icon-btn" wire:click="delete({{ $user->id }})" data-confirm="Ви дійсно хочете видалити користувача?" title="Видалити" aria-label="Видалити"><x-icon name="trash"/></button>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center">Нічого не знайдено</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <div style="margin-top:1rem">{{ $users->links('pagination.admin') }}</div>

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
            <x-admin.select model="role" :live="false" :clearable="false"
                :options="[
                    ['value' => 'manager', 'label' => 'Менеджер'],
                    ['value' => 'admin', 'label' => 'Адміністратор'],
                ]" />
            @error('role') <span style="color:red">{{ $message }}</span> @enderror
        </div>
        <div>
            <label>Пароль {{ $editingId ? '(залиште порожнім, щоб не міняти)' : '*' }}</label>
            <input wire:model="password" type="password" style="width:100%">
            @error('password') <span style="color:red">{{ $message }}</span> @enderror
        </div>

        <x-slot:footer>
            <button wire:click="save" data-confirm="Ви дійсно хочете зберегти зміни?">Зберегти</button>
            <button wire:click="$set('showModal', false)">Скасувати</button>
        </x-slot:footer>
    </x-admin.modal>
    @endif
</div>
