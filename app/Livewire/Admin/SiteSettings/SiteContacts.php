<?php

namespace App\Livewire\Admin\SiteSettings;

use App\Livewire\Concerns\WithAdminToast;
use App\Models\Setting;
use Livewire\Component;

class SiteContacts extends Component
{
    use WithAdminToast;

    public const SOCIALS = [
        'telegram'  => 'Telegram',
        'viber'     => 'Viber',
        'whatsapp'  => 'WhatsApp',
        'instagram' => 'Instagram',
        'facebook'  => 'Facebook',
        'youtube'   => 'YouTube',
        'tiktok'    => 'TikTok',
    ];

    public ?string $phone = null;
    public ?string $phone2 = null;
    public ?string $email = null;
    public ?string $address = null;
    public ?string $mapQuery = null;
    public ?string $mapEmbed = null;

    public array $socials = [];

    public function mount(): void
    {
        $this->phone   = Setting::get('contact_phone', config('site.contacts.phone'));
        $this->phone2  = Setting::get('contact_phone2');
        $this->email   = Setting::get('contact_email', config('site.contacts.email'));
        $this->address = Setting::get('contact_address', config('site.contacts.address'));
        $this->mapQuery = Setting::get('contact_map_query', config('site.contacts.map_query'));
        $this->mapEmbed = Setting::get('contact_map_embed', config('site.contacts.map_embed'));

        foreach (array_keys(self::SOCIALS) as $key) {
            $this->socials[$key] = Setting::get("social_{$key}", config("site.socials.{$key}"));
        }
    }

    protected function rules(): array
    {
        return [
            'phone'     => ['nullable', 'string', 'max:255'],
            'phone2'    => ['nullable', 'string', 'max:255'],
            'email'     => ['nullable', 'email', 'max:255'],
            'address'   => ['nullable', 'string', 'max:500'],
            'mapQuery'  => ['nullable', 'string', 'max:500'],
            'mapEmbed'  => ['nullable', 'string', 'max:2000'],
            'socials.*' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        Setting::set('contact_phone', trim((string) $this->phone) ?: null);
        Setting::set('contact_phone2', trim((string) $this->phone2) ?: null);
        Setting::set('contact_email', trim((string) $this->email) ?: null);
        Setting::set('contact_address', trim((string) $this->address) ?: null);
        Setting::set('contact_map_query', trim((string) $this->mapQuery) ?: null);
        // Приймаємо і повний код <iframe…>, і src - зберігаємо лише чистий URL (укр.).
        Setting::set('contact_map_embed', \App\Support\MapEmbed::src($this->mapEmbed));

        foreach (self::SOCIALS as $key => $label) {
            Setting::set("social_{$key}", trim((string) ($this->socials[$key] ?? '')) ?: null);
        }

        session()->flash('success', 'Контакти сайту збережено');
    }

    public function render()
    {
        return view('admin.site-settings.contacts', [
            'socialLabels' => self::SOCIALS,
        ])->layout('admin.layouts.admin');
    }
}
