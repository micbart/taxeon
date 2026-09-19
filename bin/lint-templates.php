<?php

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (!file_exists($autoload)) {
    fwrite(STDERR, "Uruchom najpierw: composer install\n");
    exit(1);
}

require $autoload;

$viewsDir = $root . '/views';

$loader = new \Twig\Loader\FilesystemLoader($viewsDir);
$twig = new \Twig\Environment($loader, ['cache' => false]);

foreach (
    [
        'svg', 'svgUrl', 'termLink', 'termColor', 'readTime', 'inlineHtml', 'wpDate', 'shopPrice', 'arrayToJson',
        'jsonToArray', 'getPermalink', 'excerpt', 'wpautop', 'shortcodes',
    ] as $filter
) {
    $twig->addFilter(new \Twig\TwigFilter($filter, static fn($value = null, ...$rest) => $value));
}

foreach (
    [
        '__', '_e', '_x', '_n', 'fn', 'function', 'get_image', 'get_post',
        'get_posts', 'get_term', 'get_terms', 'get_user', 'shopAvailableMetaQuery', 'getOption', 'block_index',
        'action', 'shortcode', 'bloginfo',
    ] as $function
) {
    $twig->addFunction(new \Twig\TwigFunction($function, static fn(...$args) => ''));
}

$errors = 0;
$checked = 0;

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));

foreach ($files as $file) {
    if ($file->getExtension() !== 'twig') {
        continue;
    }

    $checked++;
    $name = str_replace($viewsDir . '/', '', $file->getPathname());
    $source = (string) file_get_contents($file->getPathname());

    try {
        $twig->parse($twig->tokenize(new \Twig\Source($source, $name)));
    } catch (\Twig\Error\Error $e) {
        $errors++;
        printf("SKŁADNIA  %s:%d  %s\n", $name, $e->getTemplateLine(), $e->getRawMessage());
    }

    if (preg_match_all('/class="([^"]*)"/s', $source, $matches)) {
        foreach ($matches[1] as $index => $value) {
            if (!str_contains($value, '>')) {
                continue;
            }

            $errors++;
            $line = substr_count(substr($source, 0, strpos($source, $matches[0][$index])), "\n") + 1;
            printf(
                "ATRYBUT   %s:%d  znak „>” w class rozwali wptexturize — użyj [&_x] zamiast [&>x]\n",
                $name,
                $line
            );
        }
    }
}

if ($errors === 0) {
    printf("✓ Szablony Twig bez zastrzeżeń (%d plików)\n", $checked);
    exit(0);
}

printf("\n✗ Zastrzeżeń: %d (na %d plikach)\n", $errors, $checked);
exit(1);
