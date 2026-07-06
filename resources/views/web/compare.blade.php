{{-- resources/views/web/compare.blade.php — порівняння шин --}}
@extends('layouts.app')

@section('title', ($heading ?? 'Порівняння') . ' — Велика Шина')

@section('content')
<section class="section compare"
    x-data="{
        onlyDiff: false,
        restripe() {
            const table = $root.querySelector('.compare-table');
            if (!table) return;
            table.classList.add('is-js-zebra');
            const rows = [...table.querySelectorAll('tbody tr')]
                .filter(r => r.offsetParent !== null);
            rows.forEach((r, i) => r.classList.toggle('is-alt', i % 2 === 0));
        }
    }"
    x-init="$store.compare.seed(@js($cards))"
    x-effect="onlyDiff; $nextTick(() => restripe())">
    <div class="container">
        <nav class="breadcrumbs">
            <a href="{{ route('home') }}">Головна</a>
            <span class="sep">/</span>
            <span class="current">Порівняння</span>
        </nav>

        <div class="compare__head">
            <h1 class="page-title">{{ $heading ?? 'Порівняння' }}</h1>
            @if ($products->isNotEmpty())
            <label class="compare__toggle">
                <input type="checkbox" x-model="onlyDiff">
                <span>Лише відмінності</span>
            </label>
            @endif
        </div>

        @if ($products->isEmpty())
        <div class="cart-empty">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <path d="m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/>
                <path d="m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/>
                <path d="M7 21h10"/>
                <path d="M12 3v18"/>
                <path d="M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2"/>
            </svg>
            <p>Ви ще не додали шини до порівняння.<br>Натисніть значок порівняння на картці товару в каталозі.</p>
            <a href="{{ route('catalog') }}" class="btn btn--primary">Перейти до каталогу</a>
        </div>
        @else
        @php($cur = config('site.contacts'))
        <div class="compare-table-wrap">
            <table class="compare-table">
                <thead>
                    <tr>
                        <th class="compare-table__corner">Характеристика</th>
                        @foreach ($cards as $c)
                        @php($otherIds = collect($cards)->pluck('id')->reject(fn ($id) => $id === $c['id'])->implode(','))
                        <th class="compare-col">
                            <a href="{{ route('compare', ['ids' => $otherIds]) ?: route('catalog') }}"
                                class="compare-col__remove" @click="$store.compare.remove({{ $c['id'] }})"
                                aria-label="Прибрати з порівняння" title="Прибрати">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18" />
                                    <line x1="6" y1="6" x2="18" y2="18" />
                                </svg>
                            </a>
                            <a href="{{ $c['url'] ?? '#' }}" class="compare-col__media">
                                <img src="{{ $c['img_url'] ?: '/images/svg/tehnics/wheel.svg' }}" alt="{{ $c['brand'] }}" loading="lazy" />
                            </a>
                            <a href="{{ $c['url'] ?? '#' }}" class="compare-col__title">
                                {{ trim(($c['type'] ?? '') . ' ' . ($c['size'] ?? '')) }}
                            </a>
                            <div class="compare-col__brand"><b>{{ $c['brand'] }}</b> {{ $c['model'] }}</div>
                            {{-- Ціна + компактна кнопка кошика (як у каталозі).
                                 «Переглянути» прибрано — картка кликабельна через фото/назву. --}}
                            <div class="compare-col__buyline" x-data="{ item: @js($c) }">
                                <div class="compare-col__price">
                                    @if (($c['price_mode'] ?? '') === 'fixed' || ($c['price_mode'] ?? '') === 'from')
                                        @if ($c['price_mode'] === 'from')<span class="from">від</span> @endif
                                        {{ number_format($c['price'], 0, '', ' ') }} {{ $c['cur'] ?? 'грн' }}
                                    @else
                                        <span class="ask">Уточнюйте ціну</span>
                                    @endif
                                </div>
                                <button type="button" class="compare-col__cart" @click="$store.cart.add(item)"
                                    aria-label="Додати в кошик" title="Додати в кошик">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="9" cy="21" r="1" /><circle cx="20" cy="21" r="1" />
                                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                                    </svg>
                                </button>
                            </div>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                    <tr class="{{ $row['differs'] ? 'is-diff' : '' }}"
                        @if (!$row['differs']) x-show="!onlyDiff" x-cloak @endif>
                        <th class="compare-table__label">{{ $row['label'] }}</th>
                        @foreach ($row['values'] as $val)
                        <td>{{ $val ?? '—' }}</td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="compare__foot">
            <a href="{{ route('catalog') }}" class="btn btn--outline">Додати ще шини</a>
            <a href="tel:{{ $cur['phone_href'] }}" class="btn btn--dark">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                </svg>
                Потрібна консультація?
            </a>
        </div>
        @endif
    </div>
</section>
@endsection
