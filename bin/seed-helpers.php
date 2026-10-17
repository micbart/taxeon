<?php

/** @return array<string, array<int, array<string, mixed>>> */
function taxeon_seed_groups(): array
{
    static $groups = null;

    if ($groups !== null) {
        return $groups;
    }

    $groups = [];

    foreach (glob(get_template_directory() . '/acf-json/*.json') ?: [] as $file) {
        $group = json_decode((string) file_get_contents($file), true);
        $rule = $group['location'][0][0] ?? [];

        $scope = match ($rule['param'] ?? '') {
            'block' => (string) $rule['value'],
            'options_page' => 'options',
            default => (string) ($rule['value'] ?? 'other'),
        };

        $groups[$scope] = $group['fields'] ?? [];
    }

    return $groups;
}

function taxeon_seed_def(array $defs, string $name): array
{
    foreach ($defs as $def) {
        if ($def['name'] === $name) {
            return $def;
        }
    }

    WP_CLI::error("Brak pola „{$name}” w definicjach ACF. Uruchom bin/generate-acf-fields.php.");
}

function taxeon_seed_key(string $name, string $scope = 'options'): string
{
    return taxeon_seed_def(taxeon_seed_groups()[$scope] ?? [], $name)['key'];
}

function taxeon_seed_data(array $values, array $defs, string $prefix = ''): array
{
    $data = [];

    foreach ($values as $name => $value) {
        $def = taxeon_seed_def($defs, $name);
        $path = $prefix . $name;

        $data['_' . $path] = $def['key'];

        if ($def['type'] === 'repeater' && is_array($value)) {
            $data[$path] = count($value);

            foreach ($value as $index => $row) {
                $data += taxeon_seed_data($row, $def['sub_fields'], "{$path}_{$index}_");
            }

            continue;
        }

        if ($def['type'] === 'group' && is_array($value)) {
            $data += taxeon_seed_data($value, $def['sub_fields'], $path . '_');

            continue;
        }

        $data[$path] = $value;
    }

    return $data;
}

