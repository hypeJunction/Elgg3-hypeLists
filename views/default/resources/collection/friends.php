<?php

$request = elgg_extract('request', $vars);
/* @var $request \Elgg\Request */

$username = $request->getParam('username');
if ($username) {
	$user = get_user_by_username($username);
} else {
	$user = elgg_get_logged_in_user_entity();
}

if (!$user) {
	throw new \Elgg\EntityNotFoundException();
}

$collections = elgg()->collections;
/* @var $collections \hypeJunction\Lists\Collections */

$collection = $collections->build($request->getRoute(), $user, $request->getParams());
/* @var $collection \hypeJunction\Lists\CollectionInterface */

if (!$collection) {
	throw new \Elgg\PageNotFoundException();
}

$content = elgg_view('collection/view', [
	'collection' => $collection,
]);

if ($request->isXhr()) {
	echo $content;
	return;
}

$type = $collection->getType();
$subtypes = (array) $collection->getSubtypes();
$subtype = array_shift($subtypes);

foreach ($collection->getMenu() as $item) {
	$item->addLinkClass('elgg-button elgg-button-action');
	elgg_register_menu_item('title', $item);
}

elgg_push_collection_breadcrumbs($type, $subtype, $user, true);

$layout = elgg_view_layout('default', [
	'collection' => $collection,
	'title' => $collection->getDisplayName(),
	'content' => $content,
	'sidebar' => elgg_view('collection/sidebar', [
		'collection' => $collection,
	]),
	'filter_id' => 'collection/friends',
	'filter_value' => 'all',
	'target' => $user,
]);

echo elgg_view_page($title, $layout, 'default', [
	'collection' => $collection,
]);