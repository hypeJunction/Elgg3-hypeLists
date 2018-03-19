<?php

$collection = elgg_extract('collection', $vars);

if (!$collection instanceof \hypeJunction\Lists\CollectionInterface) {
	return;
}

$filter = elgg_view('collection/search', $vars);
if ($filter) {
	$title = elgg_echo('collection:search', [$collection->getDisplayName()]);
	echo elgg_view_module('aside', $title, $filter, [
		'class' => 'collection-filter-module',
	]);
}

if (in_array($collection->getCollectionType(), ['all', 'owner', 'group'])) {
	$entity = $collection->getTarget();

	echo elgg_view('page/elements/comments_block', [
		'types' => $collection->getType(),
		'subtypes' => $collection->getSubtypes(),
		'container_guid' => $entity ? $entity->guid : null,
	]);

	echo elgg_view('page/elements/tagcloud_block', [
		'types' => $collection->getType(),
		'subtypes' => $collection->getSubtypes(),
		'container_guid' => $entity ? $entity->guid : null,
	]);
}