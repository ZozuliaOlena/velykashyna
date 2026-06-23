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
                <td>
                    @if($brand->logo)
                        <img src="{{ $brand->logoUrl() }}" alt="{{ $brand->name }}"
                            style="height:38px; width:auto; object-fit:contain">
                    @else
                        <span style="color:#bbb">—</span>
                    @endif
                </td>
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
        <div style="background:#fff;padding:2rem;min-width:420px">
            <h2>{{ $editingId ? 'Редагувати бренд' : 'Новий бренд' }}</h2>

            <div>
                <label>Назва *</label><br>
                <input wire:model="name" type="text" style="width:100%">
                @error('name') <span style="color:red">{{ $message }}</span> @enderror
            </div>

            <div>
                <label>Країна</label><br>
                <input wire:model="country" type="text" style="width:100%">
            </div>

            <div style="margin-top:.5rem">
                <label>Логотип</label><br>

                {{-- прев'ю нового файлу або вже збереженого --}}
                @if($logo)
                    <div class="photo-thumb">
                        <img src="{{ $logo->temporaryUrl() }}" alt="">
                    </div>
                @elseif($currentLogo)
                    <div class="photo-thumb">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($currentLogo) }}" alt="">
                        <button type="button" class="photo-del" wire:click="deleteLogo({{ $editingId }})"
                            wire:confirm="Видалити логотип?">×</button>
                    </div>
                @endif

                <input wire:model="logo" type="file" accept="image/*">
                <div wire:loading wire:target="logo" style="color:#666">Завантаження…</div>
                @error('logo') <span style="color:red">{{ $message }}</span> @enderror
            </div>

            <div style="margin-top:.5rem">
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
