<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Setting;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class SettingIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public ?string $editingKey = null;   // null = створення

    public string $key = '';
    public ?string $value = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    protected function rules(): array
    {
        return [
            'key' => [
                'required', 'string', 'max:255',
                Rule::unique('settings', 'key')->ignore($this->editingKey, 'key'),
            ],
            'value' => ['nullable', 'string'],
        ];
    }

    public function openCreate(): void
    {
        $this->reset('key', 'value', 'editingKey');
        $this->showModal = true;
    }

    public function openEdit(string $key): void
    {
        $setting = Setting::findOrFail($key);
        $this->editingKey = $key;
        $this->key   = $setting->key;
        $this->value = $setting->value;
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        // ключ — первинний ключ, тому при редагуванні він незмінний
        Setting::updateOrCreate(
            ['key' => $this->editingKey ?? $data['key']],
            ['value' => $data['value']]
        );

        $this->showModal = false;
        $this->reset('key', 'value', 'editingKey');
        session()->flash('success', 'Збережено');
    }

    public function delete(string $key): void
    {
        Setting::where('key', $key)->delete();
        session()->flash('success', 'Видалено');
    }

    public function render()
    {
        $settings = Setting::query()
            ->when($this->search, fn ($q) => $q->where('key', 'like', "%{$this->search}%"))
            ->orderBy('key')
            ->paginate(30);

        return view('admin.settings.setting-index', compact('settings'))
            ->layout('admin.layouts.admin');
    }
}
