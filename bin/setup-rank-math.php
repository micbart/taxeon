<?php
/** Podstawowa konfiguracja Rank Math + opisy meta dla stron zbudowanych z makiety. */

// 404-monitor i redirections wymagaja wlasnych tabel; wlaczamy je dopiero na produkcji
$moduly = ['sitemap', 'rich-snippet', 'seo-analysis', 'link-counter'];
update_option('rank_math_modules', $moduly);
update_option('rank_math_wizard_completed', true);
update_option('rank_math_is_configured', true);
update_option('rank_math_registration_skip', true);

$ogolne = get_option('rank-math-options-general', []);
$ogolne = array_merge(is_array($ogolne) ? $ogolne : [], [
    'strip_category_base' => 'off',
    'nofollow_external_links' => 'off',
    'new_window_external_links' => 'on',
    'breadcrumbs' => 'on',
    'breadcrumbs_separator' => '/',
    'breadcrumbs_home_label' => 'Strona główna',
]);
update_option('rank-math-options-general', $ogolne);

$tytuly = get_option('rank-math-options-titles', []);
$tytuly = array_merge(is_array($tytuly) ? $tytuly : [], [
    'title_separator' => '|',
    'knowledgegraph_type' => 'company',
    'knowledgegraph_name' => 'TAXEON Sp. z o.o.',
    'knowledgegraph_logo' => wp_get_attachment_url((int) get_option('site_icon')) ?: '',
    'local_business_type' => 'AccountingService',
    'local_address' => [
        'streetAddress' => 'ul. Kamionkowska 51',
        'addressLocality' => 'Warszawa',
        'postalCode' => '03-812',
        'addressCountry' => 'PL',
    ],
    'local_phone' => '+48515033505',
    'local_email' => 'kontakt@taxeon.pl',
    'pt_page_default_rich_snippet' => 'off',
    'pt_post_default_rich_snippet' => 'article',
    'pt_post_default_article_type' => 'BlogPosting',
    'pt_page_title' => '%title% %sep% %sitename%',
    'pt_page_description' => '%excerpt%',
    'pt_post_title' => '%title% %sep% %sitename%',
    'pt_post_description' => '%excerpt%',
    'pt_attachment_custom_robots' => 'on',
    'pt_attachment_robots' => ['noindex'],
    'author_archives' => 'off',
    'date_archives' => 'off',
    'noindex_empty_taxonomies' => 'on',
]);
update_option('rank-math-options-titles', $tytuly);

$sitemap = get_option('rank-math-options-sitemap', []);
$sitemap = array_merge(is_array($sitemap) ? $sitemap : [], [
    'items_per_page' => 200,
    'include_images' => 'on',
    'pt_page_sitemap' => 'on',
    'pt_post_sitemap' => 'on',
    'pt_attachment_sitemap' => 'off',
    'tax_category_sitemap' => 'on',
    'tax_post_tag_sitemap' => 'off',
]);
update_option('rank-math-options-sitemap', $sitemap);

