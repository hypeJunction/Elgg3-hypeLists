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

$output = '';
foreach ($fields as $field) {
	$output .= elgg_view_field($field);
}

echo elgg_format_element('div', [
	'class' => 'elgg-grid post-form',
], $output);

$list_options = $collection->getListOptions();

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'list_type',
	'value' => elgg_extract('list_type', $list_options),
]);

$footer = elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('search'),
]);

elgg_set_form_footer($footer);