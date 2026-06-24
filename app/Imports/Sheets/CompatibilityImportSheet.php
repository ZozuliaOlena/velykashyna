<?php

namespace App\Imports\Sheets;

use App\Imports\CatalogImport;
use App\Models\MachineryBrand;
use App\Models\MachineryModel;
use App\Models\MachineryPosition;
use App\Models\MachineryType;
use App\Models\Product;
use App\Models\ProductMachineryCompatibility;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class CompatibilityImportSheet implements ToCollection
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

        if (! $this->has('артикул') || ! $this->has('тип техніки')) {
            $this->import->addError('Лист «Сумісність»: потрібні колонки «Артикул» і «Тип техніки»');
            return;
        }

        foreach ($rows as $row) {
            $sku = $this->val($row, 'Артикул');
            $typeName = $this->val($row, 'Тип техніки');
            if ($sku === null || $typeName === null) {
                continue;
            }

            $product = Product::where('sku', $sku)->first();
            if (! $product) {
                $this->import->addError("Сумісність: товар «{$sku}» не знайдено");
                continue;
            }

            $type = MachineryType::firstOrCreate(['name' => $typeName]);

            $brandName = $this->val($row, 'Виробник');
            $brandId = $brandName ? MachineryBrand::firstOrCreate(['name' => $brandName])->id : null;

            $modelName = $this->val($row, 'Модель');
            $modelId = null;
            if ($modelName && $brandId) {
                $modelId = MachineryModel::firstOrCreate(
                    ['machinery_brand_id' => $brandId, 'machinery_type_id' => $type->id, 'name' => $modelName]
                )->id;
            }

            $positionName = $this->val($row, 'Позиція');
            $positionId = $positionName ? MachineryPosition::firstOrCreate(['name' => $positionName])->id : null;

            ProductMachineryCompatibility::firstOrCreate([
                'product_id'         => $product->id,
                'machinery_type_id'  => $type->id,
                'machinery_brand_id' => $brandId,
                'machinery_model_id' => $modelId,
                'position_id'        => $positionId,
            ], [
                'notes' => $this->val($row, 'Примітка'),
            ]);

            $this->import->report['compat_created']++;
        }
    }
}
