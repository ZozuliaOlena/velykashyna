<?php

namespace App\Exports;

use App\Exports\Sheets\CategoriesSheet;
use App\Exports\Sheets\CompatibilitySheet;
use App\Exports\Sheets\ProductsSheet;
use App\Models\Attribute;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CatalogExport implements WithMultipleSheets
{
    use Exportable;

    public function sheets(): array
    {
        $attributes = Attribute::orderBy('sort_order')->orderBy('name')->get();

        return [
            new ProductsSheet($attributes),
            new CompatibilitySheet(),
            new CategoriesSheet(),
        ];
    }
}
