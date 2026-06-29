import Alpine from 'alpinejs';
import AOS from 'aos';
import 'aos/dist/aos.css';

document.addEventListener('alpine:init', () => {
    // Глобальний UI-стан (мобільне меню + пошук).
    Alpine.store('ui', {
        menu: false,
        search: false,
        toggleMenu() {
            this.menu = !this.menu;
            this.lockScroll(this.menu);
        },
        closeMenu() {
            this.menu = false;
            this.lockScroll(false);
        },
        toggleSearch() {
            this.search = !this.search;
            this.lockScroll(this.search);
        },
        closeSearch() {
            this.search = false;
            this.lockScroll(false);
        },
        lockScroll(on) {
            document.body.style.overflow = on ? 'hidden' : '';
        },
    });

    // Кошик (гостьовий, у localStorage; оформлення → /api/leads).
    Alpine.store('cart', {
        items: JSON.parse(localStorage.getItem('cart') || '[]'),
        save() {
            localStorage.setItem('cart', JSON.stringify(this.items));
        },
        find(id) {
            return this.items.find((i) => i.id === id);
        },
        has(id) {
            return !!this.find(id);
        },
        add(item, qty = 1) {
            if (!item || !item.id) return;
            const ex = this.find(item.id);
            if (ex) ex.qty += qty;
            else this.items.push({ ...item, qty });
            this.save();
            // сповіщення (тост) про додавання
            window.dispatchEvent(new CustomEvent('cart-added', { detail: { ...item, qty } }));
        },
        setQty(id, qty) {
            const i = this.find(id);
            if (i) {
                i.qty = Math.max(1, Math.min(1000, parseInt(qty) || 1));
                this.save();
            }
        },
        remove(id) {
            this.items = this.items.filter((i) => i.id !== id);
            this.save();
        },
        clear() {
            this.items = [];
            this.save();
        },
        get count() {
            return this.items.reduce((s, i) => s + i.qty, 0);
        },
        get total() {
            return this.items.reduce((s, i) => s + (Number(i.price) || 0) * i.qty, 0);
        },
        get hasInquiry() {
            return this.items.some((i) => !i.price || i.price_mode === 'inquiry');
        },
    });

    // Обране (гостьове, у localStorage).
    Alpine.store('fav', {
        items: JSON.parse(localStorage.getItem('fav') || '[]'),
        save() {
            localStorage.setItem('fav', JSON.stringify(this.items));
        },
        has(id) {
            return this.items.some((i) => i.id === id);
        },
        toggle(item) {
            if (!item || !item.id) return;
            if (this.has(item.id)) this.items = this.items.filter((i) => i.id !== item.id);
            else this.items.push(item);
            this.save();
        },
        remove(id) {
            this.items = this.items.filter((i) => i.id !== id);
            this.save();
        },
        get count() {
            return this.items.length;
        },
    });

    // Порівняння (гостьове, у localStorage). Максимум 4 шини.
    Alpine.store('compare', {
        items: JSON.parse(localStorage.getItem('compare') || '[]'),
        max: 4,
        save() {
            localStorage.setItem('compare', JSON.stringify(this.items));
        },
        has(id) {
            return this.items.some((i) => i.id === id);
        },
        full() {
            return this.items.length >= this.max;
        },
        toggle(item) {
            if (!item || !item.id) return;
            if (this.has(item.id)) {
                this.items = this.items.filter((i) => i.id !== item.id);
            } else {
                if (this.full()) {
                    window.dispatchEvent(new CustomEvent('compare-full', { detail: { max: this.max } }));
                    return;
                }
                this.items.push(item);
                window.dispatchEvent(new CustomEvent('compare-added', { detail: { ...item } }));
            }
            this.save();
        },
        remove(id) {
            this.items = this.items.filter((i) => i.id !== id);
            this.save();
        },
        // Доповнюємо стор даними з сервера (коли заходять за прямим /compare?ids=).
        seed(cards) {
            if (this.items.length || !Array.isArray(cards)) return;
            this.items = cards.filter((c) => c && c.id);
            this.save();
        },
        clear() {
            this.items = [];
            this.save();
        },
        get count() {
            return this.items.length;
        },
        get ids() {
            return this.items.map((i) => i.id).join(',');
        },
        get url() {
            return '/compare?ids=' + this.ids;
        },
    });

    /**
     * Повноекранний hero-слайдер (відео/зображення) з автопрогортанням.
     * Грає лише активне відео, решта — на паузі.
     */
    Alpine.data('heroSlider', (slides) => ({
        slides,
        active: 0,
        timer: null,
        init() {
            this.$nextTick(() => this.playActive());
            this.start();
        },
        start() {
            this.stop();
            this.timer = setInterval(() => this.next(), 6500);
        },
        stop() {
            if (this.timer) clearInterval(this.timer);
        },
        next() {
            this.active = (this.active + 1) % this.slides.length;
            this.playActive();
        },
        go(i) {
            this.active = i;
            this.playActive();
            this.start();
        },
        playActive() {
            ['v0', 'v1'].forEach((ref, i) => {
                const v = this.$refs[ref];
                if (!v) return;
                if (i === this.active) {
                    try {
                        v.currentTime = 0;
                        const p = v.play();
                        if (p) p.catch(() => {});
                    } catch (e) {}
                } else {
                    v.pause();
                }
            });
        },
    }));

    /**
     * Стан UI каталогу. view (сітка/список) та згорнутість фільтрів
     * зберігаються у localStorage, щоб не скидались під час пагінації
     * (повне перезавантаження сторінки). filtersOpen (моб. шторка) — ні.
     */
    Alpine.data('catalogUi', () => ({
        filtersOpen: false,
        view: localStorage.getItem('catalogView') === 'list' ? 'list' : 'grid',
        filtersCollapsed: localStorage.getItem('catalogFiltersCollapsed') === '1',
        init() {
            this.$watch('view', (v) => localStorage.setItem('catalogView', v));
            this.$watch('filtersCollapsed', (v) =>
                localStorage.setItem('catalogFiltersCollapsed', v ? '1' : '0')
            );
        },
    }));

    /**
     * Живий лічильник результатів фільтра каталогу.
     * ПК: біля щойно зміненого пункту спливає кнопка «Показати N товарів»
     * (по кліку застосовує фільтр). Моб.: число оновлюється на нижній кнопці.
     */
    Alpine.data('catalogFilter', (endpoint, initial) => ({
        count: initial,
        loading: false,
        timer: null,
        pill: { show: false, x: 0, y: 0 },

        onChange(e) {
            // реагуємо лише на чекбокси фільтрів (не на пошукові поля груп)
            if (e.target.type !== 'checkbox') return;
            this.loading = true; // одразу ховаємо стару цифру (показуємо «…»)
            this.positionPill(e);
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.fetchCount(), 150);
        },

        positionPill(e) {
            if (window.innerWidth < 993) return; // на моб. — нижня кнопка
            const panel = this.$root.closest('.catalog-filters');
            const row = e.target.closest('.cf-check, .cf-avail') || e.target;
            if (!panel || !row) return;
            const pr = panel.getBoundingClientRect();
            const rr = row.getBoundingClientRect();
            this.pill.x = pr.right + 12;
            this.pill.y = Math.min(Math.max(rr.top + rr.height / 2, 90), window.innerHeight - 60);
            this.pill.show = true;
        },

        async fetchCount() {
            const params = new URLSearchParams(new FormData(this.$root));
            this.loading = true;
            try {
                const res = await fetch(`${endpoint}?${params.toString()}`, {
                    headers: { Accept: 'application/json' },
                });
                this.count = (await res.json()).count;
            } catch (e) {
                // тихо ігноруємо
            } finally {
                this.loading = false;
            }
        },
    }));

    /**
     * Живий (випадаючий) пошук у навігації: на введення підвантажує
     * товари-підказки й показує дропдаун. Enter — звичайний сабміт у каталог.
     */
    Alpine.data('liveSearch', (endpoint, catalogUrl) => ({
        q: '',
        items: [],
        total: 0,
        open: false,
        loading: false,
        timer: null,

        onInput() {
            clearTimeout(this.timer);
            const term = this.q.trim();
            if (term.length < 1) {
                this.items = [];
                this.total = 0;
                this.open = false;
                return;
            }
            this.open = true;
            this.timer = setTimeout(() => this.fetch(term), 200);
        },

        async fetch(term) {
            this.loading = true;
            try {
                const res = await fetch(`${endpoint}?q=${encodeURIComponent(term)}`, {
                    headers: { Accept: 'application/json' },
                });
                const data = await res.json();
                this.items = data.items || [];
                this.total = data.total || 0;
            } catch (e) {
                this.items = [];
                this.total = 0;
            } finally {
                this.loading = false;
            }
        },

        catalogLink() {
            return `${catalogUrl}?q=${encodeURIComponent(this.q.trim())}`;
        },
        close() {
            this.open = false;
        },
    }));

    /**
     * Підтвердження видалення зі стора ($store[name]) через модалку.
     */
    Alpine.data('removeConfirm', (storeName) => ({
        confirm: { open: false, id: null, name: '' },
        askRemove(i) {
            this.confirm = { open: true, id: i.id, name: (i.size + ' ' + (i.brand || '')).trim() };
        },
        cancelRemove() {
            this.confirm.open = false;
        },
        doRemove() {
            this.$store[storeName].remove(this.confirm.id);
            this.confirm.open = false;
        },
    }));

    /**
     * Тост «додано в кошик»: вискакує на подію cart-added,
     * пропонує перейти в кошик або продовжити покупки.
     */
    Alpine.data('cartToast', (cartUrl) => ({
        cartUrl,
        open: false,
        item: {},
        timer: null,
        show(item) {
            this.item = item || {};
            this.open = true;
            clearTimeout(this.timer);
            this.timer = setTimeout(() => (this.open = false), 5000);
        },
        close() {
            this.open = false;
            clearTimeout(this.timer);
        },
    }));

    // Тост «Додано до порівняння» (у стилі тосту кошика).
    Alpine.data('compareToast', () => ({
        open: false,
        limit: false,
        item: {},
        timer: null,
        showItem(item) {
            this.item = item || {};
            this.limit = false;
            this.open = true;
            clearTimeout(this.timer);
            this.timer = setTimeout(() => (this.open = false), 5000);
        },
        showLimit() {
            this.limit = true;
            this.open = true;
            clearTimeout(this.timer);
            this.timer = setTimeout(() => (this.open = false), 3000);
        },
        close() {
            this.open = false;
            clearTimeout(this.timer);
        },
    }));

    /**
     * Оформлення кошика в заявку (сторінка /cart → /api/leads).
     */
    Alpine.data('cartCheckout', (endpoint) => ({
        sent: false,
        loading: false,
        error: '',
        orderId: null,
        confirm: { open: false, id: null, name: '' },
        form: {
            name: '',
            phone: '',
            city: '',
            delivery: 'Нова Пошта',
            address: '',
            payment: 'Накладений платіж (при отриманні)',
            comment: '',
        },

        get isPickup() {
            return this.form.delivery === 'Самовивіз зі складу';
        },
        get deliveryCost() {
            return this.isPickup ? 'Безкоштовно' : 'За тарифами перевізника';
        },

        askRemove(i) {
            this.confirm = { open: true, id: i.id, name: (i.size + ' ' + (i.brand || '')).trim() };
        },
        cancelRemove() {
            this.confirm.open = false;
        },
        doRemove() {
            this.$store.cart.remove(this.confirm.id);
            this.confirm.open = false;
        },
        async submit() {
            const items = this.$store.cart.items;
            if (!items.length) return;
            this.error = '';
            this.loading = true;
            try {
                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                    body: JSON.stringify({
                        customer_name: this.form.name,
                        phone: this.form.phone,
                        city: this.form.city,
                        delivery_method: this.form.delivery,
                        delivery_address: this.isPickup ? '' : this.form.address,
                        payment_method: this.form.payment,
                        comment: this.form.comment,
                        items: items.map((i) => ({ product_id: i.id, qty: i.qty })),
                    }),
                });
                if (!res.ok) throw new Error();
                const data = await res.json();
                this.orderId = data.lead_id;
                this.sent = true;
                this.$store.cart.clear();
            } catch (e) {
                this.error = 'Не вдалося оформити замовлення. Спробуйте ще раз або зателефонуйте нам.';
            } finally {
                this.loading = false;
            }
        },
    }));

    /**
     * Горизонтальна стрічка вкладок зі стрілками.
     * Стрілки показуються лише коли контент не вміщується.
     */
    Alpine.data('tabsScroller', () => ({
        canLeft: false,
        canRight: false,
        init() {
            this.$nextTick(() => this.update());
            window.addEventListener('resize', () => this.update());
        },
        update() {
            const el = this.$refs.track;
            if (!el) return;
            this.canLeft = el.scrollLeft > 4;
            this.canRight = el.scrollLeft + el.clientWidth < el.scrollWidth - 4;
        },
        scroll(dir) {
            this.$refs.track.scrollBy({ left: dir * 240, behavior: 'smooth' });
        },
    }));

    /**
     * Фасетний фільтр на головній: тип техніки / категорія / бренд / розмір.
     * Усі поля завжди активні й працюють у будь-який бік — можна почати з
     * бренду, з розміру чи з техніки. Після зміни будь-якого поля з БД
     * підвантажуються доступні опції для решти полів (з урахуванням вибору),
     * а вибір, що став неможливим, скидається.
     */
    Alpine.data('homeFilter', (init) => ({
        endpoint: init.endpoint,
        machinery: '',
        category: '',
        brand: '',
        size: '',
        machineryOptions: init.machinery || [],
        categoryOptions: init.categories || [],
        brandOptions: init.brands || [],
        sizeOptions: init.sizes || [],
        loading: false,

        // Відповідність поля → масив його опцій.
        optsOf(field) {
            return {
                machinery: this.machineryOptions,
                category: this.categoryOptions,
                brand: this.brandOptions,
                size: this.sizeOptions,
            }[field];
        },

        async refresh(changed) {
            await this.load();
            // Якщо якесь поле (окрім щойно зміненого) стало недоступним —
            // скидаємо його й перезавантажуємо опції ще раз.
            if (this.prune(changed)) {
                await this.load();
            }
        },

        async load() {
            const p = new URLSearchParams();
            ['machinery', 'category', 'brand', 'size'].forEach((f) => {
                if (this[f]) p.set(f, this[f]);
            });

            this.loading = true;
            try {
                const res = await fetch(`${this.endpoint}?${p.toString()}`, {
                    headers: { Accept: 'application/json' },
                });
                const data = await res.json();
                this.machineryOptions = data.machinery || [];
                this.categoryOptions = data.categories || [];
                this.brandOptions = data.brands || [];
                this.sizeOptions = data.sizes || [];
            } catch (e) {
                // тихо ігноруємо — селекти лишаються в поточному стані
            } finally {
                this.loading = false;
            }
        },

        prune(changed) {
            let reset = false;
            ['machinery', 'category', 'brand', 'size'].forEach((f) => {
                if (f === changed || !this[f]) return;
                const exists = this.optsOf(f).some((o) => o.value === this[f]);
                if (!exists) {
                    this[f] = '';
                    reset = true;
                }
            });
            return reset;
        },

        reset() {
            this.machinery = this.category = this.brand = this.size = '';
            this.load();
        },
    }));

    /**
     * Підсумковий лічильник: повна кількість РОКІВ / ДНІВ / ГОДИН
     * з дати заснування (числа з пробілами-роздільниками).
     */
    Alpine.data('experienceStats', (startIso) => ({
        years: 0,
        days: '0',
        hours: '0',
        init() {
            this.tick();
            setInterval(() => this.tick(), 1000);
        },
        tick() {
            const start = new Date(startIso);
            const now = new Date();
            const ms = Math.max(0, now - start);

            let years = now.getFullYear() - start.getFullYear();
            const anniversary = new Date(start);
            anniversary.setFullYear(start.getFullYear() + years);
            if (anniversary > now) years -= 1;

            this.years = years;
            this.days = this.fmt(Math.floor(ms / 86400000));
            this.hours = this.fmt(Math.floor(ms / 3600000));
        },
        fmt(n) {
            return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        },
    }));

    /**
     * Лічильник досвіду роботи компанії.
     * Рахує точну кількість років / днів / годин / хвилин від дати
     * заснування і оновлюється щосекунди.
     */
    Alpine.data('experienceCounter', (startIso) => ({
        years: 0,
        days: 0,
        hours: 0,
        minutes: 0,

        init() {
            this.tick();
            setInterval(() => this.tick(), 1000);
        },

        tick() {
            const start = new Date(startIso);
            const now = new Date();

            let years = now.getFullYear() - start.getFullYear();
            const anniversary = new Date(start);
            anniversary.setFullYear(start.getFullYear() + years);
            if (anniversary > now) {
                years -= 1;
                anniversary.setFullYear(anniversary.getFullYear() - 1);
            }

            let rest = Math.max(0, now - anniversary);
            const day = 1000 * 60 * 60 * 24;
            const hour = 1000 * 60 * 60;
            const minute = 1000 * 60;

            this.years = years;
            this.days = Math.floor(rest / day);
            rest -= this.days * day;
            this.hours = Math.floor(rest / hour);
            rest -= this.hours * hour;
            this.minutes = Math.floor(rest / minute);
        },
    }));
});

window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    AOS.init({
        duration: 800,
        once: true,
        offset: 80,
        disable: window.innerWidth < 768,
    });

    // Висота липкої шапки → CSS-змінна (для липких тулбара/фільтрів).
    const setHeaderH = () => {
        const h = document.querySelector('.site-header')?.offsetHeight || 0;
        document.documentElement.style.setProperty('--header-h', h + 'px');
    };
    setHeaderH();
    window.addEventListener('resize', setHeaderH);
    window.addEventListener('load', setHeaderH);
});
