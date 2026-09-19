# Raks 2.0

Motyw WordPress dla RAKS. Timber 2 (Twig) + Vite 6 + Tailwind CSS 4 + SCSS.
Bez WooCommerce.

Autor: Michał Bartczak

## Wymagania

- PHP 8.1+
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

## Treść demonstracyjna

```bash
wp eval-file wp-content/themes/raks/bin/seed-front-page.php
wp eval-file wp-content/themes/raks/bin/seed-blog.php
wp eval-file wp-content/themes/raks/bin/seed-pages-produkty.php
wp eval-file wp-content/themes/raks/bin/seed-pages-modele.php
wp eval-file wp-content/themes/raks/bin/seed-pages-firma.php
wp eval-file wp-content/themes/raks/bin/seed-pages-wsparcie.php
wp eval-file wp-content/themes/raks/bin/seed-pages-wyprobuj.php
wp eval-file wp-content/themes/raks/bin/seed-pages-sklep.php
```

### Uwagi klienta

Poprawki treści z kolejnych rund uwag mają własne skrypty. W odróżnieniu od
seederów są punktowe — zmieniają wskazane pola i nie nadpisują reszty, więc
można je puścić na instalacji, w której redakcja już coś edytowała:

```bash
wp eval-file wp-content/themes/raks/bin/uwagi-1.php
```

Runda 1 ustawia m.in. animacje w nagłówkach podstron, kadry zdjęć w sekcji
„Wsparcie", adresy serwisów RAKS, style komórek w tabelach i dane rejestrowe
na Kontakcie.

Skrypty zmieniające treść stron zapisują ją przez `wp_slash()`.
`wp_insert_post()` zdejmuje jeden poziom ukośników, więc bez tego `\uXXXX`
w JSON-ie bloku traci backslash — `<b>` zamienia się w widoczne `u003cbu003e`,
a przy `\"` cały blok przestaje się parsować. Atrybuty składa
`serialize_block_attributes()`, nie `wp_json_encode`: rdzeń dodatkowo maskuje
`<`, `>`, `&` i `--`, żeby treść nie rozbiła komentarza HTML.

### Tło nagłówka

Adres strumienia wideo w hero jest jeden dla wszystkich stron —
`raks_seed_hero_video()` w `bin/seed-helpers.php`. Odtwarzaniem steruje
`src/js/app/heroVideo.js`, który poniżej 920 px, przy `prefers-reduced-motion`
i przy oszczędzaniu danych zostawia sam gradient.

Podstrony mają zamiast wideo animację liczoną w przeglądarce — pole „Rodzaj
tła → Animacja" w bloku Hero, obsługa w `src/js/app/heroBackground.js`.
`bands` to własny shader bez zależności; `fog`, `clouds`, `cells`, `net`
i `birds` to Vanta na three.js, doładowywane dynamicznym `import()`, więc
strony bez animacji nie pobierają tych paczek. Warunki wstrzymania są te same
co przy wideo. Szablony bez bloku Hero (blog, wpis, listing i strona
szkolenia) ustawiają `data-hero-bg` wprost.

Uwaga na `canPlayType('application/vnd.apple.mpegurl')`: Chrome odpowiada
**„maybe"**, choć HLS-a nie odtworzy. Sprawdzanie tego przed `Hls.isSupported()`
ustawia mu adres `.m3u8` w `src` i kończy się `networkState 3` — wideo nie
ładuje się w ogóle, a że plakat i tak zostaje, awaria jest cicha. Kolejność
w `attachHls()` jest więc odwrotna: najpierw hls.js, natywne odtwarzanie
zostaje dla Safari.

Wspólne funkcje seederów siedzą w `bin/seed-helpers.php` — dołącza je każdy
z nich, żeby logika spłaszczania pól nie rozjechała się między kopiami.

Pierwszy skrypt buduje stronę główną z ośmiu bloków, wgrywa zdjęcia
z `projekt/assets`, tworzy strony docelowe linków, wypełnia Opcje motywu
i zakłada menu. Drugi dodaje kategorie z kolorami i dziesięć wpisów.

