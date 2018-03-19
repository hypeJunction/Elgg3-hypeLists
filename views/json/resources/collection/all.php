<?php

$request = elgg_extract('request', $vars);
/* @var $request \Elgg\Request */

$collections = elgg()->collections;
/* @var $collections \hypeJunction\Lists\Collections */

$collection = $collections->build($request->getRoute(), null, $request->getParams());
/* @var $collection \hypeJunction\Lists\CollectionInterface */

if (!$collection) {
	throw new \Elgg\PageNotFoundException();
}

$data = $collection->export();

elgg_set_http_header('Content-Type: application/json');

echo json_encode($data);
