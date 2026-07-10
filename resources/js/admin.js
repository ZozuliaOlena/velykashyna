// Адмін-скрипти. ВАЖЛИВО: не імпортуємо й не стартуємо Alpine -
// його надає Livewire. Тут drag-and-drop (SortableJS) і редактор статей (Trix).
import Sortable from 'sortablejs';
import 'trix';
import 'trix/dist/trix.css';

// Зменшуємо великі зображення в браузері перед завантаженням, щоб не впертися
// в ліміт PHP upload_max_filesize і не ганяти зайві мегабайти.
function downscaleImage(file, maxDim = 1600, quality = 0.82) {
    return new Promise((resolve) => {
        // Растрові формати масштабуємо; svg/gif/інше - як є.
        if (! /^image\/(jpeg|png|webp)$/.test(file.type)) {
            resolve(file);
            return;
        }

        const img = new Image();
        const objectUrl = URL.createObjectURL(file);

        img.onload = () => {
            URL.revokeObjectURL(objectUrl);

            // Маленькі файли не чіпаємо.
            if (img.width <= maxDim && img.height <= maxDim && file.size < 1.5 * 1024 * 1024) {
                resolve(file);
                return;
            }

            const scale = Math.min(1, maxDim / Math.max(img.width, img.height));
            const w = Math.round(img.width * scale);
            const h = Math.round(img.height * scale);

            const canvas = document.createElement('canvas');
            canvas.width = w;
            canvas.height = h;
            const ctx = canvas.getContext('2d');

            const type = file.type === 'image/png' ? 'image/png' : 'image/jpeg';
            if (type === 'image/jpeg') {
                ctx.fillStyle = '#fff'; // біле тло замість прозорості при png→jpeg
                ctx.fillRect(0, 0, w, h);
            }
            ctx.drawImage(img, 0, 0, w, h);

            canvas.toBlob((blob) => resolve(blob || file), type, quality);
        };
        img.onerror = () => {
            URL.revokeObjectURL(objectUrl);
            resolve(file);
        };
        img.src = objectUrl;
    });
}

// Доступно глобально - використовується компонентом <x-admin.image-upload>
// для стиснення фото перед завантаженням у Livewire.
window.adminCompressImage = downscaleImage;

// Завантаження зображень, вставлених прямо в текст статті (Trix attachments):
// шлемо (стиснений) файл на сервер і підставляємо отриманий URL у контент.
document.addEventListener('trix-attachment-add', (event) => {
    const attachment = event.attachment;
    if (! attachment.file) return; // не файл (напр. вставлений існуючий URL)

    const editor = event.target;
    const url = editor.dataset.uploadUrl;
    const token = document.querySelector('meta[name="csrf-token"]')?.content;

    downscaleImage(attachment.file)
        .then((blob) => {
            const form = new FormData();
            const base = (attachment.file.name || 'image').replace(/\.[^.]+$/, '');
            const ext = blob.type === 'image/png' ? '.png' : (blob.type === 'image/webp' ? '.webp' : '.jpg');
            form.append('file', blob, base + ext);

            return fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token, Accept: 'application/json' },
                body: form,
            });
        })
        .then(async (r) => {
            if (r.ok) return r.json();
            // Дістаємо зрозуміле повідомлення про помилку.
            let msg = 'Не вдалося завантажити зображення.';
            if (r.status === 413) msg = 'Зображення завелике для сервера.';
            else if (r.status === 419) msg = 'Сесія застаріла - оновіть сторінку.';
            else {
                try {
                    const data = await r.json();
                    if (data.errors?.file?.[0]) msg = data.errors.file[0];
                    else if (data.message) msg = data.message;
                } catch (e) { /* ignore */ }
            }
            return Promise.reject(msg);
        })
        // Лише url, без href - інакше Trix обгортає зображення у посилання
        // на файл (на сайті клік відкривав би картинку окремою сторінкою).
        .then((d) => attachment.setAttributes({ url: d.url }))
        .catch((err) => {
            attachment.remove();
            alert(typeof err === 'string' ? err : 'Не вдалося завантажити зображення.');
        });
});

// id прямих дочірніх вузлів у порядку відображення
function childIds(ul) {
    return Array.from(ul.querySelectorAll(':scope > .cat-node')).map((li) => li.dataset.id);
}