/** @var array<string, array{0: string, 1: string, 2: string}> */
$meta = [
    'strona-glowna' => [
        'Księgowość online od 99 zł — biuro rachunkowe dla JDG',
        'Pełna obsługa księgowa jednoosobowej działalności: ryczałt od 99 zł, KPiR od 169 zł. Dedykowana księgowa, aplikacja do faktur i rozliczenia ZUS w cenie.',
        'księgowość online',
    ],
    'ryczalt' => [
        'Ryczałt — księgowość online za 99 zł miesięcznie',
        'Prowadzenie ewidencji przychodów, rozliczenia ZUS i zaliczki na PIT w stałej cenie 99 zł netto. Bez dopłat za korekty i rozliczenie roczne.',
        'ryczałt księgowość',
    ],
    'kpir' => [
        'KPiR — księgowość online za 169 zł miesięcznie',
        'Książka Przychodów i Rozchodów z VAT, kosztami, środkami trwałymi i rozliczeniem pojazdu. Dedykowana księgowa i aplikacja w cenie abonamentu.',
        'kpir online',
    ],
    'cennik' => [
        'Cennik księgowości online — 19, 99, 139 i 169 zł',
        'Cztery pakiety bez ukrytych opłat: aplikacja do samodzielnego księgowania od 19 zł oraz pełna obsługa biura rachunkowego od 99 zł netto miesięcznie.',
        'cennik księgowości',
    ],
    'aplikacja' => [
        'Program księgowy online — faktury, koszty, KSeF',
        'Wystawiaj faktury, pobieraj koszty z KSeF i sprawdzaj podatek w jednym miejscu. Aplikacja Taxeon działa w przeglądarce i jest w cenie abonamentu.',
        'program księgowy online',
    ],
    'biura-rachunkowe' => [
        'Program dla biur rachunkowych — KPiR i ryczałt za 12 zł',
        'Rozliczaj klientów taniej: 12 zł netto za aktywną firmę, bez abonamentu za konto i bez limitu użytkowników. Klienci sami wystawiają faktury.',
        'program dla biur rachunkowych',
    ],
    'faq' => [
        'FAQ — najczęstsze pytania o księgowość online',
        'Ceny, zakres obsługi, zmiana biura rachunkowego, bezpieczeństwo danych i komunikacja z księgową. Odpowiedzi na pytania, które słyszymy najczęściej.',
        'księgowość online pytania',
    ],
    'b2b' => [
        'Księgowość dla kontraktu B2B — przejście z etatu',
        'Policz, ile realnie zostaje na B2B, załóż firmę i rozlicz pierwszą fakturę. Prowadzimy księgowość od pierwszego miesiąca działalności.',
        'księgowość b2b',
    ],
    'it' => [
        'Księgowość dla programistów i branży IT',
        'Ryczałt 12%, faktury w walutach obcych i rozliczenia z zagranicznymi kontrahentami. Księgowość dla jednoosobowych działalności z branży IT.',
        'księgowość dla programistów',
    ],
    'lekarze' => [
        'Księgowość dla lekarzy i dentystów',
        'Kontrakt z NFZ, prywatny gabinet i dyżury w jednym rozliczeniu. Prowadzimy księgowość lekarzy rozliczających się ryczałtem lub na KPiR.',
        'księgowość dla lekarzy',
    ],
    'prawnicy' => [
        'Księgowość dla kancelarii — adwokaci i radcowie',
        'Faktury z VAT 23%, koszty z urzędu, składka samorządowa i tajemnica zawodowa. Obsługa księgowa kancelarii indywidualnych i spółek partnerskich.',
        'księgowość dla kancelarii',
    ],
    'blog' => [
        'Baza wiedzy — podatki i księgowość w praktyce',
        'Ryczałt, KPiR, ZUS, VAT i KSeF wyjaśnione po polsku. Piszemy o tym, o co najczęściej pytają nas Klienci prowadzący jednoosobową działalność.',
        'baza wiedzy księgowość',
    ],
    'polityka-prywatnosci' => [
        'Polityka prywatności',
        'Zasady przetwarzania danych osobowych w serwisie taxeon.pl: zakres zbieranych danych, cele przetwarzania i prawa użytkownika.',
        '',
    ],
    'polityka-cookies' => [
        'Polityka plików cookies',
        'Jakie pliki cookies wykorzystujemy w serwisie taxeon.pl, w jakim celu i jak zmienić zgody w ustawieniach przeglądarki.',
        '',
    ],
];

$ustawione = 0;

foreach ($meta as $slug => [$tytul, $opis, $fraza]) {
    $strona = get_page_by_path($slug);

    if (!$strona) {
        WP_CLI::warning("Brak strony: {$slug}");

        continue;
    }

    update_post_meta($strona->ID, 'rank_math_title', $tytul . ' %sep% %sitename%');
    update_post_meta($strona->ID, 'rank_math_description', $opis);

    if ($fraza !== '') {
        update_post_meta($strona->ID, 'rank_math_focus_keyword', $fraza);
    }

    if (str_starts_with($slug, 'polityka')) {
        update_post_meta($strona->ID, 'rank_math_robots', ['noindex']);
    }

    $ustawione++;
}

WP_CLI::success("Rank Math skonfigurowany, opisy meta na {$ustawione} stronach.");
