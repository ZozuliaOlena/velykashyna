<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\ImportExport\ImportExport;
use App\Livewire\Admin\Attributes\AttributeIndex;
use App\Livewire\Admin\Brands\BrandIndex;
use App\Livewire\Admin\Categories\CategoryIndex;
use App\Livewire\Admin\Leads\LeadIndex;
use App\Livewire\Admin\Machinery\MachineryBrandIndex;
use App\Livewire\Admin\Machinery\MachineryModelIndex;
use App\Livewire\Admin\Machinery\MachineryPositionIndex;
use App\Livewire\Admin\Machinery\MachineryTypeIndex;
use App\Livewire\Admin\Products\ProductForm;
use App\Livewire\Admin\Products\ProductIndex;
use App\Livewire\Admin\ProductTypes\ProductTypeIndex;
use App\Livewire\Admin\Settings\SettingIndex;
use App\Livewire\Admin\Users\UserIndex;

Route::prefix('admin')
    ->middleware(['auth', 'verified', 'admin'])
    ->name('admin.')
    ->group(function () {
        Route::get('/', Dashboard::class)->name('dashboard');

        // Каталог
        Route::get('/products', ProductIndex::class)->name('products.index');
        Route::get('/products/create', ProductForm::class)->name('products.create');
        Route::get('/products/{id}/edit', ProductForm::class)->name('products.edit');

        Route::get('/categories', CategoryIndex::class)->name('categories.index');
        Route::get('/attributes', AttributeIndex::class)->name('attributes.index');
        Route::get('/brands', BrandIndex::class)->name('brands.index');
        Route::get('/product-types', ProductTypeIndex::class)->name('product-types.index');

        // Сумісність з технікою
        Route::get('/machinery-types', MachineryTypeIndex::class)->name('machinery-types.index');
        Route::get('/machinery-brands', MachineryBrandIndex::class)->name('machinery-brands.index');
        Route::get('/machinery-models', MachineryModelIndex::class)->name('machinery-models.index');
        Route::get('/machinery-positions', MachineryPositionIndex::class)->name('machinery-positions.index');

        // Заявки, користувачі, налаштування
        Route::get('/leads', LeadIndex::class)->name('leads.index');
        Route::get('/users', UserIndex::class)->name('users.index');
        Route::get('/settings', SettingIndex::class)->name('settings.index');
        Route::get('/import-export', ImportExport::class)->name('import-export.index');
    });
