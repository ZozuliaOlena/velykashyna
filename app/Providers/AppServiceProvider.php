<?php

namespace App\Providers;

use App\Models\MachineryType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /** Назва типу техніки → SVG-іконка (коли в типу нема власної). */
    private const MACHINERY_ICONS = [
        'трактор' => 'tractor.svg',
        'комбайн' => 'combine.svg',
        'обприскувач' => 'sprayer.svg',
        'навантажувач' => 'loaders.svg',
        'спец' => 'loaders.svg',
        'вантаж' => 'truck.svg',
        'причіп' => 'wheel.svg',
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Топ-3 популярні типи техніки (за кількістю сумісних товарів) для
        // чипів у шапці. Доступні в усіх в'юхах через $headerLinks.
        View::composer(['partials.header', 'partials.mobile-menu'], function ($view) {
            $view->with('headerLinks', $this->headerLinks());
        });
    }

    private function headerLinks(): array
    {
        return Cache::remember('header_machinery', 600, function () {
            return MachineryType::query()
                ->withCount('compatibility')
                ->orderByDesc('compatibility_count')
                ->orderBy('name')
                ->take(3)
                ->get()
                ->map(fn (MachineryType $m) => [
                    'name' => $m->name,
                    'url' => route('catalog', ['machinery' => $m->name]),
                    'icon' => $m->iconUrl() ?? '/images/svg/tehnics/' . $this->iconFor($m->name),
                ])
                ->all();
        });
    }

    private function iconFor(string $name): string
    {
        $name = mb_strtolower($name);
        foreach (self::MACHINERY_ICONS as $needle => $icon) {
            if (str_contains($name, $needle)) {
                return $icon;
            }
        }

        return 'wheel.svg';
    }
}
