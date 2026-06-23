<?php

namespace App\Livewire\Admin\Leads;

use App\Livewire\Concerns\WithAdminToast;
use App\Models\Lead;
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
        'canceled'   => 'Скасована',
    ];

    public string $search = '';
    public string $filterStatus = '';

    public bool $showModal = false;
    public ?int $editingId = null;

    public string $customer_name = '';
    public string $phone = '';
    public ?string $contact_method = null;
    public string $status = 'new';
    public ?string $manager_comment = null;

    public function updating($name): void
    {
        if (in_array($name, ['search', 'filterStatus'])) {
            $this->resetPage();
        }
    }

    protected function rules(): array
    {
        return [
            'customer_name'   => ['required', 'string', 'max:255'],
            'phone'           => ['required', 'string', 'max:255'],
            'contact_method'  => ['nullable', 'string', 'max:255'],
            'status'          => ['required', 'in:new,processing,confirmed,canceled'],
            'manager_comment' => ['nullable', 'string'],
        ];
    }

    public function openView(int $id): void
    {
        $lead = Lead::findOrFail($id);
        $this->editingId       = $id;
        $this->customer_name   = $lead->customer_name;
        $this->phone           = $lead->phone;
        $this->contact_method  = $lead->contact_method;
        $this->status          = $lead->status;
        $this->manager_comment = $lead->manager_comment;
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        Lead::findOrFail($this->editingId)->update($data);

        $this->showModal = false;
        session()->flash('success', 'Заявку оновлено');
    }

    public function delete(int $id): void
    {
        Lead::findOrFail($id)->delete();
        session()->flash('success', 'Заявку видалено');
    }

    public function render()
    {
        $leads = Lead::query()
            ->withCount('items')
            ->when($this->search, fn ($q) => $q->where('customer_name', 'like', "%{$this->search}%")
                ->orWhere('phone', 'like', "%{$this->search}%"))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->orderByDesc('id')
            ->paginate(25);

        $current = $this->editingId
            ? Lead::with('items.product')->find($this->editingId)
            : null;

        return view('admin.leads.lead-index', [
            'leads'    => $leads,
            'current'  => $current,
            'statuses' => self::STATUSES,
        ])->layout('admin.layouts.admin');
    }
}
