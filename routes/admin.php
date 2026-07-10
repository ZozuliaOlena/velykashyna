<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\EmailOtpController;
use App\Http\Controllers\Admin\PostImageController;
use App\Http\Controllers\Admin\ProductPdfController;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\FieldPhotos\FieldPhotoBrowse;
use App\Livewire\Admin\ImportExport\ImportExport;
use App\Livewire\Admin\Attributes\AttributeIndex;
use App\Livewire\Admin\Brands\BrandIndex;
use App\Livewire\Admin\Categories\CategoryIndex;
use App\Livewire\Admin\Leads\LeadIndex;
use App\Livewire\Admin\Machinery\MachineryBrandIndex;
use App\Livewire\Admin\Machinery\MachineryModelIndex;
use App\Livewire\Admin\Machinery\MachineryPositionIndex;
use App\Livewire\Admin\Machinery\MachinerySeriesIndex;
use App\Livewire\Admin\Machinery\MachineryTypeIndex;
use App\Livewire\Admin\Products\ProductForm;
use App\Livewire\Admin\Products\ProductIndex;
use App\Livewire\Admin\Posts\PostForm;
use App\Livewire\Admin\Posts\PostIndex;
use App\Livewire\Admin\ProductTypes\ProductTypeIndex;
use App\Livewire\Admin\Security\SecurityPage;
use App\Livewire\Admin\Settings\SettingIndex;
use App\Livewire\Admin\SiteSettings\HeroSlideIndex;
use App\Livewire\Admin\SiteSettings\SiteContacts;
use App\Livewire\Admin\SiteSettings\SiteContent;
use App\Livewire\Admin\Users\UserIndex;

// Крок «код з пошти» — доступний авторизованому адміну БЕЗ otp-гейту,
// інакше вийшов би нескінченний редирект на самого себе.
Route::prefix('admin')
    ->middleware(['auth', 'verified', 'admin'])
    ->name('admin.')
    ->group(function () {
        Route::get('/verify-code', [EmailOtpController::class, 'show'])->name('verify-code');
        Route::post('/verify-code', [EmailOtpController::class, 'verify'])
            ->middleware('throttle:6,1')->name('verify-code.verify');
        Route::post('/verify-code/resend', [EmailOtpController::class, 'resend'])
            ->middleware('throttle:3,1')->name('verify-code.resend');
    });

Route::prefix('admin')
    ->middleware(['auth', 'verified', 'admin', 'email.otp'])
    ->name('admin.')
    ->group(function () {
        Route::get('/', Dashboard::class)->name('dashboard');

        // Каталог
        Route::get('/products', ProductIndex::class)->name('products.index');
        Route::get('/products/create', ProductForm::class)->name('products.create');
        Route::get('/products/{id}/edit', ProductForm::class)->name('products.edit');
        Route::get('/products/{product}/pdf', [ProductPdfController::class, 'card'])->name('products.pdf');

        Route::get('/categories', CategoryIndex::class)->name('categories.index');
        Route::get('/attributes', AttributeIndex::class)->name('attributes.index');
        Route::get('/brands', BrandIndex::class)->name('brands.index');
        Route::get('/product-types', ProductTypeIndex::class)->name('product-types.index');

        // Сумісність з технікою
        Route::get('/machinery-types', MachineryTypeIndex::class)->name('machinery-types.index');
        Route::get('/machinery-brands', MachineryBrandIndex::class)->name('machinery-brands.index');
        Route::get('/machinery-series', MachinerySeriesIndex::class)->name('machinery-series.index');
        Route::get('/machinery-models', MachineryModelIndex::class)->name('machinery-models.index');
        Route::get('/machinery-positions', MachineryPositionIndex::class)->name('machinery-positions.index');
        Route::get('/field-photos', FieldPhotoBrowse::class)->name('field-photos.index');

        // Блог
        Route::get('/posts', PostIndex::class)->name('posts.index');
        Route::get('/posts/create', PostForm::class)->name('posts.create');
        Route::get('/posts/{id}/edit', PostForm::class)->name('posts.edit');
        Route::post('/posts/upload-image', [PostImageController::class, 'store'])->name('posts.upload-image');

        // Заявки, користувачі, налаштування
        Route::get('/leads', LeadIndex::class)->name('leads.index');
        Route::get('/users', UserIndex::class)->name('users.index');
        Route::get('/settings', SettingIndex::class)->name('settings.index');
        Route::get('/site-settings', HeroSlideIndex::class)->name('site-settings.index');
        Route::get('/site-contacts', SiteContacts::class)->name('site-contacts.index');
        Route::get('/site-content', SiteContent::class)->name('site-content.index');
        Route::get('/security', SecurityPage::class)->name('security.index');
        Route::get('/import-export', ImportExport::class)->name('import-export.index');
    });
