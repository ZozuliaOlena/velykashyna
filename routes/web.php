<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\FavoritesController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\InWorkController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

// Головна — дані тягнуться з БД (товари, категорії, техніка, бренди).
// showFooterCta=false — на головній уже є власний CTA-банер.
// transparentHeader=true — шапка накладається на повноекранний слайдер.
Route::get('/', [HomeController::class, 'index'])->name('home');

// Каскадний фільтр головної (JSON): доступні опції наступних рівнів.
Route::get('/filters/options', [HomeController::class, 'filterOptions'])->name('home.filters');

// Каталог шин — вибірка з БД з фільтрами, сортуванням та пагінацією.
Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog');

// Кількість товарів за поточним вибором фільтрів (JSON, для живого лічильника).
Route::get('/catalog/count', [CatalogController::class, 'count'])->name('catalog.count');

// Випадаючий (живий) пошук у навігації — підказки товарів (JSON).
Route::get('/search/suggest', [SearchController::class, 'suggest'])->name('search.suggest');

// Сторінка товару (детальна картка) за ЧПУ-slug.
Route::get('/product/{product:slug}', [ProductController::class, 'show'])->name('product');

// Блог — список статей та детальна сторінка за ЧПУ-slug.
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');

// Фід для Google Merchant Center (XML).
Route::get('/feed/merchant.xml', [FeedController::class, 'merchant'])->name('feed.merchant');

// Карта сайту для Google (динамічна: головна, каталог, сторінки, усі товари).
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Кошик і Обране — гостьові (стан у localStorage), рендеряться на клієнті.
Route::view('/cart', 'web.cart', ['showFooterCta' => false])->name('cart');
Route::view('/favorites', 'web.favorites', ['showFooterCta' => false])->name('favorites');
Route::get('/favorites/cards', [FavoritesController::class, 'cards'])->name('favorites.cards');

// Порівняння шин — таблиця характеристик за переданими ?ids=.
Route::get('/compare', [CompareController::class, 'index'])->name('compare');

// «Шини в роботі» на конкретній моделі техніки (фото-застосування).
Route::get('/in-work/{machineryModel}', [InWorkController::class, 'show'])->name('in-work');

// Про нас — власний CTA + прозора шапка поверх темного hero (як на головній).
Route::get('/about', function () {
    return view('web.about', ['showFooterCta' => false, 'transparentHeader' => true]);
})->name('about');

// Контакти — власний блок зв'язку/карти, стандартний CTA футера вимикаємо.
Route::get('/contacts', function () {
    return view('web.contacts', ['showFooterCta' => false]);
})->name('contacts');

// Інформаційні сторінки (потрібні, зокрема, для модерації Google Merchant).
Route::view('/delivery', 'web.pages.delivery')->name('pages.delivery');
Route::view('/returns', 'web.pages.returns')->name('pages.returns');
Route::view('/warranty', 'web.pages.warranty')->name('pages.warranty');
Route::view('/privacy', 'web.pages.privacy')->name('pages.privacy');
