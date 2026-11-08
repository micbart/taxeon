<?php

$spotkanie = ['title' => 'Umów rozmowę z księgową', 'url' => home_url('/umow-spotkanie/'), 'target' => ''];

update_field('article_cta_link', $spotkanie, 'option');

$blog = get_page_by_path('blog');

if ($blog) {
    update_field('archive_cta_link', $spotkanie, $blog->ID);
}

$opisy = [
    'branze' => 'Księgowość dla lekarzy, prawników, programistów i kontraktorów B2B. Stawki ryczałtu, zwolnienia z VAT i rozliczenia pisane pod Twój zawód.',
    'kalkulatory' => 'Darmowe kalkulatory dla przedsiębiorcy: forma opodatkowania, koszt księgowości, limit zwolnienia z VAT i limit kasy fiskalnej.',
    'polski-lad-kalkulator' => 'Porównaj ryczałt, skalę podatkową i podatek liniowy na swoich liczbach. Sprawdź, która forma opodatkowania zostawi Ci więcej.',
    'kalkulator-uslug-ksiegowych' => 'Sprawdź, ile kosztuje księgowość Twojej firmy w Taxeon. Ryczałt od {cena_ryczalt} zł, KPiR od {cena_kpir} zł netto miesięcznie.',
    'limit-uprawny' => 'Policz, ile możesz sprzedać bez rejestracji do VAT w tym roku, także gdy działalność rusza w trakcie roku.',
    'kasa-fiskalna' => 'Sprawdź limit obrotu bez kasy fiskalnej dla sprzedaży osobom prywatnym i to, kiedy musisz zacząć ewidencjonować na kasie.',
];

foreach ($opisy as $slug => $opis) {
    $strona = get_page_by_path($slug);

    if ($strona && !get_post_meta($strona->ID, 'rank_math_description', true)) {
        update_post_meta($strona->ID, 'rank_math_description', wp_slash($opis));
    }
}

require_once __DIR__ . '/patch-helpers.php';
nc_page('strona-glowna', [0 => ['cards_1_badge' => 'Najpopularniejszy']]);
nc_page('cennik', [1 => ['columns_0_note' => '{cena_aplikacja_start} / {cena_aplikacja} zł']]);

foreach (get_posts(['post_type' => 'page', 'post_status' => 'any', 'numberposts' => -1]) as $strona) {
    $bloki = parse_blocks($strona->post_content);

    foreach ($bloki as &$blok) {
        if ($blok['blockName'] === 'acf/latest-posts' && isset($blok['attrs']['data']['banner_link'])) {
            $blok['attrs']['data']['banner_link'] = ['title' => 'Przejdź do bloga', 'url' => home_url('/blog/'), 'target' => ''];
        }
    }

    unset($blok);
    nc_save($strona, $bloki);
}

$stopka = wp_get_nav_menu_object('Stopka — usługi');

if ($stopka) {
    $pozycje = wp_get_nav_menu_items($stopka->term_id);
    $blog = home_url('/blog/');
    $jest = array_filter($pozycje, fn($p) => untrailingslashit($p->url) === untrailingslashit($blog));

    foreach ($pozycje as $p) {
        if ($p->title === 'Baza wiedzy / Blog') {
            wp_update_post(['ID' => $p->ID, 'post_title' => 'Baza wiedzy']);

            if (!$jest) {
                wp_update_nav_menu_item($stopka->term_id, 0, [
                    'menu-item-title' => 'Blog',
                    'menu-item-url' => $blog,
                    'menu-item-type' => 'custom',
                    'menu-item-status' => 'publish',
                    'menu-item-position' => (int) $p->menu_order + 1,
                ]);
            }
        }
    }
}

if ($stopka) {
    $kolejnosc = 0;

    foreach (wp_get_nav_menu_items($stopka->term_id) as $p) {
        if ($p->title === 'Blog') {
            continue;
        }

        $kolejnosc++;
        wp_update_post(['ID' => $p->ID, 'menu_order' => $kolejnosc]);

        if ($p->title === 'Baza wiedzy') {
            foreach (wp_get_nav_menu_items($stopka->term_id) as $b) {
                if ($b->title === 'Blog') {
                    $kolejnosc++;
                    wp_update_post(['ID' => $b->ID, 'menu_order' => $kolejnosc]);
                }
            }
        }
    }
}

WP_CLI::success('Poprawki po weryfikacji zapisane');
