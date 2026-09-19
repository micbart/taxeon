<?php

use App\Services\BlogArchiveContext;
use App\Services\PostContext;
use Timber\Timber;

$data = Timber::context();

$template = ['templates/page.twig', 'layouts/base.twig'];

if (is_front_page()) {
	$template = ['templates/front-page.twig', 'templates/page.twig'];
}

if (is_singular('post')) {
	$template = ['templates/post.twig'];
	$data = array_merge($data, (new PostContext())->build($data['post']));
}

if (is_singular('produkt')) {
	$template = ['templates/single-produkt.twig'];
}

if (is_tax('kategoria-produktu')) {
	$template = ['templates/archive-produkt.twig'];
}

if (is_home() || is_category() || is_tag() || is_author() || is_date()) {
	$template = ['templates/archive.twig'];
	$data = array_merge($data, (new BlogArchiveContext())->build());
}

if (is_search()) {
	$template = ['templates/search.twig'];
	$data = array_merge($data, (new BlogArchiveContext())->build());
}

if (is_404()) {
	$template = ['templates/404.twig'];
}

Timber::render($template, $data);
