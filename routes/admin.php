<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Brands\BrandIndex;
use App\Livewire\Admin\Products\ProductIndex;
use App\Livewire\Admin\Products\ProductForm;

Route::prefix('admin')
    ->middleware(['auth', 'verified', 'admin'])
    ->name('admin.')
    ->group(function () {
        Route::get('/', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        Route::get('/brands', BrandIndex::class)->name('brands.index');

        Route::get('/products', ProductIndex::class)->name('products.index');
        Route::get('/products/create', ProductForm::class)->name('products.create');
        Route::get('/products/{id}/edit', ProductForm::class)->name('products.edit');
    });