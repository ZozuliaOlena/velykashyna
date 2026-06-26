<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\HomeController;
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

// Про нас — власний CTA + прозора шапка поверх темного hero (як на головній).
Route::get('/about', function () {
    return view('web.about', ['showFooterCta' => false, 'transparentHeader' => true]);
})->name('about');

// Контакти — власний блок зв'язку/карти, стандартний CTA футера вимикаємо.
Route::get('/contacts', function () {
    return view('web.contacts', ['showFooterCta' => false]);
})->name('contacts');
