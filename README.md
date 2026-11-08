# Taxeon New Color

Motyw WordPress dla TAXEON. Timber 2 (Twig) + Vite 6 + Tailwind CSS 4 + SCSS.
Bez WooCommerce.

Autor: Michał Bartczak

## Wymagania

- PHP 8.2+
- Node 20+
- Composer 2
- Wtyczka **ACF PRO** (bloki i Opcje motywu)

## Start

```bash
composer install
npm install
npm run build      # produkcja → dist/
npm run dev        # serwer deweloperski z HMR (port 5173)
```

`npm run dev` zapisuje `dist/.hot`. Dopóki plik istnieje **i** serwer odpowiada,
`App\Setup\Vite` ładuje assety z serwera deweloperskiego; w przeciwnym razie
czyta `dist/.vite/manifest.json`. Nie trzeba niczego przełączać ręcznie —
po ubiciu `npm run dev` strona sama wraca na zbudowane pliki.

Zmiana pliku `.twig` lub `.php` wymusza pełne przeładowanie przeglądarki.

## Kontrola

```bash
php wp-content/themes/taxeon/bin/lint-templates.php
wp eval-file wp-content/themes/taxeon/bin/check-icons.php
```

Zrzuty mobilne robimy przez `<iframe style="width:390px">` na tej samej domenie —
`--window-size=390,760` nie zmniejsza viewportu poniżej minimalnej szerokości okna
Chrome, więc strona układa się szerzej i zrzut wygląda jak przepełnienie poziome,
którego nie ma.

Drugi skrypt sprawdza, czy każda ikona wskazana w treści stron ma plik
w `src/svg`. Filtr `|svg` pokazuje komunikat o braku ikony **wyłącznie
zalogowanym administratorom**, więc anonimowy odwiedzający widzi pustkę —
brak ikony potrafi przejść niezauważony aż na produkcję.

Pierwszy:

Sprawdza składnię Twiga i pilnuje, żeby w atrybucie `class` nie było znaku
`>`. To drugie nie jest kaprysem: `wptexturize` WordPressa tokenizuje treść po
`<` i `>`, więc wariant `[&>a]:flex-1` urywa mu znacznik w połowie — reszta
atrybutu trafia do niego jako tekst i sekwencja `1"` zamienia się w cal (`1″`).
Atrybut przestaje się domykać, a przeglądarka połyka następny element.
Zamiast kombinatora dziecka używamy potomka: `[&_a]`.

## Struktura

```
app/                 PHP, PSR-4 pod App\
  Setup/             Vite (manifest + HMR), TimberTheme (kontekst, filtry, menu)
  Blocks/            AcfTimberBlocks — rejestracja bloków z nagłówków .twig
  Providers/         ACF, blog, typy treści
  PostTypes/         AbstractPostType — baza pod CPT (na razie żadnego nie rejestrujemy)
  Services/          cache, media, logowanie, okruszki, konteksty, reCAPTCHA + leady
  Api/               REST: /taxeon/v1/contact, /taxeon/v1/posts
views/               Twig
  layouts/base.twig  szkielet dokumentu
  templates/         front-page, page, archive, post, search, 404
  components/        header, footer, karty, obrazek, okruszki, przycisk
  blocks/            szablony bloków ACF
src/
  css/tailwind.css   tokeny designu (@theme) — jedyne źródło prawdy
  scss/              tylko to, czego utility nie obsłużą (patrz niżej)
  blocks/<nazwa>/    style i skrypty pojedynczych bloków (wyjątki)
  js/                main, blog, single-post, admin + moduły w js/app
  svg/               ikony (lucide) wstawiane inline filtrem |svg
  fonts/             Outfit + Manrope (woff2, latin + latin-ext)
acf-json/            wersjonowane definicje pól ACF
```

## Style — utility first

**Wygląd budujemy klasami utility bezpośrednio w szablonach Twig.** SCSS jest
wyjątkiem, nie regułą.

Rolę „komponentu" pełni include Twiga, nie klasa CSS: `components/button.twig`
trzyma łańcuch klas przycisku, `components/form-field.twig` — pola formularza,
`components/post-card.twig` — karty wpisu. Nie ma klas `.btn` czy `.post-card`
w CSS.

Wszystkie tokeny (kolory, fonty, rozmiary tekstu, promienie, cienie,
breakpointy, gradienty, keyframes) żyją w `src/css/tailwind.css` w bloku
`@theme` i stają się klasami utility: `text-ink`, `bg-accent`, `rounded-panel`,
`shadow-navy`, `py-section`, `px-gutter`, `h-header`, `max-w-page`.

