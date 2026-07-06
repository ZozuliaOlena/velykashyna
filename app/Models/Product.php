<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Support\Translit;
use Spatie\Image\Enums\Fit;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use SoftDeletes, HasSlug, InteractsWithMedia;

    protected $fillable = [
        'sku', 'product_type_id', 'name', 'brand_id', 'model', 'catalog_image_id',
        'size_raw', 'size_width', 'size_profile', 'rim_diameter',
        'rd_type', 'tube_type', 'ply_rating', 'load_speed_index', 'specification',
        'description', 'description_blocks', 'expert_note',
        'stock_status', 'price_mode', 'price', 'currency', 'exchange_rate',
        'discount_value', 'discount_type', 'is_promo', 'free_shipping', 'merchant_enabled',
        'condition',
        'seo_title', 'seo_description', 'seo_h1', 'slug', 'is_active',
    ];

    protected $casts = [
        'description_blocks' => 'array',
        'merchant_enabled' => 'boolean',
        'is_active' => 'boolean',
        'is_promo' => 'boolean',
        'free_shipping' => 'boolean',
        'price' => 'decimal:2',
        'size_width' => 'decimal:2',
        'size_profile' => 'decimal:2',
        'rim_diameter' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        // Тримаємо size_digits синхронізованим — лише цифри з типорозміру.
        static::saving(function (self $product) {
            $product->size_digits = $product->size_raw
                ? (preg_replace('/\D+/', '', $product->size_raw) ?: null)
                : null;
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        // Авто-генерація з назви лише при створенні (якщо slug не заданий явно).
        // На оновленні slug не чіпаємо — ним керує форма (редагований URL).
        return SlugOptions::create()
            ->generateSlugsFrom(fn (self $model) => Translit::uk($model->name))
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function registerMediaCollections(): void
    {
        // одне основне фото
        $this->addMediaCollection('main')->singleFile();
        // кілька додаткових
        $this->addMediaCollection('gallery');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // Уніфікація розміру/пропорції: вписуємо у квадрат на білому тлі.
        // Реєструємо лише за наявності графічного драйвера (gd/imagick),
        // щоб завантаження не падало в середовищах без нього.
        if (! extension_loaded('gd') && ! extension_loaded('imagick')) {
            return;
        }

        $this->addMediaConversion('uniform')
            ->fit(Fit::Contain, 800, 800)
            ->background('ffffff')
            ->nonQueued();

        $this->addMediaConversion('thumb')
            ->fit(Fit::Contain, 300, 300)
            ->background('ffffff')
            ->nonQueued();
    }

    /**
     * Товари, що потрапляють у Google Merchant фід:
     * активні, Merchant=ON, з фіксованою ціною (режими «від»/«уточнюйте»
     * виключені) та з брендом. Єдине джерело правди для фіда й статусу в адмінці.
     */
    public function scopeForMerchantFeed($query)
    {
        return $query->where('is_active', true)
            ->where('merchant_enabled', true)
            ->where('price_mode', 'fixed')
            ->whereNotNull('price')
            ->whereHas('brand');
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    public function machineryCompatibility(): HasMany
    {
        return $this->hasMany(ProductMachineryCompatibility::class);
    }

    /** Фото товару «в роботі» (встановлені/інспектовані шини на техніці). */
    public function fieldPhotos(): HasMany
    {
        return $this->hasMany(ProductFieldPhoto::class)->orderBy('sort_order')->orderByDesc('id');
    }

    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'related_products',
            'product_id',
            'related_product_id'
        )->withPivot('type');
    }

    public function relatedTo(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'related_products',
            'related_product_id',
            'product_id'
        )->withPivot('type');
    }

    public function leadItems(): HasMany
    {
        return $this->hasMany(LeadItem::class);
    }

    public function catalogImage(): BelongsTo
    {
        return $this->belongsTo(CatalogImage::class);
    }

    /**
     * URL прев'ю товару: спершу власне «живе» фото (main → gallery),
     * інакше — спільне каталожне фото. null, якщо немає жодного.
     */
    public function thumbUrl(): ?string
    {
        $media = $this->getFirstMedia('main') ?: $this->getFirstMedia('gallery');
        if ($media) {
            return \App\Support\MediaUrl::rel(
                $media->hasGeneratedConversion('thumb')
                    ? $media->getUrl('thumb')
                    : $media->getUrl()
            );
        }

        return $this->catalogImage?->imageUrl('thumb');
    }

    /**
     * Ціна з урахуванням знижки на поточний момент.
     * Для режиму «за запитом» (inquiry) або без ціни повертає null —
     * саме це значення фіксується в заявці як price_at_request.
     */
    public function effectivePrice(): ?float
    {
        if ($this->price_mode === 'inquiry' || $this->price === null) {
            return null;
        }

        $price = (float) $this->price;

        if ($this->discount_value) {
            if ($this->discount_type === 'percent') {
                $price -= $price * ((float) $this->discount_value) / 100;
            } elseif ($this->discount_type === 'amount') {
                $price -= (float) $this->discount_value;
            }
        }

        return round(max(0, $price), 2);
    }

    /** Чи діє знижка (є ціна, валідний тип і ефективна ціна нижча за базову). */
    public function hasDiscount(): bool
    {
        if ($this->price_mode === 'inquiry' || $this->price === null) {
            return false;
        }
        if (! $this->discount_value || ! in_array($this->discount_type, ['percent', 'amount'], true)) {
            return false;
        }

        $eff = $this->effectivePrice();
        return $eff !== null && $eff < (float) $this->price;
    }

    /** Стара (закреслена) ціна — лише коли діє знижка. */
    public function oldPrice(): ?float
    {
        return $this->hasDiscount() ? round((float) $this->price, 2) : null;
    }

    /**
     * Конвертація суми у гривні за курсом товару (exchange_rate).
     * Сайт показує лише грн, тож ціни у валюті (USD/EUR) перераховуються
     * автоматично. Курс масово виставляється в адмінці для всіх товарів
     * обраної валюти. Фід конвертує окремо (feedPrice), тому effectivePrice()
     * лишаємо у валюті товару — інакше було б подвійне множення на курс.
     */
    public function toUah(?float $amount): ?float
    {
        if ($amount === null) {
            return null;
        }

        if ($this->currency && $this->currency !== 'UAH') {
            $rate = (float) ($this->exchange_rate ?? 0);

            // Валютний товар без курсу — коректно перерахувати не можемо,
            // тож повертаємо null (сайт покаже «Уточнюйте ціну»).
            return $rate > 0 ? round($amount * $rate, 2) : null;
        }

        return round($amount, 2);
    }

    /** Ефективна ціна вже у гривнях (для відображення на сайті). */
    public function priceUah(): ?float
    {
        return $this->toUah($this->effectivePrice());
    }

    /** Стара (закреслена) ціна у гривнях. */
    public function oldPriceUah(): ?float
    {
        return $this->toUah($this->oldPrice());
    }

    /**
     * Режим ціни для сайту. Якщо товар у валюті, але курс не заданий —
     * показуємо «уточнюйте» замість некоректної гривневої ціни.
     */
    public function priceModeForSite(): string
    {
        if (in_array($this->price_mode, ['fixed', 'from'], true)
            && $this->effectivePrice() !== null
            && $this->priceUah() === null) {
            return 'inquiry';
        }

        return $this->price_mode;
    }

    /** Підпис знижки: "-10%" або "-50 грн". */
    public function discountLabel(): ?string
    {
        if (! $this->hasDiscount()) {
            return null;
        }

        if ($this->discount_type === 'percent') {
            $value = rtrim(rtrim(number_format((float) $this->discount_value, 2, '.', ''), '0'), '.');

            return "-{$value}%";
        }

        // Сума знижки — переводимо у гривні (сайт лише в грн).
        $amount = $this->toUah((float) $this->discount_value);
        $value = rtrim(rtrim(number_format((float) $amount, 2, '.', ''), '0'), '.');

        return "-{$value} грн";
    }

    /**
     * Пошук за змістовними полями: артикул, бренд, типорозмір, модель.
     * `name` навмисно НЕ шукаємо (там трапляються індекси навантаження
     * на кшталт «123A8», які давали хибні збіги). Розмір порівнюємо
     * без пробілів і регістру, тож «800/65r32» = «800/65 R32».
     */
    /** Стем-синоніми → код типу товару (для пошуку «шини», «диски», «камери»…). */
    private const TYPE_SEARCH_STEMS = [
        'шин' => 'tire', 'покрышк' => 'tire',
        'диск' => 'disk', 'обід' => 'disk', 'обод' => 'disk',
        'камер' => 'tube',
        'вентил' => 'valve', 'ніпел' => 'valve', 'нипел' => 'valve',
        'флап' => 'flap', 'ободн' => 'flap',
        'кільц' => 'ring', 'кольц' => 'ring',
    ];

    public function scopeSearch($query, string $term)
    {
        $term = trim($term);
        if ($term === '') {
            return $query;
        }

        // Розбиваємо запит на слова — КОЖНЕ має знайтись (AND між словами),
        // але кожне — у будь-якому з полів (OR всередині слова). Завдяки цьому
        // працюють складені запити: «BKT Agrimax Procrop», «Steel Belted TL».
        $words = preg_split('/\s+/', $term, -1, PREG_SPLIT_NO_EMPTY);

        return $query->where(function ($outer) use ($words) {
            foreach ($words as $word) {
                $norm = mb_strtolower(str_replace([' ', '-'], '', $word));
                // Лише цифри — для пошуку типорозміру, стійкого до розділювачів
                // (/, R, VF-префікс, пробіли, дефіси): «VF270/95R32», «270-95-32»,
                // «270 95 32», «2709532», «vf2709532» → «2709532».
                $digits = preg_replace('/\D+/', '', $word);

                // Тип за стемом («шини» → tire тощо).
                $typeCodes = [];
                foreach (self::TYPE_SEARCH_STEMS as $stem => $code) {
                    if (str_contains($norm, $stem)) {
                        $typeCodes[$code] = true;
                    }
                }
                $typeCodes = array_keys($typeCodes);

                $outer->where(function ($w) use ($word, $norm, $digits, $typeCodes) {
                    $w->where('sku', 'like', "%{$word}%")
                        ->orWhere('model', 'like', "%{$word}%")
                        ->orWhere('load_speed_index', 'like', "%{$word}%")
                        ->orWhere('specification', 'like', "%{$word}%")
                        ->orWhere('tube_type', 'like', "%{$word}%")
                        // Розмір — без пробілів/дефісів і регістру.
                        ->orWhereRaw("REPLACE(REPLACE(LOWER(size_raw), ' ', ''), '-', '') LIKE ?", ['%' . $norm . '%'])
                        ->orWhereHas('brand', fn ($b) => $b->where('name', 'like', "%{$word}%"))
                        // Тип товару: за назвою («Ущільнювальне», «кільце»)
                        // або за стемом («шини» → tire).
                        ->orWhereHas('productType', function ($t) use ($word, $typeCodes) {
                            $t->where('name', 'like', "%{$word}%");
                            if ($typeCodes) {
                                $t->orWhereIn('code', $typeCodes);
                            }
                        });

                    // Розмір «лише цифрами» (окрема нормалізована колонка).
                    if (strlen($digits) >= 2) {
                        $w->orWhere('size_digits', 'like', "%{$digits}%");
                    }
                });
            }
        });
    }

    /**
     * Камерність шини: TL → «Безкамерна», TT → «Камерна».
     * Радіальна/Діагональна не пишемо — це видно з типорозміру
     * ("-" = діагональна, "R" = радіальна).
     */
    public function constructionLabel(): string
    {
        return match ($this->tube_type) {
            'TL' => 'Безкамерна',
            'TT' => 'Камерна',
            default => '',
        };
    }

    /**
     * Повне найменування товару одним рядком:
     * «Шина 800/65R32 BKT AGRIMAX TERIS 178A8/175B 16PR STEEL BELTED TL».
     * Складається з усіх ключових полів (порожні пропускаються).
     */
    public function fullName(): string
    {
        $parts = [
            $this->productType?->name,
            $this->size_raw,
            $this->brand?->name,
            $this->model,
            $this->load_speed_index,
            $this->ply_rating ? $this->ply_rating . 'PR' : null,
            $this->specification,
            $this->tube_type,
        ];

        return trim(preg_replace('/\s+/', ' ', implode(' ', array_filter($parts))));
    }

    /**
     * Автоматичні SEO-теги з даних товару (title / description / h1).
     * Використовується для авто-заповнення порожніх SEO-полів в адмінці.
     *
     * @return array{title: string, description: string, h1: string}
     */
    public function defaultSeo(): array
    {
        $full = $this->fullName() ?: $this->name;

        $title = trim($full . ' — купити в Україні | Велика Шина');

        $descParts = array_filter([
            'Купити ' . $full,
            $this->constructionLabel() ?: null,
            $this->load_speed_index ? 'індекс ' . $this->load_speed_index : null,
        ]);
        $desc = trim(implode(', ', $descParts))
            . '. Вигідна ціна, доставка по всій Україні, консультація та підбір. «Велика Шина».';

        return [
            'title'       => mb_substr($title, 0, 255),
            'description' => mb_substr(trim(preg_replace('/\s+/', ' ', $desc)), 0, 300),
            'h1'          => $full,
        ];
    }

    /** Промо-бейджі для картки (узгоджено з partials/product-card). */
    public function cardPromos(): array
    {
        $promos = [];
        if ($this->is_promo) {
            $promos[] = 'Акція';
        }
        if ($this->hasDiscount()) {
            $promos[] = 'Знижка';
        }
        if ($this->free_shipping) {
            $promos[] = 'Безкоштовна доставка';
        }

        return $promos;
    }

    /**
     * Нормалізація товару у масив для partials/product-card.blade.php.
     * Очікує (бажано) завантажені зв'язки brand, catalogImage,
     * machineryCompatibility.machineryType — щоб уникнути N+1.
     */
    public function toCard(): array
    {
        $compat = $this->machineryCompatibility->first();
        $type = $compat?->machineryType;

        return [
            'id' => $this->id,
            'sku' => $this->sku ?? '',
            'slug' => $this->slug,
            'url' => $this->slug ? route('product', $this->slug) : null,
            'type' => $this->productType?->name ?? '',
            'type_code' => $this->productType?->code ?? '',
            'size' => $this->size_raw ?? '',
            'brand' => $this->brand?->name ?? '',
            'brand_logo_url' => $this->brand?->logoUrl(),
            'model' => $this->model ?? '',
            'constr' => $this->constructionLabel(),
            'tube' => $this->tube_type ?? '',
            'spec' => $this->specification ?? '',
            'li' => $this->load_speed_index ?? '',
            'app' => $type?->name ?? '',
            'app_icon_url' => $type?->iconUrl(),
            'stock' => $this->stock_status === 'in_stock',
            'stock_status' => $this->stock_status,
            'stock_label' => $this->stockLabel(),
            'img_url' => $this->thumbUrl(),
            'price_mode' => $this->priceModeForSite(),
            // Ціни на сайті — завжди у гривнях (валютні перераховуються за курсом).
            'price' => $this->priceUah(),
            'old_price' => $this->oldPriceUah(),
            // У картці показуємо бейдж лише для відсоткової знижки («-20%»),
            // суму («-500 грн») не показуємо — є стара/нова ціна.
            'discount' => $this->discount_type === 'percent' ? $this->discountLabel() : null,
            'save' => $this->toUah($this->savedAmount()),
            'cur' => 'грн',
            'promos' => $this->cardPromos(),
        ];
    }

    /** Підпис наявності: В наявності / Під замовлення / Уточнюйте. */
    public function stockLabel(): string
    {
        return match ($this->stock_status) {
            'in_stock' => 'В наявності',
            'on_order' => 'Під замовлення',
            'inquiry' => 'Уточнюйте',
            default => 'Під замовлення',
        };
    }

    /** Сума економії (стара ціна − поточна), заокруглена; null без знижки. */
    public function savedAmount(): ?float
    {
        $old = $this->oldPrice();
        if ($old === null) {
            return null;
        }

        return max(0, round($old - (float) $this->effectivePrice()));
    }
}
