<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Brands\BrandIndex;

Route::prefix('admin')
    ->middleware(['auth', 'verified', 'admin'])
    ->name('admin.')
    ->group(function () {
        Route::get('/', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        Route::get('/brands', BrandIndex::class)->name('brands.index');

        });