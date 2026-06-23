<?php

namespace App\Imports;

use App\Imports\Sheets\CategoriesImportSheet;
use App\Imports\Sheets\CompatibilityImportSheet;
use App\Imports\Sheets\ProductsImportSheet;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CatalogImport implements WithMultipleSheets, SkipsUnknownSheets
{
    public array $report = [
        'products_created'   => 0,
        'products_updated'   => 0,
        'categories_touched' => 0,
        'compat_created'     => 0,
        'errors'             => [],
    ];

    public function sheets(): array
    {
        return [
            'Товари'     => new ProductsImportSheet($this),
            'Категорії'  => new CategoriesImportSheet($this),
            'Сумісність' => new CompatibilityImportSheet($this),
        ];
    }

    public function onUnknownSheet($sheetName): void
    {
        // листи з невідомими назвами просто ігноруємо
    }

    public function addError(string $message): void
    {
        $this->report['errors'][] = $message;
    }
}
