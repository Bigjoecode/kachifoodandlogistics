<?php
/**
 * Inline SVG icon set.
 *
 * One visual language throughout: 24x24 viewBox, 1.75 stroke, round caps and
 * joins, no fills. Inline rather than a sprite so icons inherit currentColor
 * and can be sized with utility classes.
 */

function icon_paths(): array
{
    return [
        // --- Navigation & chrome ---------------------------------------
        'menu'        => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'close'       => '<path d="M18 6 6 18M6 6l12 12"/>',
        'chevron-right' => '<path d="m9 6 6 6-6 6"/>',
        'chevron-down'  => '<path d="m6 9 6 6 6-6"/>',
        'arrow-right' => '<path d="M4 12h15M13 6l6 6-6 6"/>',
        'arrow-left'  => '<path d="M20 12H5M11 18l-6-6 6-6"/>',
        'external'    => '<path d="M14 4h6v6M20 4l-8 8M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
        'search'      => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'filter'      => '<path d="M3 5h18l-7 8v6l-4 2v-8L3 5Z"/>',

        // --- Commerce ---------------------------------------------------
        'cart'        => '<circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h2.5l2.6 11.4a1.5 1.5 0 0 0 1.5 1.1h9.1a1.5 1.5 0 0 0 1.5-1.2L21 7H6"/>',
        'package'     => '<path d="M12 3 3 7.5v9L12 21l9-4.5v-9L12 3Z"/><path d="M3 7.5 12 12l9-4.5M12 12v9"/>',
        'tag'         => '<path d="M12.6 3H20a1 1 0 0 1 1 1v7.4a1 1 0 0 1-.3.7l-8.6 8.6a1 1 0 0 1-1.4 0l-7.4-7.4a1 1 0 0 1 0-1.4l8.6-8.6a1 1 0 0 1 .7-.3Z"/><circle cx="16.5" cy="7.5" r="1.3"/>',
        'banknote'    => '<rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/><path d="M6 12h.01M18 12h.01"/>',
        'receipt'     => '<path d="M5 3h14v18l-2.3-1.5L14.4 21l-2.4-1.5L9.6 21l-2.3-1.5L5 21V3Z"/><path d="M9 8h6M9 12h6"/>',
        'percent'     => '<path d="m19 5-14 14"/><circle cx="7.5" cy="7.5" r="2.5"/><circle cx="16.5" cy="16.5" r="2.5"/>',

        // --- Logistics --------------------------------------------------
        'truck'       => '<path d="M3 6h10a1 1 0 0 1 1 1v9H3a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z"/><path d="M14 9h3.6a1 1 0 0 1 .8.4L21 13v3h-7"/><circle cx="7" cy="18" r="2"/><circle cx="17.5" cy="18" r="2"/><path d="M9 18h6.5"/>',
        'route'       => '<circle cx="6" cy="6" r="2.5"/><circle cx="18" cy="18" r="2.5"/><path d="M8.5 6H15a3 3 0 0 1 0 6H9a3 3 0 0 0 0 6h6.5"/>',
        'warehouse'   => '<path d="M3 21V9l9-5 9 5v12"/><path d="M7 21v-7h10v7"/><path d="M7 17h10"/>',
        'clock'       => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5.5l3.5 2"/>',
        'calendar'    => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
        'scale'       => '<path d="M12 4v16M7 20h10"/><path d="M12 7 5 9l-2 5a4 4 0 0 0 8 0L9 9"/><path d="m12 7 7 2 2 5a4 4 0 0 1-8 0l2-5"/>',
        'map-pin'     => '<path d="M12 21s7-5.6 7-11a7 7 0 1 0-14 0c0 5.4 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/>',
        'shield'      => '<path d="M12 3 5 6v5.5c0 4.6 3 8 7 9.5 4-1.5 7-4.9 7-9.5V6l-7-3Z"/><path d="m9 12 2 2 4-4"/>',

        // --- Food categories --------------------------------------------
        'wheat'       => '<path d="M12 22V11"/><path d="M12 11c0-2 1.2-3.4 3-4 .3 2.2-.6 3.6-3 4Zm0 0c0-2-1.2-3.4-3-4-.3 2.2.6 3.6 3 4Z"/><path d="M12 7c0-2 1.2-3.4 3-4 .3 2.2-.6 3.6-3 4Zm0 0c0-2-1.2-3.4-3-4-.3 2.2.6 3.6 3 4Z"/><path d="M12 16c0-2 1.2-3.4 3-4 .3 2.2-.6 3.6-3 4Zm0 0c0-2-1.2-3.4-3-4-.3 2.2.6 3.6 3 4Z"/>',
        'leaf'        => '<path d="M4 20c0-8 4.5-13 16-13 0 8-4.5 13-11 13a5 5 0 0 1-5 0Z"/><path d="M9 15c2-3 5-5 9-6"/>',
        'sprout'      => '<path d="M12 21v-8"/><path d="M12 13c-4 0-6-2-6-6 4 0 6 2 6 6Z"/><path d="M12 13c0-3.5 2-5.5 6-5.5 0 3.5-2 5.5-6 5.5Z"/>',
        'droplet'     => '<path d="M12 3.5c3 3.4 5.5 6.6 5.5 9.5a5.5 5.5 0 1 1-11 0c0-2.9 2.5-6.1 5.5-9.5Z"/>',
        'beef'        => '<circle cx="12" cy="12" r="8.5"/><path d="M12 16.5a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9Z"/>',
        'flame'       => '<path d="M12 3c3.5 3.6 6 6.6 6 9.8A6 6 0 0 1 6 13c0-1.6.7-3 2-4.4.3 1.2 1 2 2 2.4-.4-3 .7-5.6 2-8Z"/>',
        'snowflake'   => '<path d="M12 3v18M4.2 7.5l15.6 9M19.8 7.5l-15.6 9"/><path d="m9.5 5 2.5 2.5L14.5 5M9.5 19l2.5-2.5 2.5 2.5"/>',
        'home'        => '<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V10Z"/><path d="M9 21v-7h6v7"/>',

        // --- People & contact -------------------------------------------
        'user'        => '<circle cx="12" cy="8" r="4"/><path d="M4.5 20a7.5 7.5 0 0 1 15 0"/>',
        'users'       => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 19a6.5 6.5 0 0 1 13 0"/><path d="M16 5.2a3.5 3.5 0 0 1 0 6.6M18 14.5a6 6 0 0 1 3.5 4.5"/>',
        'phone'       => '<rect x="6" y="2" width="12" height="20" rx="2.5"/><path d="M10.5 18.5h3"/>',
        'phone-call'  => '<path d="M8.4 3.5H5.2A2.2 2.2 0 0 0 3 5.8C3 13.7 10.3 21 18.2 21a2.2 2.2 0 0 0 2.3-2.2v-3.2l-4-1.6-1.6 2A14.6 14.6 0 0 1 7.8 9.6l2-1.6-1.4-4.5Z"/>',
        'mail'        => '<rect x="2.5" y="5" width="19" height="14" rx="2"/><path d="m3 7 8.4 5.6a1 1 0 0 0 1.2 0L21 7"/>',
        'message'     => '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 21l1.9-5.4A8 8 0 1 1 21 12Z"/>',
        'building'    => '<rect x="4" y="3" width="16" height="18" rx="1.5"/><path d="M9 7h2M13 7h2M9 11h2M13 11h2M9 15h2M13 15h2M10 21v-3h4v3"/>',

        // --- Feedback ---------------------------------------------------
        'check'       => '<path d="m5 13 4.5 4.5L19 7"/>',
        'check-circle'=> '<circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 5-5"/>',
        'alert'       => '<circle cx="12" cy="12" r="9"/><path d="M12 7.5v5M12 16h.01"/>',
        'info'        => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
        'star'        => '<path d="m12 3.5 2.6 5.4 5.9.8-4.3 4.2 1 5.9-5.2-2.8-5.2 2.8 1-5.9L3.5 9.7l5.9-.8L12 3.5Z"/>',
        'sparkle'     => '<path d="m12 3 2 6 6 2-6 2-2 6-2-6-6-2 6-2 2-6Z"/><path d="M19 15.5 19.8 18l2.2.8-2.2.7L19 22l-.8-2.5-2.2-.7 2.2-.8.8-2.5Z"/>',

        // --- Actions ----------------------------------------------------
        'plus'        => '<path d="M12 5v14M5 12h14"/>',
        'minus'       => '<path d="M5 12h14"/>',
        'trash'       => '<path d="M4 7h16M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/><path d="M6 7v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7"/><path d="M10 11v6M14 11v6"/>',
        'printer'     => '<path d="M7 8V3h10v5"/><rect x="3" y="8" width="18" height="8" rx="2"/><path d="M7 14h10v7H7z"/>',
        'copy'        => '<rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1"/>',
        'edit'        => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4 11.5-11.5Z"/>',
        'logout'      => '<path d="M15 4h3a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1h-3"/><path d="M10 8 6 12l4 4M6 12h10"/>',

        // --- Social ------------------------------------------------------
        'facebook'    => '<path d="M15 3h-2.4A3.6 3.6 0 0 0 9 6.6V9.5H6.6V13H9v8h3.5v-8h2.6l.5-3.5h-3.1V7.1c0-.6.3-.9.9-.9H15V3Z"/>',
        'instagram'   => '<rect x="3" y="3" width="18" height="18" rx="5.2"/><circle cx="12" cy="12" r="4"/><path d="M17.4 6.7h.01"/>',
        'tiktok'      => '<path d="M14.2 3v11.6a3.6 3.6 0 1 1-3.6-3.6c.35 0 .68.05 1 .14"/><path d="M14.2 3c.35 2.7 1.9 4.3 4.6 4.6"/>',
    ];
}

