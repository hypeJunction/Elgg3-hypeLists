<?php

$entity = \hypeJunction\Data\DataController::getEntity('user');

$options = [
	'types' => 'user',
	'relationship' => 'friend',
	'relationship_guid' => $entity->guid,
	'inverse_relationship' => false,
];

$adapter = new \hypeJunction\Data\CollectionAdapter($options);
$data = $adapter->export();

echo json_encode($data);
