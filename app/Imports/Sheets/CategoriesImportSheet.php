<?php

namespace App\Imports\Sheets;

use App\Imports\CatalogImport;
use App\Models\Category;
use App\Support\CatalogColumns;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class CategoriesImportSheet implements ToCollection
{
    use ParsesSheet;

    public function __construct(private CatalogImport $import)
    {
    }

    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            return;
        }

        $header = $rows->shift();
        $this->mapHeader($header);

        foreach ($rows as $row) {
            $levels = array_filter([
                $this->val($row, 'Рівень 1'),
                $this->val($row, 'Рівень 2'),
                $this->val($row, 'Рівень 3'),
                $this->val($row, 'Рівень 4'),
            ], fn ($v) => $v !== null);

            if (empty($levels)) {
                continue;
            }

            $leafId = CatalogColumns::resolveCategoryPath(implode(' / ', $levels));
            if (! $leafId) {
                continue;
            }

            // SEO/slug для листової категорії
            $seo = array_filter([
                'slug'            => $this->val($row, 'URL'),
                'seo_title'       => $this->val($row, 'SEO Title'),
                'seo_description' => $this->val($row, 'SEO Description'),
                'seo_h1'          => $this->val($row, 'SEO H1'),
            ], fn ($v) => $v !== null);

            if (! empty($seo)) {
                Category::whereKey($leafId)->update($seo);
            }

            $this->import->report['categories_touched']++;
        }
    }
}
