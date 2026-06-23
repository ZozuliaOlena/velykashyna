<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Str;
use ZipArchive;

/**
 * Масова прив'язка фото з архіву за іменем файлу (= артикул товару).
 *   036898.jpg     → основне фото товару 036898
 *   036898_2.jpg   → додаткове фото (галерея)
 */
class ProductPhotoArchive
{
    private const IMAGE_EXT = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public function import(string $zipPath): array
    {
        $report = ['main' => 0, 'gallery' => 0, 'skipped' => 0, 'errors' => []];

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            $report['errors'][] = 'Не вдалося відкрити архів';
            return $report;
        }

        $dir = storage_path('app/private/_photos_' . Str::random(10));
        @mkdir($dir, 0755, true);
        $zip->extractTo($dir);
        $zip->close();

        foreach ($this->imageFiles($dir) as $file) {
            $filename = pathinfo($file, PATHINFO_FILENAME);
            [$product, $collection] = $this->match($filename);

            if (! $product) {
                $report['skipped']++;
                continue;
            }

            $product->addMedia($file)
                ->usingFileName(Str::random(24) . '.' . strtolower(pathinfo($file, PATHINFO_EXTENSION)))
                ->toMediaCollection($collection);

            $report[$collection]++;
        }

        $this->cleanup($dir);

        return $report;
    }

    /** @return array{0: ?Product, 1: string} */
    private function match(string $filename): array
    {
        // точний збіг імені = артикул → основне
        if ($product = Product::where('sku', $filename)->first()) {
            return [$product, 'main'];
        }

        // "<sku>_2" або "<sku>-2" → додаткове
        if (preg_match('/^(.*)[_-]\d+$/u', $filename, $m)) {
            if ($product = Product::where('sku', $m[1])->first()) {
                return [$product, 'gallery'];
            }
        }

        return [null, 'main'];
    }

    /** @return list<string> */
    private function imageFiles(string $dir): array
    {
        $files = [];
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($it as $f) {
            if ($f->isFile() && in_array(strtolower($f->getExtension()), self::IMAGE_EXT, true)) {
                $files[] = $f->getPathname();
            }
        }
        return $files;
    }

    private function cleanup(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $f) {
            $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname());
        }
        @rmdir($dir);
    }
}
