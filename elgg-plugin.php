<?php

return [
	'routes' => [
		'collection:all' => [
			'path' => '/collection/{type}/{subtype}/all',
			'resource' => 'collection/all',
		],
		'collection:owner' => [
			'path' => '/collection/{type}/{subtype}/owner/{username?}',
			'resource' => 'collection/owner',
		],
		'collection:friends' => [
			'path' => '/collection/{type}/{subtype}/friends/{username?}',
			'resource' => 'collection/friends',
		],
		'collection:group' => [
			'path' => '/collection/{type}/{subtype}/group/{guid}',
			'resource' => 'collection/group',
		],
		'data' => [
			'path'=> '/data/{segments}',
			'controller' => \hypeJunction\Data\DataController::class,
			'requirements' => [
				'segments' => '.+',
			],
		],
	],

	'settings' => [
		'pagination_type' => null,
	],
];