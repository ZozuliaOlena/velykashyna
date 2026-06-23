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
</div>
