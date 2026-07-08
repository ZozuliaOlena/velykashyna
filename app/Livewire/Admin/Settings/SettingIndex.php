<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Setting;
use App\Livewire\Concerns\WithAdminToast;
use App\Services\TelegramNotifier;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class SettingIndex extends Component
{
    use WithPagination;
    use WithAdminToast;

    public string $search = '';
    public bool $showModal = false;
    public ?string $editingKey = null;

    public string $key = '';
    public ?string $value = null;

    public ?string $gaId = null;
    public ?string $gtmId = null;
    public ?string $adsId = null;
    public ?string $trackingHead = null;

    public ?string $tgBotToken = null;
    public ?string $tgChatIds = null;

    public function mount(): void
    {
        $this->gaId         = Setting::get('ga_measurement_id');
        $this->gtmId        = Setting::get('gtm_container_id');
        $this->adsId        = Setting::get('google_ads_id');
        $this->trackingHead = Setting::get('tracking_head_code');
        $this->tgBotToken   = Setting::get('telegram_bot_token');
        $this->tgChatIds    = Setting::get('telegram_chat_ids');
    }

    public function saveTelegram(): void
    {
        Setting::set('telegram_bot_token', trim((string) $this->tgBotToken) ?: null);
        Setting::set('telegram_chat_ids', trim((string) $this->tgChatIds) ?: null);

        session()->flash('success', 'Налаштування Telegram збережено');
    }

    public function sendTelegramTest(): void
    {
        $this->saveTelegram();

        $sent = app(TelegramNotifier::class)->sendTest();

        if ($sent > 0) {
            session()->flash('success', "Тест надіслано (отримувачів: {$sent})");
        } else {
            session()->flash('error', 'Не вдалося надіслати. Перевірте токен і ID, і що кожен отримувач написав боту /start.');
        }
    }

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
