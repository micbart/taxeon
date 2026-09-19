<?php

$t = static fn(string $text, string $tag = 'p'): array => ['text' => $text, 'tag' => $tag];

$btn = static fn(string $label, string $variant = 'primary'): array => [
	'link' => ['title' => $label, 'url' => '#', 'target' => ''],
	'variant' => $variant,
	'size' => 'lg',
];

$items = static fn(array $texts): array => array_map(static fn(string $x): array => ['text' => $x], $texts);

return [
	'hero' => [
		'layout' => 'center',
		'media_type' => 'none',
		'badge_highlight' => '35 lat',
		'badge_text' => 'na polskim rynku',
		'title' => $t('Nagłówek otwierający stronę', 'h1'),
		'subtitle' => $t('Zdanie, które tłumaczy, czym jest produkt i dla kogo.'),
		'button_primary' => $btn('Wypróbuj za darmo', 'on-navy'),
		'button_secondary' => $btn('Umów prezentację', 'ghost-navy'),
		'trust' => $items(['Bezpłatne wsparcie', 'Chmura lub instalacja lokalna']),
	],

	'choice' => [
		'eyebrow' => $t('Nadkreślenie', 'span'),
		'title' => $t('Dwie drogi do wyboru', 'h2'),
		'text' => $t('Krótkie zdanie wprowadzające do porównania.'),
		'columns' => '2',
		'cards' => [
			[
				'featured' => true,
				'icon' => 'cloud',
				'tag' => 'Wariant pierwszy',
				'title' => $t('Karta wyróżniona', 'h3'),
				'text' => $t('Opis wariantu w dwóch, trzech zdaniach.'),
				'items' => $items(['Pierwsza korzyść', 'Druga korzyść', 'Trzecia korzyść']),
			],
			[
				'featured' => false,
				'icon' => 'hard-drive',
				'tag' => 'Wariant drugi',
				'title' => $t('Karta zwykła', 'h3'),
				'text' => $t('Opis drugiego wariantu.'),
				'items' => $items(['Pierwsza korzyść', 'Druga korzyść']),
			],
		],
		'footnote' => 'Zdanie domykające sekcję pod kartami.',
	],

	'programs' => [
		'eyebrow' => $t('Nadkreślenie', 'span'),
		'title' => $t('Trzy karty produktów', 'h2'),
		'text' => $t('Zdanie wprowadzające do listy.'),
		'programs' => [
			[
				'title' => $t('Pierwszy produkt', 'h3'),
				'text' => $t('Do czego służy i komu się przyda.'),
				'tags' => [['label' => 'Etykieta'], ['label' => 'Etykieta']],
				'link' => ['title' => 'Dowiedz się więcej', 'url' => '#', 'target' => ''],
			],
			[
				'title' => $t('Drugi produkt', 'h3'),
				'text' => $t('Do czego służy i komu się przyda.'),
				'tags' => [['label' => 'Etykieta'], ['label' => 'Etykieta']],
				'link' => ['title' => 'Dowiedz się więcej', 'url' => '#', 'target' => ''],
			],
			[
				'title' => $t('Trzeci produkt', 'h3'),
				'text' => $t('Do czego służy i komu się przyda.'),
				'tags' => [['label' => 'Etykieta']],
				'link' => ['title' => 'Dowiedz się więcej', 'url' => '#', 'target' => ''],
			],
		],
	],

	'features' => [
		'eyebrow' => $t('Nadkreślenie', 'span'),
		'title' => $t('Cztery wyróżniki', 'h2'),
		'text' => $t('Zdanie wprowadzające.'),
		'layout' => 'cards',
		'columns' => '4',
		'items' => [
			['icon' => 'settings-2', 'title' => $t('Pierwszy', 'h3'), 'text' => $t('Krótkie wyjaśnienie.')],
			['icon' => 'tag', 'title' => $t('Drugi', 'h3'), 'text' => $t('Krótkie wyjaśnienie.')],
			['icon' => 'sparkles', 'title' => $t('Trzeci', 'h3'), 'text' => $t('Krótkie wyjaśnienie.')],
			['icon' => 'landmark', 'title' => $t('Czwarty', 'h3'), 'text' => $t('Krótkie wyjaśnienie.')],
		],
	],

	'support' => [
		'eyebrow' => $t('Nadkreślenie', 'span'),
		'title' => $t('Rozsuwane kafle', 'h2'),
		'text' => $t('Zdanie wprowadzające do harmonijki.'),
		'items' => [
			['icon' => 'headset', 'title' => $t('Pierwszy kafel', 'h3'), 'text' => $t('Opis widoczny po rozwinięciu.')],
			['icon' => 'file-check', 'title' => $t('Drugi kafel', 'h3'), 'text' => $t('Opis widoczny po rozwinięciu.')],
			['icon' => 'graduation-cap', 'title' => $t('Trzeci kafel', 'h3'), 'text' => $t('Opis widoczny po rozwinięciu.')],
		],
	],

	'advisor' => [
		'eyebrow' => $t('Nadkreślenie', 'span'),
		'title' => $t('Wezwanie do rozmowy', 'h2'),
		'text' => $t('Zdanie zachęcające do kontaktu z doradcą.'),
		'icon' => 'user-round',
		'benefits' => $items(['Pierwsza korzyść', 'Druga korzyść', 'Trzecia korzyść']),
		'button_primary' => $btn('Umów prezentację', 'on-navy'),
		'button_secondary' => $btn('Pobierz demo', 'ghost-navy'),
		'stats' => [
			['icon' => 'award', 'value' => '35 lat', 'label' => 'na rynku'],
			['icon' => 'building-2', 'value' => '16 000', 'label' => 'firm i biur rachunkowych'],
		],
	],

	'latest-posts' => [
		'eyebrow' => $t('Nadkreślenie', 'span'),
		'title' => $t('Najnowsze wpisy', 'h2'),
		'text' => $t('Trzy ostatnie artykuły z bloga.'),
		'source' => 'latest',
		'count' => 3,
		'link' => ['title' => 'Wszystkie wpisy', 'url' => '#', 'target' => ''],
	],

	'cta' => [
		'variant' => 'dark',
		'icon' => 'sparkles',
		'title' => $t('Wezwanie do działania', 'h2'),
		'text' => $t('Jedno zdanie i dwa przyciski.'),
		'button_primary' => $btn('Główna akcja', 'on-navy'),
		'button_secondary' => $btn('Druga akcja', 'ghost-navy'),
	],

	'contact' => [
		'layout' => 'split',
		'eyebrow' => $t('Nadkreślenie', 'span'),
		'title' => $t('Formularz kontaktowy', 'h2'),
		'text' => $t('Zdanie zachęcające do napisania.'),
		'submit_label' => 'Wyślij wiadomość',
		'consent_text' => 'Treść wymaganej zgody.',
		'details' => [
			['icon' => 'phone', 'label' => 'Telefon', 'value' => '885 885 000'],
			['icon' => 'mail', 'label' => 'E-mail', 'value' => 'kontakt@example.pl'],
		],
	],

	'pricing' => [
		'eyebrow' => $t('Nadkreślenie', 'span'),
		'title' => $t('Karty cenowe', 'h2'),
		'text' => $t('Zdanie wprowadzające do cennika.'),
		'columns' => '2',
		'cards' => [
			[
				'featured' => true,
				'icon' => 'cloud',
				'flag' => 'Polecany',
				'title' => $t('Wariant pierwszy', 'h3'),
				'text' => $t('Dla kogo jest ten wariant.'),
				'price' => '49 zł',
				'price_suffix' => 'miesięcznie',
				'items' => $items(['Pierwsza pozycja', 'Druga pozycja', 'Trzecia pozycja']),
				'button' => $btn('Wybieram', 'on-navy'),
			],
			[
				'featured' => false,
				'icon' => 'hard-drive',
				'title' => $t('Wariant drugi', 'h3'),
				'text' => $t('Dla kogo jest ten wariant.'),
				'price' => '990 zł',
				'price_suffix' => 'jednorazowo',
				'items' => $items(['Pierwsza pozycja', 'Druga pozycja']),
				'button' => $btn('Wybieram', 'accent'),
			],
		],
	],

	'table' => [
		'eyebrow' => $t('Nadkreślenie', 'span'),
		'title' => $t('Tabela porównawcza', 'h2'),
		'text' => $t('Zdanie wprowadzające do tabeli.'),
		'mode' => 'single',
		'head' => [
			['label' => 'Cecha', 'hide_mobile' => false],
			['label' => 'Wariant A', 'hide_mobile' => false],
			['label' => 'Wariant B', 'hide_mobile' => false],
		],
		'rows' => [
			['cells' => [
				['text' => 'Pierwsza cecha', 'style' => 'strong'],
				['text' => 'tak', 'style' => 'normal'],
				['text' => 'tak', 'style' => 'normal'],
			]],
			['cells' => [
				['text' => 'Druga cecha', 'style' => 'strong'],
				['text' => 'nie', 'style' => 'muted'],
				['text' => 'tak', 'style' => 'normal'],
			]],
			['cells' => [
				['text' => 'Cena', 'style' => 'strong'],
				['text' => '49 zł', 'style' => 'price'],
				['text' => '990 zł', 'style' => 'price'],
			]],
		],
	],

	'content-media' => [
		'eyebrow' => $t('Nadkreślenie', 'span'),
		'title' => $t('Tekst obok obrazka', 'h2'),
		'text' => $t('Akapit po jednej stronie, materiał po drugiej.'),
		'media_position' => 'right',
		'media_type' => 'image',
		'frame' => 'window',
		'frame_label' => 'podgląd',
		'items' => [
			['icon' => 'circle-check', 'title' => $t('Pierwszy punkt', 'h3'), 'text' => 'Zdanie wyjaśniające.'],
			['icon' => 'circle-check', 'title' => $t('Drugi punkt', 'h3'), 'text' => 'Zdanie wyjaśniające.'],
		],
		'button' => $btn('Dowiedz się więcej', 'primary'),
	],

	'timeline' => [
		'eyebrow' => $t('Nadkreślenie', 'span'),
		'title' => $t('Oś czasu', 'h2'),
		'text' => $t('Zdanie wprowadzające do chronologii.'),
		'items' => [
			['year' => '1991', 'title' => $t('Pierwszy etap', 'h3'), 'text' => $t('Co się wtedy wydarzyło.')],
			['year' => '2008', 'title' => $t('Drugi etap', 'h3'), 'text' => $t('Co się wtedy wydarzyło.')],
			['year' => '2026', 'title' => $t('Trzeci etap', 'h3'), 'text' => $t('Co się wtedy wydarzyło.')],
		],
	],

	'stats' => [
		'eyebrow' => $t('Nadkreślenie', 'span'),
		'title' => $t('Liczby', 'h2'),
		'text' => $t('Zdanie wprowadzające.'),
		'columns' => '4',
		'items' => [
			['icon' => 'award', 'value' => '35 lat', 'label' => 'na rynku'],
			['icon' => 'building-2', 'value' => '16 000', 'label' => 'firm'],
			['icon' => 'briefcase', 'value' => '2 000', 'label' => 'biur rachunkowych'],
			['icon' => 'youtube', 'value' => '75 000', 'label' => 'subskrybentów'],
		],
	],

	'faq' => [
		'eyebrow' => $t('FAQ', 'span'),
		'title' => $t('Najczęstsze pytania', 'h2'),
		'align' => 'center',
		'columns' => '1',
		'single' => true,
		'icon' => 'plus',
		'items' => [
			['question' => 'Pierwsze pytanie?', 'answer' => 'Odpowiedź w jednym, dwóch zdaniach.'],
			['question' => 'Drugie pytanie?', 'answer' => 'Odpowiedź w jednym, dwóch zdaniach.'],
			['question' => 'Trzecie pytanie?', 'answer' => 'Odpowiedź w jednym, dwóch zdaniach.'],
		],
	],

	'trial-wizard' => [
		'show_rail' => true,
		'rail_position' => 'left',
		'eyebrow' => $t('Nadkreślenie', 'span'),
		'title' => $t('Kolumna zaufania', 'h2'),
		'text' => $t('Zdanie, które przekonuje do wypełnienia kreatora.'),
		'stats' => [
			['icon' => 'award', 'value' => '35', 'unit' => 'lat', 'label' => 'na rynku'],
			['icon' => 'building-2', 'value' => '16 000', 'label' => 'firm'],
		],
		'points' => $items(['Pierwsza korzyść', 'Druga korzyść']),
		'secure_icon' => 'shield-check',
		'secure_text' => 'Twoje dane są bezpieczne.',
		'step1_kicker' => 'Krok 1 z 3',
		'step1_title' => $t('Podaj numer NIP', 'h3'),
		'step1_text' => 'Dane firmy uzupełnimy z rejestru.',
		'step2_label' => 'Dane kontaktowe',
		'step3_label' => 'Pobranie',
		'foot' => [
			['icon' => 'lock', 'text' => 'Połączenie szyfrowane'],
			['icon' => 'credit-card', 'text' => 'Bez karty'],
		],
	],
];
