<?php
require get_template_directory() . '/bin/seed-helpers.php';

$wpisy = json_decode((string) file_get_contents(get_template_directory() . '/bin/dane-blog.json'), true);

$strona = get_page_by_path('baza-wiedzy');
$stronaId = $strona ? (int) $strona->ID : (int) wp_insert_post([
    'post_type' => 'page', 'post_status' => 'publish',
    'post_title' => 'Baza wiedzy', 'post_name' => 'baza-wiedzy', 'post_content' => '',
]);

update_option('show_on_front', 'page');
update_option('page_for_posts', $stronaId);

update_field(taxeon_seed_key('archive_title', 'posts_page'), 'Księgowość bez tajemnic.', $stronaId);
update_field(taxeon_seed_key('archive_description', 'posts_page'), '<p>Piszemy o tym, o co najczęściej pytają nas Klienci. Po polsku, bez paragrafów — ryczałt, KPiR, ZUS, VAT i KSeF w praktyce.</p>', $stronaId);
update_field(taxeon_seed_key('archive_badge', 'posts_page'), 'Baza wiedzy Taxeon', $stronaId);
update_field(taxeon_seed_key('archive_cta_title', 'posts_page'), 'Masz pytanie, którego nie ma na blogu?', $stronaId);
update_field(taxeon_seed_key('archive_cta_text', 'posts_page'), 'Zapytaj księgową Taxeon. Odpowiadamy w ciągu jednego dnia roboczego.', $stronaId);
update_field(taxeon_seed_key('archive_cta_link', 'posts_page'), [
    'title' => 'Umów rozmowę z księgową', 'url' => home_url('/#kontakt'), 'target' => '',
], $stronaId);

update_field(taxeon_seed_key('author_role'), 'księgowa Taxeon', 'option');
update_field(taxeon_seed_key('article_cta_title'), 'Nie wiesz, co wybrać?', 'option');
update_field(taxeon_seed_key('article_cta_text'), 'Policzymy oba warianty na Twoich liczbach. Bezpłatnie, bez zobowiązań.', 'option');
update_field(taxeon_seed_key('article_cta_link'), [
    'title' => 'Napisz do nas', 'url' => home_url('/#kontakt'), 'target' => '',
], 'option');

$tresc = static function (string $temat): string {
    return implode("\n\n", [
        '<p>Ryczałt od przychodów ewidencjonowanych to najprostsza forma opodatkowania w Polsce — płacisz procent od przychodu i nie rozliczasz kosztów. Dla części działalności to najtańszy wariant, dla części najdroższy z możliwych.</p>',
        '<h2>Kto ma prawo do niższej stawki</h2>',
        '<p>O stawce decyduje faktyczny zakres wykonywanych czynności, a nie nazwa stanowiska w umowie. Warto, żeby umowa, opis na fakturze i kod PKWiU mówiły to samo — przy kontroli to pierwsza rzecz, którą sprawdza urząd.</p>',
        '<blockquote><p>O stawce decyduje faktyczny zakres wykonywanych czynności, a nie nazwa stanowiska w umowie B2B.</p></blockquote>',
        '<h2>Kiedy ryczałt przestaje się opłacać</h2>',
        '<p>Ryczałt wygrywa przy niskich kosztach. Sytuacje, w których zwykle lepiej wypada podatek liniowy:</p>',
        '<ul><li>kupujesz sprzęt, auto albo oprogramowanie o dużej wartości,</li><li>zatrudniasz podwykonawców,</li><li>wynajmujesz biuro albo magazyn,</li><li>rozliczasz się wspólnie z małżonkiem i korzystasz z ulg.</li></ul>',
        '<h2>Wyliczenie na konkretnych liczbach</h2>',
        '<p>Poniżej porównanie dla przychodu 20 000 zł netto miesięcznie przy dwóch poziomach kosztów. Kwoty poglądowe, bez ulg dodatkowych.</p>',
        '<table><thead><tr><th>Wariant</th><th>Ryczałt 12%</th><th>Liniowy 19%</th></tr></thead><tbody><tr><td>Koszty 1 000 zł</td><td>2 400 zł</td><td>3 610 zł</td></tr><tr><td>Koszty 5 000 zł</td><td>2 400 zł</td><td>2 850 zł</td></tr><tr><td>Koszty 9 000 zł</td><td>2 400 zł</td><td>2 090 zł</td></tr></tbody></table>',
        '<h2>Co zrobić przed wyborem formy</h2>',
        '<p>Zbierz dane z ostatnich dwunastu miesięcy: przychód, realne koszty, planowane większe zakupy i sytuację rodzinną. Na tej podstawie policzymy oba warianty — ' . esc_html($temat) . ' to decyzja na cały rok, więc warto ją oprzeć na liczbach, nie na przeczuciu.</p>',
    ]);
};

$autor = get_users(['role' => 'administrator', 'number' => 1, 'fields' => 'ID']);
$autorId = $autor ? (int) $autor[0] : 1;
wp_update_user(['ID' => $autorId, 'display_name' => 'Anna Kowalska',
    'description' => 'Księgowa Taxeon, rozlicza jednoosobowe działalności z branży IT od 9 lat.']);

$utworzone = 0;

foreach ($wpisy as [$kategoria, $tytul, $zajawka, $data]) {
    $termin = term_exists($kategoria, 'category') ?: wp_insert_term($kategoria, 'category');
    $slug = sanitize_title($tytul);
    $istnieje = get_page_by_path($slug, OBJECT, 'post');

    $id = (int) wp_insert_post(wp_slash([
        'ID' => $istnieje ? $istnieje->ID : 0,
        'post_type' => 'post', 'post_status' => 'publish',
        'post_title' => $tytul, 'post_name' => $slug,
        'post_excerpt' => $zajawka,
        'post_content' => $tresc($tytul),
        'post_date' => $data . ' 09:00:00',
        'post_author' => $autorId,
    ]));

    wp_set_post_categories($id, [(int) $termin['term_id']]);
    $utworzone++;
}

$domyslna = (int) get_option('default_category');

if ($domyslna && ($bezkategorii = get_term($domyslna, 'category')) && !is_wp_error($bezkategorii)) {
    if ($bezkategorii->count === 0) {
        WP_CLI::log("  pusta kategoria „{$bezkategorii->name}” zostaje ukryta w filtrach (hide_empty)");
    }
}

App\Services\ThemeCache::flush('options');
App\Services\ThemeCache::flush('menus');

WP_CLI::success("Wpisów: {$utworzone}, strona wpisów #{$stronaId}");
