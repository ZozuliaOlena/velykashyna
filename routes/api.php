<?php

use App\Http\Controllers\Api\LeadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Публічний API для клієнтської частини
|--------------------------------------------------------------------------
| Без авторизації (гість оформлює кошик без реєстрації).
| throttle захищає від спаму: до 30 заявок за хвилину з однієї IP.
*/

// POST /api/leads — оформлення кошика в заявку.
Route::post('/leads', [LeadController::class, 'store'])
    ->middleware('throttle:30,1')
    ->name('api.leads.store');
