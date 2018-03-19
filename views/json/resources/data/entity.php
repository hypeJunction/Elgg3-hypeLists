<?php

$entity = \hypeJunction\Data\DataController::getEntity();

$adapter = new \hypeJunction\Data\CollectionItemAdapter($entity);
$data = $adapter->export();

echo json_encode($data);
