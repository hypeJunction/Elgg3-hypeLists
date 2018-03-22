<?php

$entity = \hypeJunction\Data\DataController::getEntity('user');

$options = [
	'types' => 'user',
	'relationship' => 'friend',
	'relationship_guid' => $entity->guid,
	'inverse_relationship' => false,
];

$options = array_merge($vars, $options);

$collection = new \hypeJunction\Lists\DefaultEntityCollection($entity, $options);

$fields = $collection->getSearchFields();
foreach ($fields as $field) {
	$field->setConstraints();
}

$data = $collection->export();

echo json_encode($data);
