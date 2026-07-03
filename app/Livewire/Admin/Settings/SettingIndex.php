<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Setting;
use App\Livewire\Concerns\WithAdminToast;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class SettingIndex extends Component
{
    use WithPagination;
    use WithAdminToast;

    public string $search = '';
    public bool $showModal = false;
    public ?string $editingKey = null;   // null = створення

    public string $key = '';
    public ?string $value = null;

    // ── Google Аналітика та реклама ──────────────────────────────
    public ?string $gaId = null;         // GA4 Measurement ID (G-XXXX)
    public ?string $gtmId = null;        // Google Tag Manager (GTM-XXXX)
    public ?string $adsId = null;        // Google Ads (AW-XXXX)
    public ?string $trackingHead = null; // будь-який код у <head> (можна вставити цілий скрипт)

    public function mount(): void
    {
        $this->gaId         = Setting::get('ga_measurement_id');
        $this->gtmId        = Setting::get('gtm_container_id');
        $this->adsId        = Setting::get('google_ads_id');
        $this->trackingHead = Setting::get('tracking_head_code');
    }

    /** Зберегти налаштування аналітики/реклами (порожнє поле — очищає ключ). */
    public function saveAnalytics(): void
    {
        Setting::set('ga_measurement_id', trim((string) $this->gaId) ?: null);
        Setting::set('gtm_container_id', trim((string) $this->gtmId) ?: null);
        Setting::set('google_ads_id', trim((string) $this->adsId) ?: null);
        Setting::set('tracking_head_code', $this->trackingHead ?: null);

        session()->flash('success', 'Налаштування аналітики збережено');
    }

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
        // find()->delete() (а не масовий where-delete) — щоб спрацював подієвий
        // хук моделі й скинув кеш налаштувань у пам'яті запиту.
        Setting::find($key)?->delete();
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