Uwaga na dwie rzeczy:

- **Breakpointy to wartości z layoutu**, nie domyślne z Tailwinda:
  `sm` 560px, `md` 720px, `lg` 820px, `xl` 920px, `2xl` 1080px. Layout jest
  desktop-first, więc w szablonach dominują warianty `max-xl:`, `max-md:`.
- **Promienie i cienie mają własne nazwy** (`rounded-panel`, `shadow-mid`),
  żeby nie podmieniać znaczenia `rounded-lg` / `shadow-md` z dokumentacji.

### Co zostaje w SCSS

`src/scss/` ma siedem plików i tylko dlatego, że utility tam nie sięgają:

| Plik | Dlaczego nie utility |
|---|---|
| `base/_fonts.scss` | `@font-face` |
| `base/_base.scss` | globalne `:focus-visible`, klasa `.screen-reader-text` wypisywana przez WordPressa |
| `base/_prose.scss` | treść z edytora — nie dopniemy klas do `<h2>`, `<li>`, `<blockquote>` |
| `base/_animations.scss` | `.reveal` — klasę `in` dokłada IntersectionObserver |
| `admin.scss` | panel WordPressa, poza zasięgiem Tailwinda |

`main.scss` ładuje je przez `meta.load-css()` wewnątrz `@layer base` /
`@layer components`, żeby utility zawsze wygrywały w kaskadzie.

### Stany interaktywne

JS nie dokłada klas wyglądowych — przestawia atrybuty `data-*`, a wygląd
opisują warianty w szablonie:

| Atrybut | Gdzie | Wariant |
|---|---|---|
| `data-scrolled` | `#site-header` | `group-data-scrolled:` na potomkach |
| `data-open` | menu mobilne, burger | `data-open:`, `group-data-open/burger:` |
| `data-visible` | powrót na górę | `data-visible:` |
| `data-loading` | siatka bloga | `data-loading:` |
| `data-active` | pigułki filtra, zakładki | `data-[active=true]:` |
| `data-ready` | wideo hero | `data-ready:` |
| `data-status` | komunikat formularza | `data-[status=success]:` |

## Bloki

**Zasada: każdy blok jest uniwersalny.** Nic, co może być przełącznikiem, nie
jest zaszyte w szablonie — ta sama sekcja ma działać na jasnym i ciemnym tle,
ciaśniej albo luźniej, bez klonowania bloku pod każdy wariant.

Wspólne dla wszystkich (zakładka „Sekcja”, generowane przez `sectionSettings()`):

| pole | wartości |
|---|---|
| `background` | białe / jasnoszare / granatowe / gradient |
| `container` | 1200 / 860 / 1440 / pełna |
| `padding_top`, `padding_bottom` | standardowy / mały / brak |

Renderuje je `components/section.twig`:

```twig
{% import 'components/section.twig' as section %}
<section class="{{ section.shell(fields, classes) }}">
    <div class="{{ section.container(fields) }}">
```

`section.isDark(fields)` mówi, czy sekcja stoi na ciemnym tle — na tej podstawie
nagłówek i przyciski dobierają wariant.

Uwaga przy dodawaniu pól: nazwa `background` jest zajęta przez ustawienia
sekcji. Zdjęcie tła w bloku „Wyróżniki” nazywa się dlatego `background_image`.

Blok = katalog `views/blocks/<nazwa>/<nazwa>.twig` z nagłówkiem w komentarzu Twiga:

```twig
{#
	Title: Hero
	Description: Sekcja otwierająca
	Category: taxeon
	Icon: cover-image
	Mode: preview
	EnqueueStyle: src/blocks/hero/hero.scss
	EnqueueScript: src/blocks/hero/hero.js
#}
```

Bloki rejestrują się jako **ACF Blocks v3** (`DEFAULT_BLOCK_VERSION` w
`AcfTimberBlocks`). W v3 nie ma przełącznika Edycja/Podgląd — kanwa edytora
jest w iframe, a pola żyją w panelu bocznym i w rozwijanym edytorze. Stąd:

| nagłówek | działa w |
|---|---|
| `ExpandedEditorButtons`, `ExpandedEditorButtonText` | v3 |
| `HideFieldsInSidebar` | v3 |
| `Mode`, `SupportsMode` | tylko v1/v2 — w v3 ignorowane |
| `ACFBlockVersion` | cofa pojedynczy blok do v2 |