function taxeon_seed_block(string $slug, array $fields): string
{
    $anchor = $fields['anchor'] ?? '';
    unset($fields['anchor']);

    $attributes = [
        'name' => 'acf/' . $slug,
        'data' => taxeon_seed_data($fields, taxeon_seed_groups()['acf/' . $slug] ?? []),
        'mode' => 'preview',
    ];

    if ($anchor !== '') {
        $attributes['anchor'] = $anchor;
    }

    return '<!-- wp:acf/' . $slug . ' '
        . wp_json_encode($attributes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . ' /-->';
}

function taxeon_seed_image(string $relative, string $alt): int
{
    static $cache = [];

    if (isset($cache[$relative])) {
        return $cache[$relative];
    }

    $filename = basename($relative);
    $existing = get_posts([
        'post_type' => 'attachment',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'meta_query' => [
            [
                'key' => '_taxeon_seed_source',
                'value' => $relative,
            ],
        ],
    ]);

    if ($existing) {
        return $cache[$relative] = (int) $existing[0];
    }

    $source = dirname(get_template_directory(), 3) . '/projekt/assets/' . $relative;

    if (!file_exists($source)) {
        WP_CLI::warning("Brak pliku: {$relative}");

        return $cache[$relative] = 0;
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $upload = wp_upload_bits($filename, null, (string) file_get_contents($source));

    if (!empty($upload['error'])) {
        WP_CLI::warning("Nie udało się wgrać {$filename}: {$upload['error']}");

        return $cache[$relative] = 0;
    }

    $attachmentId = wp_insert_attachment([
        'post_mime_type' => wp_check_filetype($filename)['type'],
        'post_title' => sanitize_file_name(pathinfo($filename, PATHINFO_FILENAME)),
        'post_content' => '',
        'post_status' => 'inherit',
    ], $upload['file']);

    if (!$attachmentId) {
        return $cache[$relative] = 0;
    }

    wp_update_attachment_metadata($attachmentId, wp_generate_attachment_metadata($attachmentId, $upload['file']));
    update_post_meta($attachmentId, '_wp_attachment_image_alt', $alt);
    update_post_meta($attachmentId, '_taxeon_seed_source', $relative);

    WP_CLI::log("  media: {$filename} (ID {$attachmentId})");

    return $cache[$relative] = (int) $attachmentId;
}

function taxeon_seed_page(string $title, string $slug, string $subtitle = ''): int
{
    $existing = get_page_by_path($slug);

    if ($existing) {
        $id = (int) $existing->ID;
    } else {
        $id = (int) wp_insert_post([
            'post_type' => 'page',
            'post_title' => $title,
            'post_name' => $slug,
            'post_status' => 'publish',
            'post_content' => '',
        ]);

        WP_CLI::log("  strona: {$title} (ID {$id})");
    }

    if ($subtitle) {
        update_field(taxeon_seed_key('subtitle', 'page'), $subtitle, $id);
    }

    return $id;
}

/** @param array<int, array{title: string, url: string}> $items */
function taxeon_seed_menu(string $name, string $location, array $items): int
{
    $menu = wp_get_nav_menu_object($name);
    $menuId = $menu ? (int) $menu->term_id : (int) wp_create_nav_menu($name);

    if (is_wp_error($menuId) || !$menuId) {
        WP_CLI::warning("Nie udało się utworzyć menu {$name}");

        return 0;
    }

    foreach (wp_get_nav_menu_items($menuId) ?: [] as $item) {
        wp_delete_post($item->ID, true);
    }

    foreach ($items as $item) {
        wp_update_nav_menu_item($menuId, 0, [
            'menu-item-title' => $item['title'],
            'menu-item-url' => $item['url'],
            'menu-item-status' => 'publish',
            'menu-item-type' => 'custom',
        ]);
    }

    if ($location !== '') {
        $locations = get_theme_mod('nav_menu_locations', []);
        $locations[$location] = $menuId;
        set_theme_mod('nav_menu_locations', $locations);
    }

    WP_CLI::log('  menu: ' . $name . ($location !== '' ? " -> {$location}" : ''));

    return $menuId;
}

function uwagi_page(string $slug): int
{
    $page = get_page_by_path($slug);

    return $page ? (int) $page->ID : 0;
}

/** @param callable(array<string, mixed>, int): ?array<string, mixed> $mutate */
function uwagi_edit_blocks(int $pageId, string $blockName, callable $mutate): int
{
    if (!$pageId) {
        return 0;
    }

    $content = (string) get_post_field('post_content', $pageId);
    $changed = 0;
    $index = -1;

    $result = preg_replace_callback(
        '/<!-- wp:acf\/' . preg_quote($blockName, '/') . ' (\{.*?\}) (\/)?-->/s',
        static function (array $match) use ($mutate, $blockName, &$changed, &$index): string {
            $index++;

            $block = json_decode($match[1], true);

            if (!is_array($block)) {
                return $match[0];
            }

            $data = $mutate($block['data'] ?? [], $index);

            if ($data === null) {
                return $match[0];
            }

            $block['data'] = $data + uwagi_klucze(taxeon_seed_groups()['acf/' . $blockName] ?? [], $data);
            $changed++;

            $json = serialize_block_attributes($block);
            $selfClosing = ($match[2] ?? '') !== '';

            return '<!-- wp:acf/' . $blockName . ' ' . $json . ' ' . ($selfClosing ? '/' : '') . '-->';
        },
        $content
    );

    if ($changed && $result !== null && $result !== $content) {
        wp_update_post(['ID' => $pageId, 'post_content' => wp_slash($result)]);
    }

    return $changed;
}

/**
 * @param array<int, array<string, string>> $rows
 * @return array<string, mixed>
 */
function uwagi_repeater(string $block, string $field, array $rows): array
{
    $defs = taxeon_seed_groups()['acf/' . $block] ?? [];
    $def = taxeon_seed_def($defs, $field);

    $data = [
        '_' . $field => $def['key'],
        $field => count($rows),
    ];

    foreach ($rows as $i => $row) {
        $data += taxeon_seed_data($row, $def['sub_fields'], "{$field}_{$i}_");
    }

    return $data;
}

/** @return array<string, string> */
function uwagi_klucze(array $defs, array $data, string $prefix = ''): array
{
    $keys = [];

    foreach ($defs as $def) {
        $path = $prefix . $def['name'];

        if (($def['type'] ?? '') === 'repeater') {
            if (!isset($data[$path])) {
                continue;
            }

            $keys['_' . $path] = $def['key'];

            for ($i = 0; $i < (int) $data[$path]; $i++) {
                $keys += uwagi_klucze($def['sub_fields'] ?? [], $data, "{$path}_{$i}_");
            }

            continue;
        }

        if (($def['type'] ?? '') === 'group') {
            $sub = uwagi_klucze($def['sub_fields'] ?? [], $data, $path . '_');

            if ($sub) {
                $keys['_' . $path] = $def['key'];
                $keys += $sub;
            }

            continue;
        }

        if (array_key_exists($path, $data)) {
            $keys['_' . $path] = $def['key'];
        }
    }

    return $keys;
}
