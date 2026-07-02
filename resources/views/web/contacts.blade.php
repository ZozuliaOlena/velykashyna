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
                <a href="tel:{{ $c['phone_href'] }}" class="contact-card">
                    <span class="contact-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                        </svg>
                    </span>
                    <div class="contact-card__body">
                        <span class="contact-card__label">Телефон</span>
                        <span class="contact-card__value">{{ $c['phone'] }}</span>
                        <span class="contact-card__note">Телефонуйте будь-коли — ми на зв'язку 24/7</span>
                    </div>
                </a>

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

                {{-- Месенджери --}}
                <div class="messengers">
                    <a href="{{ $socials['telegram'] }}" class="messenger messenger--tg" target="_blank" rel="noopener">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M21.94 4.6 18.6 20.3c-.25 1.1-.9 1.38-1.83.86l-5.05-3.72-2.44 2.35c-.27.27-.5.5-1.02.5l.36-5.13L16.95 7.5c.4-.36-.09-.56-.63-.2L6.78 13.5l-4.98-1.56c-1.08-.34-1.1-1.08.23-1.6l19.45-7.5c.9-.33 1.69.2 1.46 1.76z" />
                        </svg>
                        Telegram
                    </a>
                    <a href="{{ $socials['viber'] }}" class="messenger messenger--viber" target="_blank" rel="noopener">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" />
                        </svg>
                        Viber
                    </a>
                    <a href="{{ $socials['whatsapp'] }}" class="messenger messenger--wa" target="_blank" rel="noopener">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" />
                        </svg>
                        WhatsApp
                    </a>
                </div>

                <div class="contacts-socials">
                    <span class="contacts-socials__label">Ми в соцмережах</span>
                    @include('partials.socials')
                </div>
            </div>

            {{-- ПРАВА КОЛОНКА: форма заявки --}}
            <div class="contacts-form" data-aos="fade-up" data-aos-delay="80"
                x-data="{ sent: false, submit() { this.sent = true } }">
                <div class="cform" x-show="!sent">
                    <h2 class="cform__title">Залишити заявку</h2>
                    <p class="cform__sub">Заповніть форму — менеджер передзвонить і допоможе з підбором.</p>

                    <form @submit.prevent="submit()">
                        <div class="cform__row">
                            <label class="cform__field">
                                <span>Ваше ім'я</span>
                                <input type="text" name="name" placeholder="Як до вас звертатися" required />
                            </label>
                            <label class="cform__field">
                                <span>Телефон</span>
                                <input type="tel" name="phone" value="+38 " placeholder="+38 (0__) ___ __ __" required />
                            </label>
                        </div>
                        <label class="cform__field">
                            <span>Що вас цікавить?</span>
                            <textarea name="message" rows="4"
                                placeholder="Напр.: потрібні шини 800/65 R32 на трактор John Deere"></textarea>
                        </label>
                        <button type="submit" class="btn btn--primary btn--block">Надіслати заявку</button>
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
