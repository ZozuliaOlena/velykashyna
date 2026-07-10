<?php

namespace App\Livewire\Admin\SiteSettings;

use App\Livewire\Concerns\WithAdminToast;
use App\Models\Setting;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Редаговані блоки сайту: галерея «ВЕЛИКА ШИНА в роботі», FAQ,
 * способи доставки та оплати (у кошику). Усе зберігається в settings (JSON).
 */
class SiteContent extends Component
{
    use WithAdminToast;
    use WithFileUploads;

    public const GALLERY_MIN = 4;
    public const GALLERY_MAX = 7;

    /** Значення за замовчуванням (те, що зараз «зашито» на сайті). */
    public const DEFAULT_GALLERY = [
        ['img' => '/images/about/portfolio3.jpg', 'cap' => 'Склад великогабаритних шин'],
        ['img' => '/images/about/portfolio7.jpg', 'cap' => 'Відвантаження клієнту'],
        ['img' => '/images/about/portfolio4.jpg', 'cap' => 'Асортимент у наявності'],
        ['img' => '/images/about/portfolio9.jpg', 'cap' => 'Робота з технікою'],
        ['img' => '/images/about/portfolio6.jpg', 'cap' => 'Готово до відправлення'],
        ['img' => '/images/about/portfolio8.jpg', 'cap' => 'Щоденна робота складу'],
    ];

    public const DEFAULT_FAQS = [
        ['q' => 'Чи доставляєте шини, камери та диски?', 'a' => 'Так - відправляємо по всій Україні (Нова Пошта, транспортні компанії, адресна доставка). Ходові розміри тримаємо в наявності, решту оперативно привозимо.'],
        ['q' => 'Чи є гарантія на продукцію?', 'a' => 'Так. Ми офіційний постачальник і продаємо лише оригінальну продукцію з гарантією від виробника - жодних сумнівних аналогів.'],
        ['q' => 'Чи працюєте з ПДВ?', 'a' => 'Так, працюємо як з ПДВ, так і без. Для юридичних осіб оформлюємо всі необхідні документи.'],
        ['q' => 'Чи можна отримати консультацію телефоном або в месенджерах?', 'a' => 'Звісно. Телефонуйте або пишіть у Viber / Telegram / WhatsApp - підкажемо розмір і підберемо шину під вашу техніку.'],
    ];

    public const DEFAULT_DELIVERY = ['Нова Пошта', 'САТ', "Кур'єр", 'Самовивіз зі складу'];
    public const DEFAULT_PAYMENT = ['Накладений платіж (при отриманні)', 'Оплата за реквізитами (IBAN)'];

    public array $gallery = [];
    public array $faqs = [];
    public array $delivery = [];
    public array $payment = [];

    public $galleryUpload = null;

    public function mount(): void
    {
        $this->gallery  = static::readJson('work_gallery', self::DEFAULT_GALLERY);
        $this->faqs     = static::readJson('faq_items', self::DEFAULT_FAQS);
        $this->delivery = static::readJson('delivery_methods', self::DEFAULT_DELIVERY);
        $this->payment  = static::readJson('payment_methods', self::DEFAULT_PAYMENT);
    }

    /** Читає JSON-налаштування; повертає дефолт, якщо порожньо/зіпсовано. */
    public static function readJson(string $key, array $default): array
    {
        $raw = Setting::get($key);
        if (! $raw) {
            return $default;
        }
        $decoded = json_decode($raw, true);

        return is_array($decoded) && $decoded !== [] ? $decoded : $default;
    }

    public function addFaq(): void
    {
        $this->faqs[] = ['q' => '', 'a' => ''];
    }

    public function removeFaq(int $i): void
    {
        unset($this->faqs[$i]);
        $this->faqs = array_values($this->faqs);
    }

    public function addDelivery(): void
    {
        $this->delivery[] = '';
    }

    public function removeDelivery(int $i): void
    {
        unset($this->delivery[$i]);
        $this->delivery = array_values($this->delivery);
    }

    public function addPayment(): void
    {
        $this->payment[] = '';
    }

    public function removePayment(int $i): void
    {
        unset($this->payment[$i]);
        $this->payment = array_values($this->payment);
    }

    public function updatedGalleryUpload(): void
    {
        $this->validate(['galleryUpload' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:6144']]);

        if (count($this->gallery) >= self::GALLERY_MAX) {
            $this->reset('galleryUpload');
            session()->flash('error', 'Досягнуто максимуму ' . self::GALLERY_MAX . ' фото.');
            return;
        }

        $path = \App\Support\ImageOptimizer::toWebp($this->galleryUpload, 'site-gallery');
        $this->gallery[] = ['img' => '/storage/' . $path, 'cap' => ''];
        $this->reset('galleryUpload');
    }

    public function removeGalleryItem(int $i): void
    {
        unset($this->gallery[$i]);
        $this->gallery = array_values($this->gallery);
    }

    public function moveGallery(int $i, string $dir): void
    {
        $j = $dir === 'up' ? $i - 1 : $i + 1;
        if (isset($this->gallery[$i], $this->gallery[$j])) {
            [$this->gallery[$i], $this->gallery[$j]] = [$this->gallery[$j], $this->gallery[$i]];
        }
    }

    public function saveGallery(): void
    {
        $gallery = array_values(array_filter($this->gallery, fn ($g) => ! empty($g['img'])));

        if (count($gallery) < self::GALLERY_MIN) {
            session()->flash('error', 'Галерея «в роботі»: потрібно щонайменше ' . self::GALLERY_MIN . ' фото.');
            return;
        }

        Setting::set('work_gallery', json_encode($gallery, JSON_UNESCAPED_UNICODE));
        $this->gallery = $gallery;
        session()->flash('success', 'Галерею збережено');
    }

    public function saveFaqs(): void
    {
        $faqs = array_values(array_filter($this->faqs, fn ($f) => trim((string) ($f['q'] ?? '')) !== ''));

        Setting::set('faq_items', json_encode($faqs, JSON_UNESCAPED_UNICODE));
        $this->faqs = $faqs;
        session()->flash('success', 'Часті запитання збережено');
    }

    public function saveDelivery(): void
    {
        $delivery = array_values(array_filter(array_map('trim', $this->delivery), fn ($v) => $v !== ''));

        if (empty($delivery)) {
            session()->flash('error', 'Додайте хоча б один спосіб доставки.');
            return;
        }

        Setting::set('delivery_methods', json_encode($delivery, JSON_UNESCAPED_UNICODE));
        $this->delivery = $delivery;
        session()->flash('success', 'Способи доставки збережено');
    }

    public function savePayment(): void
    {
        $payment = array_values(array_filter(array_map('trim', $this->payment), fn ($v) => $v !== ''));

        if (empty($payment)) {
            session()->flash('error', 'Додайте хоча б один спосіб оплати.');
            return;
        }

        Setting::set('payment_methods', json_encode($payment, JSON_UNESCAPED_UNICODE));
        $this->payment = $payment;
        session()->flash('success', 'Способи оплати збережено');
    }

    public function render()
    {
        return view('admin.site-settings.site-content')->layout('admin.layouts.admin');
    }
}