Oba są idempotentne: obrazki rozpoznają po metadanej `_raks_seed_source`,
strony i wpisy po slugu. Klucze pól ACF czytają z `acf-json/`, więc zmiana
definicji pola nie rozjedzie się z seederem.

## Kontrola

```bash
php wp-content/themes/raks/bin/lint-templates.php
wp eval-file wp-content/themes/raks/bin/check-icons.php
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
  Api/               REST: /raks/v1/contact, /raks/v1/posts
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
| `blocks/support/support.scss` | rozsuwanie kafli wymaga `:hover .is-active:not(:hover)` |
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
	Category: raks
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

Gotowe bloki: `hero`, `choice`, `programs`, `features`, `support`, `advisor`,
`latest-posts`, `cta`, `contact`, `pricing`, `table`, `content-media`,
`timeline`, `stats`, `faq`, `trial-wizard`.

Partiale bloku (np. kroki kreatora) mogą leżeć w jego katalogu — rejestracja
pomija pliki `.twig` bez nagłówków `Title` i `Category`.

### Przełączniki opisują odstępstwo, nie stan domyślny

Pole `true_false`, którego nigdy nie zapisano, jest **fałszywe**:
`default_value` działa przy zakładaniu bloku w edytorze, nie dla treści
zasianej wcześniej. Przełącznik nazwany `show_message` zabrałby więc pole
wiadomości wszystkim formularzom złożonym przed jego dodaniem — bez błędu,
bez śladu w logach. Dlatego nowe przełączniki pytają o odstępstwo:
`hide_message`, `consent_optional`. Brak wartości znaczy „jak dotąd”.

## Zgodność z projektem

Statyczny projekt w `projekt/` jest **w połowie przerobiony**: cztery główne
strony (`index`, `ksiegowy`, `kadrowy`, `sprzedaz`) mają nadpisania inline,
których nie ma na pozostałych. Dwie decyzje podjęte na tej podstawie:

| element | projekt | motyw | dlaczego |
|---|---|---|---|
| nadkreślenie sekcji | `--accent` w CSS, ale 3 z 6 nadpisane na `#161c66` | `--accent-strong` (#161c66) | edycje idą tylko w jedną stronę; jaśniejszy granat czyta się jako akcent, a nie jako kolejny nagłówek |
| `h2` nagłówka sekcji | CSS `clamp(30,4vw,46)`, ale 11 z 17 nadpisane na 40px | 40px | większość nadpisań i wszystkie na najświeższych stronach |

Obie zmienia się w jednym miejscu: kolor w `components/eyebrow.twig`, rozmiar
w `components/section-head.twig`.

Pozostałe nagłówki mają w projekcie własne rozmiary per komponent i motyw je
odwzorowuje: `.split h2` → 34, `.form-card h2` → 26, `.cta-band h2` → 48,
`.advisor__intro h2` → 32, `.tl-item h3` → 19.

**Uwaga na interlinię.** Wbudowane rozmiary Tailwinda (`text-base`, `text-xl`,
`text-sm`) niosą własną, ciaśniejszą interlinię, a projekt dziedziczy 1.6
z `body`. Tam, gdzie ma być jak w projekcie, trzeba dopisać `leading-[1.6]` —
własne tokeny (`text-body`, `text-lead`…) są celowo bez interlinii i dziedziczą.

Mobile projektu jest niedokładne i **nie jest wzorcem**: `.btn` ma tam
`white-space: nowrap`, przez co długie etykiety wychodzą poza tło przycisku.
Motyw pozwala im się zawinąć.

## Produkty ze sklepu

Szkolenia i pozostałe produkty żyją w **sklepie** (raks-szkolenia /
sklep.raks.pl). Ta witryna trzyma ich kopię jako typ treści `produkt`
z taksonomią `kategoria-produktu` (odwzorowanie `product_cat`), żeby dało się
je linkować, wyszukiwać i indeksować.

