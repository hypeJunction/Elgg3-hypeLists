<?php

$guid = (int) get_input('guid');
$entity = $guid ? get_entity($guid) : null;

$menu = [];

if ($entity) {
	$svc = elgg()->menus->getMenu('entity', [
		'entity' => $entity,
		'sort_by' => 'priority',
	]);

	foreach ($svc->getSections() as $section => $items) {
		foreach ($items as $item) {
			/* @var $item ElggMenuItem */
			$adapter = new \hypeJunction\Data\ElggMenuItemAdapter($item, 'entity');
			$menu[$section][] = $adapter->export();
		}
	}
}

$data = [
	'menu' => $menu ?: null,
];

echo json_encode($data);
