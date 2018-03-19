<?php

elgg_require_js('forms/collection/search');

$collection = elgg_extract('collection', $vars);

if (!$collection instanceof \hypeJunction\Lists\CollectionInterface) {
	return;
}

$fields = $collection->getSearchFields();
if (empty($fields)) {
	return;
}

$fields = array_map(function(\hypeJunction\Lists\SearchFieldInterface $e) {
	return $e->getField();
}, $fields);

$fields = array_filter($fields);

echo elgg_view_field([
	'#type' => 'fieldset',
	'class' => 'elgg-sortable-list-fieldset',
	'fields' => $fields,
]);

$list_options = $collection->getListOptions();

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'list_type',
	'value' => elgg_extract('list_type', $options),
]);

$footer = elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('search'),
]);

elgg_set_form_footer($footer);