Ścieżka użytkownika ma dwa kroki: **listing prowadzi na naszą stronę
szkolenia**, i dopiero stamtąd przycisk „Zapisz się” wychodzi do sklepu.
Czytelnik najpierw widzi opis, termin i program, a wychodzi na zewnątrz
dopiero wtedy, gdy chce się zapisać — koszyk i sesja żyją w sklepie.

Nazwa typu jest ogólna, nie „Szkolenie”: jadą tu **wszystkie** produkty, także
aktualizacje i licencje. Podział robi kategoria, gdzie „Szkolenia” to jedna
z nich.

### Jak to płynie

```
sklep: zapis produktu
   └─ App\Sync\ProductPusher (motyw raks-szkolenia)
        └─ POST /wp-json/raks/v1/sklep/produkt   ← podpis HMAC
             └─ App\Api\ApiShopProduct
                  └─ App\Services\Shop\ProductSync → wpis `produkt`
```

Pełny import (nowa witryna, odtworzenie po awarii), po stronie sklepu:

```bash
wp raks sync-products            # wszystko
wp raks sync-products --dry-run  # tylko podgląd, nic nie wysyła
wp raks sync-products --id=83    # jeden produkt
```

Jest idempotentny: wpisy rozpoznajemy po `_shop_id`, więc powtórzenie niczego
nie dubluje. Slug ani tytuł by się do tego nie nadały — jedno i drugie da się
zmienić w sklepie, a produkty o nazwie „Szkolenie” są tam dwa.

### Zakres i dostępność

Do raks jadą **tylko szkolenia** — produkty z kategorii `szkolenia`. Nie flaga
`_is_training`: w sklepie bywa nieustawiona nawet przy produktach ewidentnie
będących szkoleniami („Szkolenie z KSeF” ma ją na `no`), a kategoria jest tym,
co redakcja widzi i czym steruje. Zakres zmienia filtr `raks_sync_categories`
po stronie sklepu.

Produkt, który **wypada z zakresu** (np. przeniesiony do innej kategorii),
sklep wysyła z `listed = no`. Wtedy:

* jeśli strona już u nas istnieje — **zostaje pod swoim adresem**, znika tylko
  z list. Kasowanie dałoby 404 pod adresem, który mógł już gdzieś trafić,
  a zmiana kategorii to nie to samo co usunięcie;
* jeśli nigdy jej nie było — nie zakładamy jej.

Kosz po naszej stronie robi dopiero **skasowanie produktu w sklepie** (wtedy
`status` przychodzi jako `trash`).

**Slug ustawiamy tylko przy zakładaniu wpisu.** Zmiana adresu produktu
w sklepie nie przenosi naszej strony pod nowy adres — inaczej stary zacząłby
dawać 404, czyli dokładnie to, przed czym broni `listed`.

Listy pokazują domyślnie **tylko dostępne**: szkolenie po terminie i produkt
niedostępny znikają. Regułę trzyma `App\Services\Shop\ProductQuery` i używają
jej oba widoki — blok przez funkcję Twiga, archiwum kategorii przez
`pre_get_posts` (tam filtrujemy w zapytaniu, żeby stronicowanie nie liczyło
pozycji, których nikt nie zobaczy). Produkt bez terminu nie ma czemu wygasnąć,
więc zostaje.

### Konfiguracja

Po obu stronach ten sam sekret; stała w `wp-config.php` ma pierwszeństwo nad
opcją w bazie (na produkcji lepiej trzymać go poza bazą, żeby nie wyszedł
z kopią zapasową):

| gdzie | stała | opcja |
|---|---|---|
| sklep | `RAKS_SYNC_URL`, `RAKS_SYNC_SECRET` | `raks_sync_url`, `raks_sync_secret` |
| raks | `RAKS_SYNC_SECRET` | `raks_sync_secret` |