Iframe kanwy nie dziedziczy stylów strony, więc `enqueue_block_assets`
wstrzykuje do niej CSS wejścia `main.js` plus `src/scss/editor-canvas.scss`
z korektami.

### Reset edytora kontra warstwy kaskady

WordPress ładuje do kanwy `block-library/reset.css`, a tam m.in.:

```css
html :where(.editor-styles-wrapper) { color: initial; font-family: serif }
:where(.editor-styles-wrapper) h1,…,h6 { color: revert; font-size: revert; … }
```

Specyficzność tych reguł jest zerowa (`:where()` się nie liczy), ale stoją
**poza warstwami kaskady**, a utility Tailwinda siedzą w `@layer utilities` —
a deklaracja bez warstwy bije każdą deklarację z warstwy, niezależnie od
specyficzności. W kanwie dawało to nagłówki **czarne na granatowym tle** i
szeryfowe akapity, choć na froncie wszystko było dobrze. Do kompletu
`wp-admin/css/common.css` (dokłada je klasyczny panel metaboksów) ustawia
`a { color: #2271b1 }`, przez co napisy na przyciskach robiły się niebieskie.

Lekarstwem jest `revert-layer` w `editor-canvas.scss`: cofa wartość do tej
z warstw, czyli dokładnie do klasy wypisanej w szablonie. **Przy każdym nowym
resecie WP warto tam zajrzeć** — objaw jest zawsze ten sam: front dobrze,
kanwa źle.

### Podgląd w wyszukiwarce bloków

Każdy blok ma dane przykładowe w `app/Blocks/examples.php` — jednym pliku
zamiast długiego JSON-a w nagłówku każdego szablonu (nagłówek `Example:`
nadal działa i ma pierwszeństwo). Podgląd renderuje ten sam szablon co strona,
tylko na treści zastępczej, więc nie ma drugiego markupu do utrzymania.

W trybie przykładu ACF **nie spłaszcza pól** — podaje tablicę bez zmian jako
`fields`, więc zapisujemy ją tak, jak czyta ją szablon: grupy zagnieżdżone,
repeatery jako listy.

### Panel pól

Pola bloku zaczynają się zakładką „Treść”, a ustawienia sekcji siedzą
w „Sekcji”. Bez tej pierwszej zakładki ACF wysypuje wszystko jedną listą
i dopiero pod spodem pokazuje samotną „Sekcję” — hero dawało w ten sposób
osiemnaście pól pod rząd w kolumnie o szerokości 280 px.

Wszystkie bloki mają `HideFieldsInSidebar` i `ExpandedEditorButtons`: w panelu
bocznym zostaje sam przycisk „Edytuj zawartość”, a pola otwierają się w szerokim
oknie, gdzie da się je przeczytać.

Rejestracja jest automatyczna (`AcfTimberBlocks`), a `EnqueueStyle` dociąga
style tylko wtedy, gdy blok faktycznie jest na stronie. Vite sam wykrywa nowe
wejścia w `src/blocks/*` — po dodaniu katalogu wystarczy restart `npm run dev`.

Gotowe bloki: `hero`, `features`, `latest-posts`, `cta`, `contact`, `pricing`,
`table`, `content-media`, `timeline`, `stats`, `faq`.

Partiale bloku (np. powtarzalny element) mogą leżeć w jego katalogu — rejestracja
pomija pliki `.twig` bez nagłówków `Title` i `Category`.

### Przełączniki opisują odstępstwo, nie stan domyślny

Pole `true_false`, którego nigdy nie zapisano, jest **fałszywe**:
`default_value` działa przy zakładaniu bloku w edytorze, nie dla treści
zasianej wcześniej. Przełącznik nazwany `show_message` zabrałby więc pole
wiadomości wszystkim formularzom złożonym przed jego dodaniem — bez błędu,
bez śladu w logach. Dlatego nowe przełączniki pytają o odstępstwo:
`hide_message`, `consent_optional`. Brak wartości znaczy „jak dotąd”.

## Formularze

Wszystkie zgłoszenia idą jedną drogą: `POST /taxeon/v1/contact` → reCAPTCHA v3
→ tabela leadów. Formularz rozpoznajemy po polu `form`, a pola spoza standardowego zestawu
lądują w kolumnie `payload` jako JSON.

