<?php

/**
 * Alternative to page/components/list
 * Renders an empty list if no items are provided
 * Pagination is rendered with JS
 */

namespace hypeJunction\Lists;

use ElggBatch;
use ElggEntity;

$items = $vars['items'];

$list_class = ['elgg-list'];
if (isset($vars['list_class'])) {
	$list_class[] = $vars['list_class'];
}

$item_class = ['elgg-item elgg-discoverable'];
if (isset($vars['item_class'])) {
	$item_class[] = $vars['item_class'];
}

$list_items = [];

if (is_array($items) || $items instanceof ElggBatch) {
	foreach ($items as $item) {
		$item_view = elgg_view_list_item($item, $vars);
		if (!$item_view) {
			continue;
		}

		$item_classes = $item_class;

		if ($item instanceof ElggEntity) {
			$guid = $item->getGUID();

			$type = $item->getType();
			$subtype = $item->getSubType();

			$id = implode('-', ['elgg', $type, $guid]);

			$item_classes[] = implode('-', ['elgg', 'item', $type]);
			if ($subtype) {
				$item_classes[] = implode('-', ['elgg', 'item', $type, $subtype]);
			}
		} else if (is_callable([$item, 'getType'])) {
			$id = "item-{$item->getType()}-{$item->id}";
		}

		$list_items[] = elgg_format_element('li', [
			'id' => $id,
			'class' => implode(' ', $item_classes),
		], $item_view);
	}
}

echo elgg_format_element('ul', [
	'class' => implode(' ', $list_class),
], implode('', $list_items));
