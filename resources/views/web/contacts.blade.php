{{-- resources/views/web/contacts.blade.php --}}
@extends('layouts.app')

@section('title', 'Контакти — Велика Шина')
@section('meta_description', 'Контакти компанії «Велика Шина»: телефон, email, адреса в Києві та месенджери. Ми на зв\'язку 24/7 — підберемо шини для вашої техніки.')

@php($c = config('site.contacts'))
@php($socials = config('site.socials'))
@php($mapQuery = "пров. В'ячеслава Чорновола, 54а, Київ, 08132")

@section('content')
<section class="section contacts-page">
    {{-- Декор: колеса обертаються залежно від прокрутки --}}
    <div class="contacts-deco" aria-hidden="true" x-data="{ r: 0 }"
        @scroll.window.passive="r = window.scrollY * 0.18">
        <img src="/images/svg/tehnics/wheel.svg" class="contacts-deco__wheel contacts-deco__wheel--1"
            :style="`transform: rotate(${r}deg)`" alt="" />
        <img src="/images/svg/tehnics/wheel.svg" class="contacts-deco__wheel contacts-deco__wheel--2"
            :style="`transform: rotate(${r * -1.5}deg)`" alt="" />
    </div>

    <div class="container">
        <nav class="breadcrumbs">
            <a href="{{ route('home') }}">Головна</a>
            <span class="sep">/</span>
            <span class="current">Контакти</span>
        </nav>

        <div class="contacts-head" data-aos="fade-up">
            <span class="about-kicker">Ми на зв'язку</span>
            <h1 class="contacts-title">Контакти</h1>
            <p class="about-sub">Зателефонуйте, напишіть у месенджер або залиште заявку — підберемо шини
                для вашої техніки та проконсультуємо щодо наявності й доставки.</p>
        </div>

        <div class="contacts-grid">
            {{-- ЛІВА КОЛОНКА: контактні дані --}}
            <div class="contacts-info" data-aos="fade-up">
                <div class="contact-card">
                    <span class="contact-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                        </svg>
                    </span>
                    <div class="contact-card__body">
                        <span class="contact-card__label">Телефон</span>
                        <a href="tel:{{ $c['phone_href'] }}" class="contact-card__value contact-card__value--link">{{ $c['phone'] }}</a>
                        @if (!empty($c['phone2']))
                        <a href="tel:{{ $c['phone2_href'] }}" class="contact-card__value contact-card__value--link">{{ $c['phone2'] }}</a>
                        @endif
                        <span class="contact-card__note">Телефонуйте будь-коли — ми на зв'язку 24/7</span>
                    </div>
                </div>

                <a href="mailto:{{ $c['email'] }}" class="contact-card">
                    <span class="contact-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                            <polyline points="22,6 12,13 2,6" />
                        </svg>
                    </span>
                    <div class="contact-card__body">
                        <span class="contact-card__label">Email</span>
                        <span class="contact-card__value">{{ $c['email'] }}</span>
                        <span class="contact-card__note">Відповідаємо протягом робочого дня</span>
                    </div>
                </a>

                <div class="contact-card">
                    <span class="contact-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                            <circle cx="12" cy="10" r="3" />
                        </svg>
                    </span>
                    <div class="contact-card__body">
                        <span class="contact-card__label">Адреса</span>
                        <span class="contact-card__value contact-card__value--sm">{{ $c['address'] }}</span>
                        <a href="https://www.google.com/maps?q={{ urlencode($mapQuery) }}" target="_blank"
                            rel="noopener" class="contact-card__link">Прокласти маршрут →</a>
                    </div>
                </div>

                <div class="contact-card contact-card--schedule">
                    <span class="contact-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                    </span>
                    <div class="contact-card__body">
                        <span class="contact-card__label">Графік роботи</span>
                        <span class="contact-card__value">Ми на зв'язку 24/7</span>
                        <span class="contact-card__note">Цілодобово, без вихідних</span>
                    </div>
                </div>

                {{-- Месенджери та соцмережі — однотипні монохромні іконки без фону --}}
                <div class="contact-channels">
                    <span class="contact-channels__label">Ми на зв'язку в месенджерах і соцмережах</span>
                    <div class="contact-channels__row">
                        <a href="{{ $socials['telegram'] }}" class="channel-ico" aria-label="Telegram" target="_blank" rel="noopener">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z" />
                            </svg>
                        </a>
                        <a href="{{ $socials['viber'] }}" class="channel-ico" aria-label="Viber" target="_blank" rel="noopener">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M11.4 0C9.473.028 5.333.344 3.02 2.467 1.302 4.187.696 6.7.633 9.817c-.06 3.106-.13 8.933 5.487 10.513v2.414s-.037.97.603 1.17c.774.242 1.234-.5 1.973-1.293l1.38-1.563c3.85.326 6.812-.417 7.15-.526.776-.252 5.176-.816 5.89-6.657.74-6.02-.36-9.83-2.34-11.55C20.15 1.79 17.15.28 11.4 0m.045 1.68c.564-.003 1.24.058 1.24.058 4.856.006 6.79 1.72 7.13 2.01 1.673 1.436 2.523 4.87 1.898 9.9-.578 4.88-4.14 5.19-4.8 5.4-.28.09-2.906.744-6.208.53 0 0-2.462 2.968-3.23 3.74-.12.12-.26.17-.352.15-.13-.03-.166-.184-.164-.408l.03-4.108s-.005 0 0 0c-4.77-1.32-4.49-6.29-4.44-8.895.056-2.605.55-4.735 1.97-6.15C7.15 1.79 10.892 1.68 11.446 1.68m.55 2.94a.3.3 0 0 0-.302.298.3.3 0 0 0 .3.3c1.5.012 2.727.5 3.663 1.436.935.936 1.4 2.19 1.412 3.75a.3.3 0 0 0 .3.297h.002a.3.3 0 0 0 .298-.3c-.013-1.71-.53-3.114-1.583-4.167-1.052-1.052-2.45-1.596-4.087-1.61a.3.3 0 0 0-.014 0m.55 1.727a.3.3 0 0 0-.03.598c.865.045 1.512.32 1.985.822.474.503.71 1.14.71 1.977a.3.3 0 0 0 .6 0c0-.976-.29-1.775-.878-2.397-.588-.622-1.416-.97-2.386-1.02a.3.3 0 0 0-.03 0m-4.16.628a.68.68 0 0 0-.4.104h-.01c-.27.158-.51.365-.71.616-.15.194-.23.386-.25.573-.014.11-.005.222.03.328l.013.008c.1.294.31.607.59 1.006.454.72 1.083 1.463 1.66 2.02.28.27.596.51.94.71l.008.01.007.005.15.106c.052.037.104.072.16.106.336.204.702.363 1.028.464h.012c.11.036.222.045.335.03.19-.02.38-.1.57-.25.25-.2.457-.44.615-.71v-.01a.68.68 0 0 0 .103-.4c-.014-.16-.088-.31-.24-.42-.31-.226-.63-.44-.96-.64-.22-.13-.444-.055-.535.065l-.194.244c-.1.12-.283.104-.283.104l-.005.003c-1.354-.346-1.716-1.717-1.716-1.717s-.016-.183.106-.283l.243-.194c.12-.09.195-.315.065-.535-.2-.33-.414-.65-.64-.96-.096-.132-.234-.21-.39-.234a.5.5 0 0 0-.06-.005m2.61.885c-.157.006-.28.14-.276.297.006.157.14.28.297.276.157-.006.28.14.276-.276a.286.286 0 0 0-.297-.297z" />
                            </svg>
                        </a>
                        <a href="{{ $socials['whatsapp'] }}" class="channel-ico" aria-label="WhatsApp" target="_blank" rel="noopener">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.885-9.885 9.885m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0 0 20.885 3.488" />
                            </svg>
                        </a>
                        <a href="{{ $socials['facebook'] }}" class="channel-ico" aria-label="Facebook" target="_blank" rel="noopener">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M9.101 23.691v-7.98H6.627v-3.667h2.474v-1.58c0-4.085 1.848-5.978 5.858-5.978.401 0 .955.042 1.468.103a8.68 8.68 0 0 1 1.141.195v3.325a8.623 8.623 0 0 0-.653-.036 26.805 26.805 0 0 0-.733-.009c-.707 0-1.259.096-1.675.309a1.686 1.686 0 0 0-.679.622c-.258.42-.374.995-.374 1.752v1.297h3.919l-.386 2.103-.287 1.564h-3.246v8.245C19.396 23.238 24 18.179 24 12.044c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.628 3.874 10.35 9.101 11.647z" />
                            </svg>
                        </a>
                        <a href="{{ $socials['youtube'] }}" class="channel-ico" aria-label="YouTube" target="_blank" rel="noopener">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
                            </svg>
                        </a>
                        <a href="{{ $socials['instagram'] }}" class="channel-ico" aria-label="Instagram" target="_blank" rel="noopener">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227a3.81 3.81 0 0 1-.923 1.417 3.792 3.792 0 0 1-1.416.923c-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421a3.716 3.716 0 0 1-1.416-.923 3.716 3.716 0 0 1-.924-1.416c-.164-.42-.36-1.065-.413-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.053-1.171.249-1.816.413-2.236.216-.562.477-.96.924-1.406.42-.419.819-.679 1.381-.896.42-.164 1.051-.36 2.221-.413 1.266-.045 1.646-.06 4.849-.06zm0 3.678a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm7.846-10.405a1.441 1.441 0 0 1-2.88 0 1.44 1.44 0 0 1 2.88 0z" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            {{-- ПРАВА КОЛОНКА: форма заявки --}}
            <div class="contacts-form" data-aos="fade-up" data-aos-delay="80"
                x-data="consultationForm('{{ route('api.consultations.store') }}')">
                <div class="cform" x-show="!sent">
                    <h2 class="cform__title">Залишити заявку</h2>
                    <p class="cform__sub">Заповніть форму — менеджер передзвонить і допоможе з підбором.</p>

                    <form @submit.prevent="submit()">
                        <div class="cform__row">
                            <label class="cform__field">
                                <span>Ваше ім'я</span>
                                <input type="text" name="name" x-model="form.name" placeholder="Як до вас звертатися" required />
                            </label>
                            <label class="cform__field">
                                <span>Телефон</span>
                                <input type="tel" name="phone" x-model="form.phone" placeholder="+38 (0__) ___ __ __" required />
                            </label>
                        </div>
                        <label class="cform__field">
                            <span>Що вас цікавить?</span>
                            <textarea name="message" x-model="form.message" rows="4"
                                placeholder="Напр.: потрібні шини 800/65 R32 на трактор John Deere"></textarea>
                        </label>
                        <button type="submit" class="btn btn--outline btn--block" :disabled="loading">
                            <span x-show="!loading">Надіслати заявку</span>
                            <span x-show="loading" x-cloak>Надсилаємо…</span>
                        </button>
                        <p class="cform__err" x-show="error" x-cloak x-text="error"></p>
                        <p class="cform__note">Натискаючи кнопку, ви погоджуєтесь на обробку персональних даних.</p>
                    </form>
                </div>

                <div class="cform__success" x-show="sent" x-cloak x-transition>
                    <span class="cform__success-ico">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                            <polyline points="22 4 12 14.01 9 11.01" />
                        </svg>
                    </span>
                    <h3>Дякуємо за заявку!</h3>
                    <p>Ми отримали ваше повідомлення і зв'яжемося з вами найближчим часом.</p>
                    <a href="{{ route('catalog') }}" class="btn btn--outline">Перейти до каталогу</a>
                </div>
            </div>
        </div>
    </div>

    {{-- КАРТА --}}
    <div class="container">
        <div class="contacts-map" data-aos="fade-up">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d81394.49698227287!2d30.419900329908486!3d50.3930642649235!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x40d4c96e0f33485f%3A0x84d853a4a16a3092!2sVelyka%20Shyna!5e0!3m2!1sru!2sde!4v1782401549123!5m2!1sru!2sde"
                title="Велика Шина на мапі" loading="lazy" allowfullscreen
                referrerpolicy="strict-origin-when-cross-origin"></iframe>
        </div>
    </div>
</section>
@endsection
