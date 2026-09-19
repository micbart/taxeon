<?php

namespace App\PostTypes;

class KategoriaProduktu extends AbstractTaxonomy
{
	protected string $taxonomy = 'kategoria-produktu';
	protected string $taxonomySlug = 'sklep/kategoria';
	protected array $postTypes = ['produkt'];
	protected string $singular = 'Kategoria';
	protected string $plural = 'Kategorie produktów';
}
