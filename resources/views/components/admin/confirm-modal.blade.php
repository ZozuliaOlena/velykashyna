<div class="admin-modal admin-confirm" x-cloak style="display:none"
     x-data="{ show: false, message: '', accept: null }"
     x-show="show"
     x-on:admin-confirm.window="message = $event.detail.message; accept = $event.detail.accept; show = true"
     x-on:keydown.escape.window="show = false"
     x-on:click.self="show = false">
    <div class="admin-modal__box admin-confirm__box">
        <div class="admin-confirm__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
        </div>
        <p class="admin-confirm__text" x-text="message"></p>
        <div class="admin-confirm__actions">
            <button type="button" class="btn-primary"
                    x-on:click="const a = accept; show = false; if (a) a()">Так</button>
            <button type="button" x-on:click="show = false">Скасувати</button>
        </div>
    </div>
</div>
