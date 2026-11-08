<?php

function pf_etykieta(string $tekst): string
{
    return '<span class="pit-etykieta">' . trim(preg_replace('/\s+/', ' ', $tekst)) . '</span>';
}

function pf_tagi(string $form): array
{
    preg_match_all('/\[(\w+\*?)\s[^\]]*\]/u', $form, $m);
    sort($m[0]);

    return $m[0];
}

function pf_uklad(string $form): string
{
    $form = str_replace(["\r\n", 'target="blank_"'], ["\n", 'target="_blank"'], $form);

    $form = preg_replace_callback(
        '#<ul>(.*?)</ul>#su',
        function ($m) {
            preg_match_all('#<label>\s*(.*?)\s*(\[number[^\]]*\])\s*</label>#su', $m[1], $pola, PREG_SET_ORDER);
            $html = '';

            foreach ($pola as $pole) {
                $html .= '<label class="pit-pole">' . pf_etykieta($pole[1]) . $pole[2] . '</label>';
            }

            return '<div class="pit-siatka pit-siatka--2">' . $html . '</div>';
        },
        $form
    );

    $form = preg_replace_callback(
        '#<use_label_element>\s*(.*?)\s*(\[radio[^\]]*\])\s*</use_label_element>#su',
        fn($m) => '<div class="pit-pole pit-pole--wybor">' . pf_etykieta($m[1]) . $m[2] . '</div>',
        $form
    );

    $form = preg_replace_callback(
        '#(?:<label>\s*\[number [^\]]*placeholder "[^"]*"\]\s*</label>\s*)+#su',
        function ($m) {
            preg_match_all('#\[number [^\]]*placeholder "([^"]*)"\]#u', $m[0], $pola, PREG_SET_ORDER);
            $html = '';

            foreach ($pola as $pole) {
                $html .= '<label class="pit-pole pit-pole--krotkie"><span class="pit-etykieta pit-etykieta--mala">' . $pole[1] . '</span>' . $pole[0] . '</label>';
            }

            return '<div class="pit-siatka pit-siatka--3">' . $html . "</div>\n";
        },
        $form
    );

    $form = preg_replace(
        '#<label>\s*(\[file [^\]]*\])\s*</label>#u',
        '<label class="pit-pole pit-pole--plik">$1</label>',
        $form
    );

    $form = preg_replace_callback(
        '#<label>\s*(.+?)\s*(\[(?:text|email|date|number|file)\*?\s[^\]]*\])\s*</label>#su',
        function ($m) {
            $klasa = 'pit-pole';

            if (str_starts_with(trim($m[1]), 'Jeżeli TAK')) {
                $klasa .= ' pit-pole--zalezne';
            }

            if (str_contains($m[2], 'adres-zamieszkania')) {
                $klasa .= ' pit-pole--szerokie';
            }

            return '<label class="' . $klasa . '">' . pf_etykieta($m[1]) . $m[2] . '</label>';
        },
        $form
    );

    $form = preg_replace('#<b>(.*?)</b>#u', '<h3 class="pit-sekcja">$1</h3>', $form);

    $form = preg_replace_callback(
        '#(<h3 class="pit-sekcja">Dane osobowe</h3>)\s*((?:<label class="pit-pole[^"]*">.*?</label>\s*)+)#su',
        fn($m) => $m[1] . "\n" . '<div class="pit-siatka pit-siatka--2">' . trim($m[2]) . "</div>\n",
        $form
    );

    $form = preg_replace('#\[submit ([^\]]*)\]#u', '<div class="pit-wyslij">[submit $1]</div>', $form);

    $linie = [];

    foreach (explode("\n", $form) as $linia) {
        $tekst = trim($linia);

        if ($tekst === '') {
            continue;
        }

        if (!str_starts_with($tekst, '<') && !str_starts_with($tekst, '[')) {
            $tekst = '<p class="pit-opis">' . $tekst . '</p>';
        }

        $linie[] = $tekst;
    }

    return implode("\n", $linie);
}

if (defined('WP_CLI') && WP_CLI && !defined('PF_TYLKO_FUNKCJE')) {
    foreach (get_posts(['post_type' => 'wpcf7_contact_form', 'numberposts' => -1, 'post_status' => 'any']) as $post) {
        $form = (string) get_post_meta($post->ID, '_form', true);

        if (str_contains($form, 'pit-pole')) {
            WP_CLI::log("  {$post->post_title}: już przebudowany");
            continue;
        }

        $nowy = pf_uklad($form);

        if (pf_tagi($form) !== pf_tagi($nowy)) {
            WP_CLI::error("{$post->post_title}: zmieniłby pola formularza — przerwano");
        }

        if (!get_post_meta($post->ID, '_form_oryginal', true)) {
            update_post_meta($post->ID, '_form_oryginal', wp_slash($form));
        }

        update_post_meta($post->ID, '_form', wp_slash($nowy));
        WP_CLI::log(sprintf('  %s: przebudowany, pól: %d', $post->post_title, count(pf_tagi($nowy))));
    }
}
