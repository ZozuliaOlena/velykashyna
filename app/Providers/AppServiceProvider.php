<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\MachineryType;
use App\Models\ProductType;
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
        // Дані меню каталогу (типи / техніка / категорії) для шапки й
        // мобільного меню; $headerLinks — топ-3 техніки для чипів.
        View::composer(['partials.header', 'partials.mobile-menu'], function ($view) {
            $menu = $this->catalogMenu();
            $view->with('catalogMenu', $menu);
            $view->with('headerLinks', $menu['chips']);
        });
    }

    private function catalogMenu(): array
    {
        return Cache::remember('catalog_menu', 600, function () {
            $types = ProductType::orderBy('id')->get(['code', 'name'])
                ->map(fn (ProductType $t) => [
                    'name' => $t->name,
                    'url' => route('catalog', ['type' => $t->code]),
                ])->all();

            $machinery = MachineryType::query()
                ->withCount('compatibility')
                ->orderByDesc('compatibility_count')->orderBy('name')
                ->get()
                ->map(fn (MachineryType $m) => [
                    'name' => $m->name,
                    'url' => route('catalog', ['machinery' => $m->name]),
                    'icon' => $m->iconUrl() ?? '/images/svg/tehnics/' . $this->iconFor($m->name),
                ])->all();

            $categories = Category::query()
                ->whereNull('parent_id')->where('is_active', true)
                ->orderBy('sort_order')->orderBy('name')
                ->take(8)->get(['name', 'slug'])
                ->map(fn (Category $c) => [
                    'name' => $c->name,
                    'url' => route('catalog', ['category' => $c->slug]),
                ])->all();

            return [
                'types' => $types,
                'machinery' => $machinery,
                'categories' => $categories,
                'chips' => array_slice($machinery, 0, 3),
            ];
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
