<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Setting;
use App\Livewire\Concerns\WithAdminToast;
use App\Services\TelegramNotifier;
use App\Support\MailSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class SettingIndex extends Component
{
    use WithPagination;
    use WithFileUploads;
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

    // Пошта (SMTP)
    public ?string $mailHost = null;
    public ?string $mailPort = null;
    public ?string $mailUsername = null;
    public ?string $mailPassword = null;   // порожнє = не міняти збережений
    public ?string $mailEncryption = 'ssl';
    public ?string $mailFromAddress = null;
    public ?string $mailFromName = null;
    public bool $mailPasswordSet = false;

    // SEO / прев'ю при поширенні
    public ?string $shareTitle = null;
    public ?string $shareDescription = null;
    public ?string $shareImage = null;   // збережений шлях
    public ?string $favicon = null;      // збережений шлях
    public $shareImageUpload = null;     // тимчасове завантаження
    public $faviconUpload = null;

    public ?string $googleSiteVerification = null;
    public bool $siteNoindex = false;

    public function mount(): void
    {
        $this->gaId         = Setting::get('ga_measurement_id');
        $this->gtmId        = Setting::get('gtm_container_id');
        $this->adsId        = Setting::get('google_ads_id');
        $this->trackingHead = Setting::get('tracking_head_code');
        $this->tgBotToken   = Setting::get('telegram_bot_token');
        $this->tgChatIds    = Setting::get('telegram_chat_ids');

        $this->shareTitle       = Setting::get('share_title');
        $this->shareDescription = Setting::get('share_description');
        $this->shareImage       = Setting::get('share_image');
        $this->favicon          = Setting::get('favicon');
        $this->googleSiteVerification = Setting::get('google_site_verification');
        $this->siteNoindex      = (bool) Setting::get('site_noindex');

        $this->mailHost        = Setting::get('mail_host');
        $this->mailPort        = Setting::get('mail_port');
        $this->mailUsername    = Setting::get('mail_username');
        $this->mailEncryption  = Setting::get('mail_encryption') ?: 'ssl';
        $this->mailFromAddress = Setting::get('mail_from_address');
        $this->mailFromName    = Setting::get('mail_from_name');
        $this->mailPasswordSet = (bool) Setting::get('mail_password');
    }

    public function saveMail(): void
    {
        $this->validate([
            'mailHost'        => ['nullable', 'string', 'max:255'],
            'mailPort'        => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mailUsername'    => ['nullable', 'string', 'max:255'],
            'mailPassword'    => ['nullable', 'string', 'max:255'],
            'mailEncryption'  => ['required', 'in:ssl,tls,none'],
            'mailFromAddress' => ['nullable', 'email', 'max:255'],
            'mailFromName'    => ['nullable', 'string', 'max:255'],
        ]);

        Setting::set('mail_host', trim((string) $this->mailHost) ?: null);
        Setting::set('mail_port', trim((string) $this->mailPort) ?: null);
        Setting::set('mail_username', trim((string) $this->mailUsername) ?: null);
        Setting::set('mail_encryption', $this->mailEncryption ?: 'ssl');
        Setting::set('mail_from_address', trim((string) $this->mailFromAddress) ?: null);
        Setting::set('mail_from_name', trim((string) $this->mailFromName) ?: null);

        // Пароль оновлюємо лише якщо введено новий (зберігаємо зашифрованим).
        if (filled($this->mailPassword)) {
            Setting::set('mail_password', Crypt::encryptString($this->mailPassword));
        }
        $this->reset('mailPassword');
        $this->mailPasswordSet = (bool) Setting::get('mail_password');

        MailSettings::apply();
        session()->flash('success', 'Налаштування пошти збережено');
    }

    public function sendMailTest(): void
    {
        $this->saveMail();
        MailSettings::apply();

        $to = Auth::user()->email;

        try {
            Mail::raw(
                'Це тестовий лист від сайту «ВЕЛИКА ШИНА». Якщо ви його отримали - пошта налаштована правильно.',
                fn ($m) => $m->to($to)->subject('Тест пошти - ВЕЛИКА ШИНА')
            );
            session()->flash('success', "Тестовий лист надіслано на {$to}");
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Не вдалося надіслати: ' . $e->getMessage());
        }
    }

    public function saveSharing(): void
    {
        $this->validate([
            'shareTitle'       => ['nullable', 'string', 'max:255'],
            'shareDescription' => ['nullable', 'string', 'max:500'],
            'shareImageUpload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'faviconUpload'    => ['nullable', 'file', 'mimes:png,svg,ico', 'max:1024'],
        ]);

        Setting::set('share_title', trim((string) $this->shareTitle) ?: null);
        Setting::set('share_description', trim((string) $this->shareDescription) ?: null);
        Setting::set('google_site_verification', trim((string) $this->googleSiteVerification) ?: null);
        Setting::set('site_noindex', $this->siteNoindex ? '1' : null);

        if ($this->shareImageUpload) {
            $path = \App\Support\ImageOptimizer::toWebp($this->shareImageUpload, 'site');
            $this->shareImage = '/storage/' . $path;
            Setting::set('share_image', $this->shareImage);
            $this->reset('shareImageUpload');
        }

        if ($this->faviconUpload) {
            $path = $this->faviconUpload->store('site', 'public');
            $this->favicon = '/storage/' . $path;
            Setting::set('favicon', $this->favicon);
            $this->reset('faviconUpload');
        }

        session()->flash('success', 'Налаштування прев\'ю збережено');
    }

    public function deleteShareImage(): void
    {
        Setting::set('share_image', null);
        $this->shareImage = null;
        session()->flash('success', 'Картинку скинуто на стандартну');
    }

    public function deleteFavicon(): void
    {
        Setting::set('favicon', null);
        $this->favicon = null;
        session()->flash('success', 'Фавіконку скинуто на стандартну');
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
