@props(['model', 'accept' => 'image/*', 'label' => 'Вибрати фото'])

{{--
    Завантаження одного фото зі стисненням у браузері перед відправкою на сервер
    (обходить ліміт PHP upload_max_filesize, зручно для фото з телефона).
    Показує превʼю вибраного фото та власну кнопку (без «файл не вибрано»).

    Використання (всередині Livewire-компонента):
        <x-admin.image-upload model="photo" />
    де `photo` — публічна властивість WithFileUploads.
--}}
<div wire:ignore x-data="{
        busy: false, pct: 0, preview: null,
        init() {
            // Коли хост-компонент скидає файл (напр. після додавання) — прибираємо превʼю.
            $wire.$watch('{{ $model }}', (v) => {
                if (! v && this.preview) { URL.revokeObjectURL(this.preview); this.preview = null; }
            });
        },
        pick(e) {
            const f = e.target.files[0];
            if (! f) return;
            if (this.preview) URL.revokeObjectURL(this.preview);
            this.preview = URL.createObjectURL(f);

            const input = e.target;
            const done = () => { this.busy = false; input.value = ''; };
            Promise.resolve(window.adminCompressImage ? window.adminCompressImage(f) : f).then((blob) => {
                const ext = blob.type === 'image/png' ? '.png' : (blob.type === 'image/webp' ? '.webp' : '.jpg');
                const file = new File([blob], (f.name || 'image').replace(/\.[^.]+$/, '') + ext, { type: blob.type });
                this.busy = true; this.pct = 0;
                $wire.upload('{{ $model }}', file,
                    () => done(),
                    () => { done(); alert('Не вдалося завантажити фото.'); },
                    (ev) => { this.pct = ev.detail.progress; }
                );
            });
        },
        clear() {
            if (this.preview) { URL.revokeObjectURL(this.preview); this.preview = null; }
            $wire.set('{{ $model }}', null);
        }
     }">
    <template x-if="preview">
        <div class="photo-thumb">
            <img :src="preview" alt="">
            <button type="button" class="photo-del" x-on:click="clear()">×</button>
            <div class="photo-thumb__busy" x-show="busy" x-cloak>Завантаження <span x-text="pct"></span>%…</div>
        </div>
    </template>

    <label class="upload-btn">
        <input type="file" accept="{{ $accept }}" hidden x-on:change="pick($event)">
        <span x-text="preview ? 'Змінити фото' : @js($label)"></span>
    </label>
</div>
