<?php

namespace App\Livewire\Admin\Leads;

use App\Livewire\Concerns\WithAdminToast;
use App\Models\Lead;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class LeadIndex extends Component
{
    use WithPagination;
    use WithAdminToast;

    public const STATUSES = [
        'new'        => 'Нова',
        'processing' => 'В обробці',
        'confirmed'  => 'Підтверджена',
        'done'       => 'Виконана',
        'canceled'   => 'Скасована',
    ];

    public const ARCHIVED_STATUSES = ['done', 'canceled'];

    public const SOURCES = [
        'cart'         => 'Кошик',
        'consultation' => 'Консультація',
        'manual'       => 'Вручну',
    ];

    public const DELIVERY_METHODS = ['Нова Пошта', 'САТ', "Кур'єр", 'Самовивіз зі складу'];
    public const PAYMENT_METHODS = ['Накладений платіж (при отриманні)', 'Оплата за реквізитами (IBAN)'];

    public string $search = '';
    public string $filterStatus = '';
    public string $filterSource = '';
    public string $tab = 'active';

    public bool $showModal = false;
    public ?int $editingId = null;

    public string $customer_name = '';
    public string $phone = '';
    public ?string $city = null;
    public ?string $delivery_method = null;
    public ?string $delivery_address = null;
    public ?string $payment_method = null;
    public string $status = 'new';
    public ?string $manager_comment = null;

    public array $items = [];

    public string $productSearch = '';

    public function updating($name): void
    {
        if (in_array($name, ['search', 'filterStatus', 'filterSource'])) {
            $this->resetPage();
        }
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['active', 'archive'], true)) {
            return;
        }

        $this->tab = $tab;
        $this->filterStatus = '';
        $this->resetPage();
    }

    protected function rules(): array
    {
        return [
            'customer_name'      => ['required', 'string', 'max:255'],
            'phone'              => ['required', 'string', 'max:255', 'regex:/(?:\D*\d){9,}/'],
            'city'               => ['nullable', 'string', 'max:255'],
            'delivery_method'    => ['nullable', 'string', 'max:255'],
            'delivery_address'   => ['nullable', 'string', 'max:255'],
            'payment_method'     => ['nullable', 'string', 'max:255'],
            'status'             => ['required', 'in:new,processing,confirmed,done,canceled'],
            'manager_comment'    => ['nullable', 'string'],
            'items'              => ['array'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty'        => ['required', 'integer', 'min:1', 'max:1000'],
            'items.*.price'      => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function openCreate(): void
    {
        $this->reset('editingId', 'customer_name', 'phone',
            'city', 'delivery_method', 'delivery_address', 'payment_method',
            'manager_comment', 'items', 'productSearch');
        $this->status = 'new';
        $this->showModal = true;
    }

    public function openView(int $id): void
    {
        $lead = Lead::with('items.product')->findOrFail($id);

        $this->editingId       = $id;
        $this->customer_name    = $lead->customer_name;
        $this->phone            = $lead->phone;
        $this->city             = $lead->city;
        $this->delivery_method  = $lead->delivery_method;
        $this->delivery_address = $lead->delivery_address;
        $this->payment_method   = $lead->payment_method;
        $this->status           = $lead->status;
        $this->manager_comment  = $lead->manager_comment;
        $this->productSearch   = '';

        $this->items = $lead->items->map(fn ($i) => [
            'product_id' => $i->product_id,
            'name'       => $i->product?->name ?? '- товар видалено -',
            'sku'        => $i->product?->sku,
            'qty'        => $i->qty,
            'price'      => $i->price_at_request !== null ? (float) $i->price_at_request : null,
        ])->toArray();

        $this->showModal = true;
    }

    public function addProduct(int $productId): void
    {
        foreach ($this->items as $idx => $item) {
            if ((int) $item['product_id'] === $productId) {
                $this->items[$idx]['qty']++;
                $this->productSearch = '';
                return;
            }
        }

        $product = Product::find($productId);
        if (! $product) {
            return;
        }

        $this->items[] = [
            'product_id' => $product->id,
            'name'       => $product->name,
            'sku'        => $product->sku,
            'qty'        => 1,
            'price'      => $product->effectivePrice(),
        ];
        $this->productSearch = '';
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save(): void
    {
        $this->validate();

        $isNew = ! $this->editingId;

        $leadData = [
            'customer_name'    => $this->customer_name,
            'phone'            => $this->phone,
            'city'             => $this->city,
            'delivery_method'  => $this->delivery_method,
            'delivery_address' => $this->delivery_address,
            'payment_method'   => $this->payment_method,
            'status'           => $this->status,
            'manager_comment'  => $this->manager_comment,
        ];

        if ($this->editingId) {
            $lead = Lead::findOrFail($this->editingId);
            $lead->update($leadData);
        } else {
            $leadData['source'] = 'manual';
            $lead = Lead::create($leadData);
        }

        $lead->items()->delete();
        foreach ($this->items as $item) {
            $lead->items()->create([
                'product_id'       => $item['product_id'],
                'qty'              => (int) $item['qty'],
                'price_at_request' => ($item['price'] === null || $item['price'] === '') ? null : $item['price'],
            ]);
        }

        $this->showModal = false;
        session()->flash('success', $isNew ? 'Заявку створено' : 'Заявку оновлено');
    }

    public function delete(int $id): void
    {
        Lead::findOrFail($id)->delete();
        session()->flash('success', 'Заявку видалено');
    }

    public function render()
    {
        $archived = self::ARCHIVED_STATUSES;
        $scopeTab = fn ($q) => $this->tab === 'archive'
            ? $q->whereIn('status', $archived)
            : $q->whereNotIn('status', $archived);

        $leads = Lead::query()
            ->withCount('items')
            ->tap($scopeTab)
            ->when($this->search, fn ($q) => $q->where(fn ($w) => $w
                ->where('customer_name', 'like', "%{$this->search}%")
                ->orWhere('phone', 'like', "%{$this->search}%")))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterSource, fn ($q) => $q->where('source', $this->filterSource))
            ->orderByDesc('id')
            ->paginate(25);

        $activeCount = Lead::whereNotIn('status', $archived)->count();
        $archiveCount = Lead::whereIn('status', $archived)->count();

        $tabStatuses = collect(self::STATUSES)
            ->filter(fn ($label, $key) => in_array($key, $archived, true) === ($this->tab === 'archive'))
            ->all();

        $current = $this->editingId
            ? Lead::find($this->editingId)
            : null;

        $productResults = collect();
        if (trim($this->productSearch) !== '') {
            $addedIds = collect($this->items)->pluck('product_id')->all();
            $term = $this->productSearch;

            $productResults = Product::where('is_active', true)
                ->where(fn ($q) => $q->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%"))
                ->whereNotIn('id', $addedIds)
                ->orderBy('name')
                ->limit(10)
                ->get(['id', 'name', 'sku']);
        }

        return view('admin.leads.lead-index', [
            'leads'           => $leads,
            'current'         => $current,
            'statuses'        => self::STATUSES,
            'sources'         => self::SOURCES,
            'tabStatuses'     => $tabStatuses,
            'activeCount'     => $activeCount,
            'archiveCount'    => $archiveCount,
            'deliveryMethods' => self::DELIVERY_METHODS,
            'paymentMethods'  => self::PAYMENT_METHODS,
            'productResults'  => $productResults,
        ])->layout('admin.layouts.admin');
    }
}
