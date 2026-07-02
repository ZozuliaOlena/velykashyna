@php
    $cur = 'грн'; // сайт/PDF — завжди у гривнях (валютні перераховуються за курсом)
    $stock = match ($product->stock_status) {
        'in_stock' => 'В наявності',
        'on_order' => 'Під замовлення',
        default => 'Уточнюйте',
    };
    // Рядки характеристик (label => value), порожні пропускаємо.
    $specs = array_filter([
        'Артикул' => $product->sku,
        'Виробник' => $product->brand?->name,
        'Типорозмір' => $product->size_raw,
        'Протектор' => $product->model,
        'Конструкція' => $product->constructionLabel() ?: null,
        'Шар (PR)' => $product->ply_rating,
        'Індекс' => $product->load_speed_index,
        'Специфікація' => $product->specification,
        'Наявність' => $stock,
    ]);
    $pref = $product->price_mode === 'from' ? 'від ' : '';
@endphp
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    * { font-family: "DejaVu Sans", sans-serif; }
    body { margin: 0; color: #1a1a1a; font-size: 12px; }
    .head { border-bottom: 3px solid #d32f2f; padding-bottom: 8px; margin-bottom: 14px; }
    .logo { height: 38px; width: auto; }
    .brand { color: #d32f2f; font-size: 22px; font-weight: bold; }
    .head-contacts { color: #555; font-size: 10px; }
    h1 { font-size: 17px; margin: 0 0 12px; }
    td { vertical-align: top; }
    .photo-cell { width: 230px; padding-right: 16px; }
    /* dompdf не підтримує object-fit: фіксуємо лише ширину, висота — авто
       (аспект зберігається; квадратне «uniform»-фото лишається 220×220). */
    .photo { width: 220px; height: auto; border: 1px solid #e0e0e0; }
    .spec-row td { padding: 4px 0; border-bottom: 1px solid #eee; font-size: 12px; }
    .spec-label { color: #777; width: 130px; }
    .spec-val { font-weight: bold; }
    .price-box { margin-top: 12px; padding: 10px 12px; background: #fdecea; border-radius: 6px; }
    .price-now { color: #d32f2f; font-size: 20px; font-weight: bold; }
    .price-old { color: #9aa0aa; text-decoration: line-through; font-size: 13px; }
    .section-title { font-size: 13px; font-weight: bold; margin: 18px 0 6px; color: #d32f2f; }
    .descr { font-size: 12px; line-height: 1.55; }
    .descr h1, .descr h2, .descr h3 { font-size: 13px; margin: 10px 0 4px; }
    .descr ul, .descr ol { margin: 4px 0 8px; padding-left: 18px; }
    .descr li { margin: 2px 0; }
    .descr p, .descr div { margin: 0 0 6px; text-align: justify; }
    .descr a { color: #d32f2f; }
    .machinery { font-size: 12px; line-height: 1.6; }
    .expert { background: #f7f8fa; border-left: 3px solid #d32f2f; padding: 8px 12px; font-size: 12px; line-height: 1.5; text-align: justify; }
    .fp { width: 33%; padding: 4px; vertical-align: top; }
    .fp img { width: 150px; height: 150px; border: 1px solid #e0e0e0; }
    .fp-meta { font-size: 9px; color: #555; }
    .foot { margin-top: 22px; border-top: 1px solid #e0e0e0; padding-top: 8px; color: #555; font-size: 10px; }
</style>
</head>
<body>
    <table width="100%" class="head"><tr>
        <td>
            @if($logo)
                <img src="{{ $logo }}" class="logo" alt="Велика Шина">
            @else
                <span class="brand">ВЕЛИКА ШИНА</span>
            @endif
        </td>
        <td align="right" class="head-contacts">
            @if(!empty($contacts['phone'])){{ $contacts['phone'] }}<br>@endif
            velykashyna.com.ua
        </td>
    </tr></table>

    <h1>{{ $product->name }}</h1>

    <table width="100%"><tr>
        <td class="photo-cell">
            @if($photo)
                <img src="{{ $photo }}" class="photo" alt="">
            @endif
        </td>
        <td>
            <table width="100%">
                @foreach($specs as $label => $value)
                    <tr class="spec-row">
                        <td class="spec-label">{{ $label }}</td>
                        <td class="spec-val">{{ $value }}</td>
                    </tr>
                @endforeach
            </table>

            @if($withPrice)
                <div class="price-box">
                    @if($product->priceModeForSite() === 'inquiry' || $product->price === null)
                        <span class="price-now">Ціна: Уточнюйте</span>
                    @elseif($product->hasDiscount())
                        <span class="price-old">{{ $pref }}{{ $product->oldPriceUah() }} {{ $cur }}</span><br>
                        <span class="price-now">{{ $pref }}{{ $product->priceUah() }} {{ $cur }}</span>
                    @else
                        <span class="price-now">{{ $pref }}{{ $product->toUah((float) $product->price) }} {{ $cur }}</span>
                    @endif
                </div>
            @endif
        </td>
    </tr></table>

    @if($variant === 'full')
        @if($product->description)
            <div class="section-title">Опис товару</div>
            <div class="descr">{!! $product->description !!}</div>
        @endif

        @if(count($machinery))
            <div class="section-title">Сумісна техніка</div>
            <div class="machinery">{{ implode(' · ', $machinery) }}</div>
        @endif

        @if($product->expert_note)
            <div class="section-title">Думка експерта «Велика Шина»</div>
            <div class="expert">{{ $product->expert_note }}</div>
        @endif

        @if(count($fieldPhotos))
            <div style="page-break-inside: avoid">
                <div class="section-title">Фото в роботі</div>
                <table width="100%"><tr>
                    @foreach($fieldPhotos as $i => $fp)
                        <td class="fp">
                            <img src="{{ $fp['img'] }}" alt="">
                            <div class="fp-meta">
                                @if($fp['label'])<strong>{{ $fp['label'] }}</strong>@endif
                                @if($fp['caption']) — {{ $fp['caption'] }}@endif
                            </div>
                        </td>
                        @if(($i + 1) % 3 === 0)</tr><tr>@endif
                    @endforeach
                </tr></table>
            </div>
        @endif
    @endif

    <table width="100%" class="foot"><tr>
        <td>
            @if(!empty($contacts['phone'])){{ $contacts['phone'] }} · @endif
            @if(!empty($contacts['email'])){{ $contacts['email'] }}@endif
            @if(!empty($contacts['address']))<br>{{ $contacts['address'] }}@endif
        </td>
        <td align="right">velykashyna.com.ua</td>
    </tr></table>
</body>
</html>
