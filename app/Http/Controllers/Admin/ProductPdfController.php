<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * PDF-картка товару для пересилання клієнту (Viber/Telegram).
 *   ?variant=short|full   - коротка / повна
 *   ?price=1|0            - з ціною / без ціни
 *   ?mode=inline|download - відкрити у вкладці / завантажити файл
 */
class ProductPdfController extends Controller
{
    public function card(Product $product, Request $request)
    {
        $variant = $request->query('variant') === 'short' ? 'short' : 'full';
        $withPrice = $request->boolean('price', true);

        $product->load([
            'brand', 'productType', 'categories',
            'fieldPhotos.machineryType', 'fieldPhotos.machineryBrand', 'fieldPhotos.machineryModel', 'fieldPhotos.media',
            'machineryCompatibility.machineryBrand', 'machineryCompatibility.machineryModel',
        ]);

        $machinery = $product->machineryCompatibility
            ->map(fn ($c) => trim(($c->machineryBrand?->name ? $c->machineryBrand->name.' ' : '').($c->machineryModel?->name ?? '')))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $data = [
            'product'   => $product,
            'variant'   => $variant,
            'withPrice' => $withPrice,
            'logo'      => $this->dataUri(public_path('images/logo.png')),
            'machinery' => $machinery,
            'photo'     => $this->mainPhotoData($product),
            'fieldPhotos' => $variant === 'full'
                ? $product->fieldPhotos->take(6)->map(fn ($fp) => [
                    
                    'img'     => $this->dataUri($this->mediaPath($fp->getFirstMedia('photo'), 'thumb')),
                    'label'   => $fp->machineryLabel(),
                    'caption' => $fp->caption,
                ])->filter(fn ($x) => $x['img'])->values()->all()
                : [],
            'contacts'  => config('site.contacts', []),
        ];

        $pdf = Pdf::loadView('pdf.product-card', $data)->setPaper('a4');
        $filename = 'velykashyna-' . ($product->sku ?: $product->id) . '.pdf';

        return $request->query('mode') === 'inline'
            ? $pdf->stream($filename)
            : $pdf->download($filename);
    }

    /** Головне фото товару як data-URI: власне (main→gallery) або каталожне. */
    private function mainPhotoData(Product $product): ?string
    {
        // Головне фото: власне → каталожне → галерея (запасний варіант).
        if ($media = $product->getFirstMedia('main')) {
            return $this->dataUri($this->mediaPath($media, 'uniform'));
        }

        if (($ci = $product->catalogImage) && ($cm = $ci->getFirstMedia('image'))) {
            return $this->dataUri($this->mediaPath($cm, 'uniform'));
        }

        if ($media = $product->getFirstMedia('gallery')) {
            return $this->dataUri($this->mediaPath($media, 'uniform'));
        }

        return null;
    }

    /** Локальний шлях до медіа (потрібна конверсія, інакше оригінал). */
    private function mediaPath(?Media $media, string $conversion): ?string
    {
        if (! $media) {
            return null;
        }

        return $media->hasGeneratedConversion($conversion)
            ? $media->getPath($conversion)
            : $media->getPath();
    }

    /** Файл → data-URI (dompdf надійно вбудовує саме так). */
    private function dataUri(?string $path): ?string
    {
        if (! $path || ! is_file($path)) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/jpeg';

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }
}
