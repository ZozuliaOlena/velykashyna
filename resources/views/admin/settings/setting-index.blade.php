<div>
    <div style="display:flex; justify-content:space-between; align-items:center">
        <h1>Налаштування</h1>
        <button wire:click="openCreate">+ Додати параметр</button>
    </div>


    <div class="admin-filters">
        <input wire:model.live.debounce.300ms="search" placeholder="Пошук по ключу...">
    </div>

    <div class="table-scroll">
    <table border="1" cellpadding="6" style="width:100%; border-collapse:collapse">
        <thead>
            <tr><th>Ключ</th><th>Значення</th><th>Дії</th></tr>
        </thead>
        <tbody>
            @forelse($settings as $setting)
            <tr wire:key="setting-{{ $setting->key }}">
                <td data-label="Ключ">{{ $setting->key }}</td>
                <td data-label="Значення" style="word-break:break-all">{{ \Illuminate\Support\Str::limit($setting->value, 80) }}</td>
                <td class="cell-actions">
                    <button class="icon-btn" wire:click="openEdit('{{ $setting->key }}')" title="Редагувати" aria-label="Редагувати"><x-icon name="edit"/></button>
                    <button class="icon-btn" wire:click="delete('{{ $setting->key }}')" data-confirm="Ви дійсно хочете видалити параметр?" title="Видалити" aria-label="Видалити"><x-icon name="trash"/></button>
                </td>
            </tr>
            @empty
            <tr><td colspan="3" style="text-align:center">Параметрів немає</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <div style="margin-top:1rem">{{ $settings->links('pagination.admin') }}</div>

    @if($showModal)
    <x-admin.modal :title="$editingKey ? 'Редагувати параметр' : 'Новий параметр'">
        <div class="is-full">
            <label>Ключ *</label>
            <input wire:model="key" type="text" style="width:100%"
                placeholder="gtm_container_id, merchant_feed_url..."
                @if($editingKey) readonly @endif>
            @error('key') <span style="color:red">{{ $message }}</span> @enderror
        </div>
        <div class="is-full">
            <label>Значення</label>
            <textarea wire:model="value" rows="3" style="width:100%"></textarea>
            @error('value') <span style="color:red">{{ $message }}</span> @enderror
        </div>

        <x-slot:footer>
            <button wire:click="save" data-confirm="Ви дійсно хочете зберегти зміни?">Зберегти</button>
            <button wire:click="$set('showModal', false)">Скасувати</button>
        </x-slot:footer>
    </x-admin.modal>
    @endif
</div>
