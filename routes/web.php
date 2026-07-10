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

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/filters/options', [HomeController::class, 'filterOptions'])->name('home.filters');

Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog');

Route::get('/catalog/count', [CatalogController::class, 'count'])->name('catalog.count');

Route::get('/search/suggest', [SearchController::class, 'suggest'])->name('search.suggest');

Route::get('/product/{product:slug}', [ProductController::class, 'show'])->name('product');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/feed/merchant.xml', [FeedController::class, 'merchant'])->name('feed.merchant');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::view('/cart', 'web.cart', ['showFooterCta' => false])->name('cart');
Route::view('/favorites', 'web.favorites', ['showFooterCta' => false])->name('favorites');
Route::get('/favorites/cards', [FavoritesController::class, 'cards'])->name('favorites.cards');

Route::get('/compare', [CompareController::class, 'index'])->name('compare');

Route::get('/in-work/{machineryModel}', [InWorkController::class, 'show'])->name('in-work');

Route::get('/about', function () {
    return view('web.about', [
        'showFooterCta' => false,
        'transparentHeader' => true,
        
        'brands' => \App\Models\Brand::query()
            ->where('is_active', true)
            ->whereNotNull('logo')
            ->orderBy('name')
            ->get(),
    ]);
})->name('about');

Route::get('/contacts', function () {
    return view('web.contacts', ['showFooterCta' => false]);
})->name('contacts');

Route::view('/delivery', 'web.pages.delivery')->name('pages.delivery');
Route::view('/returns', 'web.pages.returns')->name('pages.returns');
Route::view('/warranty', 'web.pages.warranty')->name('pages.warranty');
Route::view('/privacy', 'web.pages.privacy')->name('pages.privacy');
