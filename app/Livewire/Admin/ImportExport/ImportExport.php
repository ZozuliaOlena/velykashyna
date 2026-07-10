<?php

namespace App\Livewire\Admin\ImportExport;

use App\Livewire\Concerns\WithAdminToast;
use App\Exports\CatalogExport;
use App\Imports\CatalogImport;
use App\Models\CatalogImage;
use App\Models\Product;
use App\Models\Setting;
use App\Services\BackupService;
use App\Services\ProductPhotoArchive;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class ImportExport extends Component
{
    use WithFileUploads;
    use WithAdminToast;

    public $importFile = null;
    public $photoArchive = null;
    public array $catalogImages = [];

    public array $importReport = [];
    public array $photoReport = [];
    public array $catalogImageReport = [];

    public bool $backupMedia = true;
    public $restoreFile = null;
    public array $restoreReport = [];

    public string $merchantStoreName = '';

    public function mount(): void
    {
        $this->merchantStoreName = Setting::get('merchant_store_name') ?: 'ВЕЛИКА ШИНА';
    }

    public function saveMerchantSettings(): void
    {
        $this->validate(['merchantStoreName' => ['required', 'string', 'max:255']]);

        Setting::set('merchant_store_name', trim($this->merchantStoreName));
        session()->flash('success', 'Налаштування Merchant збережено');
    }

    public function export()
    {
        return Excel::download(new CatalogExport(), 'velykashyna-catalog.xlsx');
    }

    public function import(): void
    {
        $this->validate([
            'importFile' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:20480'],
        ]);

        $import = new CatalogImport();
        Excel::import($import, $this->importFile->getRealPath());

        $this->importReport = $import->report;
        $this->reset('importFile');
    }

    public function backup()
    {
        try {
            $path = app(BackupService::class)->create($this->backupMedia);
            return response()->download($path, basename($path))->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Не вдалося створити резервну копію.');
        }
    }

    public function restore(): void
    {
        $this->validate([
            
            'restoreFile' => ['required', 'file', 'mimes:zip', 'max:1048576'],
        ]);

        $this->restoreReport = app(BackupService::class)->restore($this->restoreFile->getRealPath());
        $this->reset('restoreFile');

        if (empty($this->restoreReport['errors'])) {
            session()->flash('success', 'Відновлення з копії завершено');
        } else {
            session()->flash('error', 'Відновлення завершилось із помилками - перевірте звіт');
        }
    }

    public function uploadPhotos(): void
    {
        $this->validate([
            'photoArchive' => ['required', 'file', 'mimes:zip', 'max:204800'],
        ]);

        $this->photoReport = app(ProductPhotoArchive::class)->import($this->photoArchive->getRealPath());
        $this->reset('photoArchive');
    }

    public function uploadCatalogImages(): void
    {
        $this->validate([
            'catalogImages'   => ['required', 'array'],
            'catalogImages.*' => ['file', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
        ]);

        $uploaded = 0;
        $created = 0;
        $linked = 0;

        foreach ($this->catalogImages as $file) {
            $name = $file->getClientOriginalName();

            $ci = CatalogImage::firstOrNew(['filename' => $name]);
            if (! $ci->exists) {
                $ci->label = $name;
                $ci->save();
                $created++;
            }

            $ci->clearMediaCollection('image');
            $ci->addMedia($file->getRealPath())
                ->usingFileName(Str::random(24) . '.' . strtolower($file->getClientOriginalExtension()))
                ->toMediaCollection('image');

            $uploaded++;
            $linked += $ci->products()->count();
        }

        $this->catalogImageReport = compact('uploaded', 'created', 'linked');
        $this->reset('catalogImages');
    }

    public function render()
    {
        return view('admin.import-export.index', [
            'feedUrl'    => route('feed.merchant'),
            'feedCount'  => Product::forMerchantFeed()->count(),
            'sitemapUrl' => url('/sitemap.xml'),
            'robotsUrl'  => url('/robots.txt'),
        ])->layout('admin.layouts.admin');
    }
}
