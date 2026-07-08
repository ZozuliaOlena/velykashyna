<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Product;

/**
 * Динамічний sitemap.xml для Google: головна, каталог, категорії, статичні
 * сторінки, усі активні товари та блог (список + статті). Генерується з БД,
 * тож завжди актуальний.
 */
class SitemapController extends Controller
{
    public function index()
    {
        $urls = collect();

        // Головна + каталог + блог (список).
        $urls->push(['loc' => url('/'), 'changefreq' => 'daily', 'priority' => '1.0']);
        $urls->push(['loc' => route('catalog'), 'changefreq' => 'daily', 'priority' => '0.9']);
        $urls->push(['loc' => route('blog.index'), 'changefreq' => 'weekly', 'priority' => '0.6']);

        // Статичні сторінки.
        foreach (['about', 'contacts', 'pages.delivery', 'pages.returns', 'pages.warranty', 'pages.privacy'] as $name) {
            $urls->push(['loc' => route($name), 'changefreq' => 'monthly', 'priority' => '0.5']);
        }

        // Активні категорії каталогу (сторінки фільтра за категорією).
        Category::query()
            ->where('is_active', true)
            ->whereNotNull('slug')
            ->orderBy('id')
            ->get(['slug', 'updated_at'])
            ->each(fn (Category $c) => $urls->push([
                'loc'        => route('catalog', ['category' => $c->slug]),
                'lastmod'    => $c->updated_at?->toAtomString(),
                'changefreq' => 'weekly',
                'priority'   => '0.7',
            ]));

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

        // Опубліковані статті блогу.
        Post::published()
            ->orderBy('id')
            ->get(['slug', 'updated_at'])
            ->each(fn (Post $post) => $urls->push([
                'loc'        => route('blog.show', $post->slug),
                'lastmod'    => $post->updated_at?->toAtomString(),
                'changefreq' => 'monthly',
                'priority'   => '0.6',
            ]));

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
