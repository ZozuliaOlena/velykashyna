<?php

namespace App\Http\Controllers;

use App\Models\MachineryModel;
use App\Models\ProductFieldPhoto;

class InWorkController extends Controller
{
    /**
     * «Шини в роботі» на конкретній моделі техніки: усі фото товарів
     * (з різних карток), де ця техніка вказана як застосування.
     */
    public function show(MachineryModel $machineryModel)
    {
        $photos = ProductFieldPhoto::query()
            ->where('machinery_model_id', $machineryModel->id)
            ->with([
                'media',
                'product.brand', 'product.productType', 'product.catalogImage',
                'machineryType', 'machineryBrand', 'machineryModel',
            ])
            ->orderByDesc('id')
            ->get()
            ->filter(fn (ProductFieldPhoto $fp) => $fp->imageUrl('large') && $fp->product?->is_active)
            ->values();

        $machineryModel->loadMissing('type', 'brand');

        $label = trim(implode(' ', array_filter([
            $machineryModel->type?->name,
            $machineryModel->brand?->name,
            $machineryModel->name,
        ])));

        return view('web.in-work', [
            'model' => $machineryModel,
            'label' => $label,
            'photos' => $photos,
            'showFooterCta' => false,
        ]);
    }
}
