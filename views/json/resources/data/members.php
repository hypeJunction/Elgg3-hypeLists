<?php

$options = [
	'types' => 'user',
];

$adapter = new \hypeJunction\Data\CollectionAdapter($options);
$data = $adapter->export();

echo json_encode($data);
