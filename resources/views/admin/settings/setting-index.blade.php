<div>
    <div style="display:flex; justify-content:space-between; align-items:center">
        <h1>Налаштування</h1>
        <button wire:click="openCreate">+ Додати параметр</button>
    </div>

    @if(session('success')) <p style="color:green">{{ session('success') }}</p> @endif

    <div class="admin-filters">
        <input wire:model.live.debounce.300ms="search" placeholder="Пошук по ключу...">
    </div>

    <table border="1" cellpadding="6" style="width:100%; border-collapse:collapse; margin-top:1rem">
        <thead>
            <tr><th>Ключ</th><th>Значення</th><th>Дії</th></tr>
        </thead>
        <tbody>
            @forelse($settings as $setting)
            <tr wire:key="setting-{{ $setting->key }}">
                <td>{{ $setting->key }}</td>
                <td style="word-break:break-all">{{ \Illuminate\Support\Str::limit($setting->value, 80) }}</td>
                <td>
                    <button wire:click="openEdit('{{ $setting->key }}')">Редагувати</button>
                    <button wire:click="delete('{{ $setting->key }}')" wire:confirm="Видалити параметр?">Видалити</button>
                </td>
            </tr>
            @empty
            <tr><td colspan="3" style="text-align:center">Параметрів немає</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:1rem">{{ $settings->links() }}</div>

    @if($showModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center">
        <div style="background:#fff;padding:2rem;min-width:460px">
            <h2>{{ $editingKey ? 'Редагувати параметр' : 'Новий параметр' }}</h2>

            <div>
                <label>Ключ *</label><br>
                <input wire:model="key" type="text" style="width:100%"
                    placeholder="gtm_container_id, merchant_feed_url..."
                    @if($editingKey) readonly @endif>
                @error('key') <span style="color:red">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Значення</label><br>
                <textarea wire:model="value" rows="3" style="width:100%"></textarea>
                @error('value') <span style="color:red">{{ $message }}</span> @enderror
            </div>

            <div style="margin-top:1rem">
                <button wire:click="save">Зберегти</button>
                <button wire:click="$set('showModal', false)">Скасувати</button>
            </div>
        </div>
    </div>
    @endif
</div>