Wiadomość i zgoda są wymagane **warunkowo** — sprawdzamy je tylko wtedy, gdy
formularz w ogóle je przysłał. Zapis na demo nie ma pola wiadomości, a zapis
na test zbiera dobrowolną zgodę marketingową pod nazwą `marketing`; `consent`
znaczy zgodę wymaganą i nic innego.

Poza ustawieniami sekcji bloki mają własne przełączniki układu — po to, żeby
jeden blok obsłużył kilka miejsc w serwisie:

| blok | przełącznik | zastosowanie |
|---|---|---|
| `hero` | `layout`: wyśrodkowany / do lewej | strona główna vs nagłówki podstron |
| `hero` | `show_breadcrumbs` | okruszki w nagłówku podstrony |
| `hero` | `media_type`: gradient / wideo / zdjęcie | przyciemnienie dokładane tylko pod medium |
| `hero` | `text_width`: ograniczona / pełna | krótki tytuł wyśrodkowany vs długi na podstronie |
| `features` | `layout`: kafle / lista | „Dlaczego my" vs „Kluczowe funkcje" |
| `features` | `columns`: 1–4 | gęstość siatki |
| `choice` | `columns`: 2–4, `featured` na karcie | „Chmura vs lokalnie" (2, jedna ciemna) vs „Elementy systemu" (3 jasne) |
| `pricing` | `columns`, `featured` na karcie | warianty abonamentu |
| `table` | kolumny i komórki z pól | cennik modułów **i** tabela porównawcza |
| `content-media` | `media_position`, `frame`, `media_type` | tekst + zdjęcie **albo osadzony iframe** (kalendarz, wideo), media po dowolnej stronie |
| `table` | `mode`: jedna tabela / zakładki | tabela porównawcza vs pliki do pobrania w trzech zakładkach |
| `pricing` | dwie akcje + linki dodatkowe na karcie | warianty abonamentu **i** karty pobierania |
| `contact` | `info_cards`, `map_embed` | z kolumną boczną formularz przechodzi na lewo i przejmuje tytuł |
| `cta` | `variant`: jasny / ciemny | pas rozbijający sekcje |

Bloki nie mają własnych plików SCSS — ich wygląd to utility
w `.twig`, więc `EnqueueStyle` pojawia się tylko tam, gdzie faktycznie jest
arkusz.

### Pola

Definicje generuje `bin/generate-acf-fields.php` — pisanie ich ręcznie kończy
się literówkami w kluczach. Klucze są deterministyczne (md5 ze ścieżki), więc
ponowne uruchomienie nie tworzy duplikatów.

**Teksty semantyczne to grupy `{ text, tag }`** — redakcja wybiera znacznik
(h1–h4, p, span). Obsługuje je `components/title-with-select-tag.twig`:

```twig
{% include 'components/title-with-select-tag.twig' with {
    title: fields.title, defaultTag: 'h2', titleClass: '…'
} only %}
```

Mikrotreści (etykiety tagów, punkty list, podpisy przycisków) zostają zwykłym
tekstem — nie mają własnej semantyki w outline, a grupa zaśmiecałaby panel.

**Przyciski to grupy `{ link, variant, size, icon }`.** Wariant i rozmiar wybiera
redakcja; szablon może je nadpisać, gdy tło tego wymaga (na ciemnym pasie CTA
wymuszamy warianty czytelne na granacie).

Dwie pułapki, które kosztowały mnie sporo czasu:

- **`get_fields()` pomija pola typu `group`.** Enumeruje istniejące wiersze
  danych, a grupa własnego wiersza nie ma — w danych bloku leżą tylko podpola
  (`title_text`, `title_tag`). Dlatego `AcfTimberBlocks::blockFields()` buduje
  tablicę z definicji grup i `get_field()`, zamiast wołać `get_fields()`.
- **Nazwy pól nie są unikalne.** W jednym bloku bywa kilka podpól `text`,
  a `title`/`subtitle` powtarzają się między grupami. Seeder chodzi po drzewie
  definicji równolegle z danymi zamiast szukać pól po nazwie.

Definicje leżą w `acf-json/` (13 grup) i wczytują się
automatycznie po aktywacji ACF PRO — nie trzeba nic klikać. Zmiany zrobione
w panelu zapisują się z powrotem do tego katalogu i wchodzą do repozytorium.

