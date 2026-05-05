<?php

$collection = elgg_extract('collection', $vars);

if (!$collection instanceof \hypeJunction\Lists\CollectionInterface) {
	return;
}

$id = md5($collection->getURL());

$form = elgg_view_form('collection/search', [
	'action' => $collection->getURL(),
	'method' => 'GET',
	'disable_security' => true,
	'class' => 'elgg-form-sortable-list',
	'rel' => "#list-sort-{$id}",
], $vars);

if (empty($form)) {
	return;
}

if (elgg_extract('expand_form', $vars, true)) {
	echo $form;
} else {
	echo elgg_view('output/url', [
		'href' => '#',
		'text' => elgg_view_icon('filter') . elgg_echo('sort:menu:filter'),
		'class' => 'elgg-sortable-list-form-toggle',
	]);

	echo elgg_format_element('div', [
		'class' => 'elgg-sortable-list-form-container hidden',
	], $form);
}
