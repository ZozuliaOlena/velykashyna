<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Leads\LeadIndex;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Lead;
use App\Models\Product;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        return view('admin.dashboard', [
            'productsCount'   => Product::count(),
            'categoriesCount' => Category::count(),
            'brandsCount'     => Brand::count(),
            'newLeadsCount'   => Lead::where('status', 'new')->count(),
            'recentLeads'     => Lead::withCount('items')
                ->whereNotIn('status', LeadIndex::ARCHIVED_STATUSES)
                ->latest('id')->take(8)->get(),
            'leadStatuses'    => LeadIndex::STATUSES,
        ])->layout('admin.layouts.admin');
    }
}
