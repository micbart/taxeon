<?php

const NC_NOWE_ADRESY = [
    'it' => 'branze/it',
    'lekarze' => 'branze/lekarze',
    'prawnicy' => 'branze/prawnicy',
    'biura-rachunkowe' => 'dla-biur-rachunkowych',
];

function nc_blocks(string $slug): array
{
    $post = get_page_by_path($slug) ?: (isset(NC_NOWE_ADRESY[$slug]) ? get_page_by_path(NC_NOWE_ADRESY[$slug]) : null);

    if (!$post) {
        WP_CLI::error("Brak strony {$slug}");
    }

    return [$post, parse_blocks($post->post_content)];
}

function nc_real_indexes(array $blocks): array
{
    $indexes = [];

    foreach ($blocks as $i => $block) {
        if ($block['blockName']) {
            $indexes[] = $i;
        }
    }

    return $indexes;
}

function nc_field_ref(array $data, string $key, string $block = ''): ?string
{
    if (isset($data['_' . $key])) {
        return $data['_' . $key];
    }

    if ($block !== '') {
        $path = $block . '.' . implode('.', preg_split('/_\d+_/', $key));
        $fieldKey = 'field_' . substr(md5('taxeon:' . $path), 0, 13);

        if (acf_get_field($fieldKey)) {
            return $fieldKey;
        }
    }

    $generic = preg_replace('/_\d+_/', '_0_', $key);

    while ($generic !== $key) {
        if (isset($data['_' . $generic])) {
            return $data['_' . $generic];
        }

        $key = $generic;
        $generic = preg_replace('/_\d+_/', '_0_', $key);
    }

    return null;
}

function nc_apply(array &$data, array $changes, string $block = ''): void
{
    foreach ($changes as $key => $value) {
        if ($value === null) {
            unset($data[$key], $data['_' . $key]);
            continue;
        }

        if (!isset($data['_' . $key])) {
            $ref = nc_field_ref($data, $key, $block);

            if ($ref) {
                $data['_' . $key] = $ref;
            }
        }

        $data[$key] = $value;
    }
}

function nc_save(WP_Post $post, array $blocks): bool
{
    $content = serialize_blocks($blocks);

    if ($content === $post->post_content) {
        return false;
    }

    wp_update_post(wp_slash(['ID' => $post->ID, 'post_content' => $content]));

    return true;
}

function nc_page(string $slug, array $perBlock): void
{
    [$post, $blocks] = nc_blocks($slug);
    $indexes = nc_real_indexes($blocks);

    foreach ($perBlock as $n => $changes) {
        if (!isset($indexes[$n])) {
            WP_CLI::warning("{$slug}: brak bloku nr {$n}");
            continue;
        }

        $i = $indexes[$n];

        if (!str_starts_with((string) $blocks[$i]['blockName'], 'acf/') || !is_array($blocks[$i]['attrs']['data'] ?? null)) {
            WP_CLI::warning("{$slug}: blok nr {$n} to {$blocks[$i]['blockName']}, pomijam");
            continue;
        }

        if (is_callable($changes)) {
            $changes = $changes($blocks[$i]['attrs']['data']);
        }

        nc_apply($blocks[$i]['attrs']['data'], $changes, str_replace('acf/', '', $blocks[$i]['blockName']));
    }

    $changed = nc_save($post, $blocks);
    WP_CLI::log(($changed ? '  zmieniono ' : '  bez zmian ') . $slug);
}

function nc_replace_everywhere(array $pairs): void
{
    $walk = function ($value) use (&$walk, $pairs) {
        if (is_array($value)) {
            return array_map($walk, $value);
        }

        return is_string($value) ? strtr($value, $pairs) : $value;
    };

    $posts = get_posts(['post_type' => ['page', 'post'], 'post_status' => 'any', 'numberposts' => -1]);
    $count = 0;

    foreach ($posts as $post) {
        $blocks = parse_blocks($post->post_content);

        $fix = function (array $list) use (&$fix, $walk): array {
            foreach ($list as &$block) {
                if (isset($block['attrs']['data'])) {
                    $block['attrs']['data'] = $walk($block['attrs']['data']);
                }

                if (!empty($block['innerBlocks'])) {
                    $block['innerBlocks'] = $fix($block['innerBlocks']);
                }

                if (!$block['blockName'] && isset($block['innerHTML'])) {
                    $block['innerHTML'] = $walk($block['innerHTML']);
                    $block['innerContent'] = $walk($block['innerContent']);
                }
            }

            return $list;
        };

        if (nc_save($post, $fix($blocks))) {
            $count++;
        }
    }

    WP_CLI::log("  podmiany tekstów: {$count} wpisów");
}

function nc_list(array $data, string $name, array $rows): array
{
    $changes = [$name => count($rows)];

    foreach ($rows as $i => $row) {
        foreach ($row as $sub => $value) {
            $changes["{$name}_{$i}_{$sub}"] = $value;
        }
    }

    foreach (array_keys($data) as $key) {
        if (preg_match('/^_?' . preg_quote($name, '/') . '_(\d+)_/', $key, $m) && (int) $m[1] >= count($rows)) {
            $changes[ltrim($key, '_')] = null;
        }
    }

    return $changes;
}

function nc_replace_link(string $from, string $to, array $skipSlugs = []): void
{
    $walk = function ($value) use (&$walk, $from, $to) {
        if (is_array($value)) {
            if (($value['url'] ?? null) === $from) {
                $value['url'] = $to;

                return $value;
            }

            return array_map($walk, $value);
        }

        return $value === $from ? $to : $value;
    };

    foreach (get_posts(['post_type' => 'page', 'post_status' => 'any', 'numberposts' => -1]) as $post) {
        if (in_array($post->post_name, $skipSlugs, true)) {
            continue;
        }

        $blocks = parse_blocks($post->post_content);

        foreach ($blocks as &$block) {
            if (isset($block['attrs']['data'])) {
                $block['attrs']['data'] = $walk($block['attrs']['data']);
            }
        }

        unset($block);
        nc_save($post, $blocks);
    }
}
