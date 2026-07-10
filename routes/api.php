<?php

use App\Http\Controllers\Api\LeadController;
use Illuminate\Support\Facades\Route;

Route::post('/leads', [LeadController::class, 'store'])
    ->middleware('throttle:30,1')
    ->name('api.leads.store');

Route::post('/consultations', [LeadController::class, 'consultation'])
    ->middleware('throttle:30,1')
    ->name('api.consultations.store');