Pola o nazwie `icon` dostają listę wyboru generowaną z plików w `src/svg`
(filtr w `App\Providers\AcfSupports`), więc dorzucenie ikony wystarczy, żeby
pojawiła się w panelu.

## Ikony

Pliki SVG w `src/svg`, wstawiane inline: `{{ 'arrow-right'|svg }}` lub
`{{ 'phone'|svg('moja-klasa') }}`. Kolor dziedziczy się przez `currentColor`,
rozmiar ustawia CSS. Nazwy plików trafiają automatycznie na listę wyboru w polach
ACF o nazwie `icon`.

Build kopiuje `src/svg` do `dist/svg`, więc na produkcję wystarczy `dist/`.

## Typy treści

`App\PostTypes\AbstractPostType` jest gotowe, ale **nic nie jest zarejestrowane**.
Nowy CPT: klasa dziedzicząca po bazie + wpis na liście w
`App\Providers\PostTypeSupports::POST_TYPES`. Po dodaniu przejdź raz do
Ustawienia → Bezpośrednie odnośniki.

## Blog

- Archiwum: wyróżniony wpis (najnowszy, wycięty z siatki) + siatka 9 kart.
- Filtr kategorii działa przez AJAX (`/wp-json/taxeon/v1/posts`), karty renderuje
  serwer z `components/post-card.twig`, stan trafia do URL-a (`?kategoria=…`).
- Kolor kategorii: pole ACF `color` na taksonomii `category`, wstawiane jako
  `--term-color`. Bez pola badge używa koloru akcentu.

## Formularze

Blok `contact` wysyła dane do `POST /wp-json/taxeon/v1/contact`. Endpoint
weryfikuje reCAPTCHA v3, ma honeypot i limit żądań na IP, zapisuje zgłoszenie
do tabeli `{prefix}_taxeon_leads` i wysyła powiadomienie e-mail.

Zgłoszenia: panel → **Zgłoszenia** (filtrowanie, statusy, eksport CSV).

Klucze reCAPTCHA i adres powiadomień: Opcje motywu (`recaptcha_enabled`,
`recaptcha_site_key`, `recaptcha_secret_key`, `recaptcha_threshold`,
`contact_email`). Gdy reCAPTCHA jest wyłączona, weryfikacja przepuszcza żądania.

### Formularze PIT (Contact Form 7)

**Contact Form 7 zostaje na linii 6.1 (obecnie 6.1.7).** Od 6.2 wtyczka wymaga PHP 8.3 i na
PHP 8.2 kładzie całą stronę błędem krytycznym (`platform_check.php`). Nie aktualizuj jej,
dopóki serwer nie ma PHP 8.3.

Sześć formularzy przeniesionych 1:1 ze starej strony (`bin/migrate-stare-funkcje.php`):
PIT-28, PIT-36, PIT-36L — dla klientów i „zewnętrzne”. Maile bez zmian: do
`pit@taxeon.pl`, temat np. „PIT-28 - formularz - [email]”, załączniki z pól `file`.
Strony: `/pit-rozliczenie/` (wybór) i `/formularz-pit-*/`.

Układ: `bin/pit-formularze-uklad.php` przepisuje kod formularza na klasy `pit-*`
(siatki, etykiety, pigułki Tak/Nie) i przerywa, gdyby zmienił choć jeden tag pola.
Oryginał zostaje w meta `_form_oryginal`. Style: `src/scss/components/_formularze.scss`
— poza warstwami, bo CSS wtyczki też jest poza warstwami. `App\Services\ContactFormAssets`
wyłącza autop CF7 i ładuje skrypty CF7 + reCAPTCHA tylko na stronach z formularzem.

## Cache

`App\Services\ThemeCache` trzyma opcje ACF i HTML menu w transientach z
wersjonowaniem grup. Zapis Opcji motywu, edycja menu i zmiana kategorii
same podbijają wersję. `PageCacheFlush` raz na dobę czyści page cache (W3TC /
LiteSpeed), żeby nonce w HTML nie wygasł przed stroną.

## Pola Opcji motywu

Motyw czyta m.in.: `logo`, `logo_white`, `header_menu` (repeater mega-menu:
`link`, `submenu_width`, `submenu` → `link`/`icon`/`description`),
`header_contact_link`, `header_cta`, `footer_description`, `phone_sales`,
`phone_support`, `email`, `address`, `remote_help`, `copyrights`,
`scripts_head`, `scripts_body`.

## Menu

