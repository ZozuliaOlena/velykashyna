<div>
    <h1>Контакти сайту</h1>
    <p style="color:#666; margin:.25rem 0 1.25rem">
        Телефони, email, адреса та посилання на месенджери/соцмережі. Ці значення
        показуються на сайті (шапка, футер, сторінка «Контакти»). Порожнє поле —
        лишається значення за замовчуванням.
    </p>

    <fieldset style="margin:0 0 1.25rem; padding:16px; border:1px solid #e3e6ec; border-radius:10px">
        <legend style="padding:0 8px"><strong>Контакти</strong></legend>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:14px">
            <div>
                <label>Телефон 1</label>
                <input wire:model="phone" type="text" style="width:100%" placeholder="+38 (067) 928 20 86">
                @error('phone') <span style="color:red">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Телефон 2 (необов'язково)</label>
                <input wire:model="phone2" type="text" style="width:100%" placeholder="+38 (0__) ___ __ __">
                @error('phone2') <span style="color:red">{{ $message }}</span> @enderror
            </div>
            <div>
                <label>Email</label>
                <input wire:model="email" type="email" style="width:100%" placeholder="velykashyna@ukr.net">
                @error('email') <span style="color:red">{{ $message }}</span> @enderror
            </div>
        </div>
        <div style="margin-top:14px">
            <label>Адреса</label>
            <input wire:model="address" type="text" style="width:100%" placeholder="вул. ..., м. Київ">
            @error('address') <span style="color:red">{{ $message }}</span> @enderror
        </div>
        <small style="color:#888; display:block; margin-top:.5rem">Посилання для дзвінка (tel:) будується автоматично з номера.</small>
    </fieldset>

    <fieldset style="margin:0 0 1.25rem; padding:16px; border:1px solid #e3e6ec; border-radius:10px">
        <legend style="padding:0 8px"><strong>Карта (сторінка «Контакти»)</strong></legend>
        <div style="margin-bottom:14px">
            <label>Адреса для кнопки «Прокласти маршрут»</label>
            <input wire:model="mapQuery" type="text" style="width:100%" placeholder="вул. ..., м. Київ">
            @error('mapQuery') <span style="color:red">{{ $message }}</span> @enderror
            <small style="color:#888; display:block">Відкриває Google Maps з маршрутом до цієї адреси.</small>
        </div>
        <div>
            <label>Вбудована карта (Google Maps)</label>
            <textarea wire:model="mapEmbed" rows="3" style="width:100%; font-family:monospace; font-size:12.5px"
                placeholder='Вставте код або посилання карти'></textarea>
            @error('mapEmbed') <span style="color:red">{{ $message }}</span> @enderror
            <small style="color:#888; display:block">
                Google Maps → знайдіть точку → «Поділитися» → «Вставити карту» → скопіюйте код
                (можна цілий &lt;iframe&gt; або лише посилання src).
            </small>
        </div>
    </fieldset>

    <fieldset style="margin:0 0 1.25rem; padding:16px; border:1px solid #e3e6ec; border-radius:10px">
        <legend style="padding:0 8px"><strong>Месенджери та соцмережі</strong></legend>
        <p style="color:#888; margin:0 0 1rem">Вставте повне посилання (напр. https://t.me/...). Порожнє — не показується.</p>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:12px">
            @foreach($socialLabels as $key => $label)
                <div>
                    <label>{{ $label }}</label>
                    <input wire:model="socials.{{ $key }}" type="text" style="width:100%"
                        placeholder="https://...">
                    @error('socials.'.$key) <span style="color:red">{{ $message }}</span> @enderror
                </div>
            @endforeach
        </div>
    </fieldset>

    <div>
        <button wire:click="save" data-confirm="Зберегти контакти сайту?">Зберегти</button>
    </div>
</div>
