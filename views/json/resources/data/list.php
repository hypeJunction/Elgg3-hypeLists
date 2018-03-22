<?php

$public_metadata = [
	'location',
	'status',
	'briefdescription',
	'excerpt',
];

$public_metadata = array_merge($public_metadata, (array) elgg_get_registered_tag_metadata_names());

$public_metadata = elgg_trigger_plugin_hook('public_metadata', 'search', [], $public_metadata);

$options = [
	'types' => get_input('types'),
	'subtypes' => get_input('subtypes'),
	'owner_guids' => get_input('owner_guids'),
	'container_guids' => get_input('container_guids'),
];

$metadata = get_input('metadata');
if (is_array($metadata)) {
	foreach ($metadata as $name => $value) {
		if (!in_array($name, $public_metadata)) {
			throw new \Elgg\EntityPermissionsException("'$name' is not public metadata");
		}
		$options['metadata_name_value_pairs'][] = [
			'name' => $name,
			'value' => $value,
		];
	}
}

$options = array_merge($vars, $options);

$collection = new \hypeJunction\Lists\DefaultEntityCollection(null, $options);

$fields = $collection->getSearchFields();
foreach ($fields as $field) {
	$field->setConstraints();
}

$data = $collection->export();


echo json_encode($data);
