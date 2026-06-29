@props(['model', 'accept' => 'image/*'])

{{--
    Завантаження фото зі стисненням у браузері перед відправкою на сервер
    (обходить ліміт PHP upload_max_filesize, зручно для фото з телефона).
    Стискаємо великі зображення, потім кладемо у Livewire-властивість через $wire.upload.

    Використання (всередині Livewire-компонента):
        <x-admin.image-upload model="photo" />
    де `photo` — публічна властивість WithFileUploads.
--}}
<div x-data="{ busy: false, pct: 0 }">
    <input type="file" accept="{{ $accept }}"
        x-on:change="
            const input = $event.target;
            const f = input.files[0];
            if (! f) return;

            const reset = () => { busy = false; input.value = ''; };
            Promise.resolve(window.adminCompressImage ? window.adminCompressImage(f) : f).then((blob) => {
                const ext = blob.type === 'image/png' ? '.png' : (blob.type === 'image/webp' ? '.webp' : '.jpg');
                const file = new File([blob], (f.name || 'image').replace(/\.[^.]+$/, '') + ext, { type: blob.type });
                busy = true; pct = 0;
                $wire.upload('{{ $model }}', file,
                    () => reset(),
                    () => { reset(); alert('Не вдалося завантажити фото.'); },
                    (e) => { pct = e.detail.progress; }
                );
            });
        ">
    <span x-show="busy" x-cloak style="color:#666; font-size:13px">Завантаження <span x-text="pct"></span>%…</span>
</div>