function initSortables() {
    document.querySelectorAll('.cat-sortable').forEach((ul) => {
        if (ul._srtInit) return;
        ul._srtInit = true;

        Sortable.create(ul, {
            handle: '.cat-handle',
            draggable: '.cat-node',
            animation: 150,
            // сортуємо лише в межах одного батька (різні групи - без переносу)
            group: 'cat-' + (ul.dataset.parent || 'root'),
            onEnd: () => {
                const wireEl = ul.closest('[wire\\:id]');
                if (!wireEl || !window.Livewire) return;
                const comp = window.Livewire.find(wireEl.getAttribute('wire:id'));
                if (comp) comp.call('reorder', childIds(ul));
            },
        });
    });
}

// ── Галерея фото товару (OLX-стиль): превʼю, видалення, ліміт, порядок ──────
// Клієнтський аплоадер: стискаємо фото у браузері, тримаємо їх у локальному
// масиві (джерело правди для порядку) і синхронізуємо у Livewire через
// uploadMultiple. Контейнер має wire:ignore - DOM повністю наш.
function initGalleryUploaders() {
    document.querySelectorAll('.gallery-uploader').forEach((root) => {
        if (root._galInit) return;
        root._galInit = true;

        const model = root.dataset.model;
        const max = parseInt(root.dataset.max || '8', 10);
        const saved = parseInt(root.dataset.saved || '0', 10);
        const immediate = root.dataset.immediate === '1';
        const grid = root.querySelector('[data-pending-grid]');
        const input = root.querySelector('input[type="file"]');
        const addLabel = root.querySelector('[data-add]');
        const hint = root.querySelector('[data-hint]');

        const items = [];
        let uidc = 0;

        const wireEl = root.closest('[wire\\:id]');
        const comp = () =>
            window.Livewire && wireEl ? window.Livewire.find(wireEl.getAttribute('wire:id')) : null;

        const savedCount = () => {
            const g = root.parentElement?.querySelector('[data-reorder-gallery]');
            return g ? g.querySelectorAll('.photo-thumb').length : saved;
        };
        const remaining = () => max - savedCount() - items.length;

        function updateHint() {
            const r = remaining();
            hint.textContent = r > 0
                ? `Можна додати ще ${r} фото`
                : `Досягнуто ліміту: ${max} фото`;
            addLabel.style.display = r > 0 ? '' : 'none';
        }

        async function compressFiles(fileList) {
            const out = [];
            for (const f of Array.from(fileList)) {
                if (max - savedCount() - items.length - out.length <= 0) {
                    alert(`Можна додати щонайбільше ${max} фото.`);
                    break;
                }
                if (!/^image\//.test(f.type)) continue;
                const blob = await Promise.resolve(window.adminCompressImage ? window.adminCompressImage(f) : f);
                const ext = blob.type === 'image/png' ? '.png' : (blob.type === 'image/webp' ? '.webp' : '.jpg');
                out.push(new File([blob], (f.name || 'image').replace(/\.[^.]+$/, '') + ext, { type: blob.type }));
            }
            return out;
        }

        if (immediate) {
            input.addEventListener('change', async (e) => {
                const files = await compressFiles(e.target.files);
                e.target.value = '';
                if (!files.length) return;
                grid.innerHTML = files.map(() =>
                    '<div class="photo-thumb"><div class="photo-thumb__busy">Завантаження…</div></div>').join('');
                addLabel.style.display = 'none';
                const c = comp();
                if (!c) { grid.innerHTML = ''; updateHint(); return; }
                c.uploadMultiple(model, files,
                    () => { grid.innerHTML = ''; updateHint(); },
                    () => { grid.innerHTML = ''; updateHint(); alert('Не вдалося завантажити деякі фото.'); },
                    () => {}
                );
            });
            updateHint();
            return;
        }

        function sync() {
            const c = comp();
            if (!c) return;
            c.uploadMultiple(
                model,
                items.map((i) => i.file),
                () => {},
                () => { alert('Не вдалося завантажити деякі фото.'); },
                () => {}
            );
        }

        function render() {
            grid.innerHTML = '';
            items.forEach((it) => {
                const d = document.createElement('div');
                d.className = 'photo-thumb';
                d.dataset.uid = it.uid;
                d.innerHTML =
                    '<span class="drag-handle" title="Перетягнути">⠿</span>' +
                    '<img alt="">' +
                    '<button type="button" class="photo-del">×</button>';
                d.querySelector('img').src = it.url;
                d.querySelector('.photo-del').addEventListener('click', () => removeItem(it.uid));
                grid.appendChild(d);
            });
            updateHint();
        }

        function removeItem(uid) {
            const i = items.findIndex((x) => x.uid === uid);
            if (i < 0) return;
            URL.revokeObjectURL(items[i].url);
            items.splice(i, 1);
            render();
            sync();
        }

        input.addEventListener('change', async (e) => {
            const files = await compressFiles(e.target.files);
            e.target.value = '';
            files.forEach((file) => items.push({ uid: ++uidc, file, url: URL.createObjectURL(file) }));
            render();
            sync();
        });

        Sortable.create(grid, {
            handle: '.drag-handle',
            draggable: '.photo-thumb',
            animation: 150,
            onEnd: () => {
                const order = Array.from(grid.querySelectorAll('.photo-thumb'))
                    .map((el) => parseInt(el.dataset.uid, 10));
                items.sort((a, b) => order.indexOf(a.uid) - order.indexOf(b.uid));
                sync();
            },
        });

        render();
    });
}

// Зміна порядку вже збережених фото галереї - як у списку категорій.
function initGallerySaved() {
    document.querySelectorAll('[data-reorder-gallery]').forEach((grid) => {
        if (grid._galSavedInit) return;
        grid._galSavedInit = true;

        Sortable.create(grid, {
            handle: '.drag-handle',
            draggable: '.photo-thumb',
            animation: 150,
            onEnd: () => {
                const ids = Array.from(grid.querySelectorAll('.photo-thumb')).map((el) => el.dataset.id);
                const wireEl = grid.closest('[wire\\:id]');
                if (!wireEl || !window.Livewire) return;
                const compInst = window.Livewire.find(wireEl.getAttribute('wire:id'));
                if (compInst) compInst.call('reorderGallery', ids);
            },
        });
    });
}

// Надійний тригер незалежно від версії Livewire: спостерігаємо за DOM
// і переініціалізовуємо нові списки (усі init-функції ідемпотентні).
let scheduled = false;
function scheduleInit() {
    if (scheduled) return;
    scheduled = true;
    requestAnimationFrame(() => {
        scheduled = false;
        initSortables();
        initGalleryUploaders();
        initGallerySaved();
    });
}

// ── Підтвердження дій через гарну модалку замість нативного confirm() ──────
// Перехоплюємо клік по елементах з [data-confirm] у фазі захоплення (раніше
// за обробники Livewire), показуємо модалку, і лише після «Так» повторно
// «клікаємо» елемент - тоді спрацьовує його wire:click / submit.
function initConfirm() {
    if (window.__adminConfirmInit) return;
    window.__adminConfirmInit = true;

    document.addEventListener(
        'click',
        (e) => {
            const el = e.target.closest('[data-confirm]');
            if (!el) return;

            // Повторний (уже підтверджений) клік - пропускаємо далі.
            if (el.__confirmed) {
                el.__confirmed = false;
                return;
            }

            e.preventDefault();
            e.stopImmediatePropagation();

            window.dispatchEvent(
                new CustomEvent('admin-confirm', {
                    detail: {
                        message: el.getAttribute('data-confirm'),
                        accept: () => {
                            el.__confirmed = true;
                            el.click();
                        },
                    },
                })
            );
        },
        true // capture
    );
}

// ── Захист від втрати незбережених змін ───────────────────────────────────
// Форма з [data-dirty-guard] стежить за правками. Якщо є незбережені зміни,
// попереджаємо при: закритті/оновленні вкладки (нативно) та переході геть
// (Скасувати / ← До списку / меню - будь-яке wire:navigate) - гарною модалкою.
function initDirtyGuard() {
    if (window.__dirtyGuardInit) return;
    window.__dirtyGuardInit = true;

    let dirty = false;
    const setDirty = (v) => { dirty = v; };

    const inGuard = (node) =>
        node instanceof Element && node.closest('[data-dirty-guard]');

    // Правка будь-якого поля всередині форми → є незбережені зміни.
    document.addEventListener('input', (e) => { if (inGuard(e.target)) setDirty(true); });
    document.addEventListener('change', (e) => { if (inGuard(e.target)) setDirty(true); });
    // Редактор статей (Trix) і кастомні селекти не мають native input/change.
    document.addEventListener('trix-change', (e) => { if (inGuard(e.target)) setDirty(true); });
    document.addEventListener('click', (e) => {
        if (e.target.closest && e.target.closest('[data-dirty-guard] .aselect__opt')) setDirty(true);
    });

    // Сабміт форми (Зберегти) → зміни збережено, попередження не потрібне.
    document.addEventListener('submit', (e) => { if (inGuard(e.target)) setDirty(false); }, true);

    // Закриття / оновлення вкладки або зовнішній перехід.
    window.addEventListener('beforeunload', (e) => {
        if (!dirty) return;
        e.preventDefault();
        e.returnValue = '';
    });

    // Перехід усередині застосунку (wire:navigate: Скасувати, ← До списку,
    // меню). Офіційний відмінюваний хук Livewire - гарантовано спиняє перехід,
    // поки користувач не підтвердить у модалці. Нікуди не виходимо без «Так».
    document.addEventListener('livewire:navigate', (e) => {
        if (!dirty) return;

        const url = e.detail?.url?.toString();
        e.preventDefault(); // спиняємо навігацію

        window.dispatchEvent(
            new CustomEvent('admin-confirm', {
                detail: {
                    message: 'Є незбережені зміни. Залишити сторінку без збереження?',
                    accept: () => {
                        setDirty(false);
                        if (url && window.Livewire?.navigate) window.Livewire.navigate(url);
                    },
                },
            })
        );
    });

    // Нова сторінка завантажилась - скидаємо стан.
    document.addEventListener('livewire:navigated', () => setDirty(false));
}

// ── Перегляд зображень (лайтбокс) ─────────────────────────────────────────
// Один делегований обробник на весь документ: клік по контентному зображенню
// відкриває його збільшену версію на весь екран. Джерело великого зображення -
// data-zoom-src (якщо задано), інакше поточний src самого прев'ю.
// Опрацьовуємо у фазі захоплення, щоб випередити wire:click і посилання
// (напр. фото «в роботі», огорнуте <a href="…large">).
function initImageZoom() {
    if (window.__adminZoomInit) return;
    window.__adminZoomInit = true;

    const SELECTOR = 'img[data-zoom], .photo-thumb img, .field-photo img, img.product-thumb';

    const overlay = document.createElement('div');
    overlay.className = 'img-zoom';
    overlay.innerHTML =
        '<button type="button" class="img-zoom__close" aria-label="Закрити">×</button>' +
        '<img class="img-zoom__img" alt="">';
    document.body.appendChild(overlay);
    const zoomImg = overlay.querySelector('.img-zoom__img');

    const open = (src) => {
        zoomImg.src = src;
        overlay.classList.add('is-open');
        document.body.classList.add('img-zoom-lock');
    };
    const close = () => {
        overlay.classList.remove('is-open');
        document.body.classList.remove('img-zoom-lock');
        zoomImg.src = '';
    };

    document.addEventListener(
        'click',
        (e) => {
            if (overlay.contains(e.target)) return; // клік усередині лайтбоксу
            const img = e.target.closest(SELECTOR);
            if (!img) return;

            const src = img.getAttribute('data-zoom-src') || img.currentSrc || img.src;
            if (!src) return;

            e.preventDefault();
            e.stopPropagation();
            open(src);
        },
        true // capture - раніше за обробники Livewire / переходи посилань
    );

    overlay.addEventListener('click', (e) => {
        // Клік по тлу або хрестику закриває; по самому зображенні - ні.
        if (e.target !== zoomImg) close();
    });
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && overlay.classList.contains('is-open')) close();
    });
}

function boot() {
    initSortables();
    initGalleryUploaders();
    initGallerySaved();
    initConfirm();
    initDirtyGuard();
    initImageZoom();
    new MutationObserver(scheduleInit).observe(document.body, { childList: true, subtree: true });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}
document.addEventListener('livewire:navigated', scheduleInit);
