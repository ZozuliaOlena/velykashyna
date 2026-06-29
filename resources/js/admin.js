// Адмін-скрипти. ВАЖЛИВО: не імпортуємо й не стартуємо Alpine —
// його надає Livewire. Тут drag-and-drop (SortableJS) і редактор статей (Trix).
import Sortable from 'sortablejs';
import 'trix';
import 'trix/dist/trix.css';

// Зменшуємо великі зображення в браузері перед завантаженням, щоб не впертися
// в ліміт PHP upload_max_filesize і не ганяти зайві мегабайти.
function downscaleImage(file, maxDim = 1600, quality = 0.82) {
    return new Promise((resolve) => {
        // Растрові формати масштабуємо; svg/gif/інше — як є.
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
            else if (r.status === 419) msg = 'Сесія застаріла — оновіть сторінку.';
            else {
                try {
                    const data = await r.json();
                    if (data.errors?.file?.[0]) msg = data.errors.file[0];
                    else if (data.message) msg = data.message;
                } catch (e) { /* ignore */ }
            }
            return Promise.reject(msg);
        })
        // Лише url, без href — інакше Trix обгортає зображення у посилання
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
            // сортуємо лише в межах одного батька (різні групи — без переносу)
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

// Надійний тригер незалежно від версії Livewire: спостерігаємо за DOM
// і переініціалізовуємо нові списки (initSortables ідемпотентний).
let scheduled = false;
function scheduleInit() {
    if (scheduled) return;
    scheduled = true;
    requestAnimationFrame(() => {
        scheduled = false;
        initSortables();
    });
}

// ── Підтвердження дій через гарну модалку замість нативного confirm() ──────
// Перехоплюємо клік по елементах з [data-confirm] у фазі захоплення (раніше
// за обробники Livewire), показуємо модалку, і лише після «Так» повторно
// «клікаємо» елемент — тоді спрацьовує його wire:click / submit.
function initConfirm() {
    if (window.__adminConfirmInit) return;
    window.__adminConfirmInit = true;

    document.addEventListener(
        'click',
        (e) => {
            const el = e.target.closest('[data-confirm]');
            if (!el) return;

            // Повторний (уже підтверджений) клік — пропускаємо далі.
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

function boot() {
    initSortables();
    initConfirm();
    new MutationObserver(scheduleInit).observe(document.body, { childList: true, subtree: true });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}
document.addEventListener('livewire:navigated', scheduleInit);
