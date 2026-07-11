<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

/**
 * Резервне копіювання та відновлення сайту без зовнішніх утиліт (mysqldump тощо).
 * Копія - це zip з:
 *   database.sql - повний дамп БД (DROP + CREATE + INSERT кожної таблиці),
 *   media/…      - завантажені фото (storage/app/public), якщо увімкнено,
 *   backup.json  - метадані копії.
 */
class BackupService
{
    private const MEDIA_ROOT = 'app/public';

    /** Створює резервну копію і повертає шлях до zip-файлу. */
    public function create(bool $includeMedia = true): string
    {
        // Дамп БД будується в памʼяті одним рядком, а Livewire ще й
        // base64-кодує готовий zip для віддачі - на дефолтних 128M це
        // вичерпує памʼять. Піднімаємо ліміти на час операції.
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        $work = storage_path('app/private/_backup_' . Str::random(10));
        File::ensureDirectoryExists($work);

        $sqlPath = $work . '/database.sql';
        File::put($sqlPath, $this->dumpDatabase());

        File::ensureDirectoryExists(storage_path('app/private'));
        $zipPath = storage_path('app/private/backup-' . now()->format('Y-m-d_His') . '.zip');

        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $zip->addFile($sqlPath, 'database.sql');
        $zip->addFromString('backup.json', json_encode([
            'app'            => config('app.name'),
            'created_at'     => now()->toIso8601String(),
            'database'       => DB::getDatabaseName(),
            'includes_media' => $includeMedia,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        if ($includeMedia) {
            $mediaRoot = storage_path(self::MEDIA_ROOT);
            if (is_dir($mediaRoot)) {
                $this->addDirectory($zip, $mediaRoot, 'media');
            }
        }

        $zip->close();
        File::deleteDirectory($work);

        return $zipPath;
    }

    /** Відновлює сайт із раніше створеної копії. Повертає короткий звіт. */
    public function restore(string $zipPath): array
    {
        // Відновлення читає весь SQL-дамп у памʼять і виконує його -
        // так само тримаємо запас памʼяті/часу.
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        $report = ['db' => false, 'media' => false, 'errors' => []];

        $dir = storage_path('app/private/_restore_' . Str::random(10));
        File::ensureDirectoryExists($dir);

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            $report['errors'][] = 'Не вдалося відкрити архів.';
            return $report;
        }
        $zip->extractTo($dir);
        $zip->close();

        $sqlPath = $dir . '/database.sql';
        if (! is_file($sqlPath)) {
            File::deleteDirectory($dir);
            $report['errors'][] = 'Це не схоже на резервну копію (немає database.sql).';
            return $report;
        }

        try {
            $pdo = DB::connection()->getPdo();
            $pdo->exec(File::get($sqlPath));
            $report['db'] = true;
        } catch (\Throwable $e) {
            report($e);
            $report['errors'][] = 'Помилка відновлення БД: ' . $e->getMessage();
        }

        $mediaDir = $dir . '/media';
        if (is_dir($mediaDir)) {
            File::ensureDirectoryExists(storage_path(self::MEDIA_ROOT));
            File::copyDirectory($mediaDir, storage_path(self::MEDIA_ROOT));
            $report['media'] = true;
        }

        File::deleteDirectory($dir);

        return $report;
    }

    /** Повний SQL-дамп усіх таблиць поточної БД. */
    private function dumpDatabase(): string
    {
        $pdo = DB::connection()->getPdo();
        $out = "SET FOREIGN_KEY_CHECKS=0;\nSET NAMES utf8mb4;\n\n";

        foreach (DB::select('SHOW TABLES') as $row) {
            $table = array_values((array) $row)[0];

            $create = (array) DB::select("SHOW CREATE TABLE `{$table}`")[0];
            $createSql = $create['Create Table'] ?? array_values($create)[1];
            $out .= "DROP TABLE IF EXISTS `{$table}`;\n{$createSql};\n\n";

            foreach (DB::table($table)->cursor() as $record) {
                $data = (array) $record;
                $cols = '`' . implode('`, `', array_keys($data)) . '`';
                $vals = implode(', ', array_map(
                    fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v),
                    array_values($data)
                ));
                $out .= "INSERT INTO `{$table}` ({$cols}) VALUES ({$vals});\n";
            }
            $out .= "\n";
        }

        $out .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return $out;
    }

    /** Рекурсивно додає теку у zip під заданим префіксом. */
    private function addDirectory(ZipArchive $zip, string $dir, string $prefix): void
    {
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($items as $file) {
            if ($file->isDir()) {
                continue;
            }
            $path = $file->getRealPath();
            $rel = str_replace('\\', '/', substr($path, strlen($dir) + 1));
            $zip->addFile($path, $prefix . '/' . $rel);
        }
    }
}
