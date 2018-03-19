<?php

namespace hypeJunction\Lists;

use ElggEntity;
use InvalidParameterException;

class Collections {

	/**
	 * @var array
	 */
	protected $collections = [];

	/**
	 * Register a new collection
	 *
	 * @param string $name  Collection name
	 *                      Must follow the route name convention, e.g. collection:object:blog:owner
	 * @param string $class Collection class
	 *                      Must implement Collection interface
	 *
	 * @return void
	 */
	public function register($name, $class) {
		$this->collections[$name] = $class;
	}

	/**
	 * Build a new collection
	 *
	 * @param string     $name   Collection name
	 * @param ElggEntity $target Collection target
	 * @param array      $params Request params
	 *
	 * @return CollectionInterface|null
	 * @throws InvalidParameterException
	 */
	public function build($name, ElggEntity $target = null, array $params = []) {
		if (!isset($this->collections[$name])) {
			return null;
		}

		$class = $this->collections[$name];
		if (!is_subclass_of($class, CollectionInterface::class)) {
			throw new InvalidParameterException("Collection class " . $class . " must implement " . CollectionInterface::class);
		}

		$collection = new $class($target, $params);

		/* @var $collection CollectionInterface */

		$this->setConstraints($collection);

		return $collection;
	}

	/**
	 * Set collection constraints (filter, sort, query)
	 *
	 * @param CollectionInterface $collection Collection
	 *
	 * @return void
	 */
	protected function setConstraints(CollectionInterface $collection) {

		$fields = $collection->getSearchFields();

		foreach ($fields as $field) {
			$field->setConstraints();
		}

	}
}