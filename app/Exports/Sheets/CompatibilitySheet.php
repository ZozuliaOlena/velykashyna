<?php

namespace App\Exports\Sheets;

use App\Models\ProductMachineryCompatibility;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class CompatibilitySheet implements FromArray, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'Сумісність';
    }

    public function headings(): array
    {
        return ['Артикул', 'Тип техніки', 'Виробник', 'Модель', 'Позиція', 'Примітка'];
    }

    public function array(): array
    {
        $items = ProductMachineryCompatibility::with([
            'product', 'machineryType', 'machineryBrand', 'machineryModel', 'position',
        ])->get();

        $rows = [];
        foreach ($items as $c) {
            $rows[] = [
                $c->product?->sku,
                $c->machineryType?->name,
                $c->machineryBrand?->name,
                $c->machineryModel?->name,
                $c->position?->name,
                $c->notes,
            ];
        }

        return $rows;
    }
}