Bez sekretu endpoint odrzuca **wszystko** — nie ma trybu otwartego, bo ten
punkt zapisuje do bazy.

### Wdrożenie na produkcję

Na hostingu **nie ma konsoli ani WP-CLI**, więc wszystko robi się przez panel
i FTP. Kolejność ma znaczenie: sklep zacznie wypychać produkty od razu po
wgraniu plików, więc druga strona musi być gotowa pierwsza.

**1. Wspólny sekret.** Wygeneruj jeden ciąg (np. 64 znaki szesnastkowe)
i użyj tej samej wartości po obu stronach.

**2. `wp-config.php` na witrynie raks** — przez FTP, nad linią
`/* That's all, stop editing! */`:

```php
define('RAKS_SYNC_SECRET', 'tutaj-wygenerowany-sekret');
```

**3. Wgraj motyw raks**, aktywuj go, a potem wejdź w
**Ustawienia → Bezpośrednie odnośniki** i kliknij **Zapisz**. Typ treści
`produkt` i taksonomia `kategoria-produktu` są nowe — bez tego ich adresy
i endpoint synchronizacji dadzą 404.

**4. Wgraj na sklep** katalog `app/Sync/` i zmiany w `functions.php`.

**5. Przebuduj autoloader sklepu.** Motyw ładuje klasy przez `classmap`, więc
`App\Sync\*` nie istnieje, dopóki nie odświeży się `vendor/composer/`.
Bez konsoli są dwie drogi:

  * uruchom lokalnie `composer dump-autoload` i wgraj przez FTP cały katalog
    `vendor/composer/` (to tylko kilka plików z listą klas), **albo**
  * wgraj `vendor/` w całości razem z motywem.

To najczęstsza przyczyna „wgrałem i nic się nie dzieje”: WordPress milczy,
dopóki ktoś nie zapisze produktu.

**6. W panelu sklepu: Narzędzia → Synchronizacja z raks.**

  1. wpisz adres witryny raks (z podkatalogiem, bez ukośnika na końcu)
     i sekret, zapisz — albo ustaw stałe `RAKS_SYNC_URL` i `RAKS_SYNC_SECRET`
     w `wp-config.php` sklepu, wtedy pola są zablokowane i wygrywają stałe;
  2. **Sprawdź połączenie** — wysyła podpisane żądanie próbne, niczego nie
     zapisuje;
  3. **Wyślij wszystkie** — import leci partiami z podglądem postępu.

Od tej chwili każdy zapis produktu w sklepie jedzie do raks sam.

#### Co potrafi pójść nie tak

| komunikat | przyczyna |
|---|---|
| Podpis odrzucony | inny sekret po obu stronach albo zegary serwerów rozjechane o ponad 5 minut |
| Witryna raks jest zabezpieczona hasłem serwera | Basic Auth na środowisku przedprodukcyjnym — zdejmij je albo dopuść adres IP sklepu |
| Nie znaleziono endpointu | motyw raks niewgrany/nieaktywny albo nieodświeżone bezpośrednie odnośniki |
| Nie udało się połączyć | sklep nie dosięga raks — firewall, DNS, blokada połączeń wychodzących |
| cisza po zapisie produktu, ale „Wyślij wszystkie” działa | nieprzebudowany autoloader sklepu (punkt 5) |

Dla środowisk z konsolą te same czynności robią `wp raks sync-check`
i `wp raks sync-products` (`--dry-run`, `--id=`).

### Bezpieczeństwo

Podpisujemy treść razem ze znacznikiem czasu:

```
X-Raks-Timestamp: <czas uniksowy>
X-Raks-Signature: sha256=HMAC(sekret, "<timestamp>.<treść>")
```

Znacznik starszy niż pięć minut odrzucamy: sam podpis treści dałoby się
powtórzyć w nieskończoność i odtworzyć starą cenę.

Wszystko, co przychodzi, jest traktowane jak dane z zewnątrz — teksty przez
sanitizery, opis przez `wp_kses_post`, a obrazek pobieramy dopiero po
sprawdzeniu, że adres należy do domeny sklepu (`media_sideload_image` ściągnie
wszystko, co dostanie).

