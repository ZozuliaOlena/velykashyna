<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductSlugRedirect;
use App\Support\SizeSlug;
use App\Support\Translit;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Одноразовий фікс: у наявних товарах розділяє «злиплий» типорозмір у слагу
 * (235r25 → 23-5r25) і залишає 301-редирект зі старого ЧПУ на новий.
 * Ідемпотентна: повторний запуск нічого не змінює. Підтримує --dry-run.
 */
class SplitSizeSlugs extends Command
{
    protected $signature = 'catalog:split-size-slugs {--dry-run : Лише показати, нічого не змінювати}';

    protected $description = 'Розділити типорозмір у слагах наявних товарів дефісами (зі збереженням 301-редиректів)';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $changed = 0;

        foreach (Product::query()->whereNotNull('size_raw')->get() as $product) {
            $glued = Str::slug(Translit::uk($product->size_raw));
            $split = Str::slug(Translit::uk(SizeSlug::format($product->size_raw)));

            if ($glued === '' || $glued === $split) {
                continue;
            }

            // Замінюємо розмір лише як цілий токен (між дефісами / краями рядка).
            $newSlug = preg_replace(
                '/(?<=^|-)' . preg_quote($glued, '/') . '(?=-|$)/',
                $split,
                (string) $product->slug,
            );

            if ($newSlug === $product->slug) {
                continue; // розмір у слагу не як окремий токен - не чіпаємо
            }

            $newSlug = $this->ensureUnique($newSlug, $product->id);
            $oldSlug = $product->slug;

            $this->line(($dry ? '[dry] ' : '') . "#{$product->id}: {$oldSlug} → {$newSlug}");

            if (! $dry) {
                $product->slug = $newSlug;
                $product->save();

                ProductSlugRedirect::updateOrCreate(
                    ['old_slug' => $oldSlug],
                    ['product_id' => $product->id],
                );
            }

            $changed++;
        }

        $this->info(($dry ? '[dry-run] ' : '') . "Оброблено товарів: {$changed}");

        return self::SUCCESS;
    }

    private function ensureUnique(string $slug, int $ignoreId): string
    {
        $base = $slug;
        $i = 1;

        while (
            Product::withTrashed()->where('slug', $slug)
                ->whereKeyNot($ignoreId)->exists()
        ) {
            $slug = $base . '-' . (++$i);
        }

        return $slug;
    }
}
