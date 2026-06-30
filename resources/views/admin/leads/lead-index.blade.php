<div>
    <div style="display:flex; justify-content:space-between; align-items:center">
        <h1>Заявки</h1>
        <button wire:click="openCreate">+ Додати заявку</button>
    </div>

    @if(session('success')) <p style="color:green">{{ session('success') }}</p> @endif

    <div class="lead-tabs">
        <button type="button" @class(['lead-tab', 'is-active' => $tab === 'active'])
            wire:click="setTab('active')">
            Активні <span class="lead-tab__count">{{ $activeCount }}</span>
        </button>
        <button type="button" @class(['lead-tab', 'is-active' => $tab === 'archive'])
            wire:click="setTab('archive')">
            Архів <span class="lead-tab__count">{{ $archiveCount }}</span>
        </button>
    </div>

    <div class="admin-filters">
        <input wire:model.live.debounce.300ms="search" placeholder="Пошук: ім'я, телефон...">
        <x-admin.select model="filterStatus" placeholder="— Усі статуси —"
            wire:key="status-filter-{{ $tab }}"
            :options="collect($tabStatuses)->map(fn ($label, $key) => ['value' => $key, 'label' => $label])->values()->all()" />
    </div>

    <div class="table-scroll">
    <table border="1" cellpadding="6" style="width:100%; border-collapse:collapse">
        <thead>
            <tr><th>#</th><th>Клієнт</th><th>Телефон</th><th>Спосіб</th><th>Місто</th><th>Доставка</th><th>Позицій</th><th>Статус</th><th>Дата</th><th>Дії</th></tr>
        </thead>
        <tbody>
            @forelse($leads as $lead)
            <tr wire:key="lead-{{ $lead->id }}" @class(['is-new-lead' => $lead->status === 'new'])>
                <td data-label="#">{{ $lead->id }}</td>
                <td data-label="Клієнт">{{ $lead->customer_name }}</td>
                <td data-label="Телефон">{{ $lead->phone }}</td>
                <td data-label="Спосіб">{{ $lead->contact_method ?? '—' }}</td>
                <td data-label="Місто">{{ $lead->city ?? '—' }}</td>
                <td data-label="Доставка">{{ trim(($lead->delivery_method ?? '') . ($lead->delivery_address ? ', ' . $lead->delivery_address : '')) ?: '—' }}</td>
                <td data-label="Позицій">{{ $lead->items_count }}</td>
                <td data-label="Статус">
                    <span class="lead-status lead-status--{{ $lead->status }}">{{ $statuses[$lead->status] ?? $lead->status }}</span>
                </td>
                <td data-label="Дата">{{ $lead->created_at?->format('d.m.Y H:i') }}</td>
                <td class="cell-actions">
                    <button class="icon-btn" wire:click="openView({{ $lead->id }})" title="Редагувати" aria-label="Редагувати"><x-icon name="edit"/></button>
                    <button class="icon-btn" wire:click="delete({{ $lead->id }})" data-confirm="Ви дійсно хочете видалити заявку?" title="Видалити" aria-label="Видалити"><x-icon name="trash"/></button>
                </td>
            </tr>
            @empty
            <tr><td colspan="10" style="text-align:center">Заявок не знайдено</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <div style="margin-top:1rem">{{ $leads->links('pagination.admin') }}</div>

    @if($showModal)
    <x-admin.modal :title="$editingId ? 'Заявка #'.$current?->id : 'Нова заявка'" :wide="true">
        <div>
            <label>Клієнт *</label>
            <input wire:model="customer_name" type="text" style="width:100%">
            @error('customer_name') <span style="color:red">{{ $message }}</span> @enderror
        </div>
        <div>
            <label>Телефон *</label>
            <input wire:model="phone" type="text" style="width:100%">
            @error('phone') <span style="color:red">{{ $message }}</span> @enderror
        </div>
        <div>
            <label>Спосіб зв'язку</label>
            <input wire:model="contact_method" type="text" style="width:100%">
        </div>
        <div>
            <label>Місто</label>
            <input wire:model="city" type="text" style="width:100%">
        </div>
        <div>
            <label>Спосіб доставки</label>
            <select wire:model="delivery_method" style="width:100%">
                <option value="">— не вказано —</option>
                @foreach($deliveryMethods as $m)
                    <option value="{{ $m }}">{{ $m }}</option>
                @endforeach
                @if($delivery_method && ! in_array($delivery_method, $deliveryMethods, true))
                    <option value="{{ $delivery_method }}">{{ $delivery_method }}</option>
                @endif
            </select>
        </div>
        <div>
            <label>Відділення / адреса</label>
            <input wire:model="delivery_address" type="text" style="width:100%" placeholder="№ відділення або адреса">
        </div>
        <div>
            <label>Спосіб оплати</label>
            <select wire:model="payment_method" style="width:100%">
                <option value="">— не вказано —</option>
                @foreach($paymentMethods as $m)
                    <option value="{{ $m }}">{{ $m }}</option>
                @endforeach
                @if($payment_method && ! in_array($payment_method, $paymentMethods, true))
                    <option value="{{ $payment_method }}">{{ $payment_method }}</option>
                @endif
            </select>
        </div>
        <div>
            <label>Статус *</label>
            <x-admin.select model="status" :live="false" :clearable="false"
                :options="collect($statuses)->map(fn ($label, $key) => ['value' => $key, 'label' => $label])->values()->all()" />
        </div>

        @if($current?->customer_comment)
        <div class="is-full">
            <label>Коментар клієнта</label>
            <textarea rows="2" style="width:100%" readonly>{{ $current->customer_comment }}</textarea>
        </div>
        @endif

        <div class="is-full">
            <label>Коментар менеджера</label>
            <textarea wire:model="manager_comment" rows="3" style="width:100%"></textarea>
        </div>

        <div class="is-full">
            <h3>Позиції заявки</h3>

            {{-- Пошук товару для додавання --}}
            <div class="lead-product-search">
                <input wire:model.live.debounce.300ms="productSearch" type="text"
                       placeholder="Додати товар: назва або артикул...">
                @if($productResults->isNotEmpty())
                <div class="lead-product-results">
                    @foreach($productResults as $p)
                    <button type="button" class="lead-product-results__item"
                            wire:key="pr-{{ $p->id }}" wire:click="addProduct({{ $p->id }})">
                        <span>{{ $p->name }}</span>
                        <small>{{ $p->sku ?? '—' }}</small>
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

            <div class="lead-items">
                <table border="1" cellpadding="6" style="width:100%; border-collapse:collapse">
                    <thead>
                        <tr>
                            <th>Товар</th><th>Артикул</th>
                            <th style="width:84px">К-ть</th>
                            <th style="width:120px">Ціна</th>
                            <th style="width:44px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $i => $item)
                        <tr wire:key="item-{{ $item['product_id'] }}">
                            <td>{{ $item['name'] }}</td>
                            <td>{{ $item['sku'] ?? '—' }}</td>
                            <td><input type="number" min="1" wire:model="items.{{ $i }}.qty"></td>
                            <td><input type="number" step="0.01" min="0" wire:model="items.{{ $i }}.price" placeholder="за запитом"></td>
                            <td>
                                <button type="button" wire:click="removeItem({{ $i }})"
                                        data-confirm="Ви дійсно хочете прибрати товар із заявки?">×</button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" style="text-align:center">Додайте товари через пошук вище</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @error('items.*.qty') <span style="color:red">{{ $message }}</span> @enderror
            @error('items.*.product_id') <span style="color:red">{{ $message }}</span> @enderror

            @php $total = collect($items)->sum(fn ($it) => (float) ($it['price'] ?? 0) * (int) ($it['qty'] ?? 0)); @endphp
            @if($total > 0)
            <div style="text-align:right; margin-top:.5rem; font-weight:700">
                Разом: {{ number_format($total, 2, '.', ' ') }} грн
            </div>
            @endif
        </div>

        <x-slot:footer>
            <button wire:click="save" data-confirm="Ви дійсно хочете зберегти зміни?">Зберегти</button>
            <button wire:click="$set('showModal', false)">Закрити</button>
        </x-slot:footer>
    </x-admin.modal>
    @endif
</div>
