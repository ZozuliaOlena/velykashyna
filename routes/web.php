<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // showFooterCta=false — на головній уже є власний CTA-банер, щоб не дублювати.
    return view('web.home', ['showFooterCta' => false]);
})->name('home');

// Заглушка каталогу — повноцінний каталог із фільтрами додається окремим етапом.
Route::get('/catalog', function () {
    return view('web.catalog');
})->name('catalog');
