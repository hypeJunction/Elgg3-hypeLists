<?php

$entity = \hypeJunction\Data\DataController::getEntity();

$options = [
	'types' => 'object',
	'subtypes' => 'comment',
	'container_guids' => $entity->guid,
];

$options = array_merge($vars, $options);

$collection = new \hypeJunction\Lists\DefaultEntityCollection($entity, $options);

$fields = $collection->getSearchFields();
foreach ($fields as $field) {
	$field->setConstraints();
}

$data = $collection->export();

echo json_encode($data);
