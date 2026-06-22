<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('web.home');
})->name('home');

// Заглушка каталогу — повноцінний каталог із фільтрами додається окремим етапом.
Route::get('/catalog', function () {
    return view('web.catalog');
})->name('catalog');
