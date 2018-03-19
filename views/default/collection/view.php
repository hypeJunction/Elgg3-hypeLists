<?php

$collection = elgg_extract('collection', $vars);

if (!$collection instanceof \hypeJunction\Lists\CollectionInterface) {
	return;
}

echo $collection->render($vars);