<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // showFooterCta=false — на головній уже є власний CTA-банер, щоб не дублювати.
    // transparentHeader=true — шапка накладається на повноекранний слайдер.
    return view('web.home', ['showFooterCta' => false, 'transparentHeader' => true]);
})->name('home');

// Заглушка каталогу — повноцінний каталог із фільтрами додається окремим етапом.
Route::get('/catalog', function () {
    return view('web.catalog');
})->name('catalog');
