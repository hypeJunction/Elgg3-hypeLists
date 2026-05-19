<?php

$request = elgg_extract('request', $vars);
/* @var $request \Elgg\Request */

$username = $request->getParam('username');
if ($username) {
	$user = elgg_get_user_by_username($username);
} else {
	$user = elgg_get_logged_in_user_entity();
}

if (!$user) {
	throw new \Elgg\Exceptions\Http\EntityNotFoundException();
}

$collections = elgg()->collections;
/* @var $collections \hypeJunction\Lists\Collections */

$collection = $collections->build($request->getRoute(), $user, $request->getParams());
/* @var $collection \hypeJunction\Lists\CollectionInterface */

if (!$collection) {
	throw new \Elgg\Exceptions\Http\PageNotFoundException();
}

$data = $collection->export();

elgg_set_http_header('Content-Type: application/json');

echo json_encode($data);
