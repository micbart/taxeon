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
];