/**
 * Render an inline SVG icon.
 *
 * @param string $name  Key from icon_paths()
 * @param string $class Utility classes for sizing/colour (default 20px square)
 * @param string $label Accessible name. Empty renders it decorative (aria-hidden).
 */
function icon(string $name, string $class = 'size-5', string $label = ''): string
{
    $paths = icon_paths()[$name] ?? null;
    if ($paths === null) {
        return '';
    }

    $a11y = $label !== ''
        ? 'role="img" aria-label="' . e($label) . '"'
        : 'aria-hidden="true" focusable="false"';

    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor"'
        . ' stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" ' . $a11y . '>'
        . $paths . '</svg>';
}

/** Category slug -> icon name, with a sensible fallback. */
function category_icon(string $slug): string
{
    return [
        'grains-dry-foods'     => 'wheat',
        'vegetables'           => 'leaf',
        'tubers'               => 'sprout',
        'oils'                 => 'droplet',
        'proteins'             => 'beef',
        'spices-seasonings'    => 'flame',
        'frozen-foods'         => 'snowflake',
        'household-essentials' => 'home',
    ][$slug] ?? 'package';
}

/**
 * Configured social profiles, in display order. Empty settings are skipped,
 * so removing a URL in the back office removes the icon from the site.
 */
function social_links(): array
{
    $profiles = [
        'facebook'  => ['Facebook',  'facebook'],
        'instagram' => ['Instagram', 'instagram'],
        'tiktok'    => ['TikTok',    'tiktok'],
    ];

    $links = [];
    foreach ($profiles as $key => [$label, $ico]) {
        $url = trim((string) Setting::get($key, ''));
        if ($url !== '') {
            $links[] = ['url' => $url, 'label' => $label, 'icon' => $ico];
        }
    }
    return $links;
}