Mega-menu i kolumny stopki pochodzą z repeaterów w Opcjach motywu, nie
z lokalizacji. Kolumna stopki to nagłówek (`title_menu`) plus wskazane menu
(`select_menu`) — redakcja sama decyduje, ile kolumn jest i w jakiej
kolejności. Listę menu w polu `select_menu` podstawia filtr
w `App\Providers\AcfSupports`.

Menu mobilne składa się z tego samego repeatera co mega-menu: pozycje
z panelem rozwijają się w miejscu (`data-submenu-toggle`), a sekcja bieżącej
strony jest rozwinięta od razu. Nie ma osobnego menu do utrzymania.

Lokalizacja została jedna: `legalMenu`.

**Po edycji `menu-header.twig`, `menu-footer.twig` lub `menu-mobile.twig`
trzeba wyczyścić cache** — ThemeCache trzyma skompilowany HTML menu i sam
szablon nie wystarczy:

```bash
wp eval 'App\Services\ThemeCache::flush("menus");'
```

Zapis Opcji motywu i edycja menu w panelu robią to automatycznie.

### Progi menu

Menu desktopowe wymaga ~960 px na samą treść, więc przełącznik na burgera
stoi na 1200 px (nowa makieta), wcześniej 1080 px zamiast 920 px z layoutu — przy 920 px pozycje nie mieszczą
się i w oryginale ratuje to ściśnięcie logo do 31 px. Link „Kontakt” wchodzi
dopiero od 1180 px, a CTA znika poniżej 560 px, żeby burger nie wypadł poza
ekran (w menu mobilnym CTA i tak jest).

## Logo

`images/logo-white.png` to logo z paczki klienta (`projekt/assets/taxeon-logo-white.png`),
renderowane jako `<img>` w nagłówku i stopce — oba stoją na granacie. Wcześniejszy
`src/svg/logo.svg` był obrysem znaku RAKS-a przeniesionym razem z architekturą i został
usunięty.

Pola ACF `logo` i `logo_white` pozwalają podmienić znak na własny plik.

## Ceny

Kwoty abonamentów żyją w Opcjach motywu, zakładka „Ceny”. W treści bloków, w tytułach
pozycji menu i w tytułach/opisach Rank Math wpisujemy znaczniki:

| Znacznik | Domyślnie |
|---|---|
| `{cena_ryczalt}` | 99 |
| `{cena_ryczalt_vat}` | 149 |
| `{cena_kpir}` | 169 |
| `{cena_aplikacja_start}` / `{cena_aplikacja}` | 19 / 43 |
| `{cena_biura}` | 12 |
| `{cena_kadry}` / `{cena_pit}` | 100 / 89 |

`App\Services\Prices` podstawia je w `acf/format_value`, `the_content`, `the_title`,
pozycjach menu i filtrach Rank Math; kalkulator kosztu księgowości przepuszcza przez nie
ceny czytane z kart Cennika. Wartości czyta wprost z `get_option('options_cena_*')` —
nie przez `ThemeCache::options()`, bo `get_fields('options')` sam odpala
`acf/format_value` i zapętliłby się.

## Kolorystyka

Paleta z `projekt/handoff/tokens.css`: `navy` #173F5F, `navy-deep` #0F2F4A, `ink` #0E1F2C,
`gold` #C98E00, `gold-light` #E3B23C, `gold-dark` #B07A00, `cream` #F4F1EA, `sand` #F6EBCF,
`line` #DED7C8. Tła sekcji (`background` w ustawieniach bloku):

- `light` — biel → krem → piasek ze złotymi liniami SVG (`components/section-decor.twig`),
- `soft` — płaski krem z poświatą od dołu,
- `gradient` — granat 135° przechodzący w `ink`,
- `navy-flat` — granat 160° (strona główna),
- `navy` — najciemniejszy `ink`, sekcje zamykające.

Hero ma pole „Kolorystyka”: kremowe (`components/hero-art.twig`) albo granatowe
(`components/hero-art-dark.twig`). Fragment tytułu w nawiasach kwadratowych, np. `[12 zł]`,
dostaje złoty kolor. Wideo w tle działa tylko w wersji granatowej.

Tabela porównawcza ma „Powierzchnię”: białą kartę albo szkło na granacie (Cennik).

## Spotkania i konwersja

