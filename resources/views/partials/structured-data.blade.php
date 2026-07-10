{{-- Глобальні структуровані дані (JSON-LD) для Google:
     локальний бізнес (магазин автотоварів) + сайт із пошуком.
     Дані беруться з config('site') - їх перекриває адмінка «Контакти сайту». --}}
@php
    $site = config('site');
    $home = url('/');
    $c = $site['contacts'] ?? [];

    // Години роботи → OpeningHoursSpecification.
    $dayOrder = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Нд'];
    $dayMap = [
        'Пн' => 'Monday', 'Вт' => 'Tuesday', 'Ср' => 'Wednesday', 'Чт' => 'Thursday',
        'Пт' => 'Friday', 'Сб' => 'Saturday', 'Нд' => 'Sunday',
    ];
    $hoursSpec = [];
    foreach ((array) ($site['schedule'] ?? []) as $row) {
        $hours = $row['hours'] ?? '';
        if (mb_stripos($hours, 'вихід') !== false) {
            continue;
        }
        if (! preg_match('/(\d{1,2}:\d{2}).*?(\d{1,2}:\d{2})/u', $hours, $m)) {
            continue;
        }
        $days = array_map('trim', preg_split('/[-\--]/u', (string) ($row['days'] ?? '')));
        $list = [];
        if (count($days) === 2) {
            $start = array_search($days[0], $dayOrder, true);
            $end = array_search($days[1], $dayOrder, true);
            if ($start !== false && $end !== false) {
                for ($i = $start; $i <= $end; $i++) {
                    $list[] = $dayMap[$dayOrder[$i]];
                }
            }
        } elseif (isset($dayMap[$days[0]])) {
            $list[] = $dayMap[$days[0]];
        }
        if ($list) {
            $hoursSpec[] = ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => $list, 'opens' => $m[1], 'closes' => $m[2]];
        }
    }

    $address = trim((string) ($c['address'] ?? ''));
    $postal = null;
    if ($address && preg_match('/\b(\d{5})\b/', $address, $pm)) {
        $postal = $pm[1];
    }

    $socials = array_values(array_filter((array) ($site['socials'] ?? [])));

    $business = array_filter([
        '@context'   => 'https://schema.org',
        '@type'      => 'AutoPartsStore',
        'name'       => 'ВЕЛИКА ШИНА',
        'url'        => $home,
        'logo'       => url('/images/logo.png'),
        'image'      => url(\App\Models\Setting::get('share_image') ?: '/images/og-default.png'),
        'telephone'  => $c['phone_href'] ?? ($c['phone'] ?? null),
        'email'      => $c['email'] ?? null,
        'address'    => $address ? array_filter([
            '@type'           => 'PostalAddress',
            'streetAddress'   => $address,
            'addressLocality' => 'Київ',
            'postalCode'      => $postal,
            'addressCountry'  => 'UA',
        ]) : null,
        'openingHoursSpecification' => $hoursSpec ?: null,
        'sameAs'     => $socials ?: null,
        'priceRange' => '₴₴',
        'areaServed' => 'UA',
    ], fn ($v) => $v !== null && $v !== []);

    $website = [
        '@context' => 'https://schema.org',
        '@type'    => 'WebSite',
        'name'     => 'ВЕЛИКА ШИНА',
        'url'      => $home,
        'potentialAction' => [
            '@type'  => 'SearchAction',
            'target' => ['@type' => 'EntryPoint', 'urlTemplate' => url('/catalog') . '?q={search_term_string}'],
            'query-input' => 'required name=search_term_string',
        ],
    ];

    $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
@endphp
<script type="application/ld+json">{!! json_encode($business, $flags) !!}</script>
<script type="application/ld+json">{!! json_encode($website, $flags) !!}</script>