**Uwaga na kosz.** `post_status => 'any'` w WP_Query **nie obejmuje** kosza,
więc wyszukiwanie po `_shop_id` musi wypisywać statusy wprost. Bez tego produkt
przywrócony w sklepie zakładał tutaj drugi wpis obok pierwszego.

## Formularze

Wszystkie zgłoszenia idą jedną drogą: `POST /raks/v1/contact` → reCAPTCHA v3
→ tabela leadów. Formularz rozpoznajemy po polu `form` (`contact`, `demo`,
`trial`), a pola spoza standardowego zestawu lądują w kolumnie `payload`
jako JSON — stamtąd czytamy NIP zebrany przez kreator.

Wiadomość i zgoda są wymagane **warunkowo** — sprawdzamy je tylko wtedy, gdy
formularz w ogóle je przysłał. Zapis na demo nie ma pola wiadomości, a zapis
na test zbiera dobrowolną zgodę marketingową pod nazwą `marketing`; `consent`
znaczy zgodę wymaganą i nic innego.

`GET /raks/v1/nip` uzupełnia dane firmy w kreatorze z wykazu podatników VAT
Ministerstwa Finansów (publiczny, bez klucza — `raks_nip_lookup_url` pozwala
podmienić dostawcę). Sumę kontrolną NIP-u liczymy u siebie, żeby literówka
nie wychodziła poza serwer; odpowiedzi trzymamy dobę w transiencie. Awaria
rejestru nie blokuje formularza — odsłania pole nazwy firmy do wpisania
ręcznie.

Poza ustawieniami sekcji bloki mają własne przełączniki układu — po to, żeby
jeden blok obsłużył kilka miejsc w serwisie:

| blok | przełącznik | zastosowanie |
|---|---|---|
| `hero` | `layout`: wyśrodkowany / do lewej | strona główna vs nagłówki podstron |
| `hero` | `show_breadcrumbs` | okruszki w nagłówku podstrony |
| `hero` | `media_type`: gradient / wideo / zdjęcie | przyciemnienie dokładane tylko pod medium |
| `hero` | `text_width`: ograniczona / pełna | krótki tytuł wyśrodkowany vs długi na podstronie |
| `features` | `layout`: kafle / lista | „Dlaczego RAKS" vs „Kluczowe funkcje" |
| `features` | `columns`: 1–4 | gęstość siatki |
| `choice` | `columns`: 2–4, `featured` na karcie | „Chmura vs lokalnie" (2, jedna ciemna) vs „Elementy systemu" (3 jasne) |
| `pricing` | `columns`, `featured` na karcie | warianty abonamentu |
| `table` | kolumny i komórki z pól | cennik modułów **i** tabela porównawcza |
| `content-media` | `media_position`, `frame`, `media_type` | tekst + zdjęcie **albo osadzony iframe** (kalendarz, wideo), media po dowolnej stronie |
| `table` | `mode`: jedna tabela / zakładki | tabela porównawcza vs pliki do pobrania w trzech zakładkach |
| `pricing` | dwie akcje + linki dodatkowe na karcie | warianty abonamentu **i** karty pobierania |
| `contact` | `info_cards`, `map_embed` | z kolumną boczną formularz przechodzi na lewo i przejmuje tytuł |
| `cta` | `variant`: jasny / ciemny | pas rozbijający sekcje |

Trzy strony produktowe (Księgowy, Kadrowo-Płacowy, Sprzedaż i Magazyn) mają
identyczną strukturę siedmiu sekcji, z czego **sześć to bloki użyte już na
stronie głównej** — różnią się wyłącznie ustawieniami. Blok „Wyróżniki"
występuje na nich trzy razy z rzędu w trzech różnych konfiguracjach: lista
w dwóch kolumnach, kafle na zdjęciu w trzech, kafle na szarym w czterech.