Wszystkie „Wybierz termin spotkania” prowadzą do `taxeon.youcanbook.me`, a `booking.js`
otwiera kalendarz w modalu. youcanbook przekierowuje po rezerwacji na `/dziekujemy/` —
wewnątrz iframe'a — więc `App\Services\LeadEvent` na stronie z metą `_taxeon_lead_page`
najpierw wychodzi z ramki (`top.location.replace`), a dopiero w głównym oknie wypycha
`generate_lead` do `dataLayer`, raz na sesję. Tag GA4/Ads konfigurujemy w GTM, który
wstawia Complianz — w kodzie nie ma identyfikatora Google. `/dziekujemy/` ma `noindex`.

Opinie Google w trybie automatycznym pomijają oceny poniżej 4 (`GoogleReviews::MIN_RATING`);
opinia wybrana ręcznie w bloku pokaże się zawsze.

## Animacje i FAQ

Klasa `reveal` (sekcje, karty, kroki, pytania) wsuwa element z dołu przy wejściu w ekran —
`src/js/app/reveal.js` (IntersectionObserver, opóźnienie 70 ms dla kolejnych rodzeństw) i
`src/scss/base/_animations.scss`. Ukrywanie działa tylko z klasą `js` na `<html>`, którą dokłada
skrypt w `<head>`, więc bez JS treść jest widoczna. Animacja idzie przez `animation`, nie
`transition`, żeby nie zabierać kartom hoverów. Nie dawaj `reveal` elementom startującym z
`hidden` (np. panele okresów w cenniku) — nie wejdą w ekran i zostaną przezroczyste.

FAQ (`src/js/app/faq.js`): w obrębie sekcji otwarte jest jedno pytanie, rozwijanie i zwijanie
animuje wysokość przez Web Animations API. Stan ikony czyta `data-expanded`, nie `[open]`,
bo `open` zdejmujemy dopiero po zakończeniu zwijania.

## Migracja do nowej kolorystyki

Baza to klon `taxeon`. Skrypty (wszystkie przez `wp eval-file`, można puszczać ponownie):

1. `bin/patch-new-color.php` — tła sekcji, kolorystyka hero, teksty i listy z nowej makiety.
2. `bin/migrate-urls-new-color.php` — strona „Branże”, `/branze/it|lekarze|prawnicy/`,
   `/dla-biur-rachunkowych/` (stare adresy: 301 w `Redirects`). Argument `odbuduj` stawia
   stronę „Branże” od nowa. Linki w treści podmienia się potem `wp search-replace`.
3. `bin/seed-umow-spotkanie.php`, `bin/seed-dziekujemy.php` — nowe strony.
4. `bin/patch-ceny-znaczniki.php` — zamienia kwoty na znaczniki `{cena_*}`.
5. `bin/migrate-stare-funkcje.php` — formularze PIT, `/pit-rozliczenie/`, `/unsubscribe/`,
   `/polecaj-znajomym/`; na końcu odpala `bin/pit-formularze-uklad.php`.
6. `bin/migrate-konta.php` — konta z produkcji (`taxeon_old`) z oryginalnymi ID i hasłami,
   strony i wpisy przypisane do S. Hynowski (#2, wyświetlana nazwa „S. Hynowski” zamiast
   adresu e-mail), usunięcie lokalnego `admin@taxeon.local`.

## Wdrożenie

Paczkę na produkcję robi się Duplicatorem z tej instalacji. Szczegóły i lista kontrolna:
`/Applications/MAMP/htdocs/taxeon-new/robocze/README.md`.

W Duplicatorze wyklucz katalogi `robocze/`, `wp-content/themes/taxeon-new-color/node_modules/`
i `wp-content/themes/taxeon-new-color/.git/`.

## Zgodność z projektem

Projekt w `/Applications/MAMP/htdocs/taxeon-new/robocze/projekt/` (`export/`, `handoff/`, pliki `.dc.html`) to eksport z Claude Design: style w całości inline,
progi mobilne w `projekt/css/*.css`. Wartości przenosimy dokładnie z niego,
dwie pułapki:

- **Tokeny rozmiaru tekstu nie niosą interlinii.** Ten sam rozmiar bywa w projekcie
  łamany różnie, więc interlinia dziedziczy się z `body` (`leading-[1.6]` ustawione
  jawnie w `base.twig` — Preflight dałby 1.5), a odstępstwa zapisujemy w szablonie.
- **Krycia to dokładne wartości z projektu**, nie skala Tailwinda — zaokrąglenie
  do `/5`, `/10`, `/15` widać na ciemnych tłach.
