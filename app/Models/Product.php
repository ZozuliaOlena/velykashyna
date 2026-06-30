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
        'description', 'expert_note',
        'stock_status', 'price_mode', 'price', 'currency', 'exchange_rate',
        'discount_value', 'discount_type', 'is_promo', 'free_shipping', 'merchant_enabled',
        'condition',
        'seo_title', 'seo_description', 'seo_h1', 'slug', 'is_active',
    ];

    protected $casts = [
        'merchant_enabled' => 'boolean',
        'is_active' => 'boolean',
        'is_promo' => 'boolean',
        'free_shipping' => 'boolean',
        'price' => 'decimal:2',
        'size_width' => 'decimal:2',
        'size_profile' => 'decimal:2',
        'rim_diameter' => 'decimal:2',
    ];

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

    /** Підпис знижки: "-10%" або "-50 грн". */
    public function discountLabel(): ?string
    {
        if (! $this->hasDiscount()) {
            return null;
        }

        $value = rtrim(rtrim(number_format((float) $this->discount_value, 2, '.', ''), '0'), '.');
        $cur = $this->currency === 'UAH' ? 'грн' : $this->currency;

        return $this->discount_type === 'percent'
            ? "-{$value}%"
            : "-{$value} {$cur}";
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

        $norm = mb_strtolower(str_replace(' ', '', $term));

        // Якщо запит схожий на назву типу («шини», «диск») — додаємо ці типи.
        $typeCodes = [];
        foreach (self::TYPE_SEARCH_STEMS as $stem => $code) {
            if (str_contains($norm, $stem)) {
                $typeCodes[$code] = true;
            }
        }
        $typeCodes = array_keys($typeCodes);

        return $query->where(function ($w) use ($term, $norm, $typeCodes) {
            $w->where('sku', 'like', "%{$term}%")
                ->orWhere('model', 'like', "%{$term}%")
                ->orWhereRaw("REPLACE(LOWER(size_raw), ' ', '') LIKE ?", ['%' . $norm . '%'])
                ->orWhereHas('brand', fn ($b) => $b->where('name', 'like', "%{$term}%"));

            if ($typeCodes) {
                $w->orWhereHas('productType', fn ($t) => $t->whereIn('code', $typeCodes));
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
            'spec' => $this->specification ?? '',
            'li' => $this->load_speed_index ?? '',
            'app' => $type?->name ?? '',
            'app_icon_url' => $type?->iconUrl(),
            'stock' => $this->stock_status === 'in_stock',
            'img_url' => $this->thumbUrl(),
            'price_mode' => $this->price_mode,
            'price' => $this->effectivePrice(),
            'old_price' => $this->oldPrice(),
            // У картці показуємо бейдж лише для відсоткової знижки («-20%»),
            // суму («-500 грн») не показуємо — є стара/нова ціна.
            'discount' => $this->discount_type === 'percent' ? $this->discountLabel() : null,
            'save' => $this->savedAmount(),
            'cur' => $this->currency === 'UAH' ? 'грн' : $this->currency,
            'promos' => $this->cardPromos(),
        ];
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
