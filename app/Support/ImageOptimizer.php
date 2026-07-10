<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Image;

/**
 * Оптимізація завантажених зображень: конвертація у сучасний WebP зі
 * стисненням і масштабуванням. Векторні/анімовані (svg, gif) лишаємо як є.
 *
 * Повертає шлях відносно public-диска (як звичайний ->store()), тож є
 * прямою заміною для $file->store($dir, 'public').
 */
class ImageOptimizer
{
    /** Формати, які не чіпаємо (вектор / анімація). */
    private const PASSTHROUGH = ['svg', 'gif', 'ico'];

    public static function toWebp(UploadedFile $file, string $dir, int $maxDim = 1600, int $quality = 82): string
    {
        $dir = trim($dir, '/');
        $ext = strtolower($file->getClientOriginalExtension());

        if (in_array($ext, self::PASSTHROUGH, true) || ! str_starts_with((string) $file->getMimeType(), 'image/')) {
            return $file->store($dir, 'public');
        }

        try {
            $name = Str::random(40) . '.webp';
            $absDir = Storage::disk('public')->path($dir);
            File::ensureDirectoryExists($absDir);

            Image::load($file->getRealPath())
                ->fit(Fit::Max, $maxDim, $maxDim)   // не збільшуємо маленькі, великі — вписуємо
                ->format('webp')
                ->quality($quality)
                ->save($absDir . '/' . $name);

            return $dir . '/' . $name;
        } catch (\Throwable $e) {
            report($e);
            // Фолбек: якщо конвертація не вдалась — зберігаємо оригінал, щоб не зламати завантаження.
            return $file->store($dir, 'public');
        }
    }
}
