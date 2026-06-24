<div>
    <div style="display:flex; justify-content:space-between; align-items:center">
        <h1>Категорії</h1>
        <button wire:click="openCreate">+ Додати категорію</button>
    </div>

    @if(session('success')) <p style="color:green">{{ session('success') }}</p> @endif
    @if(session('error')) <p style="color:red">{{ session('error') }}</p> @endif

    <div class="admin-filters">
        <input wire:model.live.debounce.300ms="search" placeholder="Пошук по назві...">
    </div>

    <table border="1" cellpadding="6" style="width:100%; border-collapse:collapse; margin-top:1rem">
        <thead>
            <tr><th>Назва</th><th>Рівень</th><th>Батько</th><th>Підкат.</th><th>Товарів</th><th>Активна</th><th>Дії</th></tr>
        </thead>
        <tbody>
            @forelse($categories as $cat)
            <tr wire:key="cat-{{ $cat->id }}">
                <td>{{ str_repeat('— ', max(0, $cat->level - 1)) }}{{ $cat->name }}</td>
                <td>{{ $cat->level }}</td>
                <td>{{ $cat->parent?->name ?? '—' }}</td>
                <td>{{ $cat->children_count }}</td>
                <td>{{ $cat->products_count }}</td>
                <td>
                    <button wire:click="toggleActive({{ $cat->id }})">{{ $cat->is_active ? 'Так' : 'Ні' }}</button>
                </td>
                <td>
                    <button wire:click="openEdit({{ $cat->id }})">Редагувати</button>
                    <button wire:click="delete({{ $cat->id }})" wire:confirm="Видалити категорію?">Видалити</button>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center">Нічого не знайдено</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:1rem">{{ $categories->links() }}</div>

    @if($showModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;overflow:auto">
        <div style="background:#fff;padding:2rem;min-width:460px;max-height:90vh;overflow:auto">
            <h2>{{ $editingId ? 'Редагувати категорію' : 'Нова категорія' }}</h2>

            <div>
                <label>Батьківська категорія</label><br>
                <select wire:model="parent_id" style="width:100%">
                    <option value="">— Коренева (рівень 1) —</option>
                    @foreach($parents as $p)
                        @if($p->id !== $editingId)
                            <option value="{{ $p->id }}">{{ str_repeat('— ', max(0, $p->level - 1)) }}{{ $p->name }}</option>
                        @endif
                    @endforeach
                </select>
                @error('parent_id') <span style="color:red">{{ $message }}</span> @enderror
            </div>

            <div>
                <label>Назва *</label><br>
                <input wire:model="name" type="text" style="width:100%">
                @error('name') <span style="color:red">{{ $message }}</span> @enderror
            </div>

            <div>
                <label>Порядок сортування</label><br>
                <input wire:model="sort_order" type="number" style="width:120px">
            </div>

            <div>
                <label><input wire:model="is_active" type="checkbox"> Активна</label>
            </div>

            <fieldset style="margin-top:1rem">
                <legend>SEO</legend>
                <div><label>Title</label><br><input wire:model="seo_title" type="text" style="width:100%"></div>
                <div><label>Description</label><br><textarea wire:model="seo_description" rows="2" style="width:100%"></textarea></div>
                <div><label>H1</label><br><input wire:model="seo_h1" type="text" style="width:100%"></div>
            </fieldset>

            <div style="margin-top:1rem">
                <button wire:click="save">Зберегти</button>
                <button wire:click="$set('showModal', false)">Скасувати</button>
            </div>
        </div>
    </div>
    @endif
</div>
