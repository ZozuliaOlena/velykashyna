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
});
