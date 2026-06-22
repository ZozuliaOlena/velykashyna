{{-- resources/views/partials/footer.blade.php --}}
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col brand-col">
                <img src="/images/logo.png" alt="Велика Шина" class="footer-logo" />
                <p class="footer-desc">
                    Експерти у світі великих коліс. Ми допомагаємо забезпечити
                    безперебійну роботу вашої спецтехніки та агромашин.
                </p>
                <div class="social-links">
                    <a href="#" aria-label="Facebook">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                        </svg>
                    </a>
                    <a href="mailto:velykashyna@ukr.net" aria-label="Email">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                    </a>
                    <a href="skype:sashko109?chat" aria-label="Skype">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path
                                d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z">
                            </path>
                        </svg>
                    </a>
                </div>
            </div>

            <div class="footer-col links-col">
                <h3 class="footer-title">Меню</h3>
                <ul>
                    <li><a href="{{ route('home') }}">Головна</a></li>
                    <li><a href="#">Шини</a></li>
                    <li><a href="#">Здвоєні колеса</a></li>
                    <li><a href="#">Камери</a></li>
                    <li><a href="#">Новини</a></li>
                    <li><a href="#">Контакти</a></li>
                </ul>
            </div>

            <div class="footer-col links-col">
                <h3 class="footer-title">Каталог</h3>
                <ul>
                    <li><a href="#">Сільськогосподарські</a></li>
                    <li><a href="#">Промислові шини</a></li>
                    <li><a href="#">Вантажні шини</a></li>
                    <li><a href="#">Спецтехніка</a></li>
                </ul>
            </div>

            <div class="footer-col contacts-col">
                <h3 class="footer-title">Контакти</h3>
                <div class="contact-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                        </path>
                    </svg>
                    <div>
                        <a href="tel:+380679282086">+38 (067) 928 20 86</a>
                        <a href="tel:+380443553554">+38 (044) 355 35 54</a>
                    </div>
                </div>
                <div class="contact-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                    <a href="mailto:velykashyna@ukr.net">velykashyna@ukr.net</a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} velykashyna.com.ua. Всі права захищені.</p>
            <p>
                Сайт розроблено
                <a href="https://ksibe.dev" target="_blank" rel="noopener">KSIBE.DEV</a>
            </p>
        </div>
    </div>
</footer>
