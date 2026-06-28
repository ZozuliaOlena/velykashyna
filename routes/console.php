<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Автоматичне резервне копіювання (spatie/laravel-backup)
|--------------------------------------------------------------------------
| Працює після встановлення пакета:  composer require spatie/laravel-backup
| Та за умови, що на сервері запущено планувальник (cron):
|   * * * * * cd /шлях/до/проєкту && php artisan schedule:run >> /dev/null 2>&1
|
| Команди backup:clean / backup:run реєструє сам пакет. Якщо пакет ще не
| встановлено — ці рядки нічого не роблять, поки не запуститься cron.
*/
Schedule::command('backup:clean')->daily()->at('01:00')->onOneServer();
Schedule::command('backup:run')->daily()->at('01:30')->onOneServer();
