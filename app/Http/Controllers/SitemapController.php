<?php

namespace App\Http\Controllers;

use App\Models\Product;

/**
 * Динамічний sitemap.xml для Google: головна, каталог, статичні сторінки
 * та всі активні товари. Завжди актуальний (генерується із БД).
 */
class SitemapController extends Controller
{
    public function index()
    {
        $urls = collect();

        // Головна + каталог.
        $urls->push(['loc' => url('/'), 'changefreq' => 'daily', 'priority' => '1.0']);
        $urls->push(['loc' => route('catalog'), 'changefreq' => 'daily', 'priority' => '0.9']);

        // Статичні сторінки.
        foreach (['about', 'contacts', 'pages.delivery', 'pages.returns', 'pages.warranty', 'pages.privacy'] as $name) {
            $urls->push(['loc' => route($name), 'changefreq' => 'monthly', 'priority' => '0.5']);
        }

        // Усі активні товари.
        Product::query()
            ->where('is_active', true)
            ->whereNotNull('slug')
            ->orderBy('id')
            ->get(['slug', 'updated_at'])
            ->each(function (Product $p) use ($urls) {
                $urls->push([
                    'loc'        => route('product', $p->slug),
                    'lastmod'    => $p->updated_at?->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority'   => '0.8',
                ]);
            });

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
