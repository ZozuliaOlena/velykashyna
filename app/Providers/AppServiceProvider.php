<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\MachineryType;
use App\Models\ProductType;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
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

    /** Назва кореневої категорії → SVG-іконка для чипів у шапці. */
    private const CATEGORY_ICONS = [
        'агро' => 'tractor.svg',
        'спец' => 'loaders.svg',
        'вантаж' => 'truck.svg',
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Контакти й соцмережі, задані в адмінці, перекривають дефолти з
        // config/site.php — тож усі в'юхи (config('site...')) показують
        // актуальні значення без жодних змін у самих в'юхах.
        $this->applySiteContacts();

        // Дані меню каталогу (типи / техніка / категорії) для шапки й
        // мобільного меню; $headerLinks — топ-3 техніки для чипів.
        View::composer(['partials.header', 'partials.mobile-menu', 'partials.footer'], function ($view) {
            $menu = $this->catalogMenu();
            $view->with('catalogMenu', $menu);
            $view->with('headerLinks', $menu['chips']);
        });
    }

    /**
     * Перекриваємо config('site.contacts.*') / config('site.socials.*')
     * значеннями з таблиці settings (керуються в адмінці «Контакти сайту»).
     * Порожні поля лишають дефолт із config/site.php.
     */
    private function applySiteContacts(): void
    {
        // Не чіпаємо під час консольних команд/міграцій (БД може бути ще не готова).
        if ($this->app->runningInConsole()) {
            return;
        }

        try {
            if (! Schema::hasTable('settings')) {
                return;
            }
        } catch (\Throwable) {
            return;
        }

        $val = fn (string $k) => trim((string) Setting::get($k)) ?: null;
        $href = fn (?string $p) => $p ? '+' . preg_replace('/\D/', '', $p) : null;

        if ($phone = $val('contact_phone')) {
            config(['site.contacts.phone' => $phone, 'site.contacts.phone_href' => $href($phone)]);
        }
        if ($phone2 = $val('contact_phone2')) {
            config(['site.contacts.phone2' => $phone2, 'site.contacts.phone2_href' => $href($phone2)]);
        }
        if ($email = $val('contact_email')) {
            config(['site.contacts.email' => $email]);
        }
        if ($addr = $val('contact_address')) {
            config(['site.contacts.address' => $addr]);
        }
        if ($mq = $val('contact_map_query')) {
            config(['site.contacts.map_query' => $mq]);
        }
        if ($me = $val('contact_map_embed')) {
            // Приймаємо і повний <iframe>, і лише src — витягуємо посилання.
            if (preg_match('~src="([^"]+)"~', $me, $m)) {
                $me = $m[1];
            }
            config(['site.contacts.map_embed' => $me]);
        }

        // Соцмережі: значення з адмінки перекриває дефолт; порожнє або "#"
        // нормалізуємо до null — щоб порожнє посилання не показувалось на сайті.
        $socials = (array) config('site.socials', []);
        foreach (array_keys($socials) as $soc) {
            $link = $val("social_{$soc}") ?? $socials[$soc];
            $socials[$soc] = ($link && $link !== '#') ? $link : null;
        }
        config(['site.socials' => $socials]);
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

            $rootCategories = Category::query()
                ->whereNull('parent_id')->where('is_active', true)
                ->orderBy('sort_order')->orderBy('name')
                ->take(8)->get(['name', 'slug']);

            $categories = $rootCategories->map(fn (Category $c) => [
                'name' => $c->name,
                'url' => route('catalog', ['category' => $c->slug]),
            ])->all();

            // Чипи в шапці — головні підкатегорії «Шини» у фіксованому порядку.
            $chipSlugs = ['agroshyna', 'spetsshyna', 'vantazhni-shyny'];
            $chips = Category::query()
                ->where('is_active', true)
                ->whereIn('slug', $chipSlugs)
                ->get(['name', 'slug'])
                ->sortBy(fn (Category $c) => array_search($c->slug, $chipSlugs))
                ->map(fn (Category $c) => [
                    'name' => $c->name,
                    'url' => route('catalog', ['category' => $c->slug]),
                    'icon' => '/images/svg/tehnics/' . $this->categoryIcon($c->name),
                ])->values()->all();

            return [
                'types' => $types,
                'machinery' => $machinery,
                'categories' => $categories,
                'chips' => $chips,
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

    private function categoryIcon(string $name): string
    {
        $name = mb_strtolower($name);
        foreach (self::CATEGORY_ICONS as $needle => $icon) {
            if (str_contains($name, $needle)) {
                return $icon;
            }
        }

        return 'wheel.svg';
    }
}
