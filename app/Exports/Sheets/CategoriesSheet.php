<?php

namespace App\Exports\Sheets;

use App\Models\Category;
use App\Support\CatalogColumns;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class CategoriesSheet implements FromArray, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'Категорії';
    }

    public function headings(): array
    {
        return ['Рівень 1', 'Рівень 2', 'Рівень 3', 'Рівень 4', 'URL', 'SEO Title', 'SEO Description', 'SEO H1'];
    }

    public function array(): array
    {
        $categories = Category::with('parent')->orderBy('level')->orderBy('name')->get();

        $rows = [];
        foreach ($categories as $cat) {
            $names = explode(' / ', CatalogColumns::categoryPath($cat));
            $levels = array_pad($names, 4, null);

            $rows[] = [
                $levels[0], $levels[1], $levels[2], $levels[3],
                $cat->slug, $cat->seo_title, $cat->seo_description, $cat->seo_h1,
            ];
        }

        return $rows;
    }
}
