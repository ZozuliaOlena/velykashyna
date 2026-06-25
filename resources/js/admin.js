// Адмін-скрипти. ВАЖЛИВО: не імпортуємо й не стартуємо Alpine —
// його надає Livewire. Тут лише drag-and-drop сортування (SortableJS).
import Sortable from 'sortablejs';

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

function boot() {
    initSortables();
    new MutationObserver(scheduleInit).observe(document.body, { childList: true, subtree: true });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}
document.addEventListener('livewire:navigated', scheduleInit);