Dlatego jest jeden `bin/seed-pages-produkty.php` i jeden plik danych
`bin/pages-produkty-dane.php` wygenerowany z `projekt/*.html` — treść nie
była przepisywana ręcznie.

Bloki nie mają własnych plików SCSS (poza `support`) — ich wygląd to utility
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
- Filtr kategorii działa przez AJAX (`/wp-json/raks/v1/posts`), karty renderuje
  serwer z `components/post-card.twig`, stan trafia do URL-a (`?kategoria=…`).
- Kolor kategorii: pole ACF `color` na taksonomii `category`, wstawiane jako
  `--term-color`. Bez pola badge używa koloru akcentu.

## Formularze

Blok `contact` wysyła dane do `POST /wp-json/raks/v1/contact`. Endpoint
weryfikuje reCAPTCHA v3, ma honeypot i limit żądań na IP, zapisuje zgłoszenie
do tabeli `{prefix}_raks_leads` i wysyła powiadomienie e-mail.

Zgłoszenia: panel → **Zgłoszenia** (filtrowanie, statusy, eksport CSV).

Klucze reCAPTCHA i adres powiadomień: Opcje motywu (`recaptcha_enabled`,
`recaptcha_site_key`, `recaptcha_secret_key`, `recaptcha_threshold`,
`contact_email`). Gdy reCAPTCHA jest wyłączona, weryfikacja przepuszcza żądania.

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
stoi na 1080 px, nie na 920 px z layoutu — przy 920 px pozycje nie mieszczą
się i w oryginale ratuje to ściśnięcie logo do 31 px. Link „Kontakt” wchodzi
dopiero od 1180 px, a CTA znika poniżej 560 px, żeby burger nie wypadł poza
ekran (w menu mobilnym CTA i tak jest).

## Logo

`src/svg/logo.svg` to obrys znaku z `fill="currentColor"` — jeden plik zamiast
dwóch kolorowych PNG-ów. Kolor nadaje otoczenie: biały nad ciemnym hero,
granatowy po przewinięciu (`group-data-scrolled:text-ink`). Proporcje 4,278:1.

Pola ACF `logo` i `logo_white` pozwalają klientowi podmienić znak na własny
plik; wtedy renderujemy `<img>` i potrzebne są obie wersje kolorystyczne.

`images/logo.png` zostaje wyłącznie dla ekranu logowania, gdzie logo jest
tłem CSS i `currentColor` nie zadziała.

## Zgodność z layoutem

Wartości pochodzą z `projekt/styles.css` **oraz z nadpisań inline
w `projekt/index.html`** — autor layoutu wymusił tam rozmiary inne niż
domyślne z arkusza i to one są docelowe:

| element | wartość | skąd |
|---|---|---|
| tytuł hero | 52 px, `#ebeef0` | inline w index.html |
| podtytuł hero | 20 px | inline |
| odstępy hero | 54 px / 132 px | inline |
| nagłówki sekcji | 40 px | inline, 4 wystąpienia |

W motywie są zapisane jako `clamp()` z maksimum równym wartości z projektu,
żeby nie rozsypały się na wąskich ekranach.

Dwie pułapki, o które łatwo się potknąć przy edycji:

- **Tokeny rozmiaru tekstu nie niosą interlinii.** W layoucie ten sam rozmiar
  bywa łamany różnie (15,5 px raz z 1,6, raz z 1,55), więc interlinia
  dziedziczy się z `body` (`leading-[1.6]` ustawione jawnie w `base.twig` —
  Preflight dałby 1.5), a odstępstwa zapisujemy jako `leading-[1.55]`.
- **Krycia bieli to dokładne wartości z projektu**, nie skala Tailwinda:
  `/6`, `/7`, `/12`, `/14`, `/16`, `/22`, `/28`, `/75`, `/76`, `/78`, `/82`,
  `/92`. Zaokrąglenie do `/5`, `/10`, `/15`, `/20` widać na ciemnych tłach.
