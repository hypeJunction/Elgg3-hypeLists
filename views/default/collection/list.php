<?php

$form = elgg_extract('form', $vars, '');
unset($vars['form']);

$items = elgg_extract('items', $vars);
unset($vars['items']);

$view = elgg_view_entity_list($items, $vars);

$view = elgg_format_element('div', [
	'class' => 'elgg-sortable-list-view',
], $view);

$id = elgg_extract('list_id', $vars);

echo elgg_format_element('div', [
	'id' => "list-sort-{$id}",
	'class' => "elgg-sortable-list",
], $form . $view);