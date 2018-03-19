<?php

$entity = \hypeJunction\Data\DataController::getEntity();

$options = [
	'types' => 'object',
	'subtypes' => $entity->getSubtype() == 'discussion' ? 'discussion_reply' : 'comment',
	'container_guids' => $entity->guid,
];

$adapter = new \hypeJunction\Data\CollectionAdapter($options);
$data = $adapter->export();

echo json_encode($data);
