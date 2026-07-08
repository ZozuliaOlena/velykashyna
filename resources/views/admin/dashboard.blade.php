<div>
    <h1>Панель керування</h1>

    <div class="admin-stats">
        <a class="admin-stat" href="{{ route('admin.products.index') }}">
            <span class="admin-stat__value">{{ $productsCount }}</span>
            <span class="admin-stat__label">Товарів</span>
        </a>
        <a class="admin-stat" href="{{ route('admin.categories.index') }}">
            <span class="admin-stat__value">{{ $categoriesCount }}</span>
            <span class="admin-stat__label">Категорій</span>
        </a>
        <a class="admin-stat" href="{{ route('admin.brands.index') }}">
            <span class="admin-stat__value">{{ $brandsCount }}</span>
            <span class="admin-stat__label">Брендів</span>
        </a>
        <a class="admin-stat admin-stat--accent" href="{{ route('admin.leads.index') }}">
            <span class="admin-stat__value">{{ $newLeadsCount }}</span>
            <span class="admin-stat__label">Нових заявок</span>
        </a>
    </div>

    <div class="dash-actions">
        <a class="dash-action" href="{{ route('admin.products.create') }}">
            <x-icon name="plus"/> <span>Додати товар</span>
        </a>
        <a class="dash-action" href="{{ route('admin.posts.create') }}">
            <x-icon name="file"/> <span>Нова стаття</span>
        </a>
        <a class="dash-action" href="{{ route('admin.leads.index') }}">
            <x-icon name="box"/> <span>Заявки</span>
        </a>
        <a class="dash-action" href="{{ route('admin.import-export.index') }}">
            <x-icon name="swap"/> <span>Імпорт / Експорт</span>
        </a>
    </div>

    <div class="dash-panel">
        <div class="dash-panel__head">
            <h2>Останні заявки</h2>
            <a href="{{ route('admin.leads.index') }}">Усі заявки →</a>
        </div>

        @if($recentLeads->isEmpty())
            <p class="dash-panel__empty">Заявок поки немає.</p>
        @else
        <div class="table-scroll">
        <table border="1" cellpadding="6" style="width:100%; border-collapse:collapse">
            <thead>
                <tr><th>#</th><th>Клієнт</th><th>Телефон</th><th>Позицій</th><th>Статус</th><th>Дата</th><th></th></tr>
            </thead>
            <tbody>
                @foreach($recentLeads as $lead)
                <tr @class(['is-new-lead' => $lead->status === 'new'])>
                    <td data-label="#">{{ $lead->id }}</td>
                    <td data-label="Клієнт">{{ $lead->customer_name }}</td>
                    <td data-label="Телефон">{{ $lead->phone }}</td>
                    <td data-label="Позицій">{{ $lead->items_count }}</td>
                    <td data-label="Статус">
                        <span class="lead-status lead-status--{{ $lead->status }}">{{ $leadStatuses[$lead->status] ?? $lead->status }}</span>
                    </td>
                    <td data-label="Дата">{{ $lead->created_at?->timezone(config('app.display_timezone'))->format('d.m.Y H:i') }}</td>
                    <td class="cell-actions">
                        <a class="icon-btn" href="{{ route('admin.leads.index') }}" title="Відкрити" aria-label="Відкрити"><x-icon name="eye"/></a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @endif
    </div>
</div>
