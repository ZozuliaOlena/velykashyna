{{-- resources/views/partials/footer.blade.php --}}
@php($c = config('site.contacts'))

{{-- CTA-картка над футером (приховується там, де є власний банер) --}}
@if ($showFooterCta ?? true)
    <div class="container" style="margin-bottom:56px" data-aos="fade-up">
        <div class="footer-cta">
            <div class="fc-text">
                <div class="fc-line"></div>
                <h3>Не впевнені, які шини потрібні?</h3>
                <p>Наші спеціалісти допоможуть <b>підібрати</b> оптимальний варіант для вашої техніки.</p>
                <div class="fc-actions">
                    <a href="{{ route('contacts') }}" class="btn btn--primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path
                                d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                        </svg>
                        Отримати консультацію
                    </a>
                    <a href="{{ route('catalog') }}" class="btn btn--outline">Підібрати шини</a>
                </div>
            </div>
            <div class="fc-media">
                <img src="/images/details/kara.png" alt="ВЕЛИКА ШИНА" loading="lazy" />
            </div>
        </div>
    </div>
@endif

<footer class="footer">
    <div class="container">
        {{-- ДЕСКТОП --}}
        <div class="footer-grid">
            <div class="footer-brand">
                <img src="/images/logo.png" alt="ВЕЛИКА ШИНА" class="f-logo" />
                <p class="f-desc">Підбираємо правильні шини для агро-, спец- та вантажної техніки з
                    {{ config('site.founded_year') }} року.</p>
                @include('partials.rating')
                <div class="footer-contact-line">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                    </svg>
                    <div>
                        <a href="tel:{{ $c['phone_href'] }}" class="fc-strong">{{ $c['phone'] }}</a>
                        @if(!empty($c['phone2']))
                            <a href="tel:{{ $c['phone2_href'] }}" class="fc-strong">{{ $c['phone2'] }}</a>
                        @endif
                        <div class="fc-note">Ми на зв'язку 24/7</div>
                    </div>
                </div>
                <div class="footer-contact-line">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                        <polyline points="22,6 12,13 2,6" />
                    </svg>
                    <a href="mailto:{{ $c['email'] }}">{{ $c['email'] }}</a>
                </div>
                <div class="footer-contact-line">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                        <circle cx="12" cy="10" r="3" />
                    </svg>
                    <span>{{ $c['address'] }}</span>
                </div>
            </div>

            <div class="footer-col">
                <div class="fcol-title">Каталог</div>
                <ul>
                    <li><a href="{{ route('catalog') }}">Усі товари</a></li>
                    @foreach ($catalogMenu['types'] ?? [] as $t)
                    <li><a href="{{ $t['url'] }}">{{ $t['name'] }}</a></li>
                    @endforeach
                    @foreach (array_slice($catalogMenu['machinery'] ?? [], 0, 3) as $m)
                    <li><a href="{{ $m['url'] }}">Шини на {{ mb_strtolower($m['name']) }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div class="footer-col">
                <div class="fcol-title">Компанія</div>
                <ul>
                    <li><a href="{{ route('about') }}">Про нас</a></li>
                    <li><a href="{{ route('pages.delivery') }}">Доставка й оплата</a></li>
                    <li><a href="{{ route('pages.returns') }}">Повернення та обмін</a></li>
                    <li><a href="{{ route('pages.warranty') }}">Гарантія</a></li>
                    <li><a href="{{ route('pages.privacy') }}">Політика конфіденційності</a></li>
                    <li><a href="{{ route('contacts') }}">Контакти</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <div class="fcol-title">Контакти</div>
                <ul>
                    <li><a href="tel:{{ $c['phone_href'] }}">{{ $c['phone'] }}</a></li>
                    @if(!empty($c['phone2']))
                    <li><a href="tel:{{ $c['phone2_href'] }}">{{ $c['phone2'] }}</a></li>
                    @endif
                    <li><a href="mailto:{{ $c['email'] }}">{{ $c['email'] }}</a></li>
                    @if(config('site.socials.telegram'))<li><a href="{{ config('site.socials.telegram') }}">Telegram</a></li>@endif
                    @if(config('site.socials.viber'))<li><a href="{{ config('site.socials.viber') }}">Viber</a></li>@endif
                    @if(config('site.socials.whatsapp'))<li><a href="{{ config('site.socials.whatsapp') }}">WhatsApp</a></li>@endif
                </ul>
            </div>

            <div class="footer-col">
                <div class="fcol-title">Графік роботи</div>
                <ul>
                    <li style="color:#9aa0a8">Ми на зв'язку 24/7</li>
                    <li style="color:#9aa0a8">Замовлення приймаємо цілодобово</li>
                </ul>
            </div>
        </div>

        {{-- МОБІЛЬНИЙ --}}
        <div class="footer-mobile">
            <div class="fm-card">
                <img src="/images/logo.png" alt="ВЕЛИКА ШИНА" class="f-logo" />
                <p class="f-desc">Підбираємо правильні шини для агро-, спец- та вантажної техніки з
                    {{ config('site.founded_year') }} року.</p>
                @include('partials.rating')
                <div class="footer-contact-line">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                    </svg>
                    <a href="tel:{{ $c['phone_href'] }}" class="fc-strong">{{ $c['phone'] }}</a>
                </div>
                <div class="footer-contact-line">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                        <polyline points="22,6 12,13 2,6" />
                    </svg>
                    <a href="mailto:{{ $c['email'] }}">{{ $c['email'] }}</a>
                </div>
                <div class="footer-contact-line" style="margin-bottom:18px">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                        <circle cx="12" cy="10" r="3" />
                    </svg>
                    <span>{{ $c['address'] }}</span>
                </div>
                <a href="{{ route('contacts') }}" class="btn btn--primary btn--block">Замовити дзвінок</a>
            </div>

            @php($catalogLinks = collect([['name' => 'Усі товари', 'url' => route('catalog')]])
                ->merge($catalogMenu['types'] ?? [])
                ->merge(collect($catalogMenu['machinery'] ?? [])->take(3)->map(fn ($m) => ['name' => 'Шини на ' . mb_strtolower($m['name']), 'url' => $m['url']]))
                ->mapWithKeys(fn ($i) => [$i['name'] => $i['url']])->all())
            @php($accordions = [
                'Каталог' => $catalogLinks,
                'Компанія' => [
                    'Про нас' => route('about'),
                    'Доставка й оплата' => route('pages.delivery'),
                    'Повернення та обмін' => route('pages.returns'),
                    'Гарантія' => route('pages.warranty'),
                    'Контакти' => route('contacts'),
                ],
                'Покупцю' => [
                    'Доставка й оплата' => route('pages.delivery'),
                    'Повернення та обмін' => route('pages.returns'),
                    'Гарантія' => route('pages.warranty'),
                    'Політика конфіденційності' => route('pages.privacy'),
                ],
                'Контакти' => array_filter([
                    $c['phone'] => 'tel:' . $c['phone_href'],
                    ($c['phone2'] ?? '') => $c['phone2'] ? 'tel:' . $c['phone2_href'] : null,
                    $c['email'] => 'mailto:' . $c['email'],
                    'Telegram' => config('site.socials.telegram'),
                    'Viber' => config('site.socials.viber'),
                ]),
            ])
            @foreach ($accordions as $title => $links)
                <div class="fm-accordion" x-data="{ open: false }">
                    <button type="button" class="fm-acc-head" :class="{ 'is-open': open }" @click="open = !open">
                        {{ $title }}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </button>
                    <div class="fm-acc-body" x-show="open" x-cloak>
                        @foreach ($links as $label => $url)
                            <a href="{{ $url }}">{{ $label }}</a>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div style="text-align:center;margin:28px 0 8px">
                <p style="font-size:13px;color:#9aa0a8;margin-bottom:16px;text-transform:uppercase;letter-spacing:.5px">
                    Ми в соцмережах</p>
                @include('partials.socials')
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container"
            style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;width:100%">
            <span>© {{ config('site.founded_year') }}–{{ now()->year }} ВЕЛИКА ШИНА</span>
            <a href="{{ route('pages.privacy') }}">Політика конфіденційності</a>
            <span class="fb-made">Створено з <span style="color:#e31e24">♥</span> в Україні 🇺🇦</span>
        </div>
    </div>
</footer>
